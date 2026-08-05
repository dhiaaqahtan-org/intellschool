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
                    @foreach (config('localizer.supported_locales', ['en', 'ar']) as $supportedLocale)
                        <option value="{{ $supportedLocale }}" {{ old('locale', config('app.fallback_locale', 'en')) === $supportedLocale ? 'selected' : '' }}>{{ $supportedLocale === 'ar' ? 'Arabic' : 'English' }}</option>
                    @endforeach
                </select>
                @error('locale') <p style="color: var(--color-danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
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
                @error('timezone') <p style="color: var(--color-danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
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

        @php($fieldStyle = 'width: 100%; padding: 0.625rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 0.375rem; font-size: 0.875rem;')
        @php($errStyle = 'color: var(--color-danger); font-size: 0.75rem; margin-top: 0.25rem;')
        @php($hintStyle = 'color: var(--color-gray-500); font-size: 0.75rem; margin-top: 0.25rem;')

        {{-- Address --}}
        <hr style="margin: 1.75rem 0; border: none; border-top: 1px solid var(--color-gray-200);">
        <h2 style="font-size: 1rem; margin-bottom: 0.25rem;">Address</h2>
        <p style="{{ $hintStyle }} margin-bottom: 1rem;">
            Leave blank to use the subdomain built from the slug above.
        </p>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label for="hostname" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Hostname</label>
                <input id="hostname" type="text" name="hostname" value="{{ old('hostname') }}"
                       placeholder="tamjeed{{ config('saas.hosts.tenant_suffix', '.example.com') }} or tamjeed.com"
                       style="{{ $fieldStyle }}">
                @error('hostname') <p style="{{ $errStyle }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="domain_type" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Type</label>
                <select id="domain_type" name="domain_type" style="{{ $fieldStyle }}">
                    <option value="subdomain" {{ old('domain_type', 'subdomain') === 'subdomain' ? 'selected' : '' }}>Subdomain</option>
                    <option value="custom" {{ old('domain_type') === 'custom' ? 'selected' : '' }}>Their own domain</option>
                </select>
            </div>
        </div>
        <p style="{{ $hintStyle }} margin-bottom: 1.25rem;">
            A subdomain routes immediately. Their own domain gets a verification
            token and stays unroutable until the school publishes the DNS record
            shown on the tenant page — entering it here proves nothing about who
            controls that domain.
        </p>

        {{-- Database --}}
        <hr style="margin: 1.75rem 0; border: none; border-top: 1px solid var(--color-gray-200);">
        <h2 style="font-size: 1rem; margin-bottom: 0.25rem;">Database</h2>
        <p style="{{ $hintStyle }} margin-bottom: 1rem;">
            Leave all three blank on a VPS — the database is created for you.
            On shared hosting create it in hPanel first, then copy the exact
            names here; they carry a forced <code>u000000000_</code> prefix that
            cannot be changed.
        </p>

        <div style="margin-bottom: 1.25rem;">
            <label for="database_name" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Database name</label>
            <input id="database_name" type="text" name="database_name" value="{{ old('database_name') }}"
                   autocomplete="off" placeholder="u123456789_tamjeed" style="{{ $fieldStyle }}">
            @error('database_name') <p style="{{ $errStyle }}">{{ $message }}</p> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
            <div>
                <label for="database_username" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Database username</label>
                <input id="database_username" type="text" name="database_username" value="{{ old('database_username') }}"
                       autocomplete="off" placeholder="u123456789_tamjeed" style="{{ $fieldStyle }}">
                @error('database_username') <p style="{{ $errStyle }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="database_password" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Database password</label>
                {{-- Never re-filled from old() on a validation error: a password
                     echoed back into HTML ends up in caches and page sources. --}}
                <input id="database_password" type="password" name="database_password"
                       autocomplete="new-password" style="{{ $fieldStyle }}">
                @error('database_password') <p style="{{ $errStyle }}">{{ $message }}</p> @enderror
            </div>
        </div>
        <p style="{{ $hintStyle }} margin-bottom: 1.25rem;">
            Fill the username and password only if this database has its own
            MySQL user, which is how hPanel issues them. They are stored
            encrypted and re-entered here if they ever change. Leave both blank
            and the school opens through the shared cluster user instead.
        </p>

        {{-- Owner --}}
        <hr style="margin: 1.75rem 0; border: none; border-top: 1px solid var(--color-gray-200);">
        <h2 style="font-size: 1rem; margin-bottom: 1rem;">School contact <span style="font-weight: 400; color: var(--color-gray-500); font-size: 0.875rem;">(optional)</span></h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label for="owner_name" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Name</label>
                <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}" style="{{ $fieldStyle }}">
            </div>
            <div>
                <label for="owner_email" style="display: block; font-weight: 600; margin-bottom: 0.375rem; font-size: 0.875rem;">Email</label>
                <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" style="{{ $fieldStyle }}">
                @error('owner_email') <p style="{{ $errStyle }}">{{ $message }}</p> @enderror
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">Create & Provision Tenant</button>
            <a href="{{ route('saas.platform.tenants.index') }}" class="btn" style="background: var(--color-gray-200); color: var(--color-gray-700);">Cancel</a>
        </div>
    </form>
</div>
@endsection
