@extends('saas::platform.layouts.app')

@section('title', 'New Tenant')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding-bottom: 3rem;">
    <!-- Page Header & Navigation -->
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('saas.platform.tenants.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #6b7280; text-decoration: none; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; transition: color 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Tenants
            </a>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: #111827; margin: 0; letter-spacing: -0.02em;">Provision New School Tenant</h1>
            <p style="color: #6b7280; margin: 0.25rem 0 0 0; font-size: 0.95rem;">Configure the school details, domain routing, and database allocation to provision an isolated ERP environment.</p>
        </div>
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; border-radius: 0.5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: #991b1b; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                Please correct the errors below:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; color: #b91c1c; font-size: 0.875rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('saas.platform.tenants.store') }}">
        @csrf

        <!-- SECTION 1: BASIC SCHOOL INFORMATION -->
        <div class="card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem;">
                <div style="background: #e0e7ff; color: #4338ca; border-radius: 0.5rem; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V9l8-5 8 5v10M9 19v-6h6v6"/></svg>
                </div>
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">1. Basic School Information</h2>
                    <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Core profile and branding settings for the school.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div style="grid-column: span 2;">
                    <label for="display_name" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">
                        School Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}" required
                           placeholder="e.g. Al-Noor International School"
                           oninput="autoGenerateSlug(this.value)"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s;">
                    @error('display_name') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">
                        Subdomain Slug
                    </label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                           placeholder="e.g. alnoor"
                           oninput="updateLivePreview(this.value)"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                    @error('slug') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="legal_name" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">
                        Official Registered Legal Name
                    </label>
                    <input type="text" id="legal_name" name="legal_name" value="{{ old('legal_name') }}"
                           placeholder="Optional legal entity name"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                </div>

                <div>
                    <label for="locale" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Default Language</label>
                    <select id="locale" name="locale" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; background: #fff; box-sizing: border-box;">
                        @foreach (config('localizer.supported_locales', ['en', 'ar']) as $supportedLocale)
                            <option value="{{ $supportedLocale }}" {{ old('locale', config('app.fallback_locale', 'en')) === $supportedLocale ? 'selected' : '' }}>
                                {{ $supportedLocale === 'ar' ? '🇸🇦 Arabic (العربية)' : '🇬🇧 English' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="timezone" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Timezone</label>
                    <select id="timezone" name="timezone" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; background: #fff; box-sizing: border-box;">
                        <option value="Asia/Riyadh" {{ old('timezone', 'Asia/Riyadh') === 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh (GMT+3 - Saudi Arabia)</option>
                        <option value="Asia/Aden" {{ old('timezone') === 'Asia/Aden' ? 'selected' : '' }}>Asia/Aden (GMT+3 - Yemen)</option>
                    </select>
                </div>

                <div>
                    <label for="region" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Region</label>
                    <select id="region" name="region" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; background: #fff; box-sizing: border-box;">
                        <option value="">Select region...</option>
                        <option value="me" {{ old('region') === 'me' ? 'selected' : '' }}>Middle East</option>
                        <option value="gcc" {{ old('region') === 'gcc' ? 'selected' : '' }}>GCC</option>
                        <option value="sa" {{ old('region', 'sa') === 'sa' ? 'selected' : '' }}>Saudi Arabia</option>
                        <option value="ye" {{ old('region') === 'ye' ? 'selected' : '' }}>Yemen</option>
                        <option value="ae" {{ old('region') === 'ae' ? 'selected' : '' }}>UAE</option>
                        <option value="eg" {{ old('region') === 'eg' ? 'selected' : '' }}>Egypt</option>
                        <option value="pk" {{ old('region') === 'pk' ? 'selected' : '' }}>Pakistan</option>
                        <option value="eu" {{ old('region') === 'eu' ? 'selected' : '' }}>Europe</option>
                        <option value="na" {{ old('region') === 'na' ? 'selected' : '' }}>North America</option>
                    </select>
                </div>

                <div>
                    <label for="tier" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Subscription Tier</label>
                    <select id="tier" name="tier" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; background: #fff; box-sizing: border-box;">
                        <option value="standard" {{ old('tier', 'standard') === 'standard' ? 'selected' : '' }}>Standard Plan</option>
                        <option value="premium" {{ old('tier') === 'premium' ? 'selected' : '' }}>Premium Plan</option>
                        <option value="enterprise" {{ old('tier') === 'enterprise' ? 'selected' : '' }}>Enterprise Tier</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 2: SUBDOMAIN & CUSTOM DOMAIN ROUTING -->
        <div class="card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem;">
                <div style="background: #dbeafe; color: #1d4ed8; border-radius: 0.5rem; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">2. Domain & Web Address Routing</h2>
                    <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Configure how users will access this school on the web.</p>
                </div>
            </div>

            <!-- Live Preview Banner -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; padding: 0.875rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                <span style="background: #22c55e; color: #fff; border-radius: 9999px; width: 10px; height: 10px; display: inline-block;"></span>
                <span style="font-size: 0.875rem; color: #15803d; font-weight: 500;">
                    Live Target URL: <code id="live_url_preview" style="font-weight: 700; color: #166534; background: #dcfce7; padding: 0.2rem 0.5rem; border-radius: 0.25rem;">https://school{{ config('saas.hosts.tenant_suffix', '.intellschool.com') }}</code>
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem;">
                <div>
                    <label for="hostname" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Custom Hostname Override (Optional)</label>
                    <input id="hostname" type="text" name="hostname" value="{{ old('hostname') }}"
                           placeholder="Leave empty to use automatic subdomain"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                    @error('hostname') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="domain_type" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Domain Type</label>
                    <select id="domain_type" name="domain_type" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; background: #fff; box-sizing: border-box;">
                        <option value="subdomain" {{ old('domain_type', 'subdomain') === 'subdomain' ? 'selected' : '' }}>Subdomain (.intellschool.com)</option>
                        <option value="custom" {{ old('domain_type') === 'custom' ? 'selected' : '' }}>Custom Domain (school.com)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 3: DATABASE PROVISIONING -->
        <div class="card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem;">
                <div style="background: #fef3c7; color: #b45309; border-radius: 0.5rem; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">3. Database Allocation</h2>
                    <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Configure isolated MySQL credentials or adopt pre-created databases.</p>
                </div>
            </div>

            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 0.875rem 1rem; border-radius: 0.375rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #1e40af; line-height: 1.5;">
                💡 <strong>Hosting Setup Note:</strong> On a VPS, leave database fields blank to auto-create `tnt_xxx`. On Hostinger hPanel or cPanel, create the database in MySQL first and enter its exact name (e.g. <code>u763048361_tamjeed</code>).
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="database_name" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Database Name</label>
                <input id="database_name" type="text" name="database_name" value="{{ old('database_name') }}"
                       autocomplete="off" placeholder="e.g. u763048361_alnoor"
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                @error('database_name') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label for="database_username" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Database Username</label>
                    <input id="database_username" type="text" name="database_username" value="{{ old('database_username') }}"
                           autocomplete="off" placeholder="e.g. u763048361_tenant"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                    @error('database_username') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="database_password" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Database Password</label>
                    <input id="database_password" type="password" name="database_password"
                           autocomplete="new-password" placeholder="••••••••••••"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                    @error('database_password') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 4: SCHOOL OWNER & CONTACT -->
        <div class="card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.75rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem;">
                <div style="background: #f3e8ff; color: #7e22ce; border-radius: 0.5rem; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">4. School Administrator Contact (Optional)</h2>
                    <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Initial administrator account created inside the tenant database.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label for="owner_name" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Administrator Name</label>
                    <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}"
                           placeholder="e.g. Dr. Ahmed Hassan"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <div>
                    <label for="owner_email" style="display: block; font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.375rem;">Administrator Email</label>
                    <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}"
                           placeholder="e.g. admin@alnoor.edu"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; box-sizing: border-box;">
                    @error('owner_email') <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- FORM ACTION BUTTONS -->
        <div style="display: flex; gap: 1rem; align-items: center;">
            <button type="submit" class="btn btn-primary" style="padding: 0.875rem 1.75rem; font-size: 1rem; font-weight: 600; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Create & Provision Tenant
            </button>
            <a href="{{ route('saas.platform.tenants.index') }}" class="btn" style="padding: 0.875rem 1.5rem; font-size: 0.95rem; font-weight: 500; background: #f3f4f6; color: #374151; border-radius: 0.5rem; text-decoration: none;">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    function autoGenerateSlug(name) {
        var slugInput = document.getElementById('slug');
        if (!slugInput.dataset.manuallyEdited) {
            var slug = name.toLowerCase()
                .trim()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            slugInput.value = slug;
            updateLivePreview(slug);
        }
    }

    function updateLivePreview(slug) {
        var preview = document.getElementById('live_url_preview');
        var suffix = "{{ config('saas.hosts.tenant_suffix', '.intellschool.com') }}";
        if (!slug || slug.trim() === '') {
            preview.textContent = 'https://school' + suffix;
        } else {
            preview.textContent = 'https://' + slug.trim() + suffix;
        }
    }

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
</script>
@endsection
