<?php

namespace Modules\Saas\Domain\Tenancy;

/**
 * Normalises a Host header into a canonical form for exact lookup.
 *
 * Host-header handling is where multi-tenant systems get broken into, so this
 * class does exactly one thing and does it strictly:
 *
 *  - lowercase, because DNS is case-insensitive but string comparison is not;
 *  - strip the port, which a client controls and which is not part of identity;
 *  - strip one trailing dot (the fully-qualified form `example.com.`);
 *  - convert IDN to punycode so that two visually identical hostnames cannot
 *    resolve to different tenants (a homograph attack);
 *  - reject anything that is not a plausible hostname.
 *
 * Lookups against the result must be EXACT. No suffix matching, no wildcards,
 * no `str_contains`. `evil-example.com` must never match `example.com`.
 */
final class HostNormalizer
{
    /**
     * @return string|null Canonical host, or null when the input cannot be a hostname.
     */
    public static function normalize(?string $host): ?string
    {
        if ($host === null) {
            return null;
        }

        $host = trim($host);

        if ($host === '') {
            return null;
        }

        // A Host header may legitimately carry a port; identity ignores it.
        // IPv6 literals arrive bracketed, e.g. [::1]:8000.
        if (str_starts_with($host, '[')) {
            $close = strpos($host, ']');
            $host = $close === false ? $host : substr($host, 0, $close + 1);
        } elseif (str_contains($host, ':')) {
            $host = strstr($host, ':', true) ?: $host;
        }

        $host = rtrim($host, '.');
        $host = mb_strtolower($host, 'UTF-8');

        if ($host === '') {
            return null;
        }

        // Homograph defence: fold Unicode to punycode before comparing.
        if (! preg_match('/^[\x20-\x7E]*$/', $host) && function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if ($ascii === false) {
                return null;
            }

            $host = $ascii;
        }

        return self::isPlausible($host) ? $host : null;
    }

    /**
     * Structural check only — says nothing about whether the host is known.
     * A hostname that passes here still has to match a verified record.
     */
    private static function isPlausible(string $host): bool
    {
        if (strlen($host) > 253) {
            return false;
        }

        // Bracketed IPv6 literal.
        if (str_starts_with($host, '[')) {
            return (bool) filter_var(trim($host, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        // Labels: alphanumeric and hyphen, not starting or ending with a hyphen.
        foreach (explode('.', $host) as $label) {
            if ($label === '' || strlen($label) > 63) {
                return false;
            }

            if (! preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $label)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extracts the tenant subdomain from a host, given the platform suffix.
     *
     * Returns null unless the host is exactly one label deeper than the
     * suffix. `a.b.product.example` does not yield tenant `a.b` — nested
     * subdomains are not a supported addressing scheme, and silently
     * accepting them widens the attack surface for no benefit.
     */
    public static function extractSubdomain(string $normalizedHost, ?string $suffix): ?string
    {
        if (empty($suffix)) {
            return null;
        }

        // Strip the leading dot BEFORE normalising. The suffix is conventionally
        // written ".product.example"; normalising that first produces an empty
        // leading label, which isPlausible() correctly rejects, and the whole
        // lookup would silently return null for every host.
        $suffix = self::normalize(ltrim($suffix, '.')) ?? '';

        if ($suffix === '' || $normalizedHost === $suffix) {
            return null;
        }

        if (! str_ends_with($normalizedHost, '.'.$suffix)) {
            return null;
        }

        $label = substr($normalizedHost, 0, -(strlen($suffix) + 1));

        return str_contains($label, '.') ? null : ($label ?: null);
    }
}
