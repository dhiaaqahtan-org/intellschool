@props(['metaTitle', 'metaDescription', 'metaKeywords'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar','fa','he','ur','ps','sd']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="author" content="{{ config('config.general.meta_author', config('app.name')) }}">
    <title>{{ $metaTitle ?? config('config.general.app_name', config('app.name', 'IntellSchool')) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" href="{{ config('config.assets.favicon') }}" type="image/png">

    @vite(['resources/js/site.js', 'resources/css/site.css'], 'site/build')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('school-theme.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('school-home.css') }}?v=1">
    @livewireStyles

</head>

<body class="theme-{{ config('config.site.color_scheme', 'default') }} antialiased">

    <a href="#main-content"
        class="sr-only z-50 rounded bg-white px-4 py-3 text-gray-950 focus:not-sr-only focus:fixed focus:start-4 focus:top-4">
        {{ __('website.skip_to_content') }}
    </a>

    <x-site.header />

    <main id="main-content" tabindex="-1">
        {{ $slot }}
    </main>

    <x-site.footer />

    <div class="fixed bottom-40 end-6 z-50 hidden flex-col gap-3 md:flex">
        @if (config('config.social_network.facebook'))
            <a href="{{ config('config.social_network.facebook') }}"
                aria-label="Facebook"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-[#1877F2] text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-[#0d6efd]">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
        @endif
        @if (config('config.social_network.twitter'))
            <a href="{{ config('config.social_network.twitter') }}"
                aria-label="X"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-[#1DA1F2] text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-[#0d95e8]">
                <i class="fa-brands fa-twitter"></i>
            </a>
        @endif
        @if (config('config.social_network.linkedin'))
            <a href="{{ config('config.social_network.linkedin') }}"
                aria-label="LinkedIn"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-[#0A66C2] text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-[#094c8f]">
                <i class="fa-brands fa-linkedin-in"></i>
            </a>
        @endif
        @if (config('config.social_network.youtube'))
            <a href="{{ config('config.social_network.youtube') }}"
                aria-label="YouTube"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-[#FF0000] text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-[#cc0000]">
                <i class="fa-brands fa-youtube"></i>
            </a>
        @endif
        @if (config('config.social_network.google'))
            <a href="{{ config('config.social_network.google') }}"
                aria-label="Google"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-[#4285F4] text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-[#3367d6]">
                <i class="fa-brands fa-google"></i>
            </a>
        @endif
        @if (config('config.social_network.github'))
            <a href="{{ config('config.social_network.github') }}"
                aria-label="GitHub"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-[#333333] text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-[#24292e]">
                <i class="fa-brands fa-github"></i>
            </a>
        @endif
        @if (config('config.general.app_phone'))
            <a href="tel:{{ config('config.general.app_phone') }}"
                aria-label="{{ __('website.phone') }}"
                class="bg-site-primary hover:bg-site-dark-primary flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 text-white transition-all duration-200 hover:scale-110 hover:border-white/40">
                <i class="fa-solid fa-phone"></i>
            </a>
        @endif
        @if (config('config.general.app_email'))
            <a href="mailto:{{ config('config.general.app_email') }}"
                aria-label="{{ __('website.email') }}"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-black text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-gray-900">
                <i class="fa-solid fa-envelope"></i>
            </a>
        @endif
        @if (config('config.general.app_email'))
            <a href="https://web.whatsapp.com/send?phone={{ config('config.general.app_phone') }}"
                aria-label="WhatsApp"
                class="flex h-12 w-12 transform items-center justify-center rounded-full border-2 border-white/20 bg-green-800 text-white transition-all duration-200 hover:scale-110 hover:border-white/40 hover:bg-green-900">
                <i class="fa-brands fa-whatsapp"></i>
            </a>
        @endif
    </div>

    @livewireScriptConfig
</body>

</html>
