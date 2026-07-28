@php
    $latestProvisioningRun = $tenant->provisioningRuns->sortByDesc('created_at')->first();
    $initialProvisioning = $latestProvisioningRun
        ? (new Modules\Saas\Http\Resources\ProvisioningRunResource($latestProvisioningRun))->resolve(request())
        : null;
@endphp
<div class="card" aria-labelledby="live-provisioning-title">
    <h2 class="card-title" id="live-provisioning-title">Live provisioning status</h2>
    <div data-vue-component="provisioning-progress">
        @if($latestProvisioningRun)
            <p>
                {{ $latestProvisioningRun->state->label() }}
                — {{ $latestProvisioningRun->progress }}%
                @if($latestProvisioningRun->step)
                    · {{ $latestProvisioningRun->step }}
                @endif
            </p>
            <progress value="{{ $latestProvisioningRun->progress }}" max="100">{{ $latestProvisioningRun->progress }}%</progress>
        @else
            <p>No provisioning run has been created.</p>
        @endif
        <script type="application/json" data-props>{!! json_encode([
            'initialRun' => $initialProvisioning,
            'endpoint' => route('saas.api.platform.tenants.provisioning', $tenant, false),
            'labels' => [
                'title' => 'Provisioning progress',
                'state' => 'State',
                'step' => 'Step',
                'waiting' => 'Waiting',
                'empty' => 'No provisioning run has been created.',
                'refresh' => 'Refresh status',
                'refreshing' => 'Refreshing…',
                'expired' => 'Your platform session expired. Sign in again.',
                'denied' => 'You are not allowed to view this tenant.',
                'rate_limited' => 'Too many refresh attempts. Wait and retry.',
                'failed' => 'Provisioning status could not be refreshed.',
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </div>
</div>
