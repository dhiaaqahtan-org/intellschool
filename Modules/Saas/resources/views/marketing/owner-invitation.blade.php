@extends('saas::marketing.layouts.app')

@section('title', __('saas::marketing.signup.invitation.title').' ? '.config('saas.brand.name'))

@section('content')
<section class="signup" aria-labelledby="owner-invitation-title">
    <div class="signup__header">
        <p class="eyebrow">{{ __('saas::marketing.signup.invitation.eyebrow') }}</p>
        <h1 id="owner-invitation-title">{{ __('saas::marketing.signup.invitation.heading', ['school' => $tenant->display_name]) }}</h1>
        <p>{{ __('saas::marketing.signup.invitation.lede', ['email' => $invitation->email]) }}</p>
    </div>

    @if ($errors->any())
        <div class="form-error" role="alert" tabindex="-1" data-error-summary>
            <strong>{{ __('saas::marketing.signup.error_summary') }}</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('saas.marketing.invitation.accept', ['token' => $token]) }}" class="signup__form">
        @csrf

        <div class="field">
            <label for="password">{{ __('saas::marketing.signup.invitation.password') }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required aria-describedby="password-hint" @error('password') aria-invalid="true" @enderror>
            <small id="password-hint">{{ __('saas::marketing.signup.invitation.password_hint') }}</small>
        </div>

        <div class="field">
            <label for="password_confirmation">{{ __('saas::marketing.signup.invitation.password_confirmation') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn btn--primary btn--wide" data-pending-label="{{ __('saas::marketing.signup.invitation.submitting') }}">
            {{ __('saas::marketing.signup.invitation.submit') }}
        </button>
    </form>
</section>
@endsection
