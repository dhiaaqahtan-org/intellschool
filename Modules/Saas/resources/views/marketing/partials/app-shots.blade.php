{{--
    Mobile companion app — EMPTY phone-frame slots. Drop images named
    app-<key>.png into public/images/saas-showcase/ and each fills its screen;
    while absent, the screen shows a marked empty placeholder. CSS lives in
    partials/showcase (pushed to <head>), which also renders on this page.
--}}
@php
    $appShots = ['home', 'attendance', 'timetable'];
    $shotBase = 'images/saas-showcase';
@endphp

<div class="phones reveal">
    @foreach ($appShots as $key)
        <figure class="phone phone--empty">
            <div class="phone__body">
                <div class="phone__screen">
                    <span class="phone__notch" aria-hidden="true"></span>
                    <img src="/{{ $shotBase }}/app-{{ $key }}.png"
                         alt="{{ __("saas::marketing.showcase.app.$key") }}"
                         loading="lazy"
                         onload="this.closest('.phone').classList.remove('phone--empty')"
                         onerror="this.closest('.phone').classList.add('phone--empty');this.remove()">
                    <div class="phone__empty">
                        {{ __('saas::marketing.showcase.empty') }}
                        <code>app-{{ $key }}.png</code>
                    </div>
                </div>
            </div>
            <figcaption class="phone__cap">{{ __("saas::marketing.showcase.app.$key") }}</figcaption>
        </figure>
    @endforeach
</div>
