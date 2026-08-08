<?php

namespace Modules\Saas\Http\Controllers\Platform;

use App\Models\Config\Config;
use App\Models\User;
use App\Notifications\TestNotificationWithoutQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

class MailConfigController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant
    ) {
    }

    /**
     * Display SaaS global mail configuration overview & bulk operations.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['domains'])
            ->orderBy('display_name')
            ->get();

        $tenantMailConfigs = [];

        foreach ($tenants as $tenant) {
            try {
                $mailConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
                    $config = Config::query()->where('name', 'mail')->first();
                    return $config?->value ?? [];
                });
                $tenantMailConfigs[$tenant->uuid] = $mailConfig;
            } catch (\Throwable $e) {
                $tenantMailConfigs[$tenant->uuid] = [];
            }
        }

        return view('saas::platform.mail-config.index', compact('tenants', 'tenantMailConfigs'));
    }

    /**
     * Display mail configuration settings for a specific school tenant.
     */
    public function showTenantMailConfig(Tenant $tenant): View
    {
        $tenant->load(['domains', 'database']);

        $mailConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
            $config = Config::query()->where('name', 'mail')->first();
            return $config?->value ?? [];
        });

        return view('saas::platform.tenants.mail-config', compact('tenant', 'mailConfig'));
    }

    /**
     * Update mail configuration settings for a specific school tenant.
     */
    public function updateTenantMailConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'in:log,smtp,mailgun,ses,postmark'],
            'from_name' => ['required', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'numeric'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,TLS,SSL'],
            'mailgun_domain' => ['nullable', 'string', 'max:255'],
            'mailgun_secret' => ['nullable', 'string', 'max:255'],
            'mailgun_endpoint' => ['nullable', 'string', 'max:255'],
        ]);

        $this->currentTenant->runFor($tenant->toContext(''), function () use ($validated) {
            $config = Config::firstOrCreate(['name' => 'mail', 'team_id' => null]);
            $currentValue = $config->value ?? [];

            $mergedValue = array_merge($currentValue, [
                'driver' => $validated['driver'],
                'from_name' => $validated['from_name'],
                'from_address' => $validated['from_address'],
                'smtp_host' => $validated['smtp_host'] ?? null,
                'smtp_port' => $validated['smtp_port'] ?? null,
                'smtp_username' => $validated['smtp_username'] ?? null,
                'smtp_password' => $validated['smtp_password'] ?? null,
                'smtp_encryption' => $validated['smtp_encryption'] ?? null,
                'mailgun_domain' => $validated['mailgun_domain'] ?? null,
                'mailgun_secret' => $validated['mailgun_secret'] ?? null,
                'mailgun_endpoint' => $validated['mailgun_endpoint'] ?? null,
            ]);

            $config->value = $mergedValue;
            $config->save();

            cache()->forget('query_config_list_all');
        });

        return redirect()
            ->route('saas.platform.tenants.mail-config.index', $tenant)
            ->with('success', "Mail configuration for '{$tenant->display_name}' has been updated.");
    }

    /**
     * Send a test notification using the tenant's mail settings.
     */
    public function testTenantMailConfig(Tenant $tenant): RedirectResponse
    {
        try {
            $this->currentTenant->runFor($tenant->toContext(''), function () {
                $user = User::query()->first();
                if ($user) {
                    $user->notify(new TestNotificationWithoutQueue($user->name));
                }
            });

            return redirect()
                ->route('saas.platform.tenants.mail-config.index', $tenant)
                ->with('success', "Test email successfully queued/sent for '{$tenant->display_name}'.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('saas.platform.tenants.mail-config.index', $tenant)
                ->with('error', "Mail test failed: {$e->getMessage()}");
        }
    }

    /**
     * Execute bulk mail configuration updates across all or selected tenants.
     */
    public function bulkUpdateMailConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'in:log,smtp,mailgun'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_address' => ['nullable', 'email', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'numeric'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,TLS,SSL'],
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
                    $config = Config::firstOrCreate(['name' => 'mail', 'team_id' => null]);
                    $currentValue = $config->value ?? [];

                    $currentValue['driver'] = $validated['driver'];
                    if (! empty($validated['from_name'])) {
                        $currentValue['from_name'] = $validated['from_name'];
                    }
                    if (! empty($validated['from_address'])) {
                        $currentValue['from_address'] = $validated['from_address'];
                    }
                    if (! empty($validated['smtp_host'])) {
                        $currentValue['smtp_host'] = $validated['smtp_host'];
                    }
                    if (! empty($validated['smtp_port'])) {
                        $currentValue['smtp_port'] = $validated['smtp_port'];
                    }
                    if (! empty($validated['smtp_username'])) {
                        $currentValue['smtp_username'] = $validated['smtp_username'];
                    }
                    if (! empty($validated['smtp_password'])) {
                        $currentValue['smtp_password'] = $validated['smtp_password'];
                    }
                    if (! empty($validated['smtp_encryption'])) {
                        $currentValue['smtp_encryption'] = $validated['smtp_encryption'];
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
            ->route('saas.platform.mail-config.index')
            ->with('success', "Bulk mail configuration update applied to {$updatedCount} school tenant(s).");
    }
}
