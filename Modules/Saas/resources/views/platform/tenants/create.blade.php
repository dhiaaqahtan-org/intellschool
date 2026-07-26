@extends('saas::platform.layouts.app')

@section('title', 'New Tenant')

@section('content')
<div class="page-header">
    <h1>Create New Tenant</h1>
    <p>Provision a new school on the platform. A database, subdomain, and initial schema will be created automatically.</p>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('saas.platform.tenants.store') }}">
        @csrf

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                School Name <span style="color: var(--color-danger);">*</span>
            </label>
            <input type="text" name="display_name" value="{{ old('display_name') }}" required
                   placeholder="e.g. Al-Noor International School"
                   style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
            @error('display_name')
                <p style="color: var(--color-danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                Slug
            </label>
            <input type="text" name="slug" value="{{ old('slug') }}"
                   placeholder="auto-generated from name if left empty"
                   style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
            <p style="color: var(--color-gray-500); font-size: 0.75rem; margin-top: 0.25rem;">
                Used in the subdomain: <code>{slug}.{{ config('saas.hosts.tenant_suffix', 'school.example.com') }}</code>
            </p>
            @error('slug')
                <p style="color: var(--color-danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                Legal Name
            </label>
            <input type="text" name="legal_name" value="{{ old('legal_name') }}"
                   placeholder="Official registered name (optional)"
                   style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                    Locale
                </label>
                <select name="locale" style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
                    <option value="en" {{ old('locale', 'en') === 'en' ? 'selected' : '' }}>English</option>
                    <option value="ar" {{ old('locale') === 'ar' ? 'selected' : '' }}>Arabic</option>
                    <option value="fr" {{ old('locale') === 'fr' ? 'selected' : '' }}>French</option>
                    <option value="ur" {{ old('locale') === 'ur' ? 'selected' : '' }}>Urdu</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                    Timezone
                </label>
                <select name="timezone" style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
                    <option value="UTC" {{ old('timezone', 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="Asia/Riyadh" {{ old('timezone') === 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                    <option value="Asia/Dubai" {{ old('timezone') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                    <option value="Asia/Karachi" {{ old('timezone') === 'Asia/Karachi' ? 'selected' : '' }}>Asia/Karachi</option>
                    <option value="Africa/Cairo" {{ old('timezone') === 'Africa/Cairo' ? 'selected' : '' }}>Africa/Cairo</option>
                    <option value="Asia/Amman" {{ old('timezone') === 'Asia/Amman' ? 'selected' : '' }}>Asia/Amman</option>
                    <option value="Asia/Baghdad" {{ old('timezone') === 'Asia/Baghdad' ? 'selected' : '' }}>Asia/Baghdad</option>
                    <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                    <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                    Region
                </label>
                <select name="region" style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
                    <option value="">Select region...</option>
                    <option value="me" {{ old('region') === 'me' ? 'selected' : '' }}>Middle East</option>
                    <option value="gcc" {{ old('region') === 'gcc' ? 'selected' : '' }}>GCC</option>
                    <option value="sa" {{ old('region') === 'sa' ? 'selected' : '' }}>Saudi Arabia</option>
                    <option value="ae" {{ old('region') === 'ae' ? 'selected' : '' }}>UAE</option>
                    <option value="eg" {{ old('region') === 'eg' ? 'selected' : '' }}>Egypt</option>
                    <option value="pk" {{ old('region') === 'pk' ? 'selected' : '' }}>Pakistan</option>
                    <option value="eu" {{ old('region') === 'eu' ? 'selected' : '' }}>Europe</option>
                    <option value="na" {{ old('region') === 'na' ? 'selected' : '' }}>North America</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">
                    Tier
                </label>
                <select name="tier" style="width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;">
                    <option value="standard" {{ old('tier', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="premium" {{ old('tier') === 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="enterprise" {{ old('tier') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">Create & Provision Tenant</button>
            <a href="{{ route('saas.platform.tenants.index') }}" class="btn" style="background: var(--color-gray-200); color: var(--color-gray-700);">Cancel</a>
        </div>
    </form>
</div>
@endsection
