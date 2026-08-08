@extends('saas::platform.layouts.app')

@section('title', $tenant->display_name)

@section('content')
@php
    $platformGate = Illuminate\Support\Facades\Gate::forUser(auth('platform')->user());
    $canManageLifecycle = $platformGate->allows('suspendTenant', $tenant);
    $canProvision = $platformGate->allows('provision', $tenant);
    $canManageDomains = $platformGate->allows('manageDomains', $tenant);
@endphp
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
        <h1>{{ $tenant->display_name }}</h1>
        <p>
            <code>{{ $tenant->slug }}</code> &middot;
            UUID: <code style="font-size: 0.75rem;">{{ $tenant->uuid }}</code>
        </p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary" href="{{ route('saas.platform.tenants.modules.index', $tenant) }}">Configure Modules</a>
        <a class="btn btn-secondary" href="{{ route('saas.platform.tenants.system-config.index', $tenant) }}">System Config</a>
        <a class="btn btn-secondary" href="{{ route('saas.platform.tenants.mail-config.index', $tenant) }}">Mail Config</a>
        <a class="btn btn-secondary" href="{{ route('saas.platform.tenants.sms-config.index', $tenant) }}">SMS Config</a>
        <a class="btn btn-secondary" href="{{ route('saas.platform.tenants.chat-config.index', $tenant) }}">Chat Config</a>
        <a class="btn btn-secondary" href="{{ route('saas.platform.tenants.feature-config.index', $tenant) }}">Feature Config</a>
        @if($canManageLifecycle)
            <a class="btn" href="#lifecycle">Manage lifecycle</a>
        @endif
        @if($canProvision && ($tenant->provisioning_state->value === 'queued' || $tenant->provisioning_state->value === 'failed_recoverable'))
            <form method="POST" action="{{ route('saas.platform.tenants.provision', $tenant) }}">
                @csrf
                <button type="submit" class="btn btn-primary" data-submitting-label="Provisioning…">Run provisioning</button>
            </form>
        @endif
    </div>
</div>

{{-- Status Cards --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Status</div>
        <div>
            @switch($tenant->status->value)
                @case('active')
                    <span class="badge badge-success">Active</span>
                    @break
                @case('trialing')
                    <span class="badge badge-warning">Trialing</span>
                    @break
                @case('suspended')
                    <span class="badge badge-danger">Suspended</span>
                    @break
                @case('pending')
                    <span class="badge badge-gray">Pending</span>
                    @break
                @default
                    <span class="badge badge-gray">{{ $tenant->status->label() }}</span>
            @endswitch
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Provisioning</div>
        <div>
            @if(in_array($tenant->provisioning_state->value, ['ready', 'completed']))
                <span class="badge badge-success">{{ $tenant->provisioning_state->label() }}</span>
            @elseif(str_starts_with($tenant->provisioning_state->value, 'failed'))
                <span class="badge badge-danger">{{ $tenant->provisioning_state->label() }}</span>
            @else
                <span class="badge badge-warning">{{ $tenant->provisioning_state->label() }}</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Plan</div>
        <div style="font-weight: 600;">{{ $tenant->subscription?->plan?->display_name ?? 'No subscription' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Created</div>
        <div style="font-weight: 600;">{{ $tenant->created_at->format('M j, Y') }}</div>
    </div>
</div>

@if($canManageLifecycle)
<div class="card" id="lifecycle">
    <h2 class="card-title">Lifecycle controls</h2>
    <p class="muted">Every lifecycle change is recorded in the audit log. A reason is required for actions that restrict customer access.</p>
    <div class="form-grid" style="margin-top:1rem">
        @if(in_array($tenant->status->value, ['active', 'trialing'], true))
            <form class="field" method="POST" action="{{ route('saas.platform.tenants.suspend', $tenant) }}">
                @csrf
                <label for="suspension-reason">Suspension reason</label>
                <textarea id="suspension-reason" name="reason" required minlength="10" maxlength="500" placeholder="Explain the operational or policy reason"></textarea>
                <button type="submit" class="btn btn-warning" data-submitting-label="Suspending…">Suspend tenant</button>
            </form>
        @elseif($tenant->status->value === 'suspended')
            <form class="field" method="POST" action="{{ route('saas.platform.tenants.reactivate', $tenant) }}">
                @csrf
                <p>This restores tenant request access. Subscription and provisioning rules still apply.</p>
                <button type="submit" class="btn btn-primary" data-submitting-label="Reactivating…">Reactivate tenant</button>
            </form>
        @endif

        @unless(in_array($tenant->status->value, ['cancelled', 'terminated'], true))
            <form class="field" method="POST" action="{{ route('saas.platform.tenants.cancel', $tenant) }}">
                @csrf
                <label for="cancellation-reason">Cancellation reason</label>
                <textarea id="cancellation-reason" name="reason" required minlength="10" maxlength="500" placeholder="Record the customer request or contract reference"></textarea>
                <label for="retention-days">Retention period in days</label>
                <input id="retention-days" type="number" name="retention_days" min="0" max="3650" value="90">
                <button type="submit" class="btn btn-danger" data-submitting-label="Cancelling…">Cancel tenant</button>
            </form>
        @endunless
    </div>
</div>
@endif

{{-- Tenant Details --}}
<div class="card">
    <h2 class="card-title">Details</h2>
    <table>
        <tbody>
            <tr>
                <th style="width: 200px;">Display Name</th>
                <td>{{ $tenant->display_name }}</td>
            </tr>
            <tr>
                <th>Legal Name</th>
                <td>{{ $tenant->legal_name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Slug</th>
                <td><code>{{ $tenant->slug }}</code></td>
            </tr>
            <tr>
                <th>Tier</th>
                <td>{{ $tenant->tier ?? 'standard' }}</td>
            </tr>
            <tr>
                <th>Locale</th>
                <td>{{ $tenant->locale ?? 'en' }}</td>
            </tr>
            <tr>
                <th>Timezone</th>
                <td>{{ $tenant->timezone ?? 'UTC' }}</td>
            </tr>
            <tr>
                <th>Region</th>
                <td>{{ $tenant->region ?? '—' }}</td>
            </tr>
            @if($tenant->trial_ends_at)
                <tr>
                    <th>Trial Ends</th>
                    <td>{{ $tenant->trial_ends_at->format('M j, Y H:i') }}</td>
                </tr>
            @endif
            @if($tenant->suspended_at)
                <tr>
                    <th>Suspended At</th>
                    <td>{{ $tenant->suspended_at->format('M j, Y H:i') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

{{-- Database --}}
<div class="card">
    <h2 class="card-title">Database</h2>
    @if($tenant->database)
        <table>
            <tbody>
                <tr>
                    <th style="width: 200px;">Database Name</th>
                    <td><code>{{ $tenant->database->database_name }}</code></td>
                </tr>
                <tr>
                    <th>Cluster</th>
                    <td>{{ $tenant->database->cluster ?? 'default' }}</td>
                </tr>
                <tr>
                    <th>Secret Ref</th>
                    <td><code>{{ $tenant->database->secret_ref ?? '—' }}</code></td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="color: var(--color-gray-500);">No database record. Provisioning may not have completed.</p>
    @endif
</div>

{{-- Domains --}}
<div class="card">
    <h2 class="card-title">Domains</h2>
    <table>
        <thead>
            <tr>
                <th>Hostname</th>
                <th>Type</th>
                <th>Primary</th>
                <th>Verified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenant->domains as $domain)
                <tr>
                    <td><code>{{ $domain->hostname }}</code></td>
                    <td>{{ ucfirst($domain->type) }}</td>
                    <td>
                        @if($domain->is_primary)
                            <span class="badge badge-success">Primary</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($domain->verified_at)
                            <span class="badge badge-success">Verified</span>
                        @elseif($domain->type === 'subdomain')
                            <span class="badge badge-gray">Auto</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($canManageDomains)
                        <div style="display:flex; gap:0.375rem; flex-wrap:wrap;">
                            @if($domain->type === 'custom' && ! $domain->verified_at)
                            <form method="POST" action="{{ route('saas.platform.tenants.domains.verify', [$tenant, $domain]) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Check DNS</button>
                            </form>
                            @endif

                            @if(! $domain->is_primary && $domain->isRoutable())
                            <form method="POST" action="{{ route('saas.platform.tenants.domains.primary', [$tenant, $domain]) }}">
                                @csrf
                                <button type="submit" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Make primary</button>
                            </form>
                            @endif

                            <form method="POST" action="{{ route('saas.platform.tenants.domains.destroy', [$tenant, $domain]) }}"
                                  onsubmit="return confirm('Remove this domain?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="background: var(--color-danger); color: white; padding: 0.25rem 0.5rem; font-size: 0.75rem;">Remove</button>
                            </form>
                        </div>
                        @else
                            <span class="muted">Read only</span>
                        @endif
                    </td>
                </tr>

                @if($domain->type === 'custom' && ! $domain->verified_at && $canManageDomains)
                    {{-- The school cannot act without these exact two values, so
                         they are shown in full rather than behind a dialog. --}}
                    <tr>
                        <td colspan="5" style="background: var(--color-gray-50); font-size: 0.8125rem;">
                            <strong>{{ $domain->hostname }} is not routing yet.</strong>
                            Ask the school to add this DNS record at their registrar, then press <em>Check DNS</em>.
                            <table style="margin-top: 0.5rem;">
                                <tr><th style="text-align:left; padding-right:1rem;">Type</th><td><code>TXT</code></td></tr>
                                <tr><th style="text-align:left; padding-right:1rem;">Name</th><td><code>{{ $verificationRecords[$domain->id]['name'] ?? '' }}</code></td></tr>
                                <tr><th style="text-align:left; padding-right:1rem;">Value</th><td><code style="word-break:break-all;">{{ $verificationRecords[$domain->id]['value'] ?? '' }}</code></td></tr>
                            </table>
                            <p style="margin-top:0.5rem; color: var(--color-gray-500);">
                                They must also point <code>{{ $domain->hostname }}</code> at this server with an A or CNAME record,
                                and the domain needs its own TLS certificate — a wildcard for the platform domain does not cover it.
                            </p>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--color-gray-500);">No domains configured.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($canManageDomains)
    <form method="POST" action="{{ route('saas.platform.tenants.domains.store', $tenant) }}" class="filter-bar" style="margin-top:1rem">
        @csrf
        <label class="field filter-grow" for="domain-hostname">Hostname
        <input id="domain-hostname" type="text" name="hostname" placeholder="school.example.com" required
               style="flex: 1; padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
        </label>
        <label class="field" for="domain-type">Domain type
        <select id="domain-type" name="type" style="padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
            <option value="subdomain">Subdomain</option>
            <option value="custom">Custom</option>
        </select>
        </label>
        <button type="submit" class="btn btn-primary">Add Domain</button>
    </form>
    @endif
</div>

{{-- Subscription --}}
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 class="card-title" style="margin:0;">Subscription & Plan</h2>
        <button type="button" class="btn btn-primary" onclick="openAssignPlanModal()">
            Assign / Change Plan
        </button>
    </div>
    @if($tenant->subscription)
        <table>
            <tbody>
                <tr>
                    <th style="width: 200px;">Plan</th>
                    <td><strong>{{ $tenant->subscription->plan?->display_name ?? '—' }}</strong> @if($tenant->subscription->plan) <code>({{ $tenant->subscription->plan->plan_code }} v{{ $tenant->subscription->plan->version }})</code> @endif</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if(in_array($tenant->subscription->status, ['active', 'trialing']))
                            <span class="badge badge-success">{{ ucfirst($tenant->subscription->status) }}</span>
                        @elseif(in_array($tenant->subscription->status, ['canceled', 'terminated']))
                            <span class="badge badge-danger">{{ ucfirst($tenant->subscription->status) }}</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($tenant->subscription->status) }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Provider</th>
                    <td>{{ $tenant->subscription->provider ?? 'manual' }}</td>
                </tr>
                @if($tenant->subscription->current_period_end)
                    <tr>
                        <th>Period Ends</th>
                        <td>{{ $tenant->subscription->current_period_end->format('M j, Y') }}</td>
                    </tr>
                @endif
                @if($tenant->subscription->trial_ends_at)
                    <tr>
                        <th>Trial Ends</th>
                        <td>{{ $tenant->subscription->trial_ends_at->format('M j, Y') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <p style="color: var(--color-gray-500); margin-bottom: 1rem;">No subscription record currently assigned to this tenant.</p>
    @endif
</div>

<!-- Assign / Change Plan Modal -->
<div id="assignPlanModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal-card" style="background:#fff; border-radius:0.5rem; width:100%; max-width:520px; padding:1.5rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid #e5e7eb; padding-bottom:0.75rem;">
            <h3 style="margin:0; font-size:1.15rem; font-weight:600; color:#111827;">Assign / Change Plan for {{ $tenant->display_name }}</h3>
            <button type="button" onclick="closeAssignPlanModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#6b7280;">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('saas.platform.subscriptions.store') }}">
            @csrf
            <input type="hidden" name="tenant_uuid" value="{{ $tenant->uuid }}">
            
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.375rem; color:#374151;">Select Plan *</label>
                <select name="plan_id" required style="width:100%; padding:0.625rem 0.875rem; border:1px solid #d1d5db; border-radius:0.375rem; font-size:0.9rem; background:#fff; box-sizing:border-box;">
                    @forelse($plans as $planOption)
                        <option value="{{ $planOption->id }}" {{ $tenant->subscription?->plan_id == $planOption->id ? 'selected' : '' }}>
                            {{ $planOption->display_name }} ({{ $planOption->plan_code }} v{{ $planOption->version }}) — {{ $planOption->currency }} {{ number_format($planOption->price_cents / 100, 2) }}/{{ $planOption->billing_interval }}
                        </option>
                    @empty
                        <option value="">No active plans found</option>
                    @endforelse
                </select>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.375rem; color:#374151;">Subscription Status *</label>
                <select name="status" required style="width:100%; padding:0.625rem 0.875rem; border:1px solid #d1d5db; border-radius:0.375rem; font-size:0.9rem; background:#fff; box-sizing:border-box;">
                    @foreach(\Modules\Saas\Http\Controllers\Platform\SubscriptionController::MANUAL_STATUSES as $statusCode => $statusLabel)
                        <option value="{{ $statusCode }}" {{ ($tenant->subscription?->status ?? 'active') === $statusCode ? 'selected' : '' }}>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.375rem; color:#374151;">Reason / Reference *</label>
                <textarea name="reason" required placeholder="e.g. Manually assigned plan by Platform Admin" style="width:100%; padding:0.625rem 0.875rem; border:1px solid #d1d5db; border-radius:0.375rem; font-size:0.9rem; min-height:80px; box-sizing:border-box;">Assigned plan via Platform Control Center</textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid #e5e7eb; padding-top:1rem;">
                <button type="button" onclick="closeAssignPlanModal()" class="btn" style="padding:0.5rem 1rem; border:1px solid #d1d5db; background:#fff; border-radius:0.375rem; cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem; border-radius:0.375rem; cursor:pointer; font-weight:600;">Assign Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignPlanModal() {
        document.getElementById('assignPlanModal').style.display = 'flex';
    }
    function closeAssignPlanModal() {
        document.getElementById('assignPlanModal').style.display = 'none';
    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAssignPlanModal();
        }
    });
</script>

{{-- Owners --}}
<div class="card">
    <h2 class="card-title">Owners</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Linked</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenant->owners as $owner)
                <tr>
                    <td>{{ $owner->name }}</td>
                    <td>{{ $owner->email }}</td>
                    <td>{{ ucfirst($owner->role ?? 'owner') }}</td>
                    <td>{{ $owner->created_at->format('M j, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--color-gray-500);">No owners linked.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('saas::onboarding.provisioning', ['tenant' => $tenant])

{{-- Provisioning Runs --}}
<div class="card">
    <h2 class="card-title">Provisioning History</h2>
    <table>
        <thead>
            <tr>
                <th>State</th>
                <th>Step</th>
                <th>Attempts</th>
                <th>Started</th>
                <th>Finished</th>
                <th>Error</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenant->provisioningRuns as $run)
                <tr>
                    <td>
                        @if(in_array($run->state->value ?? $run->state, ['ready', 'completed']))
                            <span class="badge badge-success">{{ $run->state->label() }}</span>
                        @elseif(str_starts_with($run->state->value ?? $run->state, 'failed'))
                            <span class="badge badge-danger">{{ $run->state->label() }}</span>
                        @else
                            <span class="badge badge-warning">{{ $run->state->label() }}</span>
                        @endif
                    </td>
                    <td><code>{{ $run->step ?? '—' }}</code></td>
                    <td>{{ $run->attempts ?? 0 }}</td>
                    <td>{{ $run->started_at?->format('M j, Y H:i') ?? '—' }}</td>
                    <td>{{ $run->finished_at?->format('M j, Y H:i') ?? '—' }}</td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">{{ $run->error_summary ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--color-gray-500);">No provisioning runs.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Recent Audit Events --}}
<div class="card">
    <h2 class="card-title">Recent Activity</h2>
    <table>
        <thead>
            <tr>
                <th>Action</th>
                <th>Actor</th>
                <th>IP</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAuditEvents as $event)
                <tr>
                    <td><code>{{ $event->action }}</code></td>
                    <td>{{ $event->actor_type }}{{ $event->actor_id ? " #{$event->actor_id}" : '' }}</td>
                    <td>{{ $event->ip ?? '—' }}</td>
                    <td>{{ $event->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--color-gray-500);">No audit events recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
