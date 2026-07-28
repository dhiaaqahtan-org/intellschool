@extends('saas::marketing.layouts.app')

@php
    $brand       = config('saas.brand.name');
    $tenantHost  = ltrim((string) config('saas.hosts.tenant_suffix', 'example.com'), '.');
    $showPricing = $claims['pricing'];
    $mobileGa    = $claims['mobile_ga'];

    /* Permission codes are literal identifiers from the application, not prose,
       so they are not translated. Names and summaries come from the lang file. */
    $roleSamples = [
        'admin'      => ['config:store', 'user:change-role', 'backup:manage', 'payroll:process', 'finance:report', 'site:manage'],
        'principal'  => ['exam:report', 'timetable:allocate', 'leave-request:action', 'incident:manage', 'student:promotion'],
        'accountant' => ['fee:payment', 'receipt:create', 'receipt:cancel', 'ledger:read', 'day-closure:manage'],
        'staff'      => ['attendance:mark', 'exam:marks-record', 'lesson-plan:create', 'assignment:create'],
        'guardian'   => ['student:self-access', 'fee:payment', 'ticket:create', 'announcement:read'],
        'student'    => ['assignment:read', 'online-class:read', 'student:leave-request', 'chat:access'],
    ];

    $roleData = collect($roles)->map(fn ($count, $key) => [
        'key'         => $key,
        'name'        => __("saas::marketing.roles.items.$key.name"),
        'summary'     => __("saas::marketing.roles.items.$key.summary"),
        'permissions' => $count,
        'sample'      => $roleSamples[$key] ?? [],
    ])->values();

    $moduleIcons = [
        'student' => 'users', 'employee' => 'clipboard', 'academic' => 'book', 'core' => 'layers',
        'exam' => 'chart', 'finance' => 'cash', 'transport' => 'bus', 'inventory' => 'box',
        'reception' => 'phone', 'resource' => 'book', 'library' => 'book', 'hostel' => 'layers',
        'task' => 'clipboard', 'communication' => 'chat', 'helpdesk' => 'chat', 'approval' => 'check',
        'asset' => 'box', 'calendar' => 'calendar', 'mess' => 'box', 'contact' => 'phone',
        'blog' => 'globe', 'news' => 'globe', 'auth' => 'lock', 'activity' => 'calendar',
        'guardian' => 'users', 'form' => 'clipboard', 'recruitment' => 'users', 'site' => 'globe',
        'post' => 'chat', 'gallery' => 'globe', 'chat' => 'chat', 'discipline' => 'alert',
        'device' => 'phone', 'misc' => 'layers',
    ];
@endphp

@section('title', $brand.' — '.__('saas::marketing.hero.title_a').' '.__('saas::marketing.hero.title_b'))
@section('description', __('saas::marketing.hero.lede'))

@section('content')

{{-- ============================== HERO ============================== --}}
<section class="hero">
    <canvas class="hero__canvas" aria-hidden="true"></canvas>
    <div class="hero__aurora" aria-hidden="true"></div>

    <div class="wrap--wide hero__grid">
        <div>
            <span class="badge badge--ink"><span class="dot"></span> {{ __('saas::marketing.hero.badge') }}</span>

            <h1 style="margin-block-start:var(--sp-4)">
                {{ __('saas::marketing.hero.title_a') }}<br>
                <span class="grad">{{ __('saas::marketing.hero.title_b') }}</span>
            </h1>

            <p class="hero__lede">{{ __('saas::marketing.hero.lede') }}</p>

            <div class="cluster hero__cta">
                <a class="btn btn--accent btn--lg" href="{{ route('saas.marketing.demo') }}">
                    {{ __('saas::marketing.nav.demo') }}
                    <svg class="i-arrow" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
                <a class="btn btn--on-ink btn--lg" href="#platform">
                    {{ __('saas::marketing.hero.secondary') }}
                </a>
            </div>

            <p class="hero__note">
                <svg aria-hidden="true"><use href="#i-globe"></use></svg>
                {{ __('saas::marketing.hero.locale_note') }}
            </p>

            <div class="factbar">
                @foreach ([
                    'modules'     => $facts['modules'],
                    'endpoints'   => $facts['api_endpoints'],
                    'roles'       => $facts['roles'],
                    'permissions' => $facts['admin_permissions'],
                ] as $key => $value)
                    <div class="fact">
                        <div class="fact__n" data-count>{{ number_format($value) }}</div>
                        <div class="fact__l">{{ __("saas::marketing.hero.facts.$key") }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        @include('saas::marketing.partials.stage-dashboard', ['tenantHost' => $tenantHost])
    </div>
</section>

{{-- ============================ PLATFORM ============================ --}}
<section class="section" id="platform">
    <div class="wrap">
        <div class="section-head is-center reveal">
            <span class="eyebrow">{{ __('saas::marketing.platform.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.platform.title') }}</h2>
            <p>{{ __('saas::marketing.platform.lede') }}</p>
        </div>

        <div class="grid grid-3">
            @foreach (['enrolment' => 'users', 'fees' => 'cash', 'academics' => 'book',
                       'staff' => 'clipboard', 'operations' => 'bus', 'parents' => 'chat'] as $key => $icon)
                @php $card = __("saas::marketing.platform.cards.$key"); @endphp
                <article class="card reveal">
                    <div class="card__ico"><svg><use href="#i-{{ $icon }}"></use></svg></div>
                    <h3>{{ $card[0] }}</h3>
                    <p>{{ $card[1] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ ADMISSIONS ============================ --}}
<section class="section section--alt">
    <div class="wrap">
        <div class="iso">
            <div class="reveal">
                <span class="eyebrow">{{ __('saas::marketing.admissions.eyebrow') }}</span>
                <h2>{{ __('saas::marketing.admissions.title') }}</h2>
                <p style="color:var(--c-muted);font-size:var(--fs-lg);margin-block:var(--sp-4) var(--sp-5)">
                    {{ __('saas::marketing.admissions.lede') }}
                </p>
                <ul class="list-check">
                    @foreach (__('saas::marketing.admissions.points') as $point)
                        <li>
                            <svg aria-hidden="true"><use href="#i-check"></use></svg>
                            <span><strong>{{ $point[0] }}</strong> {{ $point[1] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @include('saas::marketing.partials.stage-pipeline')
        </div>
    </div>
</section>

{{-- ============================= MODULES ============================= --}}
<section class="section" id="modules">
    <div class="wrap">
        <div class="section-head is-center reveal">
            <span class="eyebrow">{{ __('saas::marketing.modules.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.modules.title', ['count' => $facts['modules']]) }}</h2>
            <p>{{ __('saas::marketing.modules.lede') }}</p>
        </div>

        <div class="bento reveal">
            @foreach ($modules as $key => $count)
                <span class="mod">
                    <svg aria-hidden="true"><use href="#i-{{ $moduleIcons[$key] ?? 'layers' }}"></use></svg>
                    {{ __("saas::marketing.modules.names.$key") }}
                    <span class="mod__n">{{ number_format($count) }}</span>
                </span>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================== ROLES ============================== --}}
<section class="section section--alt" id="roles">
    <div class="wrap">
        <div class="section-head reveal">
            <span class="eyebrow">{{ __('saas::marketing.roles.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.roles.title') }}</h2>
            <p>{{ __('saas::marketing.roles.lede', ['count' => $facts['roles']]) }}</p>
        </div>

        {{-- Server-rendered tabs are the baseline; RoleExplorer.vue replaces this
             node once loaded. Both are keyboard operable and RTL aware. --}}
        <div class="reveal" data-vue-component="role-explorer">
            {{-- Island props. A JSON script tag rather than interpolated
                 attributes, so escaping is the parser's job. Nothing sensitive
                 goes in here — it ships to every visitor.

                 Encoded in PHP, not via @json(...), whose single-line argument
                 regex cannot handle a multi-line array literal. --}}
            @php
                $roleProps = [
                    'roles' => $roleData,
                    'dir' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
                    'labels' => [
                        'tablist' => __('saas::marketing.roles.tablist'),
                        'permissions' => __('saas::marketing.roles.permissions'),
                    ],
                ];
            @endphp
            <script type="application/json" data-props>{!! json_encode($roleProps, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

            <div class="roles">
                <div class="roles__tabs" role="tablist" aria-label="{{ __('saas::marketing.roles.tablist') }}">
                    @foreach ($roleData as $i => $role)
                        <button class="roles__tab" type="button" role="tab"
                                id="role-tab-{{ $role['key'] }}"
                                aria-controls="role-panel-{{ $role['key'] }}"
                                aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                tabindex="{{ $i === 0 ? '0' : '-1' }}">
                            {{ $role['name'] }}
                            <span class="n">{{ number_format($role['permissions']) }}</span>
                        </button>
                    @endforeach
                </div>

                <div>
                    @foreach ($roleData as $i => $role)
                        <div class="roles__panel" role="tabpanel" tabindex="0"
                             id="role-panel-{{ $role['key'] }}"
                             aria-labelledby="role-tab-{{ $role['key'] }}"
                             @if ($i !== 0) hidden @endif>
                            <h3>{{ $role['name'] }} &mdash;
                                {{ number_format($role['permissions']) }}
                                {{ __('saas::marketing.roles.permissions') }}</h3>
                            <p>{{ $role['summary'] }}</p>
                            <ul class="tags">
                                @foreach ($role['sample'] as $permission)
                                    <li class="tag">{{ $permission }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================ ISOLATION ============================ --}}
<section class="section section--ink" id="isolation">
    <div class="wrap">
        <div class="section-head reveal">
            <span class="eyebrow">{{ __('saas::marketing.isolation.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.isolation.title') }}</h2>
            <p>{{ __('saas::marketing.isolation.lede') }}</p>
        </div>

        <div class="iso">
            <div class="iso-vis reveal" data-inview aria-hidden="true">
                <div class="iso-vis__stack">
                    @php
                        $flow = __('saas::marketing.isolation.flow');
                        $tags = __('saas::marketing.isolation.tags');
                        $rows = [
                            ['request', 'host', ''],
                            ['resolve', 'landlord', ''],
                            ['switch', 'isolated', 'iso-layer--tenant'],
                            ['auth', 'guard', ''],
                            ['blocked', 'denied', 'iso-layer--blocked'],
                        ];
                    @endphp
                    @foreach ($rows as [$key, $tag, $modifier])
                        <div class="iso-layer {{ $modifier }}">
                            <span>
                                <b>{{ $flow[$key][0] }}</b>
                                <span>{{ str_replace(':host', $tenantHost, $flow[$key][1]) }}</span>
                            </span>
                            <span class="iso-layer__tag">{{ $tags[$tag] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="reveal">
                <ul class="list-check">
                    @foreach (__('saas::marketing.isolation.points') as $point)
                        <li>
                            <svg aria-hidden="true"><use href="#i-check"></use></svg>
                            <span><strong>{{ $point[0] }}</strong> {{ $point[1] }}</span>
                        </li>
                    @endforeach
                </ul>

                @unless ($claims['uptime'] && $claims['certifications'])
                    <div class="notice" style="margin-block-start:var(--sp-6)">
                        <svg aria-hidden="true"><use href="#i-alert"></use></svg>
                        <span>{{ __('saas::marketing.isolation.no_claims') }}</span>
                    </div>
                @endunless
            </div>
        </div>
    </div>
</section>

{{-- ============================== CAMPUS ============================== --}}
<section class="section">
    <div class="wrap">
        <div class="section-head is-center reveal">
            <span class="eyebrow">{{ __('saas::marketing.campus.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.campus.title') }}</h2>
            <p>{{ __('saas::marketing.campus.lede') }}</p>
        </div>

        <div class="grid grid-3">
            @foreach (__('saas::marketing.campus.items') as $i => $item)
                <article class="card reveal">
                    <div class="card__ico">
                        <svg aria-hidden="true"><use href="#i-{{ ['shield', 'layers', 'globe'][$i] }}"></use></svg>
                    </div>
                    <h3>{{ $item[0] }}</h3>
                    <p>{{ $item[1] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================== MOBILE ============================== --}}
<section class="section section--alt">
    <div class="wrap">
        <div class="iso">
            <div class="reveal">
                <span class="eyebrow">{{ __('saas::marketing.mobile.eyebrow') }}</span>
                <h2>{{ __('saas::marketing.mobile.title') }}</h2>
                <p style="color:var(--c-muted);font-size:var(--fs-lg);margin-block:var(--sp-4) var(--sp-5)">
                    {{ __('saas::marketing.mobile.lede') }}
                </p>
                <ul class="list-check">
                    @foreach (__('saas::marketing.mobile.points') as $point)
                        <li>
                            <svg aria-hidden="true"><use href="#i-check"></use></svg>
                            <span><strong>{{ $point[0] }}</strong> {{ $point[1] }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Gate: no store links, screenshots or offline guarantees until
                     the client actually builds and passes its isolation tests. --}}
                @unless ($mobileGa)
                    <p style="margin-block-start:var(--sp-5)">
                        <span class="badge badge--warn"><span class="dot"></span> {{ __('saas::marketing.mobile.status') }}</span>
                    </p>
                    <p style="font-size:var(--fs-sm);color:var(--c-muted);margin-block-start:var(--sp-3);max-inline-size:36rem">
                        {{ __('saas::marketing.mobile.caveat') }}
                    </p>
                @endunless
            </div>

            @include('saas::marketing.partials.stage-guardian')
        </div>
    </div>
</section>

{{-- ============================= PRICING ============================= --}}
<section class="section" id="pricing">
    <div class="wrap">
        <div class="section-head is-center reveal">
            <span class="eyebrow">{{ __('saas::marketing.pricing.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.pricing.title') }}</h2>
            <p>{{ __('saas::marketing.pricing.lede', ['modules' => $facts['modules']]) }}</p>
        </div>

        @if ($showPricing)
            {{-- Render approved plans from the landlord `saas_plans` table here.
                 Never hard-code a price in a template. --}}
            @include('saas::marketing.partials.plans')
        @else
            <div class="notice reveal" style="max-inline-size:52rem;margin:0 auto var(--sp-6)">
                <svg aria-hidden="true"><use href="#i-alert"></use></svg>
                <span>
                    <strong>{{ __('saas::marketing.pricing.placeholder') }}</strong>
                    {{ __('saas::marketing.pricing.placeholder_body') }}
                </span>
            </div>
            <p class="reveal" style="text-align:center;color:var(--c-muted);max-inline-size:40rem;margin-inline:auto">
                {{ __('saas::marketing.pricing.unavailable') }}
            </p>
            <div class="cluster reveal" style="justify-content:center;margin-block-start:var(--sp-5)">
                <a class="btn btn--accent btn--lg" href="{{ route('saas.marketing.demo') }}">
                    {{ __('saas::marketing.pricing.contact') }}
                    <svg class="i-arrow" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
            </div>
        @endif
    </div>
</section>

{{-- ========================== IMPLEMENTATION ========================== --}}
<section class="section section--ink">
    <div class="wrap">
        <div class="section-head reveal">
            <span class="eyebrow">{{ __('saas::marketing.implementation.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.implementation.title') }}</h2>
            <p>{{ __('saas::marketing.implementation.lede') }}</p>
        </div>

        <div class="steps">
            @foreach (__('saas::marketing.implementation.steps') as $step)
                <div class="step reveal">
                    <h3>{{ $step[0] }}</h3>
                    <p>{{ $step[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =============================== FAQ =============================== --}}
<section class="section" id="faq">
    <div class="wrap">
        <div class="section-head is-center reveal">
            <span class="eyebrow">{{ __('saas::marketing.faq.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.faq.title') }}</h2>
        </div>

        <div class="faq reveal">
            @foreach (__('saas::marketing.faq.items') as $item)
                <details>
                    <summary>{{ $item[0] }}</summary>
                    <div class="faq__a">{{ str_replace(':host', $tenantHost, $item[1]) }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- =============================== CTA =============================== --}}
<section class="section section--tight">
    <div class="wrap">
        <div class="cta-band reveal">
            <h2>{{ __('saas::marketing.cta.title') }}</h2>
            <p>{{ __('saas::marketing.cta.lede') }}</p>
            <div class="cluster">
                <a class="btn btn--accent btn--lg" href="{{ route('saas.marketing.demo') }}">
                    {{ __('saas::marketing.nav.demo') }}
                    <svg class="i-arrow" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
                <a class="btn btn--on-ink btn--lg" href="#pricing">
                    {{ __('saas::marketing.cta.see_pricing') }}
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
