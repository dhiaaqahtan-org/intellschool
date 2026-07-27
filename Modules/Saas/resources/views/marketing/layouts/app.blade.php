@php
    $locale = app()->getLocale();
    $rtl    = in_array($locale, ['ar', 'fa', 'he', 'ur'], true);
    $brand  = config('saas.brand.name');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $brand)</title>
    <meta name="description" content="@yield('description')">
    <meta name="theme-color" content="#070b18">

    <link rel="canonical" href="{{ url()->current() }}">
    @foreach (config('saas.facts.locales', ['en']) as $alt)
        <link rel="alternate" hreflang="{{ $alt }}" href="{{ url()->current() }}?lang={{ $alt }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:title" content="@yield('title', $brand)">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', $locale) }}">
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
            'inLanguage' => config('saas.facts.locales'),
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
