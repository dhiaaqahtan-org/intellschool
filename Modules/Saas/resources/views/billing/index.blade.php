@extends('saas::billing.layouts.app')

@section('title', __('saas::billing.title'))

@section('content')
<header class="section-heading">
    <p class="eyebrow">{{ $context->slug }}</p>
    <h1>{{ __('saas::billing.title') }}</h1>
    <p>{{ __('saas::billing.intro') }}</p>
</header>

<section class="card" aria-labelledby="subscription-heading">
    <h2 id="subscription-heading">{{ __('saas::billing.subscription') }}</h2>

    <div data-vue-component="subscription-summary">
        @if($subscription)
            <dl>
                <dt>{{ __('saas::billing.plan') }}</dt>
                <dd>{{ $subscription->plan?->display_name ?? __('saas::billing.no_plan') }}</dd>
                <dt>{{ __('saas::billing.status') }}</dt>
                <dd>{{ __('saas::billing.statuses.'.$subscription->status) }}</dd>
                <dt>{{ __('saas::billing.period_end') }}</dt>
                <dd>{{ $subscription->current_period_end?->translatedFormat('j F Y') ?? '—' }}</dd>
            </dl>
        @else
            <p>{{ __('saas::billing.empty') }}</p>
        @endif

        <script type="application/json" data-props>{!! json_encode([
            'initialSubscription' => $summary,
            'endpoint' => route('saas.api.tenant.subscription', [], false),
            'labels' => __('saas::billing.vue'),
            'statusLabels' => __('saas::billing.statuses'),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </div>
</section>

@if(in_array($context->status->value, ['suspended', 'cancelled'], true))
    <div class="form-status is-error" role="alert">
        {{ __('saas::billing.read_only') }}
    </div>
@endif
@endsection
