@extends('saas::platform.layouts.app')

@section('title', 'System Configuration')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Tenant System Configurations</h1>
        <p class="page-description">Manage IP Security (Whitelist/Blacklist), Application Footer Credit, Version Display, Maintenance Mode, and Developer Support across all school tenants.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <div class="card shadow-sm mb-6">
            <div class="card-header flex justify-between items-center">
                <h2 class="card-title">Tenant System Settings Status</h2>
                <span class="badge badge-info">{{ count($tenants) }} Tenants Registered</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>School Tenant</th>
                                <th>Maintenance Mode</th>
                                <th>IP Restrictions</th>
                                <th>Dev Support</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $t)
                                @php
                                    $cfg = $tenantSystemConfigs[$t->uuid] ?? [];
                                    $isMaintenance = !empty($cfg['enable_maintenance_mode']);
                                    $hasWhitelist = !empty($cfg['whitelist_ips']);
                                    $hasBlacklist = !empty($cfg['blacklist_ips']);
                                    $devSupport = !empty($cfg['enable_author_support']);
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="text-slate-100 block">{{ $t->display_name }}</strong>
                                        <code class="text-xs text-slate-400">{{ $t->slug }}</code>
                                    </td>
                                    <td>
                                        @if($isMaintenance)
                                            <span class="badge badge-danger">Active Maintenance</span>
                                        @else
                                            <span class="badge badge-success">Online (Normal)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            @if($hasWhitelist)
                                                <span class="badge badge-warning text-xs" title="Allowed IPs Configured">Whitelist Set</span>
                                            @endif
                                            @if($hasBlacklist)
                                                <span class="badge badge-danger text-xs" title="Blocked IPs Configured">Blacklist Set</span>
                                            @endif
                                            @if(!$hasWhitelist && !$hasBlacklist)
                                                <span class="text-xs text-slate-500">Unrestricted</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($devSupport)
                                            <span class="badge badge-success text-xs">Enabled</span>
                                        @else
                                            <span class="badge badge-secondary text-xs">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('saas.platform.tenants.system-config.index', $t) }}" class="btn btn-xs btn-primary">
                                            Configure
                                        </a>
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
        <div class="card shadow-sm mb-6">
            <div class="card-header">
                <h2 class="card-title">Bulk Maintenance Control</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-4">Toggle maintenance mode across all school tenants simultaneously:</p>
                
                <form method="POST" action="{{ route('saas.platform.system-config.bulk') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="setting_type" value="maintenance_mode">
                    
                    <div>
                        <label class="form-label text-xs">Maintenance Message</label>
                        <textarea name="maintenance_mode_message" class="form-control text-sm" rows="2" placeholder="System under scheduled maintenance. Please check back soon."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" name="maintenance_mode" value="1" class="btn btn-danger btn-sm flex-1" onclick="return confirm('Enable maintenance mode across ALL active tenants?')">
                            Enable Maintenance
                        </button>
                        <button type="submit" name="maintenance_mode" value="0" class="btn btn-success btn-sm flex-1">
                            Disable Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title">Bulk Footer Credit Update</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-3">Update footer text for all school tenants:</p>
                
                <form method="POST" action="{{ route('saas.platform.system-config.bulk') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="setting_type" value="footer_credit">
                    
                    <div>
                        <input type="text" name="footer_credit" class="form-control text-sm" value="Designed with ❤️ by IntellSchool" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full">
                        Apply Footer Credit to All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
