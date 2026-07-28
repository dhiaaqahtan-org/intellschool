@extends('saas::platform.layouts.app')

@section('title', 'Subscription')

@section('content')
    <div class="page-header">
        <h1>{{ $subscription->tenant?->display_name ?? 'Unknown tenant' }}</h1>
        <p>
            {{ $subscription->plan?->plan_code }} v{{ $subscription->plan?->version }}
            · <span class="badge badge-gray">{{ $subscription->status }}</span>
            · provider: {{ $subscription->provider }}
        </p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" style="font-size:1.1rem">{{ $subscription->trial_ends_at?->format('Y-m-d') ?? '—' }}</div>
            <div class="stat-label">Trial ends</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:1.1rem">{{ $subscription->current_period_end?->format('Y-m-d') ?? '—' }}</div>
            <div class="stat-label">Period ends</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:1.1rem">{{ $subscription->grace_ends_at?->format('Y-m-d') ?? '—' }}</div>
            <div class="stat-label">Grace ends</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:1.1rem">{{ $subscription->cancelled_at?->format('Y-m-d') ?? '—' }}</div>
            <div class="stat-label">Cancelled</div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Effective entitlements</div>
        <p style="color:var(--color-gray-500);font-size:.875rem;margin-bottom:1rem">
            Resolved through the same service the application enforces with, so this panel cannot disagree with what
            the tenant actually gets. <code>override</code> means a per-tenant grant is winning over the plan.
        </p>

        @if (empty($snapshot['features']))
            <p style="color:var(--color-gray-500)">
                No entitlements resolve for this tenant. If the subscription status is
                <code>cancelled</code>, <code>terminated</code>, <code>paused</code> or <code>pending</code>,
                that is expected — those states grant nothing.
            </p>
        @else
            <table>
                <thead><tr><th>Feature</th><th>Enabled</th><th>Limit</th><th>Source</th></tr></thead>
                <tbody>
                    @foreach (collect($snapshot['features'])->sortKeys() as $code => $feature)
                        <tr>
                            <td><code>{{ $code }}</code></td>
                            <td>
                                <span class="badge {{ ($feature['enabled'] ?? false) ? 'badge-success' : 'badge-gray' }}">
                                    {{ ($feature['enabled'] ?? false) ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>{{ ($feature['limit'] ?? null) === null ? 'Unlimited' : number_format($feature['limit']) }}</td>
                            <td>
                                <span class="badge {{ ($feature['source'] ?? 'plan') === 'override' ? 'badge-warning' : 'badge-gray' }}">
                                    {{ $feature['source'] ?? 'plan' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="card-title">Override status</div>
        <p style="color:var(--color-gray-500);font-size:.875rem;margin-bottom:1rem">
            A manual status change is an <strong>override</strong>, not a billing event. It is recorded in the audit
            log with your name and the reason you give. Moving to <code>past_due</code> or <code>grace</code> keeps
            the school's access — a payment problem must never destroy or lock away their records.
        </p>

        <form method="POST" action="{{ route('saas.platform.subscriptions.status', $subscription) }}"
              style="display:grid;gap:.75rem;max-width:30rem">
            @csrf
            <label>New status
                <select name="status" required style="width:100%;padding:.5rem;margin-top:.25rem">
                    @foreach (\Modules\Saas\Http\Controllers\Platform\SubscriptionController::MANUAL_STATUSES as $value => $desc)
                        <option value="{{ $value }}" @selected($subscription->status === $value)>{{ $desc }}</option>
                    @endforeach
                </select>
            </label>
            <label>Reason
                <input type="text" name="reason" required maxlength="500"
                       placeholder="e.g. Card recovered, confirmed with finance"
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <button class="btn btn-primary" type="submit">Apply override</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Per-tenant entitlement override</div>
        <p style="color:var(--color-gray-500);font-size:.875rem;margin-bottom:1rem">
            Grants or revokes a single feature for this tenant only, above whatever the plan says. Use for negotiated
            terms — not as a substitute for a plan. A reason is required.
        </p>

        <form method="POST" action="{{ route('saas.platform.tenants.entitlements.store', $subscription->tenant) }}"
              style="display:grid;gap:.75rem;max-width:30rem">
            @csrf
            <label>Feature code
                <input type="text" name="feature_code" required maxlength="80" placeholder="e.g. hr.payroll"
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <label>Enabled
                <select name="enabled" style="width:100%;padding:.5rem;margin-top:.25rem">
                    <option value="1">Grant</option>
                    <option value="0">Revoke</option>
                </select>
            </label>
            <label>Limit (blank = unlimited)
                <input type="number" name="limit_value" min="0" style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <label>Expires (blank = no expiry)
                <input type="datetime-local" name="valid_until" style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <label>Reason (min 10 characters)
                <input type="text" name="reason" required minlength="10" maxlength="500"
                       placeholder="e.g. Included in signed enterprise agreement #88"
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <button class="btn btn-primary" type="submit">Save override</button>
        </form>
    </div>

    @php $overrides = \Modules\Saas\Models\Landlord\TenantEntitlement::where('tenant_uuid', $subscription->tenant_uuid)->get(); @endphp
    @if ($overrides->isNotEmpty())
        <div class="card">
            <div class="card-title">Active overrides ({{ $overrides->count() }})</div>
            <table>
                <thead><tr><th>Feature</th><th>Enabled</th><th>Expires</th><th>Reason</th><th>Granted by</th><th></th></tr></thead>
                <tbody>
                    @foreach ($overrides as $override)
                        <tr>
                            <td><code>{{ $override->feature_code }}</code></td>
                            <td><span class="badge {{ $override->enabled ? 'badge-success' : 'badge-danger' }}">{{ $override->enabled ? 'Granted' : 'Revoked' }}</span></td>
                            <td>{{ $override->valid_until?->format('Y-m-d') ?? 'Never' }}</td>
                            <td style="font-size:.8125rem">{{ $override->reason }}</td>
                            <td style="font-size:.8125rem">{{ $override->granted_by }}</td>
                            <td>
                                <form method="POST" action="{{ route('saas.platform.tenants.entitlements.destroy', [$subscription->tenant, $override]) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn" style="background:var(--color-danger);color:#fff" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($errors->any())
        <div class="card" style="background:#fee2e2;border:1px solid #fca5a5">
            <ul style="margin-inline-start:1rem">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
@endsection
