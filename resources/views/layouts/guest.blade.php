<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In • ExpenseFlow</title>

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
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f4c81 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: .92rem;
        }
        .auth-card {
            width: 100%; max-width: 420px;
            border-radius: 16px; border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,.4);
        }
        .brand-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #fff; margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <div class="px-3 w-100" style="max-width:420px">
        <div class="text-center mb-4">
            <div class="brand-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <h4 class="text-white fw-bold mb-0">ExpenseFlow</h4>
            <p class="text-white-50 small">Operational Management System</p>
        </div>
        <div class="card auth-card">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>
        <p class="text-center text-white-50 small mt-3">&copy; {{ date('Y') }} ExpenseFlow</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
