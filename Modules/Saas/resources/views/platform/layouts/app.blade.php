<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Platform Admin') — {{ config('saas.brand.name', 'SchoolOS') }}</title>

    <style>
        :root {
            --color-primary: #2563eb;
            --color-primary-dark: #1d4ed8;
            --color-success: #16a34a;
            --color-warning: #d97706;
            --color-danger: #dc2626;
            --color-gray-50: #f9fafb;
            --color-gray-100: #f3f4f6;
            --color-gray-200: #e5e7eb;
            --color-gray-300: #d1d5db;
            --color-gray-500: #6b7280;
            --color-gray-700: #374151;
            --color-gray-900: #111827;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--color-gray-50);
            color: var(--color-gray-900);
            line-height: 1.5;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: var(--color-gray-900);
            color: white;
            padding: 1.5rem 0;
        }

        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid var(--color-gray-700);
            margin-bottom: 1rem;
        }

        .sidebar-nav a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--color-gray-300);
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: var(--color-gray-700);
            color: white;
        }

        .main {
            flex: 1;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .page-header p {
            color: var(--color-gray-500);
            margin-top: 0.25rem;
        }

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--color-gray-200);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .stat-label {
            color: var(--color-gray-500);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: start;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--color-gray-200);
        }

        th {
            font-weight: 600;
            color: var(--color-gray-500);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success { background: #dcfce7; color: var(--color-success); }
        .badge-warning { background: #fef3c7; color: var(--color-warning); }
        .badge-danger { background: #fee2e2; color: var(--color-danger); }
        .badge-gray { background: var(--color-gray-100); color: var(--color-gray-500); }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
        }

        @media (max-width: 768px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                {{ config('saas.brand.name', 'SchoolOS') }} <small>Platform</small>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('saas.platform.dashboard') }}" class="{{ request()->routeIs('saas.platform.dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('saas.platform.tenants.index') }}" class="{{ request()->routeIs('saas.platform.tenants.*') ? 'active' : '' }}">
                    Tenants
                </a>
                <a href="#">Subscriptions</a>
                <a href="#">Plans</a>
                <a href="#">Support Sessions</a>
                <a href="#">Audit Log</a>
            </nav>
        </aside>

        <main class="main">
            @if(session('success'))
                <div class="card" style="background: #dcfce7; border: 1px solid #86efac;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="card" style="background: #fee2e2; border: 1px solid #fca5a5;">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
