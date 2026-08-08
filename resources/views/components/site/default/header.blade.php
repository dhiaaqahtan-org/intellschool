@php
    $school = config('config.general.app_name', config('app.name', 'Our School'));
    $localize = fn ($value) => \App\Support\LocalizedContent::get($value);
@endphp

<div class="school-topbar">
    <div class="school-shell school-topbar__inner">
        <div class="school-topbar__contact">
            @if (config('config.general.app_phone'))
                <a href="tel:{{ config('config.general.app_phone') }}">
                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                    <bdi>{{ config('config.general.app_phone') }}</bdi>
                </a>
            @endif
            @if (config('config.general.app_email'))
                <a href="mailto:{{ config('config.general.app_email') }}">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                    <bdi>{{ config('config.general.app_email') }}</bdi>
                </a>
            @endif
        </div>

        <div class="school-topbar__tools">
            <a href="/app/payment">{{ __('website.online_fee_payment') }}</a>
            <a href="/app/online-registration">{{ __('website.online_registration') }}</a>
            <div class="school-topbar__social" aria-label="{{ $localize('تابعنا || Follow us') }}">
                @if (config('config.social_network.facebook'))
                    <a href="{{ config('config.social_network.facebook') }}" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                @endif
                @if (config('config.social_network.twitter'))
                    <a href="{{ config('config.social_network.twitter') }}" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                @endif
                @if (config('config.social_network.linkedin'))
                    <a href="{{ config('config.social_network.linkedin') }}" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                @endif
                @if (config('config.social_network.youtube'))
                    <a href="{{ config('config.social_network.youtube') }}" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                @endif
            </div>
        </div>
    </div>
</div>

<header id="navbar" class="school-header">
    <div class="school-shell school-header__inner">
        <a class="school-brand" href="{{ route('site.home') }}" aria-label="{{ $localize($school) }}">
            <img src="{{ config('config.assets.logo') }}" alt="" width="190" height="64">
            <span>{{ $localize($school) }}</span>
        </a>

        <nav class="school-nav" aria-label="{{ $localize('التنقل الرئيسي || Main navigation') }}">
            <ul>
                @foreach ($headerPages as $page)
                    @php
                        $pageLabel = $localize($page->name);
                        $isActive = request()->url() === $page->url;
                    @endphp
                    <li>
                        <a href="{{ $page->url }}" @if ($isActive) aria-current="page" @endif>{{ $pageLabel }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="school-header__actions">
            <div class="school-language">
                <button type="button" aria-label="{{ $localize('تغيير اللغة || Change language') }}" aria-expanded="false">
                    <i class="fa-solid fa-globe" aria-hidden="true"></i>
                    <span>{{ app()->isLocale('ar') ? 'العربية' : 'English' }}</span>
                    <i class="fa-solid fa-angle-down" aria-hidden="true"></i>
                </button>
                <div class="school-language__menu">
                    <a href="{{ url('/site-locale/ar') }}" lang="ar">العربية</a>
                    <a href="{{ url('/site-locale/en') }}" lang="en">English</a>
                </div>
            </div>
            <a class="school-login" href="/app/login">{{ __('website.login') }}</a>
            <a class="school-apply" href="/app/online-registration">{{ $localize('قدّم الآن || Apply now') }}</a>
        </div>

        <button
            id="mobileMenuBtn"
            class="school-mobile-toggle"
            type="button"
            aria-controls="mobileMenu"
            aria-expanded="false"
            aria-label="{{ __('website.open_menu') }}">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
    </div>
</header>

<div id="mobileMenu" class="school-mobile-menu" aria-hidden="true">
    <div class="school-mobile-menu__head">
        <a class="school-brand" href="{{ route('site.home') }}">
            <img src="{{ config('config.assets.icon') }}" alt="" width="56" height="56">
            <span>{{ $localize($school) }}</span>
        </a>
        <button id="closeMobileMenu" type="button" aria-label="{{ __('website.close_menu') }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <nav aria-label="{{ $localize('التنقل عبر الهاتف || Mobile navigation') }}">
        <ul>
            @foreach ($headerPages as $page)
                <li>
                    <a href="{{ $page->url }}" @if (request()->url() === $page->url) aria-current="page" @endif>
                        {{ $localize($page->name) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="school-mobile-menu__actions">
        <div class="school-mobile-menu__languages">
            <a href="{{ url('/site-locale/ar') }}" lang="ar">العربية</a>
            <a href="{{ url('/site-locale/en') }}" lang="en">English</a>
        </div>
        <a class="school-apply" href="/app/online-registration">{{ $localize('قدّم الآن || Apply now') }}</a>
        <a class="school-login" href="/app/login">{{ __('website.login') }}</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menu = document.getElementById('mobileMenu');
        const openButton = document.getElementById('mobileMenuBtn');
        const closeButton = document.getElementById('closeMobileMenu');
        const languageButtons = document.querySelectorAll('.school-language > button');

        const closeMenu = (restoreFocus = true) => {
            menu.classList.remove('is-open');
            menu.setAttribute('aria-hidden', 'true');
            openButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('school-menu-open');
            if (restoreFocus) openButton.focus();
        };

        openButton.addEventListener('click', () => {
            menu.classList.add('is-open');
            menu.setAttribute('aria-hidden', 'false');
            openButton.setAttribute('aria-expanded', 'true');
            document.body.classList.add('school-menu-open');
            requestAnimationFrame(() => closeButton.focus());
        });

        closeButton.addEventListener('click', () => closeMenu());

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && menu.classList.contains('is-open')) {
                closeMenu();
            }

            if (event.key === 'Tab' && menu.classList.contains('is-open')) {
                const focusable = [...menu.querySelectorAll('a[href], button:not([disabled])')];
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        languageButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const expanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', String(!expanded));
                button.parentElement.classList.toggle('is-open', !expanded);
            });
        });
    });
</script>
