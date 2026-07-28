<?php

namespace Modules\Saas\Domain\Tenancy;

use Closure;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\TenantBootstrapper;
use Modules\Saas\Contracts\TenantConnectionManager;
use Modules\Saas\Exceptions\TenantNotResolved;
use Throwable;

/**
 * The single source of truth for "which tenant am I in".
 *
 * Registered as a singleton. Setting a context also swaps the database
 * connection and runs every bootstrapper, so context / connection / cache /
 * storage can never drift apart — a context saying tenant A while the
 * connection points at tenant B is the exact failure this design exists to
 * prevent.
 */
class TenantContextManager implements CurrentTenant
{
    private ?TenantContext $context = null;

    /**
     * @param  iterable<TenantBootstrapper>  $bootstrappers
     */
    public function __construct(
        private readonly TenantConnectionManager $connections,
        private readonly iterable $bootstrappers = [],
    ) {
    }

    public function get(): ?TenantContext
    {
        return $this->context;
    }

    public function getOrFail(): TenantContext
    {
        return $this->context ?? throw TenantNotResolved::inContext('CurrentTenant::getOrFail');
    }

    public function has(): bool
    {
        return $this->context !== null;
    }

    public function uuid(): ?string
    {
        return $this->context?->uuid;
    }

    public function set(TenantContext $context): void
    {
        // Switching an already-initialized singleton must completely unwind
        // the previous tenant first. Reusing bootstrappers while they still
        // hold tenant A's roots/prefixes can otherwise leak that state into B.
        if ($this->context !== null) {
            $this->forget();
        }

        // Connection first: if credentials are wrong we want to fail before
        // any other global has been mutated.
        $this->connections->connect($context);

        try {
            foreach ($this->bootstrappers as $bootstrapper) {
                $bootstrapper->bootstrap($context);
            }
        } catch (Throwable $e) {
            // Never leave the process half-switched — that is worse than
            // not switching at all, because some reads would hit the new
            // tenant and some the old.
            $this->tearDown();

            throw $e;
        }

        $this->context = $context;
    }

    public function forget(): void
    {
        $this->tearDown();
        $this->context = null;
    }

    public function runFor(TenantContext $context, Closure $callback): mixed
    {
        $previous = $this->context;

        $this->set($context);

        try {
            return $callback();
        } finally {
            // Restore in a finally so a throwing callback cannot leave a
            // long-lived worker pointed at the wrong tenant.
            $this->forget();

            if ($previous !== null) {
                $this->set($previous);
            }
        }
    }

    public function runWithout(Closure $callback): mixed
    {
        $previous = $this->context;

        $this->forget();

        try {
            return $callback();
        } finally {
            if ($previous !== null) {
                $this->set($previous);
            }
        }
    }

    /**
     * Revert bootstrappers in reverse order, then the connection.
     *
     * Reverse order matters: bootstrappers are set up outermost-first, so
     * unwinding innermost-first mirrors how nested state was applied. Each
     * revert is isolated so one failing bootstrapper cannot strand the rest —
     * a stranded tenant prefix is a data leak, a logged warning is not.
     */
    private function tearDown(): void
    {
        $bootstrappers = is_array($this->bootstrappers)
            ? $this->bootstrappers
            : iterator_to_array($this->bootstrappers);

        foreach (array_reverse($bootstrappers) as $bootstrapper) {
            try {
                $bootstrapper->revert();
            } catch (Throwable $e) {
                report($e);
            }
        }

        try {
            $this->connections->release();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
