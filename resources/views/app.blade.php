<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur', 'ps', 'sd']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('config.general.meta_description') }}">
    <meta name="keywords" content="{{ config('config.general.meta_keywords') }}">
    <meta name="author" content="{{ config('config.general.meta_author') }}">
    <title>{{ config('config.general.app_name', config('app.name', 'ScriptMint')) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" href="{{ config('config.assets.favicon') }}" type="image/png">

    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('custom/finance-report-catalog.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/sidebar-shell.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/responsive-action-menus.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/language-switcher.css') }}">

    @include('gateways.assets.index')
</head>

<body class="{{ config('config.layout.display') }}">
    <div
        id="root"
        class="theme-{{ config('config.system.color_scheme', 'default') }}"
        data-student-registration-create-title="{{ __('student.registration.create_title') }}"
    >
        <router-view></router-view>
    </div>
    <script src="/js/lang"></script>
    <script src="{{ asset('custom/finance-report-catalog.js') }}" defer></script>
    <script src="{{ asset('custom/sidebar-shell.js') }}" defer></script>
    <script src="{{ asset('custom/language-switcher.js') }}" defer></script>
    <script src="{{ asset('custom/student-registration-labels.js') }}" defer></script>
</body>

</html>
