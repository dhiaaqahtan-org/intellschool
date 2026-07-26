@extends('saas::platform.layouts.app')

@section('title', $tenant->display_name)

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
        <h1>{{ $tenant->display_name }}</h1>
        <p>
            <code>{{ $tenant->slug }}</code> &middot;
            UUID: <code style="font-size: 0.75rem;">{{ $tenant->uuid }}</code>
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        @if($tenant->status->value === 'active' || $tenant->status->value === 'trialing')
            <form method="POST" action="{{ route('saas.platform.tenants.suspend', $tenant) }}" onsubmit="return confirm('Suspend this tenant? They will lose access immediately.')">
                @csrf
                <button type="submit" class="btn" style="background: var(--color-warning); color: white;">Suspend</button>
            </form>
        @elseif($tenant->status->value === 'suspended')
            <form method="POST" action="{{ route('saas.platform.tenants.reactivate', $tenant) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Reactivate</button>
            </form>
        @endif

        @if($tenant->provisioning_state->value === 'queued' || $tenant->provisioning_state->value === 'failed_recoverable')
            <form method="POST" action="{{ route('saas.platform.tenants.provision', $tenant) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Run Provisioning</button>
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
                    <span class="badge badge-gray">{{ ucfirst($tenant->status->value) }}</span>
            @endswitch
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Provisioning</div>
        <div>
            @if(in_array($tenant->provisioning_state->value, ['ready', 'completed']))
                <span class="badge badge-success">{{ ucfirst($tenant->provisioning_state->value) }}</span>
            @elseif(str_starts_with($tenant->provisioning_state->value, 'failed'))
                <span class="badge badge-danger">{{ ucfirst(str_replace('_', ' ', $tenant->provisioning_state->value)) }}</span>
            @else
                <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $tenant->provisioning_state->value)) }}</span>
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
                        <form method="POST" action="{{ route('saas.platform.tenants.domains.destroy', [$tenant, $domain]) }}"
                              onsubmit="return confirm('Remove this domain?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="background: var(--color-danger); color: white; padding: 0.25rem 0.5rem; font-size: 0.75rem;">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--color-gray-500);">No domains configured.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <form method="POST" action="{{ route('saas.platform.tenants.domains.store', $tenant) }}" style="margin-top: 1rem; display: flex; gap: 0.5rem;">
        @csrf
        <input type="text" name="hostname" placeholder="e.g. school.example.com" required
               style="flex: 1; padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
        <select name="type" style="padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
            <option value="subdomain">Subdomain</option>
            <option value="custom">Custom</option>
        </select>
        <button type="submit" class="btn btn-primary">Add Domain</button>
    </form>
</div>

{{-- Subscription --}}
<div class="card">
    <h2 class="card-title">Subscription</h2>
    @if($tenant->subscription)
        <table>
            <tbody>
                <tr>
                    <th style="width: 200px;">Plan</th>
                    <td>{{ $tenant->subscription->plan?->display_name ?? '—' }}</td>
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
                    <td>{{ $tenant->subscription->provider ?? '—' }}</td>
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
        <p style="color: var(--color-gray-500);">No subscription record.</p>
    @endif
</div>

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
                            <span class="badge badge-success">{{ ucfirst($run->state->value ?? $run->state) }}</span>
                        @elseif(str_starts_with($run->state->value ?? $run->state, 'failed'))
                            <span class="badge badge-danger">{{ ucfirst(str_replace('_', ' ', $run->state->value ?? $run->state)) }}</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $run->state->value ?? $run->state)) }}</span>
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
