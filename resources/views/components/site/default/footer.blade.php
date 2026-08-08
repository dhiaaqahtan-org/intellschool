@php
    $school = config('config.general.app_name', config('app.name', 'Our School'));
    $localize = fn ($value) => \App\Support\LocalizedContent::get($value);
    $description = $localize(config('config.general.meta_description', 'تعليم يلهم المعرفة والشخصية والإبداع || Education that inspires knowledge, character and creativity.'));
@endphp

<footer class="school-footer">
    <div class="school-shell school-footer__grid">
        <div class="school-footer__brand">
            <a class="school-brand" href="{{ route('site.home') }}">
                <img src="{{ config('config.assets.logo') }}" alt="" width="180" height="58">
                <span>{{ $localize($school) }}</span>
            </a>
            <p>{{ $description }}</p>
            <div class="school-footer__social">
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

        <div>
            <h2>{{ $localize('روابط سريعة || Quick links') }}</h2>
            <ul>
                @forelse ($footerPages as $page)
                    <li><a href="{{ $page->url }}">{{ $localize($page->name) }}</a></li>
                @empty
                    <li><a href="{{ route('site.home') }}">{{ $localize('الرئيسية || Home') }}</a></li>
                    <li><a href="/pages/contact">{{ $localize('تواصل معنا || Contact us') }}</a></li>
                @endforelse
            </ul>
        </div>

        <div>
            <h2>{{ $localize('الخدمات الإلكترونية || Online services') }}</h2>
            <ul>
                <li><a href="/app/online-registration">{{ __('website.online_registration') }}</a></li>
                <li><a href="/app/payment">{{ __('website.online_fee_payment') }}</a></li>
                <li><a href="/app/transfer-certificate">{{ $localize('التحقق من الشهادة || Verify certificate') }}</a></li>
                <li><a href="/app/login">{{ __('website.login') }}</a></li>
            </ul>
        </div>

        <div>
            <h2>{{ $localize('تواصل معنا || Contact us') }}</h2>
            <ul class="school-footer__contact">
                @if (config('config.general.app_address'))
                    <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>{{ config('config.general.app_address') }}</span></li>
                @endif
                @if (config('config.general.app_phone'))
                    <li><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="tel:{{ config('config.general.app_phone') }}"><bdi>{{ config('config.general.app_phone') }}</bdi></a></li>
                @endif
                @if (config('config.general.app_email'))
                    <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:{{ config('config.general.app_email') }}"><bdi>{{ config('config.general.app_email') }}</bdi></a></li>
                @endif
            </ul>
        </div>
    </div>

    <div class="school-shell school-footer__bottom">
        <span>&copy; {{ now()->year }} {{ $localize($school) }}. {{ __('website.all_rights_reserved') }}.</span>
        <a href="#main-content">{{ $localize('العودة إلى الأعلى || Back to top') }} <i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
    </div>
</footer>
