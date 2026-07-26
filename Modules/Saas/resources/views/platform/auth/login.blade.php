<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Platform Login — {{ config('saas.brand.name', 'SchoolOS') }}</title>
    <style>
        :root {
            --color-primary: #2563eb;
            --color-primary-dark: #1d4ed8;
            --color-danger: #dc2626;
            --color-gray-50: #f9fafb;
            --color-gray-200: #e5e7eb;
            --color-gray-300: #d1d5db;
            --color-gray-500: #6b7280;
            --color-gray-700: #374151;
            --color-gray-900: #111827;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--color-gray-900);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 0.75rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-gray-900);
        }
        .login-brand p {
            color: var(--color-gray-500);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
            color: var(--color-gray-700);
        }
        .form-group input {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid var(--color-gray-300);
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: var(--color-primary-dark);
        }
        .error-msg {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: var(--color-danger);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .remember-row label {
            font-size: 0.875rem;
            color: var(--color-gray-500);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-brand">
            <h1>{{ config('saas.brand.name', 'SchoolOS') }}</h1>
            <p>Platform Administration</p>
        </div>

        @if($errors->any())
            <div class="error-msg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('saas.platform.login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="operator@platform.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••">
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
