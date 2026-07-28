@extends('saas::marketing.layouts.app')

@section('title', __('saas::marketing.signup.success.title').' — '.config('saas.brand.name'))

@section('content')
<section style="padding: 5rem 1.5rem; max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 1.5rem;">🎉</div>

    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">
        {{ __('saas::marketing.signup.success.heading') }}
    </h1>

    <p style="color: #6b7280; font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.7;">
        {{ __('saas::marketing.signup.success.lede') }}
    </p>

    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 2rem; margin-bottom: 2rem; text-align: start;">
        <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('saas::marketing.signup.success.next') }}</h2>
        <ol style="color: #374151; line-height: 2; padding-inline-start: 1.25rem;">
            <li>{{ __('saas::marketing.signup.success.database') }}</li>
            <li>
                @if(session('owner_email'))
                    {{ __('saas::marketing.signup.success.email_to', ['email' => session('owner_email')]) }}
                @else
                    {{ __('saas::marketing.signup.success.email_generic') }}
                @endif
            </li>
            <li>{{ __('saas::marketing.signup.success.password') }}</li>
            <li>{{ __('saas::marketing.signup.success.start') }}</li>
        </ol>
    </div>

    @if(session('tenant_slug'))
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
            <p style="font-size: 0.9rem; color: #1e40af; margin: 0;">
                {{ __('saas::marketing.signup.success.school_url') }}
                <strong>{{ session('tenant_slug') }}.{{ config('saas.hosts.tenant_suffix', 'school.example.com') }}</strong>
            </p>
        </div>
    @endif

    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('saas.marketing.home') }}"
           style="padding: 0.75rem 1.5rem; background: #2563eb; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
            {{ __('saas::marketing.signup.success.home') }}
        </a>
        <a href="{{ route('saas.marketing.demo') }}"
           style="padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
            {{ __('saas::marketing.signup.success.demo') }}
        </a>
    </div>

    <p style="margin-top: 2rem; font-size: 0.85rem; color: #9ca3af;">
        {{ __('saas::marketing.signup.success.no_email') }}
    </p>
</section>
@endsection
