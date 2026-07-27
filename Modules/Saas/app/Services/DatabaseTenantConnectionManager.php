<?php

namespace Modules\Saas\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\DatabaseManager;
use Modules\Saas\Contracts\TenantConnectionManager;
use Modules\Saas\Contracts\TenantCredentialResolver;
use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * Swaps the default database connection to a tenant's own database.
 *
 * Why the *default* connection and not a named one: the ERP has 222 models and
 * 693 controllers that declare no connection. Making the tenant connection the
 * default means every one of them lands in the right database with zero edits,
 * and — critically — a model someone forgets to update cannot accidentally
 * read the landlord or a previous tenant.
 *
 * Landlord models override getConnectionName() explicitly, so they are
 * unaffected by the swap.
 */
class DatabaseTenantConnectionManager implements TenantConnectionManager
{
    private ?string $originalDefault = null;

    private ?string $connectedUuid = null;

    /**
     * Snapshot of the template connection, captured once on first use.
     *
     * Captured rather than re-read because release() overwrites the tenant
     * connection's config entry. When the template and the tenant connection
     * are the same entry — which is a legitimate configuration, and the one
     * the test suite uses — re-reading would return the released (null) value
     * and the next connect() would build a connection with no driver.
     */
    private ?array $template = null;

    /** Original config of the tenant connection key, restored on release. */
    private ?array $originalTenantConfig = null;

    private bool $captured = false;

    public function __construct(
        private readonly DatabaseManager $db,
        private readonly Config $config,
        private readonly TenantCredentialResolver $credentials,
    ) {
    }

    public function connectionName(): string
    {
        return $this->config->get('saas.database.tenant_connection', 'tenant');
    }

    public function isConnected(): bool
    {
        return $this->connectedUuid !== null;
    }

    public function connect(TenantContext $context): void
    {
        $name = $this->connectionName();

        // Remember the real default once, so nested switches restore correctly.
        $this->originalDefault ??= $this->config->get('database.default');

        $credentials = $this->credentials->resolveFor($context);

        // Template supplies driver, charset, collation, options; credentials
        // and database name are always overridden. Nothing here comes from
        // the request.
        if (! $this->captured) {
            $templateName = $this->config->get('saas.database.tenant_template', 'mysql');

            $this->template = $this->config->get("database.connections.{$templateName}") ?? [];
            $this->originalTenantConfig = $this->config->get("database.connections.{$name}");
            $this->captured = true;
        }

        if ($this->template === []) {
            throw new \RuntimeException(
                'Tenant connection template ['.$this->config->get('saas.database.tenant_template')
                .'] is not configured; refusing to build a tenant connection from nothing.'
            );
        }

        $this->config->set("database.connections.{$name}", array_merge($this->template, [
            'database' => $context->databaseName,
            'host' => $credentials['host'],
            'port' => $credentials['port'],
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ]));

        // Purge before reconnecting. Without this, Laravel hands back the
        // cached PDO handle from the PREVIOUS tenant and every subsequent
        // query silently reads the wrong database. This single line is the
        // difference between isolation and a cross-tenant breach on a
        // long-lived worker.
        $this->db->purge($name);

        $this->config->set('database.default', $name);
        $this->db->setDefaultConnection($name);

        // Force the handle open now so a bad credential fails here, during
        // resolution, rather than halfway through a controller.
        $this->db->connection($name)->getPdo();

        $this->connectedUuid = $context->uuid;
    }

    public function release(): void
    {
        if ($this->connectedUuid === null) {
            return;
        }

        $name = $this->connectionName();

        // Drop the PDO handle. A pooled worker must not carry an open
        // connection to tenant A into a job for tenant B.
        $this->db->purge($name);

        // Restore the entry rather than nulling it, so the connection is left
        // exactly as it was found. Nulling it would also destroy the template
        // when template and tenant connection are the same key.
        $this->config->set("database.connections.{$name}", $this->originalTenantConfig);

        if ($this->originalDefault !== null) {
            $this->config->set('database.default', $this->originalDefault);
            $this->db->setDefaultConnection($this->originalDefault);
        }

        $this->connectedUuid = null;
        $this->originalDefault = null;
    }
}
