{{--
    <x-mobile-nav title="Page Title" />

    Self-contained mobile navigation component for standalone/public pages.
    Renders:
      1. <style>   — all nav CSS, scoped under .ef-mnav-* prefix
      2. Topbar    — fixed, safe-area-aware, hamburger left / title center
      3. Backdrop  — tap-to-close overlay
      4. Drawer    — slides in from left, role-aware nav links
      5. <script>  — open / close / scroll-lock / keyboard JS

    Z-index stack:
      topbar         300
      backdrop       400
      drawer         500
      (payment modals must be ≥ 600 — see show.blade.php)
      (QR overlay    9999)

    Safe-area: topbar padding-top = env(safe-area-inset-top)
    Body padding-top on host page must be:
      calc(52px + env(safe-area-inset-top, 0px) + 16px)
--}}

@props(['title' => 'ExpenseFlow'])

@php
    $user = auth()->user();

    /* ── Build role-aware navigation links ─────────────────────── */
    $navLinks = [];

    if ($user) {
        if ($user->isAdmin()) {
            $navLinks = [
                ['icon' => '🏠', 'label' => 'Dashboard',  'href' => route('admin.dashboard')],
                ['icon' => '📅', 'label' => 'Bookings',   'href' => route('hall.bookings.index')],
                ['icon' => '💳', 'label' => 'Payments',   'href' => route('admin.payments.index')],
                ['icon' => '📄', 'label' => 'Expenses',   'href' => route('admin.expense-requests.index')],
                ['icon' => '👛', 'label' => 'Wallets',    'href' => route('admin.wallets.index')],
                ['icon' => '📊', 'label' => 'Reports',    'href' => route('admin.reports.index')],
                ['icon' => '👤', 'label' => 'Profile',    'href' => route('profile.edit')],
            ];
        } elseif ($user->isManager()) {
            $navLinks = [
                ['icon' => '🏠', 'label' => 'Dashboard',  'href' => route('hall.dashboard')],
                ['icon' => '📅', 'label' => 'Bookings',   'href' => route('hall.bookings.index')],
                ['icon' => '📄', 'label' => 'Expenses',   'href' => route('manager.expense-requests.index')],
                ['icon' => '👤', 'label' => 'Profile',    'href' => route('profile.edit')],
            ];
        } else {
            // Employee
            $navLinks = [
                ['icon' => '🏠', 'label' => 'Dashboard',   'href' => route('employee.dashboard')],
                ['icon' => '📄', 'label' => 'My Expenses', 'href' => route('employee.expense-requests.index')],
                ['icon' => '👛', 'label' => 'Wallet',      'href' => route('employee.wallet.show')],
                ['icon' => '👤', 'label' => 'Profile',     'href' => route('profile.edit')],
            ];
        }
    } else {
        // Public / unauthenticated visitor
        $navLinks = [
            ['icon' => '🔑', 'label' => 'Staff Login', 'href' => route('login')],
        ];
    }

    /* ── Active link detection ─────────────────────────────────── */
    $currentUrl = request()->url();
    $currentPath = '/' . ltrim(parse_url($currentUrl, PHP_URL_PATH), '/');
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     CSS  (scoped with .ef-mnav- prefix — safe to embed in body)
═══════════════════════════════════════════════════════════════════ --}}
<style>
/* ── Topbar ──────────────────────────────────────────────────────
   Base height: 52px content.
   Actual visual height on device: 52px + env(safe-area-inset-top).
   Host body must add this as padding-top to avoid topbar overlap.
──────────────────────────────────────────────────────────────── */
:root {
    --ef-topbar-base: 52px;
}

.ef-mnav-topbar {
    /* Fixed full-width bar — sits above all page content */
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 300;
    height: calc(var(--ef-topbar-base) + env(safe-area-inset-top, 0px));
    padding-top: env(safe-area-inset-top, 0px);

    /* Dark translucent brand background */
    background: rgba(12, 22, 16, 0.97);
    -webkit-backdrop-filter: blur(14px) saturate(180%);
    backdrop-filter: blur(14px) saturate(180%);
    border-bottom: 1px solid rgba(255, 255, 255, .06);

    /* Layout */
    display: flex;
    align-items: center;
    gap: 0;
    padding-left: env(safe-area-inset-left, 0px);
    padding-right: env(safe-area-inset-right, 0px);

    /* Stacking context without transform — iOS-safe */
    isolation: isolate;
}

/* Hamburger button */
.ef-mnav-hamburger {
    width: 52px; height: 52px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4.5px;
    background: none; border: none; cursor: pointer;
    padding: 0; flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
    border-radius: 0;
    /* Touch target ≥ 44px (WCAG 2.5.5) */
}
.ef-mnav-hamburger-line {
    display: block;
    width: 20px; height: 2px;
    background: rgba(255, 255, 255, .88);
    border-radius: 2px;
    transition: transform .22s ease, opacity .18s ease, width .18s ease;
    transform-origin: center;
}
/* Hamburger → X animation */
.ef-mnav-hamburger[aria-expanded="true"] .ef-mnav-hamburger-line:nth-child(1) {
    transform: translateY(6.5px) rotate(45deg);
}
.ef-mnav-hamburger[aria-expanded="true"] .ef-mnav-hamburger-line:nth-child(2) {
    opacity: 0; width: 0;
}
.ef-mnav-hamburger[aria-expanded="true"] .ef-mnav-hamburger-line:nth-child(3) {
    transform: translateY(-6.5px) rotate(-45deg);
}

/* Page title in topbar */
.ef-mnav-topbar-title {
    flex: 1;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .88rem; font-weight: 700;
    color: rgba(255, 255, 255, .9);
    letter-spacing: .01em;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    /* Centre between buttons — achieved by flex + equal-width side slots */
    text-align: center;
}

/* Right slot (avatar or spacer) */
.ef-mnav-topbar-right {
    width: 52px; height: 52px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ef-mnav-user-badge {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, #1a6645, #22845a);
    color: #fff; font-size: .72rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    border: 1.5px solid rgba(255,255,255,.15);
}

/* ── Backdrop ────────────────────────────────────────────────────
   Sits between topbar (300) and drawer (500).
   Fade transition on opacity + pointer-events.
──────────────────────────────────────────────────────────────── */
.ef-mnav-backdrop {
    position: fixed; inset: 0;
    z-index: 400;
    background: rgba(0, 0, 0, .55);
    -webkit-backdrop-filter: blur(2px);
    backdrop-filter: blur(2px);

    /* Hidden state */
    opacity: 0;
    pointer-events: none;
    transition: opacity .28s ease;
}
.ef-mnav-backdrop.open {
    opacity: 1;
    pointer-events: auto;
}

/* ── Drawer ──────────────────────────────────────────────────────
   Slides in from the left. GPU-composited (transform only).
   Width: min(82vw, 300px) — usable one-handed on all phones.
──────────────────────────────────────────────────────────────── */
.ef-mnav-drawer {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 500;
    width: min(82vw, 300px);

    /* Extend into safe areas */
    padding-top: env(safe-area-inset-top, 0px);
    padding-left: env(safe-area-inset-left, 0px);
    padding-bottom: env(safe-area-inset-bottom, 0px);

    background: #0b1710;
    border-right: 1px solid rgba(255, 255, 255, .07);
    box-shadow: 4px 0 32px rgba(0, 0, 0, .4);

    display: flex; flex-direction: column;
    overflow: hidden;

    /* Closed state */
    transform: translateX(-100%);
    transition: transform .28s cubic-bezier(.4, 0, .2, 1);
    will-change: transform; /* GPU layer — safe on drawer, NOT on fixed page elements */
}
.ef-mnav-drawer.open {
    transform: translateX(0);
}

/* Drawer header row */
.ef-mnav-drawer-header {
    display: flex; align-items: center;
    height: calc(var(--ef-topbar-base) + env(safe-area-inset-top, 0px));
    padding-top: env(safe-area-inset-top, 0px);
    padding: env(safe-area-inset-top, 0px) 20px 0 20px;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    min-height: 52px;
}
.ef-mnav-brand-name {
    flex: 1;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .9rem; font-weight: 800;
    color: rgba(255, 255, 255, .9);
    letter-spacing: .01em;
    display: flex; align-items: center; gap: 8px;
}
.ef-mnav-brand-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 6px rgba(34, 197, 94, .6);
    flex-shrink: 0;
}
.ef-mnav-close {
    width: 36px; height: 36px;
    background: rgba(255, 255, 255, .07); border: none;
    border-radius: 10px; color: rgba(255, 255, 255, .6);
    font-size: 1.1rem; cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    -webkit-tap-highlight-color: transparent;
    transition: background .15s;
    font-family: inherit; line-height: 1;
}
.ef-mnav-close:active { background: rgba(255, 255, 255, .14); }

/* User identity strip (authenticated only) */
.ef-mnav-user-strip {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    flex-shrink: 0;
}
.ef-mnav-user-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, #1a6645, #22845a);
    color: #fff; font-size: .8rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: 1.5px solid rgba(255,255,255,.12);
}
.ef-mnav-user-name {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .82rem; font-weight: 700;
    color: rgba(255, 255, 255, .88);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ef-mnav-user-role {
    font-size: .68rem; font-weight: 600;
    letter-spacing: .04em; text-transform: uppercase;
    color: rgba(255, 255, 255, .35);
    margin-top: 2px;
}

/* Section label */
.ef-mnav-section-label {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .6rem; font-weight: 800;
    letter-spacing: .14em; text-transform: uppercase;
    color: rgba(255, 255, 255, .3);
    padding: 14px 20px 6px;
    flex-shrink: 0;
}

/* Nav link list */
.ef-mnav-links {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    padding: 4px 10px;
    list-style: none;
}
.ef-mnav-link {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px;
    border-radius: 12px;
    text-decoration: none;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .86rem; font-weight: 600;
    color: rgba(255, 255, 255, .68);
    transition: background .15s, color .15s;
    -webkit-tap-highlight-color: transparent;
    margin-bottom: 2px;
}
.ef-mnav-link:active { background: rgba(255, 255, 255, .08); }
.ef-mnav-link:hover  { background: rgba(255, 255, 255, .06); color: rgba(255,255,255,.9); }
.ef-mnav-link.active {
    background: rgba(34, 132, 90, .25);
    color: #4ade80;
}
.ef-mnav-link-icon {
    font-size: 1.05rem; width: 24px; text-align: center;
    flex-shrink: 0;
}
.ef-mnav-link-label { flex: 1; }

/* Drawer footer */
.ef-mnav-drawer-footer {
    padding: 10px 10px;
    border-top: 1px solid rgba(255, 255, 255, .06);
    flex-shrink: 0;
    padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
}
.ef-mnav-signout {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 11px 14px;
    background: rgba(239, 68, 68, .1);
    border: 1px solid rgba(239, 68, 68, .18);
    border-radius: 12px; cursor: pointer;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .84rem; font-weight: 700;
    color: #fca5a5;
    text-align: left;
    -webkit-tap-highlight-color: transparent;
    transition: background .15s;
}
.ef-mnav-signout:active { background: rgba(239, 68, 68, .2); }

/* Sign-in link (unauthenticated footer) */
.ef-mnav-signin-link {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 11px 14px;
    background: rgba(26, 102, 69, .25);
    border: 1px solid rgba(34, 132, 90, .3);
    border-radius: 12px;
    text-decoration: none;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: .84rem; font-weight: 700;
    color: #4ade80;
    transition: background .15s;
    -webkit-tap-highlight-color: transparent;
}
.ef-mnav-signin-link:active { background: rgba(26, 102, 69, .4); }

/* ── Desktop: hide the mobile nav ───────────────────────────────
   The drawer is mobile-only. On ≥768px we assume the standard
   admin-layout sidebar handles navigation. Hide topbar too.
──────────────────────────────────────────────────────────────── */
@media (min-width: 768px) {
    .ef-mnav-topbar  { display: none; }
    .ef-mnav-backdrop { display: none !important; }
    .ef-mnav-drawer   { display: none !important; }
}
</style>

{{-- ═══════════════════════════════════════════════════════════════
     TOPBAR HTML
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-mnav-topbar" id="efNavTopbar" role="banner" aria-label="Site navigation">

    {{-- Hamburger --}}
    <button class="ef-mnav-hamburger"
            id="efNavHamburger"
            type="button"
            aria-label="Open navigation menu"
            aria-expanded="false"
            aria-controls="efNavDrawer">
        <span class="ef-mnav-hamburger-line" aria-hidden="true"></span>
        <span class="ef-mnav-hamburger-line" aria-hidden="true"></span>
        <span class="ef-mnav-hamburger-line" aria-hidden="true"></span>
    </button>

    {{-- Page title --}}
    <span class="ef-mnav-topbar-title">{{ $title }}</span>

    {{-- Right slot: user avatar or blank spacer --}}
    <div class="ef-mnav-topbar-right" aria-hidden="true">
        @if($user)
        @php
            $topbarInitials = strtoupper(
                implode('', array_map(
                    fn($w) => $w[0],
                    array_slice(explode(' ', $user->name), 0, 2)
                ))
            );
        @endphp
        <div class="ef-mnav-user-badge" title="{{ $user->name }}">{{ $topbarInitials }}</div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     BACKDROP
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-mnav-backdrop" id="efNavBackdrop" aria-hidden="true"></div>

{{-- ═══════════════════════════════════════════════════════════════
     DRAWER
═══════════════════════════════════════════════════════════════════ --}}
<nav class="ef-mnav-drawer"
     id="efNavDrawer"
     aria-label="Main navigation"
     aria-hidden="true"
     role="navigation">

    {{-- Drawer header --}}
    <div class="ef-mnav-drawer-header">
        <div class="ef-mnav-brand-name">
            <span class="ef-mnav-brand-dot" aria-hidden="true"></span>
            {{ config('app.name', 'ExpenseFlow') }}
        </div>
        <button class="ef-mnav-close" id="efNavClose" type="button" aria-label="Close navigation">&#x2715;</button>
    </div>

    {{-- User identity (authenticated only) --}}
    @if($user)
    @php
        $drawerInitials = strtoupper(
            implode('', array_map(
                fn($w) => $w[0],
                array_slice(explode(' ', $user->name), 0, 2)
            ))
        );
        $drawerRole = match($user->role) {
            'admin'   => 'Administrator',
            'manager' => 'Manager',
            'employee'=> 'Employee',
            default   => ucfirst($user->role),
        };
    @endphp
    <div class="ef-mnav-user-strip">
        <div class="ef-mnav-user-avatar" aria-hidden="true">{{ $drawerInitials }}</div>
        <div>
            <div class="ef-mnav-user-name">{{ $user->name }}</div>
            <div class="ef-mnav-user-role">{{ $drawerRole }}</div>
        </div>
    </div>
    @endif

    {{-- Section label --}}
    <div class="ef-mnav-section-label" aria-hidden="true">Navigation</div>

    {{-- Nav links --}}
    <ul class="ef-mnav-links" role="list">
        @foreach($navLinks as $link)
        @php
            $linkPath = '/' . ltrim(parse_url($link['href'], PHP_URL_PATH), '/');
            $isActive = $linkPath === $currentPath || str_starts_with($currentPath, $linkPath . '/');
        @endphp
        <li role="listitem">
            <a href="{{ $link['href'] }}"
               class="ef-mnav-link{{ $isActive ? ' active' : '' }}"
               @if($isActive) aria-current="page" @endif>
                <span class="ef-mnav-link-icon" aria-hidden="true">{{ $link['icon'] }}</span>
                <span class="ef-mnav-link-label">{{ $link['label'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>

    {{-- Footer: Sign Out / Sign In --}}
    <div class="ef-mnav-drawer-footer">
        @if($user)
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ef-mnav-signout">
                <span aria-hidden="true">🚪</span>
                Sign Out
            </button>
        </form>
        @else
        <a href="{{ route('login') }}" class="ef-mnav-signin-link">
            <span aria-hidden="true">🔑</span>
            Staff Login
        </a>
        @endif
    </div>

</nav>

{{-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT
     All IDs are prefixed efNav to avoid collisions with host page.
═══════════════════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    var hamburger = document.getElementById('efNavHamburger');
    var closeBtn  = document.getElementById('efNavClose');
    var drawer    = document.getElementById('efNavDrawer');
    var backdrop  = document.getElementById('efNavBackdrop');

    if (!hamburger || !drawer || !backdrop) return; // guard

    /* ── Scroll lock (iOS-safe pattern) ──────────────────────────
       body.overflow = hidden does NOT work on iOS Safari — fixed
       elements re-composite into the scroll layer.
       Pattern: position:fixed + saved scrollY is the only
       reliable cross-browser solution.
    ──────────────────────────────────────────────────────────── */
    var _savedScrollY = 0;

    function lockBodyScroll() {
        _savedScrollY = window.scrollY;
        document.body.style.cssText =
            'position:fixed;top:-' + _savedScrollY + 'px;' +
            'width:100%;overflow-y:scroll;';
    }

    function unlockBodyScroll() {
        document.body.style.cssText = '';
        window.scrollTo(0, _savedScrollY);
    }

    /* ── Open drawer ──────────────────────────────────────────── */
    function openDrawer() {
        drawer.classList.add('open');
        backdrop.classList.add('open');
        hamburger.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        lockBodyScroll();

        // Move focus into drawer for accessibility
        var firstLink = drawer.querySelector('.ef-mnav-link, .ef-mnav-close, .ef-mnav-signout');
        if (firstLink) {
            // Delay to allow transition to start
            setTimeout(function () { firstLink.focus(); }, 50);
        }
    }

    /* ── Close drawer ─────────────────────────────────────────── */
    function closeDrawer() {
        drawer.classList.remove('open');
        backdrop.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        unlockBodyScroll();
        // Return focus to hamburger
        hamburger.focus();
    }

    /* ── Toggle ───────────────────────────────────────────────── */
    function toggleDrawer() {
        drawer.classList.contains('open') ? closeDrawer() : openDrawer();
    }

    /* ── Event listeners ──────────────────────────────────────── */
    hamburger.addEventListener('click', toggleDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer.classList.contains('open')) {
            closeDrawer();
        }
    });

    // Close on nav link tap (navigation will load new page anyway,
    // but gives instant visual feedback)
    var links = drawer.querySelectorAll('.ef-mnav-link');
    links.forEach(function (link) {
        link.addEventListener('click', function () {
            // Only close; don't prevent default (allow normal navigation)
            closeDrawer();
        });
    });

    // Focus trap: Tab key cycles within drawer when open
    drawer.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab' || !drawer.classList.contains('open')) return;

        var focusable = Array.from(drawer.querySelectorAll(
            'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) {
            return el.offsetParent !== null; // visible only
        });
        if (focusable.length === 0) return;

        var first = focusable[0];
        var last  = focusable[focusable.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    /* ── Swipe to close (left-swipe anywhere on drawer) ─────────
       Tracks touchstart X; if swipe left ≥ 60px → close.
    ──────────────────────────────────────────────────────────── */
    var _touchStartX = 0;
    var _touchStartY = 0;

    drawer.addEventListener('touchstart', function (e) {
        _touchStartX = e.touches[0].clientX;
        _touchStartY = e.touches[0].clientY;
    }, { passive: true });

    drawer.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - _touchStartX;
        var dy = e.changedTouches[0].clientY - _touchStartY;

        // Only register horizontal swipes (dx < -60px, more horizontal than vertical)
        if (dx < -60 && Math.abs(dy) < Math.abs(dx)) {
            closeDrawer();
        }
    }, { passive: true });

    /* ── Edge swipe to open (touch starts within 20px of left edge) ──
       Common native pattern for left-side drawers.
    ──────────────────────────────────────────────────────────────── */
    document.addEventListener('touchstart', function (e) {
        if (drawer.classList.contains('open')) return;
        if (e.touches[0].clientX <= 20) {
            _touchStartX = e.touches[0].clientX;
            _touchStartY = e.touches[0].clientY;
        }
    }, { passive: true });

    document.addEventListener('touchend', function (e) {
        if (drawer.classList.contains('open')) return;
        if (_touchStartX > 20) return; // didn't start at edge

        var dx = e.changedTouches[0].clientX - _touchStartX;
        var dy = e.changedTouches[0].clientY - _touchStartY;

        if (dx > 60 && Math.abs(dy) < Math.abs(dx)) {
            openDrawer();
        }
        _touchStartX = 0;
    }, { passive: true });

}());
</script>
