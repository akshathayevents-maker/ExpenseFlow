@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — ' : '' }}ExpenseFlow</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root { --sidebar-width: 260px; --topbar-height: 56px; }

        body { background: #f0f2f5; font-size: .9rem; }

        /* Topbar */
        #topbar {
            height: var(--topbar-height);
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 1030; background: #1e293b; color: #fff;
        }
        #topbar .brand {
            width: var(--sidebar-width); font-weight: 700;
            font-size: 1.1rem; letter-spacing: .5px; flex-shrink: 0;
        }

        /* Sidebar */
        #sidebar {
            position: fixed; top: var(--topbar-height);
            left: 0; bottom: 0; width: var(--sidebar-width);
            background: #1e293b; overflow-y: auto;
            z-index: 1020; transition: transform .3s ease;
        }
        #sidebar .nav-link {
            color: #94a3b8; padding: .55rem 1.25rem;
            border-radius: 6px; margin: 2px 8px;
            transition: background .15s, color .15s; font-size: .875rem;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            background: rgba(255,255,255,.08); color: #fff;
        }
        #sidebar .nav-link .bi { width: 20px; margin-right: 8px; }
        #sidebar .sidebar-section {
            font-size: .7rem; text-transform: uppercase;
            letter-spacing: 1px; color: #475569;
            padding: 1rem 1.25rem .25rem; font-weight: 600;
        }

        /* Main */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            min-height: calc(100vh - var(--topbar-height));
            padding: 1.5rem;
        }

        /* Mobile */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
            #topbar .brand { width: auto; }
        }
        #sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.4); z-index: 1015;
        }
        #sidebar-overlay.show { display: block; }

        /* Components */
        .stat-card { border: none; border-radius: 12px; transition: box-shadow .2s; }
        .stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }
        .stat-card .icon-box {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center;
            justify-content: center; font-size: 1.4rem; flex-shrink: 0;
        }
        .table thead th {
            background: #f8fafc; font-weight: 600; font-size: .8rem;
            text-transform: uppercase; letter-spacing: .5px;
            color: #64748b; border-bottom: 2px solid #e2e8f0;
        }
        .role-badge { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; }
        .page-header {
            margin-bottom: 1.5rem; padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

{{-- Topbar --}}
<nav id="topbar" class="d-flex align-items-center px-3 gap-2">
    <div class="brand d-flex align-items-center gap-2">
        <button class="btn btn-sm text-white d-lg-none me-1" id="sidebar-toggle">
            <i class="bi bi-list fs-5"></i>
        </button>
        <i class="bi bi-receipt-cutoff text-primary"></i> ExpenseFlow
    </div>

    <div class="ms-auto d-flex align-items-center gap-2">
        @auth
        @php
            $unreadCount = auth()->user()->hasMany(\App\Models\AppNotification::class)->whereNull('read_at')->count();
            $recentNotifications = \App\Models\AppNotification::where('user_id', auth()->id())->latest()->limit(6)->get();
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
    <div class="pt-3 pb-4">
        <p class="sidebar-section">Main</p>
        @php $role = auth()->user()->role; @endphp

        @if($role === 'admin')
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <p class="sidebar-section mt-2">Management</p>
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
               class="nav-link {{ request()->routeIs('admin.expense-requests.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Expense Requests
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
            <p class="sidebar-section mt-2">Analytics</p>
            <a href="{{ route('admin.analytics.index') }}"
               class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Analytics
            </a>
            <a href="{{ route('admin.reports.index') }}"
               class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>
            <p class="sidebar-section mt-2">Operations</p>
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
        @elseif($role === 'manager')
            <a href="{{ route('manager.dashboard') }}"
               class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('manager.expense-requests.index') }}"
               class="nav-link {{ request()->routeIs('manager.expense-requests.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Expense Requests
            </a>
        @else
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
        @endif

        <p class="sidebar-section mt-2">Account</p>
        <a href="{{ route('notifications.index') }}"
           class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifications
            @php $nc = auth()->user()->hasMany(\App\Models\AppNotification::class)->whereNull('read_at')->count(); @endphp
            @if($nc > 0)
                <span class="badge bg-danger ms-auto" style="font-size:.6rem">{{ $nc }}</span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}"
           class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Profile
        </a>
    </div>
</nav>

{{-- Main content --}}
<main id="main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{ $slot }}
</main>

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
