<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In • Akshathay</title>

    {{-- Favicon & PWA --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0f172a">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .akshathay-guest {
            --ak-primary: #0F766E;
            --ak-primary-hover: #0D5F59;
            --ak-accent: #14B8A6;
            --ak-bg-dark: #0F172A;
            --ak-bg-secondary: #134E4A;
            --ak-surface: #FFFFFF;
            --ak-text: #111827;
            --ak-text-muted: #64748B;
            --ak-input-bg: #F8FAFC;
            --ak-input-border: #CBD5E1;

            min-height: 100vh;
            background: linear-gradient(135deg, var(--ak-bg-dark) 0%, var(--ak-bg-secondary) 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: .92rem;
        }
        .akshathay-guest .auth-card {
            width: 100%; max-width: 420px;
            border-radius: 16px; border: 1px solid rgba(17,24,39,.06);
            box-shadow: 0 10px 30px rgba(15,23,42,.15);
            background: var(--ak-surface);
        }
        .akshathay-guest .brand-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, var(--ak-primary), var(--ak-accent));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #fff; margin: 0 auto 1rem;
        }
        .akshathay-guest h5.fw-bold { color: var(--ak-text); }
        .akshathay-guest .text-muted { color: var(--ak-text-muted) !important; }
        .akshathay-guest .form-control {
            background-color: var(--ak-input-bg);
            border-color: var(--ak-input-border);
            color: var(--ak-text);
        }
        .akshathay-guest .form-control:focus {
            border-color: var(--ak-accent);
            box-shadow: 0 0 0 3px rgba(20,184,166,.15);
            background-color: var(--ak-input-bg);
        }
        .akshathay-guest .form-check-input:checked {
            background-color: var(--ak-primary);
            border-color: var(--ak-primary);
        }
        .akshathay-guest .form-check-input:focus {
            border-color: var(--ak-accent);
            box-shadow: 0 0 0 3px rgba(20,184,166,.15);
        }
        .akshathay-guest a {
            color: var(--ak-primary);
        }
        .akshathay-guest a:hover {
            color: var(--ak-primary-hover);
        }
        .akshathay-guest .btn-primary {
            background-color: var(--ak-primary);
            border-color: var(--ak-primary);
        }
        .akshathay-guest .btn-primary:hover,
        .akshathay-guest .btn-primary:focus,
        .akshathay-guest .btn-primary:active {
            background-color: var(--ak-primary-hover) !important;
            border-color: var(--ak-primary-hover) !important;
        }
    </style>
</head>
<body>
    <div class="akshathay-guest">
    <div class="px-3 w-100" style="max-width:420px">
        <div class="text-center mb-4">
            <div class="brand-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <h4 class="text-white fw-bold mb-0">Akshathay</h4>
            <p class="text-white-50 small">Operational Management System</p>
        </div>
        <div class="card auth-card">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>
        <p class="text-center text-white-50 small mt-3">&copy; {{ date('Y') }} Akshathay</p>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
