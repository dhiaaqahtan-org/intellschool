@php
    $brand  = config('saas.brand.name');
    $locale = app()->getLocale();
    // Until the full sitemap from plan §10.3 exists, navigation targets
    // sections of the home page rather than routes that do not yet resolve.
    $home   = route('saas.marketing.home');
    $links  = [
        'platform' => $home.'#platform',
        'modules'  => $home.'#modules',
        'roles'    => $home.'#roles',
        'security' => $home.'#isolation',
        'pricing'  => $home.'#pricing',
        'faq'      => $home.'#faq',
    ];
@endphp

<header class="site-header">
    <div class="wrap--wide site-header__inner">
        <a class="brand" href="{{ route('saas.marketing.home') }}" aria-label="{{ $brand }}">
            <span class="brand__mark" aria-hidden="true"><svg><use href="#i-logo"></use></svg></span>
            {{ $brand }}
        </a>

        <nav class="nav" aria-label="{{ __('saas::marketing.nav.primary') }}">
            <ul>
                @foreach ($links as $key => $href)
                    <li><a href="{{ $href }}">{{ __("saas::marketing.nav.$key") }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div class="header-actions">
            <div class="lang-switch" role="group" aria-label="{{ __('saas::marketing.nav.language') }}">
                @foreach (config('saas.facts.locales', ['en']) as $alt)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $alt]) }}"
                       lang="{{ $alt }}"
                       hreflang="{{ $alt }}"
                       @if ($alt === $locale) aria-current="true" @endif>
                        {{ $alt === 'ar' ? 'ع' : strtoupper($alt) }}
                    </a>
                @endforeach
            </div>

            {{-- Sign-in goes to the platform host, never to the marketing host. --}}
            @if ($platformHost = config('saas.hosts.platform'))
                <a class="btn btn--on-ink" href="{{ 'https://'.$platformHost.'/login' }}">
                    {{ __('saas::marketing.nav.signin') }}
                </a>
            @endif

            <a class="btn btn--accent" href="{{ route('saas.marketing.demo') }}">
                {{ __('saas::marketing.nav.demo') }}
                <svg class="i-arrow" aria-hidden="true"><use href="#i-arrow"></use></svg>
            </a>

            <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false"
                    aria-controls="mobile-nav" aria-label="{{ __('saas::marketing.nav.open_menu') }}">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
                     stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="wrap--wide">
        <div class="mobile-nav" id="mobile-nav" data-nav-panel hidden>
            @foreach ($links as $key => $href)
                <a href="{{ $href }}">{{ __("saas::marketing.nav.$key") }}</a>
            @endforeach
            <a href="{{ route('saas.marketing.demo') }}">{{ __('saas::marketing.nav.demo') }}</a>
        </div>
    </div>
</header>
