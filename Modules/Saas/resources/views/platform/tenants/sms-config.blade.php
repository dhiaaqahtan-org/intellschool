@extends('saas::platform.layouts.app')

@section('title', 'SMS Configuration — ' . $tenant->display_name)

@section('content')
<div class="content-header flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">
                &larr; Back to {{ $tenant->display_name }}
            </a>
            <h1 class="page-title mb-0">SMS Gateway Configuration</h1>
        </div>
        <p class="page-description mt-1">Configure Twilio, Msg91, or Custom HTTP SMS Gateway credentials and parameters for <strong>{{ $tenant->display_name }}</strong> (<code>{{ $tenant->slug }}</code>).</p>
    </div>

    <div>
        <form method="POST" action="{{ route('saas.platform.tenants.sms-config.test', $tenant) }}" class="flex items-center gap-2">
            @csrf
            <input type="text" name="test_number" class="form-control form-control-sm text-sm font-mono w-48 bg-slate-900 text-slate-100" value="{{ $smsConfig['test_number'] ?? '' }}" placeholder="Mobile with country code" required>
            <button type="submit" class="btn btn-secondary btn-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Test SMS
            </button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('saas.platform.tenants.sms-config.update', $tenant) }}" class="space-y-6">
    @csrf

    {{-- Driver & Sender Details --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                SMS Gateway Driver & Sender Identity
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">SMS Driver</label>
                <select name="driver" class="form-select text-sm bg-slate-900 text-slate-100 w-full" required>
                    <option value="custom" {{ (old('driver', $smsConfig['driver'] ?? 'custom') === 'custom') ? 'selected' : '' }}>Custom HTTP API Gateway</option>
                    <option value="twilio" {{ (old('driver', $smsConfig['driver'] ?? '') === 'twilio') ? 'selected' : '' }}>Twilio SMS</option>
                    <option value="msg91" {{ (old('driver', $smsConfig['driver'] ?? '') === 'msg91') ? 'selected' : '' }}>Msg91 SMS</option>
                </select>
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Sender ID / Header</label>
                <input type="text" name="sender_id" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('sender_id', $smsConfig['sender_id'] ?? '') }}" placeholder="e.g. SCHOOL">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Number Prefix</label>
                <input type="text" name="number_prefix" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('number_prefix', $smsConfig['number_prefix'] ?? '') }}" placeholder="e.g. +967 or +966">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Default Test Mobile Number</label>
                <input type="text" name="test_number" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('test_number', $smsConfig['test_number'] ?? '') }}" placeholder="e.g. +967770000000">
            </div>
        </div>
    </div>

    {{-- Twilio & Msg91 Credentials --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Twilio & Msg91 API Credentials
            </h2>
        </div>
        <div class="card-body p-6 grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-800/40">
            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">API Key / Account SID</label>
                <input type="text" name="api_key" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('api_key', $smsConfig['api_key'] ?? '') }}">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">API Secret / Auth Token</label>
                <input type="password" name="api_secret" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('api_secret', $smsConfig['api_secret'] ?? '') }}">
            </div>

            <div>
                <label class="form-label font-semibold text-slate-200 block mb-1">Test Template ID (Msg91)</label>
                <input type="text" name="test_template_id" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('test_template_id', $smsConfig['test_template_id'] ?? '') }}">
            </div>
        </div>
    </div>

    {{-- Custom Gateway Settings --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                Custom HTTP SMS Gateway Configuration
            </h2>
        </div>
        <div class="card-body p-6 space-y-6 bg-slate-800/40">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-3">
                    <label class="form-label font-semibold text-slate-200 block mb-1">API URL Endpoint</label>
                    <input type="url" name="api_url" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('api_url', $smsConfig['api_url'] ?? '') }}" placeholder="https://api.sms-provider.com/send">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">HTTP Method</label>
                    <select name="api_method" class="form-select text-sm bg-slate-900 text-slate-100 w-full">
                        <option value="GET" {{ (old('api_method', $smsConfig['api_method'] ?? 'GET') === 'GET') ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ (old('api_method', $smsConfig['api_method'] ?? '') === 'POST') ? 'selected' : '' }}>POST</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Sender ID Parameter</label>
                    <input type="text" name="sender_id_param" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('sender_id_param', $smsConfig['sender_id_param'] ?? 'sender') }}" placeholder="sender">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Receiver Mobile Parameter</label>
                    <input type="text" name="receiver_param" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('receiver_param', $smsConfig['receiver_param'] ?? 'to') }}" placeholder="to">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Message Content Parameter</label>
                    <input type="text" name="message_param" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('message_param', $smsConfig['message_param'] ?? 'message') }}" placeholder="message">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Template ID Parameter</label>
                    <input type="text" name="template_id_param" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('template_id_param', $smsConfig['template_id_param'] ?? '') }}" placeholder="template_id">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Additional Query/Body Parameters</label>
                    <textarea name="additional_params" class="form-control text-sm font-mono bg-slate-900 text-slate-100" rows="3" placeholder="api_key=secretkey&type=text">{{ old('additional_params', is_array($smsConfig['additional_params'] ?? null) ? json_encode($smsConfig['additional_params']) : ($smsConfig['additional_params'] ?? '')) }}</textarea>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Custom API Headers</label>
                    <textarea name="api_headers" class="form-control text-sm font-mono bg-slate-900 text-slate-100" rows="3" placeholder="Authorization: Bearer token&#10;Content-Type: application/json">{{ old('api_headers', is_array($smsConfig['api_headers'] ?? null) ? json_encode($smsConfig['api_headers']) : ($smsConfig['api_headers'] ?? '')) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 flex justify-end p-4 rounded-xl bg-slate-900/90 border border-slate-700 shadow-2xl backdrop-blur">
        <button type="submit" class="btn btn-primary btn-lg px-8">
            Save SMS Configuration
        </button>
    </div>
</form>
@endsection
