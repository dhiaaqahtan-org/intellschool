<?php

namespace Modules\Saas\Http\Controllers\Platform;

use App\Models\Config\Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

class FeatureConfigController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant
    ) {
    }

    /**
     * Display SaaS global Feature configuration overview & bulk operations.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['domains'])
            ->orderBy('display_name')
            ->get();

        $tenantFeatureConfigs = [];

        foreach ($tenants as $tenant) {
            try {
                $featureConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
                    $config = Config::query()->where('name', 'feature')->first();
                    return $config?->value ?? [];
                });
                $tenantFeatureConfigs[$tenant->uuid] = $featureConfig;
            } catch (\Throwable $e) {
                $tenantFeatureConfigs[$tenant->uuid] = [];
            }
        }

        return view('saas::platform.feature-config.index', compact('tenants', 'tenantFeatureConfigs'));
    }

    /**
     * Display Feature configuration settings for a specific school tenant.
     */
    public function showTenantFeatureConfig(Tenant $tenant): View
    {
        $tenant->load(['domains', 'database']);

        $featureConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
            $config = Config::query()->where('name', 'feature')->first();
            return $config?->value ?? [];
        });

        return view('saas::platform.tenants.feature-config', compact('tenant', 'featureConfig'));
    }

    /**
     * Update Feature configuration settings for a specific school tenant.
     */
    public function updateTenantFeatureConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'enable_todo' => ['nullable', 'boolean'],
            'enable_backup' => ['nullable', 'boolean'],
            'enable_activity_log' => ['nullable', 'boolean'],
            'enable_guest_payment' => ['nullable', 'boolean'],
            'enable_post' => ['nullable', 'boolean'],
            'guest_payment_instruction' => ['nullable', 'string', 'max:2000'],
            'enable_online_enquiry' => ['nullable', 'boolean'],
            'online_enquiry_instruction' => ['nullable', 'string', 'max:2000'],
            'enable_online_registration' => ['nullable', 'boolean'],
            'online_registration_instruction' => ['nullable', 'string', 'max:2000'],
            'online_registration_version' => ['nullable', 'in:default,minimal'],
            'online_registration_mandatory_upload_field' => ['nullable', 'string', 'max:255'],
            'enable_job_application' => ['nullable', 'boolean'],
            'job_application_instruction' => ['nullable', 'string', 'max:2000'],
            'enable_transfer_certificate_verification' => ['nullable', 'boolean'],
            'transfer_certificate_verification_instruction' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->currentTenant->runFor($tenant->toContext(''), function () use ($validated) {
            $config = Config::firstOrCreate(['name' => 'feature', 'team_id' => null]);
            $currentValue = $config->value ?? [];

            $mergedValue = array_merge($currentValue, [
                'enable_todo' => (bool) ($validated['enable_todo'] ?? false),
                'enable_backup' => (bool) ($validated['enable_backup'] ?? false),
                'enable_activity_log' => (bool) ($validated['enable_activity_log'] ?? false),
                'enable_guest_payment' => (bool) ($validated['enable_guest_payment'] ?? false),
                'enable_post' => (bool) ($validated['enable_post'] ?? false),
                'guest_payment_instruction' => clean($validated['guest_payment_instruction'] ?? ''),
                'enable_online_enquiry' => (bool) ($validated['enable_online_enquiry'] ?? false),
                'online_enquiry_instruction' => clean($validated['online_enquiry_instruction'] ?? ''),
                'enable_online_registration' => (bool) ($validated['enable_online_registration'] ?? false),
                'online_registration_instruction' => clean($validated['online_registration_instruction'] ?? ''),
                'online_registration_version' => $validated['online_registration_version'] ?? 'default',
                'online_registration_mandatory_upload_field' => $validated['online_registration_mandatory_upload_field'] ?? null,
                'enable_job_application' => (bool) ($validated['enable_job_application'] ?? false),
                'job_application_instruction' => clean($validated['job_application_instruction'] ?? ''),
                'enable_transfer_certificate_verification' => (bool) ($validated['enable_transfer_certificate_verification'] ?? false),
                'transfer_certificate_verification_instruction' => clean($validated['transfer_certificate_verification_instruction'] ?? ''),
            ]);

            $config->value = $mergedValue;
            $config->save();

            cache()->forget('query_config_list_all');
        });

        return redirect()
            ->route('saas.platform.tenants.feature-config.index', $tenant)
            ->with('success', "Feature configuration for '{$tenant->display_name}' has been updated.");
    }

    /**
     * Execute bulk Feature configuration updates across all or selected tenants.
     */
    public function bulkUpdateFeatureConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enable_online_registration' => ['nullable', 'boolean'],
            'enable_online_enquiry' => ['nullable', 'boolean'],
            'enable_backup' => ['nullable', 'boolean'],
            'enable_activity_log' => ['nullable', 'boolean'],
            'tenant_uuids' => ['nullable', 'array'],
            'tenant_uuids.*' => ['string', 'exists:saas_tenants,uuid'],
        ]);

        $targetUuids = $validated['tenant_uuids'] ?? null;

        $tenantsQuery = Tenant::query();
        if (! empty($targetUuids)) {
            $tenantsQuery->whereIn('uuid', $targetUuids);
        }
        $tenants = $tenantsQuery->get();

        $updatedCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->currentTenant->runFor($tenant->toContext(''), function () use ($validated) {
                    $config = Config::firstOrCreate(['name' => 'feature', 'team_id' => null]);
                    $currentValue = $config->value ?? [];

                    if (isset($validated['enable_online_registration'])) {
                        $currentValue['enable_online_registration'] = (bool) $validated['enable_online_registration'];
                    }
                    if (isset($validated['enable_online_enquiry'])) {
                        $currentValue['enable_online_enquiry'] = (bool) $validated['enable_online_enquiry'];
                    }
                    if (isset($validated['enable_backup'])) {
                        $currentValue['enable_backup'] = (bool) $validated['enable_backup'];
                    }
                    if (isset($validated['enable_activity_log'])) {
                        $currentValue['enable_activity_log'] = (bool) $validated['enable_activity_log'];
                    }

                    $config->value = $currentValue;
                    $config->save();

                    cache()->forget('query_config_list_all');
                });

                $updatedCount++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('saas.platform.feature-config.index')
            ->with('success', "Bulk Feature configuration update applied to {$updatedCount} school tenant(s).");
    }
}
