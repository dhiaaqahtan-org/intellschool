@extends('saas::platform.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>Platform Dashboard</h1>
    <p>Operational health across {{ $stats['total_tenants'] ?? 0 }} tenants, provisioning, and subscriptions.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['active_tenants'] ?? 0 }}</div>
        <div class="stat-label">Active tenants</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending_tenants'] ?? 0 }}</div>
        <div class="stat-label">Pending tenants</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['trialing_subscriptions'] ?? 0 }}</div>
        <div class="stat-label">Trial subscriptions</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['failed_provisioning'] ?? 0 }}</div>
        <div class="stat-label">Provisioning needs attention</div>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Recent Tenants</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTenants as $tenant)
                <tr>
                    <td>{{ $tenant->display_name }}</td>
                    <td><code>{{ $tenant->slug }}</code></td>
                    <td>
                        <span class="badge {{ $tenant->status->badgeClass() }}">{{ $tenant->status->label() }}</span>
                    </td>
                    <td>{{ $tenant->created_at->diffForHumans() }}</td>
                    <td>
                        <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--color-gray-500);">
                        No tenants yet. Create your first tenant to get started.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h2 class="card-title">Provisioning Queue</h2>
    <table>
        <thead>
            <tr>
                <th>Tenant</th>
                <th>State</th>
                <th>Progress</th>
                <th>Started</th>
            </tr>
        </thead>
        <tbody>
            @forelse($provisioningRuns as $run)
                <tr>
                    <td>{{ $run->tenant?->display_name ?? $run->tenant_uuid }}</td>
                    <td>
                        <span class="badge {{ $run->state->badgeClass() }}">{{ $run->state->label() }}</span>
                    </td>
                    <td>{{ $run->progress_percentage ?? 0 }}%</td>
                    <td>{{ $run->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--color-gray-500);">
                        No provisioning runs in progress.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
