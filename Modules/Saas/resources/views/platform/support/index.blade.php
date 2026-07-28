@extends('saas::platform.layouts.app')

@section('title', 'Support Sessions')

@section('content')
    <div class="page-header">
        <h1>Support Sessions</h1>
        <p>The only sanctioned route from a platform operator into a school's records.</p>
    </div>

    <div class="card" style="background:#eff6ff;border:1px solid #bfdbfe">
        <strong>How this works.</strong>
        Read-only by default, a stated reason always, approved by someone other than the requester, time-limited,
        and expires on its own. Every step is written to the audit log. This exists so that no one — including you —
        has standing access to student data.
    </div>

    <div class="card">
        <div class="card-title">Request access</div>
        <form method="POST" action="{{ route('saas.platform.support.store') }}" style="display:grid;gap:.75rem;max-width:34rem">
            @csrf
            <label>Tenant
                <select name="tenant_uuid" required style="width:100%;padding:.5rem;margin-top:.25rem">
                    <option value="">Select a tenant…</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->uuid }}">{{ $tenant->display_name }} ({{ $tenant->slug }})</option>
                    @endforeach
                </select>
            </label>
            <label>Reason (minimum 20 characters — this is read by the customer during audits)
                <textarea name="reason" required minlength="20" maxlength="1000" rows="3"
                          placeholder="e.g. Ticket #4821: fee receipts for grade 9 show a duplicated line; need to inspect the transactions table."
                          style="width:100%;padding:.5rem;margin-top:.25rem"></textarea>
            </label>
            <label>Scope
                <select name="scope" required style="width:100%;padding:.5rem;margin-top:.25rem">
                    <option value="read">Read-only (default)</option>
                    <option value="write">Write — only when a fix genuinely requires it</option>
                </select>
            </label>
            <p style="color:var(--color-gray-500);font-size:.8125rem;margin:0">
                The expiry window is set by policy, not chosen here — an operator cannot grant themselves
                longer access than the platform allows.
            </p>
            <button class="btn btn-primary" type="submit">Request access</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Sessions</div>
        <table>
            <thead>
                <tr><th>Tenant</th><th>Operator</th><th>Scope</th><th>Status</th><th>Expires</th><th>Reason</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    <tr>
                        <td>{{ $session->tenant?->display_name ?? $session->tenant_uuid }}</td>
                        <td style="font-size:.8125rem">{{ $session->operator_email }}</td>
                        <td>
                            <span class="badge {{ $session->scope === 'write' ? 'badge-warning' : 'badge-gray' }}">
                                {{ $session->scope }}
                            </span>
                        </td>
                        <td>
                            @php
                                $class = match ($session->status) {
                                    'active' => 'badge-success',
                                    'requested' => 'badge-warning',
                                    'revoked', 'expired' => 'badge-danger',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $class }}">{{ $session->status }}</span>
                        </td>
                        <td style="font-size:.8125rem">{{ $session->expires_at?->diffForHumans() ?? '—' }}</td>
                        <td style="font-size:.8125rem;max-width:22rem">{{ $session->reason }}</td>
                        <td>
                            <div style="display:flex;gap:.5rem">
                                @if ($session->status === 'requested')
                                    <form method="POST" action="{{ route('saas.platform.support.approve', $session) }}">
                                        @csrf
                                        <button class="btn btn-primary" type="submit">Approve</button>
                                    </form>
                                @endif
                                @if (in_array($session->status, ['requested', 'approved', 'active'], true))
                                    <form method="POST" action="{{ route('saas.platform.support.revoke', $session) }}">
                                        @csrf
                                        <button class="btn" style="background:var(--color-danger);color:#fff" type="submit">Revoke</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="color:var(--color-gray-500)">No support sessions recorded.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $sessions->links() }}</div>
    </div>

    @if ($errors->any())
        <div class="card" style="background:#fee2e2;border:1px solid #fca5a5">
            <ul style="margin-inline-start:1rem">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
@endsection
