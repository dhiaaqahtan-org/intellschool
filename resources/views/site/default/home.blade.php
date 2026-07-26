@php
    $school = config('config.general.app_name', config('app.name', 'Our School'));
    $title = \App\Support\LocalizedContent::get($page->title);
    $subTitle = \App\Support\LocalizedContent::get($page->sub_title);
    $heroImage = \Illuminate\Support\Arr::get($page->assets, 'cover')
        ? $page->cover_image
        : asset('images/school/home-hero.webp');
    $hasCta = (bool) $page->getMeta('has_cta');
@endphp

<x-site.default.layout
    :meta-title="$metaTitle ?: $title"
    :meta-description="$metaDescription ?: $subTitle"
    :meta-keywords="$metaKeywords">

    <section class="school-hero" aria-labelledby="school-hero-title">
        <img
            class="school-hero__image"
            src="{{ $heroImage }}"
            alt=""
            width="1800"
            height="900"
            fetchpriority="high">
        <div class="school-hero__shade" aria-hidden="true"></div>
        <div class="school-shell school-hero__content">
            <p class="school-hero__eyebrow">{{ \App\Support\LocalizedContent::get('الرئيسية / البرامج الأكاديمية || Home / Academics') }}</p>
            <h1 id="school-hero-title">{{ $title }}</h1>
            @if ($subTitle)
                <p class="school-hero__lead">{{ $subTitle }}</p>
            @endif
        </div>
    </section>

    <div class="school-home-content">
        @foreach ($parts as $part)
            @if ($part['type'] === 'html')
                {!! $part['content'] !!}
            @elseif ($part['type'] === 'youtube')
                <section class="school-section">
                    <div class="school-shell">
                        <div class="school-video">
                            <iframe
                                src="https://www.youtube.com/embed/{{ $part['content'] }}"
                                title="{{ \App\Support\LocalizedContent::get('فيديو المدرسة || School video') }}"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                </section>
            @endif
        @endforeach
    </div>

    @if ($hasCta)
        <section class="school-final-cta" style="--school-cta-image: url('{{ $heroImage }}')">
            <div class="school-shell school-final-cta__inner">
                <div>
                    <h2>{{ \App\Support\LocalizedContent::get($page->getMeta('cta_title')) }}</h2>
                    <p>{{ \App\Support\LocalizedContent::get($page->getMeta('cta_description')) }}</p>
                </div>
                <a class="school-button school-button--gold" href="{{ $page->getMeta('cta_button_link') }}">
                    {{ \App\Support\LocalizedContent::get($page->getMeta('cta_button_text')) }}
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </section>
    @endif

</x-site.default.layout>
