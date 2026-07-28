@extends('saas::platform.layouts.app')

@section('title', 'Audit Log')

@section('content')
    <div class="page-header">
        <h1>Audit Log</h1>
        <p>Append-only record of control-plane activity. Nothing here can be edited or deleted from the application.</p>
    </div>

    <div class="card">
        <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:end">
            <label>Tenant
                <select name="tenant" style="padding:.5rem;margin-top:.25rem">
                    <option value="">All tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->uuid }}" @selected(request('tenant') === $tenant->uuid)>
                            {{ $tenant->display_name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>Action
                <select name="action" style="padding:.5rem;margin-top:.25rem">
                    <option value="">All actions</option>
                    @foreach ($actions as $action => $total)
                        <option value="{{ $action }}" @selected(request('action') === $action)>
                            {{ $action }} ({{ $total }})
                        </option>
                    @endforeach
                </select>
            </label>
            <label>Actor
                <input type="text" name="actor" value="{{ request('actor') }}" placeholder="email"
                       style="padding:.5rem;margin-top:.25rem">
            </label>
            <button class="btn btn-primary" type="submit">Filter</button>
            <a class="btn" style="background:var(--color-gray-200)" href="{{ route('saas.platform.audit.index') }}">Reset</a>
        </form>
    </div>

    <div class="card">
        <div class="card-title">{{ $events->total() }} event{{ $events->total() === 1 ? '' : 's' }}</div>

        <table>
            <thead>
                <tr><th>When</th><th>Action</th><th>Tenant</th><th>Actor</th><th>Context</th></tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr>
                        <td style="white-space:nowrap;font-size:.8125rem">
                            {{ $event->created_at?->format('Y-m-d H:i') }}
                        </td>
                        <td><code>{{ $event->action }}</code></td>
                        <td style="font-size:.8125rem">
                            {{ $tenants->firstWhere('uuid', $event->tenant_uuid)?->display_name ?? '—' }}
                        </td>
                        <td style="font-size:.8125rem">
                            {{ $event->actor_label ?? $event->actor_type }}
                            <div style="color:var(--color-gray-500);font-size:.75rem">{{ $event->actor_type }}</div>
                        </td>
                        <td style="font-size:.75rem;max-width:28rem">
                            @if ($event->context)
                                <details>
                                    <summary style="cursor:pointer;color:var(--color-primary)">View</summary>
                                    <pre style="background:var(--color-gray-100);padding:.5rem;border-radius:.25rem;overflow-x:auto;margin-top:.5rem">{{ json_encode($event->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:var(--color-gray-500)">No audit events match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $events->links() }}</div>
    </div>
@endsection
