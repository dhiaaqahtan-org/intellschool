@extends('saas::platform.layouts.app')

@section('title', 'Tenants')

@section('content')
@php $canCreateTenant = Illuminate\Support\Facades\Gate::forUser(auth('platform')->user())->allows('createTenant'); @endphp
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
        <h1>Tenants</h1>
        <p>Manage all tenant schools on the platform.</p>
    </div>
    @if($canCreateTenant)
        <a href="{{ route('saas.platform.tenants.create') }}" class="btn btn-primary">New tenant</a>
    @endif
</div>

<div class="card">
    <div data-vue-component="tenant-filter">
    <form method="GET" action="{{ route('saas.platform.tenants.index') }}" class="filter-bar" style="margin-bottom:1.5rem">
        <label class="field filter-grow" for="tenant-search">Search tenants
        <input id="tenant-search" type="search" name="search" value="{{ request('search') }}" placeholder="Name, slug, or exact UUID"
               style="flex: 1; padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
        </label>
        <label class="field" for="tenant-status">Status
        <select id="tenant-status" name="status" style="padding: 0.5rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem;">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
        </select>
        </label>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->filled('search') || request()->filled('status'))
            <a class="btn" href="{{ route('saas.platform.tenants.index') }}">Clear</a>
        @endif
    </form>
        <script type="application/json" data-props>{!! json_encode([
            'action' => route('saas.platform.tenants.index', [], false),
            'initialSearch' => (string) request('search', ''),
            'initialStatus' => (string) request('status', ''),
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'suspended', 'label' => 'Suspended'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
                ['value' => 'terminated', 'label' => 'Terminated'],
            ],
            'labels' => [
                'search' => 'Search tenants',
                'placeholder' => 'Name, slug, or exact UUID',
                'status' => 'Status',
                'all' => 'All statuses',
                'filter' => 'Filter',
                'filtering' => 'Filtering?',
                'clear' => 'Clear',
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </div>

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
                        <span class="badge {{ $tenant->status->badgeClass() }}">{{ $tenant->status->label() }}</span>
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
