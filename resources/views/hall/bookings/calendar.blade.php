<x-admin-layout title="Venue Operations Calendar">
@push('styles')
{{-- FullCalendar 6 global bundle injects its own CSS via JS — no separate stylesheet needed --}}
<style>
/* ── Calendar header: dark dramatic override ───────────────────── */
.ef-cal-header {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    margin-bottom: 24px;
    /* NO overflow:hidden — it clips positioned dropdowns AND makes native <select>
       option text inherit dark color, rendering invisible on OS light popup background */
    overflow: visible;
    padding: 32px;
    position: relative;
    z-index: 10;
}
.ef-cal-header::before {
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    /* keep orb within header bounds: was right:-80px top:-140px which bled outside */
    height: 360px;
    pointer-events: none;
    position: absolute;
    right: 0;
    top: -60px;
    width: 360px;
    z-index: 0;
}
.ef-cal-header::after {
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 70%);
    border-radius: 50%;
    /* keep orb within header bounds: was bottom:-80px which bled outside */
    bottom: -20px;
    content: "";
    height: 220px;
    left: 28%;
    pointer-events: none;
    position: absolute;
    width: 220px;
    z-index: 0;
}
.ef-cal-kicker  { color: rgba(160,114,56,.9) !important; }
.ef-cal-title   { color: #fffdfa !important; }
.ef-cal-subtitle { color: rgba(255,253,250,.52) !important; }
/* Controls need a high z-index so any dropdown/popover renders above the
   insights strip, quick actions, and calendar grid that follow in DOM order */
.ef-cal-controls {
    position: relative;
    z-index: 50;
}
.ef-cal-controls .ef-btn {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.85);
}
.ef-cal-controls .ef-btn:hover {
    background: rgba(255,255,255,.14);
    color: #fffdfa;
}
.ef-cal-controls .ef-btn-dark {
    background: #a07238;
    border-color: #a07238;
    color: #fff;
}
.ef-cal-controls .ef-btn-dark:hover {
    background: #b8854a;
    border-color: #b8854a;
}
.ef-cal-select,
.ef-cal-search {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.88);
}
.ef-cal-select::placeholder,
.ef-cal-search::placeholder { color: rgba(255,253,250,.32); }
.ef-cal-select:focus,
.ef-cal-search:focus {
    border-color: rgba(160,114,56,.6);
    box-shadow: 0 0 0 3px rgba(160,114,56,.12);
}
/* ── Custom hall filter dropdown ───────────────────────────────── */
/* Trigger button lives inside the header — menu is portalled to body via JS  */
/* using position:fixed so NO parent overflow/transform/stacking context clips it */
.ef-cal-dd { position: relative; }
.ef-cal-dd-trigger {
    align-items: center;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 10px;
    color: rgba(255,253,250,.9);
    cursor: pointer;
    display: inline-flex;
    font-size: .8rem;
    font-weight: 680;
    gap: 7px;
    height: 36px;
    letter-spacing: .01em;
    padding: 0 13px;
    transition: background .13s, border-color .13s;
    white-space: nowrap;
}
.ef-cal-dd-trigger:hover { background: rgba(255,255,255,.14); border-color: rgba(255,255,255,.26); }
.ef-cal-dd-trigger[aria-expanded="true"] {
    background: rgba(255,255,255,.14);
    border-color: rgba(160,114,56,.55);
    box-shadow: 0 0 0 3px rgba(160,114,56,.15);
}
.ef-cal-dd-icon { color: rgba(160,114,56,.8); font-size: .75rem; }
.ef-cal-dd-arrow {
    color: rgba(255,253,250,.45);
    font-size: .65rem;
    margin-left: 2px;
    transition: transform .18s;
}
.ef-cal-dd-trigger[aria-expanded="true"] .ef-cal-dd-arrow { transform: rotate(180deg); }

/* Menu — appended to <body> by JS, positioned via getBoundingClientRect + fixed */
.ef-cal-dd-menu {
    background: rgba(18,16,14,.96);
    backdrop-filter: blur(20px) saturate(160%);
    -webkit-backdrop-filter: blur(20px) saturate(160%);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px;
    box-shadow:
        0 2px 8px rgba(0,0,0,.22),
        0 12px 40px rgba(0,0,0,.38),
        inset 0 1px 0 rgba(255,255,255,.06);
    display: none;
    min-width: 180px;
    overflow: hidden;
    padding: 6px;
    position: fixed;          /* escape every overflow/stacking context */
    z-index: 99999;
}
.ef-cal-dd-menu.--open { display: block; }
.ef-cal-dd-item {
    align-items: center;
    background: none;
    border: none;
    border-radius: 9px;
    color: rgba(255,253,250,.78);
    cursor: pointer;
    display: flex;
    font-size: .8rem;
    font-weight: 580;
    gap: 8px;
    padding: 9px 12px;
    text-align: left;
    transition: background .1s, color .1s;
    width: 100%;
}
.ef-cal-dd-item:hover { background: rgba(255,255,255,.08); color: #fffdfa; }
.ef-cal-dd-item.--active {
    background: rgba(160,114,56,.18);
    color: #c8a857;
    font-weight: 700;
}
.ef-cal-dd-item.--active::after {
    content: "\F633";           /* bi-check-lg unicode */
    font-family: "bootstrap-icons";
    font-size: .75rem;
    margin-left: auto;
}
@media (min-width: 768px) { .ef-cal-dd-menu { animation: ddFadeIn .14s ease; } }
@keyframes ddFadeIn {
    from { opacity: 0; transform: translateY(-6px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ── Quick actions row ──────────────────────────────────────────── */
.ef-cal-qactions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 18px;
}
.ef-cal-qa {
    align-items: center;
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 10px;
    box-shadow: var(--ef-shadow);
    color: var(--ef-ink-2);
    display: inline-flex;
    font-size: .78rem;
    font-weight: 680;
    gap: 7px;
    height: 36px;
    padding: 0 14px;
    text-decoration: none;
    transition: background .13s, box-shadow .13s, transform .13s;
}
.ef-cal-qa:hover { background: var(--ef-surface-2); box-shadow: var(--ef-shadow-hover); color: var(--ef-ink); transform: translateY(-1px); }
.ef-cal-qa i { color: var(--ef-faint); font-size: .8rem; }
.ef-cal-qa.--primary {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
    font-weight: 760;
}
.ef-cal-qa.--primary i { color: rgba(255,253,250,.7); }
.ef-cal-qa.--primary:hover { background: var(--ef-ink-2); border-color: var(--ef-ink-2); color: #fffdfa; }
.ef-cal-qa-sep { background: var(--ef-border); height: 28px; width: 1px; }

/* ── Today's ops strip ──────────────────────────────────────────── */
.ef-cal-today-strip {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-left: 3px solid #a07238;
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 18px;
    overflow: hidden;
}
.ef-cal-today-head {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 11px 18px;
}
.ef-cal-today-title {
    align-items: center;
    color: var(--ef-ink);
    display: flex;
    font-size: .84rem;
    font-weight: 780;
    gap: 8px;
}
.ef-cal-today-badge {
    background: rgba(160,114,56,.12);
    border: 1px solid rgba(160,114,56,.25);
    border-radius: 20px;
    color: #a07238;
    font-size: .62rem;
    font-weight: 720;
    padding: 2px 9px;
}
.ef-cal-today-kpi {
    align-items: center;
    display: flex;
    gap: 16px;
}
.ef-cal-today-kpi-item {
    text-align: right;
}
.ef-cal-today-kpi-val { color: var(--ef-ink); font-size: .9rem; font-variant-numeric: tabular-nums; font-weight: 760; line-height: 1; }
.ef-cal-today-kpi-lbl { color: var(--ef-faint); font-size: .58rem; font-weight: 720; letter-spacing: .1em; margin-top: 2px; text-transform: uppercase; }
.ef-cal-today-rows { display: flex; flex-wrap: wrap; gap: 0; }
.ef-cal-today-row {
    align-items: center;
    border-right: 1px solid var(--ef-border);
    display: flex;
    flex: 1;
    gap: 10px;
    min-width: 220px;
    padding: 12px 18px;
    text-decoration: none;
    transition: background .12s;
}
.ef-cal-today-row:hover { background: var(--ef-surface-2); }
.ef-cal-today-row:last-child { border-right: none; }
.ef-cal-today-live {
    background: rgba(220,38,38,.1);
    border: 1px solid rgba(220,38,38,.25);
    border-radius: 6px;
    color: #dc2626;
    flex-shrink: 0;
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .06em;
    padding: 2px 6px;
    text-transform: uppercase;
}
.ef-cal-today-time { color: var(--ef-muted); flex-shrink: 0; font-size: .72rem; font-weight: 700; min-width: 60px; }
.ef-cal-today-name { color: var(--ef-ink-2); font-size: .84rem; font-weight: 660; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-cal-today-meta { color: var(--ef-faint); font-size: .68rem; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-cal-today-empty { color: var(--ef-faint); font-size: .82rem; padding: 16px 18px; text-align: center; }

/* ── Mobile overrides (hide desktop elements) ─────────────────── */
@media (max-width: 767.98px) {
    .ef-cal-header      { display: none !important; }
    .ef-cal-insights    { display: none !important; }
    .ef-cal-qactions    { display: none !important; }
    .ef-cal-today-strip { display: none !important; }
    .ef-calendar-card   { display: none !important; }
    .ef-agenda-panel    { display: none !important; }
    .ef-preview         { display: none !important; }
}

/* ── Mobile shell (hidden on desktop) ─────────────────────────── */
.ef-mob-shell { display: none; }

@media (max-width: 767.98px) {
    .ef-mob-shell {
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
        padding-bottom: calc(180px + env(safe-area-inset-bottom, 0px));
    }
}

/* ── Overflow prevention ────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .ef-cal-shell,
    .ef-mob-shell,
    .ef-mob-cal-wrap,
    .ef-mob-cal-grid { max-width: 100%; overflow-x: hidden; }
    body { overflow-x: hidden; }
    .fc, .fc-view, .fc-scroller { overflow-x: hidden !important; max-width: 100%; }
}

/* ── Mobile header ─────────────────────────────────────────────── */
.ef-mob-hdr {
    align-items: center;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 10px 0 14px;
}
.ef-mob-month-nav {
    align-items: center;
    display: flex;
    gap: 2px;
}
.ef-mob-month-label {
    color: var(--ef-ink);
    font-size: 1.1rem;
    font-weight: 760;
    letter-spacing: -.01em;
    min-width: 118px;
    text-align: center;
}
.ef-mob-nav-btn {
    align-items: center;
    background: rgba(20,20,18,.05);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    color: var(--ef-ink-2);
    cursor: pointer;
    display: inline-flex;
    font-size: .9rem;
    height: 32px;
    justify-content: center;
    transition: background .12s;
    width: 32px;
}
.ef-mob-nav-btn:active { background: rgba(20,20,18,.12); }
.ef-mob-hdr-actions {
    align-items: center;
    display: flex;
    gap: 5px;
}
.ef-mob-hdr-btn {
    align-items: center;
    background: rgba(255,253,250,.9);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    color: var(--ef-ink-2);
    cursor: pointer;
    display: inline-flex;
    font-size: .73rem;
    font-weight: 700;
    height: 32px;
    padding: 0 9px;
    transition: background .12s;
    white-space: nowrap;
    -webkit-appearance: none;
    appearance: none;
}
.ef-mob-hdr-btn:active { background: var(--ef-surface-2); }
.ef-mob-hdr-btn.--new {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
    font-size: 1rem;
    padding: 0 11px;
    text-decoration: none;
}

/* ── Hall filter select ─────────────────────────────────────────── */
.ef-mob-select-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.ef-mob-select-wrap::after {
    border: 4px solid transparent;
    border-top-color: var(--ef-ink-2);
    content: '';
    pointer-events: none;
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-1px);
}
.ef-mob-hall-select {
    -webkit-appearance: none;
    appearance: none;
    background: rgba(255,253,250,.9);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    color: var(--ef-ink-2);
    cursor: pointer;
    font-size: .73rem;
    font-weight: 700;
    height: 32px;
    max-width: 110px;
    padding: 0 22px 0 9px;
    white-space: nowrap;
}
.ef-mob-hall-select:focus {
    border-color: rgba(160,114,56,.5);
    box-shadow: 0 0 0 3px rgba(160,114,56,.1);
    outline: none;
}

/* ── Mobile actions menu button ─────────────────────────────────── */
.ef-mob-menu-wrap {
    position: relative;
}
.ef-mob-menu-btn {
    align-items: center;
    -webkit-appearance: none;
    appearance: none;
    background: rgba(255,253,250,.9);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    color: var(--ef-ink-2);
    cursor: pointer;
    display: inline-flex;
    font-size: 1rem;
    height: 32px;
    justify-content: center;
    padding: 0;
    transition: background .12s;
    -webkit-tap-highlight-color: transparent;
    width: 32px;
}
.ef-mob-menu-btn.--open,
.ef-mob-menu-btn:active { background: rgba(20,20,18,.1); border-color: rgba(20,20,18,.18); }

/* ── Premium dark dropdown ──────────────────────────────────────── */
.ef-mob-dropdown {
    -webkit-backdrop-filter: blur(18px);
    backdrop-filter: blur(18px);
    background: rgba(18,16,14,.96);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px;
    box-shadow:
        0 16px 48px rgba(0,0,0,.42),
        0 4px 16px rgba(0,0,0,.24),
        inset 0 1px 0 rgba(255,255,255,.08);
    max-width: min(260px, calc(100vw - 24px));
    min-width: 200px;
    opacity: 0;
    overflow: hidden;
    pointer-events: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    transform: translateY(-10px) scale(.96);
    transform-origin: top right;
    transition: opacity .18s cubic-bezier(.16,.84,.44,1), transform .18s cubic-bezier(.16,.84,.44,1);
    z-index: 9999;
}
.ef-mob-dropdown.--open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0) scale(1);
}

/* ── Dropdown items ─────────────────────────────────────────────── */
.ef-mob-dd-item {
    align-items: center;
    -webkit-appearance: none;
    appearance: none;
    background: none;
    border: none;
    color: rgba(255,253,250,.88);
    cursor: pointer;
    display: flex;
    font-size: .84rem;
    font-weight: 600;
    gap: 12px;
    min-height: 44px;
    padding: 0 16px;
    text-align: left;
    text-decoration: none;
    transition: background .1s, color .1s;
    width: 100%;
    -webkit-tap-highlight-color: transparent;
}
.ef-mob-dd-item i.dd-icon { color: rgba(255,253,250,.55); font-size: .88rem; flex-shrink: 0; width: 18px; text-align: center; }
.ef-mob-dd-item span { flex: 1; }
.ef-mob-dd-item .dd-check { color: #c8a857; font-size: .88rem; opacity: 0; transition: opacity .12s; }
.ef-mob-dd-item.--checked .dd-check { opacity: 1; }
.ef-mob-dd-item:active,
.ef-mob-dd-item:hover { background: rgba(255,255,255,.07); }
.ef-mob-dd-item:active { background: rgba(255,255,255,.12); }
.ef-mob-dd-item.--wa { color: #4ade80; }
.ef-mob-dd-item.--wa i.dd-icon { color: #4ade80; }
.ef-mob-dd-item.--wa:active { background: rgba(74,222,128,.08); }
.ef-mob-dd-sep {
    background: rgba(255,255,255,.09);
    height: 1px;
    margin: 2px 0;
}

/* ── Active view indicator in header ───────────────────────────── */
.ef-mob-view-badge {
    background: rgba(160,114,56,.18);
    border: 1px solid rgba(160,114,56,.3);
    border-radius: 6px;
    color: rgba(160,114,56,.95);
    display: none;
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .06em;
    padding: 2px 6px;
    text-transform: uppercase;
}
@media (max-width: 767.98px) { .ef-mob-view-badge { display: inline-block; } }

/* ── Insight strip ─────────────────────────────────────────────── */
.ef-mob-insights {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    display: grid;
    gap: 1px;
    grid-template-columns: repeat(3, minmax(0,1fr));
    margin-bottom: 12px;
    overflow: hidden;
}
.ef-mob-ins {
    background: rgba(255,253,250,.92);
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 11px 10px;
    position: relative;
}
.ef-mob-ins + .ef-mob-ins::before {
    background: var(--ef-border);
    bottom: 18%;
    content: '';
    left: 0;
    position: absolute;
    top: 18%;
    width: 1px;
}
.ef-mob-ins-val {
    color: var(--ef-ink);
    font-size: 1.2rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
}
.ef-mob-ins-lbl {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 720;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.ef-mob-ins-val.--emerald { color: var(--ef-emerald); }
.ef-mob-ins-val.--gold    { color: var(--ef-gold); }
.ef-mob-ins-val.--danger  { color: var(--ef-danger); }

/* ── Month calendar grid ───────────────────────────────────────── */
.ef-mob-cal-wrap {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 14px;
    overflow: hidden;
    padding: 12px 10px 8px;
}
.ef-mob-cal-dow {
    display: grid;
    grid-template-columns: repeat(7,1fr);
    margin-bottom: 2px;
}
.ef-mob-cal-dow-cell {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .08em;
    padding: 4px 0;
    text-align: center;
    text-transform: uppercase;
}
.ef-mob-cal-dow-cell:nth-child(6),
.ef-mob-cal-dow-cell:nth-child(7) { color: var(--ef-muted); }

.ef-mob-cal-grid {
    display: grid;
    gap: 2px;
    grid-template-columns: repeat(7,1fr);
}
.ef-mob-cal-day {
    aspect-ratio: 1;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 3px 1px;
    position: relative;
    transition: background .1s;
    -webkit-tap-highlight-color: transparent;
}
.ef-mob-cal-day:active { opacity: .7; }
.ef-mob-cal-day.--empty,
.ef-mob-cal-day.--other { cursor: default; opacity: .28; pointer-events: none; }
.ef-mob-cal-num {
    align-items: center;
    border-radius: 50%;
    color: var(--ef-ink-2);
    display: inline-flex;
    font-size: .76rem;
    font-weight: 700;
    height: 24px;
    justify-content: center;
    line-height: 1;
    transition: background .1s, color .1s;
    width: 24px;
}
.ef-mob-cal-day.--weekend .ef-mob-cal-num { color: var(--ef-muted); }
.ef-mob-cal-day.--today .ef-mob-cal-num {
    background: var(--ef-ink);
    color: #fffdfa;
}
.ef-mob-cal-day.--selected .ef-mob-cal-num {
    background: var(--ef-gold);
    color: #fffdfa;
}
/* Occupancy heatmap */
.ef-mob-cal-day.--occ-1 { background: rgba(169,131,56,.09); }
.ef-mob-cal-day.--occ-2 { background: rgba(169,131,56,.17); }
.ef-mob-cal-day.--occ-3 { background: rgba(61,115,88,.13); }
.ef-mob-cal-day.--selected { background: rgba(169,131,56,.18) !important; }
/* Occupancy dots */
.ef-mob-cal-dots {
    align-items: center;
    display: flex;
    gap: 2px;
    justify-content: center;
    min-height: 5px;
}
.ef-mob-cal-dot {
    border-radius: 50%;
    flex-shrink: 0;
    height: 4px;
    width: 4px;
}
.ef-mob-cal-day.--occ-1 .ef-mob-cal-dot { background: rgba(169,131,56,.65); }
.ef-mob-cal-day.--occ-2 .ef-mob-cal-dot { background: rgba(169,131,56,.85); }
.ef-mob-cal-day.--occ-3 .ef-mob-cal-dot { background: var(--ef-emerald); }

/* ── Upcoming list ─────────────────────────────────────────────── */
.ef-mob-sec-hdr {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 0 2px;
}
.ef-mob-sec-title {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}
.ef-mob-sec-action {
    background: none;
    border: none;
    color: var(--ef-muted);
    cursor: pointer;
    font-size: .7rem;
    font-weight: 700;
    padding: 0;
}
.ef-mob-upcoming-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.ef-mob-ev-row {
    align-items: center;
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-left: 3px solid var(--ef-gold);
    border-radius: 11px;
    box-shadow: 0 1px 3px rgba(24,22,18,.04);
    color: inherit;
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(0,1fr) auto;
    padding: 10px 12px;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
}
.ef-mob-ev-row:active { opacity: .8; }
.ef-mob-ev-row.--confirmed { border-left-color: var(--ef-emerald); }
.ef-mob-ev-row.--completed { border-left-color: var(--ef-bluegray); }
.ef-mob-ev-row.--cancelled { border-left-color: var(--ef-danger); opacity: .7; }
.ef-mob-ev-name {
    color: var(--ef-ink);
    font-size: .82rem;
    font-weight: 760;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-mob-ev-meta {
    color: var(--ef-muted);
    font-size: .71rem;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-mob-ev-date {
    color: var(--ef-ink);
    font-size: .68rem;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    text-align: right;
    white-space: nowrap;
}
.ef-mob-ev-amt {
    color: var(--ef-muted);
    font-size: .67rem;
    margin-top: 2px;
    text-align: right;
}
.ef-mob-chip {
    background: rgba(20,20,18,.045);
    border: 1px solid rgba(20,20,18,.065);
    border-radius: 999px;
    display: inline-block;
    font-size: .57rem;
    font-weight: 720;
    margin-top: 3px;
    padding: 2px 6px;
    text-transform: uppercase;
}
.ef-mob-chip.--pending { background: rgba(169,131,56,.1);  color: #806127; }
.ef-mob-chip.--partial { background: rgba(96,112,128,.1); color: #566777; }
.ef-mob-chip.--paid    { background: rgba(61,115,88,.1);  color: #3d7358; }
.ef-mob-empty {
    color: var(--ef-muted);
    font-size: .82rem;
    padding: 18px 0 8px;
    text-align: center;
}

/* ── Bottom sheet ──────────────────────────────────────────────── */
.ef-mob-overlay {
    background: rgba(14,13,12,.52);
    bottom: 0;
    left: 0;
    opacity: 0;
    pointer-events: none;
    position: fixed;
    right: 0;
    top: 0;
    transition: opacity .22s;
    z-index: 1060;
}
.ef-mob-overlay.--on {
    opacity: 1;
    pointer-events: auto;
}
.ef-mob-sheet {
    background: var(--ef-surface);
    border-radius: 20px 20px 0 0;
    bottom: 0;
    box-shadow: 0 -10px 56px rgba(14,13,12,.18);
    display: flex;
    flex-direction: column;
    left: 0;
    max-height: 80dvh;
    position: fixed;
    right: 0;
    transform: translateY(100%);
    transition: transform .28s cubic-bezier(.18,.82,.16,1);
    z-index: 1061;
}
.ef-mob-sheet.--open { transform: translateY(0); }
.ef-mob-sheet-handle {
    background: var(--ef-border-strong);
    border-radius: 999px;
    height: 4px;
    margin: 10px auto 0;
    width: 36px;
}
.ef-mob-sheet-hdr {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    justify-content: space-between;
    padding: 12px 18px 13px;
}
.ef-mob-sheet-day-kicker {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .12em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
.ef-mob-sheet-date {
    color: var(--ef-ink);
    font-size: 1rem;
    font-weight: 760;
}
.ef-mob-sheet-close {
    align-items: center;
    background: rgba(20,20,18,.055);
    border: 1px solid var(--ef-border);
    border-radius: 50%;
    color: var(--ef-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .85rem;
    height: 30px;
    justify-content: center;
    width: 30px;
}
.ef-mob-sheet-body {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 0 16px;
    -webkit-overflow-scrolling: touch;
}
.ef-mob-sheet-booking {
    border-bottom: 1px solid var(--ef-border);
    padding: 13px 0;
}
.ef-mob-sheet-booking:last-child { border-bottom: 0; }
.ef-mob-sheet-bname {
    color: var(--ef-ink);
    font-size: .88rem;
    font-weight: 760;
    margin-bottom: 4px;
}
.ef-mob-sheet-bmeta {
    color: var(--ef-muted);
    font-size: .74rem;
    line-height: 1.5;
    margin-bottom: 8px;
}
.ef-mob-sheet-brow {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: space-between;
}
.ef-mob-sheet-amt {
    color: var(--ef-ink);
    font-size: .86rem;
    font-variant-numeric: tabular-nums;
    font-weight: 760;
}
.ef-mob-sheet-bal {
    color: var(--ef-muted);
    font-size: .7rem;
    margin-top: 1px;
}
.ef-mob-sheet-actions {
    align-items: center;
    display: flex;
    gap: 5px;
}
.ef-mob-sheet-act {
    align-items: center;
    background: rgba(20,20,18,.04);
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    color: var(--ef-ink-2);
    display: inline-flex;
    font-size: .71rem;
    font-weight: 700;
    gap: 4px;
    height: 28px;
    padding: 0 9px;
    text-decoration: none;
    white-space: nowrap;
}
.ef-mob-sheet-act.--wa {
    background: rgba(37,211,102,.1);
    border-color: rgba(37,211,102,.22);
    color: #1a7a3d;
}
/* ef-mob-sheet-foot defined below with safe-area-inset-bottom */
.ef-mob-sheet-create {
    align-items: center;
    background: var(--ef-ink);
    border-radius: 12px;
    color: #fffdfa;
    display: flex;
    font-size: .83rem;
    font-weight: 760;
    gap: 8px;
    justify-content: center;
    padding: 13px 18px;
    text-decoration: none;
}
.ef-mob-sheet-create:hover,
.ef-mob-sheet-create:active { background: var(--ef-ink-2); color: #fffdfa; }

/* ── Day cell booking count badge ───────────────────────────────── */
.ef-mob-cal-badge {
    background: rgba(160,114,56,.8);
    border-radius: 999px;
    color: #fff;
    font-size: .47rem;
    font-weight: 800;
    letter-spacing: .01em;
    line-height: 1;
    min-width: 14px;
    padding: 2px 4px;
    text-align: center;
}
.ef-mob-cal-day.--occ-3 .ef-mob-cal-badge { background: var(--ef-emerald); }

/* ── Safe-area bottom sheet footer ─────────────────────────────── */
.ef-mob-sheet-foot {
    border-top: 1px solid var(--ef-border);
    padding: 11px 16px calc(14px + env(safe-area-inset-bottom, 0px));
}

/* ── Premium full-width pill FAB ────────────────────────────────── */
@media (max-width: 767.98px) {
    .ef-mobile-fab { display: none !important; }
    .ef-mob-fab-pill {
        align-items: center;
        background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%);
        border-radius: 999px;
        bottom: calc(76px + env(safe-area-inset-bottom, 0px));
        box-shadow:
            0 8px 32px rgba(0,0,0,.35),
            0 2px 8px rgba(0,0,0,.18),
            inset 0 1px 0 rgba(255,255,255,.08),
            0 0 0 1px rgba(160,114,56,.32);
        color: #ffffff;
        display: flex;
        font-size: .9rem;
        font-weight: 760;
        gap: 8px;
        justify-content: center;
        left: 16px;
        letter-spacing: .01em;
        padding: 16px 24px;
        position: fixed;
        right: 16px;
        text-decoration: none;
        transition: transform .1s, box-shadow .1s;
        z-index: 1050;
    }
    .ef-mob-fab-pill i { color: #c8a857; font-size: 1rem; }
    .ef-mob-fab-pill:hover { color: #fff; }
    .ef-mob-fab-pill:active {
        box-shadow: 0 4px 16px rgba(0,0,0,.28);
        transform: scale(.98);
    }
}
@media (min-width: 768px) { .ef-mob-fab-pill { display: none !important; } }
</style>
@endpush

@php
    $currentMonth = now()->format('F Y');
    $todayLabel   = now()->format('l, d M Y');

    // Build share brief data for the modal
    $eventTypes   = \App\Models\HallBooking::eventTypes();
    $shareBooking = $nextBooking ?? null;
    $shareData    = null;

    if ($shareBooking) {
        $start       = \Carbon\Carbon::parse($shareBooking->start_time);
        $end         = \Carbon\Carbon::parse($shareBooking->end_time);
        $isLive      = now()->between($start, $end);
        $totalPaid   = $shareBooking->total_paid;
        $balance     = max(0, $shareBooking->balance_amount);
        $meals       = array_filter([
            $shareBooking->has_breakfast ? 'Breakfast' : null,
            $shareBooking->has_lunch     ? 'Lunch'     : null,
            $shareBooking->has_dinner    ? 'Dinner'    : null,
        ]);
        $otherEvents = $todayBookings
            ->where('id', '!=', $shareBooking->id)
            ->map(fn ($b) => ($eventTypes[$b->event_type] ?? ucfirst($b->event_type)) . ' – ' . \Carbon\Carbon::parse($b->start_time)->format('h:i A'))
            ->values()
            ->all();

        $shareData = [
            'is_live'      => $isLive,
            'hall'         => $shareBooking->hall?->name ?? 'Akshathay Mini Hall',
            'event'        => $eventTypes[$shareBooking->event_type] ?? ucfirst(str_replace('_', ' ', $shareBooking->event_type)),
            'customer'     => $shareBooking->customer_name,
            'phone'        => $shareBooking->customer_mobile,
            'date'         => $shareBooking->booking_date->format('d M Y'),
            'time_start'   => $start->format('h:i A'),
            'time_end'     => $end->format('h:i A'),
            'guests'       => number_format($shareBooking->number_of_people),
            'meal_plan'    => $shareBooking->mealPlan?->name ?? null,
            'meals'        => implode(', ', $meals),
            'status'       => ucfirst($shareBooking->status),
            'paid'         => number_format($totalPaid, 0),
            'balance'      => number_format($balance, 0),
            'has_balance'  => $balance > 0,
            'other_events' => $otherEvents,
        ];
    }
@endphp

<div class="ef-cal-shell">

    {{-- ══ DESKTOP HEADER ══════════════════════════════════════════ --}}
    <header class="ef-cal-header">
        <div>
            <div class="ef-cal-kicker">Luxury Venue Operations</div>
            <h1 class="ef-cal-title">Calendar Overview</h1>
            <div class="ef-cal-subtitle">
                <span id="calendarPeriod">{{ $currentMonth }}</span>
                <span>{{ $summary['total_bookings'] }} bookings</span>
                <span>{{ $summary['occupancy'] }}% occupancy</span>
                <span>{{ $todayLabel }}</span>
            </div>
        </div>
        <div class="ef-cal-controls">
            {{-- Hidden native select: keeps all existing JS (hallFilter.value / change events) unchanged --}}
            <select id="hallFilter" style="display:none" aria-hidden="true">
                <option value="">All Halls</option>
                @foreach($halls as $hall)
                    <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                @endforeach
            </select>
            {{-- Custom dropdown trigger — menu is portalled to body via JS (position:fixed) --}}
            <div class="ef-cal-dd" id="hallDd">
                <button type="button" class="ef-cal-dd-trigger" id="hallDdTrigger"
                        aria-haspopup="listbox" aria-expanded="false" aria-label="Filter by hall">
                    <i class="bi bi-building-fill ef-cal-dd-icon"></i>
                    <span id="hallDdLabel">All Halls</span>
                    <i class="bi bi-chevron-down ef-cal-dd-arrow"></i>
                </button>
            </div>
            <input id="calendarSearch" type="search" class="ef-cal-search" placeholder="Search customer, hall, event">
            <button type="button" class="ef-btn" id="printSchedule"><i class="bi bi-printer"></i> Print</button>
            <button type="button" class="ef-btn" id="exportSchedule"><i class="bi bi-download"></i> Export</button>
            <button type="button" class="ef-btn" id="shareBriefBtn"
                    data-bs-toggle="modal" data-bs-target="#shareBriefModal">
                <i class="bi bi-whatsapp"></i> Share
            </button>
            <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-plus-lg"></i> New Booking
            </a>
        </div>
    </header>

    {{-- ══ DESKTOP INSIGHTS ════════════════════════════════════════ --}}
    <section class="ef-cal-insights" aria-label="Monthly booking insights">
        <div class="ef-cal-insight">
            <span class="ef-label">Month Bookings</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['total_bookings']) }}</div>
            <div class="ef-cal-insight-caption">confirmed operational load</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Upcoming</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['upcoming_events']) }}</div>
            <div class="ef-cal-insight-caption">events from today</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Revenue</span>
            <div class="ef-cal-insight-value">₹{{ number_format($summary['revenue'], 0) }}</div>
            <div class="ef-cal-insight-caption">booked this month</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Occupancy</span>
            <div class="ef-cal-insight-value">{{ $summary['occupancy'] }}%</div>
            <div class="ef-cal-insight-caption">days with bookings</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Pending Pay</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['pending_payments']) }}</div>
            <div class="ef-cal-insight-caption">need follow-up</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Catering Load</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['catering_load']) }}</div>
            <div class="ef-cal-insight-caption">guest covers planned</div>
        </div>
    </section>

    {{-- ══ QUICK ACTIONS ══════════════════════════════════════════════ --}}
    <div class="ef-cal-qactions">
        <a href="{{ route('hall.bookings.create') }}" class="ef-cal-qa --primary">
            <i class="bi bi-plus-lg"></i> Create Booking
        </a>
        <div class="ef-cal-qa-sep"></div>
        <a href="{{ route('hall.bookings.index') }}" class="ef-cal-qa">
            <i class="bi bi-list-ul"></i> All Bookings
        </a>
        <a href="{{ route('hall.bookings.kitchen') }}" class="ef-cal-qa">
            <i class="bi bi-fire"></i> Kitchen Summary
        </a>
        <a href="{{ route('hall.reports.index') }}" class="ef-cal-qa">
            <i class="bi bi-bar-chart-line"></i> Reports
        </a>
        <a href="{{ route('hall.meal-plans.index') }}" class="ef-cal-qa">
            <i class="bi bi-card-list"></i> Meal Plans
        </a>
    </div>

    {{-- ══ TODAY'S OPERATIONS STRIP ══════════════════════════════════ --}}
    <div class="ef-cal-today-strip">
        <div class="ef-cal-today-head">
            <div class="ef-cal-today-title">
                <i class="bi bi-calendar-day"></i>
                Today's Operations
                <span class="ef-cal-today-badge">{{ now()->format('D, d M') }}</span>
            </div>
            <div class="ef-cal-today-kpi">
                <div class="ef-cal-today-kpi-item">
                    <div class="ef-cal-today-kpi-val">{{ $summary['today_count'] }}</div>
                    <div class="ef-cal-today-kpi-lbl">Bookings</div>
                </div>
                <div class="ef-cal-today-kpi-item">
                    <div class="ef-cal-today-kpi-val">₹{{ number_format($summary['today_revenue'], 0) }}</div>
                    <div class="ef-cal-today-kpi-lbl">Revenue</div>
                </div>
            </div>
        </div>
        @if($todayBookings->isEmpty())
            <div class="ef-cal-today-empty">
                <i class="bi bi-moon-stars"></i> No bookings scheduled for today
            </div>
        @else
            <div class="ef-cal-today-rows">
                @foreach($todayBookings as $tb)
                    @php
                        $tbNow    = now();
                        $tbStart  = \Carbon\Carbon::parse($tb->start_time);
                        $tbEnd    = \Carbon\Carbon::parse($tb->end_time);
                        $tbIsLive = $tbStart->isPast() && $tbEnd->isFuture();
                    @endphp
                    <a href="{{ route('hall.bookings.show', $tb) }}" class="ef-cal-today-row">
                        @if($tbIsLive)
                            <span class="ef-cal-today-live">Live</span>
                        @endif
                        <span class="ef-cal-today-time">
                            {{ \Carbon\Carbon::parse($tb->start_time)->format('g:i A') }}
                        </span>
                        <div style="min-width:0;flex:1;">
                            <div class="ef-cal-today-name">{{ $tb->customer_name }}</div>
                            <div class="ef-cal-today-meta">
                                {{ $tb->event_type }} &middot; {{ number_format($tb->number_of_people) }} pax
                                @if($tb->hall) &middot; {{ $tb->hall->name }} @endif
                            </div>
                        </div>
                        <span style="color:var(--ef-faint);font-size:.72rem;white-space:nowrap;">
                            ₹{{ number_format($tb->total_amount, 0) }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ══ DESKTOP FULLCALENDAR ════════════════════════════════════ --}}
    <section class="ef-calendar-card">
        <div class="ef-calendar-toolbar">
            <div class="ef-cal-nav">
                <button type="button" class="ef-btn ef-btn-icon" id="calPrev" aria-label="Previous period"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="ef-btn" id="calToday">Today</button>
                <button type="button" class="ef-btn ef-btn-icon" id="calNext" aria-label="Next period"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div class="ef-cal-month" id="calendarTitle">{{ $currentMonth }}</div>
            <div class="ef-view-switcher" aria-label="Calendar view">
                <button type="button" class="ef-view-btn active" data-view="dayGridMonth">Month</button>
                <button type="button" class="ef-view-btn" data-view="timeGridWeek">Week</button>
                <button type="button" class="ef-view-btn" data-view="timeGridDay">Day</button>
                <button type="button" class="ef-view-btn" data-view="listWeek">Agenda</button>
            </div>
        </div>
        <div class="ef-calendar-wrap">
            <div id="venueCalendar"></div>
        </div>
    </section>

    <section class="ef-agenda-panel" id="mobileAgenda" aria-label="Mobile agenda"></section>

    {{-- ══ MOBILE CALENDAR SHELL ═══════════════════════════════════ --}}
    <div class="ef-mob-shell" id="mobShell" aria-label="Mobile calendar">

        {{-- Single toolbar row: month nav + actions --}}
        <div class="ef-mob-hdr">
            <div class="ef-mob-month-nav">
                <button type="button" class="ef-mob-nav-btn" id="mobPrev" aria-label="Previous month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div style="display:flex;flex-direction:column;align-items:center;gap:3px">
                    <span class="ef-mob-month-label" id="mobMonthLabel">{{ $currentMonth }}</span>
                    <span class="ef-mob-view-badge" id="mobViewBadge">Calendar</span>
                </div>
                <button type="button" class="ef-mob-nav-btn" id="mobNext" aria-label="Next month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="ef-mob-hdr-actions">
                <div class="ef-mob-select-wrap">
                    <select id="mobHallFilter" class="ef-mob-hall-select" aria-label="Filter hall">
                        <option value="">All Halls</option>
                        @foreach($halls as $hall)
                            <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="ef-mob-hdr-btn" id="mobTodayBtn">Today</button>
                {{-- Actions menu --}}
                <div class="ef-mob-menu-wrap">
                    <button type="button" class="ef-mob-menu-btn" id="mobMenuBtn" aria-label="Actions" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="ef-mob-dropdown" id="mobActionMenu" role="menu">
                        {{-- View items --}}
                        <button type="button" class="ef-mob-dd-item --checked" id="ddCalendar" data-mob-view="calendar" role="menuitem">
                            <i class="bi bi-calendar3 dd-icon"></i>
                            <span>Calendar</span>
                            <i class="bi bi-check-lg dd-check"></i>
                        </button>
                        <button type="button" class="ef-mob-dd-item" id="ddAgenda" data-mob-view="agenda" role="menuitem">
                            <i class="bi bi-list-ul dd-icon"></i>
                            <span>Agenda</span>
                            <i class="bi bi-check-lg dd-check"></i>
                        </button>
                        <div class="ef-mob-dd-sep"></div>
                        {{-- Kitchen --}}
                        <a href="{{ route('hall.bookings.kitchen') }}" class="ef-mob-dd-item" role="menuitem">
                            <i class="bi bi-fire dd-icon"></i>
                            <span>Kitchen Summary</span>
                            <i class="bi bi-arrow-right dd-check" style="opacity:.38"></i>
                        </a>
                        @if($shareData)
                        <div class="ef-mob-dd-sep"></div>
                        <button type="button" class="ef-mob-dd-item --wa" id="ddShare" role="menuitem">
                            <i class="bi bi-whatsapp dd-icon"></i>
                            <span>Share Brief</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 3-item insight strip --}}
        <div class="ef-mob-insights">
            <div class="ef-mob-ins">
                <div class="ef-mob-ins-val --emerald">{{ $summary['occupancy'] }}%</div>
                <div class="ef-mob-ins-lbl">Occupancy</div>
            </div>
            <div class="ef-mob-ins">
                <div class="ef-mob-ins-val --gold" id="mobPendingVal">{{ $summary['pending_payments'] }}</div>
                <div class="ef-mob-ins-lbl">Pending Pay</div>
            </div>
            <div class="ef-mob-ins">
                <div class="ef-mob-ins-val" id="mobUpcomingVal">{{ $summary['upcoming_events'] }}</div>
                <div class="ef-mob-ins-lbl">Upcoming</div>
            </div>
        </div>

        {{-- Calendar view (default) --}}
        <div id="mobCalView">
            {{-- Custom month grid --}}
            <div class="ef-mob-cal-wrap">
                <div class="ef-mob-cal-dow" aria-hidden="true">
                    <div class="ef-mob-cal-dow-cell">Mo</div>
                    <div class="ef-mob-cal-dow-cell">Tu</div>
                    <div class="ef-mob-cal-dow-cell">We</div>
                    <div class="ef-mob-cal-dow-cell">Th</div>
                    <div class="ef-mob-cal-dow-cell">Fr</div>
                    <div class="ef-mob-cal-dow-cell">Sa</div>
                    <div class="ef-mob-cal-dow-cell">Su</div>
                </div>
                <div class="ef-mob-cal-grid" id="mobCalGrid" role="grid" aria-label="Calendar dates"></div>
            </div>

            {{-- Upcoming events (limited, below calendar) --}}
            <div>
                <div class="ef-mob-sec-hdr">
                    <span class="ef-mob-sec-title">Upcoming Bookings</span>
                    <button type="button" class="ef-mob-sec-action" id="mobToggleAll">See all</button>
                </div>
                <div class="ef-mob-upcoming-list" id="mobUpcomingList"></div>
            </div>
        </div>

        {{-- Agenda view (hidden by default) --}}
        <div id="mobAgendaView" style="display:none">
            <div class="ef-mob-sec-hdr" style="margin-bottom:12px">
                <span class="ef-mob-sec-title">All Upcoming Bookings</span>
            </div>
            <div class="ef-mob-upcoming-list" id="mobAgendaList"></div>
        </div>

    </div>
</div>

{{-- ══ PREMIUM PILL FAB (mobile only) ══════════════════════════════ --}}
<a href="{{ route('hall.bookings.create') }}" class="ef-mob-fab-pill" aria-label="New Booking">
    <i class="bi bi-plus-lg"></i>
    New Booking
</a>

{{-- ══ DESKTOP HOVER PREVIEW ════════════════════════════════════════ --}}
<div class="ef-preview" id="bookingPreview" aria-live="polite"></div>

{{-- ══ QUICK BOOKING MODAL ══════════════════════════════════════════ --}}
<div class="modal fade ef-quick-modal" id="quickBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="ef-label mb-1">Fast Operation</div>
                    <h2 class="modal-title fs-5 fw-bold mb-0">Create booking</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="ef-shell-note mb-4">Start a new booking from the selected date. The full booking form will open with the date and hall context attached.</p>
                <div class="ef-info-grid">
                    <div>
                        <span class="ef-label">Selected Date</span>
                        <div class="ef-value ef-value-strong" id="quickDateLabel">-</div>
                    </div>
                    <div>
                        <span class="ef-label">Hall Context</span>
                        <div class="ef-value ef-value-strong" id="quickHallLabel">All Halls</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark" id="quickCreateLink">
                    Continue <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ══ MOBILE BOTTOM SHEET ══════════════════════════════════════════ --}}
<div class="ef-mob-overlay" id="mobOverlay" aria-hidden="true"></div>
<div class="ef-mob-sheet" id="mobSheet" role="dialog" aria-modal="true" aria-label="Day detail">
    <div class="ef-mob-sheet-handle" aria-hidden="true"></div>
    <div class="ef-mob-sheet-hdr">
        <div>
            <div class="ef-mob-sheet-day-kicker" id="mobSheetKicker">Date</div>
            <div class="ef-mob-sheet-date" id="mobSheetDate">—</div>
        </div>
        <button type="button" class="ef-mob-sheet-close" id="mobSheetClose" aria-label="Close">
            <i class="bi bi-x"></i>
        </button>
    </div>
    <div class="ef-mob-sheet-body" id="mobSheetBody"></div>
    <div class="ef-mob-sheet-foot">
        <a href="{{ route('hall.bookings.create') }}" class="ef-mob-sheet-create" id="mobSheetCreate">
            <i class="bi bi-plus-lg"></i> New Booking for this Date
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isMob = () => window.innerWidth < 768;

    /* ── shared ─────────────────────────────────────────────────── */
    const eventsUrl  = @json(route('hall.bookings.calendar-events'));
    const createBase = @json(route('hall.bookings.create'));

    let mobEvents    = [];
    let mobYear      = new Date().getFullYear();
    let mobMonth     = new Date().getMonth();
    let selectedDate = null;
    let sheetOpen    = false;
    let showAll      = false;

    /* ── utils ──────────────────────────────────────────────────── */
    const money = v => '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const esc   = v => String(v ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[c]);
    const pad   = n => String(n).padStart(2, '0');
    const ds    = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
    const todayDs = () => { const t = new Date(); return ds(t.getFullYear(), t.getMonth(), t.getDate()); };

    /* ── date counts map ────────────────────────────────────────── */
    function countsByDate(events) {
        const c = {};
        events.forEach(ev => {
            const d = (ev.start || '').slice(0, 10);
            if (d) c[d] = (c[d] || 0) + 1;
        });
        return c;
    }

    /* ── build month grid ───────────────────────────────────────── */
    function buildGrid(year, month, counts) {
        document.getElementById('mobMonthLabel').textContent =
            new Date(year, month, 1).toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });

        const today     = todayDs();
        const firstDow  = new Date(year, month, 1).getDay(); // 0=Sun
        const offset    = firstDow === 0 ? 6 : firstDow - 1; // Mon-start
        const daysTotal = new Date(year, month + 1, 0).getDate();

        let html = '';

        for (let i = 0; i < offset; i++)
            html += '<div class="ef-mob-cal-day --empty" aria-hidden="true"></div>';

        for (let d = 1; d <= daysTotal; d++) {
            const dateStr  = ds(year, month, d);
            const count    = counts[dateStr] || 0;
            const isToday  = dateStr === today;
            const isSel    = dateStr === selectedDate;
            const dow      = new Date(year, month, d).getDay();
            const isWknd   = dow === 0 || dow === 6;
            const occCls   = count >= 3 ? ' --occ-3' : count === 2 ? ' --occ-2' : count === 1 ? ' --occ-1' : '';

            const countEl = count > 0
                ? `<div class="ef-mob-cal-badge">${count > 9 ? '9+' : count}</div>`
                : '<div style="height:6px"></div>';

            const ariaLbl = `${d}${count ? ', ' + count + ' booking' + (count > 1 ? 's' : '') : ''}`;

            html += `<div class="ef-mob-cal-day${occCls}${isToday ? ' --today' : ''}${isSel ? ' --selected' : ''}${isWknd ? ' --weekend' : ''}"
                data-date="${dateStr}" role="gridcell" tabindex="0" aria-label="${ariaLbl}">
                <span class="ef-mob-cal-num">${d}</span>
                ${countEl}
            </div>`;
        }

        const trailing = (7 - ((offset + daysTotal) % 7)) % 7;
        for (let i = 0; i < trailing; i++)
            html += '<div class="ef-mob-cal-day --empty" aria-hidden="true"></div>';

        const grid = document.getElementById('mobCalGrid');
        grid.innerHTML = html;

        grid.querySelectorAll('.ef-mob-cal-day:not(.--empty)').forEach(cell => {
            cell.addEventListener('click', () => openSheet(cell.dataset.date));
            cell.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openSheet(cell.dataset.date); }
            });
        });
    }

    /* ── upcoming list ──────────────────────────────────────────── */
    function buildEvRows(events, limit) {
        const todayObj = new Date(); todayObj.setHours(0,0,0,0);
        const rows = events
            .filter(ev => { const d = new Date(ev.start); d.setHours(0,0,0,0); return d >= todayObj; })
            .sort((a, b) => new Date(a.start) - new Date(b.start))
            .slice(0, limit);
        if (!rows.length) return '<div class="ef-mob-empty">No upcoming bookings</div>';
        return rows.map(ev => {
            const p   = ev.extendedProps || {};
            const st  = (ev.classNames || []).find(c => c.startsWith('is-'))?.replace('is-', '') || 'confirmed';
            const mls = (p.meals || []).join(' + ') || 'No meals';
            return `<a href="${esc(p.url)}" class="ef-mob-ev-row --${esc(st)}">
                <div>
                    <div class="ef-mob-ev-name">${esc(p.customer)}</div>
                    <div class="ef-mob-ev-meta">${esc(p.hall)} · ${Number(p.people||0).toLocaleString('en-IN')} guests · ${esc(mls)}</div>
                </div>
                <div>
                    <div class="ef-mob-ev-date">${esc(p.date)}</div>
                    <div class="ef-mob-ev-amt">${money(p.amount)}</div>
                    <div><span class="ef-mob-chip --${esc(p.payment_status)}">${esc(p.payment_status_label)}</span></div>
                </div>
            </a>`;
        }).join('');
    }

    function renderUpcoming(events, all = false) {
        const el = document.getElementById('mobUpcomingList');
        if (el) el.innerHTML = buildEvRows(events, all ? 60 : 5);
    }

    function renderAgenda(events) {
        const el = document.getElementById('mobAgendaList');
        if (el) el.innerHTML = buildEvRows(events, 100);
    }

    /* ── view mode toggle ───────────────────────────────────────── */
    let mobViewMode = 'calendar';

    function setMobView(mode) {
        mobViewMode = mode;
        const calView    = document.getElementById('mobCalView');
        const agendaView = document.getElementById('mobAgendaView');
        const badge      = document.getElementById('mobViewBadge');
        // update dropdown checkmarks
        document.querySelectorAll('.ef-mob-dd-item[data-mob-view]').forEach(t => {
            t.classList.toggle('--checked', t.dataset.mobView === mode);
        });
        if (badge) badge.textContent = mode === 'agenda' ? 'Agenda' : 'Calendar';
        if (mode === 'agenda') {
            if (calView)    calView.style.display    = 'none';
            if (agendaView) agendaView.style.display = '';
            renderAgenda(mobEvents);
        } else {
            if (calView)    calView.style.display    = '';
            if (agendaView) agendaView.style.display = 'none';
        }
    }

    /* ── dropdown menu ──────────────────────────────────────────── */
    const menuBtn = document.getElementById('mobMenuBtn');
    const menuEl  = document.getElementById('mobActionMenu');

    function openMenu() {
        menuEl?.classList.add('--open');
        menuBtn?.classList.add('--open');
        menuBtn?.setAttribute('aria-expanded', 'true');
    }
    function closeMenu() {
        menuEl?.classList.remove('--open');
        menuBtn?.classList.remove('--open');
        menuBtn?.setAttribute('aria-expanded', 'false');
    }
    function toggleMenu() {
        menuEl?.classList.contains('--open') ? closeMenu() : openMenu();
    }

    menuBtn?.addEventListener('click', e => { e.stopPropagation(); toggleMenu(); });

    // View items in dropdown
    document.querySelectorAll('.ef-mob-dd-item[data-mob-view]').forEach(item => {
        item.addEventListener('click', () => {
            setMobView(item.dataset.mobView);
            closeMenu();
        });
    });

    // Share item in dropdown — trigger Bootstrap modal then close menu
    const ddShare = document.getElementById('ddShare');
    if (ddShare) {
        ddShare.addEventListener('click', () => {
            closeMenu();
            const modal = document.getElementById('shareBriefModal');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    }

    // Close on outside click / touch
    document.addEventListener('click', e => {
        if (menuEl?.classList.contains('--open') &&
            !menuEl.contains(e.target) &&
            e.target !== menuBtn) {
            closeMenu();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeMenu();
    });

    /* ── fetch for month ────────────────────────────────────────── */
    function fetchMonth(year, month) {
        const start  = ds(year, month, 1);
        const endDay = new Date(year, month + 1, 0).getDate();
        const end    = ds(year, month, endDay);
        const hall   = document.getElementById('mobHallFilter')?.value || '';
        const params = new URLSearchParams({ start, end });
        if (hall) params.set('hall_id', hall);

        fetch(eventsUrl + '?' + params)
            .then(r => r.json())
            .then(evs => {
                mobEvents = evs;
                buildGrid(year, month, countsByDate(evs));
                renderUpcoming(evs, showAll);
                if (mobViewMode === 'agenda') renderAgenda(evs);
            })
            .catch(err => console.warn('Mobile calendar fetch failed', err));
    }

    /* ── bottom sheet ───────────────────────────────────────────── */
    function openSheet(dateStr) {
        selectedDate = dateStr;

        document.querySelectorAll('.ef-mob-cal-day').forEach(c =>
            c.classList.toggle('--selected', c.dataset.date === dateStr));

        const d   = new Date(dateStr + 'T00:00:00');
        const kicker = d.toLocaleDateString('en-IN', { weekday: 'long' });
        const full   = d.toLocaleDateString('en-IN', { day: '2-digit', month: 'long', year: 'numeric' });

        document.getElementById('mobSheetKicker').textContent = kicker;
        document.getElementById('mobSheetDate').textContent   = full;

        const dayEvs = mobEvents.filter(ev => (ev.start || '').slice(0, 10) === dateStr);
        const body   = document.getElementById('mobSheetBody');

        if (!dayEvs.length) {
            body.innerHTML = '<div class="ef-mob-empty">No bookings on this date</div>';
        } else {
            body.innerHTML = dayEvs.map(ev => {
                const p   = ev.extendedProps || {};
                const mls = (p.meals || []).join(' + ') || 'No meals';
                const bal = p.balance > 0
                    ? `<div class="ef-mob-sheet-bal">Balance ${money(p.balance)}</div>` : '';
                return `<div class="ef-mob-sheet-booking">
                    <div class="ef-mob-sheet-bname">${esc(p.customer)}</div>
                    <div class="ef-mob-sheet-bmeta">
                        ${esc(p.hall)} · ${esc(p.start_time)} – ${esc(p.end_time)}<br>
                        ${Number(p.people||0).toLocaleString('en-IN')} guests · ${esc(mls)}<br>
                        ${esc(p.event_type)}
                    </div>
                    <div class="ef-mob-sheet-brow">
                        <div>
                            <div class="ef-mob-sheet-amt">${money(p.amount)}</div>
                            ${bal}
                        </div>
                        <div class="ef-mob-sheet-actions">
                            <span class="ef-mob-chip --${esc(p.payment_status)}">${esc(p.payment_status_label)}</span>
                            <a href="${esc(p.url)}" class="ef-mob-sheet-act">Open</a>
                            <a href="${esc(p.whatsapp_url)}" target="_blank" rel="noopener" class="ef-mob-sheet-act --wa">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        const hall = document.getElementById('mobHallFilter')?.value || '';
        const p    = new URLSearchParams({ date: dateStr });
        if (hall) p.set('hall_id', hall);
        document.getElementById('mobSheetCreate').href = createBase + '?' + p;

        document.getElementById('mobOverlay').classList.add('--on');
        document.getElementById('mobSheet').classList.add('--open');
        document.body.style.overflow = 'hidden';
        sheetOpen = true;
    }

    function closeSheet() {
        document.getElementById('mobOverlay').classList.remove('--on');
        document.getElementById('mobSheet').classList.remove('--open');
        document.body.style.overflow = '';
        sheetOpen = false;
    }

    /* ── mobile listeners ───────────────────────────────────────── */
    document.getElementById('mobPrev')?.addEventListener('click', () => {
        if (--mobMonth < 0) { mobMonth = 11; mobYear--; }
        fetchMonth(mobYear, mobMonth);
    });
    document.getElementById('mobNext')?.addEventListener('click', () => {
        if (++mobMonth > 11) { mobMonth = 0; mobYear++; }
        fetchMonth(mobYear, mobMonth);
    });
    document.getElementById('mobTodayBtn')?.addEventListener('click', () => {
        const now = new Date();
        mobYear = now.getFullYear(); mobMonth = now.getMonth();
        fetchMonth(mobYear, mobMonth);
    });
    document.getElementById('mobHallFilter')?.addEventListener('change', () => fetchMonth(mobYear, mobMonth));
    document.getElementById('mobSheetClose')?.addEventListener('click', closeSheet);
    document.getElementById('mobOverlay')?.addEventListener('click', closeSheet);

    document.getElementById('mobToggleAll')?.addEventListener('click', function () {
        showAll = !showAll;
        this.textContent = showAll ? 'Show less' : 'See all';
        renderUpcoming(mobEvents, showAll);
    });

    /* ── swipe month nav ────────────────────────────────────────── */
    let swipeX = 0;
    const mobShell = document.getElementById('mobShell');
    mobShell?.addEventListener('touchstart', e => { swipeX = e.touches[0].clientX; }, { passive: true });
    mobShell?.addEventListener('touchend', e => {
        if (sheetOpen) return;
        const dx = e.changedTouches[0].clientX - swipeX;
        if (Math.abs(dx) < 50) return;
        if (dx < 0) { if (++mobMonth > 11) { mobMonth = 0; mobYear++; } }
        else         { if (--mobMonth < 0)  { mobMonth = 11; mobYear--; } }
        fetchMonth(mobYear, mobMonth);
    }, { passive: true });

    /* ── init ───────────────────────────────────────────────────── */
    if (isMob()) {
        fetchMonth(mobYear, mobMonth);
    } else {
        initDesktop();
    }

    /* ══ DESKTOP FULLCALENDAR ════════════════════════════════════ */
    function initDesktop() {
        const calEl       = document.getElementById('venueCalendar');
        const hallFilter  = document.getElementById('hallFilter');
        const searchInput = document.getElementById('calendarSearch');
        const preview     = document.getElementById('bookingPreview');
        const quickModal  = new bootstrap.Modal(document.getElementById('quickBookingModal'));
        const quickLink   = document.getElementById('quickCreateLink');
        const quickDate   = document.getElementById('quickDateLabel');
        const quickHall   = document.getElementById('quickHallLabel');

        let deskEvents   = [];
        let lockedPv     = false;
        let searchTerm   = '';

        const matches = ev => {
            if (!searchTerm) return true;
            const p = ev.extendedProps || {};
            return [ev.title, p.customer, p.hall, p.event_type, p.payment_status_label]
                .filter(Boolean).join(' ').toLowerCase().includes(searchTerm);
        };

        const renderEv = info => {
            const p   = info.event.extendedProps;
            const mls = (p.meals || []).slice(0, 2).join(' + ') || 'No meals';
            return { html: `<div class="ef-event-card">
                <div class="ef-event-title">${esc(p.customer)}</div>
                <div class="ef-event-meta">${esc(p.hall)} · ${Number(p.people||0).toLocaleString('en-IN')} guests</div>
                <div class="ef-event-sub">${esc(mls)} · ${esc(p.start_time)}-${esc(p.end_time)}</div>
                <div class="ef-event-foot">
                    <span class="ef-event-mini pay-${esc(p.payment_status)}">${esc(p.payment_status_label)}</span>
                    <span class="ef-event-mini">${esc(p.status_label)}</span>
                </div>
            </div>` };
        };

        const updateTitles = () => {
            const t = calendar.view.title;
            document.getElementById('calendarTitle').textContent = t;
            document.getElementById('calendarPeriod').textContent = t;
        };

        const applyDensity = () => {
            const c = {};
            calendar.getEvents().forEach(ev => {
                if (!matches(ev)) return;
                const k = ev.startStr.slice(0, 10);
                c[k] = (c[k] || 0) + 1;
            });
            calEl.querySelectorAll('.fc-daygrid-day').forEach(cell => {
                cell.classList.remove('ef-busy-soft', 'ef-busy-mid', 'ef-busy-full');
                const n = c[cell.dataset.date] || 0;
                if (n >= 3) cell.classList.add('ef-busy-full');
                else if (n === 2) cell.classList.add('ef-busy-mid');
                else if (n === 1) cell.classList.add('ef-busy-soft');
            });
        };

        const showPv = (event, jsEvent, lock = false) => {
            const p = event.extendedProps;
            lockedPv = lock;
            preview.innerHTML = `
                <div class="ef-preview-title">${esc(p.customer)}</div>
                <div class="ef-preview-meta">${esc(p.event_type)} · ${esc(p.hall)}<br>${esc(p.date)} · ${esc(p.start_time)}-${esc(p.end_time)}</div>
                <div class="ef-preview-grid">
                    <div><div class="ef-preview-label">Guests</div><div class="ef-preview-value">${Number(p.people||0).toLocaleString('en-IN')}</div></div>
                    <div><div class="ef-preview-label">Meals</div><div class="ef-preview-value">${esc((p.meals||[]).join(', ')||'None')}</div></div>
                    <div><div class="ef-preview-label">Total</div><div class="ef-preview-value">${money(p.amount)}</div></div>
                    <div><div class="ef-preview-label">Balance</div><div class="ef-preview-value">${money(p.balance)}</div></div>
                </div>
                <div class="ef-preview-actions">
                    <a href="${p.url}">Open</a>
                    <a href="${p.payment_url}">Payment</a>
                    <a href="${p.whatsapp_url}" target="_blank" rel="noopener">WhatsApp</a>
                </div>`;
            const mg = 18, w = 320;
            preview.style.left = Math.max(mg, Math.min(jsEvent.clientX + 16, window.innerWidth - w - mg)) + 'px';
            preview.style.top  = Math.max(mg, Math.min(jsEvent.clientY + 16, window.innerHeight - preview.offsetHeight - mg)) + 'px';
            preview.classList.add('show');
        };

        const hidePv = (force = false) => {
            if (lockedPv && !force) return;
            preview.classList.remove('show');
            lockedPv = false;
        };

        const openQuick = dateStr => {
            const params = new URLSearchParams({ date: dateStr });
            if (hallFilter.value) params.set('hall_id', hallFilter.value);
            quickDate.textContent = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-IN', {
                weekday: 'long', day: '2-digit', month: 'short', year: 'numeric'
            });
            quickHall.textContent = hallFilter.options[hallFilter.selectedIndex]?.text || 'All Halls';
            quickLink.href = createBase + '?' + params;
            quickModal.show();
        };

        const calendar = new FullCalendar.Calendar(calEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 'auto',
            firstDay: 1,
            nowIndicator: true,
            dayMaxEvents: 3,
            eventDisplay: 'block',
            selectable: true,
            selectMirror: true,
            slotMinTime: '06:00:00',
            slotMaxTime: '23:00:00',
            allDaySlot: false,
            events: (info, success, failure) => {
                const params = new URLSearchParams({ start: info.startStr, end: info.endStr, hall_id: hallFilter.value });
                fetch(eventsUrl + '?' + params)
                    .then(r => r.json())
                    .then(evs => {
                        deskEvents = evs;
                        success(evs.filter(ev => {
                            const p = ev.extendedProps || {};
                            return !searchTerm || [ev.title, p.customer, p.hall, p.event_type, p.payment_status_label]
                                .filter(Boolean).join(' ').toLowerCase().includes(searchTerm);
                        }));
                    }).catch(failure);
            },
            datesSet:     () => { updateTitles(); setTimeout(applyDensity, 0); },
            eventsSet:    () => applyDensity(),
            eventContent: renderEv,
            select:       info => { openQuick(info.startStr.slice(0, 10)); calendar.unselect(); },
            dateClick:    info => openQuick(info.dateStr),
            eventClick:   info => { info.jsEvent.preventDefault(); showPv(info.event, info.jsEvent, true); },
            eventMouseEnter: info => { if (!lockedPv) showPv(info.event, info.jsEvent, false); },
            eventMouseLeave: () => hidePv(),
        });

        calendar.render();
        updateTitles();

        // Reposition preview on native mouse move (eventMouseMove is not a FullCalendar option)
        document.getElementById('venueCalendar').addEventListener('mousemove', e => {
            if (!lockedPv && preview && preview.classList.contains('show')) {
                const mg = 18, w = 320;
                preview.style.left = Math.max(mg, Math.min(e.clientX + 16, window.innerWidth  - w - mg)) + 'px';
                preview.style.top  = Math.max(mg, Math.min(e.clientY + 16,  window.innerHeight - preview.offsetHeight - mg)) + 'px';
            }
        });

        document.getElementById('calPrev').addEventListener('click', () => { hidePv(true); calendar.prev(); });
        document.getElementById('calNext').addEventListener('click', () => { hidePv(true); calendar.next(); });
        document.getElementById('calToday').addEventListener('click', () => { hidePv(true); calendar.today(); });

        document.querySelectorAll('.ef-view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ef-view-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                calendar.changeView(btn.dataset.view);
            });
        });

        hallFilter.addEventListener('change', () => { hidePv(true); calendar.refetchEvents(); });

        // ── Portal dropdown for "All Halls" filter ──────────────────────────
        // Menu is appended to <body> and positioned via position:fixed so NO
        // parent overflow/transform/stacking context can clip or hide it.
        (function () {
            const trigger  = document.getElementById('hallDdTrigger');
            const label    = document.getElementById('hallDdLabel');
            if (!trigger || !label) return;

            // Build menu element and append to body (portal)
            const menu = document.createElement('div');
            menu.className = 'ef-cal-dd-menu';
            menu.setAttribute('role', 'listbox');
            menu.setAttribute('aria-label', 'Select hall');

            Array.from(hallFilter.options).forEach(opt => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ef-cal-dd-item' + (opt.value === '' ? ' --active' : '');
                btn.dataset.value = opt.value;
                btn.textContent = opt.text;
                btn.setAttribute('role', 'option');
                btn.setAttribute('aria-selected', opt.value === '' ? 'true' : 'false');
                menu.appendChild(btn);
            });
            document.body.appendChild(menu);

            function positionMenu() {
                const r = trigger.getBoundingClientRect();
                const menuH = menu.offsetHeight || 200;
                const spaceBelow = window.innerHeight - r.bottom - 8;
                const above = spaceBelow < menuH && r.top > menuH;
                menu.style.left   = r.left + 'px';
                menu.style.minWidth = r.width + 'px';
                if (above) {
                    menu.style.top    = '';
                    menu.style.bottom = (window.innerHeight - r.top + 6) + 'px';
                } else {
                    menu.style.bottom = '';
                    menu.style.top    = (r.bottom + 6) + 'px';
                }
            }

            function openDd() {
                menu.classList.add('--open');
                trigger.setAttribute('aria-expanded', 'true');
                positionMenu();
            }
            function closeDd() {
                menu.classList.remove('--open');
                trigger.setAttribute('aria-expanded', 'false');
            }
            function isOpen() { return menu.classList.contains('--open'); }

            trigger.addEventListener('click', e => {
                e.stopPropagation();
                isOpen() ? closeDd() : openDd();
            });

            menu.addEventListener('click', e => {
                const btn = e.target.closest('.ef-cal-dd-item');
                if (!btn) return;
                const val = btn.dataset.value;

                // Sync hidden native select (triggers existing change listener)
                hallFilter.value = val;
                hallFilter.dispatchEvent(new Event('change'));

                // Update visual state
                label.textContent = btn.textContent;
                menu.querySelectorAll('.ef-cal-dd-item').forEach(b => {
                    b.classList.toggle('--active', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                closeDd();
            });

            // Close on outside click or Escape
            document.addEventListener('click', e => {
                if (!trigger.contains(e.target) && !menu.contains(e.target)) closeDd();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && isOpen()) { closeDd(); trigger.focus(); }
                if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && isOpen()) {
                    e.preventDefault();
                    const items = Array.from(menu.querySelectorAll('.ef-cal-dd-item'));
                    const cur = menu.querySelector('.ef-cal-dd-item:focus') || menu.querySelector('.ef-cal-dd-item.--active');
                    const idx = items.indexOf(cur);
                    const next = e.key === 'ArrowDown'
                        ? items[Math.min(idx + 1, items.length - 1)]
                        : items[Math.max(idx - 1, 0)];
                    next && next.focus();
                }
            });

            // Reposition on scroll/resize
            window.addEventListener('scroll', () => { if (isOpen()) positionMenu(); }, { passive: true });
            window.addEventListener('resize', () => { if (isOpen()) positionMenu(); }, { passive: true });
        })();
        searchInput.addEventListener('input', () => {
            searchTerm = searchInput.value.trim().toLowerCase();
            calendar.removeAllEvents();
            calendar.addEventSource(deskEvents.filter(ev => {
                const p = ev.extendedProps || {};
                return !searchTerm || [ev.title, p.customer, p.hall, p.event_type, p.payment_status_label]
                    .filter(Boolean).join(' ').toLowerCase().includes(searchTerm);
            }));
        });

        document.getElementById('printSchedule').addEventListener('click', () => window.print());
        document.getElementById('exportSchedule').addEventListener('click', () => {
            const rows = calendar.getEvents().filter(matches).map(ev => {
                const p = ev.extendedProps;
                return [p.date, p.start_time, p.end_time, p.customer, p.hall, p.event_type,
                    p.people, (p.meals||[]).join(' + '), p.payment_status_label, p.amount, p.balance, p.url];
            });
            const headers = ['Date','Start','End','Customer','Hall','Event','Guests','Meals','Payment','Amount','Balance','URL'];
            const csv = [headers, ...rows].map(r => r.map(v => `"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url; a.download = 'akshathay-booking-schedule.csv';
            document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        });

        document.addEventListener('click', ev => {
            if (!preview.contains(ev.target) && !ev.target.closest('.fc-event')) hidePv(true);
        });
    }
});
</script>
@endpush

{{-- ══ SHARE BRIEF MODAL ═══════════════════════════════════════════ --}}
<style>
#shareBriefModal .modal-content {
    border-radius: 18px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    overflow: hidden;
}
#shareBriefModal .modal-header {
    background: linear-gradient(135deg, #1a1208 0%, #2c1810 100%);
    border-bottom: none;
    padding: 20px 24px 16px;
}
#shareBriefModal .modal-title {
    color: #fef9f0;
    font-size: .95rem;
    font-weight: 800;
}
#shareBriefModal .btn-close { filter: invert(1) opacity(.6); }
#shareBriefModal .modal-body { padding: 20px 24px; }
#shareBriefModal .modal-footer { border-top: 1px solid #f1f5f9; padding: 14px 24px; gap: 8px; }

.share-live-badge {
    background: rgba(220,38,38,.15);
    border: 1px solid rgba(220,38,38,.3);
    border-radius: 8px;
    color: #dc2626;
    display: inline-flex;
    align-items: center;
    font-size: .75rem;
    font-weight: 760;
    gap: 5px;
    padding: 4px 10px;
    animation: share-pulse 1.6s ease-in-out infinite;
}
@keyframes share-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.3); }
    50%      { box-shadow: 0 0 0 4px rgba(220,38,38,0); }
}
.share-upcoming-badge {
    background: rgba(217,119,6,.1);
    border: 1px solid rgba(217,119,6,.25);
    border-radius: 8px;
    color: #d97706;
    display: inline-flex;
    align-items: center;
    font-size: .75rem;
    font-weight: 760;
    gap: 5px;
    padding: 4px 10px;
}

.share-toggle-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.share-toggle {
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    font-size: .78rem;
    font-weight: 700;
    gap: 6px;
    padding: 6px 12px;
    transition: all .14s;
    user-select: none;
}
.share-toggle input[type="checkbox"] { accent-color: #d97706; width: 14px; height: 14px; }
.share-toggle.checked { background: #fffbeb; border-color: #d97706; color: #92400e; }

.share-preview {
    background: #0d1117;
    border-radius: 12px;
    color: #e6edf3;
    font-family: 'Courier New', monospace;
    font-size: .76rem;
    line-height: 1.65;
    max-height: 320px;
    overflow-y: auto;
    padding: 16px;
    white-space: pre-wrap;
    word-break: break-word;
}
.share-preview::-webkit-scrollbar { width: 4px; }
.share-preview::-webkit-scrollbar-track { background: transparent; }
.share-preview::-webkit-scrollbar-thumb { background: #30363d; border-radius: 4px; }

.share-no-event {
    background: #fffbeb;
    border: 1px solid rgba(217,119,6,.2);
    border-radius: 10px;
    color: #92400e;
    font-size: .84rem;
    padding: 14px 16px;
    text-align: center;
}

#shareBriefModal .btn-wa {
    background: #25D366;
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: .84rem;
    font-weight: 700;
    padding: 9px 18px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: background .14s;
}
#shareBriefModal .btn-wa:hover { background: #1ebe5d; color: #fff; }
#shareBriefModal .btn-copy {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    color: #374151;
    font-size: .84rem;
    font-weight: 700;
    padding: 9px 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .14s;
}
#shareBriefModal .btn-copy:hover { background: #e2e8f0; }
#shareBriefModal .btn-kitchen {
    background: linear-gradient(135deg, #1a1208, #3d2314);
    border: none;
    border-radius: 10px;
    color: rgba(254,249,240,.85);
    font-size: .84rem;
    font-weight: 700;
    padding: 9px 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: opacity .14s;
}
#shareBriefModal .btn-kitchen:hover { opacity: .85; color: #fef9f0; }
</style>

<div class="modal fade" id="shareBriefModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-whatsapp me-2"></i>Share Event Brief</h5>
                    @if($shareData)
                        <div class="mt-2">
                            @if($shareData['is_live'])
                                <span class="share-live-badge"><i class="bi bi-record-circle-fill"></i> Live Now</span>
                            @else
                                <span class="share-upcoming-badge"><i class="bi bi-clock"></i> Upcoming</span>
                            @endif
                            <span style="color:rgba(254,249,240,.5);font-size:.78rem;margin-left:8px">
                                {{ $shareData['customer'] }} · {{ $shareData['time_start'] }}
                            </span>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($shareData)
                    {{-- Toggle options --}}
                    <div class="share-toggle-row">
                        <label class="share-toggle checked" id="toggle-phone-label">
                            <input type="checkbox" id="toggle-phone" checked onchange="rebuildMessage()">
                            <i class="bi bi-telephone" style="font-size:.75rem"></i> Customer Number
                        </label>
                        <label class="share-toggle checked" id="toggle-payment-label">
                            <input type="checkbox" id="toggle-payment" checked onchange="rebuildMessage()">
                            <i class="bi bi-wallet2" style="font-size:.75rem"></i> Payment Info
                        </label>
                        <label class="share-toggle checked" id="toggle-kitchen-label">
                            <input type="checkbox" id="toggle-kitchen" checked onchange="rebuildMessage()">
                            <i class="bi bi-cup-hot" style="font-size:.75rem"></i> Kitchen Summary
                        </label>
                    </div>

                    {{-- Message preview --}}
                    <div class="share-preview" id="sharePreview"></div>

                    {{-- Hidden data for JS --}}
                    <div id="shareDataEl" style="display:none"
                         data-share='@json($shareData)'></div>
                @else
                    <div class="share-no-event">
                        <i class="bi bi-calendar-x d-block fs-3 mb-2 opacity-40"></i>
                        No events scheduled for today.
                        <br><small>Add a booking to share an event brief.</small>
                    </div>
                @endif
            </div>
            @if($shareData)
            <div class="modal-footer">
                <a id="shareWaBtn" href="#" target="_blank" rel="noopener" class="btn-wa">
                    <i class="bi bi-whatsapp fs-6"></i> Send via WhatsApp
                </a>
                <button type="button" class="btn-copy" id="shareCopyBtn">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
                <a href="{{ route('hall.bookings.kitchen', ['date' => today()->toDateString()]) }}"
                   class="btn-kitchen ms-auto">
                    <i class="bi bi-cup-hot"></i> Kitchen
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const el = document.getElementById('shareDataEl');
    if (!el) return;

    const d = JSON.parse(el.dataset.share);

    function sep() { return '━━━━━━━━━━━━━━━'; }

    // Emoji as surrogate-pair \u escapes -- no 4-byte UTF-8 bytes in JS source
    const E = {
        live:     '\uD83D\uDD34',
        party:    '\uD83C\uDF89',
        hall:     '\uD83C\uDFDB\uFE0F',
        event:    '\uD83C\uDF8A',
        person:   '\uD83D\uDC64',
        phone:    '\uD83D\uDCDE',
        date:     '\uD83D\uDCC5',
        clock:    '\u23F0',
        guests:   '\uD83D\uDC65',
        meal:     '\uD83C\uDF7D\uFE0F',
        pin:      '\uD83D\uDCCD',
        wallet:   '\uD83D\uDCB0',
        kitchen:  '\uD83D\uDCCC',
        list:     '\uD83D\uDCCB',
    };

    function buildMessage() {
        const inclPhone   = document.getElementById('toggle-phone').checked;
        const inclPayment = document.getElementById('toggle-payment').checked;
        const inclKitchen = document.getElementById('toggle-kitchen').checked;

        let msg = '';

        // Header
        msg += d.is_live
            ? E.live   + ' *Function Currently Live*\n\n'
            : E.party  + ' *Upcoming Function Alert*\n\n';

        // Core details
        msg += E.hall    + ' Hall: '     + d.hall     + '\n';
        msg += E.event   + ' Event: '    + d.event    + '\n';
        msg += E.person  + ' Customer: ' + d.customer + '\n';
        if (inclPhone) msg += E.phone + ' Contact: ' + d.phone + '\n';

        msg += '\n' + E.date  + ' Date: ' + d.date + '\n';
        msg += E.clock + ' Time: ' + d.time_start + ' – ' + d.time_end + '\n';
        msg += '\n' + E.guests + ' Guests: ' + d.guests + '\n';
        if (d.meal_plan) msg += E.meal + ' Meal Plan: ' + d.meal_plan + '\n';
        msg += E.pin + ' Status: ' + d.status + '\n';

        // Payment
        if (inclPayment) {
            msg += '\n' + E.wallet + ' Payment:\n';
            msg += '₹' + d.paid + ' Paid\n';
            if (d.has_balance) msg += '₹' + d.balance + ' Pending\n';
        }

        // Kitchen
        if (inclKitchen && d.meals) {
            msg += '\n' + sep() + '\n';
            msg += E.kitchen + ' Kitchen:\n';
            msg += d.meals + ' – ' + d.guests + ' Covers\n';
        }

        // Other events today
        if (d.other_events && d.other_events.length > 0) {
            msg += '\n' + sep() + '\n';
            msg += E.list + ' Other Events Today:\n';
            d.other_events.forEach(ev => { msg += '• ' + ev + '\n'; });
        }

        msg += '\n' + sep() + '\n';
        msg += 'Shared from ExpenseFlow Hall Operations';

        return msg;
    }

    function rebuildMessage() {
        const msg = buildMessage();
        document.getElementById('sharePreview').textContent = msg;
        document.getElementById('shareWaBtn').href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(msg);

        // Update toggle label styles
        ['phone','payment','kitchen'].forEach(key => {
            const cb    = document.getElementById('toggle-' + key);
            const label = document.getElementById('toggle-' + key + '-label');
            if (label) label.classList.toggle('checked', cb.checked);
        });
    }

    // Build on modal open
    document.getElementById('shareBriefModal').addEventListener('show.bs.modal', rebuildMessage);

    // Copy button
    const copyBtn = document.getElementById('shareCopyBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const msg = buildMessage();
            navigator.clipboard.writeText(msg).then(() => {
                this.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = msg;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
                this.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
            });
        });
    }

    // Expose for checkbox onchange
    window.rebuildMessage = rebuildMessage;
})();
</script>
@endpush

</x-admin-layout>
