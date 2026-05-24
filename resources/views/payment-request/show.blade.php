<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#0e1a13">

{{-- ── WhatsApp / OpenGraph rich preview ─────────────────────────── --}}
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="{{ config('app.name') }}">
<meta property="og:title"       content="₹{{ number_format((float)$expense->amount, 2) }} — {{ $expense->title }}">
<meta property="og:description" content="Payment request from {{ $expense->requester?->name ?? 'Employee' }}. Tap to view QR and pay instantly.">
@if($expense->qrUrl())
<meta property="og:image"       content="{{ $expense->qrUrl() }}">
<meta property="og:image:width" content="600">
<meta property="og:image:height" content="600">
@endif
<meta name="twitter:card" content="summary_large_image">

<title>₹{{ number_format((float)$expense->amount, 2) }} • {{ $expense->title }} • ExpenseFlow</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

<style>
/* ═══════════════════════════════════════════════════════════════════
   RESET + TOKENS
═══════════════════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    /* Brand */
    --g900:  #0e1a13;
    --g800:  #133220;
    --g700:  #1a6645;
    --g600:  #22845a;
    --g400:  #4ade80;
    --g100:  #f0fdf4;

    /* Semantic */
    --paid-bg:    #dcfce7;
    --paid-text:  #14532d;
    --paid-border:#86efac;

    --reject-bg:    #fee2e2;
    --reject-text:  #7f1d1d;
    --reject-border:#fecaca;

    --amber-bg:   #fef3c7;
    --amber-text: #78350f;
    --amber-border:#fde68a;

    --blue-bg:    #e0f2fe;
    --blue-text:  #0c4a6e;
    --blue-border:#bae6fd;

    /* Neutrals */
    --text:     #111827;
    --sub:      #6b7280;
    --muted:    #9ca3af;
    --border:   #e5e7eb;
    --surface:  #f9fafb;
    --white:    #ffffff;
}

html {
    height: -webkit-fill-available;
    height: 100dvh;
    overflow-x: hidden;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f1f5f2;
    min-height: 100%;
    min-height: -webkit-fill-available;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    /* Top pad = mobile topbar height (52px) + safe-area-inset-top + 16px gap.
       Safe-area is also applied on the topbar itself, so the card clears it. */
    padding: calc(52px + env(safe-area-inset-top, 0px) + 16px) 14px 120px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ═══════════════════════════════════════════════════════════════════
   CARD SHELL
═══════════════════════════════════════════════════════════════════ */
.card {
    width: 100%;
    max-width: 420px;
    background: var(--white);
    border-radius: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06), 0 8px 32px rgba(0,0,0,.08);
    overflow: hidden;
    margin-bottom: 16px;
}

/* ═══════════════════════════════════════════════════════════════════
   APP BAR
═══════════════════════════════════════════════════════════════════ */
.app-bar {
    background: var(--g900);
    padding: 16px 20px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.app-bar-icon {
    width: 34px; height: 34px;
    background: rgba(255,255,255,.1);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.app-bar-title {
    font-size: .85rem; font-weight: 700;
    color: rgba(255,255,255,.9);
    letter-spacing: .01em;
}
.app-bar-sub {
    font-size: .7rem;
    color: rgba(255,255,255,.45);
    margin-top: 1px;
}
.app-bar-id {
    margin-left: auto;
    font-size: .7rem; font-weight: 700;
    color: rgba(255,255,255,.5);
    background: rgba(255,255,255,.08);
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
    letter-spacing: .02em;
}

/* ═══════════════════════════════════════════════════════════════════
   IN-APP BROWSER WARNING BANNER
   Shown by JS when WhatsApp / Instagram / Facebook in-app browser
   detected — prompts staff to open in their default browser.
═══════════════════════════════════════════════════════════════════ */
.inapp-banner {
    display: none; /* JS shows if detected */
    align-items: flex-start;
    gap: 10px;
    background: #fffbeb;
    border-bottom: 1px solid #fde68a;
    padding: 12px 16px;
    font-size: .78rem;
    color: #78350f;
    line-height: 1.45;
}
.inapp-banner-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
.inapp-banner strong { display: block; font-weight: 700; margin-bottom: 2px; }
.inapp-copy-btn {
    display: inline-flex; align-items: center; gap: 5px;
    margin-top: 8px;
    background: #92400e; color: #fff;
    border: none; border-radius: 8px;
    padding: 6px 12px; font-size: .75rem; font-weight: 700;
    cursor: pointer; font-family: inherit;
    -webkit-tap-highlight-color: transparent;
}

/* ═══════════════════════════════════════════════════════════════════
   AMOUNT HERO
═══════════════════════════════════════════════════════════════════ */
.amount-hero {
    text-align: center;
    padding: 28px 20px 22px;
    border-bottom: 1px solid #f3f4f6;
    position: relative;
}
.amount-eyebrow {
    font-size: .65rem; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--sub); margin-bottom: 10px;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.amount-eyebrow::before, .amount-eyebrow::after {
    content: ''; flex: 1; max-width: 32px;
    height: 1px; background: var(--border);
}
.amount-val {
    font-size: 3.2rem; font-weight: 900;
    color: var(--text); line-height: 1;
    letter-spacing: -.03em;
}
.amount-val .rupee { font-size: 2rem; font-weight: 700; vertical-align: .15em; }
.amount-title {
    font-size: .9rem; font-weight: 600; color: var(--sub);
    margin-top: 8px; line-height: 1.4;
}
.amount-hero .status-chip {
    display: inline-flex; align-items: center; gap: 5px;
    margin-top: 12px;
    font-size: .68rem; font-weight: 800;
    letter-spacing: .08em; text-transform: uppercase;
    padding: 5px 12px; border-radius: 20px; border: 1px solid;
}

/* Status chip colours */
.chip-pending         { background: var(--amber-bg); color: var(--amber-text); border-color: var(--amber-border); }
.chip-pending_payment { background: var(--blue-bg);  color: var(--blue-text);  border-color: var(--blue-border); }
.chip-approved        { background: var(--g100);     color: var(--g700);       border-color: #bbf7d0; }
.chip-paid            { background: var(--paid-bg);  color: var(--paid-text);  border-color: var(--paid-border); }
.chip-rejected        { background: var(--reject-bg);color: var(--reject-text);border-color: var(--reject-border); }
.chip-reimbursement_pending { background: #ede9fe; color: #3b0764; border-color: #ddd6fe; }
.chip-reimbursed, .chip-completed { background: var(--g100); color: var(--g700); border-color: #bbf7d0; }

/* ═══════════════════════════════════════════════════════════════════
   REQUESTER ROW
═══════════════════════════════════════════════════════════════════ */
.emp-row {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
}
.emp-avatar {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--g700), var(--g600));
    color: #fff; font-size: .83rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
}
.emp-name { font-size: .86rem; font-weight: 700; color: var(--text); }
.emp-meta { font-size: .7rem; color: var(--sub); margin-top: 1px; }
.emp-time {
    margin-left: auto; text-align: right;
    font-size: .7rem; color: var(--muted); line-height: 1.5;
}

/* ═══════════════════════════════════════════════════════════════════
   QR SECTION
═══════════════════════════════════════════════════════════════════ */
.qr-section {
    padding: 22px 20px 20px;
    text-align: center;
    border-bottom: 1px solid #f3f4f6;
}
.qr-lbl {
    font-size: .65rem; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--sub); margin-bottom: 16px;
}
.qr-box {
    background: var(--white);
    border: 2px solid var(--border); border-radius: 18px;
    display: inline-block; padding: 14px;
    cursor: zoom-in; position: relative;
    transition: border-color .18s, box-shadow .18s, transform .1s;
    -webkit-tap-highlight-color: transparent;
}
.qr-box:hover  { border-color: rgba(26,102,69,.3); box-shadow: 0 4px 20px rgba(26,102,69,.10); }
.qr-box:active { transform: scale(.96); }
/* Corner brackets */
.qr-box::before, .qr-box::after {
    content: ''; position: absolute;
    width: 18px; height: 18px;
    border-color: var(--g700); border-style: solid;
}
.qr-box::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; border-radius: 14px 0 0 0; }
.qr-box::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; border-radius: 0 0 14px 0; }
/* Skeleton */
.qr-skeleton {
    width: 200px; height: 200px; border-radius: 10px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%; animation: skeletonWave 1.4s infinite;
}
@keyframes skeletonWave { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
/* QR image */
.qr-img {
    border-radius: 10px; display: block;
    max-width: 220px; max-height: 220px;
    object-fit: contain; opacity: 0;
    transition: opacity .25s ease; width: 100%;
}
.qr-img.loaded { opacity: 1; }
/* Tap hint */
.qr-tap-hint {
    display: none; align-items: center; justify-content: center;
    gap: 4px; margin-top: 8px;
    font-size: .67rem; color: var(--sub); opacity: .7;
}
.qr-upi-hint {
    display: flex; align-items: center; justify-content: center;
    gap: 5px; margin-top: 12px;
    font-size: .75rem; color: var(--sub);
}
/* Error / no-QR placeholder */
.no-qr {
    background: var(--surface); border: 2px dashed var(--border);
    border-radius: 16px; padding: 28px 20px; text-align: center;
    color: var(--sub); font-size: .85rem;
}
.no-qr-icon { font-size: 2.2rem; margin-bottom: 10px; opacity: .35; }

@media (min-width: 480px) {
    .qr-img { max-width: 260px; max-height: 260px; }
}

/* Fullscreen overlay */
.qr-full-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.93);
    -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
    align-items: center; justify-content: center;
    padding: 24px; z-index: 9999;
}
.qr-full-overlay.open { display: flex; animation: fadeIn .18s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.qr-full-img {
    border-radius: 16px; display: block;
    max-height: 82vh; max-width: 100%;
    object-fit: contain; touch-action: pinch-zoom;
    box-shadow: 0 24px 80px rgba(0,0,0,.5);
    -webkit-user-select: none; user-select: none;
}
.qr-full-close {
    position: absolute; top: 16px; right: 16px;
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,.12); border: none; color: #fff;
    font-size: 1.1rem; cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    transition: background .15s; line-height: 1;
}
.qr-full-close:hover { background: rgba(255,255,255,.22); }
.qr-full-label {
    position: absolute; bottom: 20px; left: 0; right: 0;
    text-align: center; font-size: .68rem;
    color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .04em;
}

/* ═══════════════════════════════════════════════════════════════════
   TERMINAL STATE BANNERS  (PAID / REJECTED)
═══════════════════════════════════════════════════════════════════ */
.state-banner {
    margin: 0; padding: 18px 20px;
    display: flex; align-items: flex-start; gap: 14px;
    border-bottom: 1px solid var(--border);
}
.state-banner-icon { font-size: 1.8rem; flex-shrink: 0; line-height: 1; }
.state-banner-body { flex: 1; }
.state-banner-title { font-size: .88rem; font-weight: 800; margin-bottom: 3px; }
.state-banner-sub   { font-size: .75rem; line-height: 1.45; }
.state-banner-meta  { font-size: .7rem; margin-top: 6px; opacity: .75; }
.state-banner-row   { display: flex; gap: 8px; margin-top: 6px; }
.state-banner-chip  {
    font-size: .68rem; font-weight: 700; padding: 3px 9px;
    border-radius: 6px; background: rgba(0,0,0,.07);
}

/* PAID variant */
.state-paid {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-left: 4px solid #22c55e;
}
.state-paid .state-banner-title { color: var(--paid-text); }
.state-paid .state-banner-sub   { color: #166534; }

/* REJECTED variant */
.state-rejected {
    background: linear-gradient(135deg, #fff5f5, var(--reject-bg));
    border-left: 4px solid #ef4444;
}
.state-rejected .state-banner-title { color: var(--reject-text); }
.state-rejected .state-banner-sub   { color: #991b1b; }

/* ═══════════════════════════════════════════════════════════════════
   PROOF DOWNLOAD STRIP  (inside paid state)
═══════════════════════════════════════════════════════════════════ */
.proof-strip {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 20px 14px 52px; /* indented under the paid icon */
    border-bottom: 1px solid #f3f4f6;
}
.proof-strip a {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .75rem; font-weight: 700;
    color: var(--g700); text-decoration: none;
    background: var(--g100); border: 1px solid #bbf7d0;
    padding: 6px 12px; border-radius: 8px;
    transition: background .15s;
}
.proof-strip a:hover { background: #dcfce7; }

/* ═══════════════════════════════════════════════════════════════════
   STAFF ACTION PANEL  (logged-in admin/manager only)
═══════════════════════════════════════════════════════════════════ */
.action-panel {
    padding: 18px 20px;
    background: linear-gradient(180deg, #f8fffe 0%, var(--white) 100%);
    border-top: 2px solid var(--g100);
}
.action-panel-header {
    display: flex; align-items: center; gap: 7px;
    font-size: .65rem; font-weight: 800;
    letter-spacing: .1em; text-transform: uppercase;
    color: var(--g700); margin-bottom: 14px;
}
.action-panel-header::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(to right, var(--border), transparent);
}
.action-staff-badge {
    font-size: .68rem; font-weight: 700;
    color: var(--g600); background: var(--g100);
    border: 1px solid #bbf7d0; border-radius: 6px;
    padding: 2px 8px; margin-left: auto;
    white-space: nowrap;
}

/* Primary action row */
.action-row { display: flex; gap: 10px; margin-bottom: 10px; }

.btn-mark-paid {
    flex: 2;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px 16px;
    background: linear-gradient(135deg, var(--g700), var(--g600));
    color: var(--white); font-size: .88rem; font-weight: 800;
    border: none; border-radius: 14px; cursor: pointer;
    box-shadow: 0 4px 16px rgba(26,102,69,.28);
    transition: transform .12s, box-shadow .12s;
    -webkit-tap-highlight-color: transparent;
    font-family: inherit;
}
.btn-mark-paid:active { transform: scale(.97); box-shadow: 0 2px 8px rgba(26,102,69,.22); }

.btn-reject {
    flex: 1;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 14px 12px;
    background: var(--surface); color: #dc2626;
    font-size: .82rem; font-weight: 700;
    border: 1.5px solid #fca5a5; border-radius: 14px; cursor: pointer;
    transition: background .12s, border-color .12s;
    -webkit-tap-highlight-color: transparent;
    font-family: inherit;
}
.btn-reject:hover { background: #fff5f5; border-color: #f87171; }

/* Secondary actions */
.action-secondary { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-secondary {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 8px 14px;
    background: var(--white); color: var(--sub);
    font-size: .76rem; font-weight: 700;
    border: 1.5px solid var(--border); border-radius: 10px; cursor: pointer;
    transition: border-color .15s, color .15s;
    -webkit-tap-highlight-color: transparent;
    font-family: inherit;
}
.btn-secondary:hover { border-color: var(--g700); color: var(--g700); }

/* Proof upload mini-form */
.proof-form { margin-top: 10px; display: none; }
.proof-file-input {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px dashed var(--border); border-radius: 10px;
    font-size: .8rem; color: var(--text); background: var(--surface);
    cursor: pointer; font-family: inherit;
    transition: border-color .15s;
}
.proof-file-input:focus { outline: none; border-color: var(--g700); }
.proof-upload-btn {
    width: 100%; margin-top: 8px;
    padding: 11px; background: var(--g700); color: var(--white);
    font-size: .84rem; font-weight: 700; border: none; border-radius: 10px;
    cursor: pointer; font-family: inherit;
    transition: background .15s;
}
.proof-upload-btn:hover { background: var(--g600); }

/* ═══════════════════════════════════════════════════════════════════
   STAFF LOGIN PROMPT  (unauthenticated visitors who might be staff)
═══════════════════════════════════════════════════════════════════ */
.staff-login-strip {
    padding: 14px 20px;
    border-top: 1px solid #f3f4f6;
    display: flex; align-items: center; gap: 12px;
    background: var(--surface);
}
.staff-login-text { font-size: .76rem; color: var(--sub); flex: 1; }
.staff-login-text strong { display: block; color: var(--text); font-weight: 700; margin-bottom: 2px; }
.btn-staff-login {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px;
    background: var(--g900); color: var(--white);
    font-size: .78rem; font-weight: 700;
    border: none; border-radius: 10px; cursor: pointer;
    text-decoration: none; white-space: nowrap;
    transition: background .15s;
    -webkit-tap-highlight-color: transparent;
}
.btn-staff-login:hover { background: var(--g800); }

/* ═══════════════════════════════════════════════════════════════════
   FLASH BANNERS  (success / info toasts inside card)
═══════════════════════════════════════════════════════════════════ */
.flash {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 16px;
    margin: 12px 16px 0;
    border-radius: 12px; font-size: .8rem; line-height: 1.45;
}
.flash-success {
    background: var(--paid-bg); color: var(--paid-text);
    border: 1px solid var(--paid-border);
}
.flash-info {
    background: var(--blue-bg); color: var(--blue-text);
    border: 1px solid var(--blue-border);
}
.flash-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }

/* ═══════════════════════════════════════════════════════════════════
   MARK-PAID CONFIRM MODAL (bottom sheet)
   z-index: 600 — must render above mobile nav drawer (z-index: 500)
═══════════════════════════════════════════════════════════════════ */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.52);
    -webkit-backdrop-filter: blur(3px); backdrop-filter: blur(3px);
    z-index: 600;
    align-items: flex-end; justify-content: center;
    padding: 0 0 env(safe-area-inset-bottom, 0px);
}
.modal-overlay.open { display: flex; }
.modal-sheet {
    width: 100%; max-width: 480px;
    background: var(--white); border-radius: 24px 24px 0 0;
    padding: 24px 20px calc(24px + env(safe-area-inset-bottom, 0px));
    box-shadow: 0 -8px 40px rgba(0,0,0,.14);
    animation: slideUp .22s cubic-bezier(.32,0,.67,0);
}
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.modal-handle {
    width: 40px; height: 4px; background: #e5e7eb;
    border-radius: 2px; margin: 0 auto 20px;
}
.modal-title   { font-size: 1.05rem; font-weight: 900; color: var(--text); margin-bottom: 4px; }
.modal-sub     { font-size: .8rem; color: var(--sub); margin-bottom: 16px; }
.modal-summary {
    background: var(--surface); border-radius: 12px;
    padding: 12px 14px; margin-bottom: 16px;
    font-size: .82rem; border: 1px solid var(--border);
}
.modal-summary-row {
    display: flex; justify-content: space-between;
    padding: 3px 0; border-bottom: 1px solid var(--border);
}
.modal-summary-row:last-child { border-bottom: none; }
.modal-summary-lbl { color: var(--sub); }
.modal-summary-val { font-weight: 700; color: var(--text); }

/* Mode selector */
.mode-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;
    margin-bottom: 12px;
}
.mode-btn {
    padding: 9px 4px; border-radius: 10px;
    border: 1.5px solid var(--border);
    background: var(--white); color: var(--sub);
    font-size: .7rem; font-weight: 700; text-align: center;
    cursor: pointer; transition: all .12s;
    font-family: inherit;
    line-height: 1.3;
}
.mode-btn.active {
    border-color: var(--g700); color: var(--g700);
    background: var(--g100);
}
.mode-icon { font-size: 1.2rem; display: block; margin-bottom: 3px; }

.modal-input {
    width: 100%; border: 1.5px solid var(--border); border-radius: 10px;
    padding: 10px 12px; font-size: .86rem; color: var(--text);
    margin-bottom: 10px; outline: none; font-family: inherit;
    background: var(--white);
    transition: border-color .15s;
}
.modal-input:focus { border-color: var(--g700); }
.modal-input::placeholder { color: #adb5bd; }

.modal-actions { display: grid; grid-template-columns: 1fr 2fr; gap: 10px; }
.modal-cancel {
    padding: 14px; background: var(--surface); color: var(--sub);
    font-weight: 700; font-size: .86rem; border: none;
    border-radius: 12px; cursor: pointer; font-family: inherit;
}
.modal-confirm {
    padding: 14px;
    background: linear-gradient(135deg, var(--g700), var(--g600));
    color: var(--white); font-weight: 800; font-size: .88rem;
    border: none; border-radius: 12px; cursor: pointer;
    font-family: inherit; box-shadow: 0 3px 12px rgba(26,102,69,.25);
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: opacity .15s;
}
.modal-confirm:disabled { opacity: .6; pointer-events: none; }

/* ═══════════════════════════════════════════════════════════════════
   REJECT MODAL
   z-index: 600 — above mobile nav drawer (z-index: 500)
═══════════════════════════════════════════════════════════════════ */
.reject-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.52);
    -webkit-backdrop-filter: blur(3px); backdrop-filter: blur(3px);
    z-index: 600;
    align-items: flex-end; justify-content: center;
    padding: 0 0 env(safe-area-inset-bottom, 0px);
}
.reject-modal-overlay.open { display: flex; }
.reject-sheet {
    width: 100%; max-width: 480px;
    background: var(--white); border-radius: 24px 24px 0 0;
    padding: 24px 20px calc(24px + env(safe-area-inset-bottom, 0px));
    box-shadow: 0 -8px 40px rgba(0,0,0,.14);
    animation: slideUp .22s cubic-bezier(.32,0,.67,0);
}
.reject-title { font-size: 1.05rem; font-weight: 900; color: #b91c1c; margin-bottom: 4px; }
.reject-sub   { font-size: .8rem; color: var(--sub); margin-bottom: 14px; }
.reject-actions { display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-top: 4px; }
.btn-confirm-reject {
    padding: 14px;
    background: #dc2626; color: var(--white);
    font-weight: 800; font-size: .88rem;
    border: none; border-radius: 12px; cursor: pointer;
    font-family: inherit; transition: opacity .15s;
}
.btn-confirm-reject:disabled { opacity: .6; pointer-events: none; }

/* ═══════════════════════════════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════════════════════════════ */
.page-footer {
    padding: 12px 20px;
    text-align: center; font-size: .7rem; color: var(--muted);
    border-top: 1px solid #f3f4f6;
}
.brand { font-weight: 800; color: var(--sub); }

/* ═══════════════════════════════════════════════════════════════════
   STICKY MOBILE ACTION BAR
   Only rendered when canAct is true.
   Uses safe-area-inset-bottom for iPhone notch/home-bar.
   NO transform or will-change — avoids iOS compositing scroll bug.
═══════════════════════════════════════════════════════════════════ */
.sticky-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 200;
    padding: 12px 16px;
    padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
    background: rgba(14, 26, 19, 0.97);
    -webkit-backdrop-filter: blur(16px);
    backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 -4px 24px rgba(0,0,0,.3);
    display: flex; align-items: center; gap: 10px;
    isolation: isolate;
}
.sticky-bar-info {
    flex: 1; min-width: 0;
}
.sticky-bar-amount {
    font-size: .95rem; font-weight: 900; color: var(--white);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sticky-bar-status {
    font-size: .68rem; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    color: rgba(255,255,255,.45); margin-top: 1px;
}
.sticky-bar-paid {
    flex: 0 0 auto;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 11px 18px;
    background: linear-gradient(135deg, var(--g700), var(--g600));
    color: var(--white); font-size: .84rem; font-weight: 800;
    border: none; border-radius: 12px; cursor: pointer;
    box-shadow: 0 3px 12px rgba(74,222,128,.2);
    -webkit-tap-highlight-color: transparent;
    font-family: inherit;
    transition: opacity .12s;
    white-space: nowrap;
}
.sticky-bar-paid:active { opacity: .85; }
.sticky-bar-reject {
    flex: 0 0 auto;
    display: flex; align-items: center; justify-content: center;
    width: 44px; height: 44px;
    background: rgba(239,68,68,.15); color: #fca5a5;
    border: 1.5px solid rgba(239,68,68,.25); border-radius: 12px;
    cursor: pointer; font-size: 1rem;
    -webkit-tap-highlight-color: transparent;
    font-family: inherit;
    transition: background .12s;
}
.sticky-bar-reject:active { background: rgba(239,68,68,.25); }

/* 480px+: hide sticky bar (action panel visible in card) */
@media (min-width: 480px) {
    .sticky-bar { display: none; }
    body { padding-bottom: 40px; }
}

/* 768px+: mobile topbar hidden by component CSS, so remove topbar padding-top */
@media (min-width: 768px) {
    body {
        padding-top: 32px;
    }
}
</style>
</head>
<body>

{{-- ── Mobile navigation: topbar + hamburger + slide drawer ───────── --}}
{{-- Renders its own <style> + HTML + <script>. Zero external deps.   --}}
{{-- Topbar z-index: 300 | Backdrop: 400 | Drawer: 500               --}}
{{-- Modals (mark-paid, reject) bumped to z-index 600 to sit above.  --}}
<x-mobile-nav title="Payment Request" />

@php
    $name     = $expense->requester?->name ?? 'Employee';
    $initials = strtoupper(implode('', array_map(
        fn($w) => $w[0],
        array_slice(explode(' ', $name), 0, 2)
    )));
    $role     = ucfirst($expense->requester?->role ?? 'employee');
    $isPaid   = $expense->isPaid() || $expense->isReimbursementPending() || $expense->isReimbursed() || $expense->isCompleted();
    $isRejected = $expense->isRejected();
    $payment  = $expense->payment;
@endphp

{{-- ── MAIN CARD ─────────────────────────────────────────────────── --}}
<div class="card">

    {{-- App bar --}}
    <div class="app-bar">
        <div class="app-bar-icon">💰</div>
        <div>
            <div class="app-bar-title">Payment Request</div>
            <div class="app-bar-sub">{{ config('app.name') }}</div>
        </div>
        <span class="app-bar-id">#{{ $expense->id }}</span>
    </div>

    {{-- In-app browser warning (shown by JS) --}}
    <div class="inapp-banner" id="inappBanner">
        <span class="inapp-banner-icon">⚠️</span>
        <div>
            <strong>You're in an in-app browser</strong>
            Staff controls require your default browser where you're logged in.
            <br>
            <button class="inapp-copy-btn" id="inappCopyBtn" onclick="copyPageLink()">
                📋 Copy link to open in browser
            </button>
        </div>
    </div>

    {{-- Flash: session messages --}}
    @if(session('paid_success'))
    <div class="flash flash-success" role="alert">
        <span class="flash-icon">✅</span>
        <div>Payment marked as received. Employee has been notified.</div>
    </div>
    @endif
    @if(session('reject_success'))
    <div class="flash flash-success" style="background:#fff0f0;color:#7f1d1d;border-color:#fecaca;" role="alert">
        <span class="flash-icon">🚫</span>
        <div>Payment request has been rejected.</div>
    </div>
    @endif
    @if(session('proof_success'))
    <div class="flash flash-success" role="alert">
        <span class="flash-icon">📎</span>
        <div>Payment proof uploaded successfully.</div>
    </div>
    @endif
    @if(session('info'))
    <div class="flash flash-info" role="alert">
        <span class="flash-icon">ℹ️</span>
        <div>{{ session('info') }}</div>
    </div>
    @endif

    {{-- Amount hero --}}
    <div class="amount-hero">
        <p class="amount-eyebrow">Amount to Pay</p>
        <div class="amount-val">
            <span class="rupee">₹</span>{{ number_format((float)$expense->amount, 2) }}
        </div>
        <p class="amount-title">{{ $expense->title }}</p>

        @php
        $statusLabel = match($expense->status) {
            'pending'               => 'Pending Approval',
            'pending_payment'       => 'Awaiting Payment',
            'approved'              => 'Approved',
            'paid'                  => 'Paid',
            'rejected'              => 'Rejected',
            'reimbursement_pending' => 'Reimbursement Pending',
            'reimbursed'            => 'Reimbursed',
            'completed'             => 'Completed',
            default                 => ucwords(str_replace('_', ' ', $expense->status)),
        };
        $statusDot = match($expense->status) {
            'pending', 'pending_payment' => '🔵',
            'approved'                   => '🟢',
            'paid', 'reimbursed', 'completed' => '✅',
            'rejected'                   => '🔴',
            default                      => '⚪',
        };
        @endphp
        <div class="status-chip chip-{{ $expense->status }}">
            {{ $statusDot }} {{ $statusLabel }}
        </div>
    </div>

    {{-- Requester row --}}
    <div class="emp-row">
        <div class="emp-avatar">{{ $initials }}</div>
        <div>
            <div class="emp-name">{{ $name }}</div>
            <div class="emp-meta">{{ $role }}</div>
        </div>
        <div class="emp-time">
            {{ $expense->created_at->format('d M Y') }}<br>
            {{ $expense->created_at->format('h:i A') }}
        </div>
    </div>

    {{-- ── PAID STATE BANNER ──────────────────────────────── --}}
    @if($isPaid)
    <div class="state-banner state-paid">
        <div class="state-banner-icon">✅</div>
        <div class="state-banner-body">
            <div class="state-banner-title">Payment Confirmed</div>
            <div class="state-banner-sub">
                This payment has been verified and recorded.
            </div>
            @if($payment)
            <div class="state-banner-row">
                @if($payment->transaction_reference)
                <span class="state-banner-chip">UTR: {{ $payment->transaction_reference }}</span>
                @endif
                @if($payment->payer)
                <span class="state-banner-chip">By {{ $payment->payer->name }}</span>
                @endif
                @if($payment->paid_at)
                <span class="state-banner-chip">{{ $payment->paid_at->format('d M, h:i A') }}</span>
                @endif
            </div>
            @elseif($expense->approver && $expense->approved_at)
            <div class="state-banner-meta">
                Confirmed by {{ $expense->approver->name }} on {{ $expense->approved_at->format('d M Y, h:i A') }}
            </div>
            @endif
        </div>
    </div>

    {{-- Proof download (staff only) --}}
    @if($isStaff && $payment?->proof_file_path)
    <div class="proof-strip">
        <a href="{{ route('payment-request.serve-proof', $expense->id) }}" target="_blank" rel="noopener">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            View Proof of Payment
        </a>
    </div>
    @endif

    {{-- ── REJECTED STATE BANNER ──────────────────────────── --}}
    @elseif($isRejected)
    <div class="state-banner state-rejected">
        <div class="state-banner-icon">🚫</div>
        <div class="state-banner-body">
            <div class="state-banner-title">Payment Rejected</div>
            @if($expense->rejection_reason)
            <div class="state-banner-sub">{{ $expense->rejection_reason }}</div>
            @else
            <div class="state-banner-sub">This request was rejected without a specific reason.</div>
            @endif
            @if($expense->approver && $expense->approved_at)
            <div class="state-banner-meta">
                By {{ $expense->approver->name }} · {{ $expense->approved_at->format('d M Y, h:i A') }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── QR SECTION ─────────────────────────────────────── --}}
    {{-- Hide QR for terminal states to reduce confusion --}}
    @if(!$isPaid && !$isRejected)
    <div class="qr-section">
        <p class="qr-lbl">Scan QR to Pay</p>

        @if($expense->qrUrl())
        <div class="qr-box" id="qrBox" role="button" aria-label="Tap to enlarge QR" tabindex="0">
            <div class="qr-skeleton" id="qrSkeleton"></div>
            <img id="qrImage"
                 src="{{ $expense->qrUrl() }}"
                 class="qr-img"
                 alt="Payment QR — {{ $expense->title }}"
                 loading="eager"
                 decoding="async"
                 style="display:none">
        </div>
        <div class="qr-tap-hint" id="qrTapHint">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            Tap to enlarge
        </div>
        <div id="qrError" style="display:none">
            <div class="no-qr">
                <div class="no-qr-icon">⚠️</div>
                <p style="font-weight:700;color:#374151;margin-bottom:6px">QR image unavailable</p>
                <a href="{{ $expense->qrUrl() }}"
                   target="_blank" rel="noopener"
                   style="display:inline-flex;align-items:center;gap:6px;background:var(--g700);color:#fff;font-size:.78rem;font-weight:700;padding:8px 14px;border-radius:9px;text-decoration:none;margin-top:4px">
                    Open QR directly ↗
                </a>
            </div>
        </div>
        <div class="qr-upi-hint" id="qrUpiHint">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            Scan with any UPI app · GPay · PhonePe · Paytm
        </div>
        @else
        <div class="no-qr">
            <div class="no-qr-icon">🖼️</div>
            <p>No QR image attached to this request.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ── WAITING CONFIRMATION (public view, actionable state) ─── --}}
    @if(!$isPaid && !$isRejected && !$canAct && !$isStaff)
    <div style="padding:14px 20px;border-top:1px solid #f3f4f6;
                background:#fafffe;display:flex;align-items:center;gap:10px;font-size:.8rem;color:var(--sub);">
        <span style="font-size:1.3rem">⏳</span>
        <span>Waiting for payment confirmation from staff.</span>
    </div>
    @endif

    {{-- ── STAFF ACTION PANEL ──────────────────────────────── --}}
    @if($canAct)
    <div class="action-panel" id="actionPanel">
        <div class="action-panel-header">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Staff Controls
            <span class="action-staff-badge">{{ auth()->user()->name }}</span>
        </div>

        {{-- Primary row: Mark Paid + Reject --}}
        <div class="action-row">
            <button type="button" class="btn-mark-paid" id="btnOpenMarkPaid">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Mark as Paid
            </button>
            <button type="button" class="btn-reject" id="btnOpenReject">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Reject
            </button>
        </div>

        {{-- Secondary: Upload proof --}}
        <div class="action-secondary">
            <button type="button" class="btn-secondary" id="btnToggleProof">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Upload Proof
            </button>
            @if($payment?->proof_file_path)
            <a href="{{ route('payment-request.serve-proof', $expense->id) }}"
               class="btn-secondary" target="_blank" rel="noopener">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                View Proof
            </a>
            @endif
        </div>

        {{-- Collapsible proof upload form --}}
        <div class="proof-form" id="proofForm">
            <form method="POST"
                  action="{{ route('payment-request.proof', $expense->id) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="file"
                       name="proof_file"
                       class="proof-file-input"
                       accept="image/*,application/pdf"
                       required>
                <button type="submit" class="proof-upload-btn">
                    Upload Proof
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ── STAFF LOGIN PROMPT (not logged in, actionable state) ── --}}
    @if(!$isStaff && !$isPaid && !$isRejected)
    <div class="staff-login-strip">
        <div class="staff-login-text">
            <strong>Staff Access</strong>
            Log in to confirm or reject this payment.
        </div>
        <a href="{{ route('payment-request.login-redirect', ['return' => $expense->paymentPageUrl()]) }}"
           class="btn-staff-login">
            🔐 Login
        </a>
    </div>
    @endif

    {{-- Footer --}}
    <div class="page-footer">
        <span class="brand">{{ config('app.name') }}</span>
        · Request #{{ $expense->id }}
        · {{ $expense->created_at->format('d M Y') }}
    </div>

</div>{{-- /.card --}}

{{-- ── FULLSCREEN QR OVERLAY ────────────────────────────────────── --}}
@if(!$isPaid && !$isRejected && $expense->qrUrl())
<div class="qr-full-overlay" id="qrFullOverlay" role="dialog" aria-modal="true">
    <button class="qr-full-close" id="qrFullClose" aria-label="Close">&#x2715;</button>
    <img id="qrFullImg" src="" class="qr-full-img" alt="QR fullscreen">
    <p class="qr-full-label">Tap anywhere · pinch to zoom</p>
</div>
@endif

{{-- ── MARK-AS-PAID MODAL ───────────────────────────────────────── --}}
@if($canAct)
<div class="modal-overlay" id="markPaidModal" role="dialog" aria-modal="true">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <p class="modal-title">Confirm Payment Received</p>
        <p class="modal-sub">This marks the expense paid and notifies the requester.</p>

        <div class="modal-summary">
            <div class="modal-summary-row">
                <span class="modal-summary-lbl">Employee</span>
                <span class="modal-summary-val">{{ $expense->requester?->name ?? '—' }}</span>
            </div>
            <div class="modal-summary-row">
                <span class="modal-summary-lbl">Amount</span>
                <span class="modal-summary-val">₹{{ number_format((float)$expense->amount, 2) }}</span>
            </div>
            <div class="modal-summary-row">
                <span class="modal-summary-lbl">Description</span>
                <span class="modal-summary-val">{{ Str::limit($expense->title, 30) }}</span>
            </div>
        </div>

        <form method="POST"
              action="{{ route('payment-request.mark-paid', $expense->id) }}"
              id="markPaidForm">
            @csrf
            <input type="hidden" name="payment_mode" id="hiddenMode" value="upi">

            {{-- Payment mode selector --}}
            <div class="mode-grid" id="modeGrid">
                <button type="button" class="mode-btn active" data-mode="upi">
                    <span class="mode-icon">📱</span>UPI
                </button>
                <button type="button" class="mode-btn" data-mode="cash">
                    <span class="mode-icon">💵</span>Cash
                </button>
                <button type="button" class="mode-btn" data-mode="bank_transfer">
                    <span class="mode-icon">🏦</span>NEFT
                </button>
                <button type="button" class="mode-btn" data-mode="wallet">
                    <span class="mode-icon">👛</span>Wallet
                </button>
            </div>

            <input type="text" name="payment_reference" class="modal-input"
                   placeholder="UTR / Reference No. (optional)" maxlength="100">
            <textarea name="payment_note" class="modal-input" rows="2"
                      placeholder="Internal note (optional)" maxlength="500"
                      style="resize:none"></textarea>

            <div class="modal-actions">
                <button type="button" class="modal-cancel" id="btnMarkPaidCancel">Cancel</button>
                <button type="submit" class="modal-confirm" id="btnMarkPaidConfirm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirm Paid
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── REJECT MODAL ─────────────────────────────────────────────── --}}
<div class="reject-modal-overlay" id="rejectModal" role="dialog" aria-modal="true">
    <div class="reject-sheet">
        <div class="modal-handle"></div>
        <p class="reject-title">Reject Payment Request</p>
        <p class="reject-sub">Please provide a reason. The requester will be notified.</p>

        <form method="POST"
              action="{{ route('payment-request.reject', $expense->id) }}"
              id="rejectForm">
            @csrf
            <textarea name="rejection_reason" class="modal-input" rows="3"
                      placeholder="Reason for rejection…" maxlength="300"
                      required style="resize:none"></textarea>
            <div class="reject-actions">
                <button type="button" class="modal-cancel" id="btnRejectCancel">Cancel</button>
                <button type="submit" class="btn-confirm-reject" id="btnRejectConfirm">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── STICKY MOBILE ACTION BAR ─────────────────────────────────── --}}
@if($canAct)
<div class="sticky-bar" id="stickyBar">
    <div class="sticky-bar-info">
        <div class="sticky-bar-amount">₹{{ number_format((float)$expense->amount, 2) }}</div>
        <div class="sticky-bar-status">{{ $statusLabel }}</div>
    </div>
    <button type="button" class="sticky-bar-reject" id="btnStickyReject" title="Reject">
        ✕
    </button>
    <button type="button" class="sticky-bar-paid" id="btnStickyPaid">
        ✓ Mark Paid
    </button>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════ --}}
<script>
(function () {
'use strict';

/* ── In-app browser detection ────────────────────────────────── */
var ua = navigator.userAgent || '';
var isWhatsApp   = /WhatsApp/i.test(ua);
var isInstagram  = /Instagram/i.test(ua);
var isFacebook   = /FBAN|FBAV/i.test(ua);
var isAndroidWV  = /wv/.test(ua) && /Android/i.test(ua);
var isInApp      = isWhatsApp || isInstagram || isFacebook || isAndroidWV;

if (isInApp) {
    var banner = document.getElementById('inappBanner');
    if (banner) banner.style.display = 'flex';
}

/* ── Copy link helper ────────────────────────────────────────── */
window.copyPageLink = function () {
    var url = window.location.href;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
            var btn = document.getElementById('inappCopyBtn');
            if (btn) { btn.textContent = '✅ Copied!'; setTimeout(function() { btn.innerHTML = '📋 Copy link to open in browser'; }, 2500); }
        });
    } else {
        // Fallback for older browsers
        var ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select(); document.execCommand('copy');
        document.body.removeChild(ta);
    }
};

/* ── QR image load & fullscreen ──────────────────────────────── */
var img        = document.getElementById('qrImage');
var skeleton   = document.getElementById('qrSkeleton');
var qrBox      = document.getElementById('qrBox');
var qrError    = document.getElementById('qrError');
var qrUpiHint  = document.getElementById('qrUpiHint');
var tapHint    = document.getElementById('qrTapHint');
var overlay    = document.getElementById('qrFullOverlay');
var fullImg    = document.getElementById('qrFullImg');
var closeBtn   = document.getElementById('qrFullClose');

function showQr() {
    if (skeleton) skeleton.style.display = 'none';
    if (img)      { img.style.display = 'block'; img.classList.add('loaded'); }
    if (tapHint)  tapHint.style.display = 'flex';
}
function showQrError() {
    if (skeleton)   skeleton.style.display = 'none';
    if (qrBox)      qrBox.style.display    = 'none';
    if (qrError)    qrError.style.display  = 'block';
    if (qrUpiHint)  qrUpiHint.style.display = 'none';
    if (tapHint)    tapHint.style.display   = 'none';
}
function openFullscreen() {
    if (!overlay || !fullImg || !img || !img.src) return;
    fullImg.src = img.src;
    overlay.classList.add('open');
    // iOS-safe scroll lock: position:fixed + save scrollY
    window.__payScrollY = window.scrollY;
    document.body.style.cssText = 'position:fixed;top:-' + window.__payScrollY + 'px;width:100%;overflow-y:scroll;';
}
function closeFullscreen() {
    if (!overlay) return;
    overlay.classList.remove('open');
    document.body.style.cssText = '';
    window.scrollTo(0, window.__payScrollY || 0);
    if (fullImg) setTimeout(function () { fullImg.src = ''; }, 220);
}

if (img) {
    img.addEventListener('load',  showQr);
    img.addEventListener('error', showQrError);
    if (img.complete) { img.naturalWidth > 0 ? showQr() : showQrError(); }
}
if (qrBox)    { qrBox.addEventListener('click', openFullscreen); qrBox.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); openFullscreen(); } }); }
if (overlay)  overlay.addEventListener('click', function(e){ if(e.target===overlay) closeFullscreen(); });
if (closeBtn) closeBtn.addEventListener('click', closeFullscreen);
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeFullscreen(); });

/* ── Mark-Paid modal ─────────────────────────────────────────── */
var markPaidModal   = document.getElementById('markPaidModal');
var btnOpenMarkPaid = document.getElementById('btnOpenMarkPaid');
var btnStickyPaid   = document.getElementById('btnStickyPaid');
var btnMarkPaidCancel = document.getElementById('btnMarkPaidCancel');
var markPaidForm    = document.getElementById('markPaidForm');
var btnMarkPaidConfirm = document.getElementById('btnMarkPaidConfirm');
var modeGrid        = document.getElementById('modeGrid');
var hiddenMode      = document.getElementById('hiddenMode');

function openMarkPaid() { if (markPaidModal) markPaidModal.classList.add('open'); }
function closeMarkPaid() { if (markPaidModal) markPaidModal.classList.remove('open'); }

if (btnOpenMarkPaid) btnOpenMarkPaid.addEventListener('click', openMarkPaid);
if (btnStickyPaid)   btnStickyPaid.addEventListener('click', openMarkPaid);
if (btnMarkPaidCancel) btnMarkPaidCancel.addEventListener('click', closeMarkPaid);
if (markPaidModal) markPaidModal.addEventListener('click', function(e){ if(e.target===markPaidModal) closeMarkPaid(); });

// Mode buttons
if (modeGrid) {
    modeGrid.addEventListener('click', function(e) {
        var btn = e.target.closest('.mode-btn');
        if (!btn) return;
        modeGrid.querySelectorAll('.mode-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        if (hiddenMode) hiddenMode.value = btn.dataset.mode;
    });
}

// Submit guard
if (markPaidForm) {
    markPaidForm.addEventListener('submit', function() {
        if (btnMarkPaidConfirm) {
            btnMarkPaidConfirm.disabled = true;
            btnMarkPaidConfirm.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Processing…';
        }
    });
}

/* ── Reject modal ────────────────────────────────────────────── */
var rejectModal      = document.getElementById('rejectModal');
var btnOpenReject    = document.getElementById('btnOpenReject');
var btnStickyReject  = document.getElementById('btnStickyReject');
var btnRejectCancel  = document.getElementById('btnRejectCancel');
var rejectForm       = document.getElementById('rejectForm');
var btnRejectConfirm = document.getElementById('btnRejectConfirm');

function openReject()  { if (rejectModal) rejectModal.classList.add('open'); }
function closeReject() { if (rejectModal) rejectModal.classList.remove('open'); }

if (btnOpenReject)   btnOpenReject.addEventListener('click', openReject);
if (btnStickyReject) btnStickyReject.addEventListener('click', openReject);
if (btnRejectCancel) btnRejectCancel.addEventListener('click', closeReject);
if (rejectModal)     rejectModal.addEventListener('click', function(e){ if(e.target===rejectModal) closeReject(); });

if (rejectForm) {
    rejectForm.addEventListener('submit', function() {
        if (btnRejectConfirm) {
            btnRejectConfirm.disabled = true;
            btnRejectConfirm.textContent = 'Rejecting…';
        }
    });
}

/* ── Proof upload toggle ─────────────────────────────────────── */
var btnToggleProof = document.getElementById('btnToggleProof');
var proofForm      = document.getElementById('proofForm');

if (btnToggleProof && proofForm) {
    btnToggleProof.addEventListener('click', function() {
        var open = proofForm.style.display === 'block';
        proofForm.style.display = open ? 'none' : 'block';
        btnToggleProof.style.borderColor = open ? '' : 'var(--g700)';
        btnToggleProof.style.color       = open ? '' : 'var(--g700)';
    });
}

/* ── Auto-dismiss flash banners after 6s ─────────────────────── */
document.querySelectorAll('.flash').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .5s, max-height .5s, padding .5s, margin .5s';
        el.style.opacity = '0'; el.style.maxHeight = '0';
        el.style.padding = '0'; el.style.margin = '0';
        setTimeout(function() { el.remove(); }, 550);
    }, 6000);
});

}());
</script>

</body>
</html>
