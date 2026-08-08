@extends('saas::platform.layouts.app')

@section('title', 'Module Management')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Platform Module Control</h1>
        <p class="page-description">Control module visibility, sub-feature permissions, and bulk enablement across all registered school tenants.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <div class="card shadow-sm">
            <div class="card-header flex justify-between items-center">
                <h2 class="card-title">Core System Modules</h2>
                <span class="badge badge-info">{{ count($modules) }} Modules Defined</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Module Name</th>
                                <th>Sub-features (Children)</th>
                                <th>Bulk Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $mod)
                                <tr>
                                    <td class="font-semibold text-slate-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                                            <span>{{ $mod['label'] }}</span>
                                        </div>
                                        <code class="text-xs text-slate-400 block mt-1">{{ $mod['name'] }}</code>
                                    </td>
                                    <td>
                                        @if(!empty($mod['children']))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($mod['children'] as $child)
                                                    <span class="badge badge-secondary text-xs">{{ $child['label'] }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-500">No sub-features</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('saas.platform.modules.bulk') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="module_name" value="{{ $mod['name'] }}">
                                                <input type="hidden" name="action" value="enable">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Enable module for all active tenants">
                                                    Enable All
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('saas.platform.modules.bulk') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="module_name" value="{{ $mod['name'] }}">
                                                <input type="hidden" name="action" value="disable">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Disable module for all active tenants" onclick="return confirm('Disable {{ $mod['label'] }} across all active tenants?')">
                                                    Disable All
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title">School Tenant Module Config</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-4">Select an active school tenant to configure its specific module visibility and feature order:</p>

                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($activeTenants as $t)
                        <a href="{{ route('saas.platform.tenants.modules.index', $t) }}" class="flex items-center justify-between p-3 rounded-lg border border-slate-700 hover:border-indigo-500 hover:bg-slate-800 transition">
                            <div>
                                <strong class="block text-slate-100 text-sm">{{ $t->display_name }}</strong>
                                <span class="text-xs text-slate-400 font-mono">{{ $t->slug }}</span>
                            </div>
                            <span class="btn btn-xs btn-primary">Configure</span>
                        </a>
                    @empty
                        <div class="text-center text-slate-400 py-6">No active tenants found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
