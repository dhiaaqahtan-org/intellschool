@php
    $brand = config('saas.brand.name');
    $legal = config('saas.brand.legal');
    $home  = route('saas.marketing.home');
@endphp

<footer class="site-footer">
    <div class="wrap">
        <div class="footer-grid">
            <div>
                <a class="brand" href="{{ route('saas.marketing.home') }}" style="margin-block-end:var(--sp-4)">
                    <span class="brand__mark" aria-hidden="true"><svg><use href="#i-logo"></use></svg></span>
                    {{ $brand }}
                </a>
                <p>{{ __('saas::marketing.footer.tagline') }}</p>
            </div>

            <div>
                <h4>{{ __('saas::marketing.footer.product') }}</h4>
                <ul>
                    <li><a href="{{ $home }}#platform">{{ __('saas::marketing.nav.platform') }}</a></li>
                    <li><a href="{{ $home }}#modules">{{ __('saas::marketing.nav.modules') }}</a></li>
                    <li><a href="{{ $home }}#roles">{{ __('saas::marketing.footer.links.roles_permissions') }}</a></li>
                    <li><a href="{{ $home }}#pricing">{{ __('saas::marketing.nav.pricing') }}</a></li>
                </ul>
            </div>

            <div>
                <h4>{{ __('saas::marketing.footer.trust') }}</h4>
                <ul>
                    <li><a href="{{ $home }}#isolation">{{ __('saas::marketing.footer.links.security') }}</a></li>
                    <li><a href="{{ route('saas.marketing.legal.privacy') }}">{{ __('saas::marketing.footer.links.privacy') }}</a></li>
                    <li><a href="{{ route('saas.marketing.legal.terms') }}">{{ __('saas::marketing.footer.links.terms') }}</a></li>
                    <li><a href="{{ route('saas.marketing.legal.dpa') }}">{{ __('saas::marketing.footer.links.dpa') }}</a></li>
                </ul>
            </div>

            <div>
                <h4>{{ __('saas::marketing.footer.company') }}</h4>
                <ul>
                    <li><a href="{{ route('saas.marketing.demo') }}">{{ __('saas::marketing.nav.demo') }}</a></li>
                    @if ($email = config('saas.brand.email'))
                        <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                    @endif
                    @if ($phone = config('saas.brand.phone'))
                        <li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>
                &copy; {{ date('Y') }} {{ $legal ?: $brand }}.
                @unless ($legal && config('saas.brand.reg_no') && config('saas.brand.address'))
                    {{ __('saas::marketing.footer.legal_placeholder') }}
                @else
                    {{ config('saas.brand.reg_no') }} · {{ config('saas.brand.address') }}
                @endunless
            </span>

            @unless (app(Modules\Saas\Domain\Website\ClaimGate::class)->pricing())
                <span class="badge badge--soon">{{ __('saas::marketing.footer.preview_badge') }}</span>
            @endunless
        </div>
    </div>
</footer>
