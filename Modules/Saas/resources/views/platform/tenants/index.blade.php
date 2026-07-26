@extends('saas::platform.layouts.app')

@section('title', 'Tenants')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
        <h1>Tenants</h1>
        <p>Manage all tenant schools on the platform.</p>
    </div>
    <a href="{{ route('saas.platform.tenants.create') }}" class="btn btn-primary">+ New Tenant</a>
</div>

<div class="card">
    <form method="GET" action="{{ route('saas.platform.tenants.index') }}" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or slug..."
               style="flex: 1; padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
        <select name="status" style="padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>Trialing</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Plan</th>
                <th>Domain</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
                <tr>
                    <td>
                        <strong>{{ $tenant->display_name }}</strong>
                        @if($tenant->legal_name && $tenant->legal_name !== $tenant->display_name)
                            <br><small style="color: var(--color-gray-500);">{{ $tenant->legal_name }}</small>
                        @endif
                    </td>
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
                            @case('pending')
                                <span class="badge badge-gray">Pending</span>
                                @break
                            @default
                                <span class="badge badge-gray">{{ ucfirst($tenant->status) }}</span>
                        @endswitch
                    </td>
                    <td>{{ $tenant->subscription?->plan?->display_name ?? '—' }}</td>
                    <td>
                        @if($tenant->domains->firstWhere('is_primary', true))
                            <code>{{ $tenant->domains->firstWhere('is_primary', true)->hostname }}</code>
                        @else
                            <span style="color: var(--color-gray-500);">No domain</span>
                        @endif
                    </td>
                    <td>{{ $tenant->created_at->format('M j, Y') }}</td>
                    <td>
                        <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
                        No tenants found matching your criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($tenants->hasPages())
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $tenants->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
