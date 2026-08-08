@extends('saas::platform.layouts.app')

@section('title', 'Mail Configuration')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Tenant Mail Server Configurations</h1>
        <p class="page-description">Manage SMTP, Mailgun, SES, and Log mail drivers, sender credentials, and bulk configuration across all school tenants.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <div class="card shadow-sm mb-6">
            <div class="card-header flex justify-between items-center">
                <h2 class="card-title">Tenant Mail Settings Status</h2>
                <span class="badge badge-info">{{ count($tenants) }} Tenants Registered</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>School Tenant</th>
                                <th>Mail Driver</th>
                                <th>Sender Email</th>
                                <th>SMTP Host</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $t)
                                @php
                                    $cfg = $tenantMailConfigs[$t->uuid] ?? [];
                                    $driver = strtoupper($cfg['driver'] ?? 'LOG');
                                    $fromAddr = $cfg['from_address'] ?? 'Not set';
                                    $smtpHost = $cfg['smtp_host'] ?? '-';
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="text-slate-100 block">{{ $t->display_name }}</strong>
                                        <code class="text-xs text-slate-400">{{ $t->slug }}</code>
                                    </td>
                                    <td>
                                        @if($driver === 'SMTP')
                                            <span class="badge badge-success">SMTP</span>
                                        @elseif($driver === 'MAILGUN')
                                            <span class="badge badge-info">Mailgun</span>
                                        @elseif($driver === 'LOG')
                                            <span class="badge badge-secondary">Log (Local)</span>
                                        @else
                                            <span class="badge badge-gray">{{ $driver }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-xs font-mono text-slate-200">{{ $fromAddr }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-mono text-slate-400">{{ $smtpHost }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('saas.platform.tenants.mail-config.index', $t) }}" class="btn btn-xs btn-primary">
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
                <h2 class="card-title">Bulk Driver & SMTP Update</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-300 mb-4">Bulk update mail server settings across all active school tenants:</p>
                
                <form method="POST" action="{{ route('saas.platform.mail-config.bulk') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="form-label text-xs">Mail Driver</label>
                        <select name="driver" class="form-select text-sm bg-slate-900 text-slate-100">
                            <option value="log">Log Driver (Local Testing)</option>
                            <option value="smtp">SMTP Driver</option>
                            <option value="mailgun">Mailgun Driver</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs">Sender Address (From Email)</label>
                        <input type="email" name="from_address" class="form-control text-sm" placeholder="noreply@intellschool.com">
                    </div>

                    <div>
                        <label class="form-label text-xs">Sender Name (From Name)</label>
                        <input type="text" name="from_name" class="form-control text-sm" placeholder="School Administration">
                    </div>

                    <div>
                        <label class="form-label text-xs">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control text-sm" placeholder="smtp.mailtrap.io">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="form-label text-xs">Port</label>
                            <input type="number" name="smtp_port" class="form-control text-sm" placeholder="587">
                        </div>
                        <div>
                            <label class="form-label text-xs">Encryption</label>
                            <select name="smtp_encryption" class="form-select text-sm bg-slate-900 text-slate-100">
                                <option value="">None</option>
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control text-sm">
                    </div>

                    <div>
                        <label class="form-label text-xs">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control text-sm">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full" onclick="return confirm('Apply these mail settings to ALL active school tenants?')">
                        Apply Mail Settings to All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
