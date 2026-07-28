<?php

namespace Modules\Saas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Saas\Mail\OwnerInvitationMail;
use Modules\Saas\Models\Landlord\Invitation;
use Modules\Saas\Models\Landlord\Tenant;
use Modules\Saas\Models\Landlord\TenantOwner;

class SendOwnerInvitationJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $uniqueFor = 900;

    public function __construct(public readonly string $tenantUuid)
    {
        $this->onQueue((string) config('saas.leads.queue', 'notifications'));
    }

    public function uniqueId(): string
    {
        return $this->tenantUuid;
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [30, 120, 300, 900];
    }

    public function handle(): void
    {
        if (! config('saas.signup.owner_invitations_enabled', false)) {
            return;
        }

        $tenant = Tenant::query()->where('uuid', $this->tenantUuid)->firstOrFail();
        $owner = TenantOwner::query()
            ->where('tenant_uuid', $tenant->uuid)
            ->where('role', 'owner')
            ->first();

        if ($owner === null || ! $owner->isPending() || $owner->tenant_user_uuid === null) {
            return;
        }

        Invitation::query()
            ->forTenant($tenant->uuid)
            ->forEmail($owner->email)
            ->valid()
            ->update(['revoked_at' => now()]);

        ['token' => $token] = Invitation::createWithToken([
            'tenant_uuid' => $tenant->uuid,
            'email' => $owner->email,
            'name' => $owner->name,
            'role' => 'owner',
            'invited_by_type' => 'platform',
            'expires_at' => now()->addDays((int) config('saas.signup.invitation_expiry_days', 7)),
        ]);

        $locale = $tenant->locale ?: config('app.fallback_locale', 'en');
        $url = route('saas.marketing.invitation.show', [
            'locale' => $locale,
            'token' => $token,
        ]);

        Mail::to($owner->email)
            ->locale($locale)
            ->send(new OwnerInvitationMail(
                ownerName: $owner->name ?: $owner->email,
                schoolName: $tenant->display_name,
                invitationUrl: $url,
                expiresInDays: (int) config('saas.signup.invitation_expiry_days', 7),
            ));
    }
}
