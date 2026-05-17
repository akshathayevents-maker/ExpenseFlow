<x-admin-layout title="Approval Operations">
@push('styles')
<style>
/*
 * MANAGER APPROVAL CENTER — ef-mgr-* namespace
 * Role accent: emerald / teal · mirrors platform design system
 */

/* ── Design tokens ─────────────────────────────────────────────── */
:root {
    --mgr-emerald:    #059669;
    --mgr-emerald-hi: #10b981;
    --mgr-teal:       #0d9488;
    --mgr-amber:      #d97706;
    --mgr-danger:     #dc2626;
    --mgr-indigo:     #6366f1;
    --mgr-blue:       #2563eb;
    --mgr-ink:        #0c1a14;
    --mgr-muted:      #64748b;
    --mgr-faint:      #f0fdf4;
    --mgr-border:     rgba(5,150,105,.13);
    --mgr-border-s:   rgba(5,150,105,.28);
    --mgr-shadow:     0 1px 3px rgba(5,30,20,.06),0 4px 12px rgba(5,30,20,.05);
    --mgr-shadow-h:   0 4px 20px rgba(5,30,20,.12),0 1px 4px rgba(5,30,20,.06);
    --mgr-radius:     14px;
    --mgr-ease:       cubic-bezier(.25,.46,.45,.94);
}

/* ── Hero ──────────────────────────────────────────────────────── */
.ef-mgr-hero {
    background: linear-gradient(135deg, #081a0f 0%, #0e2b1c 50%, #0a1f14 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    margin-bottom: 20px;
    overflow: hidden;
    padding: 28px 28px 24px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 24px;
}
.ef-mgr-hero::before {
    background: radial-gradient(circle, rgba(16,185,129,.15) 0%, transparent 65%);
    border-radius: 50%;
    content: "";
    height: 420px;
    pointer-events: none;
    position: absolute;
    right: -80px;
    top: -130px;
    width: 420px;
}
.ef-mgr-hero::after {
    background: radial-gradient(circle, rgba(5,150,105,.09) 0%, transparent 65%);
    bottom: -90px;
    content: "";
    height: 260px;
    left: 15%;
    pointer-events: none;
    position: absolute;
    width: 260px;
    border-radius: 50%;
}
.ef-mgr-hero-main { flex: 1; position: relative; z-index: 1; }
.ef-mgr-eyebrow {
    color: rgba(16,185,129,.85);
    font-size: .65rem;
    font-weight: 760;
    letter-spacing: .18em;
    margin-bottom: 6px;
    text-transform: uppercase;
}
.ef-mgr-hero-title {
    color: #f0fdf8;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
    margin-bottom: 4px;
}
.ef-mgr-hero-sub {
    color: rgba(240,253,248,.48);
    font-size: .83rem;
}
.ef-mgr-hero-stats {
    display: flex;
    gap: 20px;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}
.ef-mgr-hero-stat { text-align: center; }
.ef-mgr-hero-stat-val {
    color: #f0fdf8;
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -.03em;
    line-height: 1;
}
.ef-mgr-hero-stat-val.amber  { color: #fbbf24; }
.ef-mgr-hero-stat-val.danger { color: #f87171; }
.ef-mgr-hero-stat-lbl {
    color: rgba(240,253,248,.42);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    margin-top: 3px;
    text-transform: uppercase;
}
.ef-mgr-hero-divider {
    width: 1px;
    background: rgba(255,255,255,.1);
    align-self: stretch;
    margin: 4px 0;
}

/* ── KPI Strip ─────────────────────────────────────────────────── */
.ef-mgr-kpi-wrap {
    overflow-x: auto;
    scrollbar-width: none;
    margin-bottom: 20px;
}
.ef-mgr-kpi-wrap::-webkit-scrollbar { display: none; }
.ef-mgr-kpi-strip {
    display: grid;
    grid-template-columns: repeat(6, minmax(140px, 1fr));
    gap: 12px;
    min-width: 900px;
}
.ef-mgr-kpi {
    background: #fff;
    border: 1px solid var(--mgr-border);
    border-radius: var(--mgr-radius);
    box-shadow: var(--mgr-shadow);
    padding: 14px 16px;
    position: relative;
    transition: transform .16s var(--mgr-ease), box-shadow .16s var(--mgr-ease);
    text-decoration: none;
    display: block;
}
a.ef-mgr-kpi:hover {
    border-color: var(--mgr-border-s);
    box-shadow: var(--mgr-shadow-h);
    transform: translateY(-2px);
}
.ef-mgr-kpi-icon {
    color: var(--mgr-emerald);
    float: right;
    font-size: .95rem;
    opacity: .5;
}
.ef-mgr-kpi-label {
    color: var(--mgr-muted);
    font-size: .67rem;
    font-weight: 720;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-mgr-kpi-value {
    color: var(--mgr-ink);
    font-size: 1.4rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-mgr-kpi-note {
    color: var(--mgr-muted);
    font-size: .7rem;
    margin-top: 4px;
}
.ef-mgr-kpi-value.amber   { color: var(--mgr-amber); }
.ef-mgr-kpi-value.emerald { color: var(--mgr-emerald); }
.ef-mgr-kpi-value.danger  { color: var(--mgr-danger); }
.ef-mgr-kpi-value.indigo  { color: var(--mgr-indigo); }
.ef-mgr-kpi-value.teal    { color: var(--mgr-teal); }
.ef-mgr-kpi-value.blue    { color: var(--mgr-blue); }

/* pending pulse dot */
.ef-mgr-kpi-pulse {
    animation: mgr-pulse 1.8s ease-in-out infinite;
    background: var(--mgr-amber);
    border-radius: 50%;
    display: inline-block;
    height: 7px;
    margin-right: 5px;
    vertical-align: middle;
    width: 7px;
}
@keyframes mgr-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(217,119,6,.5); }
    50%      { box-shadow: 0 0 0 5px rgba(217,119,6,0); }
}

/* ── Filter toolbar ────────────────────────────────────────────── */
.ef-mgr-toolbar {
    background: #fff;
    border: 1px solid var(--mgr-border);
    border-radius: 16px;
    box-shadow: var(--mgr-shadow);
    margin-bottom: 16px;
    padding: 14px 16px;
}
.ef-mgr-search-wrap {
    position: relative;
    margin-bottom: 12px;
}
.ef-mgr-search-icon {
    color: var(--mgr-muted);
    font-size: .9rem;
    left: 14px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}
.ef-mgr-search {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 50px;
    color: var(--mgr-ink);
    font-size: .88rem;
    outline: none;
    padding: 10px 42px;
    transition: border-color .18s;
    width: 100%;
}
.ef-mgr-search:focus { border-color: var(--mgr-emerald); background: #fff; }
.ef-mgr-search-clear {
    background: none;
    border: none;
    color: var(--mgr-muted);
    cursor: pointer;
    font-size: .85rem;
    padding: 0 14px;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
}
.ef-mgr-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.ef-mgr-chip {
    background: #f1f5f9;
    border: 1.5px solid transparent;
    border-radius: 50px;
    color: var(--mgr-muted);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 700;
    padding: 5px 14px;
    text-decoration: none;
    transition: all .15s var(--mgr-ease);
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.ef-mgr-chip:hover, .ef-mgr-chip.active {
    background: var(--mgr-faint);
    border-color: var(--mgr-emerald);
    color: var(--mgr-emerald);
}
.ef-mgr-chip.active { background: var(--mgr-emerald); color: #fff; border-color: var(--mgr-emerald); }
.ef-mgr-chip-count {
    background: rgba(255,255,255,.3);
    border-radius: 50px;
    font-size: .65rem;
    padding: 1px 6px;
    font-weight: 800;
}
.ef-mgr-chip-danger.active  { background: var(--mgr-danger); border-color: var(--mgr-danger); }
.ef-mgr-chip-amber.active   { background: var(--mgr-amber);  border-color: var(--mgr-amber); }
.ef-mgr-filter-adv {
    background: none;
    border: 1.5px solid var(--mgr-border);
    border-radius: 50px;
    color: var(--mgr-muted);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 700;
    margin-left: auto;
    padding: 5px 14px;
    transition: all .15s;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.ef-mgr-filter-adv:hover { border-color: var(--mgr-emerald); color: var(--mgr-emerald); }
.ef-mgr-filter-adv.has-filter { background: var(--mgr-faint); border-color: var(--mgr-emerald); color: var(--mgr-emerald); }

/* ── Advanced filter drawer ────────────────────────────────────── */
.ef-mgr-adv-drawer {
    background: #f8fafc;
    border-top: 1px solid var(--mgr-border);
    display: none;
    margin: 12px -16px -14px;
    padding: 16px 16px 14px;
}
.ef-mgr-adv-drawer.open { display: block; }
.ef-mgr-adv-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr auto;
    gap: 10px;
    align-items: end;
}
.ef-mgr-adv-label {
    color: var(--mgr-muted);
    font-size: .72rem;
    font-weight: 720;
    letter-spacing: .04em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-mgr-adv-select, .ef-mgr-adv-date {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    color: var(--mgr-ink);
    font-size: .83rem;
    outline: none;
    padding: 8px 10px;
    transition: border-color .15s;
    width: 100%;
}
.ef-mgr-adv-select:focus, .ef-mgr-adv-date:focus { border-color: var(--mgr-emerald); }
.ef-mgr-adv-actions { display: flex; gap: 8px; }
.ef-mgr-adv-apply {
    background: var(--mgr-emerald);
    border: none;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    font-size: .82rem;
    font-weight: 700;
    padding: 8px 16px;
    white-space: nowrap;
}
.ef-mgr-adv-reset {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    color: var(--mgr-muted);
    cursor: pointer;
    font-size: .82rem;
    font-weight: 700;
    padding: 8px 12px;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
}

/* ── Results meta bar ──────────────────────────────────────────── */
.ef-mgr-meta {
    align-items: center;
    color: var(--mgr-muted);
    display: flex;
    font-size: .8rem;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 12px;
}
.ef-mgr-meta b { color: var(--mgr-ink); font-weight: 800; }
.ef-mgr-sort {
    background: none;
    border: 1.5px solid var(--mgr-border);
    border-radius: 8px;
    color: var(--mgr-muted);
    cursor: pointer;
    font-size: .76rem;
    font-weight: 700;
    padding: 5px 10px;
}

/* ── Request card ──────────────────────────────────────────────── */
.ef-mgr-card-list { display: flex; flex-direction: column; gap: 10px; }

.ef-mgr-req-card {
    background: #fff;
    border: 1px solid var(--mgr-border);
    border-left: 4px solid transparent;
    border-radius: var(--mgr-radius);
    box-shadow: var(--mgr-shadow);
    overflow: hidden;
    transition: box-shadow .16s var(--mgr-ease), transform .16s var(--mgr-ease);
}
.ef-mgr-req-card:hover { box-shadow: var(--mgr-shadow-h); transform: translateY(-1px); }

/* status left stripe */
.ef-mgr-req-card[data-status="pending"]    { border-left-color: var(--mgr-amber); }
.ef-mgr-req-card[data-status="approved"]   { border-left-color: var(--mgr-emerald); }
.ef-mgr-req-card[data-status="rejected"]   { border-left-color: var(--mgr-danger); }
.ef-mgr-req-card[data-status="paid"]       { border-left-color: var(--mgr-blue); }
.ef-mgr-req-card[data-status="completed"]  { border-left-color: var(--mgr-teal); }
.ef-mgr-req-card[data-status="reimbursed"] { border-left-color: var(--mgr-indigo); }

.ef-mgr-req-body {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
}
.ef-mgr-avatar {
    align-items: center;
    background: var(--mgr-faint);
    border-radius: 50%;
    color: var(--mgr-emerald);
    display: flex;
    flex-shrink: 0;
    font-size: .72rem;
    font-weight: 800;
    height: 40px;
    justify-content: center;
    letter-spacing: .04em;
    text-transform: uppercase;
    width: 40px;
}
.ef-mgr-req-main { flex: 1; min-width: 0; }
.ef-mgr-req-employee {
    color: var(--mgr-muted);
    font-size: .74rem;
    font-weight: 700;
    margin-bottom: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-mgr-req-title {
    color: var(--mgr-ink);
    font-size: .92rem;
    font-weight: 750;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-bottom: 4px;
}
.ef-mgr-req-chips { display: flex; gap: 6px; flex-wrap: wrap; }
.ef-mgr-req-amount {
    flex-shrink: 0;
    text-align: right;
}
.ef-mgr-req-amt-val {
    color: var(--mgr-ink);
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: -.02em;
    white-space: nowrap;
}
.ef-mgr-req-amt-time {
    color: var(--mgr-muted);
    font-size: .7rem;
    margin-top: 2px;
    text-align: right;
}

/* status flow mini-timeline */
.ef-mgr-req-footer {
    align-items: center;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 10px 18px;
    flex-wrap: wrap;
}
.ef-mgr-status-flow {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.ef-mgr-sf-step {
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.ef-mgr-sf-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    background: #fff;
    flex-shrink: 0;
}
.ef-mgr-sf-dot.done   { background: var(--mgr-emerald); border-color: var(--mgr-emerald); }
.ef-mgr-sf-dot.active { background: var(--mgr-amber);   border-color: var(--mgr-amber);
    box-shadow: 0 0 0 3px rgba(217,119,6,.2); }
.ef-mgr-sf-dot.paid   { background: var(--mgr-blue);    border-color: var(--mgr-blue); }
.ef-mgr-sf-dot.reject { background: var(--mgr-danger);  border-color: var(--mgr-danger); }
.ef-mgr-sf-label {
    color: var(--mgr-muted);
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .02em;
    white-space: nowrap;
}
.ef-mgr-sf-line {
    background: #e2e8f0;
    height: 2px;
    width: 18px;
    margin-bottom: 10px;
    flex-shrink: 0;
}
.ef-mgr-sf-line.done { background: var(--mgr-emerald); }

/* footer action buttons */
.ef-mgr-req-actions { display: flex; gap: 6px; align-items: center; }
.ef-mgr-btn-view {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    color: var(--mgr-ink);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 700;
    padding: 6px 12px;
    text-decoration: none;
    transition: background .14s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ef-mgr-btn-view:hover { background: #e2e8f0; color: var(--mgr-ink); }
.ef-mgr-btn-approve {
    background: var(--mgr-emerald);
    border: none;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    font-size: .78rem;
    font-weight: 700;
    padding: 6px 14px;
    transition: background .14s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ef-mgr-btn-approve:hover { background: var(--mgr-teal); }
.ef-mgr-btn-reject {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    border-radius: 8px;
    color: var(--mgr-danger);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 700;
    padding: 6px 12px;
    transition: background .14s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ef-mgr-btn-reject:hover { background: #fee2e2; }

/* priority chips */
.ef-mgr-priority {
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    font-size: .66rem;
    font-weight: 760;
    letter-spacing: .04em;
    padding: 2px 8px;
    text-transform: uppercase;
    gap: 4px;
    flex-shrink: 0;
}
.ef-mgr-priority.urgent {
    background: #fef2f2;
    color: var(--mgr-danger);
    animation: urgent-glow 2s ease-in-out infinite;
}
@keyframes urgent-glow {
    0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.3); }
    50%      { box-shadow: 0 0 0 4px rgba(220,38,38,0); }
}
.ef-mgr-priority.high   { background: #fffbeb; color: var(--mgr-amber); }
.ef-mgr-priority.medium { background: #ecfeff; color: var(--mgr-teal); }
.ef-mgr-priority.low    { background: #f8fafc; color: #94a3b8; }

.ef-mgr-cat-chip {
    background: #f1f5f9;
    border-radius: 6px;
    color: #475569;
    font-size: .66rem;
    font-weight: 700;
    padding: 2px 8px;
}
.ef-mgr-status-chip {
    border-radius: 6px;
    font-size: .66rem;
    font-weight: 760;
    padding: 2px 8px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.ef-mgr-status-chip.pending   { background: #fffbeb; color: var(--mgr-amber); }
.ef-mgr-status-chip.approved  { background: #ecfdf5; color: var(--mgr-emerald); }
.ef-mgr-status-chip.rejected  { background: #fef2f2; color: var(--mgr-danger); }
.ef-mgr-status-chip.paid      { background: #eff6ff; color: var(--mgr-blue); }
.ef-mgr-status-chip.completed { background: #f0fdfa; color: var(--mgr-teal); }
.ef-mgr-status-chip.reimbursed{ background: #eef2ff; color: var(--mgr-indigo); }

/* ── Empty state ───────────────────────────────────────────────── */
.ef-mgr-empty {
    background: #fff;
    border: 1px solid var(--mgr-border);
    border-radius: 16px;
    box-shadow: var(--mgr-shadow);
    padding: 60px 24px;
    text-align: center;
}
.ef-mgr-empty-orb {
    align-items: center;
    background: var(--mgr-faint);
    border: 2px solid var(--mgr-border-s);
    border-radius: 50%;
    color: var(--mgr-emerald);
    display: inline-flex;
    font-size: 1.8rem;
    height: 72px;
    justify-content: center;
    margin-bottom: 16px;
    width: 72px;
}
.ef-mgr-empty h5 { color: var(--mgr-ink); font-size: 1rem; font-weight: 800; margin-bottom: 6px; }
.ef-mgr-empty p  { color: var(--mgr-muted); font-size: .86rem; margin-bottom: 20px; }

/* ── Reject modal ──────────────────────────────────────────────── */
#rejectModal .modal-content { border-radius: 16px; border: none; }
#rejectModal .modal-header  { border-bottom: 1px solid #f1f5f9; padding: 18px 22px; }
#rejectModal .modal-title   { font-weight: 800; font-size: .95rem; color: var(--mgr-ink); }
#rejectModal .modal-body    { padding: 20px 22px; }
#rejectModal .modal-footer  { border-top: 1px solid #f1f5f9; padding: 14px 22px; }
.ef-mgr-reject-textarea {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    color: var(--mgr-ink);
    font-size: .86rem;
    outline: none;
    padding: 12px 14px;
    resize: vertical;
    transition: border-color .15s;
    width: 100%;
    min-height: 90px;
}
.ef-mgr-reject-textarea:focus { border-color: var(--mgr-danger); background: #fff; }
#rejectModal .btn-danger {
    background: var(--mgr-danger);
    border-color: var(--mgr-danger);
    border-radius: 8px;
    font-weight: 700;
    font-size: .84rem;
}

/* ── Pagination ────────────────────────────────────────────────── */
.ef-mgr-pagination {
    align-items: center;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-top: 16px;
    flex-wrap: wrap;
}
.ef-mgr-pagination .page-info {
    color: var(--mgr-muted);
    font-size: .8rem;
}
.ef-mgr-pagination .pagination {
    margin: 0;
    gap: 4px;
}
.ef-mgr-pagination .page-link {
    border: 1.5px solid var(--mgr-border);
    border-radius: 8px !important;
    color: var(--mgr-muted);
    font-size: .8rem;
    font-weight: 700;
    padding: 5px 11px;
}
.ef-mgr-pagination .page-item.active .page-link {
    background: var(--mgr-emerald);
    border-color: var(--mgr-emerald);
    color: #fff;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .ef-mgr-hero { flex-direction: column; align-items: flex-start; gap: 18px; }
    .ef-mgr-hero-stats { gap: 16px; }
    .ef-mgr-adv-row { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767.98px) {
    .ef-mgr-hero { padding: 20px 18px; border-radius: 16px; }
    .ef-mgr-hero-title { font-size: 1.3rem; }
    .ef-mgr-hero-stats { gap: 14px; }
    .ef-mgr-hero-stat-val { font-size: 1.3rem; }
    .ef-mgr-req-body { flex-wrap: wrap; gap: 10px; }
    .ef-mgr-req-amount { text-align: left; }
    .ef-mgr-adv-row { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 575.98px) {
    .ef-mgr-chips { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
    .ef-mgr-chips::-webkit-scrollbar { display: none; }
    .ef-mgr-adv-row { grid-template-columns: 1fr; }
    .ef-mgr-req-footer { gap: 8px; }
    .ef-mgr-req-actions { flex-wrap: wrap; }
}
</style>
@endpush

@php
    $activeStatus   = $filters['status']   ?? '';
    $activePriority = $filters['priority'] ?? '';
    $activeFrom     = $filters['from']     ?? '';
    $activeTo       = $filters['to']       ?? '';
    $hasAdvFilter   = $filters['category_id'] ?? $activeFrom || $activeTo;
    $todayStr       = today()->toDateString();
    $weekStartStr   = today()->startOfWeek()->toDateString();

    $statusTimelines = [
        'pending'    => [['done','Submitted'],['active','Review'],['pending','Approved'],['pending','Paid']],
        'approved'   => [['done','Submitted'],['done','Review'],['done','Approved'],['pending','Paid']],
        'rejected'   => [['done','Submitted'],['reject','Review'],['pending','Approved'],['pending','Paid']],
        'paid'       => [['done','Submitted'],['done','Review'],['done','Approved'],['paid','Paid']],
        'completed'  => [['done','Submitted'],['done','Review'],['done','Approved'],['paid','Paid']],
        'reimbursed' => [['done','Submitted'],['done','Review'],['done','Approved'],['paid','Paid']],
    ];
@endphp

{{-- ── Hero ────────────────────────────────────────────────────── --}}
<section class="ef-mgr-hero">
    <div class="ef-mgr-hero-main">
        <div class="ef-mgr-eyebrow">Approval Operations</div>
        <h1 class="ef-mgr-hero-title">Expense Requests</h1>
        <div class="ef-mgr-hero-sub">Review, approve and monitor employee reimbursements</div>
    </div>
    <div class="ef-mgr-hero-stats">
        <div class="ef-mgr-hero-stat">
            <div class="ef-mgr-hero-stat-val {{ $summary['pending'] > 0 ? 'amber' : '' }}">
                {{ $summary['pending'] }}
            </div>
            <div class="ef-mgr-hero-stat-lbl">Pending</div>
        </div>
        <div class="ef-mgr-hero-divider"></div>
        <div class="ef-mgr-hero-stat">
            <div class="ef-mgr-hero-stat-val {{ $summary['high_priority'] > 0 ? 'danger' : '' }}">
                {{ $summary['high_priority'] }}
            </div>
            <div class="ef-mgr-hero-stat-lbl">High Priority</div>
        </div>
        <div class="ef-mgr-hero-divider"></div>
        <div class="ef-mgr-hero-stat">
            <div class="ef-mgr-hero-stat-val" style="font-size:1.25rem">
                ₹{{ number_format($summary['pending_amount'], 0) }}
            </div>
            <div class="ef-mgr-hero-stat-lbl">Awaiting</div>
        </div>
    </div>
</section>

{{-- ── KPI Strip ───────────────────────────────────────────────── --}}
<div class="ef-mgr-kpi-wrap">
    <div class="ef-mgr-kpi-strip">
        <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}" class="ef-mgr-kpi">
            <div class="ef-mgr-kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="ef-mgr-kpi-label">
                @if($summary['pending'] > 0)<span class="ef-mgr-kpi-pulse"></span>@endif
                Pending
            </div>
            <div class="ef-mgr-kpi-value {{ $summary['pending'] > 0 ? 'amber' : 'emerald' }}">{{ $summary['pending'] }}</div>
            <div class="ef-mgr-kpi-note">awaiting review</div>
        </a>
        <div class="ef-mgr-kpi">
            <div class="ef-mgr-kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="ef-mgr-kpi-label">Approved Today</div>
            <div class="ef-mgr-kpi-value emerald" style="font-size:1.15rem">₹{{ number_format($summary['approved_today_amount'], 0) }}</div>
            <div class="ef-mgr-kpi-note">{{ $summary['approved_today'] }} requests</div>
        </div>
        <a href="{{ route('manager.expense-requests.index', ['status' => 'rejected']) }}" class="ef-mgr-kpi">
            <div class="ef-mgr-kpi-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="ef-mgr-kpi-label">Rejected</div>
            <div class="ef-mgr-kpi-value danger">{{ $summary['rejected'] }}</div>
            <div class="ef-mgr-kpi-note">all time</div>
        </a>
        <div class="ef-mgr-kpi">
            <div class="ef-mgr-kpi-icon"><i class="bi bi-currency-rupee"></i></div>
            <div class="ef-mgr-kpi-label">Pending Amount</div>
            <div class="ef-mgr-kpi-value teal" style="font-size:1.1rem">₹{{ number_format($summary['pending_amount'], 0) }}</div>
            <div class="ef-mgr-kpi-note">in review queue</div>
        </div>
        <a href="{{ route('manager.expense-requests.index', ['priority' => 'urgent']) }}" class="ef-mgr-kpi">
            <div class="ef-mgr-kpi-icon"><i class="bi bi-fire"></i></div>
            <div class="ef-mgr-kpi-label">High Priority</div>
            <div class="ef-mgr-kpi-value {{ $summary['high_priority'] > 0 ? 'danger' : 'emerald' }}">{{ $summary['high_priority'] }}</div>
            <div class="ef-mgr-kpi-note">urgent + high</div>
        </a>
        <div class="ef-mgr-kpi">
            <div class="ef-mgr-kpi-icon"><i class="bi bi-wallet2"></i></div>
            <div class="ef-mgr-kpi-label">Paid Today</div>
            <div class="ef-mgr-kpi-value blue" style="font-size:1.1rem">₹{{ number_format($summary['paid_today'], 0) }}</div>
            <div class="ef-mgr-kpi-note">settled</div>
        </div>
    </div>
</div>

{{-- ── Filter Toolbar ──────────────────────────────────────────── --}}
<form method="GET" id="filterForm">
    <div class="ef-mgr-toolbar">
        {{-- Search --}}
        <div class="ef-mgr-search-wrap">
            <span class="ef-mgr-search-icon"><i class="bi bi-search"></i></span>
            <input type="text"
                   name="search"
                   id="searchInput"
                   class="ef-mgr-search"
                   placeholder="Search by title, employee…"
                   value="{{ $filters['search'] ?? '' }}"
                   autocomplete="off">
            @if(!empty($filters['search']))
                <button type="button" class="ef-mgr-search-clear"
                        onclick="document.getElementById('searchInput').value='';document.getElementById('filterForm').submit()">
                    <i class="bi bi-x-lg"></i>
                </button>
            @endif
        </div>

        {{-- Quick filter chips --}}
        <div class="ef-mgr-chips">
            <a href="{{ route('manager.expense-requests.index') }}"
               class="ef-mgr-chip {{ $activeStatus === '' && $activePriority === '' && !$activeFrom ? 'active' : '' }}">
                All
            </a>
            <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}"
               class="ef-mgr-chip ef-mgr-chip-amber {{ $activeStatus === 'pending' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split"></i> Pending
                @if($summary['pending'] > 0)
                    <span class="ef-mgr-chip-count">{{ $summary['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('manager.expense-requests.index', ['status' => 'approved']) }}"
               class="ef-mgr-chip {{ $activeStatus === 'approved' ? 'active' : '' }}">
                <i class="bi bi-check-circle"></i> Approved
            </a>
            <a href="{{ route('manager.expense-requests.index', ['status' => 'rejected']) }}"
               class="ef-mgr-chip ef-mgr-chip-danger {{ $activeStatus === 'rejected' ? 'active' : '' }}">
                <i class="bi bi-x-circle"></i> Rejected
            </a>
            <a href="{{ route('manager.expense-requests.index', ['status' => 'paid']) }}"
               class="ef-mgr-chip {{ $activeStatus === 'paid' ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Paid
            </a>
            <a href="{{ route('manager.expense-requests.index', ['priority' => 'urgent']) }}"
               class="ef-mgr-chip ef-mgr-chip-danger {{ $activePriority === 'urgent' ? 'active' : '' }}">
                <i class="bi bi-fire"></i> Urgent
                @if($summary['high_priority'] > 0)
                    <span class="ef-mgr-chip-count">{{ $summary['high_priority'] }}</span>
                @endif
            </a>
            <a href="{{ route('manager.expense-requests.index', ['priority' => 'high']) }}"
               class="ef-mgr-chip ef-mgr-chip-amber {{ $activePriority === 'high' ? 'active' : '' }}">
                <i class="bi bi-arrow-up-circle"></i> High
            </a>
            <a href="{{ route('manager.expense-requests.index', ['from' => $todayStr, 'to' => $todayStr]) }}"
               class="ef-mgr-chip {{ $activeFrom === $todayStr && $activeTo === $todayStr ? 'active' : '' }}">
                <i class="bi bi-calendar-day"></i> Today
            </a>
            <a href="{{ route('manager.expense-requests.index', ['from' => $weekStartStr]) }}"
               class="ef-mgr-chip {{ $activeFrom === $weekStartStr && !$activeTo ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i> This Week
            </a>

            <button type="button"
                    class="ef-mgr-filter-adv {{ $hasAdvFilter ? 'has-filter' : '' }}"
                    id="advToggle"
                    onclick="toggleAdv()">
                <i class="bi bi-sliders"></i> Filters
                @if($hasAdvFilter)<i class="bi bi-dot" style="color:var(--mgr-emerald)"></i>@endif
            </button>
        </div>

        {{-- Advanced filter drawer --}}
        <div class="ef-mgr-adv-drawer {{ $hasAdvFilter ? 'open' : '' }}" id="advDrawer">
            <div class="ef-mgr-adv-row">
                <div>
                    <div class="ef-mgr-adv-label">Category</div>
                    <select name="category_id" class="ef-mgr-adv-select">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="ef-mgr-adv-label">Priority</div>
                    <select name="priority" class="ef-mgr-adv-select">
                        <option value="">All priorities</option>
                        @foreach(['low','medium','high','urgent'] as $p)
                            <option value="{{ $p }}" {{ $activePriority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="ef-mgr-adv-label">From date</div>
                    <input type="date" name="from" class="ef-mgr-adv-date" value="{{ $activeFrom }}">
                </div>
                <div>
                    <div class="ef-mgr-adv-label">To date</div>
                    <input type="date" name="to" class="ef-mgr-adv-date" value="{{ $activeTo }}">
                </div>
                <div class="ef-mgr-adv-actions">
                    <button type="submit" class="ef-mgr-adv-apply">Apply</button>
                    <a href="{{ route('manager.expense-requests.index') }}" class="ef-mgr-adv-reset">Reset</a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- ── Results Meta ────────────────────────────────────────────── --}}
<div class="ef-mgr-meta">
    <span>
        <b>{{ $requests->total() }}</b> request{{ $requests->total() === 1 ? '' : 's' }}
        @if($activeStatus || $activePriority || $activeFrom || !empty($filters['search']))
            · <a href="{{ route('manager.expense-requests.index') }}"
                 style="color:var(--mgr-emerald);font-weight:700;text-decoration:none">Clear filters</a>
        @endif
    </span>
    <span>Priority-sorted</span>
</div>

{{-- ── Request Cards ───────────────────────────────────────────── --}}
@if($requests->isNotEmpty())
    <div class="ef-mgr-card-list">
        @foreach($requests as $req)
            @php
                $initials  = collect(explode(' ', $req->requester?->name ?? 'UN'))->take(2)->map(fn($w) => strtoupper($w[0] ?? 'U'))->join('');
                $priority  = $req->priority ?? 'low';
                $status    = $req->status;
                $timeline  = $statusTimelines[$status] ?? $statusTimelines['pending'];
                $isPending = $status === 'pending';
            @endphp
            <div class="ef-mgr-req-card" data-status="{{ $status }}">
                <div class="ef-mgr-req-body">
                    {{-- Avatar --}}
                    <div class="ef-mgr-avatar">{{ $initials }}</div>

                    {{-- Main info --}}
                    <div class="ef-mgr-req-main">
                        <div class="ef-mgr-req-employee">
                            {{ $req->requester?->name ?? '—' }}
                            <span style="font-weight:500;opacity:.6"> · #{{ $req->id }}</span>
                        </div>
                        <div class="ef-mgr-req-title">{{ $req->title }}</div>
                        <div class="ef-mgr-req-chips">
                            @if($req->category)
                                <span class="ef-mgr-cat-chip"><i class="bi bi-tag" style="font-size:.6rem"></i> {{ $req->category->name }}</span>
                            @endif
                            <span class="ef-mgr-priority {{ $priority }}">
                                @if($priority === 'urgent') <i class="bi bi-fire"></i>
                                @elseif($priority === 'high') <i class="bi bi-arrow-up"></i>
                                @endif
                                {{ ucfirst($priority) }}
                            </span>
                            <span class="ef-mgr-status-chip {{ $status }}">{{ ucfirst($status) }}</span>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="ef-mgr-req-amount">
                        <div class="ef-mgr-req-amt-val">₹{{ number_format($req->amount, 0) }}</div>
                        <div class="ef-mgr-req-amt-time">{{ $req->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                {{-- Footer: timeline + actions --}}
                <div class="ef-mgr-req-footer">
                    {{-- Status flow --}}
                    <div class="ef-mgr-status-flow">
                        @foreach($timeline as $i => $step)
                            @if($i > 0)
                                <div class="ef-mgr-sf-line {{ $step[0] === 'done' || $step[0] === 'paid' ? 'done' : '' }}"></div>
                            @endif
                            <div class="ef-mgr-sf-step">
                                <div class="ef-mgr-sf-dot {{ $step[0] }}"></div>
                                <div class="ef-mgr-sf-label">{{ $step[1] }}</div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    <div class="ef-mgr-req-actions">
                        <a href="{{ route('manager.expense-requests.show', $req) }}" class="ef-mgr-btn-view">
                            <i class="bi bi-eye"></i> View
                        </a>
                        @if($isPending)
                            <form method="POST"
                                  action="{{ route('manager.expense-requests.approve', $req) }}"
                                  onsubmit="return confirm('Approve ₹{{ number_format($req->amount, 0) }} — {{ addslashes(Str::limit($req->title, 30)) }}?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="ef-mgr-btn-approve">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                            </form>
                            <button type="button"
                                    class="ef-mgr-btn-reject"
                                    onclick="openRejectModal({{ $req->id }}, '{{ addslashes(Str::limit($req->title, 40)) }}', '{{ route('manager.expense-requests.reject', $req) }}')">
                                <i class="bi bi-x-lg"></i> Reject
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
        <div class="ef-mgr-pagination">
            <span class="page-info">
                Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}
            </span>
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    @endif

@else
    {{-- Empty state --}}
    <div class="ef-mgr-empty">
        <div class="ef-mgr-empty-orb">
            <i class="bi bi-{{ $activeStatus || $activePriority || !empty($filters['search']) ? 'search' : 'check2-all' }}"></i>
        </div>
        @if($activeStatus || $activePriority || !empty($filters['search']))
            <h5>No matching requests</h5>
            <p>Try adjusting filters or search terms.</p>
            <a href="{{ route('manager.expense-requests.index') }}"
               style="background:var(--mgr-emerald);border:none;border-radius:10px;color:#fff;cursor:pointer;font-size:.84rem;font-weight:700;padding:10px 20px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                <i class="bi bi-arrow-counterclockwise"></i> Clear filters
            </a>
        @else
            <h5>All caught up!</h5>
            <p>No expense requests found. The queue is clear.</p>
        @endif
    </div>
@endif

{{-- ── Reject Modal ────────────────────────────────────────────── --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle-fill me-2" style="color:var(--mgr-danger)"></i>
                    Reject Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p id="rejectTitle" style="color:var(--mgr-muted);font-size:.86rem;margin-bottom:14px"></p>
                    <label class="ef-mgr-adv-label" style="margin-bottom:6px">Reason for rejection <span style="color:var(--mgr-danger)">*</span></label>
                    <textarea name="rejection_reason"
                              id="rejectReason"
                              class="ef-mgr-reject-textarea"
                              placeholder="Briefly explain why this request is being rejected…"
                              minlength="5"
                              required></textarea>
                    @error('rejection_reason')
                        <div style="color:var(--mgr-danger);font-size:.78rem;margin-top:6px">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAdv() {
    const drawer = document.getElementById('advDrawer');
    const btn    = document.getElementById('advToggle');
    drawer.classList.toggle('open');
    btn.classList.toggle('has-filter', drawer.classList.contains('open'));
}

function openRejectModal(id, title, action) {
    document.getElementById('rejectForm').action   = action;
    document.getElementById('rejectTitle').textContent = 'Rejecting: ' + title;
    document.getElementById('rejectReason').value  = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Auto-submit on search (with debounce)
(function () {
    const input = document.getElementById('searchInput');
    if (!input) return;
    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    });
})();
</script>
@endpush

</x-admin-layout>
