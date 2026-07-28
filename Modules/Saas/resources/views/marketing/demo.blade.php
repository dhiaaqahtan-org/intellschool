@extends('saas::marketing.layouts.app')

@section('title', __('saas::marketing.demo.title').' — '.config('saas.brand.name'))
@section('description', __('saas::marketing.demo.lede'))

@push('head')
    {{-- A conversion page has no business being indexed with thin duplicate
         copy, but it must remain crawlable so the CTA target resolves. --}}
    <meta name="robots" content="index,follow">
@endpush

@section('content')

<section class="section section--alt" id="demo">
    <div class="wrap">
        <div class="iso">
            <div class="reveal">
                <span class="eyebrow">{{ __('saas::marketing.demo.eyebrow') }}</span>
                <h2>{{ __('saas::marketing.demo.title') }}</h2>
                <p style="color:var(--c-muted);font-size:var(--fs-lg);margin-block:var(--sp-4) var(--sp-5)">
                    {{ __('saas::marketing.demo.lede') }}
                </p>
                <ul class="list-check">
                    @foreach (__('saas::marketing.demo.points') as $point)
                        <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>{{ $point }}</span></li>
                    @endforeach
                </ul>
            </div>

            <div class="reveal" data-vue-component="demo-form">
                @php
                    $demoProps = [
                        'action' => route('saas.marketing.demo.store'),
                        'csrf' => csrf_token(),
                        'sizes' => $sizes,
                        'privacyUrl' => route('saas.marketing.legal.privacy'),
                        't' => __('saas::marketing.form'),
                    ];
                @endphp
                <script type="application/json" data-props>@json($demoProps)</script>

                {{--
                    Server-rendered baseline. Identical fields, identical
                    endpoint, identical validation — the Vue island only adds
                    pending state, inline errors and a duplicate-submit guard.
                --}}
                <form class="form card" method="post" action="{{ route('saas.marketing.demo.store') }}" novalidate>
                    @csrf

                    @if (session('saas_demo_status'))
                        <p class="alert alert--ok" role="alert">{{ session('saas_demo_status') }}</p>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert--err" role="alert">
                            <p>{{ __('saas::marketing.form.error_validation') }}</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form__row">
                        <div class="field">
                            <label for="demo-name">{{ __('saas::marketing.form.name') }} <span class="req" aria-hidden="true">*</span></label>
                            <input id="demo-name" name="name" type="text" autocomplete="name" required
                                   value="{{ old('name') }}" @error('name') aria-invalid="true" aria-describedby="demo-name-err" @enderror>
                            @error('name')<span id="demo-name-err" class="err">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="demo-school">{{ __('saas::marketing.form.school') }} <span class="req" aria-hidden="true">*</span></label>
                            <input id="demo-school" name="school" type="text" autocomplete="organization" required
                                   value="{{ old('school') }}" @error('school') aria-invalid="true" aria-describedby="demo-school-err" @enderror>
                            @error('school')<span id="demo-school-err" class="err">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form__row">
                        <div class="field">
                            <label for="demo-email">{{ __('saas::marketing.form.email') }} <span class="req" aria-hidden="true">*</span></label>
                            <input id="demo-email" name="email" type="email" inputmode="email" autocomplete="email" required
                                   value="{{ old('email') }}" aria-describedby="demo-email-hint"
                                   @error('email') aria-invalid="true" @enderror>
                            <span id="demo-email-hint" class="hint">{{ __('saas::marketing.form.email_hint') }}</span>
                            @error('email')<span class="err">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="demo-size">{{ __('saas::marketing.form.size') }}</label>
                            <select id="demo-size" name="size">
                                <option value="">{{ __('saas::marketing.form.size_placeholder') }}</option>
                                @foreach ($sizes as $option)
                                    <option value="{{ $option['value'] }}" @selected(old('size') === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="demo-message">{{ __('saas::marketing.form.message') }}</label>
                        <textarea id="demo-message" name="message" rows="4">{{ old('message') }}</textarea>
                    </div>

                    {{-- Honeypot. Hidden from users and assistive tech. --}}
                    <div class="visually-hidden" aria-hidden="true">
                        <label for="demo-website">Website</label>
                        <input id="demo-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <label class="consent">
                        <input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
                        <span>
                            {{ __('saas::marketing.form.consent') }}
                            <a href="{{ route('saas.marketing.legal.privacy') }}">{{ __('saas::marketing.form.privacy_link') }}</a>.
                        </span>
                    </label>
                    @error('consent')<span class="err">{{ $message }}</span>@enderror

                    <button class="btn btn--accent btn--lg btn--block" type="submit">
                        {{ __('saas::marketing.form.submit') }}
                        <svg class="i-arrow" aria-hidden="true"><use href="#i-arrow"></use></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection
