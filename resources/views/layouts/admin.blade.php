<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} • ExpenseFlow</title>

    {{-- Favicon & PWA --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#10b981">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 56px;
            --primary: #0d6efd;
        }

        body {
            background-color: #f0f2f5;
            font-size: 0.9rem;
        }

        /* ── Topbar ── */
        #topbar {
            height: var(--topbar-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #1e293b;
            color: #fff;
        }

        #topbar .brand {
            width: var(--sidebar-width);
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
        }

        /* ── Sidebar ── */
        #sidebar {
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #1e293b;
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
        }

        #sidebar .nav-link {
            color: #94a3b8;
            padding: 0.6rem 1.25rem;
            border-radius: 6px;
            margin: 2px 8px;
            transition: background 0.15s, color 0.15s;
            font-size: 0.875rem;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        #sidebar .nav-link .bi {
            width: 20px;
            margin-right: 8px;
            font-size: 1rem;
        }

        #sidebar .sidebar-section {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
            padding: 1rem 1.25rem 0.25rem;
            font-weight: 600;
        }

        /* ── Main content ── */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            min-height: calc(100vh - var(--topbar-height));
            padding: 1.5rem;
        }

        /* ── Mobile ── */
        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0;
            }

            #topbar .brand {
                width: auto;
            }
        }

        /* ── Sidebar overlay ── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1015;
        }

        #sidebar-overlay.show {
            display: block;
        }

        /* ── Cards ── */
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: box-shadow 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Table ── */
        .table thead th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        /* ── Badge ── */
        .role-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Page header ── */
        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Topbar --}}
<nav id="topbar" class="d-flex align-items-center px-3 gap-2">
    <div class="brand d-flex align-items-center gap-2 flex-shrink-0">
        <button class="btn btn-sm text-white d-lg-none me-1" id="sidebar-toggle">
            <i class="bi bi-list fs-5"></i>
        </button>
        <i class="bi bi-receipt-cutoff text-primary"></i>
        ExpenseFlow
    </div>

    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="d-none d-sm-inline text-secondary small">
            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
            <span class="badge bg-secondary ms-1 text-uppercase" style="font-size:0.6rem">{{ auth()->user()->role }}</span>
        </span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-box-arrow-right"></i>
                <span class="d-none d-sm-inline ms-1">Logout</span>
            </button>
        </form>
    </div>
</nav>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebar-overlay"></div>

{{-- Sidebar --}}
<nav id="sidebar">
    <div class="pt-3 pb-4">
        <p class="sidebar-section">Main</p>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @if(auth()->user()->isAdmin())
        <p class="sidebar-section mt-2">Management</p>

        <a href="{{ route('admin.employees.index') }}"
           class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Employees
        </a>

        <a href="#" class="nav-link text-muted">
            <i class="bi bi-tag"></i> Categories
            <span class="badge bg-secondary ms-auto" style="font-size:0.6rem">Soon</span>
        </a>

        <a href="#" class="nav-link text-muted">
            <i class="bi bi-file-earmark-text"></i> Expense Requests
            <span class="badge bg-secondary ms-auto" style="font-size:0.6rem">Soon</span>
        </a>

        <p class="sidebar-section mt-2">Analytics</p>

        <a href="#" class="nav-link text-muted">
            <i class="bi bi-bar-chart-line"></i> Reports
            <span class="badge bg-secondary ms-auto" style="font-size:0.6rem">Soon</span>
        </a>
        @endif

        <p class="sidebar-section mt-2">Account</p>
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Profile
        </a>
    </div>
</nav>

{{-- Main content --}}
<main id="main-content">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{ $slot }}
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toggleBtn  = document.getElementById('sidebar-toggle');
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);
</script>
@stack('scripts')
</body>
</html>
