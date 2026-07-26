<?php

namespace Modules\Saas\Contracts;

use Modules\Saas\Domain\Tenancy\TenantContext;

/**
 * Turns a tenant's secret reference into live database credentials.
 *
 * The landlord table stores a *reference* to a secret, never the secret
 * (plan §5.1). This seam is what lets that reference be an AWS Secrets Manager
 * ARN, a Vault path, or — in local development only — an env var name, without
 * the connection manager knowing the difference.
 */
interface TenantCredentialResolver
{
    /**
     * @return array{host: string, port: int, username: string, password: string}
     *
     * @throws \RuntimeException when the reference cannot be resolved. Must
     *         fail rather than return partial or default credentials, which
     *         would connect to the wrong database.
     */
    public function resolveFor(TenantContext $context): array;
}
