@php
    $locale = app()->getLocale();
    $direction = Localizer::currentLocaleDirection();
    $brand  = config('saas.brand.name');
    $isLocalizedRequest = Route::isLocalized();
    $currentRoute = Route::current();
    $baseRouteName = $currentRoute?->baseName();
    $routeParameters = $currentRoute
        ? array_intersect_key($currentRoute->parameters(), array_flip($currentRoute->parameterNames()))
        : [];
    unset($routeParameters['locale']);
    $xDefaultUrl = null;
    if ($isLocalizedRequest && $baseRouteName) {
        $xDefaultUrl = route('without_locale.'.$baseRouteName, $routeParameters);
    }
    $canonicalUrl = $isLocalizedRequest ? Route::localizedUrl($locale) : url()->current();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $brand)</title>
    <meta name="description" content="@yield('description')">
    <meta name="theme-color" content="#070b18">

    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($isLocalizedRequest)
        @foreach (config('localizer.supported_locales', ['en', 'ar']) as $alt)
            <link rel="alternate" hreflang="{{ $alt }}" href="{{ Route::localizedUrl($alt) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $xDefaultUrl }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:title" content="@yield('title', $brand)">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', $locale) }}">
    @foreach (config('localizer.supported_locales', ['en', 'ar']) as $alt)
        @if ($alt !== $locale)
            <meta property="og:locale:alternate" content="{{ str_replace('-', '_', $alt) }}">
        @endif
    @endforeach
    <meta name="twitter:card" content="summary_large_image">

    {{--
        Structured data is intentionally minimal. Add aggregateRating, review,
        offers or organisation contact points ONLY when the underlying facts are
        verified — fabricated structured data is a search-policy violation and a
        consumer-protection risk.
    --}}
    @php
        // Built in PHP rather than with @json(...): the Blade directive parses
        // its argument with a single-line regex, so a multi-line array literal
        // is truncated into syntactically invalid PHP and the whole layout
        // fails to compile.
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $brand,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => config('localizer.supported_locales'),
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>

    @vite(['resources/assets/css/marketing.css', 'resources/assets/js/marketing/app.js'], 'build-saas')
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#main">{{ __('saas::marketing.nav.skip') }}</a>

    @include('saas::marketing.partials.icons')
    @include('saas::marketing.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('saas::marketing.partials.footer')
    @stack('scripts')
</body>
</html>
