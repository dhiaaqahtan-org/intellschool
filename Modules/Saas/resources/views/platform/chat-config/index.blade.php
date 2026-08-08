@extends('saas::platform.layouts.app')

@section('title', 'Chat & Pusher Configuration')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Tenant Chat & Pusher Configurations</h1>
        <p class="page-description">Manage real-time Chat enablement, Pusher credentials (App ID, Key, Secret, Cluster), and bulk Chat configuration across all school tenants.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <div class="card shadow-sm mb-6">
            <div class="card-header flex justify-between items-center">
                <h2 class="card-title">Tenant Chat & Broadcast Status</h2>
                <span class="badge badge-info">{{ count($tenants) }} Tenants Registered</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>School Tenant</th>
                                <th>Chat Module</th>
                                <th>Pusher Broadcast</th>
                                <th>Pusher Key / Cluster</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $t)
                                @php
                                    $cfg = $tenantChatConfigs[$t->uuid] ?? [];
                                    $chatEnabled = !empty($cfg['enable_chat']);
                                    $pusherEnabled = !empty($cfg['enable_pusher_notification']);
                                    $pusherKey = $cfg['pusher_app_key'] ?? 'Not set';
                                    $cluster = $cfg['pusher_app_cluster'] ?? '-';
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="text-slate-100 block">{{ $t->display_name }}</strong>
                                        <code class="text-xs text-slate-400">{{ $t->slug }}</code>
                                    </td>
                                    <td>
                                        @if($chatEnabled)
                                            <span class="badge badge-success">Enabled</span>
                                        @else
                                            <span class="badge badge-gray">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pusherEnabled)
                                            <span class="badge badge-info">Active</span>
                                        @else
                                            <span class="badge badge-gray">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-xs font-mono text-slate-200 block">{{ $pusherKey }}</span>
                                        <span class="text-xs text-slate-400">Cluster: {{ $cluster }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('saas.platform.tenants.chat-config.index', $t) }}" class="btn btn-xs btn-primary">
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
                <h2 class="card-title">Bulk Chat & Pusher Update</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-4">Bulk update Chat module & Pusher credentials across active school tenants:</p>
                
                <form method="POST" action="{{ route('saas.platform.chat-config.bulk') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="form-label text-xs">Chat Module Status</label>
                        <select name="enable_chat" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="1">Enable Chat Module</option>
                            <option value="0">Disable Chat Module</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Pusher Real-Time Status</label>
                        <select name="enable_pusher_notification" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="1">Enable Pusher Real-Time</option>
                            <option value="0">Disable Pusher Real-Time</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Global Pusher App Key</label>
                        <input type="text" name="pusher_app_key" class="form-control text-sm" placeholder="e.g. app-key">
                    </div>

                    <div>
                        <label class="form-label text-xs">Pusher Cluster</label>
                        <input type="text" name="pusher_app_cluster" class="form-control text-sm" placeholder="e.g. mt1 or eu">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full" onclick="return confirm('Apply these Chat & Pusher settings to ALL active school tenants?')">
                        Apply Chat Settings to All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
