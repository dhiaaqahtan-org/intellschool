@extends('saas::marketing.layouts.app')

@section('title', __('saas::marketing.signup.title').' — '.config('saas.brand.name'))
@section('description', __('saas::marketing.signup.description', ['brand' => config('saas.brand.name'), 'days' => config('saas.billing.trial_days', 14)]))

@section('content')
<section style="padding: 4rem 1.5rem; max-width: 640px; margin: 0 auto;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; text-align: center;">
        {{ __('saas::marketing.signup.heading') }}
    </h1>
    <p style="text-align: center; color: #6b7280; margin-bottom: 2.5rem;">
        {{ __('saas::marketing.signup.lede', ['days' => config('saas.billing.trial_days', 14)]) }}
    </p>

    @unless($signupAvailable)
        <div class="alert alert--err" role="status" style="margin-block: 2rem;">
            <strong>{{ __('saas::marketing.signup.unavailable_title') }}</strong>
            <p>{{ __('saas::marketing.signup.unavailable_body') }}</p>
        </div>
        <a class="btn btn--accent btn--block" href="{{ route('saas.marketing.demo') }}">
            {{ __('saas::marketing.signup.request_demo') }}
        </a>
    @else

    @if($errors->any())
        <div id="signup-errors" class="alert alert--err" role="alert" tabindex="-1" data-error-summary style="margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-inline-start: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('saas.marketing.signup.register') }}" data-submit-guard>
        @csrf

        <div style="margin-bottom: 1.5rem;">
            <label for="school_name" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                {{ __('saas::marketing.signup.school_name') }} *
            </label>
            <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" required
                   placeholder="{{ __('saas::marketing.signup.school_placeholder') }}"
                   autocomplete="organization" @error('school_name') aria-invalid="true" aria-describedby="school-name-error" @enderror
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
            @error('school_name')<span id="school-name-error" class="err" style="display:block;margin-top:.35rem;">{{ $message }}</span>@enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="owner_name" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                {{ __('saas::marketing.signup.owner_name') }} *
            </label>
            <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required
                   placeholder="{{ __('saas::marketing.signup.owner_placeholder') }}"
                   autocomplete="name" @error('owner_name') aria-invalid="true" aria-describedby="owner-name-error" @enderror
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
            @error('owner_name')<span id="owner-name-error" class="err" style="display:block;margin-top:.35rem;">{{ $message }}</span>@enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                {{ __('saas::marketing.signup.email') }} *
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   placeholder="{{ __('saas::marketing.signup.email_placeholder') }}"
                   inputmode="email" autocomplete="email"
                   @error('email') aria-invalid="true" aria-describedby="email-hint email-error" @else aria-describedby="email-hint" @enderror
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
            <p id="email-hint" style="font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem;">
                {{ __('saas::marketing.signup.email_hint') }}
            </p>
            @error('email')<span id="email-error" class="err" style="display:block;margin-top:.35rem;">{{ $message }}</span>@enderror
        </div>

        @if($plans->count() > 0)
            <fieldset style="border:0;padding:0;margin:0 0 1.5rem;min-inline-size:0;">
                <legend style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                    {{ __('saas::marketing.signup.plan') }}
                </legend>
                <div style="display: grid; gap: 0.75rem;">
                    @foreach($plans as $plan)
                        <label class="signup-plan-option">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" required
                                   @error('plan_id') aria-invalid="true" aria-describedby="plan-error" @enderror
                                   {{ old('plan_id', $plans->first()?->id) == $plan->id ? 'checked' : '' }}>
                            <div>
                                <strong>{{ $plan->display_name }}</strong>
                                @if($plan->price_cents)
                                    <span style="color: #6b7280;"> — {{ \Illuminate\Support\Number::currency($plan->price_cents / 100, in: $plan->currency, locale: app()->getLocale()) }}/{{ __("saas::marketing.signup.intervals.".($plan->billing_interval ?? 'monthly')) }}</span>
                                @else
                                    <span style="color: #6b7280;"> — {{ __('saas::marketing.signup.custom_pricing') }}</span>
                                @endif
                                <br>
                                <small style="color: #6b7280;">{{ $plan->description ?? '' }}</small>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('plan_id')<span id="plan-error" class="err" style="display:block;margin-top:.35rem;">{{ $message }}</span>@enderror
            </fieldset>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label for="locale" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                    {{ __('saas::marketing.signup.language') }}
                </label>
                <select id="locale" name="locale"
                        style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
                    @foreach (config('localizer.supported_locales', ['en', 'ar']) as $supportedLocale)
                        <option value="{{ $supportedLocale }}" {{ old('locale', app()->getLocale()) === $supportedLocale ? 'selected' : '' }}>{{ __("saas::marketing.signup.languages.$supportedLocale") }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="timezone" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                    {{ __('saas::marketing.signup.timezone') }}
                </label>
                <select id="timezone" name="timezone"
                        style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="UTC" {{ old('timezone', 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="Asia/Riyadh" {{ old('timezone') === 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                    <option value="Asia/Dubai" {{ old('timezone') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                    <option value="Asia/Karachi" {{ old('timezone') === 'Asia/Karachi' ? 'selected' : '' }}>Asia/Karachi</option>
                    <option value="Africa/Cairo" {{ old('timezone') === 'Africa/Cairo' ? 'selected' : '' }}>Africa/Cairo</option>
                    <option value="Asia/Amman" {{ old('timezone') === 'Asia/Amman' ? 'selected' : '' }}>Asia/Amman</option>
                    <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                    <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                </select>
            </div>
        </div>

        <button type="submit" data-submit-button
                data-pending-label="{{ __('saas::marketing.signup.submitting') }}"
                aria-describedby="signup-submit-note"
                class="btn btn--accent btn--lg btn--block">
            {{ __('saas::marketing.signup.submit', ['days' => config('saas.billing.trial_days', 14)]) }}
        </button>

        <p id="signup-submit-note" style="text-align: center; font-size: 0.8rem; color: #6b7280; margin-top: 1rem;">
            {{ __('saas::marketing.signup.agreement_prefix') }}
            <a href="{{ route('saas.marketing.legal.terms') }}" style="color: #2563eb;">{{ __('saas::marketing.signup.terms') }}</a>
            {{ __('saas::marketing.signup.agreement_join') }}
            <a href="{{ route('saas.marketing.legal.privacy') }}" style="color: #2563eb;">{{ __('saas::marketing.signup.privacy') }}</a>{{ __('saas::marketing.signup.agreement_suffix') }}
        </p>
    </form>
    @endunless
</section>
@endsection
