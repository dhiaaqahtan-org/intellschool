@extends('saas::platform.layouts.app')

@section('title', 'System Configuration — ' . $tenant->display_name)

@section('content')
<div class="content-header flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">
                &larr; Back to {{ $tenant->display_name }}
            </a>
            <h1 class="page-title mb-0">System Configuration</h1>
        </div>
        <p class="page-description mt-1">Manage IP Security, App Footer Credits, Maintenance Mode, and Developer Support for <strong>{{ $tenant->display_name }}</strong> (<code>{{ $tenant->slug }}</code>).</p>
    </div>
</div>

<form method="POST" action="{{ route('saas.platform.tenants.system-config.update', $tenant) }}" class="space-y-6">
    @csrf

    {{-- Security & IP Controls --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Security & IP Access Control (عناوين IP)
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">
                    Allowed IP Addresses (عناوين IP المسموح بها)
                </label>
                <p class="text-xs text-slate-400 mb-2">Comma or line separated IPs permitted to access the application.</p>
                <textarea name="whitelist_ips" class="form-control text-sm font-mono bg-slate-900 text-slate-100" rows="4" placeholder="e.g. 192.168.1.1, 10.0.0.1">{{ old('whitelist_ips', $sysConfig['whitelist_ips'] ?? '') }}</textarea>
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">
                    Blocked IP Addresses (عناوين IP المحظورة)
                </label>
                <p class="text-xs text-slate-400 mb-2">Comma or line separated IPs blocked from accessing the application.</p>
                <textarea name="blacklist_ips" class="form-control text-sm font-mono bg-slate-900 text-slate-100" rows="4" placeholder="e.g. 192.168.1.100">{{ old('blacklist_ips', $sysConfig['blacklist_ips'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- App Branding & Footer --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                App Branding & Footer (حقوق التطبيق والتذييل)
            </h2>
        </div>
        <div class="card-body p-6 space-y-4 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">
                    Footer Credit (حقوق التذييل)
                </label>
                <p class="text-xs text-slate-400 mb-2">Displayed in the footer of all school tenant pages.</p>
                <input type="text" name="footer_credit" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('footer_credit', $sysConfig['footer_credit'] ?? 'Designed with ❤️ by IntellSchool') }}" placeholder="Designed with ❤️ by IntellSchool">
            </div>

            <div class="pt-2">
                <label class="relative inline-flex items-center cursor-pointer gap-3">
                    <input type="checkbox" name="show_version_number" value="1" class="sr-only peer" {{ !empty($sysConfig['show_version_number']) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <div>
                        <span class="text-sm font-semibold text-slate-200 block">Show Version Number (إظهار رقم الإصدار)</span>
                        <span class="text-xs text-slate-400">Display application version number in footer or settings.</span>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Maintenance Mode --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Maintenance Mode (وضع الصيانة)
            </h2>
        </div>
        <div class="card-body p-6 space-y-4 bg-slate-800/40">
            <div>
                <label class="relative inline-flex items-center cursor-pointer gap-3">
                    <input type="checkbox" name="enable_maintenance_mode" value="1" class="sr-only peer" {{ !empty($sysConfig['enable_maintenance_mode']) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                    <div>
                        <span class="text-sm font-semibold text-slate-200 block">Enable Maintenance Mode (تفعيل وضع الصيانة)</span>
                        <span class="text-xs text-slate-400">Regular users will be blocked from logging in. Only system administrators can access during maintenance.</span>
                    </div>
                </label>
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">
                    Maintenance Message (رسالة وضع الصيانة)
                </label>
                <textarea name="maintenance_mode_message" class="form-control text-sm bg-slate-900 text-slate-100" rows="3" placeholder="Application is currently under scheduled maintenance. Please try again later.">{{ old('maintenance_mode_message', $sysConfig['maintenance_mode_message'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Developer Support --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Developer Support (تفعيل دعم المطوّر)
            </h2>
        </div>
        <div class="card-body p-6 bg-slate-800/40">
            <label class="relative inline-flex items-center cursor-pointer gap-3">
                <input type="checkbox" name="enable_author_support" value="1" class="sr-only peer" {{ !empty($sysConfig['enable_author_support']) ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                <div>
                    <span class="text-sm font-semibold text-slate-200 block">Enable Developer Support Access (تفعيل دعم المطوّر)</span>
                    <span class="text-xs text-slate-400">Allows generating temporary developer support tokens for troubleshooting without sharing admin passwords.</span>
                </div>
            </label>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 flex justify-end p-4 rounded-xl bg-slate-900/90 border border-slate-700 shadow-2xl backdrop-blur">
        <button type="submit" class="btn btn-primary btn-lg px-8">
            Save System Configuration
        </button>
    </div>
</form>
@endsection
