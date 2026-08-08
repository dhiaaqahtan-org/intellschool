@extends('saas::platform.layouts.app')

@section('title', 'Feature Configuration — ' . $tenant->display_name)

@section('content')
<div class="content-header flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">
                &larr; Back to {{ $tenant->display_name }}
            </a>
            <h1 class="page-title mb-0">Feature Configuration</h1>
        </div>
        <p class="page-description mt-1">Configure system feature toggles, online portals, and instructions for <strong>{{ $tenant->display_name }}</strong> (<code>{{ $tenant->slug }}</code>).</p>
    </div>
</div>

<form method="POST" action="{{ route('saas.platform.tenants.feature-config.update', $tenant) }}" class="space-y-6">
    @csrf

    {{-- Utility & System Features --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                System Utilities & Activities
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-slate-800/40">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="enable_todo" value="0">
                <input type="checkbox" name="enable_todo" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_todo', $featureConfig['enable_todo'] ?? true)) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold text-slate-100 block">To-Do Lists</span>
                    <span class="text-xs text-slate-400">Personal & shared todo tasks.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="enable_backup" value="0">
                <input type="checkbox" name="enable_backup" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_backup', $featureConfig['enable_backup'] ?? true)) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold text-slate-100 block">Database Backups</span>
                    <span class="text-xs text-slate-400">Database backup manager.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="enable_activity_log" value="0">
                <input type="checkbox" name="enable_activity_log" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_activity_log', $featureConfig['enable_activity_log'] ?? true)) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold text-slate-100 block">Activity Logs</span>
                    <span class="text-xs text-slate-400">Track user activity logs.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="enable_post" value="0">
                <input type="checkbox" name="enable_post" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_post', $featureConfig['enable_post'] ?? true)) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold text-slate-100 block">Announcements / Posts</span>
                    <span class="text-xs text-slate-400">Internal news & posts.</span>
                </div>
            </label>
        </div>
    </div>

    {{-- Online Admission & Registration --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Online Admissions & Student Registration
            </h2>
        </div>
        <div class="card-body p-6 space-y-6 bg-slate-800/40">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="enable_online_enquiry" value="0">
                    <input type="checkbox" name="enable_online_enquiry" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_online_enquiry', $featureConfig['enable_online_enquiry'] ?? true)) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-slate-100 block">Enable Online Admission Enquiry</span>
                        <span class="text-xs text-slate-400">Allow prospective parents/students to submit online enquiries.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="enable_online_registration" value="0">
                    <input type="checkbox" name="enable_online_registration" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_online_registration', $featureConfig['enable_online_registration'] ?? true)) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-slate-100 block">Enable Online Student Registration</span>
                        <span class="text-xs text-slate-400">Allow direct online student registration portal.</span>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Online Registration Layout Version</label>
                    <select name="online_registration_version" class="form-select text-sm bg-slate-900 text-slate-100 w-full">
                        <option value="default" {{ (old('online_registration_version', $featureConfig['online_registration_version'] ?? 'default') === 'default') ? 'selected' : '' }}>Default Full Form</option>
                        <option value="minimal" {{ (old('online_registration_version', $featureConfig['online_registration_version'] ?? '') === 'minimal') ? 'selected' : '' }}>Minimal Fast Form</option>
                    </select>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Mandatory File Upload Field</label>
                    <input type="text" name="online_registration_mandatory_upload_field" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('online_registration_mandatory_upload_field', $featureConfig['online_registration_mandatory_upload_field'] ?? '') }}" placeholder="e.g. birth_certificate">
                </div>
            </div>
        </div>
    </div>

    {{-- Public Portals & Verifications --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                Public Portals & Verification Services
            </h2>
        </div>
        <div class="card-body p-6 space-y-6 bg-slate-800/40">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="enable_guest_payment" value="0">
                    <input type="checkbox" name="enable_guest_payment" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_guest_payment', $featureConfig['enable_guest_payment'] ?? true)) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-slate-100 block">Guest Fees Payment</span>
                        <span class="text-xs text-slate-400">Public fee payment portal.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="enable_job_application" value="0">
                    <input type="checkbox" name="enable_job_application" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_job_application', $featureConfig['enable_job_application'] ?? true)) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-slate-100 block">Careers & Job Application</span>
                        <span class="text-xs text-slate-400">Public job recruitment portal.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="enable_transfer_certificate_verification" value="0">
                    <input type="checkbox" name="enable_transfer_certificate_verification" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_transfer_certificate_verification', $featureConfig['enable_transfer_certificate_verification'] ?? true)) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-slate-100 block">TC Verification</span>
                        <span class="text-xs text-slate-400">Public Transfer Certificate check.</span>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Online Registration Instructions</label>
                    <textarea name="online_registration_instruction" class="form-control text-sm bg-slate-900 text-slate-100" rows="3">{{ old('online_registration_instruction', $featureConfig['online_registration_instruction'] ?? '') }}</textarea>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Guest Payment Instructions</label>
                    <textarea name="guest_payment_instruction" class="form-control text-sm bg-slate-900 text-slate-100" rows="3">{{ old('guest_payment_instruction', $featureConfig['guest_payment_instruction'] ?? '') }}</textarea>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Job Application Instructions</label>
                    <textarea name="job_application_instruction" class="form-control text-sm bg-slate-900 text-slate-100" rows="3">{{ old('job_application_instruction', $featureConfig['job_application_instruction'] ?? '') }}</textarea>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Transfer Certificate Verification Instructions</label>
                    <textarea name="transfer_certificate_verification_instruction" class="form-control text-sm bg-slate-900 text-slate-100" rows="3">{{ old('transfer_certificate_verification_instruction', $featureConfig['transfer_certificate_verification_instruction'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 flex justify-end p-4 rounded-xl bg-slate-900/90 border border-slate-700 shadow-2xl backdrop-blur">
        <button type="submit" class="btn btn-primary btn-lg px-8">
            Save Feature Configuration
        </button>
    </div>
</form>
@endsection
