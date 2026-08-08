@extends('saas::platform.layouts.app')

@section('title', 'Configure Modules — ' . $tenant->display_name)

@section('content')
<div class="content-header flex justify-between items-center mb-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-outline">
                &larr; Back to {{ $tenant->display_name }}
            </a>
            <h1 class="page-title mb-0">Module Configuration</h1>
        </div>
        <p class="page-description mt-1">Configure module visibility and sub-features for <strong>{{ $tenant->display_name }}</strong> (<code>{{ $tenant->slug }}</code>).</p>
    </div>
</div>

<form method="POST" action="{{ route('saas.platform.tenants.modules.update', $tenant) }}">
    @csrf

    <div class="space-y-6 mb-8">
        @foreach($modules as $index => $mod)
            <div class="card shadow-sm border border-slate-700 bg-slate-800/80">
                <div class="card-header flex items-center justify-between bg-slate-800 p-4 border-b border-slate-700">
                    <div class="flex items-center gap-4">
                        <input type="hidden" name="modules[{{ $index }}][name]" value="{{ $mod['name'] }}">

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="modules[{{ $index }}][visibility]"
                                   value="1"
                                   class="sr-only peer"
                                   {{ $mod['visibility'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>

                        <div>
                            <h3 class="text-base font-semibold text-white mb-0">{{ $mod['label'] }}</h3>
                            <code class="text-xs text-slate-400">{{ $mod['name'] }}</code>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Position:</span>
                        <input type="number"
                               name="modules[{{ $index }}][position]"
                               value="{{ $mod['position'] }}"
                               class="form-control form-control-sm w-16 text-center bg-slate-900 border-slate-700 text-white"
                               min="0" max="100">
                    </div>
                </div>

                @if(!empty($mod['children']))
                    <div class="card-body p-4 bg-slate-900/40">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Sub-features & Capabilities</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($mod['children'] as $childIndex => $child)
                                <div class="flex items-center justify-between p-2.5 rounded border border-slate-700/60 bg-slate-800/50">
                                    <span class="text-sm font-medium text-slate-200">{{ $child['label'] }}</span>
                                    <input type="hidden" name="modules[{{ $index }}][children][{{ $childIndex }}][name]" value="{{ $child['name'] }}">
                                    
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               name="modules[{{ $index }}][children][{{ $childIndex }}][visibility]"
                                               value="1"
                                               class="sr-only peer"
                                               {{ $child['visibility'] ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="sticky bottom-4 z-10 flex justify-end p-4 rounded-xl bg-slate-900/90 border border-slate-700 shadow-2xl backdrop-blur">
        <button type="submit" class="btn btn-primary btn-lg px-8">
            Save Module Configuration
        </button>
    </div>
</form>
@endsection
