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
                    <td style="white-space: nowrap;">
                        <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                            <a href="{{ route('saas.platform.tenants.show', $tenant) }}" class="btn btn-sm btn-primary">View</a>
                            <button type="button" 
                                    class="btn btn-sm btn-secondary"
                                    onclick="openEditTenantModal({
                                        uuid: '{{ $tenant->uuid }}',
                                        displayName: '{{ addslashes($tenant->display_name) }}',
                                        legalName: '{{ addslashes($tenant->legal_name ?? '') }}',
                                        region: '{{ addslashes($tenant->region ?? '') }}',
                                        tier: '{{ addslashes($tenant->tier ?? '') }}',
                                        locale: '{{ $tenant->locale }}',
                                        timezone: '{{ $tenant->timezone }}'
                                    })">
                                Edit
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-danger" 
                                    onclick="openDeleteTenantModal('{{ $tenant->uuid }}', '{{ addslashes($tenant->display_name) }}')">
                                Delete
                            </button>
                        </div>
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

<!-- Edit Tenant Modal -->
<div id="editTenantModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal-card" style="background:#fff; border-radius:0.5rem; width:100%; max-width:550px; padding:1.5rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid #e5e7eb; padding-bottom:0.75rem;">
            <h3 style="margin:0; font-size:1.25rem; font-weight:600; color:#111827;">Edit Tenant</h3>
            <button type="button" onclick="closeEditTenantModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#6b7280;">&times;</button>
        </div>
        
        <form id="editTenantForm" method="POST" action="">
            @csrf
            @method('PATCH')
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom:1rem;">
                <div style="grid-column: span 2;">
                    <label style="display:block; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem; color:#374151;">Display Name *</label>
                    <input type="text" id="edit_display_name" name="display_name" required style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; box-sizing:border-box;">
                </div>
                
                <div style="grid-column: span 2;">
                    <label style="display:block; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem; color:#374151;">Legal Name</label>
                    <input type="text" id="edit_legal_name" name="legal_name" style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem; color:#374151;">Region</label>
                    <input type="text" id="edit_region" name="region" placeholder="e.g. us-east-1" style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem; color:#374151;">Tier</label>
                    <input type="text" id="edit_tier" name="tier" placeholder="e.g. standard" style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem; color:#374151;">Language</label>
                    <select id="edit_locale" name="locale" style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; box-sizing:border-box;">
                        <option value="en">English (en)</option>
                        <option value="ar">Arabic (ar)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem; color:#374151;">Timezone</label>
                    <input type="text" id="edit_timezone" name="timezone" placeholder="e.g. UTC" style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; box-sizing:border-box;">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem; border-top:1px solid #e5e7eb; padding-top:1rem;">
                <button type="button" onclick="closeEditTenantModal()" class="btn" style="padding:0.5rem 1rem; border:1px solid #d1d5db; background:#fff; border-radius:0.375rem; cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem; border-radius:0.375rem; cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Tenant Confirmation Modal -->
<div id="deleteTenantModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal-card" style="background:#fff; border-radius:0.5rem; width:100%; max-width:480px; padding:1.5rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; color:#dc2626;">
            <div style="background:#fee2e2; border-radius:50%; width:40px; height:40px; display:flex; items-center; justify-content:center; flex-shrink:0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 style="margin:0; font-size:1.25rem; font-weight:600; color:#991b1b;">Delete Tenant</h3>
        </div>

        <p style="color:#374151; margin-bottom:1rem; font-size:0.95rem; line-height:1.5;">
            Are you sure you want to delete <strong id="delete_tenant_name" style="color:#111827;"></strong>?
        </p>
        <p style="color:#b91c1c; background:#fef2f2; border-left:4px solid #ef4444; padding:0.75rem; border-radius:0.25rem; font-size:0.875rem; margin-bottom:1.5rem;">
            ⚠️ <strong>Warning:</strong> This will delete the tenant record, domain routing pointers, and owner linkages. This action cannot be undone.
        </p>

        <form id="deleteTenantForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" onclick="closeDeleteTenantModal()" class="btn" style="padding:0.5rem 1rem; border:1px solid #d1d5db; background:#fff; border-radius:0.375rem; cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-danger" style="background:#dc2626; color:#fff; padding:0.5rem 1rem; border:none; border-radius:0.375rem; cursor:pointer; font-weight:600;">Yes, Delete Tenant</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditTenantModal(tenant) {
        var form = document.getElementById('editTenantForm');
        form.action = '/platform/tenants/' + tenant.uuid;
        document.getElementById('edit_display_name').value = tenant.displayName || '';
        document.getElementById('edit_legal_name').value = tenant.legalName || '';
        document.getElementById('edit_region').value = tenant.region || '';
        document.getElementById('edit_tier').value = tenant.tier || '';
        document.getElementById('edit_locale').value = tenant.locale || 'en';
        document.getElementById('edit_timezone').value = tenant.timezone || 'UTC';
        
        var modal = document.getElementById('editTenantModal');
        modal.style.display = 'flex';
    }

    function closeEditTenantModal() {
        document.getElementById('editTenantModal').style.display = 'none';
    }

    function openDeleteTenantModal(uuid, displayName) {
        var form = document.getElementById('deleteTenantForm');
        form.action = '/platform/tenants/' + uuid;
        document.getElementById('delete_tenant_name').textContent = displayName;
        
        var modal = document.getElementById('deleteTenantModal');
        modal.style.display = 'flex';
    }

    function closeDeleteTenantModal() {
        document.getElementById('deleteTenantModal').style.display = 'none';
    }

    // Close modal on Escape key press
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditTenantModal();
            closeDeleteTenantModal();
        }
    });
</script>
@endsection
