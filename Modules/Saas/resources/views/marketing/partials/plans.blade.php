{{--
    Approved plan cards.

    Only rendered when config('saas.claims.publish_pricing') is true.

    IMPORTANT: read plans from the landlord `saas_plans` / `saas_plan_features`
    tables (plan §5.1) and pass them in as $plans. Prices must never be
    hard-coded in a template — a plan is versioned, and a published price is a
    contractual statement. This view renders whatever the control plane says is
    currently active and public.

    Expected shape per plan:
      ['key', 'name', 'description', 'price_display', 'interval_display',
       'featured' => bool, 'features' => [string, ...], 'cta_route']
--}}
@php $plans = $plans ?? []; @endphp

@if (empty($plans))
    <div class="notice">
        <svg aria-hidden="true"><use href="#i-alert"></use></svg>
        <span>{{ __('saas::marketing.pricing.unavailable') }}</span>
    </div>
@else
    <div class="price-grid">
        @foreach ($plans as $plan)
            <div class="plan reveal {{ ($plan['featured'] ?? false) ? 'plan--featured' : '' }}">
                <div>
                    @if ($plan['featured'] ?? false)
                        <span class="badge badge--ink"><span class="dot"></span> {{ __('saas::marketing.pricing.popular') }}</span>
                    @endif
                    <h3 @if ($plan['featured'] ?? false) style="margin-block-start:var(--sp-3)" @endif>
                        {{ $plan['name'] }}
                    </h3>
                    <p class="plan__desc">{{ $plan['description'] }}</p>
                </div>

                <div class="plan__price">
                    <b>{{ $plan['price_display'] }}</b>
                    @if (! empty($plan['interval_display']))
                        <span>{{ $plan['interval_display'] }}</span>
                    @endif
                </div>

                <ul class="list-check">
                    @foreach ($plan['features'] as $feature)
                        <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>{{ $feature }}</span></li>
                    @endforeach
                </ul>

                <a class="btn btn--block {{ ($plan['featured'] ?? false) ? 'btn--accent' : 'btn--ghost' }}"
                   href="{{ $plan['cta_route'] ?? route('saas.marketing.demo') }}">
                    {{ ($plan['featured'] ?? false) ? __('saas::marketing.nav.demo') : __('saas::marketing.pricing.talk') }}
                </a>
            </div>
        @endforeach
    </div>
@endif
