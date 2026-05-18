@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' • ' : '' }}ExpenseFlow</title>

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
    @php
        $viteManifestPath = public_path('build/manifest.json');
        $viteManifest = file_exists($viteManifestPath)
            ? json_decode(file_get_contents($viteManifestPath), true)
            : [];
        $appCss = $viteManifest['resources/css/app.css']['file'] ?? null;
        $appJs = $viteManifest['resources/js/app.js']['file'] ?? null;
    @endphp
    @if($appCss)
        <link rel="stylesheet" href="{{ asset('build/' . $appCss) }}">
    @endif
    @if($appJs)
        <script type="module" src="{{ asset('build/' . $appJs) }}"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}?v=1">

    <style>
        :root {
            --sb-width:     280px;
            --tb-height:    58px;
            --sb-gold:      #B8893E;
            --sb-gold-soft: #D6B97A;
            --pg-bg:        var(--ef-bg);
            --border:       var(--ef-border);
            --text-primary: #111111;
            --text-muted:   #737373;
        }

        body { background: var(--pg-bg); font-size: .9rem; color: var(--text-primary); }

        /* ── Topbar ─────────────────────────────────────────────────────── */
        #topbar {
            height: var(--tb-height);
            position: fixed; top: 0; left: 0; right: 0; z-index: 1030;
            background: rgba(14,15,13,.97);
            backdrop-filter: blur(20px) saturate(140%);
            border-bottom: 1px solid rgba(184,137,62,.10);
        }
        #topbar .brand {
            width: var(--sb-width); font-weight: 720; font-size: .96rem;
            letter-spacing: .02em; flex-shrink: 0; color: rgba(255,255,255,.92);
        }
        #topbar .brand .bi { color: var(--sb-gold); }

        /* ── Sidebar ─────────────────────────────────────────────────────── */
        #sidebar {
            position: fixed; top: var(--tb-height);
            left: 0; bottom: 0; width: var(--sb-width);
            background: linear-gradient(180deg, #111311 0%, #151815 35%, #0e100f 100%);
            backdrop-filter: blur(18px);
            border-right: 1px solid rgba(184,137,62,.12);
            overflow-y: auto; z-index: 1020;
            transition: transform .28s cubic-bezier(.2,.7,.2,1);
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.07) transparent;
        }
        #sidebar::-webkit-scrollbar { width: 3px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(184,137,62,.15); border-radius: 2px; }

        #sidebar .nav-link {
            color: rgba(255,255,255,.72);
            padding: .6rem 1rem;
            border-radius: 10px;
            margin: 1px 10px;
            border: 1px solid transparent;
            font-size: .83rem;
            font-weight: 500;
            display: flex; align-items: center;
            transition: all .22s ease;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            text-decoration: none;
        }
        #sidebar .nav-link:hover {
            background: rgba(184,137,62,.08);
            color: #F5E7C8;
            transform: translateX(2px);
        }
        #sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(184,137,62,.18), rgba(184,137,62,.05));
            border-color: rgba(184,137,62,.35);
            box-shadow: 0 4px 20px rgba(184,137,62,.10);
            color: #FFF4DA;
            font-weight: 600;
        }
        #sidebar .nav-link .bi {
            width: 20px; margin-right: 10px; flex-shrink: 0;
            font-size: 1.05rem;
            color: rgba(255,255,255,.55);
            text-align: center;
            transition: color .22s ease;
        }
        #sidebar .nav-link:hover .bi { color: #D6B97A; }
        #sidebar .nav-link.active .bi { color: #E7C98A; }

        /* Sidebar section label */
        .sidebar-group-btn {
            width: 100%; background: none; border: none; outline: none;
            color: rgba(214,185,122,.55);
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 2px;
            padding: 1.5rem 1.3rem .45rem;
            display: flex; align-items: center; justify-content: space-between;
            cursor: pointer; line-height: 1;
            transition: color .18s ease;
        }
        .sidebar-group-btn:hover  { color: rgba(214,185,122,.78); }
        .sidebar-group-btn.has-active { color: rgba(214,185,122,.72); }
        .sidebar-group-btn .sidebar-chevron {
            font-size: .5rem; transition: transform .22s ease; flex-shrink: 0;
            color: rgba(214,185,122,.4);
        }
        .sidebar-group-btn[aria-expanded="false"] .sidebar-chevron { transform: rotate(-90deg); }
        .sidebar-group-btn[aria-expanded="true"]  .sidebar-chevron { transform: rotate(0deg); }

        .sidebar-group-body .nav-link { padding-left: 2.6rem; }
        .sidebar-standalone { padding: .2rem 0; }

        /* Sidebar divider */
        .sb-divider {
            height: 1px; background: rgba(255,255,255,.05);
            margin: .5rem 1rem;
        }

        /* Bottom account section separator */
        #grp-account-wrap {
            border-top: 1px solid rgba(255,255,255,.05);
            margin-top: .5rem;
            padding-top: .25rem;
        }

        /* ── Main ────────────────────────────────────────────────────────── */
        #main-content {
            margin-left: var(--sb-width);
            margin-top: var(--tb-height);
            min-height: calc(100vh - var(--tb-height));
            overflow-x: hidden;
            padding: 32px;
        }

        /* ── Mobile ──────────────────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; overflow-x: hidden; padding: 16px; }
            #topbar .brand { width: auto; }
        }
        #sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            backdrop-filter: blur(3px);
            z-index: 1015;
            transition: opacity .22s ease;
        }
        #sidebar-overlay.show { display: block; }

        /* ── Shared components ───────────────────────────────────────────── */
        .stat-card {
            border: 1px solid var(--border); border-radius: var(--ef-radius-sm);
            background: var(--ef-surface); transition: box-shadow .18s;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
        .stat-card .icon-box {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center;
            justify-content: center; font-size: 1.25rem; flex-shrink: 0;
        }
        .table thead th {
            background: #fafaf9; font-weight: 700; font-size: .7rem;
            text-transform: uppercase; letter-spacing: .07em;
            color: var(--text-muted); border-bottom: 1px solid var(--border);
        }
        .role-badge { font-size: .65rem; text-transform: uppercase; letter-spacing: .06em; }
        .page-header {
            margin-bottom: 1.75rem; padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Topbar --}}
<nav id="topbar" class="d-flex align-items-center px-3 gap-2">
    <div class="brand d-flex align-items-center gap-2">
        <button class="btn btn-sm text-white d-lg-none me-1" id="sidebar-toggle">
            <i class="bi bi-list fs-5"></i>
        </button>
        <i class="bi bi-receipt-cutoff"></i> ExpenseFlow
    </div>

    <div class="ms-auto d-flex align-items-center gap-2">
        @auth
        @php
            $unreadCount = auth()->user()->hasMany(\App\Models\AppNotification::class)->whereNull('read_at')->count();
            $recentNotifications = \App\Models\AppNotification::where('user_id', auth()->id())->latest()->limit(6)->get();
            $mPendingCount = in_array(auth()->user()->role, ['admin', 'manager'])
                ? \App\Models\ExpenseRequest::where('status', 'pending')->count()
                : 0;
        @endphp
        {{-- Notification bell --}}
        <div class="dropdown">
            <button class="btn btn-sm text-white position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-6"></i>
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:.55rem;padding:2px 4px">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow" style="width:320px;max-height:420px;overflow-y:auto">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <span class="fw-semibold small">Notifications</span>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-link btn-sm p-0 text-muted" style="font-size:.75rem">Mark all read</button>
                        </form>
                    @endif
                </div>
                @forelse($recentNotifications as $notif)
                @php $meta = \App\Models\AppNotification::typeIcons()[$notif->type] ?? ['icon' => 'bi-bell', 'color' => 'secondary']; @endphp
                <div class="dropdown-item px-3 py-2 {{ $notif->isRead() ? '' : 'bg-light' }}" style="white-space:normal;cursor:pointer">
                    <form method="POST" action="{{ route('notifications.read', $notif) }}" class="d-flex align-items-start gap-2">
                        @csrf @method('PATCH')
                        <i class="bi {{ $meta['icon'] }} text-{{ $meta['color'] }} flex-shrink-0 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="small fw-semibold {{ $notif->isRead() ? 'text-muted' : '' }}">{{ $notif->title }}</div>
                            @if($notif->body)
                                <div class="text-muted" style="font-size:.72rem">{{ Str::limit($notif->body, 60) }}</div>
                            @endif
                            <div class="text-muted" style="font-size:.68rem">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                        @if(!$notif->isRead())
                            <button class="btn p-0 border-0 bg-transparent flex-shrink-0" title="Mark read">
                                <i class="bi bi-circle-fill text-primary" style="font-size:.5rem"></i>
                            </button>
                        @endif
                    </form>
                </div>
                @empty
                <div class="px-3 py-4 text-center text-muted small">
                    <i class="bi bi-bell-slash d-block fs-4 mb-1 opacity-50"></i>
                    No notifications
                </div>
                @endforelse
                <div class="border-top px-3 py-2 text-center">
                    <a href="{{ route('notifications.index') }}" class="small text-primary text-decoration-none">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>
        @endauth

        <span class="d-none d-sm-inline text-secondary small">
            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
            <span class="badge bg-secondary ms-1 text-uppercase" style="font-size:.6rem">
                {{ auth()->user()->role }}
            </span>
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

<div id="sidebar-overlay"></div>

{{-- Sidebar --}}
<nav id="sidebar">
@php
    $role = auth()->user()->role;

    // Pre-compute open states server-side — no JS needed, no flicker.
    $grpMgmt      = request()->routeIs('admin.employees.*','admin.categories.*','admin.vendors.*','admin.expense-requests.*','admin.wallets.*','admin.payments.*','admin.inventory.*','admin.purchase-plans.*');
    $grpAnalytics = request()->routeIs('admin.analytics.*','admin.reports.*');
    $grpOps       = request()->routeIs('admin.daily-closings.*','admin.audit-logs.*','admin.settings.*');
    $grpHall      = request()->routeIs('hall.*');
@endphp
<div class="pt-2 pb-4">

{{-- ═══ ADMIN ═══ --}}
@if($role === 'admin')

    {{-- Dashboard (standalone) --}}
    <div class="sidebar-standalone px-2 pt-2">
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>

    {{-- Management --}}
    <button class="sidebar-group-btn {{ $grpMgmt ? 'has-active' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#grp-mgmt"
            aria-expanded="{{ $grpMgmt ? 'true' : 'false' }}">
        <span>Management</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ $grpMgmt ? 'show' : '' }}" id="grp-mgmt">
        <a href="{{ route('admin.employees.index') }}"
           class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Employees
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tag"></i> Categories
        </a>
        <a href="{{ route('admin.vendors.index') }}"
           class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
            <i class="bi bi-shop"></i> Vendors
        </a>
        <a href="{{ route('admin.expense-requests.index') }}"
           class="nav-link {{ request()->routeIs('admin.expense-requests.*') && !request()->routeIs('admin.expense-requests.create','admin.expense-requests.success') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Expense Requests
        </a>
        <a href="{{ route('admin.expense-requests.create') }}"
           class="nav-link {{ request()->routeIs('admin.expense-requests.create','admin.expense-requests.success') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> Create Expense
        </a>
        <a href="{{ route('admin.wallets.index') }}"
           class="nav-link {{ request()->routeIs('admin.wallets.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> Wallets
        </a>
        <a href="{{ route('admin.payments.index') }}"
           class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Payments
        </a>
        <a href="{{ route('admin.inventory.items.index') }}"
           class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i> Inventory
        </a>
        <a href="{{ route('admin.purchase-plans.index') }}"
           class="nav-link {{ request()->routeIs('admin.purchase-plans.*') ? 'active' : '' }}">
            <i class="bi bi-cart3"></i> Purchase Plans
        </a>
    </div>

    {{-- Analytics --}}
    <button class="sidebar-group-btn {{ $grpAnalytics ? 'has-active' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#grp-analytics"
            aria-expanded="{{ $grpAnalytics ? 'true' : 'false' }}">
        <span>Analytics</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ $grpAnalytics ? 'show' : '' }}" id="grp-analytics">
        <a href="{{ route('admin.analytics.index') }}"
           class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i> Analytics
        </a>
        <a href="{{ route('admin.reports.index') }}"
           class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Reports
        </a>
    </div>

    {{-- Operations --}}
    <button class="sidebar-group-btn {{ $grpOps ? 'has-active' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#grp-ops"
            aria-expanded="{{ $grpOps ? 'true' : 'false' }}">
        <span>Operations</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ $grpOps ? 'show' : '' }}" id="grp-ops">
        <a href="{{ route('admin.daily-closings.index') }}"
           class="nav-link {{ request()->routeIs('admin.daily-closings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Daily Closing
        </a>
        <a href="{{ route('admin.audit-logs.index') }}"
           class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Audit Logs
        </a>
        <a href="{{ route('admin.settings.index') }}"
           class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> Settings
        </a>
    </div>

    {{-- Hall Management --}}
    <button class="sidebar-group-btn {{ $grpHall ? 'has-active' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#grp-hall"
            aria-expanded="{{ $grpHall ? 'true' : 'false' }}">
        <span>Hall Management</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ $grpHall ? 'show' : '' }}" id="grp-hall">
        <a href="{{ route('hall.dashboard') }}"
           class="nav-link {{ request()->routeIs('hall.dashboard') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Dashboard
        </a>
        <a href="{{ route('hall.bookings.index') }}"
           class="nav-link {{ request()->routeIs('hall.bookings.*') && !request()->routeIs('hall.bookings.calendar','hall.bookings.kitchen') ? 'active' : '' }}">
            <i class="bi bi-calendar2-event"></i> Hall Bookings
        </a>
        <a href="{{ route('hall.bookings.calendar') }}"
           class="nav-link {{ request()->routeIs('hall.bookings.calendar') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Calendar View
        </a>
        <a href="{{ route('hall.meal-plans.index') }}"
           class="nav-link {{ request()->routeIs('hall.meal-plans.*') ? 'active' : '' }}">
            <i class="bi bi-egg-fried"></i> Meal Plans
        </a>
        <a href="{{ route('hall.reports.index') }}"
           class="nav-link {{ request()->routeIs('hall.reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports
        </a>
    </div>

{{-- ═══ MANAGER ═══ --}}
@elseif($role === 'manager')

    <div class="sidebar-standalone px-2 pt-2">
        <a href="{{ route('manager.dashboard') }}"
           class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('manager.expense-requests.index') }}"
           class="nav-link {{ request()->routeIs('manager.expense-requests.*') && !request()->routeIs('manager.expense-requests.create','manager.expense-requests.success') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Expense Requests
        </a>
        <a href="{{ route('manager.expense-requests.create') }}"
           class="nav-link {{ request()->routeIs('manager.expense-requests.create','manager.expense-requests.success') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> Create Expense
        </a>
    </div>

    {{-- Hall Management --}}
    <button class="sidebar-group-btn {{ $grpHall ? 'has-active' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#grp-hall"
            aria-expanded="{{ $grpHall ? 'true' : 'false' }}">
        <span>Hall Management</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ $grpHall ? 'show' : '' }}" id="grp-hall">
        <a href="{{ route('hall.dashboard') }}"
           class="nav-link {{ request()->routeIs('hall.dashboard') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Dashboard
        </a>
        <a href="{{ route('hall.bookings.index') }}"
           class="nav-link {{ request()->routeIs('hall.bookings.*') && !request()->routeIs('hall.bookings.calendar','hall.bookings.kitchen') ? 'active' : '' }}">
            <i class="bi bi-calendar2-event"></i> Hall Bookings
        </a>
        <a href="{{ route('hall.bookings.calendar') }}"
           class="nav-link {{ request()->routeIs('hall.bookings.calendar') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Calendar View
        </a>
        <a href="{{ route('hall.meal-plans.index') }}"
           class="nav-link {{ request()->routeIs('hall.meal-plans.*') ? 'active' : '' }}">
            <i class="bi bi-egg-fried"></i> Meal Plans
        </a>
        <a href="{{ route('hall.reports.index') }}"
           class="nav-link {{ request()->routeIs('hall.reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports
        </a>
    </div>

{{-- ═══ EMPLOYEE ═══ --}}
@else

    <div class="sidebar-standalone px-2 pt-2">
        <a href="{{ route('employee.dashboard') }}"
           class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('employee.expense-requests.create') }}"
           class="nav-link {{ request()->routeIs('employee.expense-requests.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> New Request
        </a>
        <a href="{{ route('employee.expense-requests.index') }}"
           class="nav-link {{ request()->routeIs('employee.expense-requests.*') && !request()->routeIs('employee.expense-requests.create') ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i> My Requests
        </a>
        <a href="{{ route('employee.wallet.show') }}"
           class="nav-link {{ request()->routeIs('employee.wallet.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> My Wallet
        </a>
    </div>

@endif

    {{-- Account (always visible, no accordion) --}}
    @php $grpAccount = request()->routeIs('notifications.*','profile.*'); @endphp
    <div id="grp-account-wrap">
    <button class="sidebar-group-btn {{ $grpAccount ? 'has-active' : 'collapsed' }}"
            data-bs-toggle="collapse" data-bs-target="#grp-account"
            aria-expanded="{{ $grpAccount ? 'true' : 'false' }}">
        <span>Account</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ request()->routeIs('notifications.*','profile.*') ? 'show' : '' }}" id="grp-account">
        @php $nc = auth()->user()->hasMany(\App\Models\AppNotification::class)->whereNull('read_at')->count(); @endphp
        <a href="{{ route('notifications.index') }}"
           class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifications
            @if($nc > 0)
                <span class="badge bg-danger ms-auto" style="font-size:.6rem">{{ $nc }}</span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}"
           class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Profile
        </a>
    </div>
    </div>{{-- /grp-account-wrap --}}

</div>
</nav>

{{-- Main content --}}
<main id="main-content">
    @if(session('success'))
        <div class="alert alert-dismissible fade show" role="alert"
             style="align-items:center;background:rgba(15,123,95,.08);border:1px solid rgba(15,123,95,.2);border-radius:12px;color:#0F7B5F;display:flex;font-size:.86rem;gap:10px;margin-bottom:16px;padding:10px 14px">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span style="flex:1">{{ session('success') }}</span>
            <button type="button" class="btn-close btn-sm flex-shrink-0" data-bs-dismiss="alert" style="filter:invert(40%) sepia(80%) saturate(300%) hue-rotate(120deg);margin:0"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-dismissible fade show" role="alert"
             style="align-items:center;background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:12px;color:#C84B44;display:flex;font-size:.86rem;gap:10px;margin-bottom:16px;padding:10px 14px">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span style="flex:1">{{ session('error') }}</span>
            <button type="button" class="btn-close btn-sm flex-shrink-0" data-bs-dismiss="alert" style="filter:invert(30%) sepia(80%) saturate(600%) hue-rotate(330deg);margin:0"></button>
        </div>
    @endif

    {{ $slot }}
</main>

{{-- Mobile bottom navigation (shown on screens ≤ 767px via CSS) --}}
@auth
@php $mRole = auth()->user()->role ?? 'employee'; @endphp
<nav class="ef-m-bottomnav" role="navigation" aria-label="Mobile navigation">
    @if($mRole === 'admin')
        <a href="{{ route('admin.dashboard') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-speedometer2"></i></div>
            <span>Home</span>
        </a>
        <a href="{{ route('admin.expense-requests.index') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('admin.expense-requests.*') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap">
                <i class="bi bi-file-earmark-text"></i>
                @if($mPendingCount > 0)
                    <span class="ef-m-nav-badge">{{ $mPendingCount > 99 ? '99+' : $mPendingCount }}</span>
                @endif
            </div>
            <span>Expenses</span>
        </a>
        <a href="{{ route('admin.wallets.index') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('admin.wallets.*') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-wallet2"></i></div>
            <span>Wallets</span>
        </a>
        <button type="button" class="ef-m-bottomnav-item" onclick="document.getElementById('sidebar-toggle').click()">
            <div class="ef-m-bottomnav-icon-wrap">
                <i class="bi bi-grid-3x3-gap"></i>
                @if($unreadCount > 0)
                    <span class="ef-m-nav-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </div>
            <span>Menu</span>
        </button>

    @elseif($mRole === 'manager')
        <a href="{{ route('manager.dashboard') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-speedometer2"></i></div>
            <span>Home</span>
        </a>
        <a href="{{ route('manager.expense-requests.index') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('manager.expense-requests.*') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap">
                <i class="bi bi-file-earmark-text"></i>
                @if($mPendingCount > 0)
                    <span class="ef-m-nav-badge">{{ $mPendingCount > 99 ? '99+' : $mPendingCount }}</span>
                @endif
            </div>
            <span>Requests</span>
        </a>
        <a href="{{ route('hall.dashboard') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('hall.*') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-building"></i></div>
            <span>Hall</span>
        </a>
        <button type="button" class="ef-m-bottomnav-item" onclick="document.getElementById('sidebar-toggle').click()">
            <div class="ef-m-bottomnav-icon-wrap">
                <i class="bi bi-grid-3x3-gap"></i>
                @if($unreadCount > 0)
                    <span class="ef-m-nav-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </div>
            <span>Menu</span>
        </button>

    @else
        {{-- Employee --}}
        <a href="{{ route('employee.dashboard') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-speedometer2"></i></div>
            <span>Home</span>
        </a>
        <a href="{{ route('employee.expense-requests.index') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('employee.expense-requests.index') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-list-ul"></i></div>
            <span>Requests</span>
        </a>
        <a href="{{ route('employee.wallet.show') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('employee.wallet.*') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-wallet2"></i></div>
            <span>Wallet</span>
        </a>
        <a href="{{ route('employee.expense-requests.create') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('employee.expense-requests.create') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-plus-circle"></i></div>
            <span>New</span>
        </a>
    @endif
</nav>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Global double-submission guard: disables submit button on form submit.
    // Re-enabled automatically on page reload (validation errors) or back-navigation.
    document.addEventListener('submit', function (e) {
        const form = e.target;

        // For forms with novalidate, honour HTML5 validity before locking.
        if (form.hasAttribute('novalidate') && !form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const btn = form.querySelector('[type="submit"]');
        if (!btn || btn.dataset.noLoading !== undefined) return;

        const originalHtml  = btn.innerHTML;
        const loadingText   = btn.dataset.loadingText || 'Processing…';

        btn.disabled  = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"
                              role="status" aria-hidden="true"></span>${loadingText}`;

        // Re-enable on back-button navigation (bfcache pageshow).
        window.addEventListener('pageshow', function onShow(ev) {
            if (ev.persisted) {
                btn.disabled  = false;
                btn.innerHTML = originalHtml;
            }
            window.removeEventListener('pageshow', onShow);
        });
    });
</script>
<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    document.getElementById('sidebar-toggle').addEventListener('click', () => {
        const open = sidebar.classList.toggle('show');
        overlay.classList.toggle('show', open);
        document.body.style.overflow = open ? 'hidden' : '';
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    });
</script>
@stack('scripts')
</body>
</html>
