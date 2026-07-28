<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('saas::marketing.signup.invitation.email_subject', ['school' => $schoolName]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <h1>{{ __('saas::marketing.signup.invitation.email_heading', ['school' => $schoolName]) }}</h1>
    <p>{{ __('saas::marketing.signup.invitation.email_greeting', ['name' => $ownerName]) }}</p>
    <p>{{ __('saas::marketing.signup.invitation.email_body', ['days' => $expiresInDays]) }}</p>
    <p>
        <a href="{{ $invitationUrl }}" style="display: inline-block; padding: 12px 20px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px;">
            {{ __('saas::marketing.signup.invitation.email_action') }}
        </a>
    </p>
    <p>{{ __('saas::marketing.signup.invitation.email_ignore') }}</p>
</body>
</html>
