{{-- Top utility bar --}}
<div class="sch-topbar">
    <div class="wrap">
        <div class="sch-topbar__left">
            @if (config('config.general.app_phone'))
                <span><i class="fa-solid fa-phone"></i> <a href="tel:{{ config('config.general.app_phone') }}">{{ config('config.general.app_phone') }}</a></span>
            @endif
            @if (config('config.general.app_email'))
                <span><i class="fa-solid fa-envelope"></i> <a href="mailto:{{ config('config.general.app_email') }}">{{ config('config.general.app_email') }}</a></span>
            @endif
        </div>
        <div class="sch-topbar__social">
            <span class="lbl">Follow Us</span>
            @if (config('config.social_network.facebook'))<a href="{{ config('config.social_network.facebook') }}" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>@endif
            @if (config('config.social_network.twitter'))<a href="{{ config('config.social_network.twitter') }}" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>@endif
            @if (config('config.social_network.linkedin'))<a href="{{ config('config.social_network.linkedin') }}" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>@endif
            @if (config('config.social_network.youtube'))<a href="{{ config('config.social_network.youtube') }}" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>@endif
        </div>
    </div>
</div>

<header id="navbar" class="inset-0 z-40 w-full items-center bg-slate-100 transition-all">
    <x-site.sticky-head />

    <div class="container">
        <nav class="flex items-center">
            <div class="flex h-20 items-center">
                <a href="{{ route('site.home') }}">
                    <img src="{{ config('config.assets.logo') }}" class="logo-dark h-12"
                        alt="{{ __('website.site_logo') }}">
                    <img src="{{ config('config.assets.logo') }}" class="logo-light h-12"
                        alt="{{ __('website.site_logo') }}">
                </a>
            </div>

            <div class="ms-auto hidden lg:block">
                <ul class="navbar-nav flex items-center justify-center gap-x-1">

                    @foreach ($headerMenus as $menu)
                        @if ($menu->children->count())
                            <li class="nav-item group relative">
                                <a href="javascript:void(0);" class="nav-link flex items-center">
                                    {{ $menu->name }} <i
                                        class="fa-solid fa-angle-down ms-2 align-middle transition-transform group-hover:rotate-180"></i>
                                </a>

                                <div
                                    class="invisible absolute start-0 top-full z-50 mt-2 w-48 origin-top scale-95 transform opacity-0 transition-all duration-200 group-hover:visible group-hover:scale-100 group-hover:opacity-100">
                                    <div class="space-y-1.5 rounded-lg border border-gray-200 bg-white p-2 shadow-lg">
                                        @foreach ($menu->children as $child)
                                            <div class="nav-item">
                                                @if ($child->is_external)
                                                    <a class="nav-link block rounded-md px-3 py-2 transition-colors hover:bg-gray-100"
                                                        href="{{ $child->url }}">{{ $child->name }}</a>
                                                @else
                                                    <a class="nav-link block rounded-md px-3 py-2 transition-colors hover:bg-gray-100"
                                                        href="{{ $child->url }}">{{ $child->name }}</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                        @else
                            <li class="nav-item">
                                @if ($menu->is_external)
                                    <a class="nav-link" href="{{ $menu->url }}">{{ $menu->name }}</a>
                                @else
                                    <a class="nav-link" href="{{ $menu->url }}">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            {{-- Language switcher (desktop) --}}
            <div class="group relative ms-3 hidden items-center lg:flex">
                <button type="button" class="nav-link flex items-center gap-2" aria-haspopup="true" aria-label="Change language">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}</span>
                    <i class="fa-solid fa-angle-down text-xs transition-transform group-hover:rotate-180"></i>
                </button>
                <div class="invisible absolute end-0 top-full z-50 mt-2 w-44 origin-top scale-95 opacity-0 transition-all duration-200 group-hover:visible group-hover:scale-100 group-hover:opacity-100">
                    <div class="space-y-1 rounded-lg border border-gray-200 bg-white p-2 shadow-lg">
                        <a href="{{ url('/site-locale/en') }}"
                            class="block rounded-md px-3 py-2 text-sm hover:bg-gray-100 {{ app()->getLocale() == 'en' ? 'text-site-primary font-bold' : '' }}">🇬🇧 English</a>
                        <a href="{{ url('/site-locale/ar') }}"
                            class="block rounded-md px-3 py-2 text-right text-sm hover:bg-gray-100 {{ app()->getLocale() == 'ar' ? 'text-site-primary font-bold' : '' }}">العربية 🇸🇦</a>
                    </div>
                </div>
            </div>

            <div class="ms-3 hidden items-center lg:flex">
                <a href="/app/login"
                    class="bg-site-primary inline-flex items-center rounded px-4 py-2 text-sm text-white">{{ __('website.login') }}</a>
            </div>

            <div class="ms-auto flex items-center px-2.5 lg:hidden">
                <button type="button" id="mobileMenuBtn" class="mobile-menu-btn" aria-controls="mobileMenu"
                    aria-expanded="false" aria-label="{{ __('website.open_menu') }}">
                    <i class="fa-solid fa-bars text-2xl text-gray-500"></i>
                </button>
            </div>
        </nav>
    </div>
</header>

<div id="mobileMenu"
    class="fixed end-0 top-0 z-50 hidden h-full w-full max-w-md border-s bg-white" aria-hidden="true">
    <div class="flex h-full flex-col divide-y-2 divide-gray-200">
        <div class="flex items-center justify-between p-6">
            <a href="{{ route('site.home') }}">
                <img src="{{ config('config.assets.icon') }}" class="h-16" alt="{{ __('website.site_logo') }}">
            </a>

            <button type="button" id="closeMobileMenu" class="flex items-center px-2"
                aria-label="{{ __('website.close_menu') }}">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <nav class="h-full overflow-y-auto p-6" aria-label="{{ __('website.open_menu') }}">
            <ul class="navbar-nav flex flex-col gap-2">

                @foreach ($headerMenus as $menu)
                    @if ($menu->children->count())
                        <li class="nav-item">
                            <button type="button"
                                class="nav-link mobile-dropdown-btn flex w-full items-center justify-between"
                                data-menu="{{ $menu->slug }}" aria-expanded="false">
                                {{ $menu->name }} <i class="fa-solid fa-angle-down transition-transform"></i>
                            </button>

                            <ul class="mobile-dropdown hidden space-y-2 overflow-hidden transition-all duration-300"
                                data-menu="{{ $menu->slug }}">
                                @foreach ($menu->children as $child)
                                    <li class="nav-item mt-2">
                                        @if ($child->is_external)
                                            <a class="nav-link ms-4 block ps-8"
                                                href="{{ $child->url }}">{{ $child->name }}</a>
                                        @else
                                            <a class="nav-link ms-4 block ps-8"
                                                href="{{ $child->url }}">{{ $child->name }}</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            @if ($menu->is_external)
                                <a class="nav-link" href="{{ $menu->url }}">{{ $menu->name }}</a>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        {{-- Language switcher (mobile) --}}
        <div class="flex items-center gap-2 px-6 pt-6">
            <a href="{{ url('/site-locale/en') }}"
                class="flex-1 rounded-lg border px-3 py-2 text-center text-sm {{ app()->getLocale() == 'en' ? 'border-site-primary text-site-primary font-bold' : 'border-gray-200' }}">🇬🇧 English</a>
            <a href="{{ url('/site-locale/ar') }}"
                class="flex-1 rounded-lg border px-3 py-2 text-center text-sm {{ app()->getLocale() == 'ar' ? 'border-site-primary text-site-primary font-bold' : 'border-gray-200' }}">العربية 🇸🇦</a>
        </div>

        <div class="flex items-center justify-center p-6">
            <a href="/app/login"
                class="bg-site-primary flex w-full items-center justify-center rounded p-3 text-sm text-white">{{ __('website.login') }}</a>
        </div>
    </div>
</div>

<script>
    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileDropdownBtns = document.querySelectorAll('.mobile-dropdown-btn');

        function closeMenu({ restoreFocus = true } = {}) {
            mobileMenu.classList.add('hidden');
            mobileMenu.setAttribute('aria-hidden', 'true');
            mobileMenuBtn.setAttribute('aria-expanded', 'false');

            if (restoreFocus) {
                mobileMenuBtn.focus();
            }
        }

        // Toggle mobile menu
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.remove('hidden');
            mobileMenu.setAttribute('aria-hidden', 'false');
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
            closeMobileMenu.focus();
        });

        // Close mobile menu
        closeMobileMenu.addEventListener('click', function() {
            closeMenu();
        });

        // Mobile dropdown functionality
        mobileDropdownBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const menuSlug = this.getAttribute('data-menu');
                const dropdown = document.querySelector(
                    `.mobile-dropdown[data-menu="${menuSlug}"]`);
                const icon = this.querySelector('i');

                // Close all other dropdowns first
                mobileDropdownBtns.forEach(otherBtn => {
                    if (otherBtn !== btn) {
                        const otherMenuSlug = otherBtn.getAttribute('data-menu');
                        const otherDropdown = document.querySelector(
                            `.mobile-dropdown[data-menu="${otherMenuSlug}"]`);
                        const otherIcon = otherBtn.querySelector('i');

                        if (otherDropdown && !otherDropdown.classList.contains(
                                'hidden')) {
                            otherDropdown.classList.add('hidden');
                            otherIcon.style.transform = 'rotate(0deg)';
                            otherBtn.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                // Toggle current dropdown
                if (dropdown.classList.contains('hidden')) {
                    dropdown.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';
                    this.setAttribute('aria-expanded', 'true');
                } else {
                    dropdown.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';
                    this.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                closeMenu({
                    restoreFocus: false
                });
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                closeMenu();
            }
        });
    });
</script>
