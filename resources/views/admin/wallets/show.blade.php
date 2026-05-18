<x-admin-layout title="Wallet — {{ $wallet->user->name }}">

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   WALLET FINANCE CONSOLE — ef-wfin namespace
   Design language: executive fintech operations (matches dashboard)
   ═══════════════════════════════════════════════════════════════════ */

:root {
    --wfin-grad:     linear-gradient(135deg, #041b14 0%, #052e21 45%, #02110c 100%);
    --wfin-emerald:  #0F7B5F;
    --wfin-gold:     #B8893E;
    --wfin-danger:   #C84B44;
    --wfin-amber:    #D89A3D;
    --wfin-radius:   16px;
    --wfin-ease:     cubic-bezier(.2,.7,.2,1);
}

/* ── Hero ──────────────────────────────────────────────────────────── */
.ef-wfin-hero {
    background: var(--wfin-grad);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 20px;
    display: flex;
    gap: 0;
    margin-bottom: 16px;
    overflow: hidden;
    position: relative;
}

/* Emerald glow — top right */
.ef-wfin-hero::before {
    background: radial-gradient(circle, rgba(15,123,95,.2) 0%, transparent 65%);
    border-radius: 50%;
    content: '';
    height: 500px;
    pointer-events: none;
    position: absolute;
    right: -80px;
    top: -180px;
    width: 500px;
    z-index: 0;
}

/* Gold glow — bottom center */
.ef-wfin-hero::after {
    background: radial-gradient(circle, rgba(184,137,62,.12) 0%, transparent 65%);
    bottom: -100px;
    border-radius: 50%;
    content: '';
    height: 320px;
    left: 30%;
    pointer-events: none;
    position: absolute;
    width: 320px;
    z-index: 0;
}

/* Left: title + meta + actions */
.ef-wfin-hero-main {
    flex: 1;
    min-width: 0;
    padding: 30px 32px;
    position: relative;
    z-index: 1;
}

.ef-wfin-eyebrow {
    color: rgba(184,137,62,.88);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .18em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.ef-wfin-title {
    color: rgba(240,253,248,.95);
    font-size: clamp(1.5rem, 3vw, 2.1rem);
    font-weight: 800;
    letter-spacing: -.025em;
    line-height: 1.15;
    margin: 0 0 5px;
}

.ef-wfin-subtitle {
    color: rgba(240,253,248,.42);
    font-size: .84rem;
    margin-bottom: 14px;
}

.ef-wfin-meta {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 5px 14px;
    margin-bottom: 18px;
}

.ef-wfin-meta-item {
    align-items: center;
    color: rgba(240,253,248,.52);
    display: flex;
    font-size: .8rem;
    gap: 5px;
}

.ef-wfin-meta-item i { font-size: .85rem; }

/* Action buttons row */
.ef-wfin-acts {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ef-wfin-btn {
    align-items: center;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    color: rgba(240,253,248,.85);
    cursor: pointer;
    display: inline-flex;
    font-size: .82rem;
    font-weight: 660;
    gap: 7px;
    padding: 8px 15px;
    text-decoration: none;
    transition: background .18s var(--wfin-ease), color .18s var(--wfin-ease), transform .14s var(--wfin-ease);
    white-space: nowrap;
}
.ef-wfin-btn:hover {
    background: rgba(255,255,255,.16);
    color: rgba(240,253,248,1);
    transform: translateY(-1px);
}
.ef-wfin-btn:active { transform: scale(.97); }

.ef-wfin-btn.--credit {
    background: var(--wfin-emerald);
    border-color: var(--wfin-emerald);
    color: #fff;
}
.ef-wfin-btn.--credit:hover {
    background: #0D9E78;
    border-color: #0D9E78;
}

.ef-wfin-btn.--debit {
    background: rgba(200,75,68,.18);
    border-color: rgba(200,75,68,.35);
    color: #f87171;
}
.ef-wfin-btn.--debit:hover {
    background: rgba(200,75,68,.28);
    color: #fca5a5;
}

/* Right: balance panel */
.ef-wfin-hero-bal {
    background: rgba(255,255,255,.03);
    border-left: 1px solid rgba(255,255,255,.07);
    display: flex;
    flex-direction: column;
    gap: 0;
    justify-content: space-between;
    min-width: 230px;
    padding: 28px 28px;
    position: relative;
    z-index: 1;
}

.ef-wfin-bal-label {
    color: rgba(240,253,248,.35);
    font-size: .65rem;
    font-weight: 760;
    letter-spacing: .1em;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.ef-wfin-bal-amount {
    font-size: 2.4rem;
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    letter-spacing: -.03em;
    line-height: 1;
    margin-bottom: 10px;
}
.ef-wfin-bal-amount.--ok  { color: rgba(184,137,62,.92); }
.ef-wfin-bal-amount.--low { color: #f6c86b; }
.ef-wfin-bal-amount.--neg { color: #f87171; }

/* Health chip */
.ef-wfin-health {
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .67rem;
    font-weight: 700;
    letter-spacing: .06em;
    margin-bottom: 16px;
    padding: 3px 10px;
    text-transform: uppercase;
}
.ef-wfin-health.--healthy { background: rgba(15,123,95,.22); color: #4ade80; }
.ef-wfin-health.--low     { background: rgba(246,200,107,.16); color: #f6c86b; }
.ef-wfin-health.--neg     { background: rgba(248,113,113,.18); color: #f87171; }

/* Health bar */
.ef-wfin-health-bar {
    background: rgba(255,255,255,.06);
    border-radius: 4px;
    height: 4px;
    margin-bottom: 16px;
    overflow: hidden;
}
.ef-wfin-health-bar-fill {
    border-radius: 4px;
    height: 100%;
    transition: width .6s var(--wfin-ease);
    width: var(--bar-w, 100%);
}
.ef-wfin-health-bar-fill.--healthy { background: linear-gradient(90deg, #0D9E78, #4ade80); }
.ef-wfin-health-bar-fill.--low     { background: linear-gradient(90deg, #D89A3D, #f6c86b); }
.ef-wfin-health-bar-fill.--neg     { background: #f87171; }

/* Net flow mini stats */
.ef-wfin-netflow {
    display: grid;
    gap: 8px;
    grid-template-columns: 1fr 1fr;
    margin-bottom: 16px;
}
.ef-wfin-netflow-item { display: flex; flex-direction: column; }
.ef-wfin-netflow-label {
    color: rgba(240,253,248,.28);
    font-size: .61rem;
    font-weight: 700;
    letter-spacing: .08em;
    margin-bottom: 2px;
    text-transform: uppercase;
}
.ef-wfin-netflow-val {
    font-size: .92rem;
    font-variant-numeric: tabular-nums;
    font-weight: 760;
}
.ef-wfin-netflow-val.--in  { color: #4ade80; }
.ef-wfin-netflow-val.--out { color: #f87171; }

/* Hero mobile stat */
.ef-wfin-mstat {
    align-items: baseline;
    border-top: 1px solid rgba(255,255,255,.08);
    display: none;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
}
.ef-wfin-mstat-val {
    color: rgba(184,137,62,.92);
    font-size: 1.55rem;
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.03em;
}
.ef-wfin-mstat-note {
    color: rgba(240,253,248,.38);
    font-size: .74rem;
}

/* ── Body grid ─────────────────────────────────────────────────────── */
.ef-wfin-body {
    align-items: start;
    display: grid;
    gap: 16px;
    grid-template-columns: 260px 1fr;
    margin-bottom: 0;
}

/* ── Employee profile card ─────────────────────────────────────────── */
.ef-wfin-profile {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--wfin-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

.ef-wfin-profile-head {
    background: var(--wfin-grad);
    padding: 20px 20px 16px;
    position: relative;
    overflow: hidden;
}
.ef-wfin-profile-head::before {
    background: radial-gradient(circle, rgba(15,123,95,.2) 0%, transparent 65%);
    border-radius: 50%;
    content: '';
    height: 200px;
    pointer-events: none;
    position: absolute;
    right: -40px;
    top: -60px;
    width: 200px;
}

.ef-wfin-avatar {
    align-items: center;
    background: rgba(255,255,255,.08);
    border: 2px solid rgba(184,137,62,.3);
    border-radius: 14px;
    color: rgba(184,137,62,.92);
    display: flex;
    font-size: 1.4rem;
    font-weight: 800;
    height: 56px;
    justify-content: center;
    letter-spacing: -.01em;
    margin-bottom: 12px;
    position: relative;
    width: 56px;
    z-index: 1;
}

.ef-wfin-pname {
    color: rgba(240,253,248,.95);
    font-size: .98rem;
    font-weight: 800;
    margin: 0 0 2px;
    position: relative;
    z-index: 1;
}
.ef-wfin-pemail {
    color: rgba(240,253,248,.42);
    font-size: .76rem;
    margin: 0 0 10px;
    position: relative;
    word-break: break-all;
    z-index: 1;
}
.ef-wfin-prole {
    border-radius: 20px;
    color: rgba(184,137,62,.92);
    background: rgba(184,137,62,.14);
    border: 1px solid rgba(184,137,62,.22);
    display: inline-block;
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .08em;
    padding: 2px 10px;
    position: relative;
    text-transform: uppercase;
    z-index: 1;
}

.ef-wfin-profile-body {
    padding: 16px 18px;
}

.ef-wfin-pdetail {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    padding: 9px 0;
}
.ef-wfin-pdetail:last-child { border-bottom: none; padding-bottom: 0; }
.ef-wfin-pdetail:first-child { padding-top: 0; }

.ef-wfin-pdetail-icon {
    align-items: center;
    background: var(--ef-bg-subtle);
    border-radius: 8px;
    color: var(--ef-muted);
    display: flex;
    flex-shrink: 0;
    font-size: .85rem;
    height: 30px;
    justify-content: center;
    width: 30px;
}

.ef-wfin-pdetail-info { display: flex; flex-direction: column; gap: 2px; }
.ef-wfin-pdetail-label { color: var(--ef-faint); font-size: .63rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
.ef-wfin-pdetail-val   { color: var(--ef-ink-2); font-size: .82rem; font-weight: 600; }

.ef-wfin-profile-foot {
    border-top: 1px solid var(--ef-border);
    padding: 14px 18px;
}

/* ── Right column: filter + ledger ────────────────────────────────── */
.ef-wfin-right { display: flex; flex-direction: column; gap: 14px; min-width: 0; }

/* Filter module */
.ef-wfin-filters {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--wfin-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

.ef-wfin-filter-head {
    align-items: center;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 13px 16px 11px;
}

.ef-wfin-filter-title {
    align-items: center;
    color: var(--ef-faint);
    display: inline-flex;
    font-size: .67rem;
    font-weight: 700;
    gap: 6px;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.ef-wfin-filter-title i { font-size: .72rem; opacity: .6; }

.ef-wfin-filter-clear {
    align-items: center;
    background: rgba(200,75,68,.08);
    border: 1px solid rgba(200,75,68,.18);
    border-radius: 20px;
    color: var(--ef-danger);
    display: inline-flex;
    font-size: .67rem;
    font-weight: 700;
    gap: 4px;
    letter-spacing: .04em;
    padding: 3px 10px;
    text-decoration: none;
    transition: background .14s;
}
.ef-wfin-filter-clear:hover { background: rgba(200,75,68,.14); color: var(--ef-danger); }

/* Quick-filter chips */
.ef-wfin-chips {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    border-top: 1px solid var(--ef-border);
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 10px 16px;
}

.ef-wfin-fchip {
    align-items: center;
    border-radius: 20px;
    border: 1px solid transparent;
    background: var(--ef-bg-subtle);
    color: var(--ef-muted);
    display: inline-flex;
    font-size: .73rem;
    font-weight: 680;
    gap: 5px;
    letter-spacing: .01em;
    padding: 5px 13px;
    text-decoration: none;
    transition: background .13s, color .13s, border-color .13s, box-shadow .13s;
    white-space: nowrap;
}
.ef-wfin-fchip i { font-size: .72rem; }
.ef-wfin-fchip:hover {
    background: var(--ef-surface-raised, var(--ef-bg-subtle));
    border-color: var(--ef-border);
    color: var(--ef-ink-2);
}
.ef-wfin-fchip.--active {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    box-shadow: 0 1px 6px rgba(0,0,0,.18);
    color: rgba(255,253,250,.94);
    font-weight: 720;
}
.ef-wfin-fchip.--credit.--active {
    background: rgba(15,123,95,.13);
    border-color: rgba(15,123,95,.35);
    box-shadow: 0 0 0 3px rgba(15,123,95,.07);
    color: var(--ef-emerald);
}
.ef-wfin-fchip.--debit.--active {
    background: rgba(200,75,68,.1);
    border-color: rgba(200,75,68,.3);
    box-shadow: 0 0 0 3px rgba(200,75,68,.06);
    color: var(--ef-danger);
}
.ef-wfin-fchip.--adj.--active {
    background: rgba(96,112,128,.12);
    border-color: rgba(96,112,128,.28);
    box-shadow: 0 0 0 3px rgba(96,112,128,.06);
    color: var(--ef-bluegray);
}
.ef-wfin-fchip.--reimb.--active {
    background: rgba(184,137,62,.12);
    border-color: rgba(184,137,62,.3);
    box-shadow: 0 0 0 3px rgba(184,137,62,.06);
    color: var(--ef-gold);
}

/* Date range filter */
.ef-wfin-daterange {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px 16px 14px;
}
.ef-wfin-date-row {
    align-items: flex-end;
    display: flex;
    gap: 8px;
}
.ef-wfin-date-group {
    align-items: flex-end;
    display: flex;
    flex: 1;
    gap: 0;
    min-width: 0;
}
.ef-wfin-date-sep {
    align-items: center;
    color: var(--ef-faint);
    display: flex;
    flex-shrink: 0;
    font-size: .7rem;
    height: 36px;
    justify-content: center;
    padding: 0 6px;
}
.ef-wfin-daterange-field { flex: 1; min-width: 0; }
.ef-wfin-daterange-field label {
    color: var(--ef-faint);
    display: block;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .07em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-wfin-date-input {
    background: var(--ef-bg-subtle);
    border: 1.5px solid var(--ef-border);
    border-radius: 10px;
    color: var(--ef-ink-2);
    font-size: .8rem;
    font-weight: 600;
    height: 36px;
    padding: 0 10px;
    transition: border-color .14s, box-shadow .14s;
    width: 100%;
}
.ef-wfin-date-input:focus {
    border-color: rgba(184,137,62,.45);
    box-shadow: 0 0 0 3px rgba(184,137,62,.08);
    outline: none;
}
.ef-wfin-date-input::-webkit-calendar-picker-indicator { opacity: .4; cursor: pointer; }
.ef-wfin-apply-btn {
    align-items: center;
    background: var(--ef-ink);
    border: none;
    border-radius: 10px;
    color: rgba(255,253,250,.92);
    cursor: pointer;
    display: inline-flex;
    font-size: .78rem;
    font-weight: 700;
    gap: 6px;
    height: 36px;
    letter-spacing: .02em;
    padding: 0 16px;
    transition: background .14s, box-shadow .14s;
    white-space: nowrap;
}
.ef-wfin-apply-btn:hover {
    background: var(--ef-ink-2, #2a2420);
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
}
.ef-wfin-apply-btn i { font-size: .8rem; }

/* ── Transaction ledger ────────────────────────────────────────────── */
.ef-wfin-ledger {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--wfin-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

.ef-wfin-ledger-head {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 14px 18px;
}

.ef-wfin-ledger-title {
    color: var(--ef-ink);
    font-size: .9rem;
    font-weight: 800;
}

.ef-wfin-ledger-count {
    background: var(--ef-bg-subtle);
    border-radius: 20px;
    color: var(--ef-faint);
    font-size: .7rem;
    font-weight: 700;
    padding: 2px 10px;
}

/* Transaction entry */
.ef-wfin-entry {
    align-items: flex-start;
    border-bottom: 1px solid var(--ef-border);
    display: grid;
    gap: 12px;
    grid-template-columns: 40px 1fr auto;
    padding: 14px 18px;
    transition: background .12s var(--wfin-ease);
}
.ef-wfin-entry:last-child { border-bottom: none; }
.ef-wfin-entry:hover { background: rgba(15,123,95,.015); }

/* Type icon circle */
.ef-wfin-entry-icon {
    align-items: center;
    border-radius: 10px;
    display: flex;
    flex-shrink: 0;
    font-size: .95rem;
    height: 38px;
    justify-content: center;
    margin-top: 1px;
    width: 38px;
}
.ef-wfin-entry-icon.--credit       { background: rgba(15,123,95,.12);  color: var(--ef-emerald); }
.ef-wfin-entry-icon.--debit        { background: rgba(200,75,68,.1);   color: var(--ef-danger); }
.ef-wfin-entry-icon.--adjustment   { background: rgba(96,112,128,.1);  color: var(--ef-bluegray); }
.ef-wfin-entry-icon.--reimbursement{ background: rgba(184,137,62,.12); color: var(--ef-gold); }

/* Entry body */
.ef-wfin-entry-body { min-width: 0; }

.ef-wfin-entry-top {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 4px;
}

.ef-wfin-entry-badge {
    border-radius: 6px;
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .05em;
    padding: 2px 8px;
    text-transform: capitalize;
}
.ef-wfin-entry-badge.--credit       { background: rgba(15,123,95,.1);  color: var(--ef-emerald); }
.ef-wfin-entry-badge.--debit        { background: rgba(200,75,68,.08); color: var(--ef-danger); }
.ef-wfin-entry-badge.--adjustment   { background: rgba(96,112,128,.1); color: var(--ef-bluegray); }
.ef-wfin-entry-badge.--reimbursement{ background: rgba(184,137,62,.1); color: var(--ef-gold); }

.ef-wfin-entry-note {
    color: var(--ef-muted);
    font-size: .82rem;
    line-height: 1.4;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-wfin-entry-meta {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ef-wfin-entry-bal {
    color: var(--ef-faint);
    font-size: .73rem;
    font-variant-numeric: tabular-nums;
}
.ef-wfin-entry-link {
    align-items: center;
    color: var(--ef-emerald);
    display: inline-flex;
    font-size: .73rem;
    font-weight: 600;
    gap: 3px;
    overflow: hidden;
    text-decoration: none;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-wfin-entry-link:hover { text-decoration: underline; }

/* Right: amount + date */
.ef-wfin-entry-right {
    align-items: flex-end;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    gap: 4px;
    text-align: right;
}
.ef-wfin-entry-amount {
    font-size: .96rem;
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    letter-spacing: -.01em;
}
.ef-wfin-entry-amount.--in  { color: var(--ef-emerald); }
.ef-wfin-entry-amount.--out { color: var(--ef-danger); }

.ef-wfin-entry-date {
    color: var(--ef-faint);
    font-size: .7rem;
    line-height: 1.3;
    text-align: right;
}

/* Empty state */
.ef-wfin-empty {
    padding: 56px 20px;
    text-align: center;
}
.ef-wfin-empty-orb {
    align-items: center;
    background: rgba(15,123,95,.06);
    border: 1px solid rgba(15,123,95,.12);
    border-radius: 50%;
    color: var(--ef-emerald);
    display: inline-flex;
    font-size: 1.5rem;
    height: 62px;
    justify-content: center;
    margin-bottom: 14px;
    opacity: .65;
    width: 62px;
}
.ef-wfin-empty-title { color: var(--ef-muted); font-size: .88rem; font-weight: 700; margin-bottom: 4px; }
.ef-wfin-empty-sub   { color: var(--ef-faint); font-size: .78rem; }

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .ef-wfin-hero { flex-direction: column; }
    .ef-wfin-hero-bal {
        border-left: none;
        border-top: 1px solid rgba(255,255,255,.07);
        flex-direction: row;
        flex-wrap: wrap;
        gap: 12px 24px;
        min-width: unset;
        padding: 18px 28px;
        justify-content: flex-start;
        align-items: flex-start;
    }
    .ef-wfin-health-bar { width: 140px; }
    .ef-wfin-netflow { width: auto; }
}

@media (max-width: 767.98px) {
    .ef-wfin-hero-main { padding: 20px 18px 16px; }
    .ef-wfin-hero-bal { padding: 16px 18px; gap: 10px 18px; }
    .ef-wfin-bal-amount { font-size: 2rem; }
    .ef-wfin-body { grid-template-columns: 1fr; }
    .ef-wfin-mstat { display: flex; }
    .ef-wfin-health-bar,
    .ef-wfin-netflow { display: none; }
    .ef-wfin-entry { grid-template-columns: 36px 1fr auto; padding: 12px 14px; gap: 10px; }
    .ef-wfin-entry-icon { height: 34px; width: 34px; font-size: .88rem; border-radius: 9px; }
    .ef-wfin-entry-amount { font-size: .88rem; }
    .ef-wfin-fchip { font-size: .69rem; padding: 4px 11px; }
    .ef-wfin-chips { gap: 5px; padding: 9px 14px; }
    .ef-wfin-filter-head { padding: 10px 14px 9px; }
    .ef-wfin-daterange { padding: 10px 14px 12px; }
    .ef-wfin-date-row { flex-direction: column; gap: 8px; }
    .ef-wfin-date-group { flex: unset; }
    .ef-wfin-apply-btn { width: 100%; justify-content: center; height: 38px; border-radius: 10px; }
}
</style>
@endpush

@php
    $nameParts = explode(' ', trim($wallet->user->name));
    $initials  = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) $initials .= strtoupper(substr(end($nameParts), 0, 1));

    $isNeg    = $wallet->isNegative();
    $isLow    = !$isNeg && $wallet->isLow();
    $isOk     = !$isNeg && !$isLow;
    $healthCls = $isNeg ? '--neg' : ($isLow ? '--low' : '--ok');
    $healthHCls= $isNeg ? '--neg' : ($isLow ? '--low' : '--healthy');
    $healthLbl = $isNeg ? 'Negative' : ($isLow ? 'Low Balance' : 'Healthy');
    $healthDot = $isNeg ? '▼' : ($isLow ? '●' : '●');

    /* Health bar width: healthy=100%, low=30%, negative=5% */
    $barWidth  = $isNeg ? 5 : ($isLow ? 30 : 100);
@endphp

{{-- ═══ PREMIUM HERO ═══ --}}
<div class="ef-wfin-hero">

    {{-- Left: identity + actions --}}
    <div class="ef-wfin-hero-main">
        <div class="ef-wfin-eyebrow">Wallet Operations</div>
        <h1 class="ef-wfin-title">{{ $wallet->user->name }}'s Wallet</h1>
        <div class="ef-wfin-subtitle">Realtime reimbursement balance &amp; transaction monitoring</div>

        <div class="ef-wfin-meta">
            <div class="ef-wfin-meta-item">
                <i class="bi bi-person-badge"></i>
                {{ ucfirst($wallet->user->role) }}
            </div>
            <div class="ef-wfin-meta-item">
                <i class="bi bi-clock-history"></i>
                {{ $stats['last_txn_at'] ? \Carbon\Carbon::parse($stats['last_txn_at'])->diffForHumans() : 'No transactions' }}
            </div>
            <div class="ef-wfin-meta-item">
                <i class="bi bi-receipt"></i>
                {{ number_format($stats['txn_count']) }} transactions
            </div>
            <div class="ef-wfin-meta-item">
                <i class="bi bi-circle-fill" style="font-size:.5rem;color:#4ade80"></i>
                Active
            </div>
        </div>

        <div class="ef-wfin-acts">
            <button class="ef-wfin-btn --credit" data-bs-toggle="modal" data-bs-target="#creditModal">
                <i class="bi bi-plus-circle"></i> Credit
            </button>
            <button class="ef-wfin-btn --debit" data-bs-toggle="modal" data-bs-target="#debitModal">
                <i class="bi bi-dash-circle"></i> Debit
            </button>
            <button class="ef-wfin-btn" data-bs-toggle="modal" data-bs-target="#adjustModal">
                <i class="bi bi-sliders"></i> Adjust
            </button>
            <a href="{{ route('admin.employees.show', $wallet->user) }}" class="ef-wfin-btn">
                <i class="bi bi-person-circle"></i> Profile
            </a>
            <a href="{{ route('admin.wallets.index') }}" class="ef-wfin-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        {{-- Mobile: show balance here --}}
        <div class="ef-wfin-mstat">
            <span class="ef-wfin-mstat-val" id="wallet-balance-display-m">₹{{ number_format($wallet->balance, 2) }}</span>
            <span class="ef-wfin-mstat-note">{{ $healthLbl }}</span>
        </div>
    </div>

    {{-- Right: balance dominance panel --}}
    <div class="ef-wfin-hero-bal">
        <div>
            <div class="ef-wfin-bal-label">Current Balance</div>
            <div id="wallet-balance-display"
                 class="ef-wfin-bal-amount {{ $healthCls }}"
                 data-raw="{{ $wallet->balance }}">
                ₹{{ number_format($wallet->balance, 2) }}
            </div>
            <div id="wallet-balance-badge">
                <span class="ef-wfin-health {{ $healthHCls }}">
                    <i class="bi {{ $isNeg ? 'bi-exclamation-triangle' : ($isLow ? 'bi-exclamation-circle' : 'bi-shield-check') }}" style="font-size:.7rem"></i>
                    {{ $healthLbl }}
                </span>
            </div>

            <div class="ef-wfin-health-bar" style="margin-top:12px">
                <div class="ef-wfin-health-bar-fill {{ $healthHCls }}" style="--bar-w:{{ $barWidth }}%"></div>
            </div>

            <div class="ef-wfin-netflow">
                <div class="ef-wfin-netflow-item">
                    <div class="ef-wfin-netflow-label">Total In</div>
                    <div class="ef-wfin-netflow-val --in">₹{{ number_format($stats['total_credited'], 0) }}</div>
                </div>
                <div class="ef-wfin-netflow-item">
                    <div class="ef-wfin-netflow-label">Total Out</div>
                    <div class="ef-wfin-netflow-val --out">₹{{ number_format($stats['total_debited'], 0) }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═══ BODY GRID ═══ --}}
<div class="ef-wfin-body">

    {{-- ── Left: Employee profile card ── --}}
    <div class="ef-wfin-profile">

        {{-- Dark gradient header --}}
        <div class="ef-wfin-profile-head">
            <div class="ef-wfin-avatar">{{ $initials }}</div>
            <div class="ef-wfin-pname">{{ $wallet->user->name }}</div>
            <div class="ef-wfin-pemail">{{ $wallet->user->email }}</div>
            <span class="ef-wfin-prole">{{ $wallet->user->role }}</span>
        </div>

        {{-- Details --}}
        <div class="ef-wfin-profile-body">
            <div class="ef-wfin-pdetail">
                <div class="ef-wfin-pdetail-icon"><i class="bi bi-telephone"></i></div>
                <div class="ef-wfin-pdetail-info">
                    <div class="ef-wfin-pdetail-label">Phone</div>
                    <div class="ef-wfin-pdetail-val">{{ $wallet->user->phone ?? '—' }}</div>
                </div>
            </div>
            <div class="ef-wfin-pdetail">
                <div class="ef-wfin-pdetail-icon"><i class="bi bi-calendar3"></i></div>
                <div class="ef-wfin-pdetail-info">
                    <div class="ef-wfin-pdetail-label">Joined</div>
                    <div class="ef-wfin-pdetail-val">{{ $wallet->user->created_at->format('d M Y') }}</div>
                </div>
            </div>
            <div class="ef-wfin-pdetail">
                <div class="ef-wfin-pdetail-icon"><i class="bi bi-wallet2"></i></div>
                <div class="ef-wfin-pdetail-info">
                    <div class="ef-wfin-pdetail-label">Wallet Since</div>
                    <div class="ef-wfin-pdetail-val">{{ $wallet->created_at->format('d M Y') }}</div>
                </div>
            </div>
            <div class="ef-wfin-pdetail">
                <div class="ef-wfin-pdetail-icon"><i class="bi bi-list-ul"></i></div>
                <div class="ef-wfin-pdetail-info">
                    <div class="ef-wfin-pdetail-label">Transactions</div>
                    <div class="ef-wfin-pdetail-val">{{ number_format($stats['txn_count']) }} total</div>
                </div>
            </div>
        </div>

        <div class="ef-wfin-profile-foot">
            <a href="{{ route('admin.employees.show', $wallet->user) }}"
               class="ef-btn ef-btn-dark" style="width:100%;justify-content:center;gap:7px">
                <i class="bi bi-person-circle"></i> View Employee Profile
            </a>
        </div>
    </div>

    {{-- ── Right: Filter + Ledger ── --}}
    <div class="ef-wfin-right">

        {{-- Filter module --}}
        <div class="ef-wfin-filters">
            <div class="ef-wfin-filter-head">
                <span class="ef-wfin-filter-title">
                    <i class="bi bi-funnel"></i>Filter Transactions
                </span>
                @if(request('type') || request('from') || request('to'))
                    <a href="{{ route('admin.wallets.show', $wallet->user) }}" class="ef-wfin-filter-clear">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </div>

            {{-- Quick filter chips --}}
            <div class="ef-wfin-chips">
                @php
                    $dateQs = collect(['from' => request('from'), 'to' => request('to')])->filter()->map(fn($v,$k) => "$k=$v")->implode('&');
                    $dqs    = $dateQs ? '?'.$dateQs : '';
                @endphp
                <a href="{{ route('admin.wallets.show', $wallet->user) }}{{ $dqs }}"
                   class="ef-wfin-fchip {{ !request('type') ? '--active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i> All
                </a>
                <a href="?type=credit{{ request('from') ? '&from='.request('from') : '' }}{{ request('to') ? '&to='.request('to') : '' }}"
                   class="ef-wfin-fchip --credit {{ request('type') === 'credit' ? '--active' : '' }}">
                    <i class="bi bi-plus-circle"></i> Credit
                </a>
                <a href="?type=debit{{ request('from') ? '&from='.request('from') : '' }}{{ request('to') ? '&to='.request('to') : '' }}"
                   class="ef-wfin-fchip --debit {{ request('type') === 'debit' ? '--active' : '' }}">
                    <i class="bi bi-dash-circle"></i> Debit
                </a>
                <a href="?type=adjustment{{ request('from') ? '&from='.request('from') : '' }}{{ request('to') ? '&to='.request('to') : '' }}"
                   class="ef-wfin-fchip --adj {{ request('type') === 'adjustment' ? '--active' : '' }}">
                    <i class="bi bi-sliders"></i> Adj
                </a>
                <a href="?type=reimbursement{{ request('from') ? '&from='.request('from') : '' }}{{ request('to') ? '&to='.request('to') : '' }}"
                   class="ef-wfin-fchip --reimb {{ request('type') === 'reimbursement' ? '--active' : '' }}">
                    <i class="bi bi-arrow-counterclockwise"></i> Reimb
                </a>
            </div>

            {{-- Date range --}}
            <form method="GET" class="ef-wfin-daterange" data-no-ajax>
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <div class="ef-wfin-date-row">
                    <div class="ef-wfin-date-group">
                        <div class="ef-wfin-daterange-field">
                            <label>From</label>
                            <input type="date" name="from" class="ef-wfin-date-input"
                                   value="{{ request('from') }}"
                                   style="border-radius:10px 0 0 10px">
                        </div>
                        <div class="ef-wfin-date-sep">→</div>
                        <div class="ef-wfin-daterange-field">
                            <label>To</label>
                            <input type="date" name="to" class="ef-wfin-date-input"
                                   value="{{ request('to') }}"
                                   style="border-radius:0 10px 10px 0">
                        </div>
                    </div>
                    <button type="submit" class="ef-wfin-apply-btn">
                        <i class="bi bi-check2"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        {{-- Transaction ledger --}}
        <div class="ef-wfin-ledger">
            <div class="ef-wfin-ledger-head">
                <span class="ef-wfin-ledger-title">Transaction Ledger</span>
                <span class="ef-wfin-ledger-count">{{ $transactions->total() }} records</span>
            </div>

            @forelse($transactions as $txn)
            @php
                $entryIcon = match($txn->type) {
                    'credit'        => 'bi-plus-circle',
                    'debit'         => 'bi-dash-circle',
                    'adjustment'    => 'bi-sliders',
                    'reimbursement' => 'bi-arrow-counterclockwise',
                    default         => 'bi-circle',
                };
                $isInflow  = $txn->isCredit();
            @endphp
            <div class="ef-wfin-entry">
                {{-- Icon --}}
                <div class="ef-wfin-entry-icon --{{ $txn->type }}">
                    <i class="bi {{ $entryIcon }}"></i>
                </div>

                {{-- Body --}}
                <div class="ef-wfin-entry-body">
                    <div class="ef-wfin-entry-top">
                        <span class="ef-wfin-entry-badge --{{ $txn->type }}">{{ $txn->type }}</span>
                        @if($txn->expenseRequest)
                            <a href="{{ route('admin.expense-requests.show', $txn->expenseRequest) }}"
                               class="ef-wfin-entry-link">
                                <i class="bi bi-link-45deg"></i>
                                {{ Str::limit($txn->expenseRequest->title, 22) }}
                            </a>
                        @endif
                    </div>
                    <div class="ef-wfin-entry-note">{{ $txn->notes ?: 'No notes' }}</div>
                    <div class="ef-wfin-entry-meta">
                        <span class="ef-wfin-entry-bal">After: ₹{{ number_format($txn->balance_after, 2) }}</span>
                        <span style="color:var(--ef-faint);font-size:.68rem">by {{ $txn->creator->name }}</span>
                    </div>
                </div>

                {{-- Amount + date --}}
                <div class="ef-wfin-entry-right">
                    <div class="ef-wfin-entry-amount {{ $isInflow ? '--in' : '--out' }}">
                        {{ $isInflow ? '+' : '−' }}₹{{ number_format($txn->amount, 2) }}
                    </div>
                    <div class="ef-wfin-entry-date">
                        {{ $txn->created_at->format('d M Y') }}<br>
                        {{ $txn->created_at->format('h:i A') }}
                    </div>
                </div>
            </div>
            @empty
            <div class="ef-wfin-empty">
                <div class="ef-wfin-empty-orb"><i class="bi bi-clock-history"></i></div>
                <div class="ef-wfin-empty-title">No transactions found</div>
                <div class="ef-wfin-empty-sub">
                    @if(request('type') || request('from') || request('to'))
                        Try clearing your filters to see all activity.
                    @else
                        Wallet activity will appear here.
                    @endif
                </div>
            </div>
            @endforelse

            @if($transactions->hasPages())
            <div style="border-top:1px solid var(--ef-border);padding:14px 18px">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>

    </div>{{-- /ef-wfin-right --}}

</div>{{-- /ef-wfin-body --}}

{{-- ═══ MODALS ═══ --}}

{{-- Credit Modal --}}
<div class="modal fade" id="creditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:16px">
            <form id="creditForm" method="POST" action="{{ route('admin.wallets.transact', $wallet->user) }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="credit">
                <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-plus-circle" style="color:var(--ef-emerald);margin-right:8px"></i>Credit Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:20px">
                    <div class="d-none wallet-error" style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;color:var(--ef-danger);font-size:.82rem;margin-bottom:12px;padding:8px 14px" role="alert"></div>
                    <div style="margin-bottom:16px">
                        <label class="ef-label">Amount (₹) <span style="color:var(--ef-danger)">*</span></label>
                        <input type="number" name="amount" class="ef-input" min="0.01" step="0.01" required placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label class="ef-label">Notes <span style="color:var(--ef-faint);font-weight:400">(optional)</span></label>
                        <textarea name="notes" class="ef-textarea" rows="2" placeholder="Reason for credit…" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark" style="background:var(--ef-emerald);border-color:var(--ef-emerald)">
                        <i class="bi bi-plus-circle"></i> Credit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Debit Modal --}}
<div class="modal fade" id="debitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:16px">
            <form id="debitForm" method="POST" action="{{ route('admin.wallets.transact', $wallet->user) }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="debit">
                <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-dash-circle" style="color:var(--ef-danger);margin-right:8px"></i>Debit Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:20px">
                    <div class="d-none wallet-error" style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;color:var(--ef-danger);font-size:.82rem;margin-bottom:12px;padding:8px 14px" role="alert"></div>
                    <div style="background:rgba(246,200,107,.06);border:1px solid rgba(246,200,107,.18);border-radius:10px;font-size:.82rem;margin-bottom:14px;padding:9px 13px">
                        <i class="bi bi-exclamation-triangle" style="color:#c8900a;margin-right:6px"></i>
                        <span style="color:#7a5c00">Available: <strong id="debit-balance-hint">₹{{ number_format($wallet->balance, 2) }}</strong></span>
                    </div>
                    <div style="margin-bottom:16px">
                        <label class="ef-label">Amount (₹) <span style="color:var(--ef-danger)">*</span></label>
                        <input type="number" name="amount" class="ef-input" id="debitAmount"
                               min="0.01" step="0.01" max="{{ $wallet->balance }}" required placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label class="ef-label">Notes <span style="color:var(--ef-faint);font-weight:400">(optional)</span></label>
                        <textarea name="notes" class="ef-textarea" rows="2" placeholder="Reason for debit…" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark" style="background:var(--ef-danger);border-color:var(--ef-danger)">
                        <i class="bi bi-dash-circle"></i> Debit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Adjust Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:16px">
            <form id="adjustForm" method="POST" action="{{ route('admin.wallets.transact', $wallet->user) }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="adjustment">
                <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-sliders" style="margin-right:8px"></i>Balance Adjustment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:20px">
                    <div class="d-none wallet-error" style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;color:var(--ef-danger);font-size:.82rem;margin-bottom:12px;padding:8px 14px" role="alert"></div>
                    <div style="margin-bottom:16px">
                        <label class="ef-label">New Balance (₹) <span style="color:var(--ef-danger)">*</span></label>
                        <input type="number" name="amount" class="ef-input" min="0" step="0.01"
                               value="{{ $wallet->balance }}" required>
                        <div style="color:var(--ef-faint);font-size:.74rem;margin-top:5px">Set the exact target balance. Delta will be recorded.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label class="ef-label">Notes <span style="color:var(--ef-faint);font-weight:400">(optional)</span></label>
                        <textarea name="notes" class="ef-textarea" rows="2" placeholder="Reason for adjustment…" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark">
                        <i class="bi bi-sliders"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="walletToast" role="alert" aria-live="assertive" aria-atomic="true"
         style="background:var(--ef-hero-grad);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fffdfa;min-width:240px">
        <div style="align-items:center;display:flex;gap:10px;padding:12px 14px">
            <div class="toast-body" style="flex:1;font-size:.875rem;font-weight:600"></div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const LOW_TH     = 500;

    const balanceEl     = document.getElementById('wallet-balance-display');
    const badgeEl       = document.getElementById('wallet-balance-badge');
    const debitHint     = document.getElementById('debit-balance-hint');
    const debitAmountEl = document.getElementById('debitAmount');
    const toastEl       = document.getElementById('walletToast');
    const bsToast       = new bootstrap.Toast(toastEl, { delay: 4000 });
    const healthBar     = document.querySelector('.ef-wfin-health-bar-fill');

    function fmtBalance(n) {
        return '₹' + parseFloat(n).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function updateBalanceDisplay(newBalance) {
        /* Main balance amount */
        balanceEl.textContent = fmtBalance(newBalance);
        balanceEl.className   = 'ef-wfin-bal-amount';
        if      (newBalance < 0)      balanceEl.classList.add('--neg');
        else if (newBalance < LOW_TH) balanceEl.classList.add('--low');
        else                          balanceEl.classList.add('--ok');

        if (debitHint)     debitHint.textContent = fmtBalance(newBalance);
        if (debitAmountEl) debitAmountEl.max = newBalance > 0 ? newBalance : 0;

        /* Health chip */
        if (badgeEl) {
            const neg = newBalance < 0, low = !neg && newBalance < LOW_TH;
            const cls = neg ? '--neg' : (low ? '--low' : '--healthy');
            const lbl = neg ? 'Negative' : (low ? 'Low Balance' : 'Healthy');
            const ico = neg ? 'bi-exclamation-triangle' : (low ? 'bi-exclamation-circle' : 'bi-shield-check');
            badgeEl.innerHTML = `<span class="ef-wfin-health ${cls}">
                <i class="bi ${ico}" style="font-size:.7rem"></i> ${lbl}
            </span>`;
        }

        /* Health bar */
        if (healthBar) {
            const neg = newBalance < 0, low = !neg && newBalance < LOW_TH;
            const w   = neg ? 5 : (low ? 30 : 100);
            healthBar.style.setProperty('--bar-w', w + '%');
            healthBar.className   = 'ef-wfin-health-bar-fill ' + (neg ? '--neg' : (low ? '--low' : '--healthy'));
        }
    }

    function showToast(message, type) {
        if (type === 'success') {
            toastEl.style.background  = 'var(--ef-hero-grad)';
            toastEl.style.borderColor = 'rgba(255,255,255,.1)';
        } else {
            toastEl.style.background  = 'rgba(200,75,68,.92)';
            toastEl.style.borderColor = 'rgba(200,75,68,.3)';
        }
        toastEl.querySelector('.toast-body').textContent = message;
        bsToast.show();
    }

    function clearModalErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        const err = form.querySelector('.wallet-error');
        if (err) { err.classList.add('d-none'); err.textContent = ''; }
        form.classList.remove('was-validated');
    }

    function showModalErrors(form, errors) {
        const errBox = form.querySelector('.wallet-error');
        if (errors && typeof errors === 'object') {
            Object.entries(errors).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const fb = input.parentNode.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            });
        }
        if (errBox) {
            errBox.textContent = 'Please fix the errors above.';
            errBox.classList.remove('d-none');
        }
    }

    function attachWalletForm(formEl, modalId) {
        const modalEl = document.getElementById(modalId);
        if (!formEl || !modalEl) return;

        const submitBtn = formEl.querySelector('[type="submit"]');

        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            clearModalErrors(formEl);

            if (!formEl.checkValidity()) {
                formEl.classList.add('was-validated');
                return;
            }

            if (submitBtn.disabled) return;

            const originalHtml  = submitBtn.innerHTML;
            submitBtn.disabled  = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Processing…';

            fetch(formEl.action, {
                method : 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept'          : 'application/json',
                    'X-CSRF-TOKEN'    : CSRF,
                },
                body: new FormData(formEl),
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data;
                return data;
            })
            .then(data => {
                bootstrap.Modal.getInstance(modalEl).hide();
                updateBalanceDisplay(data.balance);
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1600);
            })
            .catch(err => {
                submitBtn.disabled  = false;
                submitBtn.innerHTML = originalHtml;

                if (err && err.errors) {
                    showModalErrors(formEl, err.errors);
                } else {
                    const errBox = formEl.querySelector('.wallet-error');
                    if (errBox) {
                        errBox.textContent = (err && err.message) || 'Transaction failed. Please try again.';
                        errBox.classList.remove('d-none');
                    }
                    showToast((err && err.message) || 'Transaction failed.', 'danger');
                }
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            formEl.reset();
            clearModalErrors(formEl);
            submitBtn.disabled  = false;
            submitBtn.innerHTML = submitBtn.dataset.originalHtml || submitBtn.innerHTML;
        });

        submitBtn.dataset.originalHtml = submitBtn.innerHTML;
    }

    attachWalletForm(document.getElementById('creditForm'),  'creditModal');
    attachWalletForm(document.getElementById('debitForm'),   'debitModal');
    attachWalletForm(document.getElementById('adjustForm'),  'adjustModal');
})();
</script>
@endpush

</x-admin-layout>
