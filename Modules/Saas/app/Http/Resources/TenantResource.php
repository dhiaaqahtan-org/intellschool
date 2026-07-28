<?php

namespace Modules\Saas\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $provisioningState = $this->provisioning_state;

        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'legal_name' => $this->legal_name,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'provisioning_state' => $provisioningState instanceof \BackedEnum
                ? $provisioningState->value
                : $provisioningState,
            'tier' => $this->tier,
            'region' => $this->region,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'primary_domain' => $this->whenLoaded(
                'domains',
                fn () => $this->primaryDomain()?->hostname,
            ),
            'domains' => $this->whenLoaded('domains', fn () => $this->domains->map(fn ($domain) => [
                'uuid' => $domain->uuid,
                'hostname' => $domain->hostname,
                'type' => $domain->type,
                'is_primary' => (bool) $domain->is_primary,
                'verified_at' => $domain->verified_at?->toIso8601String(),
                'tls_status' => $domain->tls_status,
            ])->values()),
            'database' => $this->whenLoaded('database', fn () => $this->database === null ? null : [
                'cluster' => $this->database->cluster,
                'health_status' => $this->database->health_status,
                'schema_version' => $this->database->schema_version,
                'app_version' => $this->database->app_version,
                'last_migrated_at' => $this->database->last_migrated_at?->toIso8601String(),
            ]),
            'owners' => $this->whenLoaded('owners', fn () => $this->owners->map(fn ($owner) => [
                'uuid' => $owner->uuid,
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => $owner->role,
                'status' => $owner->status,
            ])->values()),
            'subscription' => $this->whenLoaded(
                'subscription',
                fn () => $this->subscription ? new SubscriptionResource($this->subscription) : null,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
