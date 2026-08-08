@extends('saas::platform.layouts.app')

@section('title', 'SMS Gateway Configuration')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Tenant SMS Gateway Configurations</h1>
        <p class="page-description">Manage SMS Gateway drivers (Twilio, Msg91, Custom HTTP API), sender IDs, number prefixes, and bulk SMS configuration across all school tenants.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <div class="card shadow-sm mb-6">
            <div class="card-header flex justify-between items-center">
                <h2 class="card-title">Tenant SMS Settings Status</h2>
                <span class="badge badge-info">{{ count($tenants) }} Tenants Registered</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>School Tenant</th>
                                <th>SMS Driver</th>
                                <th>Sender ID</th>
                                <th>Test Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $t)
                                @php
                                    $cfg = $tenantSMSConfigs[$t->uuid] ?? [];
                                    $driver = strtoupper($cfg['driver'] ?? 'CUSTOM');
                                    $senderId = $cfg['sender_id'] ?? 'Not set';
                                    $testNum = $cfg['test_number'] ?? '-';
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="text-slate-100 block">{{ $t->display_name }}</strong>
                                        <code class="text-xs text-slate-400">{{ $t->slug }}</code>
                                    </td>
                                    <td>
                                        @if($driver === 'TWILIO')
                                            <span class="badge badge-info">Twilio</span>
                                        @elseif($driver === 'MSG91')
                                            <span class="badge badge-warning">Msg91</span>
                                        @elseif($driver === 'CUSTOM')
                                            <span class="badge badge-success">Custom HTTP API</span>
                                        @else
                                            <span class="badge badge-gray">{{ $driver }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-xs font-mono text-slate-200">{{ $senderId }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-mono text-slate-400">{{ $testNum }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('saas.platform.tenants.sms-config.index', $t) }}" class="btn btn-xs btn-primary">
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
                <h2 class="card-title">Bulk Driver & Gateway Update</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-4">Bulk update SMS gateway settings across all active school tenants:</p>
                
                <form method="POST" action="{{ route('saas.platform.sms-config.bulk') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="form-label text-xs">SMS Gateway Driver</label>
                        <select name="driver" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="custom">Custom HTTP API</option>
                            <option value="twilio">Twilio</option>
                            <option value="msg91">Msg91</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Sender ID (Header)</label>
                        <input type="text" name="sender_id" class="form-control text-sm" placeholder="e.g. SCHOOL">
                    </div>

                    <div>
                        <label class="form-label text-xs">Country Number Prefix</label>
                        <input type="text" name="number_prefix" class="form-control text-sm" placeholder="e.g. +967 or +966">
                    </div>

                    <div>
                        <label class="form-label text-xs">API Key</label>
                        <input type="text" name="api_key" class="form-control text-sm">
                    </div>

                    <div>
                        <label class="form-label text-xs">API Secret / Auth Token</label>
                        <input type="password" name="api_secret" class="form-control text-sm">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full" onclick="return confirm('Apply these SMS gateway settings to ALL active school tenants?')">
                        Apply SMS Settings to All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
