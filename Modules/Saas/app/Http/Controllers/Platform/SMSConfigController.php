<?php

namespace Modules\Saas\Http\Controllers\Platform;

use App\Actions\SendSMS;
use App\Models\Communication\Template;
use App\Models\Config\Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

class SMSConfigController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant
    ) {
    }

    /**
     * Display SaaS global SMS configuration overview & bulk operations.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['domains'])
            ->orderBy('display_name')
            ->get();

        $tenantSMSConfigs = [];

        foreach ($tenants as $tenant) {
            try {
                $smsConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
                    $config = Config::query()->where('name', 'sms')->first();
                    return $config?->value ?? [];
                });
                $tenantSMSConfigs[$tenant->uuid] = $smsConfig;
            } catch (\Throwable $e) {
                $tenantSMSConfigs[$tenant->uuid] = [];
            }
        }

        return view('saas::platform.sms-config.index', compact('tenants', 'tenantSMSConfigs'));
    }

    /**
     * Display SMS configuration settings for a specific school tenant.
     */
    public function showTenantSMSConfig(Tenant $tenant): View
    {
        $tenant->load(['domains', 'database']);

        $smsConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
            $config = Config::query()->where('name', 'sms')->first();
            return $config?->value ?? [];
        });

        return view('saas::platform.tenants.sms-config', compact('tenant', 'smsConfig'));
    }

    /**
     * Update SMS configuration settings for a specific school tenant.
     */
    public function updateTenantSMSConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'in:twilio,msg91,custom'],
            'sender_id' => ['nullable', 'string', 'max:255'],
            'test_number' => ['nullable', 'string', 'max:50'],
            'test_template_id' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'api_url' => ['nullable', 'string', 'max:500'],
            'api_method' => ['nullable', 'in:GET,POST'],
            'number_prefix' => ['nullable', 'string', 'max:10'],
            'sender_id_param' => ['nullable', 'string', 'max:255'],
            'receiver_param' => ['nullable', 'string', 'max:255'],
            'message_param' => ['nullable', 'string', 'max:255'],
            'template_id_param' => ['nullable', 'string', 'max:255'],
            'additional_params' => ['nullable', 'string'],
            'api_headers' => ['nullable', 'string'],
        ]);

        $this->currentTenant->runFor($tenant->toContext(''), function () use ($validated) {
            $config = Config::firstOrCreate(['name' => 'sms', 'team_id' => null]);
            $currentValue = $config->value ?? [];

            $mergedValue = array_merge($currentValue, [
                'driver' => $validated['driver'],
                'sender_id' => $validated['sender_id'] ?? null,
                'test_number' => $validated['test_number'] ?? null,
                'test_template_id' => $validated['test_template_id'] ?? null,
                'api_key' => $validated['api_key'] ?? null,
                'api_secret' => $validated['api_secret'] ?? null,
                'api_url' => $validated['api_url'] ?? null,
                'api_method' => $validated['api_method'] ?? 'GET',
                'number_prefix' => $validated['number_prefix'] ?? null,
                'sender_id_param' => $validated['sender_id_param'] ?? null,
                'receiver_param' => $validated['receiver_param'] ?? null,
                'message_param' => $validated['message_param'] ?? null,
                'template_id_param' => $validated['template_id_param'] ?? null,
                'additional_params' => $validated['additional_params'] ?? null,
                'api_headers' => $validated['api_headers'] ?? null,
            ]);

            $config->value = $mergedValue;
            $config->save();

            cache()->forget('query_config_list_all');
        });

        return redirect()
            ->route('saas.platform.tenants.sms-config.index', $tenant)
            ->with('success', "SMS Gateway configuration for '{$tenant->display_name}' has been updated.");
    }

    /**
     * Send a test SMS message using the tenant's SMS configuration.
     */
    public function testTenantSMSConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'test_number' => ['required', 'string', 'max:50'],
        ]);

        $testNumber = $validated['test_number'];

        try {
            $this->currentTenant->runFor($tenant->toContext(''), function () use ($testNumber) {
                $testSMSTemplate = Template::query()
                    ->where('code', 'test-sms')
                    ->first();

                $messageContent = $testSMSTemplate?->content ?? 'This is a test SMS message from IntellSchool SaaS Admin.';

                $params = [
                    'template_id' => $testSMSTemplate?->getMeta('template_id'),
                    'to' => $testNumber,
                    'variables' => [
                        'message' => $messageContent,
                    ],
                ];

                (new SendSMS)->execute($params);
            });

            return redirect()
                ->route('saas.platform.tenants.sms-config.index', $tenant)
                ->with('success', "Test SMS sent to '{$testNumber}' for tenant '{$tenant->display_name}'.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('saas.platform.tenants.sms-config.index', $tenant)
                ->with('error', "SMS test failed: {$e->getMessage()}");
        }
    }

    /**
     * Execute bulk SMS configuration updates across all or selected tenants.
     */
    public function bulkUpdateSMSConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'in:twilio,msg91,custom'],
            'sender_id' => ['nullable', 'string', 'max:255'],
            'number_prefix' => ['nullable', 'string', 'max:10'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
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
                    $config = Config::firstOrCreate(['name' => 'sms', 'team_id' => null]);
                    $currentValue = $config->value ?? [];

                    $currentValue['driver'] = $validated['driver'];
                    if (! empty($validated['sender_id'])) {
                        $currentValue['sender_id'] = $validated['sender_id'];
                    }
                    if (! empty($validated['number_prefix'])) {
                        $currentValue['number_prefix'] = $validated['number_prefix'];
                    }
                    if (! empty($validated['api_key'])) {
                        $currentValue['api_key'] = $validated['api_key'];
                    }
                    if (! empty($validated['api_secret'])) {
                        $currentValue['api_secret'] = $validated['api_secret'];
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
            ->route('saas.platform.sms-config.index')
            ->with('success', "Bulk SMS configuration update applied to {$updatedCount} school tenant(s).");
    }
}
