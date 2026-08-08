@php
    $operator = auth('platform')->user();
    $gate = Illuminate\Support\Facades\Gate::forUser($operator);
    $canBilling = $operator && $gate->allows('manageBilling');
    $canSupport = $operator && $gate->allows('accessSupport');
    $canAudit = $operator && $gate->allows('viewAuditLog');
    $initials = $operator ? collect(preg_split('/\s+/', trim($operator->name)))->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('') : 'OP';
@endphp
<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}' dir='{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='csrf-token' content='{{ csrf_token() }}'>
    <meta name='theme-color' content='#07111f'>
    <title>@yield('title', 'Platform Admin') — {{ config('saas.brand.name', 'SchoolOS') }}</title>
    <script>document.documentElement.classList.add('platform-js')</script>
    @vite(['resources/assets/css/platform.css', 'resources/assets/css/components.css', 'resources/assets/js/platform/app.js'], 'build-saas')
    @stack('styles')
</head>
<body>
    <a class='skip-link' href='#platform-content'>Skip to main content</a>
    <div class='platform-layout'>
        <aside class='platform-sidebar' id='platform-sidebar' data-platform-sidebar aria-label='Platform navigation'>
            <a class='platform-brand' href='{{ route('saas.platform.dashboard') }}'>
                <span class='platform-brand-mark' aria-hidden='true'>
                    <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M4 19V9l8-5 8 5v10'/><path d='M9 19v-6h6v6'/></svg>
                </span>
                <span class='platform-brand-copy'>
                    <span class='platform-brand-name'>{{ config('saas.brand.name', 'SchoolOS') }}</span>
                    <span class='platform-brand-subtitle'>Control center</span>
                </span>
            </a>

            <nav class='platform-nav'>
                <section class='platform-nav-section' aria-labelledby='nav-overview'>
                    <h2 class='platform-nav-label' id='nav-overview'>Overview</h2>
                    <a class='platform-nav-link' href='{{ route('saas.platform.dashboard') }}' @if(request()->routeIs('saas.platform.dashboard')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M4 13h6V4H4zM14 20h6v-9h-6zM4 20h6v-3H4zM14 7h6V4h-6z'/></svg></span>
                        Dashboard
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.tenants.index') }}' @if(request()->routeIs('saas.platform.tenants.*') && !request()->routeIs('saas.platform.tenants.modules.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M4 20V8l8-4 8 4v12M8 20v-5h8v5M8 10h.01M12 10h.01M16 10h.01'/></svg></span>
                        Tenants
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.modules.index') }}' @if(request()->routeIs('saas.platform.modules.*') || request()->routeIs('saas.platform.tenants.modules.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M4 6h16M4 12h16M4 18h16'/></svg></span>
                        Modules
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.system-config.index') }}' @if(request()->routeIs('saas.platform.system-config.*') || request()->routeIs('saas.platform.tenants.system-config.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><circle cx='12' cy='12' r='3'/><path d='M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z'/></svg></span>
                        System Config
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.mail-config.index') }}' @if(request()->routeIs('saas.platform.mail-config.*') || request()->routeIs('saas.platform.tenants.mail-config.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'/></svg></span>
                        Mail Config
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.sms-config.index') }}' @if(request()->routeIs('saas.platform.sms-config.*') || request()->routeIs('saas.platform.tenants.sms-config.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'/></svg></span>
                        SMS Config
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.chat-config.index') }}' @if(request()->routeIs('saas.platform.chat-config.*') || request()->routeIs('saas.platform.tenants.chat-config.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'/></svg></span>
                        Chat Config
                    </a>
                    <a class='platform-nav-link' href='{{ route('saas.platform.feature-config.index') }}' @if(request()->routeIs('saas.platform.feature-config.*') || request()->routeIs('saas.platform.tenants.feature-config.*')) aria-current='page' @endif>
                        <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'/></svg></span>
                        Feature Config
                    </a>
                </section>

                @if($canBilling || $canSupport || $canAudit)
                    <section class='platform-nav-section' aria-labelledby='nav-operations'>
                        <h2 class='platform-nav-label' id='nav-operations'>Operations</h2>
                        @if($canBilling)
                            <a class='platform-nav-link' href='{{ route('saas.platform.subscriptions.index') }}' @if(request()->routeIs('saas.platform.subscriptions.*')) aria-current='page' @endif>
                                <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='5' width='18' height='14' rx='2'/><path d='M3 10h18'/></svg></span>
                                Subscriptions
                            </a>
                            <a class='platform-nav-link' href='{{ route('saas.platform.plans.index') }}' @if(request()->routeIs('saas.platform.plans.*')) aria-current='page' @endif>
                                <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M5 5h14v14H5zM8 9h8M8 13h5'/></svg></span>
                                Plans
                            </a>
                        @endif
                        @if($canSupport)
                            <a class='platform-nav-link' href='{{ route('saas.platform.support.index') }}' @if(request()->routeIs('saas.platform.support.*')) aria-current='page' @endif>
                                <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><circle cx='12' cy='12' r='9'/><path d='M8 15h8M9 9h.01M15 9h.01'/></svg></span>
                                Support access
                            </a>
                        @endif
                        @if($canAudit)
                            <a class='platform-nav-link' href='{{ route('saas.platform.audit.index') }}' @if(request()->routeIs('saas.platform.audit.*')) aria-current='page' @endif>
                                <span class='platform-nav-icon' aria-hidden='true'><svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M7 3h10v4H7zM5 5H4v16h16V5h-1M8 12h8M8 16h5'/></svg></span>
                                Audit log
                            </a>
                        @endif
                    </section>
                @endif
            </nav>

            <div class='platform-operator'>
                <div class='platform-operator-card'>
                    <span class='platform-avatar' aria-hidden='true'>{{ $initials }}</span>
                    <span>
                        <span class='platform-operator-name'>{{ $operator?->name ?? 'Operator' }}</span>
                        <span class='platform-operator-role'>{{ str_replace('_', ' ', $operator?->role ?? 'platform') }}</span>
                    </span>
                    <form method='POST' action='{{ route('saas.platform.logout') }}'>
                        @csrf
                        <button class='platform-signout' type='submit' aria-label='Sign out' title='Sign out'>
                            <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' aria-hidden='true'><path d='M10 17l5-5-5-5M15 12H3M15 3h5v18h-5'/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <button class='sidebar-backdrop' type='button' data-sidebar-backdrop aria-hidden='true' tabindex='-1' aria-label='Close navigation'></button>

        <div class='platform-main'>
            <header class='platform-topbar'>
                <div class='platform-topbar-start'>
                    <button class='platform-menu-toggle' type='button' data-sidebar-toggle aria-controls='platform-sidebar' aria-expanded='false' aria-label='Open navigation'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' aria-hidden='true'><path d='M4 7h16M4 12h16M4 17h16'/></svg>
                    </button>
                    <div class='platform-context'>
                        <span class='platform-context-label'>Platform administration</span>
                        <span class='platform-context-title'>@yield('title', 'Dashboard')</span>
                    </div>
                </div>
                <div class='platform-topbar-meta'>
                    <span class='environment-badge'><span class='environment-dot' aria-hidden='true'></span>{{ app()->environment() }}</span>
                </div>
            </header>

            <main class='platform-content' id='platform-content' tabindex='-1'>
                @if(session('success'))
                    <div class='alert alert-success' role='status'><div><strong class='alert-title'>Action completed</strong>{{ session('success') }}</div></div>
                @endif
                @if(session('error'))
                    <div class='alert alert-danger' role='alert'><div><strong class='alert-title'>Action could not be completed</strong>{{ session('error') }}</div></div>
                @endif
                @if($errors->any())
                    <div class='alert alert-danger error-summary' role='alert' tabindex='-1' data-error-summary>
                        <div><strong class='alert-title'>Review the highlighted information</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
