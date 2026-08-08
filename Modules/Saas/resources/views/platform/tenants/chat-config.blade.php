@extends('saas::platform.layouts.app')

@section('title', 'Chat Configuration — ' . $tenant->display_name)

@section('content')
<div class="content-header flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">
                &larr; Back to {{ $tenant->display_name }}
            </a>
            <h1 class="page-title mb-0">Chat & Pusher Configuration</h1>
        </div>
        <p class="page-description mt-1">Configure Chat module enablement and Pusher real-time broadcasting credentials for <strong>{{ $tenant->display_name }}</strong> (<code>{{ $tenant->slug }}</code>).</p>
    </div>

    <div>
        <form method="POST" action="{{ route('saas.platform.tenants.chat-config.test', $tenant) }}" class="inline">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test Pusher Connection
            </button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('saas.platform.tenants.chat-config.update', $tenant) }}" class="space-y-6">
    @csrf

    {{-- Chat Module Settings --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Chat Module Settings
            </h2>
        </div>
        <div class="card-body p-6 bg-slate-800/40">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="enable_chat" value="0">
                <input type="checkbox" name="enable_chat" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_chat', $chatConfig['enable_chat'] ?? true)) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold text-slate-100 block">Enable Instant Chat Module</span>
                    <span class="text-xs text-slate-400">Allow users to send real-time instant messages within the school portal.</span>
                </div>
            </label>
        </div>
    </div>

    {{-- Pusher Real-Time Settings --}}
    <div class="card shadow-sm border border-slate-700">
        <div class="card-header bg-slate-800 p-4 border-b border-slate-700">
            <h2 class="card-title text-base text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Pusher Real-Time Credentials
            </h2>
        </div>
        <div class="card-body p-6 space-y-6 bg-slate-800/40">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="enable_pusher_notification" value="0">
                <input type="checkbox" name="enable_pusher_notification" value="1" class="form-checkbox text-indigo-600 rounded" {{ !empty(old('enable_pusher_notification', $chatConfig['enable_pusher_notification'] ?? false)) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold text-slate-100 block">Enable Pusher Real-Time Broadcasting</span>
                    <span class="text-xs text-slate-400">Broadcast live chat updates, notifications, and alerts via Pusher web-sockets.</span>
                </div>
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Pusher App ID</label>
                    <input type="text" name="pusher_app_id" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('pusher_app_id', $chatConfig['pusher_app_id'] ?? '') }}" placeholder="e.g. 1234567">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Pusher App Key</label>
                    <input type="text" name="pusher_app_key" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('pusher_app_key', $chatConfig['pusher_app_key'] ?? '') }}" placeholder="e.g. key-xxxxxx">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Pusher App Secret</label>
                    <input type="password" name="pusher_app_secret" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('pusher_app_secret', $chatConfig['pusher_app_secret'] ?? '') }}" placeholder="e.g. secret-xxxxxx">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-200 block mb-1">Pusher App Cluster</label>
                    <input type="text" name="pusher_app_cluster" class="form-control text-sm bg-slate-900 text-slate-100" value="{{ old('pusher_app_cluster', $chatConfig['pusher_app_cluster'] ?? 'mt1') }}" placeholder="e.g. mt1 or eu">
                </div>
            </div>
        </div>
    </div>

    <div class="sticky bottom-4 z-10 flex justify-end p-4 rounded-xl bg-slate-900/90 border border-slate-700 shadow-2xl backdrop-blur">
        <button type="submit" class="btn btn-primary btn-lg px-8">
            Save Chat Configuration
        </button>
    </div>
</form>
@endsection
