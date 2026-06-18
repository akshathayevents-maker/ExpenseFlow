<style>
/* ═══════════════════════════════════════════════════════════════
   MENU COMPOSER — styles
   ═══════════════════════════════════════════════════════════════ */
:root {
    --mc-gold:      #a0723a;
    --mc-gold-hi:   #b8832a;
    --mc-gold-glow: rgba(160,114,58,.18);
    --mc-gold-dim:  rgba(160,114,58,.10);
    --mc-green:     #0f7b5f;
    --mc-surface:   #fff;
    --mc-page:      #f7f5f2;
    --mc-border:    #e8e2d8;
    --mc-border-hi: #cfc5b2;
    --mc-ink:       #1c1712;
    --mc-muted:     #7a6e62;
    --mc-faint:     #c0b8ac;
    --mc-radius:    18px;
    --mc-r-md:      14px;
    --mc-r-sm:      10px;
    --mc-r-xs:      7px;
    --mc-lib-w:     300px;
}

/* ── Two-panel layout ────────────────────────────────────────── */
.mc-layout {
    display: flex;
    gap: 0;
    align-items: flex-start;
}

/* ── Left: Library ───────────────────────────────────────────── */
.mc-library {
    width: var(--mc-lib-w);
    flex-shrink: 0;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    border-right: 1.5px solid var(--mc-border);
    background: #faf8f5;
    display: flex;
    flex-direction: column;
}
.mc-lib-hdr {
    padding: 14px 16px 12px;
    border-bottom: 1px solid var(--mc-border);
    background: var(--mc-surface);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.mc-lib-title {
    font-size: .82rem; font-weight: 800;
    letter-spacing: .07em; text-transform: uppercase;
    color: var(--mc-muted); flex: 1;
}
.mc-lib-count-badge {
    font-size: .74rem; font-weight: 700;
    color: var(--mc-gold); background: var(--mc-gold-dim);
    border-radius: 999px; padding: 2px 8px;
}
.mc-lib-search-wrap {
    padding: 10px 12px;
    background: var(--mc-surface);
    border-bottom: 1px solid var(--mc-border);
    flex-shrink: 0;
    position: relative;
}
.mc-lib-search-ico {
    position: absolute; left: 22px; top: 50%; transform: translateY(-50%);
    color: var(--mc-faint); font-size: .85rem; pointer-events: none;
}
.mc-lib-search {
    width: 100%; padding: 8px 32px 8px 32px;
    border: 1.5px solid var(--mc-border); border-radius: var(--mc-r-sm);
    font-size: .88rem; background: #faf8f5; color: var(--mc-ink);
    box-sizing: border-box; transition: border-color .12s, box-shadow .12s;
}
.mc-lib-search:focus {
    outline: none; border-color: var(--mc-gold);
    box-shadow: 0 0 0 3px var(--mc-gold-glow);
    background: var(--mc-surface);
}
.mc-lib-search-clear {
    position: absolute; right: 22px; top: 50%; transform: translateY(-50%);
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--mc-faint); border: none; color: #fff;
    font-size: .75rem; cursor: pointer; display: none;
    align-items: center; justify-content: center; padding: 0;
}
.mc-lib-search-clear.show { display: flex; }
.mc-lib-body {
    flex: 1; overflow-y: auto; padding: 6px 0 80px;
}

/* ── Library active-section hint ─────────────────────────────── */
.mc-lib-hint {
    margin: 10px 12px;
    padding: 9px 12px;
    background: rgba(160,114,58,.08);
    border: 1px dashed rgba(160,114,58,.35);
    border-radius: var(--mc-r-sm);
    font-size: .78rem;
    color: var(--mc-gold);
    font-weight: 600;
    text-align: center;
    line-height: 1.4;
}
.mc-lib-hint.--ok {
    background: rgba(15,123,95,.07);
    border-color: rgba(15,123,95,.3);
    color: var(--mc-green);
}

/* ── Category group ──────────────────────────────────────────── */
.mc-lib-group { border-bottom: 1px solid var(--mc-border); }
.mc-lib-group-btn {
    width: 100%; display: flex; align-items: center; gap: 8px;
    padding: 10px 14px; background: none; border: none;
    font-size: .8rem; font-weight: 700; color: var(--mc-ink);
    cursor: pointer; text-align: left; transition: background .1s;
}
.mc-lib-group-btn:hover { background: rgba(160,114,58,.06); }
.mc-lib-group-name { flex: 1; }
.mc-lib-group-count {
    font-size: .72rem; font-weight: 700;
    color: var(--mc-muted); background: #f0ece6;
    border-radius: 999px; padding: 1px 7px; flex-shrink: 0;
}
.mc-lib-group-chevron {
    color: var(--mc-faint); font-size: .75rem; flex-shrink: 0;
    transition: transform .15s;
}
.mc-lib-group.--open .mc-lib-group-chevron { transform: rotate(90deg); }
.mc-lib-group-items { display: none; }
.mc-lib-group.--open .mc-lib-group-items { display: block; }

/* ── Library item ────────────────────────────────────────────── */
.mc-lib-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px 8px 24px;
    cursor: pointer; transition: background .08s;
    border-bottom: 1px solid #f3ede4;
}
.mc-lib-item:last-child { border-bottom: none; }
.mc-lib-item:hover { background: var(--mc-gold-dim); }
.mc-lib-item:active { background: rgba(160,114,58,.18); }
.mc-lib-item-names { flex: 1; min-width: 0; }
.mc-lib-item-en {
    font-size: .88rem; font-weight: 600; color: var(--mc-ink);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.mc-lib-item-ta { font-size: .8rem; color: var(--mc-muted); }
.mc-lib-item-add {
    flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%;
    background: var(--mc-gold-dim); border: 1.5px solid rgba(160,114,58,.3);
    color: var(--mc-gold); font-size: .8rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    transition: all .1s;
}
.mc-lib-item:hover .mc-lib-item-add { background: var(--mc-gold); color: #fff; border-color: var(--mc-gold); }
.mc-lib-empty {
    padding: 24px 16px; text-align: center;
    color: var(--mc-faint); font-size: .85rem;
}

/* ── Mobile library toggle ───────────────────────────────────── */
.mc-lib-fab {
    display: none; position: fixed;
    bottom: calc(80px + env(safe-area-inset-bottom, 0px));
    right: 16px; z-index: 1050;
    width: 48px; height: 48px; border-radius: 50%;
    background: var(--mc-gold); color: #fff; border: none;
    box-shadow: 0 4px 16px rgba(160,114,58,.4);
    font-size: 1.1rem; cursor: pointer; align-items: center; justify-content: center;
    transition: background .12s;
}
.mc-lib-fab:hover { background: var(--mc-gold-hi); }
.mc-lib-overlay {
    display: none; position: fixed; inset: 0; z-index: 1200;
    background: rgba(28,23,18,.5); backdrop-filter: blur(3px);
}
.mc-lib-overlay.open { display: block; }
.mc-lib-drawer {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 1210;
    height: 72dvh; background: #faf8f5;
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -8px 40px rgba(0,0,0,.2);
    display: flex; flex-direction: column;
    transform: translateY(100%); transition: transform .28s cubic-bezier(.32,.72,0,1);
}
.mc-lib-drawer.open { transform: translateY(0); }
.mc-lib-drawer-hdr {
    padding: 12px 16px 10px; border-bottom: 1px solid var(--mc-border);
    display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.mc-lib-drawer-close {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1.5px solid var(--mc-border); background: none;
    font-size: 1rem; cursor: pointer; color: var(--mc-muted);
    display: flex; align-items: center; justify-content: center;
}
.mc-lib-drawer-body { flex: 1; overflow-y: auto; }

/* ── Right: Composer ─────────────────────────────────────────── */
.mc-shell {
    flex: 1; min-width: 0;
    padding: 0 20px 160px 20px;
    max-width: 860px;
}

/* ── Page header ─────────────────────────────────────────────── */
.mc-back {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .82rem; font-weight: 600; color: var(--mc-muted);
    text-decoration: none; padding: 8px 0; transition: color .12s;
}
.mc-back:hover { color: var(--mc-gold); }
.mc-save-status {
    margin-left: auto; font-size: .8rem; color: var(--mc-muted);
    display: flex; align-items: center; gap: 5px;
    opacity: 0; transition: opacity .3s;
}
.mc-save-status.show { opacity: 1; }
.mc-save-status .dot {
    width: 6px; height: 6px; border-radius: 50%; background: var(--mc-faint);
}
.mc-save-status.saving .dot { background: var(--mc-gold); animation: mc-pulse .8s infinite; }
.mc-save-status.saved  .dot { background: #16a34a; }
.mc-save-status.error  .dot { background: #dc2626; }
@keyframes mc-pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

/* ── Meta card ───────────────────────────────────────────────── */
.mc-meta-card {
    background: var(--mc-surface); border: 1.5px solid var(--mc-border);
    border-radius: var(--mc-radius); padding: 20px 20px 16px;
    margin-bottom: 16px;
}
.mc-meta-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.mc-meta-row.full { grid-template-columns: 1fr; }
.mc-field-lbl {
    display: block; font-size: .72rem; font-weight: 800;
    letter-spacing: .08em; text-transform: uppercase;
    color: var(--mc-muted); margin-bottom: 5px;
}
.mc-opt { font-weight: 400; text-transform: none; letter-spacing: 0; font-size: .85em; }
.mc-input {
    width: 100%; padding: 10px 14px;
    border: 1.5px solid var(--mc-border); border-radius: var(--mc-r-sm);
    font-size: .95rem; background: var(--mc-surface); color: var(--mc-ink);
    transition: border-color .12s; box-sizing: border-box;
}
.mc-input:focus { outline: none; border-color: var(--mc-gold); }
.mc-input.--title { font-size: 1.1rem; font-weight: 700; }

/* ── Empty state ─────────────────────────────────────────────── */
.mc-empty-sections {
    text-align: center; padding: 48px 24px;
    background: var(--mc-surface); border: 2px dashed var(--mc-border);
    border-radius: var(--mc-radius); margin-bottom: 16px; color: var(--mc-muted);
}
.mc-empty-icon { font-size: 2.5rem; color: var(--mc-faint); display: block; margin-bottom: 10px; }
.mc-empty-p { font-size: 1rem; font-weight: 700; color: var(--mc-ink); margin-bottom: 4px; }
.mc-empty-sub { font-size: .88rem; }

/* ── Add Section button ──────────────────────────────────────── */
.mc-add-section-wrap { text-align: center; margin-bottom: 16px; }
.mc-add-section-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px; border-radius: 999px;
    border: 2px dashed var(--mc-gold); background: transparent;
    color: var(--mc-gold); font-size: .95rem; font-weight: 700;
    cursor: pointer; transition: all .15s;
}
.mc-add-section-btn:hover { background: var(--mc-gold-dim); border-style: solid; }

/* ── Section card ────────────────────────────────────────────── */
.mc-section {
    background: var(--mc-surface); border: 1.5px solid var(--mc-border);
    border-radius: var(--mc-radius); margin-bottom: 14px; overflow: visible;
    transition: box-shadow .15s, border-color .15s;
    cursor: default;
}
.mc-section--dragging {
    opacity: .5; box-shadow: 0 8px 32px rgba(160,114,58,.22);
}
.mc-section--dragover {
    border-color: var(--mc-gold); box-shadow: 0 0 0 3px var(--mc-gold-glow);
}
/* Active section: gold left border + subtle glow */
.mc-section--active {
    border-color: var(--mc-gold);
    box-shadow: 0 0 0 3px var(--mc-gold-glow), 0 2px 12px rgba(160,114,58,.1);
}
.mc-section--active .mc-section-hdr {
    background: rgba(160,114,58,.07);
}
/* Item drop zone highlight */
.mc-section--item-drop {
    border-color: var(--mc-green) !important;
    box-shadow: 0 0 0 3px rgba(15,123,95,.15) !important;
}

.mc-section-hdr {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 14px 10px; border-bottom: 1px solid var(--mc-border);
    background: #faf8f5; border-radius: var(--mc-radius) var(--mc-radius) 0 0;
    transition: background .2s; flex-wrap: wrap;
}
.mc-drag-handle {
    cursor: grab; color: var(--mc-faint); font-size: 1.1rem;
    padding: 2px 4px; flex-shrink: 0; line-height: 1; touch-action: none;
}
.mc-drag-handle:active { cursor: grabbing; }
.mc-section-icon { font-size: 1.05rem; color: var(--mc-gold); flex-shrink: 0; }
.mc-section-title-wrap { flex: 1; min-width: 0; }
.mc-section-title { font-size: .98rem; font-weight: 800; color: var(--mc-ink); display: block; }
.mc-section-title-ta { font-size: .78rem; color: var(--mc-muted); display: block; font-weight: 400; }
.mc-section-count {
    font-size: .72rem; color: var(--mc-muted); background: var(--mc-gold-dim);
    border-radius: 999px; padding: 3px 10px; font-weight: 600; flex-shrink: 0;
    white-space: nowrap;
}
.mc-section-actions { display: flex; gap: 4px; flex-shrink: 0; }
.mc-sec-btn {
    width: 30px; height: 30px; border-radius: var(--mc-r-xs);
    border: 1.5px solid var(--mc-border); background: transparent;
    color: var(--mc-muted); cursor: pointer; font-size: .85rem;
    display: flex; align-items: center; justify-content: center;
    transition: all .1s; -webkit-tap-highlight-color: transparent;
}
.mc-sec-btn:hover { border-color: var(--mc-gold); color: var(--mc-gold); background: var(--mc-gold-dim); }
.mc-sec-btn.--del:hover { border-color: #dc2626; color: #dc2626; background: #fee2e2; }

/* ── Per-section people count ────────────────────────────────── */
.mc-people-wrap {
    display: flex; align-items: center; gap: 5px;
    background: rgba(160,114,58,.07); border: 1px solid rgba(160,114,58,.2);
    border-radius: var(--mc-r-xs); padding: 3px 8px; flex-shrink: 0;
}
.mc-people-input {
    width: 64px; border: none; background: transparent;
    font-size: .82rem; font-weight: 700; color: var(--mc-ink);
    text-align: right; padding: 0;
}
.mc-people-input:focus { outline: none; }
.mc-people-input::placeholder { color: var(--mc-faint); font-weight: 400; }
.mc-people-lbl {
    font-size: .74rem; color: var(--mc-muted); font-weight: 600; white-space: nowrap;
}

/* ── Items inside a section ──────────────────────────────────── */
.mc-items-body { padding: 0 16px; }
.mc-items-body-inner { min-height: 10px; }
.mc-items-empty {
    padding: 20px 0; text-align: center; color: var(--mc-faint); font-size: .87rem;
}
.mc-cat-group { padding: 12px 0 4px; }
.mc-cat-label {
    font-size: .7rem; font-weight: 800; letter-spacing: .09em;
    text-transform: uppercase; color: var(--mc-gold);
    margin-bottom: 5px; display: flex; align-items: center; gap: 6px;
}
.mc-cat-label-ta {
    font-weight: 400; color: var(--mc-muted); letter-spacing: 0;
    text-transform: none; font-size: .8rem;
}

/* ── Item row ────────────────────────────────────────────────── */
.mc-item-row {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 0; border-bottom: 1px solid #f3ede4;
    transition: background .08s; position: relative;
}
.mc-item-row:last-child { border-bottom: 0; }
.mc-item-row:hover { background: #fdfcfa; }
.mc-item-row.mc-item--dragging { opacity: .4; }
.mc-item-row.mc-item--dragover-before::before {
    content: ''; position: absolute; top: -1px; left: 0; right: 0;
    height: 2px; background: var(--mc-gold); border-radius: 1px;
}
.mc-item-drag {
    cursor: grab; color: var(--mc-faint); font-size: .95rem;
    padding: 2px 3px; flex-shrink: 0; line-height: 1; touch-action: none;
}
.mc-item-drag:active { cursor: grabbing; }
.mc-item-names { flex: 1; min-width: 0; }
.mc-item-en { font-size: .92rem; font-weight: 600; color: var(--mc-ink); }
.mc-item-ta { font-size: .84rem; color: var(--mc-muted); }
.mc-item-actions { display: flex; gap: 3px; flex-shrink: 0; }
.mc-item-remove {
    flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%;
    border: 1.5px solid #e5e0d8; background: transparent; color: var(--mc-muted);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .9rem; transition: all .1s;
    -webkit-tap-highlight-color: transparent;
}
.mc-item-remove:hover { border-color: #dc2626; color: #dc2626; background: #fee2e2; }

/* ── Move-to dropdown ────────────────────────────────────────── */
.mc-item-moveto { position: relative; }
.mc-item-moveto-btn {
    width: 26px; height: 26px; border-radius: 50%;
    border: 1.5px solid #e5e0d8; background: transparent; color: var(--mc-muted);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .78rem; transition: all .1s;
    -webkit-tap-highlight-color: transparent;
}
.mc-item-moveto-btn:hover { border-color: var(--mc-gold); color: var(--mc-gold); background: var(--mc-gold-dim); }
.mc-moveto-menu {
    display: none; position: absolute; right: 0; bottom: calc(100% + 4px);
    background: var(--mc-surface); border: 1.5px solid var(--mc-border);
    border-radius: var(--mc-r-md); box-shadow: 0 8px 28px rgba(0,0,0,.14);
    z-index: 300; min-width: 160px; padding: 4px;
}
.mc-moveto-menu.open { display: block; }
.mc-moveto-item {
    display: block; width: 100%; padding: 8px 12px; background: none; border: none;
    font-size: .83rem; font-weight: 600; color: var(--mc-ink); text-align: left;
    border-radius: var(--mc-r-xs); cursor: pointer; transition: background .08s;
}
.mc-moveto-item:hover { background: var(--mc-gold-dim); color: var(--mc-gold); }

/* ── Add item search ─────────────────────────────────────────── */
.mc-add-wrap { padding: 10px 16px 14px; }
.mc-add-inner { position: relative; }
.mc-add-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: var(--mc-faint); font-size: .9rem; pointer-events: none;
    transition: color .12s;
}
.mc-add-input {
    width: 100%; padding: 9px 34px 9px 34px;
    border: 1.5px solid var(--mc-border); border-radius: var(--mc-r-md);
    font-size: .9rem; background: #faf8f5; color: var(--mc-ink);
    transition: border-color .12s, background .12s, box-shadow .12s;
    box-sizing: border-box;
}
.mc-add-input:focus {
    outline: none; border-color: var(--mc-gold);
    background: var(--mc-surface);
    box-shadow: 0 0 0 3px var(--mc-gold-glow);
}
.mc-add-clear {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--mc-faint); border: none; color: #fff;
    font-size: .8rem; cursor: pointer; display: none;
    align-items: center; justify-content: center;
}
.mc-add-clear.show { display: flex; }

/* ── Search results dropdown ─────────────────────────────────── */
.mc-results {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: var(--mc-surface); border: 1.5px solid var(--mc-border);
    border-radius: var(--mc-r-md); box-shadow: 0 8px 32px rgba(0,0,0,.12);
    z-index: 200; max-height: 260px; overflow-y: auto; display: none;
}
.mc-results.open { display: block; }
.mc-result-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; cursor: pointer;
    border-bottom: 1px solid #f5f1eb; transition: background .08s;
}
.mc-result-item:last-child { border-bottom: 0; }
.mc-result-item:hover, .mc-result-item.focused { background: var(--mc-gold-dim); }
.mc-result-item.mc-result--added { opacity: .6; }
.mc-result-cat {
    font-size: .7rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: var(--mc-gold);
    padding: 2px 7px; background: var(--mc-gold-dim);
    border-radius: 999px; white-space: nowrap; flex-shrink: 0;
}
.mc-result-names { flex: 1; min-width: 0; }
.mc-result-en { font-size: .9rem; font-weight: 600; color: var(--mc-ink); }
.mc-result-ta { font-size: .82rem; color: var(--mc-muted); }
.mc-result-added-badge { font-size: .72rem; color: #16a34a; font-weight: 700; flex-shrink: 0; }
.mc-result-empty { padding: 16px; text-align: center; color: var(--mc-faint); font-size: .87rem; }

/* ── Section picker modal ────────────────────────────────────── */
.mc-section-picker { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.mc-picker-btn {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 12px 8px 10px; border-radius: var(--mc-r-md);
    border: 1.5px solid var(--mc-border); background: #faf8f5;
    cursor: pointer; transition: all .12s; text-align: center;
}
.mc-picker-btn:hover { border-color: var(--mc-gold); background: var(--mc-gold-dim); }
.mc-picker-icon { font-size: 1.4rem; color: var(--mc-gold); }
.mc-picker-en { font-size: .8rem; font-weight: 700; color: var(--mc-ink); line-height: 1.2; }
.mc-picker-ta { font-size: .74rem; color: var(--mc-muted); }

/* ── Modals ──────────────────────────────────────────────────── */
.mc-modal-overlay {
    position: fixed; inset: 0; z-index: 1100;
    background: rgba(28,23,18,.6); backdrop-filter: blur(4px);
    display: flex; align-items: flex-end; justify-content: center;
    padding: 0 0 env(safe-area-inset-bottom, 0px);
}
@media (min-width: 600px) { .mc-modal-overlay { align-items: center; padding: 20px; } }
.mc-modal-box {
    background: var(--mc-surface); border-radius: var(--mc-radius) var(--mc-radius) 0 0;
    width: 100%; max-width: 520px; max-height: 85dvh; overflow-y: auto;
    box-shadow: 0 -8px 40px rgba(0,0,0,.2);
}
@media (min-width: 600px) { .mc-modal-box { border-radius: var(--mc-radius); box-shadow: 0 24px 64px rgba(0,0,0,.22); } }
.mc-modal-hdr {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 16px; border-bottom: 1px solid var(--mc-border);
    position: sticky; top: 0; background: var(--mc-surface); z-index: 1;
}
.mc-modal-title { font-size: 1rem; font-weight: 800; color: var(--mc-ink); }
.mc-modal-close {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1.5px solid var(--mc-border); background: transparent;
    font-size: 1.1rem; cursor: pointer; color: var(--mc-muted);
    display: flex; align-items: center; justify-content: center; transition: all .1s;
}
.mc-modal-close:hover { background: #fee2e2; border-color: #dc2626; color: #dc2626; }
.mc-modal-body { padding: 18px 20px 24px; }
.mc-modal-confirm-btn {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 12px 20px; border-radius: var(--mc-r-sm);
    background: var(--mc-gold); color: #fff; border: none;
    font-size: .95rem; font-weight: 700; cursor: pointer; transition: background .12s;
}
.mc-modal-confirm-btn:hover { background: var(--mc-gold-hi); }

/* ── Bottom sticky bar ───────────────────────────────────────── */
.mc-action-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 1040;
    background: rgba(28,23,18,.97);
    backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255,255,255,.08);
    padding: 12px 20px calc(12px + env(safe-area-inset-bottom, 0px));
    display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;
}
.mc-bar-save {
    padding: 10px 20px; background: rgba(255,255,255,.1);
    color: rgba(255,255,255,.75); border: 1.5px solid rgba(255,255,255,.15);
    border-radius: var(--mc-r-sm); font-size: .88rem; font-weight: 700;
    cursor: pointer; transition: all .12s; white-space: nowrap;
}
.mc-bar-save:hover { background: rgba(255,255,255,.18); color: #fff; }
.mc-bar-copy {
    padding: 10px 20px; background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.7); border: 1.5px solid rgba(255,255,255,.12);
    border-radius: var(--mc-r-sm); font-size: .88rem; font-weight: 700;
    cursor: pointer; transition: all .12s; white-space: nowrap;
}
.mc-bar-copy:hover { background: rgba(255,255,255,.15); color: #fff; }
.mc-bar-tpl {
    padding: 10px 16px; background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.65); border: 1.5px solid rgba(255,255,255,.1);
    border-radius: var(--mc-r-sm); font-size: .88rem; font-weight: 700;
    cursor: pointer; transition: all .12s; white-space: nowrap;
}
.mc-bar-tpl:hover { background: rgba(255,255,255,.14); color: #fff; }
.mc-bar-pdf-group { display: flex; gap: 6px; flex-wrap: wrap; }
.mc-bar-pdf-split { display: flex; }
.mc-bar-pdf {
    padding: 10px 14px; background: var(--mc-gold); color: #fff; border: none;
    border-radius: var(--mc-r-sm) 0 0 var(--mc-r-sm); font-size: .88rem; font-weight: 700;
    cursor: pointer; transition: background .12s; white-space: nowrap;
}
.mc-bar-pdf-split > .mc-bar-pdf:only-child { border-radius: var(--mc-r-sm); }
.mc-bar-pdf:hover { background: var(--mc-gold-hi); }
.mc-bar-pdf--ta  { background: #1d4ed8; }
.mc-bar-pdf--ta:hover { background: #1e40af; }
.mc-bar-pdf--bi  { background: #0f7b5f; }
.mc-bar-pdf--bi:hover { background: #0a6b50; }
.mc-bar-pdf-caret {
    padding: 10px 8px; border-left: 1px solid rgba(255,255,255,.3);
    border-radius: 0 var(--mc-r-sm) var(--mc-r-sm) 0;
}
.mc-pdf-dropdown { min-width: 190px; font-size: .88rem; }
.mc-pdf-dropdown .dropdown-header { font-size: .75rem; color: #7a6e62; }
.mc-pdf-dropdown .dropdown-item { padding: 7px 14px; }

/* ── Toast ───────────────────────────────────────────────────── */
.mc-toast {
    position: fixed; top: 80px; left: 50%; transform: translateX(-50%) translateY(-20px);
    background: rgba(28,23,18,.9); color: #fff;
    padding: 10px 20px; border-radius: 999px; font-size: .88rem;
    z-index: 2000; opacity: 0; transition: opacity .2s, transform .2s;
    pointer-events: none; white-space: nowrap;
}
.mc-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.mc-toast--error { background: rgba(185,28,28,.93); }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .mc-library { display: none; }
    .mc-lib-fab  { display: flex; }
    .mc-shell    { padding: 0 12px 160px; max-width: 100%; }
    .mc-layout   { display: block; }
}
@media (max-width: 767.98px) {
    .mc-meta-row { grid-template-columns: 1fr; }
    .mc-section-picker { grid-template-columns: repeat(2, 1fr); }
    .mc-action-bar {
        bottom: var(--mn-height, 0px);
        padding-bottom: 12px;
        z-index: 1050;
        gap: 6px;
    }
    .mc-bar-pdf { padding: 9px 12px; font-size: .82rem; }
    .mc-bar-save, .mc-bar-copy { padding: 9px 14px; font-size: .82rem; }
    .mc-shell { padding-bottom: calc(var(--ef-mobile-nav-height, 0px) + 110px + env(safe-area-inset-bottom, 0px)); }
}
@media (max-width: 419.98px) {
    .mc-section-picker { grid-template-columns: repeat(2, 1fr); }
    .mc-bar-pdf-group { width: 100%; justify-content: center; }
    .mc-bar-pdf { flex: 1; text-align: center; }
}
</style>
