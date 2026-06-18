@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' • ' : '' }}ExpenseFlow</title>

    {{-- ── Favicon ────────────────────────────────────────────────────── --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">

    {{-- ── PWA manifest ───────────────────────────────────────────────── --}}
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- ── Theme colour (browser chrome + Android task switcher) ────────── --}}
    {{-- Dark: matches app shell background. Light fallback via media query. --}}
    <meta name="theme-color" content="#0e0f0d" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#0e0f0d">

    {{-- ── Apple / iOS PWA ──────────────────────────────────────────────── --}}
    {{-- apple-mobile-web-app-capable: enables standalone mode when added to home screen --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    {{-- black-translucent: status bar overlaps the app (respects viewport-fit=cover + safe-area) --}}
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ExpenseFlow">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    {{-- Mask icon for Safari pinned tabs --}}
    <link rel="mask-icon" href="{{ asset('favicon.svg') }}?v=2" color="#B8893E">

    {{-- ── Android / Windows supplemental ──────────────────────────────── --}}
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="ExpenseFlow">
    <meta name="msapplication-TileColor" content="#0b0d0c">
    <meta name="msapplication-TileImage" content="{{ asset('android-chrome-192x192.png') }}">
    <meta name="msapplication-config" content="none">

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
    @else
        {{-- ASSET LOADING FAILED: manifest.json not found at {{ public_path('build/manifest.json') }} — run: npm ci && npm run build --}}
    @endif
    @if($appJs)
        <script type="module" src="{{ asset('build/' . $appJs) }}"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}?v=2">
    <style>
    /* ── Premium global toast ────────────────────────────────────── */
    #ef-toast-wrap {
        left: 50%;
        max-width: 420px;
        pointer-events: none;
        position: fixed;
        top: calc(var(--tb-height, 76px) + 12px);
        transform: translateX(-50%);
        width: calc(100% - 32px);
        z-index: 1200;
    }
    .ef-toast {
        align-items: center;
        animation: efToastIn .28s cubic-bezier(.3,.7,.2,1) forwards;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,.14), 0 2px 6px rgba(0,0,0,.08);
        display: flex;
        font-size: .875rem;
        gap: 10px;
        margin-bottom: 8px;
        padding: 13px 14px 13px 16px;
        pointer-events: auto;
    }
    .ef-toast.--out {
        animation: efToastOut .25s ease forwards;
    }
    .ef-toast.--success {
        background: #fff;
        border-left: 4px solid #16a34a;
    }
    .ef-toast.--error {
        background: #fff;
        border-left: 4px solid #dc2626;
    }
    .ef-toast.--info {
        background: #fff;
        border-left: 4px solid #2563eb;
    }
    .ef-toast.--warning {
        background: #fff;
        border-left: 4px solid #d97706;
    }
    .ef-toast-icon {
        flex-shrink: 0;
        font-size: 1.05rem;
        line-height: 1;
    }
    .ef-toast.--success .ef-toast-icon { color: #16a34a; }
    .ef-toast.--error   .ef-toast-icon { color: #dc2626; }
    .ef-toast.--info    .ef-toast-icon { color: #2563eb; }
    .ef-toast.--warning .ef-toast-icon { color: #d97706; }
    .ef-toast-msg {
        color: #111827;
        flex: 1;
        font-weight: 500;
        line-height: 1.4;
    }
    .ef-toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        flex-shrink: 0;
        font-size: 1rem;
        line-height: 1;
        padding: 2px 0 2px 4px;
        transition: color .15s;
    }
    .ef-toast-close:hover { color: #374151; }
    @keyframes efToastIn {
        from { opacity: 0; transform: translateY(-10px) scale(.97); }
        to   { opacity: 1; transform: translateY(0)     scale(1); }
    }
    @keyframes efToastOut {
        from { opacity: 1; transform: translateY(0)    scale(1); }
        to   { opacity: 0; transform: translateY(-6px) scale(.97); }
    }
    </style>

    <style>
        :root {
            --sb-width:     280px;
            /*
             * ── Topbar height system ──────────────────────────────────────
             * --tb-base    = content row height (no safe-area).
             *               64px = premium height. On iOS (SAI ≥ 20px always),
             *               total ≥ 84px. On notch (SAI 34px) = 98px.
             *               On Android browser (SAI 0) = 64px.
             * --tb-height  = total fixed height including safe-area inset.
             *               Used for: topbar height, sidebar top, main-content margin.
             */
            --tb-base:      64px;
            --tb-height:    calc(var(--tb-base) + env(safe-area-inset-top, 0px));
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
            /*
             * ─── Safe-area two-layer pattern (iOS / PWA / standalone) ────
             *
             * Problem: env(safe-area-inset-top) is the notch / Dynamic Island /
             * status-bar height. Content must NOT appear there. Using
             * align-items:center + padding-top on a fixed flex container is
             * broken on iOS Safari: it centers in the TOTAL height (content +
             * padding), so items land inside the status bar.
             *
             * Solution — two layers:
             *   OUTER (#topbar)
             *     height: var(--tb-height) = content + safe-area
             *     flex-direction: column; justify-content: flex-end
             *     padding-top: env(safe-area-inset-top)   ← absorbs the inset
             *   INNER (.ef-topbar-inner)
             *     height: var(--tb-base) = content only (64px)
             *     flex-direction: row; align-items: center   ← centering is correct
             *       because the inner has no padding-top
             *
             * Result: inner row always sits BELOW the notch, perfectly centered.
             * Works in browser, PWA standalone, installed home-screen app.
             * ──────────────────────────────────────────────────────────── */
            height: var(--tb-height);
            position: fixed; top: 0; left: 0; right: 0; z-index: 1030;
            /* Premium glassmorphism: dark translucent + blur + gold grain */
            background: rgba(10, 12, 10, 0.96);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            backdrop-filter: blur(24px) saturate(160%);
            /* Gold-tinted subtle border — brand identity cue */
            border-bottom: 1px solid rgba(184, 137, 62, 0.18);
            box-shadow: 0 1px 0 rgba(184, 137, 62, 0.06), 0 4px 24px rgba(0, 0, 0, 0.18);
            /* Two-layer flex */
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            /* Absorb all safe-area sides */
            padding-top:   env(safe-area-inset-top, 0px);
            padding-left:  env(safe-area-inset-left, 0px);
            padding-right: env(safe-area-inset-right, 0px);
            /* Stacking context — no transform (transform breaks fixed positioning on iOS) */
            isolation: isolate;
        }
        /* ── Inner content row — always 64px, always below the safe area ── */
        .ef-topbar-inner {
            height: var(--tb-base);    /* exactly 64px — never changes */
            display: flex;
            align-items: center;       /* safe: no padding-top on THIS element */
            gap: 0.75rem;
            padding: 0 1rem;
            flex-shrink: 0;
            width: 100%;
        }
        #topbar .brand {
            width: var(--sb-width); font-weight: 720; font-size: .96rem;
            letter-spacing: .02em; flex-shrink: 0; color: rgba(255,255,255,.92);
        }
        #topbar .brand .bi { color: var(--sb-gold); }

        /* ── Hamburger touch target — 44px minimum (WCAG 2.5.5) ─────────── */
        #sidebar-toggle {
            min-width: 44px; min-height: 44px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px;
            transition: background .15s ease;
        }
        #sidebar-toggle:hover, #sidebar-toggle:focus-visible {
            background: rgba(255,255,255,.08);
        }
        #sidebar-toggle:active {
            background: rgba(255,255,255,.14);
        }

        /* ═══════════════════════════════════════════════════════════════════
           SIDEBAR  —  Premium dark drawer
           Layout: flex column → user-strip (fixed) | scroll-body (flex:1) | footer (fixed)
           This lets the header and footer stay pinned while only the nav
           links scroll, exactly like a native mobile banking app.
        ═══════════════════════════════════════════════════════════════════ */
        #sidebar {
            position: fixed; top: var(--tb-height);
            left: 0; bottom: 0; width: var(--sb-width);
            background: linear-gradient(180deg, #0e1210 0%, #111713 28%, #0b0f0d 100%);
            border-right: 1px solid rgba(184,137,62,.12);
            box-shadow: 4px 0 40px rgba(0,0,0,.28);
            overflow: hidden;                    /* scroll delegated to .sb-scroll-body */
            z-index: 1020;
            transition: transform .28s cubic-bezier(.2,.7,.2,1);
            display: flex; flex-direction: column;
        }

        /* ── User profile strip ─────────────────────────────────────────── */
        .sb-user-strip {
            display: flex; align-items: center; gap: 11px;
            padding: 14px 16px 13px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
            background: rgba(255,255,255,.015);
        }
        .sb-user-avatar {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, #1a6645, #22845a);
            color: #fff; font-size: .74rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,.14);
            letter-spacing: .03em;
        }
        .sb-user-name {
            font-size: .82rem; font-weight: 700;
            color: rgba(255,255,255,.9);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 1.25;
        }
        .sb-user-role {
            font-size: .62rem; font-weight: 800;
            letter-spacing: .09em; text-transform: uppercase;
            color: rgba(184,137,62,.65);
            margin-top: 2px;
        }
        .sb-user-online-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 6px rgba(34,197,94,.6);
            margin-left: auto; flex-shrink: 0;
        }

        /* ── Scrollable nav area ────────────────────────────────────────── */
        .sb-scroll-body {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.07) transparent;
        }
        .sb-scroll-body::-webkit-scrollbar { width: 3px; }
        .sb-scroll-body::-webkit-scrollbar-thumb { background: rgba(184,137,62,.15); border-radius: 2px; }

        /* ── Nav link (child items & standalone items) ──────────────────── */
        #sidebar .nav-link {
            color: rgba(255,255,255,.65);
            padding: .58rem 1rem;
            border-radius: 10px;
            margin: 1px 10px;
            border: 1px solid transparent;
            font-size: .82rem; font-weight: 500;
            display: flex; align-items: center;
            transition: background .18s ease, color .18s ease, transform .15s ease;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            text-decoration: none;
            min-height: 40px;
        }
        #sidebar .nav-link:hover {
            background: rgba(184,137,62,.07);
            color: #F5E7C8;
            transform: translateX(2px);
        }
        #sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(184,137,62,.16), rgba(184,137,62,.04));
            border-color: rgba(184,137,62,.28);
            box-shadow: inset 2px 0 0 rgba(184,137,62,.55), 0 2px 12px rgba(184,137,62,.08);
            color: #FFF4DA;
            font-weight: 600;
        }
        #sidebar .nav-link .bi {
            width: 18px; margin-right: 10px; flex-shrink: 0;
            font-size: .95rem; text-align: center;
            color: rgba(255,255,255,.35);
            transition: color .18s ease;
        }
        #sidebar .nav-link:hover .bi  { color: #D6B97A; }
        #sidebar .nav-link.active .bi { color: #E7C98A; }

        /* Standalone section (no accordion parent) */
        .sidebar-standalone { padding: .15rem 0; }

        /* ── Group header button (premium accordion row) ────────────────────
           Visual match to nav-link — same height, same padding, same colours.
           Old design was a 10px uppercase section-divider label.
           New design is an interactive row with left icon, label, chevron.
        ─────────────────────────────────────────────────────────────────── */
        .sidebar-group-btn {
            /* Reset */
            background: none; border: none; outline: none;
            /* Sizing — match nav-link exactly */
            width: calc(100% - 20px);
            margin: 1px 10px;
            padding: .62rem 1rem;
            min-height: 42px;
            border-radius: 10px;
            border: 1px solid transparent;
            /* Typography */
            font-size: .83rem; font-weight: 600; letter-spacing: .01em;
            text-transform: none;                /* was uppercase */
            line-height: 1;
            /* Layout */
            display: flex; align-items: center; gap: 0;
            cursor: pointer; text-align: left;
            /* Colours */
            color: rgba(255,255,255,.65);
            transition: background .18s ease, color .18s ease;
        }
        .sidebar-group-btn:hover {
            background: rgba(184,137,62,.07);
            color: #F5E7C8;
        }
        .sidebar-group-btn.has-active { color: rgba(245,231,200,.9); }

        /* Group icon (matches .bi slot on nav-links) */
        .sb-grp-icon {
            width: 18px; margin-right: 10px; flex-shrink: 0;
            font-size: .95rem; text-align: center;
            color: rgba(255,255,255,.32);
            transition: color .18s ease;
        }
        .sidebar-group-btn:hover   .sb-grp-icon { color: #D6B97A; }
        .sidebar-group-btn.has-active .sb-grp-icon { color: #D6B97A; }

        /* Label fills remaining space */
        .sb-grp-label { flex: 1; }

        /* Chevron — animated on expand/collapse */
        .sidebar-chevron {
            font-size: .72rem; flex-shrink: 0; margin-left: 4px;
            color: rgba(214,185,122,.3);
            transition: transform .22s ease, color .18s ease;
        }
        .sidebar-group-btn:hover .sidebar-chevron { color: rgba(214,185,122,.55); }
        .sidebar-group-btn[aria-expanded="false"] .sidebar-chevron { transform: rotate(-90deg); }
        .sidebar-group-btn[aria-expanded="true"]  .sidebar-chevron { transform: rotate(0deg); }

        /* Child links inside accordion body — extra left indent for hierarchy */
        .sidebar-group-body { padding: 2px 0; }
        .sidebar-group-body .nav-link {
            padding-left: 2.85rem;
            font-size: .8rem;
            min-height: 38px;
        }

        /* Divider */
        .sb-divider {
            height: 1px; background: rgba(255,255,255,.05);
            margin: .4rem 14px;
        }

        /* Account group separator */
        #grp-account-wrap {
            border-top: 1px solid rgba(255,255,255,.05);
            padding-top: .15rem;
        }

        /* ── Sticky footer ──────────────────────────────────────────────── */
        .sb-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,.06);
            padding: 10px 10px calc(10px + env(safe-area-inset-bottom, 0px));
            background: rgba(0,0,0,.12);
        }
        .sb-signout-btn {
            display: flex; align-items: center; gap: 9px;
            width: 100%; padding: 10px 14px;
            background: rgba(239,68,68,.07);
            border: 1px solid rgba(239,68,68,.14);
            border-radius: 10px; cursor: pointer;
            font-family: inherit; font-size: .82rem; font-weight: 700;
            color: #fca5a5; text-align: left;
            transition: background .15s, border-color .15s;
            min-height: 40px;
        }
        .sb-signout-btn:hover {
            background: rgba(239,68,68,.14);
            border-color: rgba(239,68,68,.25);
        }
        .sb-signout-btn .bi { font-size: .9rem; flex-shrink: 0; }
        .sb-meta {
            display: flex; align-items: center; justify-content: space-between;
            padding: 7px 6px 0;
            font-size: .62rem; color: rgba(255,255,255,.18);
            letter-spacing: .02em;
        }
        .sb-env-badge {
            font-size: .58rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .07em;
            background: rgba(251,146,60,.12);
            color: #fb923c;
            border: 1px solid rgba(251,146,60,.22);
            border-radius: 5px; padding: 1px 6px;
        }

        /* ── Main ────────────────────────────────────────────────────────── */
        #main-content {
            margin-left: var(--sb-width);
            margin-top: var(--tb-height);
            /*
             * ANDROID SCROLL FIX: Use 100dvh (dynamic viewport height).
             * 100vh on Android Chrome = page height BEFORE the URL bar hides.
             * When the URL bar hides (on scroll), 100vh doesn't update, causing
             * content to be shorter than the visible viewport = blank gap.
             * 100dvh tracks the actual visible viewport including URL-bar changes.
             * Use clip not hidden: hidden creates a BFC that confuses Android
             * Chrome's scroll container detection.
             */
            min-height: calc(100dvh - var(--tb-height));
            overflow-x: clip;
            padding: 32px;
        }

        /* ── Mobile ──────────────────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; overflow-x: clip; padding: 16px; }
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

        .collapse {
            visibility: visible !important;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Topbar --}}
<nav id="topbar">
<div class="ef-topbar-inner">
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
</div>{{-- /.ef-topbar-inner --}}
</nav>

<div id="sidebar-overlay"></div>

{{-- ══════════════════════════════════════════════════════════════════
     SIDEBAR — Premium dark ERP navigation
     Structure:
       .sb-user-strip   → fixed header: user avatar + name + role
       .sb-scroll-body  → flex:1, scrollable: all nav groups
       .sb-footer       → fixed footer: sign-out + env badge
     Accordion groups use Bootstrap collapse (unchanged).
     All routes, permissions, active-state logic unchanged.
══════════════════════════════════════════════════════════════════ --}}
<nav id="sidebar" aria-label="Main navigation">
@php
    $sbUser   = auth()->user();
    $role     = $sbUser->role;
    $sbInits  = strtoupper(implode('', array_map(
        fn($w) => $w[0],
        array_slice(explode(' ', $sbUser->name), 0, 2)
    )));
    $sbRoleLabel = match($role) {
        'admin'    => 'Administrator',
        'manager'  => 'Manager',
        'employee' => 'Employee',
        default    => ucfirst($role),
    };

    // Pre-compute open states server-side — no JS needed, no flicker.
    $grpExpenses  = request()->routeIs('admin.expense-requests.*','admin.wallets.*','admin.payments.*');
    $grpInventory = request()->routeIs('admin.inventory.*','admin.purchase-plans.*');
    $grpSetup     = request()->routeIs('admin.employees.*','admin.categories.*','admin.vendors.*');
    $grpAnalytics = request()->routeIs('admin.analytics.*','admin.reports.*');
    $grpOps       = request()->routeIs('admin.daily-closings.*','admin.audit-logs.*','admin.settings.*');
    $grpHall      = request()->routeIs('hall.*');
    $grpKitchen   = request()->routeIs('kitchen.recipes.*');
    $grpMenu      = request()->routeIs('menu.*');
    $grpAccount   = request()->routeIs('notifications.*','profile.*');
    $nc           = $sbUser->hasMany(\App\Models\AppNotification::class)->whereNull('read_at')->count();
@endphp

    {{-- ── User profile strip ────────────────────────────────────── --}}
    <div class="sb-user-strip">
        <div class="sb-user-avatar" aria-hidden="true">{{ $sbInits }}</div>
        <div style="flex:1;min-width:0">
            <div class="sb-user-name">{{ $sbUser->name }}</div>
            <div class="sb-user-role">{{ $sbRoleLabel }}</div>
        </div>
        <span class="sb-user-online-dot" aria-hidden="true" title="Active"></span>
    </div>

    {{-- ── Scrollable nav body ────────────────────────────────────── --}}
    <div class="sb-scroll-body">
    <div class="pt-1 pb-3">

    {{-- ══ ADMIN ══════════════════════════════════════════════════ --}}
    @if($role === 'admin')

        {{-- Dashboard (standalone) --}}
        <div class="sidebar-standalone pt-2">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>

        {{-- Expenses ────────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpExpenses ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-expenses"
                aria-expanded="{{ $grpExpenses ? 'true' : 'false' }}">
            <i class="bi bi-file-earmark-text sb-grp-icon"></i>
            <span class="sb-grp-label">Expenses</span>
            <i class="bi bi-chevron-down sidebar-chevron"></i>
        </button>
        <div class="collapse sidebar-group-body {{ $grpExpenses ? 'show' : '' }}" id="grp-expenses">
            <a href="{{ route('admin.expense-requests.index') }}"
               class="nav-link {{ request()->routeIs('admin.expense-requests.*') && !request()->routeIs('admin.expense-requests.create','admin.expense-requests.success') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> All Requests
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
        </div>

        {{-- Inventory ────────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpInventory ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-inventory"
                aria-expanded="{{ $grpInventory ? 'true' : 'false' }}">
            <i class="bi bi-boxes sb-grp-icon"></i>
            <span class="sb-grp-label">Inventory</span>
            <i class="bi bi-chevron-down sidebar-chevron"></i>
        </button>
        <div class="collapse sidebar-group-body {{ $grpInventory ? 'show' : '' }}" id="grp-inventory">
            <a href="{{ route('admin.inventory.items.index') }}"
               class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> Items
            </a>
            <a href="{{ route('admin.purchase-plans.index') }}"
               class="nav-link {{ request()->routeIs('admin.purchase-plans.*') ? 'active' : '' }}">
                <i class="bi bi-cart3"></i> Purchase Plans
            </a>
        </div>

        {{-- Setup ───────────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpSetup ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-setup"
                aria-expanded="{{ $grpSetup ? 'true' : 'false' }}">
            <i class="bi bi-sliders sb-grp-icon"></i>
            <span class="sb-grp-label">Setup</span>
            <i class="bi bi-chevron-down sidebar-chevron"></i>
        </button>
        <div class="collapse sidebar-group-body {{ $grpSetup ? 'show' : '' }}" id="grp-setup">
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
        </div>

        {{-- Analytics ───────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpAnalytics ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-analytics"
                aria-expanded="{{ $grpAnalytics ? 'true' : 'false' }}">
            <i class="bi bi-graph-up-arrow sb-grp-icon"></i>
            <span class="sb-grp-label">Analytics</span>
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

        {{-- Operations ──────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpOps ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-ops"
                aria-expanded="{{ $grpOps ? 'true' : 'false' }}">
            <i class="bi bi-gear-wide-connected sb-grp-icon"></i>
            <span class="sb-grp-label">Operations</span>
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

        {{-- Hall Management ─────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpHall ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-hall"
                aria-expanded="{{ $grpHall ? 'true' : 'false' }}">
            <i class="bi bi-building sb-grp-icon"></i>
            <span class="sb-grp-label">Hall Management</span>
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

        {{-- Kitchen ─────────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ ($grpKitchen || $grpMenu) ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-kitchen-admin"
                aria-expanded="{{ ($grpKitchen || $grpMenu) ? 'true' : 'false' }}">
            <i class="bi bi-fire sb-grp-icon"></i>
            <span class="sb-grp-label">Kitchen</span>
            <i class="bi bi-chevron-down sidebar-chevron"></i>
        </button>
        <div class="collapse sidebar-group-body {{ ($grpKitchen || $grpMenu) ? 'show' : '' }}" id="grp-kitchen-admin">
            <a href="{{ route('kitchen.recipes.index') }}"
               class="nav-link {{ request()->routeIs('kitchen.recipes.*') ? 'active' : '' }}">
                <i class="bi bi-journal-richtext"></i> Recipe Library
            </a>
            <a href="{{ route('menu.composer.index') }}"
               class="nav-link {{ request()->routeIs('menu.composer.*','menu.drafts.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i> Menu Composer
            </a>
            <a href="{{ route('menu.templates.index') }}"
               class="nav-link {{ request()->routeIs('menu.templates.*') ? 'active' : '' }}">
                <i class="bi bi-collection"></i> Menu Templates
            </a>
            <a href="{{ route('menu.items.index') }}"
               class="nav-link {{ request()->routeIs('menu.items.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Menu Items
            </a>
        </div>

    {{-- ══ MANAGER ════════════════════════════════════════════════ --}}
    @elseif($role === 'manager')

        <div class="sidebar-standalone pt-2">
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

        {{-- Hall Management ─────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpHall ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-hall"
                aria-expanded="{{ $grpHall ? 'true' : 'false' }}">
            <i class="bi bi-building sb-grp-icon"></i>
            <span class="sb-grp-label">Hall Management</span>
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

        {{-- Kitchen ─────────────────────────────────────────────── --}}
        <button class="sidebar-group-btn {{ $grpKitchen ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-kitchen-mgr"
                aria-expanded="{{ $grpKitchen ? 'true' : 'false' }}">
            <i class="bi bi-fire sb-grp-icon"></i>
            <span class="sb-grp-label">Kitchen</span>
            <i class="bi bi-chevron-down sidebar-chevron"></i>
        </button>
        <div class="collapse sidebar-group-body {{ $grpKitchen ? 'show' : '' }}" id="grp-kitchen-mgr">
            <a href="{{ route('kitchen.recipes.index') }}"
               class="nav-link {{ request()->routeIs('kitchen.recipes.*') ? 'active' : '' }}">
                <i class="bi bi-journal-richtext"></i> Recipe Library
            </a>
        </div>

    {{-- ══ EMPLOYEE ════════════════════════════════════════════════ --}}
    @else

        <div class="sidebar-standalone pt-2">
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
            <a href="{{ route('employee.hall.bookings.calendar') }}"
               class="nav-link {{ request()->routeIs('employee.hall.bookings.calendar') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Hall Calendar
            </a>
            <a href="{{ route('employee.kitchen.calculator') }}"
               class="nav-link {{ request()->routeIs('employee.kitchen.*') ? 'active' : '' }}">
                <i class="bi bi-fire"></i> Kitchen Calculator
            </a>
        </div>

    @endif

    {{-- ── Account (all roles) ─────────────────────────────────── --}}
    <div id="grp-account-wrap">
        <button class="sidebar-group-btn {{ $grpAccount ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#grp-account"
                aria-expanded="{{ $grpAccount ? 'true' : 'false' }}">
            <i class="bi bi-person-circle sb-grp-icon"></i>
            <span class="sb-grp-label">Account</span>
            <i class="bi bi-chevron-down sidebar-chevron"></i>
        </button>
        <div class="collapse sidebar-group-body {{ $grpAccount ? 'show' : '' }}" id="grp-account">
            <a href="{{ route('notifications.index') }}"
               class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i> Notifications
                @if($nc > 0)
                    <span class="badge bg-danger ms-auto" style="font-size:.58rem;padding:2px 5px">{{ $nc > 99 ? '99+' : $nc }}</span>
                @endif
            </a>
            <a href="{{ route('profile.edit') }}"
               class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i> Profile
            </a>
        </div>
    </div>

    </div>
    </div>{{-- /.sb-scroll-body --}}

    {{-- ── Sticky footer ──────────────────────────────────────── --}}
    <div class="sb-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-signout-btn" data-no-loading>
                <i class="bi bi-box-arrow-right"></i>
                Sign Out
            </button>
        </form>
        <div class="sb-meta">
            <span>ExpenseFlow</span>
            @if(config('app.env') !== 'production')
                <span class="sb-env-badge">{{ config('app.env') }}</span>
            @endif
        </div>
    </div>

</nav>

{{-- Premium global toast (single source of truth for all flash messages) --}}
<div id="ef-toast-wrap" role="status" aria-live="polite" aria-atomic="true">
    @if(session('success'))
    <div class="ef-toast --success" role="alert">
        <i class="bi bi-check-circle-fill ef-toast-icon"></i>
        <span class="ef-toast-msg">{{ session('success') }}</span>
        <button type="button" class="ef-toast-close" aria-label="Close">&#x2715;</button>
    </div>
    @endif
    @if(session('error'))
    <div class="ef-toast --error" role="alert">
        <i class="bi bi-exclamation-triangle-fill ef-toast-icon"></i>
        <span class="ef-toast-msg">{{ session('error') }}</span>
        <button type="button" class="ef-toast-close" aria-label="Close">&#x2715;</button>
    </div>
    @endif
    @if(session('info'))
    <div class="ef-toast --info" role="alert">
        <i class="bi bi-info-circle-fill ef-toast-icon"></i>
        <span class="ef-toast-msg">{{ session('info') }}</span>
        <button type="button" class="ef-toast-close" aria-label="Close">&#x2715;</button>
    </div>
    @endif
    @if(session('warning'))
    <div class="ef-toast --warning" role="alert">
        <i class="bi bi-exclamation-triangle-fill ef-toast-icon"></i>
        <span class="ef-toast-msg">{{ session('warning') }}</span>
        <button type="button" class="ef-toast-close" aria-label="Close">&#x2715;</button>
    </div>
    @endif
</div>

{{-- Main content --}}
<main id="main-content">
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
        <a href="{{ route('employee.kitchen.calculator') }}"
           class="ef-m-bottomnav-item {{ request()->routeIs('employee.kitchen.*') ? 'active' : '' }}">
            <div class="ef-m-bottomnav-icon-wrap"><i class="bi bi-fire"></i></div>
            <span>Kitchen</span>
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

{{-- ── PWA Install Prompt ──────────────────────────────────────────── --}}
{{-- Shown on Android (beforeinstallprompt) and iOS (manual instruction) --}}
<div id="pwa-install-banner" aria-hidden="true" style="
    display:none;
    position:fixed;
    bottom:0;left:0;right:0;
    z-index:9999;
    background:rgba(14,15,13,.97);
    border-top:1px solid rgba(184,137,62,.25);
    backdrop-filter:blur(20px);
    -webkit-backdrop-filter:blur(20px);
    padding:14px 16px calc(14px + env(safe-area-inset-bottom,0px));
    color:#f0ede6;
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
">
    <div style="display:flex;align-items:flex-start;gap:12px;max-width:480px;margin:0 auto">
        <div style="flex-shrink:0;font-size:1.8rem;line-height:1">📲</div>
        <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:.9rem;margin-bottom:3px;color:#f5ede0">
                Install ExpenseFlow
            </div>
            <div id="pwa-install-msg" style="font-size:.78rem;color:rgba(240,237,230,.55);line-height:1.45">
                Add to your home screen for a native app experience.
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;align-items:center;margin-top:2px">
            <button id="pwa-install-btn" style="
                background:#B8893E;border:none;border-radius:8px;color:#fff;
                cursor:pointer;font-size:.8rem;font-weight:700;padding:8px 14px;
                white-space:nowrap;
            ">Install</button>
            <button id="pwa-dismiss-btn" aria-label="Dismiss" style="
                background:none;border:1px solid rgba(255,255,255,.12);border-radius:8px;
                color:rgba(240,237,230,.5);cursor:pointer;font-size:.8rem;padding:8px 10px;
            ">✕</button>
        </div>
    </div>
</div>

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

    // Scroll lock: position:fixed pattern, class-based (safe on iOS + Android).
    //
    // WHY NOT body.style.overflow = 'hidden':
    //   iOS Safari reparents fixed elements into the scroll layer when body
    //   overflow changes — bottom nav scrolls with content.
    //
    // WHY NOT document.body.style.cssText = '...':
    //   cssText REPLACES all existing inline styles — destroys Bootstrap modal's
    //   padding-right compensation and any other dynamic inline styles, causing
    //   layout jumps when the drawer closes while a modal is open.
    //
    // CLASS-BASED FIX: .ef-scroll-locked defined in app.css applies
    //   position:fixed + overflow-y:scroll without touching other inline styles.
    //   Only body.style.top is written as an inline property (minimal footprint).
    let _sidebarScrollY = 0;
    const _mainContent   = document.getElementById('main-content');

    function lockBodyScroll() {
        if (window.innerWidth <= 767 && _mainContent) {
            // Mobile: body is overflow:hidden (flex container) — body itself never scrolls.
            // Lock the #main-content scroll container instead.
            _sidebarScrollY = _mainContent.scrollTop;
            _mainContent.style.overflowY = 'hidden';
        } else {
            // Desktop: classic body scroll lock.
            _sidebarScrollY = window.scrollY;
            document.body.style.top = '-' + _sidebarScrollY + 'px';
            document.body.classList.add('ef-scroll-locked');
        }
    }
    function unlockBodyScroll() {
        if (window.innerWidth <= 767 && _mainContent) {
            _mainContent.style.overflowY = '';
            _mainContent.scrollTop = _sidebarScrollY;
        } else {
            document.body.classList.remove('ef-scroll-locked');
            document.body.style.top = '';
            window.scrollTo(0, _sidebarScrollY);
        }
    }

    document.getElementById('sidebar-toggle').addEventListener('click', () => {
        const open = sidebar.classList.toggle('show');
        overlay.classList.toggle('show', open);
        if (window.innerWidth < 992) {
            open ? lockBodyScroll() : unlockBodyScroll();
        }
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        unlockBodyScroll();
    });
</script>
<script>
(function () {
    var DISMISS_MS = 4500;
    document.querySelectorAll('.ef-toast').forEach(function (toast) {
        var timer = setTimeout(function () { dismiss(toast); }, DISMISS_MS);
        var closeBtn = toast.querySelector('.ef-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(timer);
                dismiss(toast);
            });
        }
    });
    function dismiss(toast) {
        toast.classList.add('--out');
        setTimeout(function () {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 260);
    }
}());
</script>
@stack('scripts')

<script>
/* ─── Service Worker registration ─────────────────────────────── */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/' })
            .catch(function (err) {
                // Non-fatal — app still works without SW
                console.warn('[SW] Registration failed:', err);
            });
    });
}

/* ─── PWA Install Prompt ───────────────────────────────────────── */
(function () {
    var DISMISS_KEY  = 'ef_pwa_dismissed';
    var DISMISS_DAYS = 14; // don't re-show for 14 days after dismiss
    var banner       = document.getElementById('pwa-install-banner');
    var installBtn   = document.getElementById('pwa-install-btn');
    var dismissBtn   = document.getElementById('pwa-dismiss-btn');
    var msgEl        = document.getElementById('pwa-install-msg');
    var deferredPrompt = null;

    // Don't show if: already installed standalone, or recently dismissed
    function isDismissed() {
        var ts = localStorage.getItem(DISMISS_KEY);
        if (!ts) return false;
        return (Date.now() - Number(ts)) < DISMISS_DAYS * 86400000;
    }
    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true
            || document.referrer.includes('android-app://');
    }
    function showBanner() {
        if (isStandalone() || isDismissed()) return;
        banner.style.display = 'block';
        banner.removeAttribute('aria-hidden');
    }
    function hideBanner() {
        banner.style.display = 'none';
        banner.setAttribute('aria-hidden', 'true');
    }

    // ── Android Chrome: beforeinstallprompt ─────────────────────
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        msgEl.textContent = 'Add to your home screen for the full app experience — no App Store needed.';
        showBanner();
    });

    installBtn.addEventListener('click', function () {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (result) {
                if (result.outcome === 'accepted') {
                    hideBanner();
                }
                deferredPrompt = null;
            });
        } else {
            // iOS fallback — button navigates to instructions
            hideBanner();
        }
    });

    // ── iOS Safari: detect browser (not standalone) + show instructions
    var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    var isSafari = /safari/i.test(navigator.userAgent) && !/chrome|crios|fxios/i.test(navigator.userAgent);
    if (isIos && isSafari && !isStandalone()) {
        msgEl.innerHTML = 'Tap <strong style="color:#B8893E">Share</strong> then <strong style="color:#B8893E">Add to Home Screen</strong> for the app experience.';
        installBtn.textContent = 'Got it';
        installBtn.addEventListener('click', function () {
            hideBanner();
            localStorage.setItem(DISMISS_KEY, Date.now());
        });
        showBanner();
    }

    dismissBtn.addEventListener('click', function () {
        hideBanner();
        localStorage.setItem(DISMISS_KEY, Date.now());
    });

    // Hide banner when app is actually installed
    window.addEventListener('appinstalled', function () {
        hideBanner();
        localStorage.setItem(DISMISS_KEY, Date.now());
    });
}());
</script>
</body>
</html>
