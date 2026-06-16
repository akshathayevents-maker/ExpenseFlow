<x-admin-layout title="Hall Bookings">
@push('styles')
<style>
/* ── Hall Bookings — ef-bk-* (v3 mobile-first) ─────────────────── */

:root {
    --bk-ink:      #131110;
    --bk-sub:      #50473f;
    --bk-muted:    #8a827a;
    --bk-faint:    #bab3aa;
    --bk-gold:     #8a6c30;
    --bk-gold-hi:  #b89040;
    --bk-gold-soft:#d4b06a;
    --bk-surface:  #fdfaf5;
    --bk-cream:    #f7f3ec;
    --bk-border:   rgba(100,82,42,.11);
    --bk-border-s: rgba(100,82,42,.22);
    --bk-shadow:   0 1px 3px rgba(18,14,8,.06), 0 3px 10px rgba(18,14,8,.04);
    --bk-shadow-h: 0 4px 18px rgba(18,14,8,.11), 0 1px 4px rgba(18,14,8,.06);
    --bk-r:        14px;
    --bk-ease:     cubic-bezier(.25,.46,.45,.94);
}

.ef-bk-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 100px;
}

/* ── Flash ─────────────────────────────────────────────────────── */
.ef-bk-flash {
    align-items: center;
    border-radius: 10px;
    display: flex;
    font-size: .83rem;
    gap: 9px;
    margin-bottom: 14px;
    padding: 11px 14px;
}
.ef-bk-flash.--success { background: rgba(15,120,80,.07); border: 1px solid rgba(15,120,80,.18); color: #0A5C40; }
.ef-bk-flash.--error   { background: rgba(180,60,50,.07); border: 1px solid rgba(180,60,50,.18); color: #8B2020; }

/* ══════════════════════════════════════════════════════════════════
   HERO — compact command bar with operational summary
══════════════════════════════════════════════════════════════════ */
.ef-bk-hero {
    background: linear-gradient(135deg, #10180d 0%, #182414 50%, #0e1a0c 100%);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
    overflow: hidden;
    padding: 16px 20px;
    position: relative;
}
.ef-bk-hero::before {
    background: radial-gradient(circle, rgba(180,145,60,.15) 0%, transparent 65%);
    border-radius: 50%;
    content: "";
    height: 240px;
    pointer-events: none;
    position: absolute;
    right: -40px;
    top: -90px;
    width: 240px;
}
.ef-bk-hero-left {
    position: relative;
    z-index: 1;
    min-width: 0;
    flex: 1;
}
.ef-bk-hero-title {
    color: #f8f5ee;
    font-size: 1.3rem;
    font-weight: 780;
    letter-spacing: -.02em;
    line-height: 1.1;
    margin-bottom: 8px;
}
/* Operational summary pills below title */
.ef-bk-hero-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.ef-bk-hero-pill {
    align-items: center;
    border-radius: 7px;
    display: inline-flex;
    font-size: .7rem;
    font-weight: 700;
    gap: 4px;
    padding: 3px 9px;
    white-space: nowrap;
}
.ef-bk-hero-pill.--neutral {
    background: rgba(255,255,255,.08);
    color: rgba(248,245,238,.65);
}
.ef-bk-hero-pill.--amber {
    background: rgba(180,145,60,.22);
    color: #e8c870;
}
.ef-bk-hero-pill.--red {
    background: rgba(200,75,68,.22);
    color: #f08080;
}
.ef-bk-hero-pill.--green {
    background: rgba(15,123,95,.22);
    color: #6adbaa;
}
.ef-bk-hero-pill i { font-size: .7rem; }
.ef-bk-hero-pill .ef-bk-pulse {
    animation: bk-pulse 1.8s ease-in-out infinite;
    background: currentColor;
    border-radius: 50%;
    display: inline-block;
    height: 5px;
    width: 5px;
}

.ef-bk-hero-actions {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 6px;
    position: relative;
    z-index: 1;
}
.ef-bk-hbtn {
    align-items: center;
    border-radius: 9px;
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .76rem;
    font-weight: 660;
    gap: 5px;
    padding: 7px 12px;
    text-decoration: none;
    transition: all .15s var(--bk-ease);
    white-space: nowrap;
    border: 1.5px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.07);
    color: rgba(248,245,238,.8);
}
.ef-bk-hbtn:hover { background: rgba(255,255,255,.14); color: #f8f5ee; border-color: rgba(255,255,255,.2); }
.ef-bk-hbtn.--gold {
    background: rgba(180,145,60,.25);
    border-color: rgba(180,145,60,.45);
    color: #e8c870;
}
.ef-bk-hbtn.--gold:hover { background: rgba(180,145,60,.38); color: #f5d882; }

/* ══════════════════════════════════════════════════════════════════
   KPI STRIP — horizontally scrollable on mobile
══════════════════════════════════════════════════════════════════ */
.ef-bk-kpi-scroll {
    overflow-x: auto;
    scrollbar-width: none;
    margin-bottom: 12px;
    -webkit-overflow-scrolling: touch;
}
.ef-bk-kpi-scroll::-webkit-scrollbar { display: none; }
.ef-bk-kpi-row {
    display: grid;
    grid-template-columns: repeat(5, minmax(104px, 1fr));
    gap: 8px;
    min-width: 560px;
}
.ef-bk-kpi {
    background: var(--bk-surface);
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-r);
    box-shadow: var(--bk-shadow);
    padding: 10px 12px;
}
.ef-bk-kpi-label {
    align-items: center;
    color: var(--bk-muted);
    display: flex;
    font-size: .6rem;
    font-weight: 720;
    gap: 4px;
    letter-spacing: .1em;
    margin-bottom: 5px;
    text-transform: uppercase;
}
.ef-bk-kpi-val {
    color: var(--bk-ink);
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.02em;
    font-variant-numeric: tabular-nums;
}
.ef-bk-kpi-val.--gold   { color: var(--bk-gold); }
.ef-bk-kpi-val.--red    { color: #8B2020; }
.ef-bk-kpi-val.--green  { color: #0A6640; }
.ef-bk-kpi-note {
    color: var(--bk-faint);
    font-size: .65rem;
    margin-top: 3px;
}
@keyframes bk-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(138,108,48,.5); }
    50%      { box-shadow: 0 0 0 4px rgba(138,108,48,0); }
}

/* ══════════════════════════════════════════════════════════════════
   FILTER BAR — sticky, glass
══════════════════════════════════════════════════════════════════ */
.ef-bk-filter {
    background: rgba(253,250,245,.97);
    -webkit-backdrop-filter: blur(12px);
    backdrop-filter: blur(12px);
    border: 1px solid var(--bk-border);
    border-radius: 12px;
    box-shadow: var(--bk-shadow);
    margin-bottom: 12px;
    padding: 10px 12px;
    position: sticky;
    top: 8px;
    z-index: 30;
}
.ef-bk-filter-row1 {
    align-items: center;
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}
.ef-bk-search-wrap { position: relative; flex: 1; min-width: 0; }
.ef-bk-search-ico {
    color: var(--bk-faint);
    font-size: .8rem;
    left: 10px;
    pointer-events: none;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}
.ef-bk-search {
    background: var(--bk-cream);
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-ink);
    font-family: inherit;
    font-size: .82rem;
    font-size: 16px; /* prevent iOS zoom */
    outline: none;
    padding: 8px 10px 8px 28px;
    transition: border-color .14s;
    width: 100%;
}
.ef-bk-search:focus { border-color: var(--bk-gold); background: #fff; }
.ef-bk-search::placeholder { color: var(--bk-faint); font-size: .82rem; }
.ef-bk-filter-btns { display: flex; gap: 6px; flex-shrink: 0; }
.ef-bk-filt-btn {
    align-items: center;
    background: var(--bk-cream);
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .76rem;
    font-weight: 640;
    gap: 5px;
    padding: 7px 11px;
    transition: all .14s;
    white-space: nowrap;
    min-height: 36px;
}
.ef-bk-filt-btn:hover { border-color: var(--bk-gold-hi); color: var(--bk-gold); }
.ef-bk-filt-btn.--active {
    background: rgba(138,108,48,.08);
    border-color: var(--bk-gold);
    color: var(--bk-gold);
}
.ef-bk-filter-dot {
    background: var(--bk-gold);
    border-radius: 50%;
    display: inline-block;
    height: 5px;
    width: 5px;
    flex-shrink: 0;
}

/* Quick chips */
.ef-bk-chips {
    display: flex;
    gap: 5px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 2px; /* prevent shadow clip */
}
.ef-bk-chips::-webkit-scrollbar { display: none; }
.ef-bk-chip {
    background: transparent;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 20px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-block;
    flex-shrink: 0;
    font-size: .72rem;
    font-weight: 660;
    padding: 4px 12px;
    text-decoration: none;
    transition: all .14s;
    white-space: nowrap;
}
.ef-bk-chip:hover { border-color: var(--bk-ink); color: var(--bk-ink); }
.ef-bk-chip.--active {
    background: var(--bk-ink);
    border-color: var(--bk-ink);
    color: var(--bk-surface);
}

/* Advanced drawer */
.ef-bk-adv { max-height: 0; overflow: hidden; transition: max-height .28s ease; }
.ef-bk-adv.--open { max-height: 400px; }
.ef-bk-adv-inner {
    align-items: end;
    border-top: 1px solid var(--bk-border);
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
    margin-top: 10px;
    padding-top: 10px;
}
.ef-bk-adv-label {
    color: var(--bk-muted);
    display: block;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .1em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-bk-adv-input,
.ef-bk-adv-select {
    background: #fff;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-ink);
    font-family: inherit;
    font-size: .82rem;
    outline: none;
    padding: 7px 10px;
    transition: border-color .14s;
    width: 100%;
}
.ef-bk-adv-input:focus,
.ef-bk-adv-select:focus { border-color: var(--bk-gold); }
.ef-bk-adv-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 12 12'%3E%3Cpath fill='%23aaa' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 26px;
}
.ef-bk-adv-row { display: flex; gap: 7px; align-items: center; }
.ef-bk-btn-apply {
    background: var(--bk-ink);
    border: none;
    border-radius: 8px;
    color: var(--bk-surface);
    cursor: pointer;
    font-family: inherit;
    font-size: .8rem;
    font-weight: 660;
    min-height: 36px;
    padding: 7px 16px;
    white-space: nowrap;
}
.ef-bk-btn-clear {
    background: transparent;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-muted);
    font-size: .8rem;
    font-weight: 640;
    padding: 7px 12px;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
}

/* Results meta */
.ef-bk-meta-bar {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}
.ef-bk-meta-bar span { color: var(--bk-muted); font-size: .76rem; }
.ef-bk-meta-bar strong { color: var(--bk-ink); }

/* ══════════════════════════════════════════════════════════════════
   BOOKING LIST
══════════════════════════════════════════════════════════════════ */
.ef-bk-list {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 20px;
}

/* ══════════════════════════════════════════════════════════════════
   BOOKING CARD — redesigned mobile hierarchy
   Priority: Customer Name > Amount > Status > Details
══════════════════════════════════════════════════════════════════ */
.ef-bk-card {
    background: var(--bk-surface);
    border: 1px solid var(--bk-border);
    border-left: 3px solid transparent;
    border-radius: var(--bk-r);
    box-shadow: var(--bk-shadow);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: box-shadow .16s var(--bk-ease);
}
.ef-bk-card:hover { box-shadow: var(--bk-shadow-h); }
.ef-bk-card.--confirmed { border-left-color: #0F7B5F; }
.ef-bk-card.--completed { border-left-color: #2F6FED; }
.ef-bk-card.--cancelled { border-left-color: #C84B44; opacity: .72; }
.ef-bk-card:has(.dropdown-menu.show) { z-index: 50; }

/* ── Dropdown polish ────────────────────────────────────────────── */
.ef-bk-card .dropdown-menu {
    border: 1px solid var(--bk-border-s);
    border-radius: 12px;
    box-shadow: 0 10px 32px rgba(18,14,8,.16), 0 2px 6px rgba(18,14,8,.06);
    font-size: .82rem;
    min-width: 185px;
    padding: 5px;
    z-index: 1000;
}
.ef-bk-card .dropdown-item {
    border-radius: 7px;
    color: var(--bk-sub);
    font-weight: 580;
    padding: 9px 12px;
    transition: background .1s, color .1s;
}
.ef-bk-card .dropdown-item:hover { background: var(--bk-cream); color: var(--bk-ink); }
.ef-bk-card .dropdown-item.--danger { color: #C84B44; }
.ef-bk-card .dropdown-item.--danger:hover { background: rgba(200,75,68,.07); color: #9B1C1C; }
.ef-bk-card .dropdown-divider { border-color: var(--bk-border); margin: 4px 0; }

/* ── Card top: avatar + name + event + status badge ────────────── */
.ef-bk-r1 {
    align-items: center;
    display: flex;
    gap: 10px;
    padding: 12px 14px 9px;
}
.ef-bk-av {
    align-items: center;
    border-radius: 9px;
    color: rgba(255,255,255,.92);
    display: flex;
    flex-shrink: 0;
    font-size: .82rem;
    font-weight: 800;
    height: 36px;
    justify-content: center;
    letter-spacing: -.01em;
    width: 36px;
}
.ef-bk-r1-text { flex: 1; min-width: 0; }
.ef-bk-name {
    color: var(--bk-ink);
    font-size: .92rem;
    font-weight: 740;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-bk-evtype {
    color: var(--bk-muted);
    font-size: .67rem;
    font-weight: 620;
    letter-spacing: .04em;
    margin-top: 1px;
}
.ef-bk-badge {
    border-radius: 6px;
    flex-shrink: 0;
    font-size: .58rem;
    font-weight: 780;
    letter-spacing: .08em;
    padding: 3px 8px;
    text-transform: uppercase;
    white-space: nowrap;
}
.ef-bk-badge.--confirmed { background: rgba(15,123,95,.1); border: 1px solid rgba(15,123,95,.2); color: #0A5240; }
.ef-bk-badge.--completed { background: rgba(47,111,237,.1); border: 1px solid rgba(47,111,237,.2); color: #1A3E8A; }
.ef-bk-badge.--cancelled { background: rgba(200,75,68,.08); border: 1px solid rgba(200,75,68,.2); color: #8B2020; }

/* ── Amount highlight block ────────────────────────────────────── */
.ef-bk-amount-row {
    align-items: baseline;
    display: flex;
    gap: 8px;
    padding: 0 14px 8px;
}
.ef-bk-amt {
    color: var(--bk-ink);
    font-size: 1.2rem;
    font-variant-numeric: tabular-nums;
    font-weight: 820;
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-bk-pchip {
    border-radius: 5px;
    display: inline-block;
    font-size: .6rem;
    font-weight: 780;
    letter-spacing: .07em;
    padding: 2px 7px;
    text-transform: uppercase;
}
.ef-bk-pchip.--pending { background: rgba(138,108,48,.09); border: 1px solid rgba(138,108,48,.2);  color: #7A5A18; }
.ef-bk-pchip.--partial { background: rgba(47,111,237,.09); border: 1px solid rgba(47,111,237,.2);  color: #1A3E8A; }
.ef-bk-pchip.--paid    { background: rgba(15,123,95,.09);  border: 1px solid rgba(15,123,95,.2);   color: #0A5240; }

/* ── Meta rows: hall · date · time · guests ────────────────────── */
.ef-bk-rows {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 0 14px 10px;
}
.ef-bk-mrow {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 2px 8px;
    line-height: 1;
}
.ef-bk-mitem {
    align-items: center;
    color: var(--bk-sub);
    display: inline-flex;
    font-size: .73rem;
    gap: 3px;
}
.ef-bk-mitem i { color: var(--bk-faint); font-size: .68rem; }
.ef-bk-mdot {
    background: var(--bk-border-s);
    border-radius: 50%;
    flex-shrink: 0;
    height: 3px;
    width: 3px;
}

/* Meal tags */
.ef-bk-meal-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 0 14px 10px;
}
.ef-bk-meal-tag {
    background: rgba(138,108,48,.07);
    border: 1px solid rgba(138,108,48,.15);
    border-radius: 4px;
    color: var(--bk-gold);
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .05em;
    padding: 1px 6px;
    text-transform: uppercase;
}

/* ── Card footer: actions ──────────────────────────────────────── */
.ef-bk-foot {
    align-items: center;
    border-top: 1px solid var(--bk-border);
    display: flex;
    gap: 6px;
    justify-content: flex-end;
    margin-top: auto;
    padding: 8px 12px;
}
.ef-bk-acts {
    align-items: center;
    display: flex;
    gap: 4px;
}
.ef-bk-act {
    align-items: center;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .76rem;
    font-weight: 660;
    gap: 4px;
    min-height: 32px;
    padding: 5px 10px;
    text-decoration: none;
    transition: all .13s;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}
.ef-bk-act:hover { border-color: var(--bk-ink); color: var(--bk-ink); }
.ef-bk-act.--primary {
    background: var(--bk-ink);
    border-color: var(--bk-ink);
    color: var(--bk-surface);
}
.ef-bk-act.--primary:hover { opacity: .84; color: var(--bk-surface); }
.ef-bk-act.--ico { padding: 5px 8px; }
.ef-bk-act.--wa:hover { border-color: #25d366; color: #25d366; }
.ef-bk-more {
    align-items: center;
    background: transparent;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .85rem;
    min-height: 32px;
    justify-content: center;
    padding: 0 8px;
    transition: all .13s;
    -webkit-tap-highlight-color: transparent;
}
.ef-bk-more:hover { border-color: var(--bk-ink); color: var(--bk-ink); }

/* ══════════════════════════════════════════════════════════════════
   EMPTY STATES
══════════════════════════════════════════════════════════════════ */
.ef-bk-empty {
    background: var(--bk-surface);
    border: 1px solid var(--bk-border);
    border-radius: 16px;
    box-shadow: var(--bk-shadow);
    padding: 40px 24px;
    text-align: center;
}
.ef-bk-empty-icon {
    font-size: 2.4rem;
    line-height: 1;
    margin-bottom: 12px;
    opacity: .35;
}
.ef-bk-empty-title { color: var(--bk-ink); font-size: .98rem; font-weight: 760; margin-bottom: 6px; }
.ef-bk-empty-note  { color: var(--bk-muted); font-size: .82rem; line-height: 1.55; margin-bottom: 20px; }
.ef-bk-empty-actions { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }

/* ── Pagination ─────────────────────────────────────────────────── */
.ef-bk-pagination { display: flex; justify-content: center; margin-bottom: 16px; }

/* ══════════════════════════════════════════════════════════════════
   FAB — fixed above bottom navigation
   Bottom nav = ~54px + safe-area. FAB at 54px + 14px gap + safe-area.
══════════════════════════════════════════════════════════════════ */
.ef-bk-fab {
    align-items: center;
    background: linear-gradient(135deg, #8a6c30 0%, #b89040 100%);
    border: none;
    border-radius: 50%;
    bottom: calc(var(--ef-mobile-nav-height, 0px) + 16px + env(safe-area-inset-bottom, 0px));
    box-shadow: 0 4px 16px rgba(138,108,48,.45), 0 2px 6px rgba(0,0,0,.14);
    color: #fff;
    cursor: pointer;
    display: none;
    font-size: 1.35rem;
    height: 54px;
    justify-content: center;
    position: fixed;
    right: 16px;
    text-decoration: none;
    transition: transform .2s var(--bk-ease), box-shadow .2s var(--bk-ease);
    width: 54px;
    z-index: 1050;
}
.ef-bk-fab:hover { color: #fff; transform: scale(1.07); box-shadow: 0 6px 22px rgba(138,108,48,.55); }
.ef-bk-fab:active { transform: scale(.94); }

/* ══════════════════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════════════════ */
@media (max-width: 1199.98px) {
    .ef-bk-list { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 991.98px) {
    .ef-bk-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 767.98px) {
    /* Hero */
    .ef-bk-hero { padding: 14px 16px; border-radius: 13px; }
    .ef-bk-hero-title { font-size: 1.2rem; margin-bottom: 6px; }
    .ef-bk-hbtn span { display: none; }
    .ef-bk-hbtn { padding: 7px 9px; }

    /* FAB + filter sticky */
    .ef-bk-fab  { display: flex; }
    .ef-bk-filter { top: 4px; }

    /* Content clears FAB (54px) + gap (16px) + nav (--ef-mobile-nav-height) */
    .ef-bk-shell { padding-bottom: calc(var(--ef-mobile-nav-height, 0px) + 90px + env(safe-area-inset-bottom, 0px)); }

    /* Hide text search button on mobile — Enter key submits, filter icon stays */
    .ef-bk-btn-search-mob { display: none; }

    /* KPI row: slightly narrower items */
    .ef-bk-kpi-row { grid-template-columns: repeat(5, minmax(96px, 1fr)); }
}

@media (max-width: 639.98px) {
    /* Single column cards */
    .ef-bk-list { grid-template-columns: minmax(0, 1fr); }

    /* Card: tighter but clear hierarchy */
    .ef-bk-r1 { padding: 12px 13px 8px; gap: 8px; }
    .ef-bk-av { height: 34px; width: 34px; }
    .ef-bk-name { font-size: .9rem; }
    .ef-bk-amount-row { padding: 0 13px 8px; }
    .ef-bk-amt { font-size: 1.15rem; }
    .ef-bk-rows { padding: 0 13px 9px; }
    .ef-bk-meal-tags { padding: 0 13px 9px; }
    .ef-bk-foot { padding: 8px 10px; }

    /* Mobile action consolidation: hide icon-only buttons, keep View + ⋮
       This keeps all action links in the DOM (accessible) but hides the redundant ones */
    .ef-bk-act.--ico { display: none; }
    .ef-bk-act.--primary { padding: 6px 14px; font-size: .78rem; min-height: 34px; }
    .ef-bk-more { min-height: 34px; padding: 0 10px; }
}

@media (max-width: 479.98px) {
    .ef-bk-hero-title { font-size: 1.1rem; }
    .ef-bk-hero-summary { gap: 4px; }
    .ef-bk-hero-pill { font-size: .66rem; padding: 2px 8px; }
    .ef-bk-kpi-row { grid-template-columns: repeat(5, minmax(88px, 1fr)); min-width: 480px; }
}

/* Desktop: wider grid */
@media (min-width: 1200px) {
    .ef-bk-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-bk-kpi-row { grid-template-columns: repeat(5, minmax(108px, 1fr)); min-width: unset; }
}
</style>
@endpush

@php
$today = now()->toDateString();
$hasAny = fn(array $keys) => collect($keys)->some(fn($k) => request()->filled($k));

$allActive      = !request()->hasAny(['status','payment_status','date_from','date_to','search','hall_id','booking_type']);
$todayActive    = request('date_from') === $today && request('date_to') === $today
                  && !$hasAny(['status','payment_status','search','hall_id','booking_type']);
$upcomingActive = request('date_from') === $today && !request()->filled('date_to')
                  && !$hasAny(['status','payment_status','search','hall_id','booking_type']);
$pendingActive  = request('payment_status') === 'pending'
                  && !$hasAny(['status','date_from','date_to','search','hall_id','booking_type']);
$paidActive     = request('payment_status') === 'paid'
                  && !$hasAny(['status','date_from','date_to','search','hall_id','booking_type']);
$confirmedActive= request('status') === 'confirmed'
                  && !$hasAny(['payment_status','date_from','date_to','search','hall_id','booking_type']);
$hasAdvFilter   = request()->hasAny(['hall_id','status','payment_status','date_from','date_to','booking_type']);

$avatarTones = ['#7a5a28','#3e6a5a','#4a5e8a','#6a4e7a','#5a6840'];
@endphp

<div class="ef-bk-shell">

{{-- ═══════════════════════════════════════════════════════════════
     HERO — with operational summary
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-bk-hero">
    <div class="ef-bk-hero-left">
        <h1 class="ef-bk-hero-title">Hall Bookings</h1>
        <div class="ef-bk-hero-summary">
            @if($stats['today'] > 0)
                <span class="ef-bk-hero-pill --amber">
                    <span class="ef-bk-pulse"></span>
                    {{ $stats['today'] }} today
                </span>
            @endif
            @if($stats['pending_pay'] > 0)
                <span class="ef-bk-hero-pill --red">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $stats['pending_pay'] }} pending pay
                </span>
            @elseif($stats['pending_pay'] === 0)
                <span class="ef-bk-hero-pill --green">
                    <i class="bi bi-check-circle"></i>
                    Payments clear
                </span>
            @endif
            <span class="ef-bk-hero-pill --neutral">
                {{ $stats['upcoming'] }} upcoming
            </span>
        </div>
    </div>
    <div class="ef-bk-hero-actions">
        <a href="{{ route('hall.bookings.create') }}" class="ef-bk-hbtn --gold">
            <i class="bi bi-plus-circle"></i><span>New Booking</span>
        </a>
        <a href="{{ route('hall.bookings.calendar') }}" class="ef-bk-hbtn" title="Calendar">
            <i class="bi bi-calendar3"></i><span>Calendar</span>
        </a>
        <a href="{{ route('hall.bookings.kitchen') }}" class="ef-bk-hbtn" title="Kitchen">
            <i class="bi bi-cup-hot"></i><span>Kitchen</span>
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     KPI STRIP
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-bk-kpi-scroll">
    <div class="ef-bk-kpi-row">
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">
                @if($stats['today'] > 0)
                    <span style="animation:bk-pulse 1.8s ease-in-out infinite;background:var(--bk-gold);border-radius:50%;display:inline-block;height:6px;width:6px;flex-shrink:0"></span>
                @endif
                Today
            </div>
            <div class="ef-bk-kpi-val {{ $stats['today'] > 0 ? '--gold' : '' }}">{{ $stats['today'] }}</div>
            <div class="ef-bk-kpi-note">Active now</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Upcoming</div>
            <div class="ef-bk-kpi-val">{{ $stats['upcoming'] }}</div>
            <div class="ef-bk-kpi-note">From today</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Pending Pay</div>
            <div class="ef-bk-kpi-val {{ $stats['pending_pay'] > 0 ? '--red' : '' }}">{{ $stats['pending_pay'] }}</div>
            <div class="ef-bk-kpi-note">{{ $stats['pending_pay'] > 0 ? 'Needs attention' : 'All clear' }}</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Occupancy</div>
            <div class="ef-bk-kpi-val --green">{{ $stats['month_occ'] }}<span style="font-size:.75rem;font-weight:640">%</span></div>
            <div class="ef-bk-kpi-note">{{ now()->format('M') }}</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Guests</div>
            <div class="ef-bk-kpi-val">{{ number_format($stats['week_guests']) }}</div>
            <div class="ef-bk-kpi-note">This week</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     FILTER BAR — sticky
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-bk-filter">
    <form method="GET" id="bkFilterForm">
        {{-- Search row --}}
        <div class="ef-bk-filter-row1">
            <div class="ef-bk-search-wrap">
                <i class="bi bi-search ef-bk-search-ico"></i>
                <input type="text" name="search" class="ef-bk-search"
                       placeholder="Search name or mobile…"
                       value="{{ request('search') }}" autocomplete="off">
            </div>
            <div class="ef-bk-filter-btns">
                <button type="button" id="bkFiltToggle"
                        class="ef-bk-filt-btn {{ $hasAdvFilter ? '--active' : '' }}"
                        onclick="bkToggleAdv()">
                    <i class="bi bi-sliders2"></i>
                    @if($hasAdvFilter)<span class="ef-bk-filter-dot"></span>@endif
                </button>
                {{-- Desktop: show Search button; hidden on mobile via CSS (Enter key works) --}}
                <button type="submit" class="ef-bk-btn-apply ef-bk-btn-search-mob">
                    <i class="bi bi-search" style="font-size:.75rem"></i>
                </button>
            </div>
        </div>

        {{-- Quick chips --}}
        <div class="ef-bk-chips">
            <a href="{{ route('hall.bookings.index') }}"
               class="ef-bk-chip {{ $allActive ? '--active' : '' }}">All</a>
            <a href="{{ route('hall.bookings.index', ['date_from' => $today, 'date_to' => $today]) }}"
               class="ef-bk-chip {{ $todayActive ? '--active' : '' }}">Today</a>
            <a href="{{ route('hall.bookings.index', ['date_from' => $today]) }}"
               class="ef-bk-chip {{ $upcomingActive ? '--active' : '' }}">Upcoming</a>
            <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
               class="ef-bk-chip {{ $pendingActive ? '--active' : '' }}">Pending Pay</a>
            <a href="{{ route('hall.bookings.index', ['status' => 'confirmed']) }}"
               class="ef-bk-chip {{ $confirmedActive ? '--active' : '' }}">Confirmed</a>
            <a href="{{ route('hall.bookings.index', ['payment_status' => 'paid']) }}"
               class="ef-bk-chip {{ $paidActive ? '--active' : '' }}">Paid</a>
        </div>

        {{-- Advanced drawer --}}
        <div class="ef-bk-adv {{ $hasAdvFilter ? '--open' : '' }}" id="bkAdvPanel">
            <div class="ef-bk-adv-inner">
                <div>
                    <label class="ef-bk-adv-label">Booking Type</label>
                    <select name="booking_type" class="ef-bk-adv-select">
                        <option value="">All Types</option>
                        @foreach(\App\Models\HallBooking::bookingTypes() as $v => $l)
                            <option value="{{ $v }}" {{ request('booking_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">Hall</label>
                    <select name="hall_id" class="ef-bk-adv-select">
                        <option value="">All Halls</option>
                        @foreach($halls as $h)
                            <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">Status</label>
                    <select name="status" class="ef-bk-adv-select">
                        <option value="">All Status</option>
                        @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">Payment</label>
                    <select name="payment_status" class="ef-bk-adv-select">
                        <option value="">All Payments</option>
                        @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                            <option value="{{ $v }}" {{ request('payment_status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">From</label>
                    <input type="date" name="date_from" class="ef-bk-adv-input" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="ef-bk-adv-label">To</label>
                    <input type="date" name="date_to" class="ef-bk-adv-input" value="{{ request('date_to') }}">
                </div>
                <div style="display:flex;flex-direction:column;justify-content:flex-end">
                    <div class="ef-bk-adv-row">
                        <button type="submit" class="ef-bk-btn-apply">Apply</button>
                        <a href="{{ route('hall.bookings.index') }}" class="ef-bk-btn-clear">Clear</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Results meta --}}
<div class="ef-bk-meta-bar">
    <span>
        <strong>{{ $bookings->total() }}</strong> {{ Str::plural('booking', $bookings->total()) }}
        @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
            <span style="color:var(--bk-faint)"> · filtered</span>
            <span style="color:var(--bk-faint)"> · </span>
            <a href="{{ route('hall.bookings.index') }}"
               style="color:var(--bk-gold);font-weight:660;text-decoration:none">Clear</a>
        @endif
    </span>
    <span>By date ↓</span>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     BOOKING CARDS
═══════════════════════════════════════════════════════════════════ --}}
@if($bookings->isNotEmpty())
<div class="ef-bk-list">
    @foreach($bookings as $b)
    @php
        $tone   = $avatarTones[ord(strtoupper($b->customer_name[0] ?? 'A')) % count($avatarTones)];
        $meals  = collect(['Breakfast' => $b->has_breakfast, 'Lunch' => $b->has_lunch, 'Dinner' => $b->has_dinner])->filter()->keys();
        $waUrl  = 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile ?? '');
        $evType = \App\Models\HallBooking::eventTypes()[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type));
    @endphp

    <div class="ef-bk-card --{{ $b->status }}">

        {{-- Row 1: Avatar · Name · Event type · Status --}}
        <div class="ef-bk-r1">
            <div class="ef-bk-av" style="background:{{ $tone }}">
                {{ strtoupper(mb_substr($b->customer_name, 0, 1)) }}
            </div>
            <div class="ef-bk-r1-text">
                <div class="ef-bk-name">{{ $b->customer_name }}</div>
                <div class="ef-bk-evtype">{{ $evType }}</div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
                <span class="ef-bk-badge --{{ $b->status }}">{{ $b->status }}</span>
                <x-booking-type-badge :type="$b->booking_type" size="xs" />
            </div>
        </div>

        {{-- Amount + payment status — most operationally important --}}
        <div class="ef-bk-amount-row">
            <div class="ef-bk-amt">₹{{ number_format($b->total_amount) }}</div>
            <span class="ef-bk-pchip --{{ $b->payment_status }}">
                {{ \App\Models\HallBooking::paymentStatuses()[$b->payment_status] ?? $b->payment_status }}
            </span>
        </div>

        {{-- Meta: Hall · Date · Time · Guests --}}
        <div class="ef-bk-rows">
            <div class="ef-bk-mrow">
                <span class="ef-bk-mitem">
                    <i class="bi {{ $b->isFoodOnly() ? 'bi-cup-hot' : 'bi-building' }}"></i>
                    {{ $b->location_label }}
                </span>
                <span class="ef-bk-mdot"></span>
                <span class="ef-bk-mitem"><i class="bi bi-calendar3"></i> {{ $b->booking_date->format('d M') }}</span>
            </div>
            <div class="ef-bk-mrow">
                <span class="ef-bk-mitem">
                    <i class="bi bi-clock"></i>
                    {{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}
                </span>
                <span class="ef-bk-mdot"></span>
                <span class="ef-bk-mitem"><i class="bi bi-people"></i> {{ number_format($b->number_of_people) }}</span>
                @if($b->mealPlan || $meals->isNotEmpty())
                    <span class="ef-bk-mdot"></span>
                    <span class="ef-bk-mitem"><i class="bi bi-egg-fried"></i> Catering</span>
                @endif
            </div>
        </div>

        {{-- Meal tags --}}
        @if($meals->isNotEmpty())
            <div class="ef-bk-meal-tags">
                @foreach($meals as $m)
                    <span class="ef-bk-meal-tag">{{ $m }}</span>
                @endforeach
            </div>
        @endif

        {{-- Footer: actions --}}
        <div class="ef-bk-foot">
            <div class="ef-bk-acts">
                {{-- Primary: View Booking --}}
                <a href="{{ route('hall.bookings.show', $b) }}" class="ef-bk-act --primary">
                    <i class="bi bi-eye"></i> View
                </a>
                {{-- Desktop-visible icon buttons (hidden on mobile ≤639px via CSS) --}}
                <a href="{{ route('hall.bookings.invoice', $b) }}" class="ef-bk-act --ico"
                   target="_blank" title="Invoice">
                    <i class="bi bi-receipt"></i>
                </a>
                <a href="{{ $waUrl }}" class="ef-bk-act --ico --wa" target="_blank" title="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
                {{-- Overflow menu — visible always --}}
                <div class="dropdown">
                    <button class="ef-bk-more" type="button"
                            data-bs-toggle="dropdown"
                            data-bs-offset="0,4"
                            aria-expanded="false"
                            aria-label="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}">
                                <i class="bi bi-eye me-2" style="color:var(--bk-faint)"></i>View Booking
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('hall.bookings.edit', $b) }}">
                                <i class="bi bi-pencil me-2" style="color:var(--bk-faint)"></i>Edit Booking
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}#record-payment">
                                <i class="bi bi-cash-coin me-2" style="color:var(--bk-faint)"></i>Record Payment
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('hall.bookings.invoice', $b) }}" target="_blank">
                                <i class="bi bi-receipt me-2" style="color:var(--bk-faint)"></i>View Invoice
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                                <i class="bi bi-file-pdf me-2" style="color:var(--bk-faint)"></i>Download PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ $waUrl }}" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp me-2" style="color:#25d366"></i>Share via WhatsApp
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
    @endforeach
</div>

@else
{{-- ═══════════════════════════════════════════════════════════════
     EMPTY STATE
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-bk-empty">
    @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
        <div class="ef-bk-empty-icon">🔍</div>
        <h3 class="ef-bk-empty-title">No bookings match</h3>
        <p class="ef-bk-empty-note">
            No results for your current filters.<br>Try a different search or broaden your selection.
        </p>
        <div class="ef-bk-empty-actions">
            <a href="{{ route('hall.bookings.index') }}" class="ef-bk-btn-clear">
                <i class="bi bi-x-circle me-1"></i>Clear Filters
            </a>
        </div>
    @else
        <div class="ef-bk-empty-icon">📅</div>
        <h3 class="ef-bk-empty-title">No bookings yet</h3>
        <p class="ef-bk-empty-note">
            Your venue calendar is empty.<br>Create your first hall booking to get started.
        </p>
        <div class="ef-bk-empty-actions">
            <a href="{{ route('hall.bookings.create') }}" class="ef-bk-hbtn --gold" style="display:inline-flex">
                <i class="bi bi-plus-circle"></i><span>Create Booking</span>
            </a>
            <a href="{{ route('hall.bookings.calendar') }}" class="ef-bk-hbtn" style="display:inline-flex">
                <i class="bi bi-calendar3"></i><span>View Calendar</span>
            </a>
        </div>
    @endif
</div>
@endif

{{-- Pagination --}}
@if($bookings->hasPages())
    <div class="ef-bk-pagination">
        {{ $bookings->links() }}
    </div>
@endif

</div>{{-- /shell --}}

{{-- FAB — mobile new booking, positioned above bottom nav --}}
<a href="{{ route('hall.bookings.create') }}" class="ef-bk-fab ef-mobile-fab" title="New Booking" aria-label="Create new booking">
    <i class="bi bi-plus"></i>
</a>

@push('scripts')
<script>
function bkToggleAdv() {
    const panel = document.getElementById('bkAdvPanel');
    const btn   = document.getElementById('bkFiltToggle');
    const open  = panel.classList.toggle('--open');
    btn.classList.toggle('--active', open);
}

// Disable empty fields before submit (prevents ?search=&date_from= in URL)
document.getElementById('bkFilterForm').addEventListener('submit', function () {
    this.querySelectorAll('input[type="text"], input[type="date"], select').forEach(el => {
        if (!el.value.trim()) el.disabled = true;
    });
});
</script>
@endpush

</x-admin-layout>
