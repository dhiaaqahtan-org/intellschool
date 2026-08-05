<?php

namespace Modules\Saas\Bootstrappers;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\SessionManager;
use Modules\Saas\Contracts\TenantBootstrapper;
use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * Gives each tenant its own session store.
 *
 * WHY THIS EXISTS — the file session driver keys a session by its random ID and
 * nothing else, in one directory shared by every school. The session cookie is
 * host-only (session.domain stays unset), so a browser will not carry it across
 * tenants on its own; but a cookie is client-side data, and pasting one onto
 * another tenant's host is a devtools operation, not an exploit chain.
 *
 * What happened next, before this class existed:
 *   1. StartSession finds the session file — the ID is valid, and the directory
 *      is shared, so nothing about it looks foreign.
 *   2. The payload carries `login_web_<hash> => 5`, a bare numeric user id.
 *   3. The guard resolves id 5 against whatever database is now default, which
 *      during a tenant request is THAT tenant's database.
 *   4. The request is authenticated as tenant B's user #5 — a different person
 *      at a different school.
 *
 * Numeric ids collide across tenant databases by design (see TenantContext),
 * which is exactly why nothing may be scoped by them. Laravel's own defence
 * here, AuthenticateSession, is commented out in App\Http\Kernel, and it only
 * compares a password hash — it would reject the crossover by accident rather
 * than on purpose.
 *
 * Re-rooting the directory makes the crossover a miss instead of a hit: the
 * file is not there, so the request starts a fresh, unauthenticated session.
 *
 * The other drivers were already isolated, and are left alone:
 *   - `database` writes to the default connection, which is the tenant's own
 *     database for the duration of the request;
 *   - `redis`/`cache` go through the cache store, whose prefix CacheBootstrapper
 *     has already namespaced by tenant (it is tagged ahead of this one).
 */
class SessionBootstrapper implements TenantBootstrapper
{
    private ?string $originalPath = null;

    private ?string $originalCookie = null;

    private bool $active = false;

    public function __construct(
        private readonly SessionManager $sessions,
        private readonly Config $config,
        private readonly Filesystem $files,
    ) {
    }

    public function bootstrap(TenantContext $context): void
    {
        // The cookie name is renamed for every driver. Storage separation stops
        // a copied cookie from *finding* a session; a distinct name stops the
        // browser from ever presenting it in the first place, which also means
        // a school's own users cannot be logged into two schools under one
        // cookie by accident. The two defences are independent on purpose.
        $this->originalCookie ??= (string) $this->config->get('session.cookie');
        $this->config->set('session.cookie', $this->cookieNameFor($context));

        if ($this->config->get('session.driver') === 'file') {
            $this->originalPath ??= $this->config->get('session.files');

            $path = rtrim((string) $this->originalPath, '/\\').DIRECTORY_SEPARATOR.$context->uuid;

            // FileSessionHandler writes with file_put_contents and does NOT
            // create its directory — unlike the file cache store, which is why
            // CacheBootstrapper needs no equivalent. Without this the first
            // request for a tenant throws on save and the session never
            // persists.
            $this->files->ensureDirectoryExists($path);

            $this->config->set('session.files', $path);
        }

        $this->refreshDrivers();

        $this->active = true;
    }

    public function revert(): void
    {
        if (! $this->active) {
            return;
        }

        if ($this->originalPath !== null) {
            $this->config->set('session.files', $this->originalPath);
        }

        $this->config->set('session.cookie', $this->originalCookie);

        $this->refreshDrivers();

        $this->originalPath = null;
        $this->originalCookie = null;
        $this->active = false;
    }

    /**
     * Derived from the UUID, not the slug or host. A slug can be changed by an
     * operator and a school can move to a custom domain; either would silently
     * rename the cookie and log everyone out. The UUID is the one identifier
     * that never changes for the life of the tenant.
     */
    private function cookieNameFor(TenantContext $context): string
    {
        $base = $this->originalCookie !== '' ? $this->originalCookie : 'laravel_session';

        return $base.'_'.substr(hash('sha256', $context->uuid), 0, 12);
    }

    /**
     * The file handler captures its directory at construction, so changing
     * config after the driver has been resolved would leave the previous
     * tenant's path in place — the precise failure this class exists to stop.
     */
    private function refreshDrivers(): void
    {
        $this->sessions->forgetDrivers();
    }
}
