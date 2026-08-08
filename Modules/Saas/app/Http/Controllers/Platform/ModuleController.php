<?php

namespace Modules\Saas\Http\Controllers\Platform;

use App\Models\Config\Config;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Models\Landlord\Tenant;

class ModuleController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant
    ) {
    }

    /**
     * Display SaaS global module management overview & bulk operations.
     */
    public function index(): View
    {
        $rawModules = Arr::getVar('modules') ?? [];

        $modules = collect($rawModules)->map(function ($module) {
            return [
                'name' => $module['name'],
                'label' => trans($module['label'] ?? $module['name']),
                'children' => collect($module['children'] ?? [])->map(function ($child) {
                    return [
                        'name' => $child['name'],
                        'label' => trans($child['label'] ?? $child['name']),
                    ];
                })->all(),
            ];
        })->all();

        $activeTenants = Tenant::query()
            ->with(['domains'])
            ->orderBy('display_name')
            ->get();

        return view('saas::platform.modules.index', compact('modules', 'activeTenants'));
    }

    /**
     * Display module configuration for a specific school tenant.
     */
    public function showTenantModules(Tenant $tenant): View
    {
        $tenant->load(['domains', 'database']);

        $modulesData = $this->currentTenant->runFor($tenant->toContext(''), function () {
            $team = Team::query()->first();
            $teamId = $team?->id;

            $moduleConfig = Config::query()
                ->where('name', 'module')
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->first();

            $rawModules = Arr::getVar('modules') ?? [];

            $modules = collect($rawModules)->map(function ($module) use ($moduleConfig) {
                $systemModule = collect($moduleConfig->value ?? [])->firstWhere('name', $module['name']) ?? [];

                $visibility = $systemModule['visibility'] ?? true;
                $position = $systemModule['position'] ?? 0;

                return [
                    'name' => $module['name'],
                    'label' => trans($module['label'] ?? $module['name']),
                    'position' => (int) $position,
                    'visibility' => (bool) $visibility,
                    'children' => collect($module['children'] ?? [])->map(function ($child) use ($systemModule) {
                        $systemChild = collect($systemModule['children'] ?? [])->firstWhere('name', $child['name']) ?? [];
                        $visibility = $systemChild['visibility'] ?? true;

                        return [
                            'name' => $child['name'],
                            'label' => trans($child['label'] ?? $child['name']),
                            'visibility' => (bool) $visibility,
                        ];
                    })->all(),
                ];
            })->sortBy('position')->values()->all();

            return compact('modules', 'teamId');
        });

        $modules = $modulesData['modules'];

        return view('saas::platform.tenants.modules', compact('tenant', 'modules'));
    }

    /**
     * Update module configuration for a specific school tenant.
     */
    public function updateTenantModules(Request $request, Tenant $tenant): RedirectResponse
    {
        $inputModules = collect($request->input('modules', []));

        $this->currentTenant->runFor($tenant->toContext(''), function () use ($inputModules) {
            $team = Team::query()->first();
            $teamId = $team?->id;

            $moduleConfig = Config::query()
                ->where('name', 'module')
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->first();

            $rawModules = Arr::getVar('modules') ?? [];

            $modulesToSave = collect($rawModules)->map(function ($module) use ($inputModules, $moduleConfig) {
                $systemModule = collect($moduleConfig->value ?? [])->firstWhere('name', $module['name']) ?? [];
                $inputModule = $inputModules->firstWhere('name', $module['name']) ?? [];

                $index = $inputModules->search(fn ($item) => ($item['name'] ?? null) === $module['name']);
                if ($index === false) {
                    $index = 0;
                }

                $visibility = array_key_exists('visibility', $inputModule)
                    ? (bool) $inputModule['visibility']
                    : ($systemModule['visibility'] ?? true);

                $inputChildren = collect($inputModule['children'] ?? []);

                $childrenToSave = collect($module['children'] ?? [])->map(function ($child) use ($inputChildren, $systemModule) {
                    $systemChild = collect($systemModule['children'] ?? [])->firstWhere('name', $child['name']) ?? [];
                    $inputChild = $inputChildren->firstWhere('name', $child['name']) ?? [];

                    $visibility = array_key_exists('visibility', $inputChild)
                        ? (bool) $inputChild['visibility']
                        : ($systemChild['visibility'] ?? true);

                    return [
                        'name' => $child['name'],
                        'visibility' => (bool) $visibility,
                    ];
                })->all();

                return [
                    'name' => $module['name'],
                    'position' => $index,
                    'visibility' => (bool) $visibility,
                    'children' => $childrenToSave,
                ];
            })->all();

            Config::updateOrCreate(
                [
                    'team_id' => $teamId,
                    'name' => 'module',
                ],
                [
                    'value' => $modulesToSave,
                ]
            );
        });

        return redirect()
            ->route('saas.platform.tenants.modules.index', $tenant)
            ->with('success', "Module configuration for '{$tenant->display_name}' has been updated.");
    }

    /**
     * Execute bulk module updates across all or selected tenants.
     */
    public function bulkUpdateModules(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string'],
            'action' => ['required', 'in:enable,disable'],
            'tenant_uuids' => ['nullable', 'array'],
            'tenant_uuids.*' => ['string', 'exists:saas_tenants,uuid'],
        ]);

        $moduleName = $validated['module_name'];
        $enable = $validated['action'] === 'enable';
        $targetUuids = $validated['tenant_uuids'] ?? null;

        $tenantsQuery = Tenant::query();
        if (! empty($targetUuids)) {
            $tenantsQuery->whereIn('uuid', $targetUuids);
        }
        $tenants = $tenantsQuery->get();

        $updatedCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->currentTenant->runFor($tenant->toContext(''), function () use ($moduleName, $enable) {
                    $team = Team::query()->first();
                    $teamId = $team?->id;

                    $moduleConfig = Config::query()
                        ->where('name', 'module')
                        ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                        ->first();

                    $rawModules = Arr::getVar('modules') ?? [];

                    $modulesToSave = collect($rawModules)->map(function ($module) use ($moduleName, $enable, $moduleConfig) {
                        $systemModule = collect($moduleConfig->value ?? [])->firstWhere('name', $module['name']) ?? [];

                        $visibility = ($module['name'] === $moduleName)
                            ? $enable
                            : ($systemModule['visibility'] ?? true);

                        $children = collect($module['children'] ?? [])->map(function ($child) use ($systemModule) {
                            $systemChild = collect($systemModule['children'] ?? [])->firstWhere('name', $child['name']) ?? [];

                            return [
                                'name' => $child['name'],
                                'visibility' => (bool) ($systemChild['visibility'] ?? true),
                            ];
                        })->all();

                        return [
                            'name' => $module['name'],
                            'position' => $systemModule['position'] ?? 0,
                            'visibility' => (bool) $visibility,
                            'children' => $children,
                        ];
                    })->all();

                    Config::updateOrCreate(
                        [
                            'team_id' => $teamId,
                            'name' => 'module',
                        ],
                        [
                            'value' => $modulesToSave,
                        ]
                    );
                });

                $updatedCount++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $actionWord = $enable ? 'enabled' : 'disabled';

        return redirect()
            ->route('saas.platform.modules.index')
            ->with('success', "Module '{$moduleName}' successfully {$actionWord} across {$updatedCount} tenant(s).");
    }
}
