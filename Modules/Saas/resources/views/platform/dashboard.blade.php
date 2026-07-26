@extends('saas::platform.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>Platform Dashboard</h1>
    <p>Overview of your SaaS platform metrics and tenant activity.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_tenants'] ?? 0 }}</div>
        <div class="stat-label">Total Tenants</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['active_tenants'] ?? 0 }}</div>
        <div class="stat-label">Active Tenants</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['trialing_tenants'] ?? 0 }}</div>
        <div class="stat-label">Trialing</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['active_subscriptions'] ?? 0 }}</div>
        <div class="stat-label">Active Subscriptions</div>
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
                        @switch($tenant->status)
                            @case('active')
                                <span class="badge badge-success">Active</span>
                                @break
                            @case('trialing')
                                <span class="badge badge-warning">Trialing</span>
                                @break
                            @case('suspended')
                                <span class="badge badge-danger">Suspended</span>
                                @break
                            @default
                                <span class="badge badge-gray">{{ ucfirst($tenant->status) }}</span>
                        @endswitch
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
                        @if(in_array($run->state, ['ready', 'completed']))
                            <span class="badge badge-success">{{ ucfirst($run->state) }}</span>
                        @elseif(str_starts_with($run->state, 'failed'))
                            <span class="badge badge-danger">{{ ucfirst(str_replace('_', ' ', $run->state)) }}</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $run->state)) }}</span>
                        @endif
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
