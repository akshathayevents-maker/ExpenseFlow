<x-admin-layout title="Kitchen Calculator">
@push('styles')
<style>
/* ════════════════════════════════════════════════════════════
   KITCHEN CALCULATOR — ef-kc-* namespace
   Mobile-first, high-contrast, one-hand operation
   ════════════════════════════════════════════════════════════ */
:root {
    --kc-green:      #0F7B5F;
    --kc-green-hi:   #0d6b51;
    --kc-green-glow: rgba(15,123,95,.28);
    --kc-amber:      #d97706;
    --kc-surface:    #fff;
    --kc-page:       #f5f2ee;
    --kc-border:     #e4dfd8;
    --kc-ink:        #1a1410;
    --kc-muted:      #7c7067;
    --kc-faint:      #b0a89e;
    --kc-danger:     #b91c1c;
    --kc-radius-lg:  20px;
    --kc-radius:     14px;
    --kc-radius-sm:  10px;
}

/* ── Page shell ────────────────────────────────────────────── */
.kc-wrap {
    max-width: 680px;
    margin: 0 auto;
    padding-bottom: 120px; /* space for sticky bar */
}

/* ── Back nav ──────────────────────────────────────────────── */
.kc-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .82rem;
    font-weight: 600;
    color: var(--kc-muted);
    text-decoration: none;
    padding: 10px 0;
    margin-bottom: 4px;
    transition: color .12s;
    -webkit-tap-highlight-color: transparent;
}
.kc-back:hover { color: var(--kc-green); }

/* ── Page header ────────────────────────────────────────────── */
.kc-page-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--kc-ink);
    line-height: 1.15;
    margin-bottom: 4px;
    letter-spacing: -.02em;
}
.kc-page-sub {
    font-size: .84rem;
    color: var(--kc-muted);
    margin-bottom: 20px;
}

/* ── Input card ────────────────────────────────────────────── */
.kc-card {
    background: var(--kc-surface);
    border: 1.5px solid var(--kc-border);
    border-radius: var(--kc-radius-lg);
    padding: 22px 20px;
    margin-bottom: 14px;
}
.kc-card-title {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--kc-muted);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.kc-card-title i { font-size: .9rem; color: var(--kc-green); }

/* ── Recipe selector ───────────────────────────────────────── */
.kc-recipe-select {
    width: 100%;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--kc-ink);
    background: #faf8f5;
    border: 2px solid var(--kc-border);
    border-radius: var(--kc-radius);
    padding: 16px 44px 16px 16px;
    min-height: 60px;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='%237c7067' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    line-height: 1.3;
}
.kc-recipe-select:focus {
    outline: none;
    border-color: var(--kc-green);
    box-shadow: 0 0 0 3px var(--kc-green-glow);
    background-color: #fff;
}
.kc-recipe-select option { font-size: 1rem; color: var(--kc-ink); }
.kc-recipe-select option[value=""] { color: var(--kc-muted); }

/* ── Searchable combo box ──────────────────────────────────── */
.kc-combo { position: relative; }

.kc-combo-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.kc-combo-search-icon {
    position: absolute;
    left: 16px;
    font-size: .9rem;
    color: var(--kc-muted);
    pointer-events: none;
    z-index: 1;
}
.kc-combo-input {
    width: 100%;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--kc-ink);
    background: #faf8f5;
    border: 2px solid var(--kc-border);
    border-radius: var(--kc-radius);
    padding: 16px 48px 16px 44px;
    min-height: 60px;
    appearance: none;
    transition: border-color .15s, box-shadow .15s;
    line-height: 1.3;
}
.kc-combo-input:focus {
    outline: none;
    border-color: var(--kc-green);
    box-shadow: 0 0 0 3px var(--kc-green-glow);
    background: #fff;
}
.kc-combo-input.--has-value { color: var(--kc-ink); }
.kc-combo-clear {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: var(--kc-muted);
    font-size: .82rem;
    cursor: pointer;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background .1s, color .1s;
    min-width: 36px;
    min-height: 36px;
    -webkit-tap-highlight-color: transparent;
}
.kc-combo-clear:hover { background: #f0ece6; color: var(--kc-danger); }

.kc-combo-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0; right: 0;
    background: #fff;
    border: 2px solid var(--kc-green);
    border-radius: var(--kc-radius);
    box-shadow: 0 8px 32px rgba(0,0,0,.14);
    max-height: 320px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1050;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}
.kc-combo-list {
    list-style: none;
    padding: 4px 0;
    margin: 0;
}
.kc-combo-group-lbl {
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--kc-muted);
    padding: 10px 16px 4px;
    border-top: 1px solid #f0ece6;
    margin-top: 2px;
}
.kc-combo-group-lbl:first-child { border-top: none; margin-top: 0; }
.kc-combo-item {
    padding: 12px 16px;
    font-size: .95rem;
    font-weight: 600;
    color: var(--kc-ink);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-height: 48px;
    transition: background .08s;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.kc-combo-item:hover,
.kc-combo-item.--focused {
    background: #f0fdf4;
    color: var(--kc-green);
}
.kc-combo-item-meta {
    font-size: .72rem;
    font-weight: 500;
    color: var(--kc-faint);
    white-space: nowrap;
    flex-shrink: 0;
}
.kc-combo-item.--focused .kc-combo-item-meta { color: rgba(15,123,95,.5); }
.kc-combo-empty {
    padding: 24px 16px;
    text-align: center;
    color: var(--kc-muted);
    font-size: .88rem;
    font-style: italic;
}
mark.kc-hl {
    background: #fef08a;
    color: inherit;
    border-radius: 2px;
    padding: 0 1px;
    font-style: normal;
}

/* Selected recipe meta strip */
.kc-recipe-meta {
    display: none;
    margin-top: 12px;
    padding: 12px 14px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: var(--kc-radius-sm);
    gap: 16px;
    flex-wrap: wrap;
}
.kc-recipe-meta.--show { display: flex; }
.kc-recipe-meta-item {
    font-size: .8rem;
    font-weight: 600;
    color: #166534;
    display: flex;
    align-items: center;
    gap: 5px;
}
.kc-recipe-meta-item i { font-size: .85rem; opacity: .8; }

/* ── People input ──────────────────────────────────────────── */
.kc-people-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.kc-people-input {
    flex: 1;
    min-width: 0;          /* override min-width:auto — the flex overflow root cause */
    width: 100%;
    box-sizing: border-box;
    font-size: 2rem;
    font-weight: 800;
    color: var(--kc-ink);
    background: #faf8f5;
    border: 2px solid var(--kc-border);
    border-radius: var(--kc-radius);
    padding: 14px 18px;
    min-height: 70px;
    text-align: center;
    letter-spacing: .02em;
    transition: border-color .15s, box-shadow .15s;
    -webkit-appearance: none;
    -moz-appearance: textfield;
    appearance: textfield;
}
.kc-people-input::-webkit-inner-spin-button,
.kc-people-input::-webkit-outer-spin-button { -webkit-appearance: none; appearance: none; margin: 0; }
.kc-people-input::placeholder { font-weight: 400; color: var(--kc-faint); }
.kc-people-input::-webkit-input-placeholder { font-weight: 400; color: var(--kc-faint); }
.kc-people-input::-moz-placeholder { font-weight: 400; color: var(--kc-faint); }
.kc-people-input:focus {
    outline: none;
    border-color: var(--kc-green);
    box-shadow: 0 0 0 3px var(--kc-green-glow);
    background: #fff;
}
/* +/- stepper buttons */
.kc-stepper {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex-shrink: 0;
}
.kc-step-btn {
    width: 44px;
    height: 31px;
    border: 1.5px solid var(--kc-border);
    border-radius: 8px;
    background: #f5f2ee;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--kc-ink);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .1s, border-color .1s;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
}
.kc-step-btn:active { background: #e8e3db; border-color: #ccc6bc; }

/* Quick-pick presets */
.kc-presets {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
    margin-top: 12px;
}
.kc-preset-btn {
    padding: 10px 4px;
    border: 1.5px solid var(--kc-border);
    border-radius: var(--kc-radius-sm);
    background: #faf8f5;
    font-size: .9rem;
    font-weight: 700;
    color: var(--kc-muted);
    cursor: pointer;
    text-align: center;
    transition: all .12s;
    -webkit-tap-highlight-color: transparent;
}
.kc-preset-btn:hover,
.kc-preset-btn.--active {
    background: var(--kc-green);
    border-color: var(--kc-green);
    color: #fff;
}

/* ── Calculate button ──────────────────────────────────────── */
.kc-calc-btn {
    width: 100%;
    min-height: 64px;
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: .03em;
    color: #fff;
    background: var(--kc-green);
    border: none;
    border-radius: var(--kc-radius-lg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 4px 18px var(--kc-green-glow);
    transition: background .15s, transform .1s, box-shadow .15s;
    -webkit-tap-highlight-color: transparent;
    margin-bottom: 14px;
}
.kc-calc-btn:hover   { background: var(--kc-green-hi); }
.kc-calc-btn:active  { transform: scale(.98); box-shadow: 0 2px 8px var(--kc-green-glow); }
.kc-calc-btn:disabled { background: #b0c9bf; box-shadow: none; cursor: not-allowed; }
.kc-calc-btn i { font-size: 1.2rem; }

/* ── Error banner ──────────────────────────────────────────── */
.kc-error {
    display: none;
    align-items: flex-start;
    gap: 10px;
    padding: 14px 16px;
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    border-radius: var(--kc-radius);
    font-size: .88rem;
    font-weight: 600;
    color: #991b1b;
    margin-bottom: 14px;
}
.kc-error.--show { display: flex; }

/* ── Results section ───────────────────────────────────────── */
.kc-results {
    display: none;
}
.kc-results.--show { display: block; }

/* Results header */
.kc-results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
}
.kc-results-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--kc-ink);
}
.kc-results-sub {
    font-size: .8rem;
    color: var(--kc-muted);
    margin-top: 2px;
}
/* Detailed/Compact toggle */
.kc-mode-toggle {
    display: flex;
    background: #f0ece6;
    border-radius: var(--kc-radius-sm);
    padding: 3px;
    gap: 2px;
    flex-shrink: 0;
}
.kc-mode-btn {
    padding: 7px 14px;
    border-radius: 8px;
    border: none;
    background: transparent;
    font-size: .8rem;
    font-weight: 700;
    color: var(--kc-muted);
    cursor: pointer;
    transition: all .12s;
    -webkit-tap-highlight-color: transparent;
    white-space: nowrap;
}
.kc-mode-btn.--active {
    background: #fff;
    color: var(--kc-ink);
    box-shadow: 0 1px 4px rgba(0,0,0,.1);
}

/* ── Ingredients list ──────────────────────────────────────── */
.kc-ing-list {
    border: 1.5px solid var(--kc-border);
    border-radius: var(--kc-radius-lg);
    overflow: hidden;
    margin-bottom: 14px;
}
.kc-ing-list-head {
    padding: 12px 18px;
    background: #f7f4f0;
    border-bottom: 1px solid var(--kc-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.kc-ing-count-pill {
    font-size: .72rem;
    font-weight: 700;
    background: var(--kc-green);
    color: #fff;
    padding: 3px 10px;
    border-radius: 20px;
}

/* Detailed mode rows */
.kc-ing-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid #f0ece6;
    gap: 12px;
    transition: background .1s;
}
.kc-ing-row:last-child { border-bottom: none; }
.kc-ing-row:active { background: #faf8f5; }
.kc-ing-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--kc-ink);
    flex: 1;
}
.kc-ing-name-note {
    font-size: .75rem;
    font-weight: 500;
    color: var(--kc-muted);
    display: block;
    margin-top: 2px;
}
.kc-ing-optional-tag {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--kc-amber);
    background: #fef3c7;
    border: 1px solid #fde68a;
    padding: 1px 6px;
    border-radius: 8px;
    margin-left: 6px;
    vertical-align: middle;
}
.kc-ing-qty {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--kc-green);
    white-space: nowrap;
    flex-shrink: 0;
}
.kc-ing-qty.--verbatim {
    font-size: .9rem;
    font-weight: 600;
    color: var(--kc-muted);
    font-style: italic;
}

/* Compact mode rows */
.kc-ing-compact {
    padding: 11px 18px;
    border-bottom: 1px solid #f0ece6;
    font-size: .95rem;
    font-weight: 600;
    color: var(--kc-ink);
    display: flex;
    align-items: center;
    gap: 0;
}
.kc-ing-compact:last-child { border-bottom: none; }
.kc-ing-compact .kc-cqty {
    font-weight: 800;
    color: var(--kc-green);
    margin-right: 6px;
}
.kc-ing-compact .kc-cunit {
    color: var(--kc-muted);
    font-weight: 600;
    margin-right: 6px;
}
.kc-ing-compact .kc-cname { color: var(--kc-ink); }
.kc-ing-compact .kc-cverbatim {
    font-style: italic;
    color: var(--kc-muted);
    margin-right: 6px;
}

/* ── SOP section ───────────────────────────────────────────── */
.kc-sop-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--kc-surface);
    border: 1.5px solid var(--kc-border);
    border-radius: var(--kc-radius-lg);
    font-size: 1rem;
    font-weight: 700;
    color: var(--kc-ink);
    cursor: pointer;
    transition: background .12s, border-color .12s;
    -webkit-tap-highlight-color: transparent;
    text-align: left;
    margin-bottom: 0;
}
.kc-sop-toggle.--open {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-color: transparent;
    background: #faf8f5;
}
.kc-sop-toggle:active { background: #f5f2ee; }
.kc-sop-chevron {
    transition: transform .2s;
    color: var(--kc-muted);
    flex-shrink: 0;
}
.kc-sop-toggle.--open .kc-sop-chevron { transform: rotate(180deg); }

.kc-sop-body {
    display: none;
    border: 1.5px solid var(--kc-border);
    border-top: none;
    border-bottom-left-radius: var(--kc-radius-lg);
    border-bottom-right-radius: var(--kc-radius-lg);
    overflow: hidden;
    margin-bottom: 14px;
}
.kc-sop-body.--open { display: block; }

.kc-sop-step {
    display: flex;
    gap: 14px;
    padding: 18px;
    border-bottom: 1px solid #f0ece6;
}
.kc-sop-step:last-child { border-bottom: none; }
.kc-sop-num {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--kc-green);
    color: #fff;
    font-size: .85rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}
.kc-sop-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--kc-ink);
    margin-bottom: 5px;
    line-height: 1.3;
}
.kc-sop-inst {
    font-size: .92rem;
    color: #3d3528;
    line-height: 1.65;
    margin: 0 0 6px;
}
.kc-sop-dur {
    font-size: .75rem;
    font-weight: 600;
    color: var(--kc-muted);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* ── Empty / placeholder ───────────────────────────────────── */
.kc-placeholder {
    text-align: center;
    padding: 48px 24px;
    color: var(--kc-faint);
}
.kc-placeholder-icon {
    font-size: 3rem;
    margin-bottom: 12px;
    color: #d4cfc8;
}
.kc-placeholder p {
    font-size: .9rem;
    font-weight: 600;
    color: var(--kc-muted);
    margin: 0;
    line-height: 1.5;
}

/* ── Sticky action bar (mobile) ────────────────────────────── */
.kc-sticky-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 1040;
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid var(--kc-border);
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
    display: none;
    transition: transform .2s ease;
}
/* Hidden while keyboard is open — prevents obscuring the input */
.kc-sticky-bar.--kbd-hidden { transform: translateY(110%); }
@media (max-width: 767.98px) {
    .kc-sticky-bar {
        display: block;
        bottom: var(--mn-height, 0px); /* above mobile nav — height from mobile.css:--mn-height */
        z-index: 1050;                 /* above nav z-index: 1045 */
        padding-bottom: 12px;          /* no safe-area inset — bar is no longer at screen edge */
    }
    .kc-calc-btn.--desktop { display: none; }
    /* Focused input stays above bar + nav combined */
    html { scroll-padding-bottom: calc(var(--mn-height, 0px) + 80px); }
}
@media (min-width: 768px) {
    .kc-calc-btn.--mobile-bar { display: none; }
}

/* ══════════════════════════════════════════════════════════
   PRINT STYLES
   ══════════════════════════════════════════════════════════ */
@media print {
    /* Hide everything except the result */
    body > *:not(.kc-print-root),
    .kc-wrap > *:not(#kcPrintArea),
    .kc-sticky-bar,
    nav, header, footer,
    [class*="ef-nav"], [class*="ef-sidebar"], [class*="ef-top"],
    #kcInputCard, #kcPeopleCard, .kc-calc-btn,
    .kc-mode-toggle, .kc-back, .kc-page-title, .kc-page-sub,
    .kc-placeholder, .kc-error {
        display: none !important;
    }

    body { background: #fff !important; }

    #kcPrintArea {
        display: block !important;
        padding: 0;
        margin: 0;
    }

    /* AKSHATHAY header */
    .kc-print-header {
        text-align: center;
        border-bottom: 2.5px solid #000;
        padding-bottom: 12px;
        margin-bottom: 18px;
    }
    .kc-print-company {
        font-size: 22pt;
        font-weight: 900;
        letter-spacing: .12em;
        color: #000;
        margin-bottom: 2pt;
    }
    .kc-print-sheet-label {
        font-size: 11pt;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #444;
    }
    .kc-print-meta-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18pt;
        font-size: 10pt;
    }
    .kc-print-meta-table td {
        padding: 5pt 0;
        border-bottom: 0.5pt solid #ccc;
        vertical-align: top;
    }
    .kc-print-meta-table td:first-child {
        font-weight: 700;
        color: #555;
        width: 120pt;
        padding-right: 12pt;
    }
    .kc-print-section-title {
        font-size: 11pt;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #000;
        border-bottom: 1pt solid #000;
        padding-bottom: 4pt;
        margin-bottom: 10pt;
    }
    .kc-print-ing-row {
        display: flex;
        justify-content: space-between;
        padding: 5pt 0;
        border-bottom: 0.5pt solid #e0e0e0;
        font-size: 10pt;
    }
    .kc-print-ing-row:last-child { border-bottom: none; }
    .kc-print-ing-name { font-weight: 600; }
    .kc-print-ing-qty  { font-weight: 700; }
    .kc-print-ing-verbatim { font-style: italic; color: #555; }
    .kc-print-sop-step {
        display: flex;
        gap: 12pt;
        padding: 8pt 0;
        border-bottom: 0.5pt solid #e8e8e8;
        font-size: 10pt;
    }
    .kc-print-sop-step:last-child { border-bottom: none; }
    .kc-print-sop-num {
        width: 20pt; height: 20pt;
        border: 1.5pt solid #000;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8pt;
        font-weight: 800;
        flex-shrink: 0;
        margin-top: 1pt;
        color: #000;
    }
    .kc-print-sop-title { font-weight: 700; margin-bottom: 3pt; }
    .kc-print-sop-inst  { line-height: 1.55; color: #222; }
    .kc-print-sop-dur   { font-style: italic; color: #666; font-size: 9pt; margin-top: 3pt; }
    .kc-print-footer {
        margin-top: 18pt;
        border-top: 1pt solid #ccc;
        padding-top: 8pt;
        font-size: 8pt;
        color: #888;
        text-align: center;
    }
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .kc-page-title  { font-size: 1.35rem; }
    .kc-card        { padding: 18px 16px; }
    .kc-people-input{ font-size: 1.75rem; min-height: 64px; }
    .kc-ing-row     { padding: 13px 14px; }
    .kc-ing-name    { font-size: .95rem; }
    .kc-ing-qty     { font-size: 1rem; }
    /* Content must scroll past: sticky bar (~88px) + nav (--mn-height) */
    .kc-wrap        { padding-bottom: calc(var(--mn-height, 0px) + 100px); }
}
@media (max-width: 479.98px) {
    .kc-preset-btn  { padding: 8px 2px; font-size: .78rem; }
}
</style>
@endpush

{{-- ══ Pass recipe data to JS ══════════════════════════════════════════════ --}}
<script>
window.KcRecipes    = @json($recipes->keyBy('id'));
window.KcCategories = @json($categories);
</script>

<div class="kc-wrap">

    {{-- Back nav --}}
    <a href="{{ route('employee.dashboard') }}" class="kc-back">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>

    <h1 class="kc-page-title"><i class="bi bi-calculator" style="color:var(--kc-green);margin-right:8px"></i>Kitchen Calculator</h1>
    <p class="kc-page-sub">Select a recipe and enter the number of people to scale ingredients automatically.</p>

    {{-- ── Step 1: Recipe Selector ──────────────────────────────────────── --}}
    <div class="kc-card" id="kcInputCard">
        <div class="kc-card-title"><i class="bi bi-journal-richtext"></i> Step 1 — Select Recipe</div>

        <div class="kc-combo" id="kcComboWrap">
            <div class="kc-combo-input-wrap">
                <i class="bi bi-search kc-combo-search-icon" aria-hidden="true"></i>
                <input type="text"
                       id="kcComboInput"
                       class="kc-combo-input"
                       placeholder="Search recipes…"
                       autocomplete="off"
                       autocorrect="off"
                       autocapitalize="off"
                       spellcheck="false"
                       role="combobox"
                       aria-expanded="false"
                       aria-autocomplete="list"
                       aria-haspopup="listbox"
                       aria-controls="kcComboList">
                <button type="button" id="kcComboClear" class="kc-combo-clear" aria-label="Clear selection" style="display:none">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="kc-combo-dropdown" id="kcComboDropdown" role="listbox" style="display:none">
                <ul class="kc-combo-list" id="kcComboList"></ul>
            </div>
        </div>

        <div class="kc-recipe-meta" id="kcRecipeMeta">
            <span class="kc-recipe-meta-item" id="kcMetaYield">
                <i class="bi bi-people"></i> <span id="kcMetaYieldTxt">—</span>
            </span>
            <span class="kc-recipe-meta-item" id="kcMetaTime">
                <i class="bi bi-clock"></i> <span id="kcMetaTimeTxt">—</span>
            </span>
            <span class="kc-recipe-meta-item">
                <i class="bi bi-list-check"></i> <span id="kcMetaIngsTxt">—</span> ingredients
            </span>
        </div>
    </div>

    {{-- ── Step 2: People Count ──────────────────────────────────────────── --}}
    <div class="kc-card" id="kcPeopleCard">
        <div class="kc-card-title"><i class="bi bi-people"></i> Step 2 — Number of People</div>

        <div class="kc-people-wrap">
            <div class="kc-stepper">
                <button type="button" class="kc-step-btn" id="kcStepUp" aria-label="Increase">+</button>
                <button type="button" class="kc-step-btn" id="kcStepDown" aria-label="Decrease">−</button>
            </div>
            <input type="number"
                   id="kcPeopleInput"
                   class="kc-people-input"
                   placeholder="50"
                   min="1"
                   inputmode="numeric"
                   pattern="[0-9]*"
                   autocomplete="off"
                   autocorrect="off"
                   autocapitalize="off"
                   spellcheck="false">
        </div>

        <div class="kc-presets">
            <button type="button" class="kc-preset-btn" data-n="50">50</button>
            <button type="button" class="kc-preset-btn" data-n="100">100</button>
            <button type="button" class="kc-preset-btn" data-n="250">250</button>
            <button type="button" class="kc-preset-btn" data-n="500">500</button>
            <button type="button" class="kc-preset-btn" data-n="1000">1000</button>
            <button type="button" class="kc-preset-btn" data-n="5000">5000</button>
        </div>
    </div>

    {{-- ── Calculate button (desktop) ───────────────────────────────────── --}}
    <button type="button" class="kc-calc-btn --desktop" id="kcCalcBtnDesktop">
        <i class="bi bi-calculator"></i> Calculate Ingredients
    </button>

    {{-- ── Error ─────────────────────────────────────────────────────────── --}}
    <div class="kc-error" id="kcError" role="alert">
        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:1px"></i>
        <span id="kcErrorMsg">Please select a recipe and enter a valid number of people.</span>
    </div>

    {{-- ── Results ────────────────────────────────────────────────────────── --}}
    <div class="kc-results" id="kcResults">

        <div class="kc-results-header">
            <div>
                <div class="kc-results-title" id="kcResultsTitle">Ingredients</div>
                <div class="kc-results-sub" id="kcResultsSub"></div>
            </div>
            <div class="kc-mode-toggle" role="group" aria-label="View mode">
                <button type="button" class="kc-mode-btn --active" id="kcModeDetailed">Detailed</button>
                <button type="button" class="kc-mode-btn" id="kcModeCompact">Compact</button>
            </div>
        </div>

        {{-- Ingredient list placeholder --}}
        <div class="kc-ing-list" id="kcIngList">
            <div class="kc-ing-list-head">
                <span class="kc-card-title" style="margin:0">
                    <i class="bi bi-list-check"></i> Ingredients
                </span>
                <span class="kc-ing-count-pill" id="kcIngCount">0 items</span>
            </div>
            <div id="kcIngRows">
                <div class="kc-placeholder">
                    <div class="kc-placeholder-icon"><i class="bi bi-egg-fried"></i></div>
                    <p>Results will appear here<br>after you calculate.</p>
                </div>
            </div>
        </div>

        {{-- SOP section --}}
        <div id="kcSopSection" style="display:none">
            <button type="button" class="kc-sop-toggle" id="kcSopToggle" aria-expanded="false">
                <span style="display:flex;align-items:center;gap:8px">
                    <i class="bi bi-list-ol" style="color:var(--kc-green)"></i>
                    <span>Preparation Steps</span>
                    <span class="kc-ing-count-pill" id="kcSopCount" style="background:#64748b">0 steps</span>
                </span>
                <i class="bi bi-chevron-down kc-sop-chevron"></i>
            </button>
            <div class="kc-sop-body" id="kcSopBody"></div>
        </div>

        {{-- Print area (hidden on screen, shown on print) --}}
        <div id="kcPrintArea" style="display:none">
            <div class="kc-print-header">
                <div class="kc-print-company">AKSHATHAY</div>
                <div class="kc-print-sheet-label">Production Sheet</div>
            </div>
            <table class="kc-print-meta-table">
                <tr><td>Recipe</td><td id="kcp-recipe">—</td></tr>
                <tr><td>People</td><td id="kcp-people">—</td></tr>
                <tr><td>Generated by</td><td>{{ auth()->user()->name }}</td></tr>
                <tr><td>Generated at</td><td id="kcp-time">—</td></tr>
            </table>
            <div class="kc-print-section-title">Ingredients</div>
            <div id="kcp-ings"></div>
            <div id="kcp-sop-wrap" style="margin-top:18pt">
                <div class="kc-print-section-title">Preparation Steps</div>
                <div id="kcp-sops"></div>
            </div>
            <div class="kc-print-footer">
                Printed from ExpenseFlow Kitchen Calculator · AKSHATHAY
            </div>
        </div>

        {{-- Print & reset actions --}}
        <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
            <button type="button" id="kcPrintBtn"
                    style="flex:1;min-height:48px;border:1.5px solid var(--kc-border);border-radius:var(--kc-radius);background:#fff;font-size:.92rem;font-weight:700;color:var(--kc-ink);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .12s;-webkit-tap-highlight-color:transparent">
                <i class="bi bi-printer"></i> Print Sheet
            </button>
            <button type="button" id="kcResetBtn"
                    style="padding:0 18px;min-height:48px;border:1.5px solid var(--kc-border);border-radius:var(--kc-radius);background:#fff;font-size:.88rem;font-weight:600;color:var(--kc-muted);cursor:pointer;transition:background .12s;-webkit-tap-highlight-color:transparent">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </div>

    </div>{{-- /kc-results --}}

</div>{{-- /kc-wrap --}}

{{-- ── Sticky calculate bar (mobile only) ──────────────────────────────── --}}
<div class="kc-sticky-bar">
    <button type="button" class="kc-calc-btn --mobile-bar" id="kcCalcBtnMobile" style="margin:0">
        <i class="bi bi-calculator"></i> Calculate
    </button>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    /* ── State ─────────────────────────────────────────────── */
    let currentRecipe    = null;
    let currentPeople    = 0;
    let viewMode         = 'detailed';
    let sopOpen          = false;
    let comboSelectedId  = null;
    let comboFocusedIdx  = -1;
    let comboDropdownOpen = false;
    let comboVisibleItems = []; // flat array of recipe objects currently rendered

    /* ── DOM refs ──────────────────────────────────────────── */
    const comboInput    = document.getElementById('kcComboInput');
    const comboDropdown = document.getElementById('kcComboDropdown');
    const comboList     = document.getElementById('kcComboList');
    const comboClear    = document.getElementById('kcComboClear');
    const meta          = document.getElementById('kcRecipeMeta');
    const metaYield     = document.getElementById('kcMetaYieldTxt');
    const metaTime      = document.getElementById('kcMetaTimeTxt');
    const metaIngs      = document.getElementById('kcMetaIngsTxt');
    const peopleIn      = document.getElementById('kcPeopleInput');
    const stepUp        = document.getElementById('kcStepUp');
    const stepDown      = document.getElementById('kcStepDown');
    const presets       = document.querySelectorAll('.kc-preset-btn');
    const calcBtns      = [document.getElementById('kcCalcBtnDesktop'), document.getElementById('kcCalcBtnMobile')];
    const errorBox      = document.getElementById('kcError');
    const errorMsg      = document.getElementById('kcErrorMsg');
    const results       = document.getElementById('kcResults');
    const resTit        = document.getElementById('kcResultsTitle');
    const resSub        = document.getElementById('kcResultsSub');
    const modeDet       = document.getElementById('kcModeDetailed');
    const modeCom       = document.getElementById('kcModeCompact');
    const ingRows       = document.getElementById('kcIngRows');
    const ingCount      = document.getElementById('kcIngCount');
    const sopSec        = document.getElementById('kcSopSection');
    const sopToggle     = document.getElementById('kcSopToggle');
    const sopBody       = document.getElementById('kcSopBody');
    const sopCount      = document.getElementById('kcSopCount');
    const printBtn      = document.getElementById('kcPrintBtn');
    const resetBtn      = document.getElementById('kcResetBtn');
    const stickyBar     = document.querySelector('.kc-sticky-bar');

    /* ── Helpers ───────────────────────────────────────────── */
    function formatQty(val) {
        let s = parseFloat(val).toFixed(3);
        s = s.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '');
        return s;
    }
    function showError(msg) { errorMsg.textContent = msg; errorBox.classList.add('--show'); }
    function hideError()    { errorBox.classList.remove('--show'); }
    function pluralise(n, word) { return n + ' ' + (n === 1 ? word : word + 's'); }

    /* ── Combo: render dropdown ─────────────────────────────── */
    function comboRender(query) {
        const q = query.toLowerCase().trim();
        comboVisibleItems = [];
        let html = '';

        // Helper: render a group of recipes under a heading
        function renderGroup(groupLabel, groupRecipes) {
            if (!groupRecipes.length) return;
            html += '<li class="kc-combo-group-lbl" role="presentation">' + escHtml(groupLabel) + '</li>';
            for (const r of groupRecipes) {
                const idx      = comboVisibleItems.length;
                comboVisibleItems.push(r);
                const nameHtml = q ? highlightMatch(r.name, q) : escHtml(r.name);
                const total    = (r.prep_time_minutes || 0) + (r.cook_time_minutes || 0);
                const metaTxt  = formatQty(r.yield_per_batch) + ' ' + escHtml(r.yield_unit)
                               + (total ? ' · ' + total + ' min' : '');
                const selected = (r.id == comboSelectedId) ? ' style="background:#f0fdf4;color:var(--kc-green)"' : '';
                html += '<li class="kc-combo-item" role="option"'
                      + ' data-idx="' + idx + '" data-id="' + r.id + '"'
                      + ' id="kcCOpt' + idx + '"' + selected + '>'
                      + '<span>' + nameHtml + '</span>'
                      + '<span class="kc-combo-item-meta">' + metaTxt + '</span>'
                      + '</li>';
            }
        }

        // Categorised recipes (grouped)
        for (const cat of window.KcCategories) {
            const catRecipes = Object.values(window.KcRecipes).filter(r => {
                if (r.category !== cat) return false;
                if (!q) return true;
                return r.name.toLowerCase().includes(q) || cat.toLowerCase().includes(q);
            });
            renderGroup(cat, catRecipes);
        }

        // Uncategorised recipes (null category)
        const uncatRecipes = Object.values(window.KcRecipes).filter(r => {
            if (r.category) return false;
            if (!q) return true;
            return r.name.toLowerCase().includes(q);
        });
        renderGroup('Uncategorised', uncatRecipes);

        if (!comboVisibleItems.length) {
            html = '<li class="kc-combo-empty">No recipes match "' + escHtml(query) + '"</li>';
        }

        comboList.innerHTML = html;
        comboFocusedIdx = -1;

        comboList.querySelectorAll('.kc-combo-item').forEach(li => {
            li.addEventListener('mousedown', e => {
                e.preventDefault(); // prevent blur closing before select
                comboSelect(parseInt(li.dataset.id));
            });
            li.addEventListener('touchend', e => {
                e.preventDefault();
                comboSelect(parseInt(li.dataset.id));
            });
        });
    }

    function highlightMatch(text, q) {
        const idx = text.toLowerCase().indexOf(q);
        if (idx === -1) return escHtml(text);
        return escHtml(text.slice(0, idx))
             + '<mark class="kc-hl">' + escHtml(text.slice(idx, idx + q.length)) + '</mark>'
             + escHtml(text.slice(idx + q.length));
    }

    /* ── Combo: open/close ──────────────────────────────────── */
    function comboOpenDropdown() {
        if (comboDropdownOpen) return;
        comboDropdownOpen = true;
        comboDropdown.style.display = 'block';
        comboInput.setAttribute('aria-expanded', 'true');
        comboRender(comboSelectedId ? '' : comboInput.value);
    }
    function comboCloseDropdown() {
        if (!comboDropdownOpen) return;
        comboDropdownOpen = false;
        comboDropdown.style.display = 'none';
        comboInput.setAttribute('aria-expanded', 'false');
        comboFocusedIdx = -1;
    }

    /* ── Combo: select a recipe ─────────────────────────────── */
    function comboSelect(id) {
        const r = window.KcRecipes[id];
        if (!r) return;
        comboSelectedId = id;
        comboInput.value = r.name;
        comboInput.classList.add('--has-value');
        comboClear.style.display = 'flex';
        comboCloseDropdown();

        // Update meta strip
        const total = (r.prep_time_minutes || 0) + (r.cook_time_minutes || 0);
        metaYield.textContent = '1 batch = ' + formatQty(r.yield_per_batch) + ' ' + r.yield_unit;
        metaTime.textContent  = total ? total + ' min total' : 'Time not set';
        metaIngs.textContent  = r.ingredients_count;
        meta.classList.add('--show');

        hideError();
        results.classList.remove('--show');
        currentRecipe = null;
        fetchRecipe(id);
    }

    /* ── Combo: clear selection ─────────────────────────────── */
    function comboClearSelection() {
        comboSelectedId = null;
        comboInput.value = '';
        comboInput.classList.remove('--has-value');
        comboClear.style.display = 'none';
        meta.classList.remove('--show');
        results.classList.remove('--show');
        currentRecipe = null;
        hideError();
        comboInput.focus();
    }

    /* ── Combo: keyboard focus tracking ────────────────────── */
    function comboSetFocus(idx) {
        const items = comboList.querySelectorAll('.kc-combo-item');
        items.forEach(el => el.classList.remove('--focused'));
        comboFocusedIdx = idx;
        if (idx >= 0 && idx < items.length) {
            items[idx].classList.add('--focused');
            items[idx].scrollIntoView({ block: 'nearest' });
            comboInput.setAttribute('aria-activedescendant', 'kcCOpt' + idx);
        } else {
            comboInput.removeAttribute('aria-activedescendant');
        }
    }

    /* ── Combo: events ──────────────────────────────────────── */
    comboInput.addEventListener('focus', () => {
        comboOpenDropdown();
        if (stickyBar) stickyBar.classList.add('--kbd-hidden');
    });
    comboInput.addEventListener('blur', () => {
        // Delay so mousedown/touchend on items fires first
        setTimeout(() => {
            comboCloseDropdown();
            // Restore selected name if user blurred without selecting
            if (comboSelectedId && window.KcRecipes[comboSelectedId]) {
                comboInput.value = window.KcRecipes[comboSelectedId].name;
            }
        }, 160);
    });
    comboInput.addEventListener('input', () => {
        if (comboSelectedId) {
            // User started typing again — deselect
            comboSelectedId = null;
            comboClear.style.display = 'none';
            comboInput.classList.remove('--has-value');
            meta.classList.remove('--show');
            results.classList.remove('--show');
            currentRecipe = null;
        }
        comboOpenDropdown();
        comboRender(comboInput.value);
    });
    comboInput.addEventListener('keydown', e => {
        const items = comboList.querySelectorAll('.kc-combo-item');
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                comboOpenDropdown();
                comboSetFocus(Math.min(comboFocusedIdx + 1, items.length - 1));
                break;
            case 'ArrowUp':
                e.preventDefault();
                comboSetFocus(Math.max(comboFocusedIdx - 1, 0));
                break;
            case 'Enter':
                e.preventDefault();
                if (comboDropdownOpen && comboFocusedIdx >= 0 && comboVisibleItems[comboFocusedIdx]) {
                    comboSelect(comboVisibleItems[comboFocusedIdx].id);
                } else if (!comboDropdownOpen) {
                    calculate();
                }
                break;
            case 'Escape':
                comboCloseDropdown();
                if (comboSelectedId && window.KcRecipes[comboSelectedId]) {
                    comboInput.value = window.KcRecipes[comboSelectedId].name;
                }
                break;
            case 'Tab':
                comboCloseDropdown();
                break;
        }
    });
    comboClear.addEventListener('click', comboClearSelection);
    comboClear.addEventListener('mousedown', e => e.preventDefault()); // prevent blur

    function fetchRecipe(id) {
        fetch('{{ route("employee.kitchen.calculator.recipe", ":id") }}'.replace(':id', id), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => { if (!r.ok) throw new Error('Recipe not found'); return r.json(); })
        .then(data => { currentRecipe = data; })
        .catch(() => { currentRecipe = null; showError('Could not load recipe data. Please try again.'); });
    }

    /* ── Steppers ──────────────────────────────────────────── */
    stepUp.addEventListener('click', () => {
        const v = parseInt(peopleIn.value) || 0;
        const step = v >= 1000 ? 500 : v >= 100 ? 50 : v >= 50 ? 10 : 1;
        peopleIn.value = v + step;
        syncPresets();
    });
    stepDown.addEventListener('click', () => {
        const v = parseInt(peopleIn.value) || 0;
        const step = v > 1000 ? 500 : v > 100 ? 50 : v > 50 ? 10 : 1;
        peopleIn.value = Math.max(1, v - step);
        syncPresets();
    });

    /* Input: only sync presets — never modify the value (cursor position stays stable) */
    peopleIn.addEventListener('input', syncPresets);

    /* Enter key triggers calculate */
    peopleIn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            calculate();
        }
    });

    /* Hide sticky bar while typing to prevent obscuring input on mobile */
    peopleIn.addEventListener('focus', () => {
        if (stickyBar) stickyBar.classList.add('--kbd-hidden');
    });
    peopleIn.addEventListener('blur', () => {
        if (stickyBar) stickyBar.classList.remove('--kbd-hidden');
    });

    function syncPresets() {
        const v = parseInt(peopleIn.value);
        presets.forEach(btn => {
            btn.classList.toggle('--active', parseInt(btn.dataset.n) === v);
        });
    }

    presets.forEach(btn => {
        btn.addEventListener('click', () => {
            peopleIn.value = btn.dataset.n;
            syncPresets();
            peopleIn.focus();   // move focus to input so Enter still works
        });
    });

    /* ── Calculate ─────────────────────────────────────────── */
    calcBtns.forEach(btn => btn && btn.addEventListener('click', calculate));

    function calculate() {
        hideError();

        if (!comboSelectedId) {
            showError('Please select a recipe first.');
            comboInput.focus();
            return;
        }

        const rawVal = peopleIn.value.trim();
        if (rawVal === '') {
            showError('Enter the number of people.');
            peopleIn.focus();
            return;
        }
        const people = parseInt(rawVal, 10);
        if (isNaN(people) || people < 1) {
            showError('Number of people must be 1 or more.');
            peopleIn.focus();
            return;
        }
        if (people > 100000) {
            showError('Value too large (max 100,000). Check the number and try again.');
            peopleIn.focus();
            return;
        }
        if (!currentRecipe) {
            showError('Recipe data is still loading — please wait a moment and try again.');
            return;
        }

        currentPeople = people;
        const factor = people / currentRecipe.yield_per_batch;

        renderResults(factor, people);
        results.classList.add('--show');

        // Scroll results into view on mobile
        if (window.innerWidth < 768) {
            setTimeout(() => results.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
        }
    }

    /* ── Render ────────────────────────────────────────────── */
    function renderResults(factor, people) {
        resTit.textContent = currentRecipe.name;
        resSub.textContent = 'Scaled for ' + people + ' people  ·  ' +
            formatQty(factor) + '× batch  ·  1 batch = ' +
            formatQty(currentRecipe.yield_per_batch) + ' ' + currentRecipe.yield_unit;

        renderIngredients(factor);
        renderSops();
        buildPrintArea(factor, people);
    }

    function renderIngredients(factor) {
        const ings = currentRecipe.ingredients;
        ingCount.textContent = pluralise(ings.length, 'item');

        if (!ings.length) {
            ingRows.innerHTML = '<div class="kc-placeholder"><div class="kc-placeholder-icon"><i class="bi bi-egg-fried"></i></div><p>No ingredients recorded for this recipe.</p></div>';
            return;
        }

        if (viewMode === 'detailed') {
            ingRows.innerHTML = ings.map(ing => {
                const scaled = ing.is_scalable
                    ? formatQty(ing.quantity_per_batch * factor) + (ing.unit ? ' ' + ing.unit : '')
                    : (ing.quantity_note || 'As Required');
                const isVerbatim = !ing.is_scalable;
                return `<div class="kc-ing-row">
                    <div>
                        <span class="kc-ing-name">${escHtml(ing.ingredient_name)}</span>
                        ${ing.is_optional ? '<span class="kc-ing-optional-tag">optional</span>' : ''}
                        ${ing.prep_note ? '<span class="kc-ing-name-note">' + escHtml(ing.prep_note) + '</span>' : ''}
                    </div>
                    <div class="kc-ing-qty${isVerbatim ? ' --verbatim' : ''}">${escHtml(scaled)}</div>
                </div>`;
            }).join('');
        } else {
            ingRows.innerHTML = ings.map(ing => {
                if (ing.is_scalable) {
                    const qty  = formatQty(ing.quantity_per_batch * factor);
                    const unit = ing.unit ? ing.unit + ' ' : '';
                    return `<div class="kc-ing-compact">
                        <span class="kc-cqty">${escHtml(qty)}</span>
                        <span class="kc-cunit">${escHtml(unit)}</span>
                        <span class="kc-cname">${escHtml(ing.ingredient_name)}</span>
                        ${ing.is_optional ? '<span class="kc-ing-optional-tag" style="margin-left:6px">opt</span>' : ''}
                    </div>`;
                } else {
                    const note = ing.quantity_note || 'As Required';
                    return `<div class="kc-ing-compact">
                        <span class="kc-cverbatim">${escHtml(note)}</span>
                        <span class="kc-cname">${escHtml(ing.ingredient_name)}</span>
                        ${ing.is_optional ? '<span class="kc-ing-optional-tag" style="margin-left:6px">opt</span>' : ''}
                    </div>`;
                }
            }).join('');
        }
    }

    function renderSops() {
        const sops = currentRecipe.sops;
        if (!sops || !sops.length) {
            sopSec.style.display = 'none';
            return;
        }

        sopCount.textContent = pluralise(sops.length, 'step');
        sopSec.style.display = 'block';

        sopBody.innerHTML = sops.map(sop => `
            <div class="kc-sop-step">
                <div class="kc-sop-num">${sop.step_number}</div>
                <div>
                    <div class="kc-sop-title">${escHtml(sop.title)}</div>
                    <p class="kc-sop-inst">${escHtml(sop.instruction)}</p>
                    ${sop.duration_minutes ? '<div class="kc-sop-dur"><i class="bi bi-clock"></i>' + sop.duration_minutes + ' min</div>' : ''}
                </div>
            </div>
        `).join('');
    }

    /* ── SOP expand/collapse ───────────────────────────────── */
    sopToggle.addEventListener('click', () => {
        sopOpen = !sopOpen;
        sopToggle.classList.toggle('--open', sopOpen);
        sopBody.classList.toggle('--open', sopOpen);
        sopToggle.setAttribute('aria-expanded', sopOpen);
    });

    /* ── View mode toggle ──────────────────────────────────── */
    modeDet.addEventListener('click', () => setMode('detailed'));
    modeCom.addEventListener('click', () => setMode('compact'));

    function setMode(mode) {
        viewMode = mode;
        modeDet.classList.toggle('--active', mode === 'detailed');
        modeCom.classList.toggle('--active', mode === 'compact');
        if (currentRecipe && currentPeople) {
            const factor = currentPeople / currentRecipe.yield_per_batch;
            renderIngredients(factor);
        }
    }

    /* ── Print ─────────────────────────────────────────────── */
    function buildPrintArea(factor, people) {
        document.getElementById('kcp-recipe').textContent = currentRecipe.name;
        document.getElementById('kcp-people').textContent = people;
        document.getElementById('kcp-time').textContent = new Date().toLocaleString('en-IN', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: true
        });

        // Ingredients
        const ings = currentRecipe.ingredients;
        document.getElementById('kcp-ings').innerHTML = ings.map(ing => {
            if (ing.is_scalable) {
                const qty  = formatQty(ing.quantity_per_batch * factor);
                const unit = ing.unit ? ' ' + ing.unit : '';
                return `<div class="kc-print-ing-row">
                    <span class="kc-print-ing-name">${escHtml(ing.ingredient_name)}${ing.prep_note ? ' <em>(' + escHtml(ing.prep_note) + ')</em>' : ''}</span>
                    <span class="kc-print-ing-qty">${escHtml(qty + unit)}</span>
                </div>`;
            } else {
                const note = ing.quantity_note || 'As Required';
                return `<div class="kc-print-ing-row">
                    <span class="kc-print-ing-name">${escHtml(ing.ingredient_name)}${ing.prep_note ? ' <em>(' + escHtml(ing.prep_note) + ')</em>' : ''}</span>
                    <span class="kc-print-ing-verbatim">${escHtml(note)}</span>
                </div>`;
            }
        }).join('');

        // SOPs
        const sops = currentRecipe.sops;
        const sopWrap = document.getElementById('kcp-sop-wrap');
        if (sops && sops.length) {
            sopWrap.style.display = 'block';
            document.getElementById('kcp-sops').innerHTML = sops.map(sop => `
                <div class="kc-print-sop-step">
                    <div class="kc-print-sop-num">${sop.step_number}</div>
                    <div>
                        <div class="kc-print-sop-title">${escHtml(sop.title)}</div>
                        <div class="kc-print-sop-inst">${escHtml(sop.instruction)}</div>
                        ${sop.duration_minutes ? '<div class="kc-print-sop-dur">' + sop.duration_minutes + ' min</div>' : ''}
                    </div>
                </div>
            `).join('');
        } else {
            sopWrap.style.display = 'none';
        }
    }

    printBtn.addEventListener('click', () => {
        if (!currentRecipe || !currentPeople) {
            showError('Calculate first before printing.');
            return;
        }
        // Make print area visible before calling print (hidden with display:none fails in some browsers)
        const pa = document.getElementById('kcPrintArea');
        pa.style.display = 'block';
        window.print();
        pa.style.display = 'none';
    });

    /* ── Reset ─────────────────────────────────────────────── */
    resetBtn.addEventListener('click', () => {
        currentRecipe   = null;
        currentPeople   = 0;
        peopleIn.value  = '';
        hideError();
        results.classList.remove('--show');
        presets.forEach(b => b.classList.remove('--active'));
        sopOpen = false;
        sopToggle.classList.remove('--open');
        sopBody.classList.remove('--open');
        comboClearSelection();
    });

    /* ── Escape helper ─────────────────────────────────────── */
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
</script>
@endpush

</x-admin-layout>
