<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('saas.brand.name', 'SchoolOS') }}</title>
    @vite(['resources/assets/css/marketing.css', 'resources/assets/css/components.css', 'resources/assets/js/app.js'], 'build-saas')
</head>
<body>
    <a class="skip-link" href="#billing-content">{{ __('saas::billing.skip') }}</a>
    <header class="site-header">
        <div class="container nav-shell">
            <a class="brand" href="/">{{ config('saas.brand.name', 'SchoolOS') }}</a>
            <span>{{ __('saas::billing.account') }}</span>
        </div>
    </header>
    <main id="billing-content" class="container" style="padding-block: 3rem">
        @yield('content')
    </main>
</body>
</html>
