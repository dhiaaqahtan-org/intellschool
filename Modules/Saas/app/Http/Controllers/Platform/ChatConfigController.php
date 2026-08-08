<?php

namespace Modules\Saas\Http\Controllers\Platform;

use App\Actions\Config\TestPusherConnection;
use App\Models\Config\Config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

class ChatConfigController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant
    ) {
    }

    /**
     * Display SaaS global Chat & Pusher configuration overview & bulk operations.
     */
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['domains'])
            ->orderBy('display_name')
            ->get();

        $tenantChatConfigs = [];

        foreach ($tenants as $tenant) {
            try {
                $chatConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
                    $chat = Config::query()->where('name', 'chat')->first()?->value ?? [];
                    $notification = Config::query()->where('name', 'notification')->first()?->value ?? [];
                    return array_merge($chat, $notification);
                });
                $tenantChatConfigs[$tenant->uuid] = $chatConfig;
            } catch (\Throwable $e) {
                $tenantChatConfigs[$tenant->uuid] = [];
            }
        }

        return view('saas::platform.chat-config.index', compact('tenants', 'tenantChatConfigs'));
    }

    /**
     * Display Chat configuration settings for a specific school tenant.
     */
    public function showTenantChatConfig(Tenant $tenant): View
    {
        $tenant->load(['domains', 'database']);

        $chatConfig = $this->currentTenant->runFor($tenant->toContext(''), function () {
            $chat = Config::query()->where('name', 'chat')->first()?->value ?? [];
            $notification = Config::query()->where('name', 'notification')->first()?->value ?? [];
            return array_merge($chat, $notification);
        });

        return view('saas::platform.tenants.chat-config', compact('tenant', 'chatConfig'));
    }

    /**
     * Update Chat & Pusher configuration settings for a specific school tenant.
     */
    public function updateTenantChatConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'enable_chat' => ['nullable', 'boolean'],
            'enable_pusher_notification' => ['nullable', 'boolean'],
            'pusher_app_id' => ['nullable', 'string', 'max:255'],
            'pusher_app_key' => ['nullable', 'string', 'max:255'],
            'pusher_app_secret' => ['nullable', 'string', 'max:255'],
            'pusher_app_cluster' => ['nullable', 'string', 'max:255'],
        ]);

        $enableChat = (bool) ($validated['enable_chat'] ?? false);
        $enablePusher = (bool) ($validated['enable_pusher_notification'] ?? false);

        $this->currentTenant->runFor($tenant->toContext(''), function () use ($validated, $enableChat, $enablePusher) {
            // Update chat config
            $chatConfig = Config::firstOrCreate(['name' => 'chat', 'team_id' => null]);
            $chatVal = $chatConfig->value ?? [];
            $chatVal['enable_chat'] = $enableChat;
            $chatConfig->value = $chatVal;
            $chatConfig->save();

            // Update notification/pusher config
            $notifConfig = Config::firstOrCreate(['name' => 'notification', 'team_id' => null]);
            $notifVal = $notifConfig->value ?? [];
            $notifVal['enable_pusher_notification'] = $enablePusher;
            $notifVal['pusher_app_id'] = $validated['pusher_app_id'] ?? null;
            $notifVal['pusher_app_key'] = $validated['pusher_app_key'] ?? null;
            $notifVal['pusher_app_secret'] = $validated['pusher_app_secret'] ?? null;
            $notifVal['pusher_app_cluster'] = $validated['pusher_app_cluster'] ?? null;
            $notifConfig->value = $notifVal;
            $notifConfig->save();

            cache()->forget('query_config_list_all');
        });

        return redirect()
            ->route('saas.platform.tenants.chat-config.index', $tenant)
            ->with('success', "Chat & Pusher configuration for '{$tenant->display_name}' has been updated.");
    }

    /**
     * Send a test Pusher notification using the tenant's configuration.
     */
    public function testTenantChatConfig(Request $request, Tenant $tenant): RedirectResponse
    {
        try {
            $this->currentTenant->runFor($tenant->toContext(''), function () {
                (new TestPusherConnection)->execute();
            });

            return redirect()
                ->route('saas.platform.tenants.chat-config.index', $tenant)
                ->with('success', "Test Pusher broadcast sent successfully for tenant '{$tenant->display_name}'.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('saas.platform.tenants.chat-config.index', $tenant)
                ->with('error', "Pusher test broadcast failed: {$e->getMessage()}");
        }
    }

    /**
     * Execute bulk Chat & Pusher configuration updates across all or selected tenants.
     */
    public function bulkUpdateChatConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enable_chat' => ['nullable', 'boolean'],
            'enable_pusher_notification' => ['nullable', 'boolean'],
            'pusher_app_key' => ['nullable', 'string', 'max:255'],
            'pusher_app_cluster' => ['nullable', 'string', 'max:255'],
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
                    if (isset($validated['enable_chat'])) {
                        $chatConfig = Config::firstOrCreate(['name' => 'chat', 'team_id' => null]);
                        $chatVal = $chatConfig->value ?? [];
                        $chatVal['enable_chat'] = (bool) $validated['enable_chat'];
                        $chatConfig->value = $chatVal;
                        $chatConfig->save();
                    }

                    if (isset($validated['enable_pusher_notification']) || ! empty($validated['pusher_app_key']) || ! empty($validated['pusher_app_cluster'])) {
                        $notifConfig = Config::firstOrCreate(['name' => 'notification', 'team_id' => null]);
                        $notifVal = $notifConfig->value ?? [];
                        if (isset($validated['enable_pusher_notification'])) {
                            $notifVal['enable_pusher_notification'] = (bool) $validated['enable_pusher_notification'];
                        }
                        if (! empty($validated['pusher_app_key'])) {
                            $notifVal['pusher_app_key'] = $validated['pusher_app_key'];
                        }
                        if (! empty($validated['pusher_app_cluster'])) {
                            $notifVal['pusher_app_cluster'] = $validated['pusher_app_cluster'];
                        }
                        $notifConfig->value = $notifVal;
                        $notifConfig->save();
                    }

                    cache()->forget('query_config_list_all');
                });

                $updatedCount++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('saas.platform.chat-config.index')
            ->with('success', "Bulk Chat configuration update applied to {$updatedCount} school tenant(s).");
    }
}
