{{--
    Product-tour showcase — EMPTY, labelled screenshot slots.

    Each frame points at a file under public/images/saas-showcase/. Drop an image
    named web-<key>.png (desktop) there and it fills the frame automatically; while
    the file is absent the slot falls back to a clearly-marked empty placeholder
    (see the img onerror handler). Nothing here fabricates a claim — the slots are
    visibly empty until a real capture is supplied.
--}}
@php
    $webShots = ['dashboard', 'students', 'finance', 'exams'];
    $shotBase = 'images/saas-showcase';
@endphp

@push('head')
<style>
/* Product-tour slots — reuse the marketing design tokens (see marketing.css). */
.shots { display: grid; gap: var(--sp-5); grid-template-columns: repeat(2, 1fr); margin-block-start: var(--sp-6); }
@media (max-width: 780px) { .shots { grid-template-columns: 1fr; } }

.shot { border-radius: var(--r-lg); overflow: hidden; background: var(--c-surface); border: 1px solid var(--c-border); box-shadow: var(--e-3); }
.shot__bar { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--c-ink-2); }
.shot__dots { display: flex; gap: 6px; }
.shot__dots i { inline-size: 10px; block-size: 10px; border-radius: 50%; background: rgba(255,255,255,.22); }
.shot__url { margin-inline: auto; padding: 3px 14px; border-radius: var(--r-full); background: rgba(255,255,255,.08); color: var(--c-ink-muted); font-size: var(--fs-xs); font-weight: 600; }
.shot__frame { position: relative; aspect-ratio: 16 / 10; display: grid; place-items: center; background: var(--c-surface-2); }
.shot__frame img { grid-area: 1 / 1; inline-size: 100%; block-size: 100%; object-fit: cover; display: block; }
.shot__empty { grid-area: 1 / 1; display: none; text-align: center; padding: var(--sp-5); color: var(--c-muted); }
.shot--empty .shot__frame {
    background: repeating-linear-gradient(45deg, var(--c-surface-2), var(--c-surface-2) 12px, var(--c-surface) 12px, var(--c-surface) 24px);
    border-block-start: 1px dashed var(--c-border-strong);
}
.shot--empty .shot__empty { display: block; }
.shot__empty svg { inline-size: 34px; block-size: 34px; opacity: .4; margin-block-end: var(--sp-2); }
.shot__empty b { display: block; font-weight: 700; color: var(--c-fg-strong); font-size: var(--fs-sm); }
.shot__empty code { font-family: var(--font-mono); font-size: var(--fs-xs); color: var(--c-muted); word-break: break-all; }
.shot__cap { padding: var(--sp-3) var(--sp-4); font-size: var(--fs-sm); font-weight: 600; color: var(--c-fg-strong); border-block-start: 1px solid var(--c-border); }

/* Phone frames for the mobile companion app (used by partials/app-shots). */
.phones { display: flex; flex-wrap: wrap; gap: var(--sp-6); justify-content: center; margin-block-start: var(--sp-7); }
.phone { inline-size: 210px; max-inline-size: 46vw; }
.phone__body { position: relative; border-radius: 34px; background: #05070f; padding: 10px; box-shadow: var(--e-4); border: 1px solid var(--c-ink-border); }
.phone__screen { position: relative; border-radius: 26px; overflow: hidden; aspect-ratio: 9 / 19.5; background: var(--c-surface-2); display: grid; place-items: center; }
.phone__screen img { grid-area: 1 / 1; inline-size: 100%; block-size: 100%; object-fit: cover; display: block; }
.phone__notch { position: absolute; inset-block-start: 0; inset-inline-start: 50%; transform: translateX(-50%); inline-size: 44%; block-size: 18px; background: #05070f; border-end-start-radius: 12px; border-end-end-radius: 12px; z-index: 2; }
.phone--empty .phone__screen {
    background: repeating-linear-gradient(45deg, var(--c-surface-2), var(--c-surface-2) 10px, var(--c-surface) 10px, var(--c-surface) 20px);
}
.phone__empty { grid-area: 1 / 1; display: none; text-align: center; padding: var(--sp-4); color: var(--c-muted); font-size: var(--fs-xs); }
.phone--empty .phone__empty { display: block; }
.phone__empty code { font-family: var(--font-mono); display: block; margin-block-start: 4px; word-break: break-all; }
.phone__cap { text-align: center; margin-block-start: var(--sp-3); font-size: var(--fs-sm); font-weight: 600; color: var(--c-fg-strong); }
.section--ink .phone__cap { color: var(--c-ink-fg); }
</style>
@endpush

<section class="section" id="screenshots">
    <div class="wrap">
        <div class="section-head is-center reveal">
            <span class="eyebrow">{{ __('saas::marketing.showcase.eyebrow') }}</span>
            <h2>{{ __('saas::marketing.showcase.title') }}</h2>
            <p>{{ __('saas::marketing.showcase.lede') }}</p>
        </div>

        <div class="shots reveal">
            @foreach ($webShots as $key)
                <figure class="shot shot--empty">
                    <div class="shot__bar">
                        <span class="shot__dots"><i></i><i></i><i></i></span>
                        <span class="shot__url">{{ __('saas::marketing.showcase.web_label') }}</span>
                    </div>
                    <div class="shot__frame">
                        <img src="/{{ $shotBase }}/web-{{ $key }}.png"
                             alt="{{ __("saas::marketing.showcase.web.$key") }}"
                             loading="lazy"
                             onload="this.closest('.shot').classList.remove('shot--empty')"
                             onerror="this.closest('.shot').classList.add('shot--empty');this.remove()">
                        <div class="shot__empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="8.5" cy="9.5" r="1.5"/>
                                <path d="m21 16-5-5L5 20"/>
                            </svg>
                            <b>{{ __('saas::marketing.showcase.empty') }}</b>
                            <code>{{ __('saas::marketing.showcase.empty_hint') }} {{ $shotBase }}/web-{{ $key }}.png</code>
                        </div>
                    </div>
                    <figcaption class="shot__cap">{{ __("saas::marketing.showcase.web.$key") }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
