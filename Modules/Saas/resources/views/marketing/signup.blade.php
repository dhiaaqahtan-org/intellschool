@extends('saas::marketing.layouts.app')

@section('title', 'Start Your Free Trial — ' . config('saas.brand.name'))
@section('description', 'Create your school on ' . config('saas.brand.name') . '. Free ' . config('saas.billing.trial_days', 14) . '-day trial, no credit card required.')

@section('content')
<section style="padding: 4rem 1.5rem; max-width: 640px; margin: 0 auto;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; text-align: center;">
        Start Your Free Trial
    </h1>
    <p style="text-align: center; color: #6b7280; margin-bottom: 2.5rem;">
        {{ config('saas.billing.trial_days', 14) }} days free. No credit card required. Set up in minutes.
    </p>

    @if($errors->any())
        <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-inline-start: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('saas.marketing.signup.register') }}">
        @csrf

        <div style="margin-bottom: 1.5rem;">
            <label for="school_name" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                School Name *
            </label>
            <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" required
                   placeholder="e.g. Al-Noor International School"
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="owner_name" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                Your Name *
            </label>
            <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required
                   placeholder="e.g. Ahmed Al-Rashid"
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                Work Email *
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   placeholder="you@school.edu"
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
            <p style="font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem;">
                This will be your login and the primary contact for your school.
            </p>
        </div>

        @if($plans->count() > 0)
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                    Plan
                </label>
                <div style="display: grid; gap: 0.75rem;">
                    @foreach($plans as $plan)
                        <label style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; cursor: pointer; transition: border-color 0.2s;"
                               onmouseover="this.style.borderColor='#2563eb'" onmouseout="this.style.borderColor='#d1d5db'">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                   {{ old('plan_id', $plans->first()?->id) == $plan->id ? 'checked' : '' }}>
                            <div>
                                <strong>{{ $plan->display_name }}</strong>
                                @if($plan->price_cents)
                                    <span style="color: #6b7280;"> — ${{ number_format($plan->price_cents / 100, 0) }}/{{ $plan->billing_interval ?? 'mo' }}</span>
                                @else
                                    <span style="color: #6b7280;"> — Custom pricing</span>
                                @endif
                                <br>
                                <small style="color: #6b7280;">{{ $plan->description ?? '' }}</small>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label for="locale" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                    Language
                </label>
                <select id="locale" name="locale"
                        style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="en" {{ old('locale', 'en') === 'en' ? 'selected' : '' }}>English</option>
                    <option value="ar" {{ old('locale') === 'ar' ? 'selected' : '' }}>العربية</option>
                    <option value="fr" {{ old('locale') === 'fr' ? 'selected' : '' }}>Français</option>
                    <option value="ur" {{ old('locale') === 'ur' ? 'selected' : '' }}>اردو</option>
                </select>
            </div>
            <div>
                <label for="timezone" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">
                    Timezone
                </label>
                <select id="timezone" name="timezone"
                        style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="UTC">UTC</option>
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

        <button type="submit"
                style="width: 100%; padding: 1rem; background: #2563eb; color: white; border: none; border-radius: 0.5rem; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
            Create My School — Free for {{ config('saas.billing.trial_days', 14) }} Days
        </button>

        <p style="text-align: center; font-size: 0.8rem; color: #6b7280; margin-top: 1rem;">
            By signing up, you agree to our
            <a href="{{ route('saas.marketing.legal.terms') }}" style="color: #2563eb;">Terms of Service</a> and
            <a href="{{ route('saas.marketing.legal.privacy') }}" style="color: #2563eb;">Privacy Policy</a>.
        </p>
    </form>
</section>
@endsection
