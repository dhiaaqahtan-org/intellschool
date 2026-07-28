@extends('saas::platform.layouts.app')

@section('title', 'Subscriptions')

@section('content')
    <div class="page-header">
        <h1>Subscriptions</h1>
        <p>Which plan each tenant is on, and what state their billing is in.</p>
    </div>

    <div class="stats-grid">
        @foreach (['active' => 'Active', 'trialing' => 'Trialing', 'past_due' => 'Past due', 'grace' => 'Grace', 'cancelled' => 'Cancelled'] as $key => $label)
            <div class="stat-card">
                <div class="stat-value">{{ $counts[$key] ?? 0 }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-title">All subscriptions</div>

        <form method="GET" style="margin-bottom:1rem">
            <select name="status" onchange="this.form.submit()" style="padding:.5rem">
                <option value="">All statuses</option>
                @foreach (\Modules\Saas\Http\Controllers\Platform\SubscriptionController::MANUAL_STATUSES as $value => $desc)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $value }}</option>
                @endforeach
            </select>
            <noscript><button class="btn btn-primary" type="submit">Filter</button></noscript>
        </form>

        <table>
            <thead>
                <tr><th>Tenant</th><th>Plan</th><th>Status</th><th>Period ends</th><th>Provider</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td>
                            {{ $subscription->tenant?->display_name ?? '—' }}
                            <div style="color:var(--color-gray-500);font-size:.75rem">{{ $subscription->tenant?->slug }}</div>
                        </td>
                        <td>{{ $subscription->plan?->plan_code }} v{{ $subscription->plan?->version }}</td>
                        <td>
                            @php
                                $class = match ($subscription->status) {
                                    'active', 'trialing' => 'badge-success',
                                    'past_due', 'grace' => 'badge-warning',
                                    'cancelled', 'terminated' => 'badge-danger',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $class }}">{{ $subscription->status }}</span>
                        </td>
                        <td>{{ $subscription->current_period_end?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $subscription->provider }}</td>
                        <td><a class="btn btn-primary" href="{{ route('saas.platform.subscriptions.show', $subscription) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:var(--color-gray-500)">No subscriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1rem">{{ $subscriptions->links() }}</div>
    </div>

    <div class="card">
        <div class="card-title">Assign a plan to a tenant</div>
        <p style="color:var(--color-gray-500);font-size:.875rem;margin-bottom:1rem">
            This records an operator decision, not a payment. It does not charge anyone and does not call a payment
            provider — provider state arrives through verified webhooks.
        </p>

        <form method="POST" action="{{ route('saas.platform.subscriptions.store') }}" style="display:grid;gap:.75rem;max-width:30rem">
            @csrf
            <label>Tenant
                <select name="tenant_uuid" required style="width:100%;padding:.5rem;margin-top:.25rem">
                    <option value="">Select a tenant…</option>
                    @foreach (\Modules\Saas\Models\Landlord\Tenant::orderBy('display_name')->get() as $t)
                        <option value="{{ $t->uuid }}">{{ $t->display_name }} ({{ $t->slug }})</option>
                    @endforeach
                </select>
            </label>
            <label>Plan version
                <select name="plan_id" required style="width:100%;padding:.5rem;margin-top:.25rem">
                    <option value="">Select a plan…</option>
                    @foreach (\Modules\Saas\Models\Landlord\Plan::orderBy('plan_code')->orderByDesc('version')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->plan_code }} v{{ $p->version }} — {{ $p->display_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Status
                <select name="status" required style="width:100%;padding:.5rem;margin-top:.25rem">
                    @foreach (\Modules\Saas\Http\Controllers\Platform\SubscriptionController::MANUAL_STATUSES as $value => $desc)
                        <option value="{{ $value }}" @selected($value === 'trialing')>{{ $desc }}</option>
                    @endforeach
                </select>
            </label>
            <label>Reason (recorded in the audit log)
                <input type="text" name="reason" required maxlength="500"
                       placeholder="e.g. Signed order form #1042"
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <button class="btn btn-primary" type="submit">Assign plan</button>
        </form>
    </div>

    @if ($errors->any())
        <div class="card" style="background:#fee2e2;border:1px solid #fca5a5">
            <ul style="margin-inline-start:1rem">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
@endsection
