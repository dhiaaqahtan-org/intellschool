<?php

namespace Modules\Saas\Services;

use Modules\Saas\Models\Landlord\TenantDomain;

/**
 * Proves that a school actually controls a custom domain before we route it.
 *
 * WHY THIS GATE EXISTS — TenantDomain::isRoutable() treats a subdomain we issued
 * as trusted and a customer-owned domain as untrusted until `verified_at` is
 * set. Without a way to set it, a custom domain is added and then never routes,
 * which is the safe failure but not a working product.
 *
 * The reason it cannot simply be trusted on entry: DNS is the only thing that
 * decides which server answers for tamjeed.com, and anyone can point their DNS
 * at us. If adding a hostname were enough to claim it, one school could enter a
 * rival's domain — or their own registrar-expired one — and take delivery of
 * whatever traffic arrived. Requiring a TXT record only the domain's DNS owner
 * can publish is what makes the claim mean something.
 *
 * Verification is deliberately one-directional: this class can mark a domain
 * verified, never unverified. Revocation is removing the domain, which is an
 * audited action, rather than a background check that could silently take a
 * school offline because a DNS lookup failed once.
 */
class DomainVerifier
{
    /** @var callable(string): array<int, array<string, mixed>> */
    private $lookup;

    /**
     * @param  callable(string): array<int, array<string, mixed>>|null  $lookup
     *         DNS TXT resolver. Injectable so tests never touch real DNS —
     *         a test suite whose result depends on a network lookup is a test
     *         suite that fails for reasons unrelated to the code.
     */
    public function __construct(
        private readonly TenantResolver $resolver,
        ?callable $lookup = null,
    ) {
        $this->lookup = $lookup ?? static function (string $name): array {
            // Suppressed: a domain with no such record is the ordinary "not
            // verified yet" case, not an error worth a warning in the log.
            return @dns_get_record($name, DNS_TXT) ?: [];
        };
    }

    /**
     * The hostname the school must create a TXT record at. Shown in the admin
     * panel verbatim — an operator copies this, so it must be the whole name.
     */
    public function recordName(TenantDomain $domain): string
    {
        $prefix = (string) config('saas.domains.verification_prefix', '_saas-verify');

        return $prefix.'.'.$domain->hostname;
    }

    public function expectedValue(TenantDomain $domain): string
    {
        return (string) $domain->verification_token;
    }

    /**
     * @return array{verified: bool, reason: string}
     */
    public function verify(TenantDomain $domain): array
    {
        if ($domain->type === TenantDomain::TYPE_SUBDOMAIN) {
            return [
                'verified' => true,
                'reason' => 'Subdomains we issue are trusted without a DNS check.',
            ];
        }

        if ($domain->verified_at !== null) {
            return ['verified' => true, 'reason' => 'Already verified.'];
        }

        $token = $this->expectedValue($domain);

        if ($token === '') {
            // Fail closed. An empty expected value would otherwise match an
            // empty TXT record and verify a domain nobody proved they own.
            return [
                'verified' => false,
                'reason' => 'This domain has no verification token. Remove it and add it again.',
            ];
        }

        $name = $this->recordName($domain);
        $records = ($this->lookup)($name);

        foreach ($this->txtValues($records) as $value) {
            if (hash_equals($token, $value)) {
                $domain->forceFill(['verified_at' => now()])->save();

                // The host→tenant mapping is cached, and until now this host
                // resolved to nothing. Without this the school waits out the
                // TTL wondering why a verified domain still 404s.
                $this->resolver->forget($domain->hostname);

                return ['verified' => true, 'reason' => 'DNS record matched.'];
            }
        }

        return [
            'verified' => false,
            'reason' => $records === []
                ? "No TXT record found at {$name}. DNS changes can take up to an hour to propagate."
                : "A TXT record exists at {$name} but none of its values match the expected token.",
        ];
    }

    /**
     * PHP returns a TXT record's payload as `txt`, and separately as `entries`
     * when the value was split across strings. Long tokens are exactly what
     * gets split, so reading only `txt` would fail on the records most likely
     * to be chunked.
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, string>
     */
    private function txtValues(array $records): array
    {
        $values = [];

        foreach ($records as $record) {
            if (isset($record['txt']) && is_string($record['txt'])) {
                $values[] = trim($record['txt']);
            }

            if (isset($record['entries']) && is_array($record['entries'])) {
                $values[] = trim(implode('', array_filter($record['entries'], 'is_string')));
            }
        }

        return array_values(array_unique(array_filter($values, static fn ($v) => $v !== '')));
    }
}
