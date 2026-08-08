@extends('saas::platform.layouts.app')

@section('title', 'Feature Toggles Configuration')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Tenant Feature Toggles Configuration</h1>
        <p class="page-description">Manage system feature toggles (Online Registration, Online Enquiry, Backups, Activity Logs, Guest Payments, Job Applications, TC Verification) across all school tenants.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <div class="card shadow-sm mb-6">
            <div class="card-header flex justify-between items-center">
                <h2 class="card-title">Tenant Feature Configuration Status</h2>
                <span class="badge badge-info">{{ count($tenants) }} Tenants Registered</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>School Tenant</th>
                                <th>Online Reg.</th>
                                <th>Online Enquiry</th>
                                <th>Backups & Logs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $t)
                                @php
                                    $cfg = $tenantFeatureConfigs[$t->uuid] ?? [];
                                    $onlineReg = !empty($cfg['enable_online_registration']);
                                    $onlineEnquiry = !empty($cfg['enable_online_enquiry']);
                                    $backups = !empty($cfg['enable_backup']);
                                    $logs = !empty($cfg['enable_activity_log']);
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="text-slate-100 block">{{ $t->display_name }}</strong>
                                        <code class="text-xs text-slate-400">{{ $t->slug }}</code>
                                    </td>
                                    <td>
                                        @if($onlineReg)
                                            <span class="badge badge-success">Enabled</span>
                                        @else
                                            <span class="badge badge-gray">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($onlineEnquiry)
                                            <span class="badge badge-info">Enabled</span>
                                        @else
                                            <span class="badge badge-gray">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-xs text-slate-300 block">Backups: {{ $backups ? 'ON' : 'OFF' }}</span>
                                        <span class="text-xs text-slate-400">Activity Logs: {{ $logs ? 'ON' : 'OFF' }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('saas.platform.tenants.feature-config.index', $t) }}" class="btn btn-xs btn-primary">
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
                <h2 class="card-title">Bulk Feature Toggles Update</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-4">Bulk enable or disable system features across active school tenants:</p>
                
                <form method="POST" action="{{ route('saas.platform.feature-config.bulk') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="form-label text-xs">Online Student Registration</label>
                        <select name="enable_online_registration" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="1">Enable Online Registration</option>
                            <option value="0">Disable Online Registration</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Online Admission Enquiry</label>
                        <select name="enable_online_enquiry" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="1">Enable Online Enquiry</option>
                            <option value="0">Disable Online Enquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Database Backups</label>
                        <select name="enable_backup" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="1">Enable Backups</option>
                            <option value="0">Disable Backups</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Activity Logging</label>
                        <select name="enable_activity_log" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="1">Enable Activity Logging</option>
                            <option value="0">Disable Activity Logging</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full" onclick="return confirm('Apply these Feature toggles to ALL active school tenants?')">
                        Apply Feature Settings to All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
