<?php

namespace Modules\Saas\Http\Controllers\Platform;

use App\Models\Config\Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

class SystemConfigController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant
    ) {
    }

    /**
     * Display SaaS global system configuration overview and bulk operations.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['domains'])
            ->orderBy('display_name')
            ->get();

        $tenantSystemConfigs = [];

        foreach ($tenants as $tenant) {
            try {
                $sysConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
                    $config = Config::query()->where('name', 'system')->first();
                    return $config?->value ?? [];
                });
                $tenantSystemConfigs[$tenant->uuid] = $sysConfig;
            } catch (\Throwable $e) {
                $tenantSystemConfigs[$tenant->uuid] = [];
            }
        }

        return view('saas::platform.system-config.index', compact('tenants', 'tenantSystemConfigs'));
    }

    /**
     * Display system configuration settings for a specific school tenant.
     */
    public function showTenantSystemConfig(Tenant $tenant): View
    {
        $tenant->load(['domains', 'database']);

        $sysConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
            $config = Config::query()->where('name', 'system')->first();
            return $config?->value ?? [];
        });

        return view('saas::platform.tenants.system-config', compact('tenant', 'sysConfig'));
    }

    /**
     * Update system configuration settings for a specific school tenant.
     */
    public function updateTenantSystemConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'whitelist_ips' => ['nullable', 'string'],
            'blacklist_ips' => ['nullable', 'string'],
            'footer_credit' => ['nullable', 'string', 'max:500'],
            'show_version_number' => ['sometimes', 'boolean'],
            'enable_maintenance_mode' => ['sometimes', 'boolean'],
            'maintenance_mode_message' => ['nullable', 'string', 'max:500'],
            'enable_author_support' => ['sometimes', 'boolean'],
        ]);

        $this->currentTenant->runFor($tenant->toContext(''), function () use ($validated, $request) {
            $config = Config::firstOrCreate(['name' => 'system', 'team_id' => null]);
            $currentValue = $config->value ?? [];

            $mergedValue = array_merge($currentValue, [
                'whitelist_ips' => $validated['whitelist_ips'] ?? null,
                'blacklist_ips' => $validated['blacklist_ips'] ?? null,
                'footer_credit' => $validated['footer_credit'] ?? null,
                'show_version_number' => $request->boolean('show_version_number'),
                'enable_maintenance_mode' => $request->boolean('enable_maintenance_mode'),
                'maintenance_mode_message' => $validated['maintenance_mode_message'] ?? null,
                'enable_author_support' => $request->boolean('enable_author_support'),
            ]);

            $config->value = $mergedValue;
            $config->save();

            cache()->forget('query_config_list_all');
        });

        return redirect()
            ->route('saas.platform.tenants.system-config.index', $tenant)
            ->with('success', "System configuration for '{$tenant->display_name}' has been updated.");
    }

    /**
     * Execute bulk system configuration updates across all or selected tenants.
     */
    public function bulkUpdateSystemConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'setting_type' => ['required', 'in:maintenance_mode,footer_credit,developer_support'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_mode_message' => ['nullable', 'string', 'max:500'],
            'footer_credit' => ['nullable', 'string', 'max:500'],
            'developer_support' => ['nullable', 'boolean'],
            'tenant_uuids' => ['nullable', 'array'],
            'tenant_uuids.*' => ['string', 'exists:saas_tenants,uuid'],
        ]);

        $settingType = $validated['setting_type'];
        $targetUuids = $validated['tenant_uuids'] ?? null;

        $tenantsQuery = Tenant::query();
        if (! empty($targetUuids)) {
            $tenantsQuery->whereIn('uuid', $targetUuids);
        }
        $tenants = $tenantsQuery->get();

        $updatedCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->currentTenant->runFor($tenant->toContext(''), function () use ($settingType, $validated, $request) {
                    $config = Config::firstOrCreate(['name' => 'system', 'team_id' => null]);
                    $currentValue = $config->value ?? [];

                    if ($settingType === 'maintenance_mode') {
                        $currentValue['enable_maintenance_mode'] = $request->boolean('maintenance_mode');
                        if ($request->has('maintenance_mode_message')) {
                            $currentValue['maintenance_mode_message'] = $validated['maintenance_mode_message'];
                        }
                    } elseif ($settingType === 'footer_credit') {
                        $currentValue['footer_credit'] = $validated['footer_credit'];
                    } elseif ($settingType === 'developer_support') {
                        $currentValue['enable_author_support'] = $request->boolean('developer_support');
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
            ->route('saas.platform.system-config.index')
            ->with('success', "Bulk system configuration update applied to {$updatedCount} school tenant(s).");
    }
}
