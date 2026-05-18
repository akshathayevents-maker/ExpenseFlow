<x-admin-layout title="Employees">

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Employees — Premium Hospitality Workforce Operations
   Namespace: ef-emp-*
   ═══════════════════════════════════════════════════════ */

/* ── Design Tokens ────────────────────────────────────── */
.ef-emp-shell {
    --emp-olive:      #182414;
    --emp-olive-d:    #10180d;
    --emp-olive-m:    #0e1a0c;
    --emp-gold:       #8a6c30;
    --emp-gold-hi:    #b89040;
    --emp-gold-soft:  #d4b06a;
    --emp-cream:      #fdfaf5;
    --emp-border:     rgba(100,82,42,.11);
    --emp-border-s:   rgba(100,82,42,.24);
    --emp-ink-l:      rgba(245,240,232,.92);
    --emp-sub-l:      rgba(245,240,232,.55);
    --emp-mgr-color:  #607080;
    --emp-adm-color:  #8d4a3c;
    max-width: 1480px;
    margin: 0 auto;
    padding-bottom: 88px;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-emp-hero {
    align-items: stretch;
    background: linear-gradient(135deg, rgba(255,253,250,.98), rgba(249,247,242,.94));
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
    margin-bottom: 16px;
    overflow: hidden;
}

.ef-emp-hero-main { padding: 30px 34px; }

.ef-emp-hero-side {
    background: rgba(20,20,18,.022);
    border-left: 1px solid var(--ef-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 30px 34px;
}

.ef-emp-title {
    color: var(--ef-ink);
    font-size: clamp(2.2rem, 4vw, 3.5rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: .96;
    margin: 6px 0 14px;
}

.ef-emp-subtitle {
    color: var(--ef-muted);
    display: flex;
    flex-wrap: wrap;
    font-size: .9rem;
    gap: 4px 14px;
    margin: 0;
}

.ef-emp-subtitle i { font-size: .74rem; opacity: .5; }

.ef-emp-hero-stat { margin-bottom: 20px; }

.ef-emp-hero-stat-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-emp-hero-stat-value {
    color: var(--ef-ink);
    font-size: 2.5rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1.05;
    margin-top: 4px;
}

.ef-emp-hero-stat-note {
    color: var(--ef-muted);
    font-size: .76rem;
    margin-top: 4px;
}

/* Hero action buttons */
.ef-emp-hero-acts {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: flex-end;
}

.ef-emp-hbtn {
    align-items: center;
    border-radius: 10px;
    border: 1px solid rgba(20,20,18,.15);
    color: var(--ef-ink-2);
    background: rgba(255,253,250,.9);
    cursor: pointer;
    display: inline-flex;
    font-size: .84rem;
    font-weight: 650;
    gap: 6px;
    padding: 7px 13px;
    text-decoration: none;
    transition: background .15s var(--ef-ease), border-color .15s var(--ef-ease),
                box-shadow .15s var(--ef-ease), transform .12s var(--ef-ease);
}

.ef-emp-hbtn:hover {
    background: rgba(255,253,250,1);
    border-color: rgba(20,20,18,.28);
    box-shadow: 0 2px 8px rgba(20,20,18,.1);
    color: var(--ef-ink);
    transform: translateY(-1px);
}

.ef-emp-hbtn.--dark {
    background: linear-gradient(135deg, #232220, #1a1a18);
    border-color: rgba(20,20,18,.8);
    box-shadow: 0 2px 8px rgba(20,20,18,.22);
    color: #fffdfa;
}

.ef-emp-hbtn.--dark:hover {
    background: linear-gradient(135deg, #2e2c2a, #242220);
    box-shadow: 0 4px 14px rgba(20,20,18,.32);
    color: #fffdfa;
}

/* Mobile inline stat — hidden on desktop */
.ef-emp-hero-mstat { display: none; }

/* ── KPI Strip ────────────────────────────────────────── */
.ef-emp-kpi-wrap {
    margin-bottom: 14px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.ef-emp-kpi-wrap::-webkit-scrollbar { display: none; }

.ef-emp-stats {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    min-width: 0;
}

.ef-emp-stat {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    padding: 16px 18px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
}

.ef-emp-stat:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
    transform: translateY(-1px);
}

.ef-emp-stat-icon {
    color: var(--ef-faint);
    font-size: .82rem;
    margin-bottom: 9px;
}

.ef-emp-stat-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.ef-emp-stat-value {
    color: var(--ef-ink);
    font-size: 1.32rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 8px;
}

.ef-emp-stat-note {
    color: var(--ef-muted);
    font-size: .7rem;
    line-height: 1.4;
    margin-top: 5px;
}

.ef-emp-stat.--managers  .ef-emp-stat-icon,
.ef-emp-stat.--managers  .ef-emp-stat-value { color: var(--emp-mgr-color); }
.ef-emp-stat.--active    .ef-emp-stat-icon,
.ef-emp-stat.--active    .ef-emp-stat-value { color: var(--ef-emerald); }
.ef-emp-stat.--inactive  .ef-emp-stat-value { color: var(--ef-muted); }
.ef-emp-stat.--recent    .ef-emp-stat-icon,
.ef-emp-stat.--recent    .ef-emp-stat-value { color: var(--emp-gold); }

/* ── Search + Filter Toolbar ──────────────────────────── */
.ef-emp-toolbar {
    background: rgba(255,253,250,.95);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 14px;
    padding: 13px 20px;
}

.ef-emp-toolbar-inner {
    align-items: flex-end;
    display: flex;
    flex-wrap: wrap;
    gap: 9px 12px;
}

.ef-emp-search-wrap {
    flex: 1;
    min-width: 240px;
    position: relative;
}

.ef-emp-search-icon {
    color: var(--ef-faint);
    font-size: .88rem;
    left: 12px;
    pointer-events: none;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}

.ef-emp-search-input {
    background: rgba(251,250,247,.96);
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink);
    font-size: .88rem;
    height: 40px;
    padding: 0 12px 0 34px;
    transition: background .16s, border-color .16s, box-shadow .16s;
    width: 100%;
}

.ef-emp-search-input:focus {
    background: #fff;
    border-color: rgba(20,20,18,.44);
    box-shadow: 0 0 0 3px rgba(20,20,18,.05);
    outline: 0;
}

.ef-emp-search-input::placeholder { color: var(--ef-faint); }

.ef-emp-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ef-emp-filter-label {
    color: var(--ef-faint);
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-emp-filter-select {
    background: rgba(251,250,247,.96);
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink-2);
    font-size: .83rem;
    font-weight: 520;
    height: 36px;
    padding: 0 10px;
    transition: border-color .16s, box-shadow .16s;
}

.ef-emp-filter-select:focus {
    border-color: rgba(20,20,18,.44);
    box-shadow: 0 0 0 3px rgba(20,20,18,.05);
    outline: 0;
}

.ef-emp-toolbar-actions {
    align-items: flex-end;
    display: flex;
    gap: 7px;
}

.ef-emp-toolbar-sep {
    align-self: flex-end;
    background: var(--ef-border);
    flex-shrink: 0;
    height: 28px;
    margin-bottom: 4px;
    width: 1px;
}

.ef-emp-active-chip {
    align-items: center;
    background: rgba(96,112,128,.08);
    border: 1px solid rgba(96,112,128,.18);
    border-radius: 999px;
    color: var(--ef-bluegray);
    display: flex;
    font-size: .62rem;
    font-weight: 760;
    gap: 5px;
    letter-spacing: .06em;
    padding: 4px 10px;
    text-transform: uppercase;
    align-self: flex-end;
}

/* ── Employee List ────────────────────────────────────── */
.ef-emp-list-wrap {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

.ef-emp-list-head {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.065);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 12px 22px;
}

.ef-emp-list-title {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-emp-list-count {
    color: var(--ef-muted);
    font-size: .74rem;
}

/* ── Desktop Employee Row ─────────────────────────────── */
.ef-emp-row {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.055);
    display: grid;
    gap: 0 14px;
    grid-template-columns: 48px minmax(0, 1fr) 190px auto auto;
    padding: 14px 22px;
    transition: background .14s var(--ef-ease);
}

.ef-emp-row:last-child { border-bottom: 0; }
.ef-emp-row:hover { background: rgba(20,20,18,.015); }

/* Avatar */
.ef-emp-avatar {
    align-items: center;
    border-radius: 12px;
    color: rgba(255,253,250,.94);
    display: flex;
    flex-shrink: 0;
    font-size: .76rem;
    font-weight: 780;
    height: 44px;
    justify-content: center;
    letter-spacing: .02em;
    transition: transform .14s var(--ef-ease);
    width: 44px;
}

.ef-emp-row:hover .ef-emp-avatar { transform: scale(1.04); }

.ef-emp-avatar[data-role="manager"] {
    background: linear-gradient(135deg, #607080, #4a5f70);
    box-shadow: 0 4px 10px rgba(96,112,128,.28);
}

.ef-emp-avatar[data-role="employee"] {
    background: linear-gradient(135deg, #3d5c3a, #2a4228);
    box-shadow: 0 4px 10px rgba(42,66,40,.28);
}

.ef-emp-avatar[data-role="admin"] {
    background: linear-gradient(135deg, #8d4a3c, #6e3a2f);
    box-shadow: 0 4px 10px rgba(141,74,60,.28);
}

/* Identity */
.ef-emp-identity { min-width: 0; }

.ef-emp-name {
    color: var(--ef-ink);
    font-size: .94rem;
    font-weight: 720;
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-emp-email {
    color: var(--ef-muted);
    font-size: .76rem;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Contact */
.ef-emp-contact {
    align-items: center;
    display: flex;
    gap: 5px;
}

.ef-emp-phone-text {
    color: var(--ef-muted);
    font-size: .78rem;
    font-variant-numeric: tabular-nums;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-emp-contact-btn {
    align-items: center;
    background: rgba(20,20,18,.04);
    border: 1px solid rgba(20,20,18,.08);
    border-radius: 8px;
    color: var(--ef-muted);
    display: inline-flex;
    font-size: .8rem;
    height: 28px;
    justify-content: center;
    text-decoration: none;
    transition: background .14s, border-color .14s, color .14s;
    width: 28px;
    flex-shrink: 0;
}

.ef-emp-contact-btn:hover {
    background: rgba(20,20,18,.08);
    border-color: rgba(20,20,18,.16);
    color: var(--ef-ink);
}

.ef-emp-contact-btn.--wa:hover {
    background: rgba(37,211,102,.1);
    border-color: rgba(37,211,102,.2);
    color: #25d366;
}

.ef-emp-no-contact {
    color: var(--ef-faint);
    font-size: .74rem;
}

/* Chips column */
.ef-emp-chips {
    align-items: flex-start;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* Actions column */
.ef-emp-row-actions {
    align-items: center;
    display: flex;
    gap: 4px;
}

/* ── Mobile Employee Card ─────────────────────────────── */
.ef-emp-mcard {
    border-bottom: 1px solid rgba(20,20,18,.055);
    border-left: 3px solid transparent;
    display: none;
    padding: 10px 14px 10px 11px;
    transition: background .14s var(--ef-ease);
    position: relative;
}

.ef-emp-mcard:last-child { border-bottom: 0; }
.ef-emp-mcard:hover { background: rgba(20,20,18,.015); }

/* Role accent */
.ef-emp-mcard[data-role="manager"]  { border-left-color: #607080; }
.ef-emp-mcard[data-role="employee"] { border-left-color: #3d5c3a; }
.ef-emp-mcard[data-role="admin"]    { border-left-color: #8d4a3c; }

.ef-emp-mc-inner {
    display: grid;
    grid-template-areas:
        "av  top  top"
        "av  mail mail"
        "av  foot foot";
    grid-template-columns: 36px 1fr auto;
    grid-template-rows: auto auto auto;
    gap: 1px 10px;
    align-items: start;
}

.ef-emp-mc-av {
    grid-area: av;
    align-self: center;
    flex-shrink: 0;
}

.ef-emp-mc-av .ef-emp-avatar {
    height: 36px;
    width: 36px;
    border-radius: 10px;
    font-size: .68rem;
}

.ef-emp-mc-top {
    grid-area: top;
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}

.ef-emp-mc-name {
    color: var(--ef-ink);
    font-size: .9rem;
    font-weight: 720;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    min-width: 0;
}

.ef-emp-mc-chips {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.ef-emp-mc-mail {
    grid-area: mail;
    color: var(--ef-muted);
    font-size: .73rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-top: 2px;
}

.ef-emp-mc-foot {
    grid-area: foot;
    display: flex;
    align-items: center;
    gap: 5px;
    padding-top: 6px;
}

.ef-emp-mc-phone {
    color: var(--ef-muted);
    font-size: .72rem;
    font-variant-numeric: tabular-nums;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-emp-mc-acts {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

/* ── Pagination ───────────────────────────────────────── */
.ef-emp-pagination {
    display: flex;
    justify-content: center;
    margin-top: 14px;
}

.ef-emp-pagination .pagination { gap: 4px; margin: 0; }

.ef-emp-pagination .page-link {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: 10px !important;
    color: var(--ef-ink-2);
    font-size: .78rem;
    font-weight: 650;
    height: 34px;
    line-height: 34px;
    min-width: 34px;
    padding: 0 9px;
    text-align: center;
    transition: background .15s, border-color .15s;
}

.ef-emp-pagination .page-link:hover {
    background: var(--ef-surface-2);
    border-color: var(--ef-border-strong);
    color: var(--ef-ink);
}

.ef-emp-pagination .active .page-link {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
}

.ef-emp-pagination .disabled .page-link { opacity: .34; }

/* ── Delete Modal ─────────────────────────────────────── */
.ef-emp-modal .modal-content {
    background: #fffdfa;
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: 0 28px 80px rgba(24,22,18,.2);
}

.ef-emp-modal .modal-header,
.ef-emp-modal .modal-footer {
    border-color: var(--ef-border);
    padding: 18px 22px;
}

.ef-emp-modal .modal-body { padding: 22px; }

/* ── FAB (mobile add button) ──────────────────────────── */
.ef-emp-fab {
    align-items: center;
    background: linear-gradient(135deg, #182414 0%, #2a3d25 100%);
    border: none;
    border-radius: 50%;
    bottom: 20px;
    box-shadow: 0 6px 20px rgba(10,20,8,.38), 0 2px 6px rgba(10,20,8,.22);
    color: rgba(245,240,232,.95);
    cursor: pointer;
    display: none;
    font-size: 1.3rem;
    height: 52px;
    justify-content: center;
    position: fixed;
    right: 18px;
    text-decoration: none;
    transition: box-shadow .18s var(--ef-ease), transform .15s var(--ef-ease);
    width: 52px;
    z-index: 1050;
}

.ef-emp-fab:hover {
    box-shadow: 0 8px 26px rgba(10,20,8,.46), 0 2px 8px rgba(10,20,8,.26);
    color: #fffdfa;
    transform: scale(1.07);
}

.ef-emp-fab:active { transform: scale(.95); }

/* ── Mobile bar (hidden — replaced by FAB) ────────────── */
.ef-emp-mobile-bar { display: none !important; }

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-emp-hero { grid-template-columns: 1fr; }
    .ef-emp-hero-side {
        border-left: 0;
        border-top: 1px solid var(--ef-border);
        padding: 22px 30px;
    }
    .ef-emp-hero-acts { justify-content: flex-start; }
    .ef-emp-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ef-emp-hero-stat-value { font-size: 2rem; }
}

@media (max-width: 991.98px) {
    .ef-emp-row {
        grid-template-columns: 44px minmax(0, 1fr) auto auto;
    }
    .ef-emp-contact { display: none; }
}

@media (max-width: 767.98px) {
    /* ── Shell ── */
    .ef-emp-shell { padding-bottom: 84px; }

    /* ── Hero mobile ── */
    .ef-emp-hero {
        background: linear-gradient(155deg, var(--emp-olive-d) 0%, var(--emp-olive) 55%, var(--emp-olive-m) 100%);
        border-color: rgba(255,255,255,.06);
        border-radius: 14px;
        grid-template-columns: 1fr;
        margin-bottom: 10px;
    }

    .ef-emp-hero-main { padding: 16px 18px 14px; }

    .ef-emp-hero-main .ef-eyebrow {
        color: rgba(245,240,232,.45);
        font-size: .6rem;
        letter-spacing: .14em;
    }

    .ef-emp-title {
        color: var(--emp-ink-l, rgba(245,240,232,.95));
        font-size: 1.45rem;
        margin: 4px 0 8px;
    }

    .ef-emp-subtitle {
        color: rgba(245,240,232,.52);
        font-size: .76rem;
        gap: 3px 10px;
    }

    .ef-emp-hero-side { display: none; }

    /* Inline stat row on mobile */
    .ef-emp-hero-mstat {
        display: flex;
        align-items: baseline;
        gap: 7px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(255,255,255,.08);
    }

    .ef-emp-hero-mstat-val {
        color: rgba(245,240,232,.95);
        font-size: 1.55rem;
        font-weight: 780;
        font-variant-numeric: tabular-nums;
        line-height: 1;
    }

    .ef-emp-hero-mstat-note {
        color: rgba(245,240,232,.45);
        font-size: .74rem;
    }

    /* Hero buttons on mobile — icon-only for secondary */
    .ef-emp-hero-acts {
        margin-top: 12px;
        justify-content: flex-start;
    }

    .ef-emp-hbtn span { display: none; }

    .ef-emp-hbtn {
        background: rgba(255,255,255,.1);
        border-color: rgba(255,255,255,.15);
        color: rgba(245,240,232,.9);
        padding: 7px 10px;
    }

    .ef-emp-hbtn:hover {
        background: rgba(255,255,255,.18);
        border-color: rgba(255,255,255,.24);
        color: #fffdfa;
    }

    .ef-emp-hbtn.--dark {
        background: rgba(255,255,255,.15);
        border-color: rgba(255,255,255,.2);
        box-shadow: none;
        color: rgba(245,240,232,.95);
    }

    .ef-emp-hbtn.--dark:hover {
        background: rgba(255,255,255,.22);
        color: #fffdfa;
    }

    /* ── KPI horizontal scroll ── */
    .ef-emp-kpi-wrap {
        margin-bottom: 10px;
        padding-bottom: 2px;
    }

    .ef-emp-stats {
        grid-template-columns: repeat(5, minmax(100px, 1fr));
        gap: 8px;
        min-width: 540px;
    }

    .ef-emp-stat {
        border-radius: 10px;
        padding: 10px 12px;
    }

    .ef-emp-stat-icon  { font-size: .76rem; margin-bottom: 7px; }
    .ef-emp-stat-label { font-size: .55rem; }
    .ef-emp-stat-value { font-size: 1.15rem; margin-top: 6px; }
    .ef-emp-stat-note  { font-size: .65rem; margin-top: 4px; }

    /* ── Toolbar sticky + compact ── */
    .ef-emp-toolbar {
        border-radius: 12px;
        margin-bottom: 10px;
        padding: 10px 13px;
        position: sticky;
        top: 8px;
        z-index: 30;
        backdrop-filter: blur(16px) saturate(180%);
        background: rgba(253,250,245,.96);
    }

    .ef-emp-toolbar-inner { gap: 8px; flex-wrap: wrap; }
    .ef-emp-search-wrap { min-width: 100%; order: -1; }
    .ef-emp-search-input { height: 38px; font-size: .85rem; }
    .ef-emp-toolbar-sep { display: none; }

    .ef-emp-filter-group {
        flex-direction: row;
        align-items: center;
        gap: 6px;
    }

    .ef-emp-filter-label { white-space: nowrap; }

    .ef-emp-filter-select {
        font-size: .8rem;
        height: 32px;
        padding: 0 8px;
    }

    .ef-emp-toolbar-actions { align-items: center; }

    /* ── List head ── */
    .ef-emp-list-head { padding: 9px 14px; }

    /* ── Show mobile cards, hide desktop rows ── */
    .ef-emp-row  { display: none; }
    .ef-emp-mcard { display: block; }

    /* ── FAB ── */
    .ef-emp-fab { display: flex; }
}

@media (max-width: 479.98px) {
    .ef-emp-stats { min-width: 500px; }
}

@media print {
    .ef-emp-toolbar,
    .ef-emp-hero-acts,
    .ef-emp-fab,
    .ef-emp-row-actions,
    .ef-emp-mc-acts { display: none !important; }
}
</style>
@endpush

@php
$hasFilters = $search || $role || $status;

$roleTones = [
    'admin'    => 'danger',
    'manager'  => 'bluegray',
    'employee' => 'neutral',
];
@endphp

<div class="ef-emp-shell">

    {{-- ═══ HERO ════════════════════════════════════════════════════════════ --}}
    <header class="ef-emp-hero">

        <div class="ef-emp-hero-main">
            <p class="ef-eyebrow">Hospitality Workforce Operations</p>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap">
                <h1 class="ef-emp-title">Employees</h1>
                <div class="ef-emp-hero-acts">
                    <a href="{{ route('admin.employees.create') }}" class="ef-emp-hbtn --dark">
                        <i class="bi bi-person-plus"></i>
                        <span>Add Employee</span>
                    </a>
                    <button class="ef-emp-hbtn" onclick="window.print()" title="Print Directory">
                        <i class="bi bi-printer"></i>
                    </button>
                </div>
            </div>
            <p class="ef-emp-subtitle">
                <span><i class="bi bi-calendar3"></i> {{ now()->format('l, d F Y') }}</span>
                <span><i class="bi bi-people"></i> Workforce and access management</span>
            </p>
            {{-- Mobile inline stat (hidden on desktop) --}}
            <div class="ef-emp-hero-mstat">
                <span class="ef-emp-hero-mstat-val">{{ number_format($stats['total']) }}</span>
                <span class="ef-emp-hero-mstat-note">total &middot; {{ $stats['active'] }} active &middot; {{ $stats['managers'] }} managers</span>
            </div>
        </div>

        <div class="ef-emp-hero-side">
            <div class="ef-emp-hero-stat">
                <div class="ef-emp-hero-stat-label">Total Workforce</div>
                <div class="ef-emp-hero-stat-value">{{ number_format($stats['total']) }}</div>
                <div class="ef-emp-hero-stat-note">{{ $stats['active'] }} active &middot; {{ $stats['managers'] }} managers</div>
            </div>

            <div class="ef-emp-hero-acts">
                <a href="{{ route('admin.employees.create') }}" class="ef-emp-hbtn --dark">
                    <i class="bi bi-person-plus"></i> Add Employee
                </a>
                <button class="ef-emp-hbtn" onclick="window.print()" title="Print Directory">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>

    </header>

    {{-- ═══ STATS STRIP ═══════════════════════════════════════════════════ --}}
    <div class="ef-emp-kpi-wrap">
        <div class="ef-emp-stats">

            <div class="ef-emp-stat">
                <div class="ef-emp-stat-icon"><i class="bi bi-people"></i></div>
                <div class="ef-emp-stat-label">Total Workforce</div>
                <div class="ef-emp-stat-value">{{ number_format($stats['total']) }}</div>
                <div class="ef-emp-stat-note">employees &amp; managers</div>
            </div>

            <div class="ef-emp-stat --managers">
                <div class="ef-emp-stat-icon"><i class="bi bi-person-badge"></i></div>
                <div class="ef-emp-stat-label">Managers</div>
                <div class="ef-emp-stat-value">{{ number_format($stats['managers']) }}</div>
                <div class="ef-emp-stat-note">operational leads</div>
            </div>

            <div class="ef-emp-stat --active">
                <div class="ef-emp-stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="ef-emp-stat-label">Active Staff</div>
                <div class="ef-emp-stat-value">{{ number_format($stats['active']) }}</div>
                <div class="ef-emp-stat-note">with system access</div>
            </div>

            <div class="ef-emp-stat --inactive">
                <div class="ef-emp-stat-icon"><i class="bi bi-pause-circle"></i></div>
                <div class="ef-emp-stat-label">Inactive</div>
                <div class="ef-emp-stat-value">{{ number_format($stats['inactive']) }}</div>
                <div class="ef-emp-stat-note">access suspended</div>
            </div>

            <div class="ef-emp-stat --recent">
                <div class="ef-emp-stat-icon"><i class="bi bi-person-check"></i></div>
                <div class="ef-emp-stat-label">Recent Joins</div>
                <div class="ef-emp-stat-value">{{ number_format($stats['recent']) }}</div>
                <div class="ef-emp-stat-note">last 30 days</div>
            </div>

        </div>
    </div>

    {{-- ═══ SEARCH + FILTER TOOLBAR ════════════════════════════════════════ --}}
    <div class="ef-emp-toolbar">
        <form method="GET" action="{{ route('admin.employees.index') }}"
              class="ef-emp-toolbar-inner" id="empFilterForm">

            <div class="ef-emp-search-wrap">
                <i class="bi bi-search ef-emp-search-icon"></i>
                <input type="text" name="search"
                       class="ef-emp-search-input"
                       placeholder="Search by name, email or phone…"
                       value="{{ $search }}">
            </div>

            <div class="ef-emp-toolbar-sep"></div>

            <div class="ef-emp-filter-group">
                <label class="ef-emp-filter-label">Role</label>
                <select name="role" class="ef-emp-filter-select">
                    <option value="">All roles</option>
                    <option value="manager"  {{ $role === 'manager'  ? 'selected' : '' }}>Manager</option>
                    <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
            </div>

            <div class="ef-emp-filter-group">
                <label class="ef-emp-filter-label">Status</label>
                <select name="status" class="ef-emp-filter-select">
                    <option value="">All statuses</option>
                    <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="ef-emp-toolbar-actions">
                @if($hasFilters)
                    <span class="ef-emp-active-chip">
                        <i class="bi bi-funnel-fill"></i> Filtered
                    </span>
                    <a href="{{ route('admin.employees.index') }}" class="ef-btn">
                        <i class="bi bi-x"></i> Reset
                    </a>
                @endif
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>

        </form>
    </div>

    {{-- ═══ EMPLOYEE LIST ══════════════════════════════════════════════════ --}}
    <div class="ef-emp-list-wrap">

        <div class="ef-emp-list-head">
            <span class="ef-emp-list-title">Workforce Directory</span>
            <span class="ef-emp-list-count">
                {{ $employees->total() }} member{{ $employees->total() != 1 ? 's' : '' }}
                @if($employees->total() > 0)
                    &middot; {{ $employees->firstItem() }}–{{ $employees->lastItem() }} shown
                @endif
            </span>
        </div>

        @forelse($employees as $employee)
        @php
            $nameParts = explode(' ', trim($employee->name));
            $initials  = strtoupper(
                substr($nameParts[0], 0, 1) .
                (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
            );
            $tone    = $roleTones[$employee->role] ?? 'neutral';
            $waPhone = preg_replace('/\D/', '', $employee->phone ?? '');
        @endphp

        {{-- ── Desktop Row (hidden on mobile) ── --}}
        <div class="ef-emp-row">

            <div class="ef-emp-avatar" data-role="{{ $employee->role }}">{{ $initials }}</div>

            <div class="ef-emp-identity">
                <div class="ef-emp-name">{{ $employee->name }}</div>
                <div class="ef-emp-email">{{ $employee->email }}</div>
            </div>

            <div class="ef-emp-contact">
                @if($employee->phone)
                    <span class="ef-emp-phone-text">{{ $employee->phone }}</span>
                    <a href="tel:{{ $employee->phone }}"
                       class="ef-emp-contact-btn"
                       title="Call {{ $employee->name }}"
                       onclick="event.stopPropagation()">
                        <i class="bi bi-telephone"></i>
                    </a>
                    @if($waPhone)
                    <a href="https://wa.me/{{ $waPhone }}"
                       class="ef-emp-contact-btn --wa"
                       target="_blank"
                       title="WhatsApp {{ $employee->name }}"
                       onclick="event.stopPropagation()">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    @endif
                @else
                    <span class="ef-emp-no-contact">No phone</span>
                @endif
            </div>

            <div class="ef-emp-chips">
                <x-premium.chip :tone="$tone">{{ ucfirst($employee->role) }}</x-premium.chip>
                <x-premium.chip :tone="$employee->is_active ? 'emerald' : 'neutral'">
                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                </x-premium.chip>
            </div>

            <div class="ef-emp-row-actions">
                <a href="{{ route('admin.employees.edit', $employee) }}"
                   class="ef-btn ef-btn-icon" title="Edit {{ $employee->name }}">
                    <i class="bi bi-pencil"></i>
                </a>

                <div class="dropdown">
                    <button class="ef-btn ef-btn-icon"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                        style="border-color:var(--ef-border);border-radius:12px;min-width:172px">
                        @if($employee->phone)
                        <li>
                            <a class="dropdown-item" href="tel:{{ $employee->phone }}" style="font-size:.84rem">
                                <i class="bi bi-telephone me-2 opacity-55"></i> Call
                            </a>
                        </li>
                        @if($waPhone)
                        <li>
                            <a class="dropdown-item" href="https://wa.me/{{ $waPhone }}"
                               target="_blank" style="font-size:.84rem">
                                <i class="bi bi-whatsapp me-2 opacity-55"></i> WhatsApp
                            </a>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form method="POST"
                                  action="{{ route('admin.employees.toggle-status', $employee) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="dropdown-item" style="font-size:.84rem">
                                    <i class="bi bi-{{ $employee->is_active ? 'pause-circle' : 'play-circle' }} me-2 opacity-55"></i>
                                    {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </li>
                        @if(auth()->id() !== $employee->id)
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item" style="color:var(--ef-danger)"
                                    style="font-size:.84rem"
                                    data-bs-toggle="modal"
                                    data-bs-target="#delModal{{ $employee->id }}">
                                <i class="bi bi-trash me-2 opacity-65"></i> Delete
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        {{-- ── Mobile Card (shown only on mobile) ── --}}
        <div class="ef-emp-mcard" data-role="{{ $employee->role }}">
            <div class="ef-emp-mc-inner">

                {{-- Avatar --}}
                <div class="ef-emp-mc-av">
                    <div class="ef-emp-avatar" data-role="{{ $employee->role }}">{{ $initials }}</div>
                </div>

                {{-- Name + chips --}}
                <div class="ef-emp-mc-top">
                    <span class="ef-emp-mc-name">{{ $employee->name }}</span>
                    <div class="ef-emp-mc-chips">
                        <x-premium.chip :tone="$tone" size="xs">{{ ucfirst($employee->role) }}</x-premium.chip>
                        <x-premium.chip :tone="$employee->is_active ? 'emerald' : 'neutral'" size="xs">
                            {{ $employee->is_active ? 'Active' : 'Off' }}
                        </x-premium.chip>
                    </div>
                </div>

                {{-- Email --}}
                <div class="ef-emp-mc-mail">{{ $employee->email }}</div>

                {{-- Footer: phone + actions --}}
                <div class="ef-emp-mc-foot">
                    <span class="ef-emp-mc-phone">
                        @if($employee->phone)
                            <i class="bi bi-telephone" style="font-size:.65rem;opacity:.5;margin-right:3px"></i>{{ $employee->phone }}
                        @else
                            <span style="color:var(--ef-faint);font-size:.7rem">No phone</span>
                        @endif
                    </span>

                    <div class="ef-emp-mc-acts">
                        @if($employee->phone)
                        <a href="tel:{{ $employee->phone }}"
                           class="ef-emp-contact-btn" title="Call"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-telephone"></i>
                        </a>
                        @if($waPhone)
                        <a href="https://wa.me/{{ $waPhone }}"
                           class="ef-emp-contact-btn --wa"
                           target="_blank" title="WhatsApp"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        @endif
                        @endif

                        <a href="{{ route('admin.employees.edit', $employee) }}"
                           class="ef-emp-contact-btn" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <div class="dropdown">
                            <button class="ef-emp-contact-btn"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="More">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                style="border-color:var(--ef-border);border-radius:12px;min-width:160px">
                                <li>
                                    <form method="POST"
                                          action="{{ route('admin.employees.toggle-status', $employee) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="dropdown-item" style="font-size:.82rem">
                                            <i class="bi bi-{{ $employee->is_active ? 'pause-circle' : 'play-circle' }} me-2 opacity-55"></i>
                                            {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </li>
                                @if(auth()->id() !== $employee->id)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item" style="color:var(--ef-danger)"
                                            style="font-size:.82rem"
                                            data-bs-toggle="modal"
                                            data-bs-target="#delModal{{ $employee->id }}">
                                        <i class="bi bi-trash me-2 opacity-65"></i> Delete
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @empty

        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-people"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">
                No employees found
            </h3>
            <p style="color:var(--ef-muted);font-size:.86rem;margin:0 0 20px;max-width:300px;line-height:1.6">
                @if($hasFilters)
                    No employees match your current filters. Try adjusting the search or role selection.
                @else
                    Employee records and workforce operations will appear here once staff are added.
                @endif
            </p>
            @if($hasFilters)
                <a href="{{ route('admin.employees.index') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            @else
                <a href="{{ route('admin.employees.create') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-person-plus"></i> Add Employee
                </a>
            @endif
        </div>

        @endforelse
    </div>

    {{-- Pagination --}}
    @if($employees->hasPages())
        <div class="ef-emp-pagination">{{ $employees->links() }}</div>
    @endif

</div>

{{-- ═══ FAB (mobile add button) ════════════════════════════════════════════ --}}
<a href="{{ route('admin.employees.create') }}"
   class="ef-emp-fab"
   title="Add Employee">
    <i class="bi bi-person-plus-fill"></i>
</a>

{{-- ═══ DELETE MODALS ══════════════════════════════════════════════════════ --}}
@foreach($employees as $employee)
@if(auth()->id() !== $employee->id)
<div class="modal fade ef-emp-modal" id="delModal{{ $employee->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title" style="color:var(--ef-ink);font-weight:760">
                    <i class="bi bi-person-x me-2" style="color:var(--ef-danger)"></i> Remove Employee
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="rounded-3 p-3 mb-3"
                     style="background:rgba(141,74,60,.06);border:1px solid rgba(141,74,60,.14)">
                    <p class="mb-0" style="color:var(--ef-danger);font-size:.8rem;font-weight:680">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This permanently removes the employee and all associated data.
                    </p>
                </div>

                <div style="display:flex;align-items:center;gap:12px">
                    <div class="ef-emp-avatar" data-role="{{ $employee->role }}"
                         style="width:36px;height:36px;border-radius:10px;font-size:.68rem;flex-shrink:0">
                        @php
                            $p = explode(' ', trim($employee->name));
                            echo strtoupper(substr($p[0],0,1).(isset($p[1])?substr($p[1],0,1):''));
                        @endphp
                    </div>
                    <div>
                        <div style="color:var(--ef-ink);font-size:.9rem;font-weight:720">{{ $employee->name }}</div>
                        <div style="color:var(--ef-muted);font-size:.76rem">{{ $employee->email }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 gap-2">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST"
                      action="{{ route('admin.employees.destroy', $employee) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn"
                            style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fff">
                        Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

</x-admin-layout>
