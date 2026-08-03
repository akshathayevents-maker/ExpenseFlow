@props(['title' => 'Event Request'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Akshathay Mini Hall</title>
    <meta name="theme-color" content="#3E2D23">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/scss/event-request.scss', 'resources/js/event-request-public.js'])
    @stack('head')
</head>
<body>
    <header class="erp-header">
        <div class="erp-mark"><i class="bi bi-stars"></i></div>
        <div>
            <div class="erp-brand-name">Akshathay Mini Hall</div>
            <div class="erp-brand-sub">Event Request</div>
        </div>
    </header>

    <div class="erp-shell">
        {{ $slot }}
    </div>
</body>
</html>
