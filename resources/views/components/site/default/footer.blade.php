@php($school = config('config.general.app_name', config('app.name', 'Our School')))
<footer class="sch-footer">
    <div class="wrap sch-footer__top">

        {{-- Brand --}}
        <div class="sch-footer__brand">
            <div class="logo">
                <img src="{{ config('config.assets.logo') }}" alt="{{ $school }}" onerror="this.style.display='none'">
                <strong style="color:#fff;font-family:var(--serif);font-size:1.1rem;">{{ $school }}</strong>
            </div>
            <p>Empowering students to learn, lead, and make a difference in a changing world.</p>
            <div class="sch-footer__social">
                @if (config('config.social_network.facebook'))<a href="{{ config('config.social_network.facebook') }}" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>@endif
                @if (config('config.social_network.twitter'))<a href="{{ config('config.social_network.twitter') }}" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>@endif
                @if (config('config.social_network.linkedin'))<a href="{{ config('config.social_network.linkedin') }}" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>@endif
                @if (config('config.social_network.youtube'))<a href="{{ config('config.social_network.youtube') }}" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>@endif
            </div>
        </div>

        {{-- Quick Links (CMS footer menu) --}}
        <div>
            <h4>Quick Links</h4>
            <ul>
                @forelse ($footerMenus as $menu)
                    <li><a href="{{ $menu->url }}">{{ $menu->name }}</a></li>
                @empty
                    <li><a href="/pages/about">About</a></li>
                    <li><a href="/pages/academics">Academics</a></li>
                    <li><a href="/pages/admissions">Admissions</a></li>
                    <li><a href="/pages/contact">Contact</a></li>
                @endforelse
            </ul>
        </div>

        {{-- Admissions --}}
        <div>
            <h4>Admissions</h4>
            <ul>
                <li><a href="/pages/admissions">Why {{ $school }}</a></li>
                <li><a href="/pages/admissions">How to Apply</a></li>
                <li><a href="/pages/admissions">Tuition &amp; Fees</a></li>
                <li><a href="/pages/admissions">Scholarships</a></li>
                <li><a href="/pages/contact">Visit Us</a></li>
            </ul>
        </div>

        {{-- Newsletter --}}
        <div class="sch-footer__news">
            <h4>Newsletter</h4>
            <p>Stay updated with the latest news and events.</p>
            <form onsubmit="return false;">
                <input type="email" placeholder="Your email address" aria-label="Email address">
                <button type="submit" class="btn btn-gold" style="justify-content:center;padding:.7rem 1rem;">Subscribe</button>
            </form>
        </div>

        {{-- Contact --}}
        <div>
            <h4>Contact Us</h4>
            <ul class="sch-footer__contact">
                @if (config('config.general.app_address'))<li><i class="fa-solid fa-location-dot"></i><span>{{ config('config.general.app_address') }}</span></li>@endif
                @if (config('config.general.app_phone'))<li><i class="fa-solid fa-phone"></i><a href="tel:{{ config('config.general.app_phone') }}">{{ config('config.general.app_phone') }}</a></li>@endif
                @if (config('config.general.app_email'))<li><i class="fa-solid fa-envelope"></i><a href="mailto:{{ config('config.general.app_email') }}">{{ config('config.general.app_email') }}</a></li>@endif
            </ul>
        </div>
    </div>

    <div class="wrap sch-footer__bottom">
        <span>&copy; {{ now()->year }} {{ $school }}. {{ __('website.all_rights_reserved') }}.</span>
        <span style="display:flex;gap:18px;">
            <a href="/pages/privacy-policy">Privacy Policy</a>
            <a href="/pages/terms">Terms of Use</a>
        </span>
    </div>
</footer>

<button data-toggle="back-to-top"
    type="button" aria-label="{{ __('website.back_to_top') }}"
    class="fixed bottom-5 end-5 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-gray-500 bg-gray-200/20 text-center text-sm text-gray-200">
    <i class="fa-solid fa-arrow-up text-base"></i>
</button>
