@extends('saas::platform.layouts.app')

@section('title', 'Mail Configuration — ' . $tenant->display_name)

@section('content')
<div class="content-header flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">
                &larr; Back to {{ $tenant->display_name }}
            </a>
            <h1 class="page-title mb-0">Mail Configuration</h1>
        </div>
        <p class="page-description mt-1">Configure SMTP, Mailgun, or Log driver credentials and sender details for <strong>{{ $tenant->display_name }}</strong> (<code>{{ $tenant->slug }}</code>).</p>
    </div>

    <div>
        <form method="POST" action="{{ route('saas.platform.tenants.mail-config.test', $tenant) }}" class="inline">
            @csrf
            <button type="submit" class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Test Mail Connection
            </button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('saas.platform.tenants.mail-config.update', $tenant) }}" class="space-y-6">
    @csrf

    {{-- Driver & Sender Identity --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Mail Driver & Sender Identity
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Mail Driver</label>
                <select name="driver" class="form-select text-sm bg-slate-900 text-slate-100 w-full" required>
                    <option value="log" {{ (old('driver', $mailConfig['driver'] ?? 'log') === 'log') ? 'selected' : '' }}>Log Driver (Local Testing)</option>
                    <option value="smtp" {{ (old('driver', $mailConfig['driver'] ?? '') === 'smtp') ? 'selected' : '' }}>SMTP Driver</option>
                    <option value="mailgun" {{ (old('driver', $mailConfig['driver'] ?? '') === 'mailgun') ? 'selected' : '' }}>Mailgun API</option>
                    <option value="ses" {{ (old('driver', $mailConfig['driver'] ?? '') === 'ses') ? 'selected' : '' }}>Amazon SES</option>
                    <option value="postmark" {{ (old('driver', $mailConfig['driver'] ?? '') === 'postmark') ? 'selected' : '' }}>Postmark</option>
                </select>
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">From Name (Sender Name)</label>
                <input type="text" name="from_name" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('from_name', $mailConfig['from_name'] ?? 'School Administration') }}" required>
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">From Address (Sender Email)</label>
                <input type="email" name="from_address" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('from_address', $mailConfig['from_address'] ?? '') }}" required placeholder="noreply@school.com">
            </div>
        </div>
    </div>

    {{-- SMTP Settings --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
                SMTP Configuration
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('smtp_host', $mailConfig['smtp_host'] ?? '') }}" placeholder="smtp.mailtrap.io">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">SMTP Port</label>
                <input type="number" name="smtp_port" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('smtp_port', $mailConfig['smtp_port'] ?? '587') }}" placeholder="587">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Encryption Protocol</label>
                <select name="smtp_encryption" class="form-select text-sm bg-slate-900 text-slate-100 w-full">
                    <option value="" {{ empty($mailConfig['smtp_encryption']) ? 'selected' : '' }}>None</option>
                    <option value="tls" {{ (strtolower(old('smtp_encryption', $mailConfig['smtp_encryption'] ?? '')) === 'tls') ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ (strtolower(old('smtp_encryption', $mailConfig['smtp_encryption'] ?? '')) === 'ssl') ? 'selected' : '' }}>SSL</option>
                </select>
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">SMTP Username</label>
                <input type="text" name="smtp_username" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('smtp_username', $mailConfig['smtp_username'] ?? '') }}">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">SMTP Password</label>
                <input type="password" name="smtp_password" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('smtp_password', $mailConfig['smtp_password'] ?? '') }}">
            </div>
        </div>
    </div>

    {{-- Mailgun API Settings --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Mailgun API Settings
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Mailgun Domain</label>
                <input type="text" name="mailgun_domain" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('mailgun_domain', $mailConfig['mailgun_domain'] ?? '') }}" placeholder="mg.yourdomain.com">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Mailgun Secret</label>
                <input type="password" name="mailgun_secret" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('mailgun_secret', $mailConfig['mailgun_secret'] ?? '') }}" placeholder="key-xxxxxxxx">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Mailgun Endpoint</label>
                <input type="text" name="mailgun_endpoint" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('mailgun_endpoint', $mailConfig['mailgun_endpoint'] ?? 'api.mailgun.net') }}" placeholder="api.mailgun.net">
            </div>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 flex justify-end p-4 rounded-xl bg-slate-900/90 border border-slate-700 shadow-2xl backdrop-blur">
        <button type="submit" class="btn btn-primary btn-lg px-8">
            Save Mail Configuration
        </button>
    </div>
</form>
@endsection
