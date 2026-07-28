<?php

namespace Modules\Saas\Domain\Website;

/**
 * Central fail-closed gate for public commercial and product claims.
 */
final class ClaimGate
{
    public function pricing(): bool
    {
        return (bool) config('saas.claims.publish_pricing', false);
    }

    public function mobileGeneralAvailability(): bool
    {
        return (bool) config('saas.claims.publish_mobile_ga', false);
    }

    public function uptime(): bool
    {
        return (bool) config('saas.claims.publish_uptime', false);
    }

    public function certifications(): bool
    {
        return (bool) config('saas.claims.publish_certifications', false);
    }

    public function selfServiceSignup(): bool
    {
        return $this->pricing()
            && (bool) config('saas.signup.enabled', false)
            && (bool) config('saas.signup.owner_invitations_enabled', false);
    }
}
