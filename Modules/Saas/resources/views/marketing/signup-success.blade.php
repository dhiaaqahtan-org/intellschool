@extends('saas::marketing.layouts.app')

@section('title', 'Welcome Aboard! — ' . config('saas.brand.name'))

@section('content')
<section style="padding: 5rem 1.5rem; max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 1.5rem;">🎉</div>

    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">
        Your School is Being Created!
    </h1>

    <p style="color: #6b7280; font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.7;">
        We're provisioning your dedicated school environment right now.
        This typically takes 1–2 minutes.
    </p>

    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 2rem; margin-bottom: 2rem; text-align: start;">
        <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">What happens next:</h2>
        <ol style="color: #374151; line-height: 2; padding-inline-start: 1.25rem;">
            <li>Your dedicated database and subdomain are being created</li>
            <li>
                @if(session('owner_email'))
                    A setup invitation will be sent to <strong>{{ session('owner_email') }}</strong>
                @else
                    Check your email for a setup invitation
                @endif
            </li>
            <li>Click the link to set your password and complete your school setup</li>
            <li>Start adding students, staff, and academic data</li>
        </ol>
    </div>

    @if(session('tenant_slug'))
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
            <p style="font-size: 0.9rem; color: #1e40af; margin: 0;">
                Your school URL will be:
                <strong>{{ session('tenant_slug') }}.{{ config('saas.hosts.tenant_suffix', 'school.example.com') }}</strong>
            </p>
        </div>
    @endif

    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('saas.marketing.home') }}"
           style="padding: 0.75rem 1.5rem; background: #2563eb; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
            Back to Home
        </a>
        <a href="{{ route('saas.marketing.demo') }}"
           style="padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
            Request a Demo
        </a>
    </div>

    <p style="margin-top: 2rem; font-size: 0.85rem; color: #9ca3af;">
        Didn't receive an email? Check your spam folder or contact support.
    </p>
</section>
@endsection
