<x-admin-layout title="Reports">

@push('styles')
@verbatim
<style>
/* ═══════════════════════════════════════════════════════
   Reports — existing desktop styles (ef-rp-*)
   ═══════════════════════════════════════════════════════ */
:root {
    --rp-gold: #B8893E;
    --rp-gold-hi: #D6B97A;
    --rp-emerald: #0F7B5F;
    --rp-danger: var(--ef-danger);
    --rp-indigo: #4338ca;
}

.ef-rp-hero {
    background: var(--ef-hero-grad);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(4,27,20,.24), 0 1px 4px rgba(4,27,20,.12);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.ef-rp-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(184,137,62,.16) 0%, transparent 68%);
    height: 480px; width: 480px;
    right: -80px; top: -140px;
    pointer-events: none;
}
.ef-rp-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 68%);
    height: 320px; width: 320px;
    bottom: -80px; left: 30%;
    pointer-events: none;
}
.ef-rp-kicker {
    font-size: .7rem; font-weight: 700; letter-spacing: .12em;
    text-transform: uppercase; color: rgba(184,137,62,.9); margin-bottom: 6px;
}
.ef-rp-title {
    font-size: 1.6rem; font-weight: 700; color: #fffdfa;
    margin-bottom: 4px; line-height: 1.2;
}
.ef-rp-subtitle { font-size: .85rem; color: rgba(255,253,250,.48); margin-bottom: 0; }

.ef-rp-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.ef-rp-kpi-card {
    background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: 14px;
    padding: 18px 20px; position: relative; overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.ef-rp-kpi-card:hover { box-shadow: 0 4px 16px rgba(184,137,62,.1); transform: translateY(-1px); }
.ef-rp-kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: 14px 14px 0 0;
}
.kpi-alltime::before   { background: var(--rp-gold); }
.kpi-month::before     { background: var(--rp-indigo); }
.kpi-reimb::before     { background: #d97706; }
.kpi-wallet::before    { background: var(--rp-emerald); }
.kpi-pending::before   { background: #dc2626; }
.kpi-employees::before { background: #0891b2; }
.kpi-vendors::before   { background: #7c3aed; }

.ef-rp-kpi-label {
    font-size: .68rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: var(--ef-muted); margin-bottom: 6px;
}
.ef-rp-kpi-value { font-size: 1.5rem; font-weight: 700; color: var(--ef-ink); line-height: 1; }
.ef-rp-kpi-value.is-alert { color: var(--ef-danger); }
.ef-rp-kpi-sub { font-size: .72rem; color: var(--ef-faint); margin-top: 4px; }

.ef-rp-section-head {
    font-size: .7rem; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--ef-muted);
    margin-bottom: 14px; padding-bottom: 10px;
    border-bottom: 1px solid var(--ef-border);
}

.ef-rp-nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.ef-rp-nav-card {
    background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: 16px;
    padding: 20px; text-decoration: none;
    display: flex; align-items: center; gap: 16px;
    transition: box-shadow .18s, transform .18s, border-color .18s;
    position: relative; overflow: hidden;
}
.ef-rp-nav-card::before {
    content: ''; position: absolute;
    top: 0; left: 0; bottom: 0;
    width: 3px; border-radius: 16px 0 0 16px;
    opacity: 0; transition: opacity .18s;
}
.ef-rp-nav-card:hover {
    box-shadow: 0 6px 24px rgba(184,137,62,.1);
    transform: translateY(-2px);
    border-color: rgba(184,137,62,.25);
    text-decoration: none;
}
.ef-rp-nav-card:hover::before { opacity: 1; }
.ef-rp-nav-card.nav-employee::before  { background: #0891b2; }
.ef-rp-nav-card.nav-category::before  { background: var(--rp-emerald); }
.ef-rp-nav-card.nav-vendor::before    { background: #d97706; }
.ef-rp-nav-card.nav-ledger::before    { background: var(--ef-ink); }
.ef-rp-nav-card.nav-reimb::before     { background: var(--rp-danger); }
.ef-rp-nav-card.nav-daily::before     { background: var(--rp-indigo); }
.ef-rp-nav-card.nav-monthly::before   { background: #7c3aed; }

.ef-rp-nav-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.icon-employee  { background: #e0f2fe; color: #0369a1; }
.icon-category  { background: #dcfce7; color: #15803d; }
.icon-vendor    { background: #fef3c7; color: #b45309; }
.icon-ledger    { background: #f3f4f6; color: #374151; }
.icon-reimb     { background: #fee2e2; color: var(--rp-danger); }
.icon-daily     { background: #ede9fe; color: #5b21b6; }
.icon-monthly   { background: #f3e8ff; color: #7c3aed; }

.ef-rp-nav-title { font-size: .9rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 2px; }
.ef-rp-nav-desc  { font-size: .75rem; color: var(--ef-muted); }
.ef-rp-nav-arrow {
    margin-left: auto; color: var(--ef-faint); font-size: .85rem;
    transition: transform .18s, color .18s;
}
.ef-rp-nav-card:hover .ef-rp-nav-arrow { transform: translateX(3px); color: var(--rp-gold); }

/* ── View toggles ─────────────────────────────────────── */
.ef-rpm-view        { display: none; }
.ef-rp-desktop-view { display: block; }

/* ── Desktop responsive ───────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-rp-hero { padding: 28px; }
    .ef-rp-kpi  { grid-template-columns: repeat(2, 1fr); }
}

/* ═══════════════════════════════════════════════════════
   Mobile Reports — ef-rpm-* namespace
   Shown ≤ 767px. Desktop hidden.
   ═══════════════════════════════════════════════════════ */
@media (max-width: 767.98px) {
    .ef-rp-desktop-view { display: none !important; }
    .ef-rpm-view        { display: block; padding-bottom: 96px; }

/* ── Hero ─────────────────────────────────────────────── */
.ef-rpm-hero {
    background: linear-gradient(148deg, #0f1410 0%, #192219 52%, #0d110b 100%);
    border-radius: 18px;
    margin-bottom: 10px;
    overflow: hidden;
    padding: 20px 18px 18px;
    position: relative;
}
.ef-rpm-hero::before {
    background: radial-gradient(ellipse at 72% 12%, rgba(184,137,62,.14) 0%, transparent 58%),
                radial-gradient(ellipse at 18% 82%, rgba(61,92,58,.1) 0%, transparent 50%);
    content: '';
    inset: 0;
    pointer-events: none;
    position: absolute;
}
.ef-rpm-toprow {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 18px;
    position: relative;
}
.ef-rpm-eyebrow {
    align-items: center;
    color: rgba(245,240,232,.38);
    display: flex;
    font-size: .59rem;
    font-weight: 760;
    gap: 6px;
    letter-spacing: .13em;
    text-transform: uppercase;
}
.ef-rpm-period-badge {
    align-items: center;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    color: rgba(245,240,232,.62);
    display: inline-flex;
    font-size: .67rem;
    font-weight: 700;
    gap: 5px;
    padding: 4px 11px;
}
.ef-rpm-hero-lbl {
    color: rgba(245,240,232,.34);
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .13em;
    margin-bottom: 5px;
    position: relative;
    text-transform: uppercase;
}
.ef-rpm-hero-amount {
    align-items: flex-start;
    display: flex;
    gap: 2px;
    line-height: 1;
    margin-bottom: 5px;
    position: relative;
}
.ef-rpm-hero-currency {
    color: rgba(184,137,62,.72);
    font-size: 1.55rem;
    font-weight: 700;
    padding-top: 6px;
}
.ef-rpm-hero-num {
    color: rgba(245,240,232,.97);
    font-size: 3rem;
    font-variant-numeric: tabular-nums;
    font-weight: 820;
    letter-spacing: -.025em;
}
.ef-rpm-hero-sub {
    color: rgba(245,240,232,.38);
    font-size: .73rem;
    font-weight: 600;
    margin-bottom: 12px;
    position: relative;
}
.ef-rpm-hero-month-tag {
    align-items: center;
    background: rgba(184,137,62,.12);
    border: 1px solid rgba(184,137,62,.22);
    border-radius: 10px;
    display: inline-flex;
    font-size: .7rem;
    font-weight: 700;
    gap: 5px;
    margin-bottom: 14px;
    padding: 5px 11px;
    position: relative;
}
.ef-rpm-hero-month-tag .lbl { color: rgba(245,240,232,.42); }
.ef-rpm-hero-month-tag .val { color: rgba(220,185,100,.95); }
.ef-rpm-hero-alert {
    align-items: center;
    background: rgba(220,38,38,.12);
    border: 1px solid rgba(220,38,38,.24);
    border-radius: 10px;
    color: rgba(252,165,165,.9);
    display: flex;
    font-size: .7rem;
    font-weight: 700;
    gap: 6px;
    margin-bottom: 14px;
    padding: 6px 11px;
    position: relative;
}
.ef-rpm-hero-alert i { flex-shrink: 0; font-size: .78rem; }
.ef-rpm-hero-divider {
    background: rgba(255,255,255,.07);
    border: none;
    height: 1px;
    margin: 0 0 13px;
    position: relative;
}
.ef-rpm-qnav {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 2px;
    position: relative;
    scrollbar-width: none;
}
.ef-rpm-qnav::-webkit-scrollbar { display: none; }
.ef-rpm-qchip {
    align-items: center;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 20px;
    color: rgba(245,240,232,.5);
    display: inline-flex;
    flex-shrink: 0;
    font-size: .71rem;
    font-weight: 720;
    gap: 5px;
    height: 30px;
    padding: 0 12px;
    text-decoration: none;
    transition: background .13s, color .13s, border-color .13s;
}
.ef-rpm-qchip:hover { background: rgba(255,255,255,.11); color: rgba(245,240,232,.88); }
.ef-rpm-qchip i { font-size: .72rem; }

/* ── Primary KPI 2×2 grid ────────────────────────────── */
.ef-rpm-kpi-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: 1fr 1fr;
    margin-bottom: 8px;
}
.ef-rpm-kpi {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    overflow: hidden;
    padding: 14px 14px 12px;
    position: relative;
}
.ef-rpm-kpi::after {
    border-radius: 14px 14px 0 0;
    content: '';
    height: 2px;
    left: 0; right: 0; top: 0;
    position: absolute;
}
.ef-rpm-kpi.--gold::after    { background: #b8893e; }
.ef-rpm-kpi.--indigo::after  { background: #4338ca; }
.ef-rpm-kpi.--amber::after   { background: #d97706; }
.ef-rpm-kpi.--emerald::after { background: var(--ef-emerald); }

.ef-rpm-kpi-icon { color: var(--ef-faint); font-size: .82rem; margin-bottom: 8px; }
.ef-rpm-kpi.--gold    .ef-rpm-kpi-icon { color: #b8893e; }
.ef-rpm-kpi.--indigo  .ef-rpm-kpi-icon { color: #4338ca; }
.ef-rpm-kpi.--amber   .ef-rpm-kpi-icon { color: #d97706; }
.ef-rpm-kpi.--emerald .ef-rpm-kpi-icon { color: var(--ef-emerald); }

.ef-rpm-kpi-val {
    color: var(--ef-ink);
    font-size: 1.22rem;
    font-variant-numeric: tabular-nums;
    font-weight: 820;
    letter-spacing: -.01em;
    line-height: 1;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-rpm-kpi-val.--alert { color: var(--ef-danger); }
.ef-rpm-kpi-lbl {
    color: var(--ef-faint);
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.ef-rpm-kpi-note {
    color: var(--ef-muted);
    font-size: .64rem;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Secondary 3-column strip ───────────────────────────*/
.ef-rpm-sec-strip {
    display: grid;
    gap: 8px;
    grid-template-columns: 1fr 1fr 1fr;
    margin-bottom: 10px;
}
.ef-rpm-sec {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 12px;
    box-shadow: var(--ef-shadow);
    padding: 11px 8px;
    text-align: center;
}
.ef-rpm-sec-icon { color: var(--ef-faint); font-size: .78rem; margin-bottom: 5px; }
.ef-rpm-sec-val  {
    color: var(--ef-ink);
    font-size: 1.15rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-bottom: 3px;
}
.ef-rpm-sec-val.--alert { color: var(--ef-danger); }
.ef-rpm-sec-lbl {
    color: var(--ef-faint);
    font-size: .55rem;
    font-weight: 760;
    letter-spacing: .1em;
    text-transform: uppercase;
}

/* ── Report module list ───────────────────────────────── */
.ef-rpm-modules {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    margin-bottom: 10px;
    overflow: hidden;
}
.ef-rpm-modules-head {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    gap: 8px;
    padding: 10px 16px;
}
.ef-rpm-modules-head-lbl {
    color: var(--ef-faint);
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .12em;
    text-transform: uppercase;
}
.ef-rpm-mod {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.05);
    display: flex;
    gap: 12px;
    padding: 13px 16px;
    position: relative;
    text-decoration: none;
    transition: background .14s;
}
.ef-rpm-mod:last-child { border-bottom: 0; }
.ef-rpm-mod:hover      { background: rgba(20,20,18,.018); text-decoration: none; }
.ef-rpm-mod:active     { background: rgba(20,20,18,.032); }
.ef-rpm-mod::before {
    bottom: 0;
    content: '';
    left: 0;
    opacity: 0;
    position: absolute;
    top: 0;
    transition: opacity .14s;
    width: 3px;
}
.ef-rpm-mod:hover::before { opacity: 1; }
.ef-rpm-mod.--employee::before { background: #0891b2; }
.ef-rpm-mod.--category::before { background: #15803d; }
.ef-rpm-mod.--vendor::before   { background: #d97706; }
.ef-rpm-mod.--ledger::before   { background: #374151; }
.ef-rpm-mod.--reimb::before    { background: #dc2626; }
.ef-rpm-mod.--daily::before    { background: #5b21b6; }
.ef-rpm-mod.--monthly::before  { background: #7c3aed; }

.ef-rpm-mod-icon {
    align-items: center;
    border-radius: 10px;
    display: flex;
    flex-shrink: 0;
    font-size: 1rem;
    height: 40px;
    justify-content: center;
    width: 40px;
}
.ef-rpm-mod-icon.--employee { background: rgba(3,105,161,.1);  color: #0369a1; }
.ef-rpm-mod-icon.--category { background: rgba(21,128,61,.1);  color: #15803d; }
.ef-rpm-mod-icon.--vendor   { background: rgba(180,83,9,.1);   color: #b45309; }
.ef-rpm-mod-icon.--ledger   { background: rgba(55,65,81,.08);  color: #374151; }
.ef-rpm-mod-icon.--reimb    { background: rgba(220,38,38,.09); color: #dc2626; }
.ef-rpm-mod-icon.--daily    { background: rgba(91,33,182,.1);  color: #5b21b6; }
.ef-rpm-mod-icon.--monthly  { background: rgba(124,58,237,.1); color: #7c3aed; }

.ef-rpm-mod-body  { flex: 1; min-width: 0; }
.ef-rpm-mod-title { color: var(--ef-ink);   font-size: .88rem; font-weight: 720; margin-bottom: 2px; }
.ef-rpm-mod-desc  { color: var(--ef-muted); font-size: .72rem; }
.ef-rpm-mod-arrow {
    color: var(--ef-faint);
    flex-shrink: 0;
    font-size: .78rem;
    transition: transform .14s, color .14s;
}
.ef-rpm-mod:hover .ef-rpm-mod-arrow { color: #b8893e; transform: translateX(3px); }

} /* end @media ≤767px */
</style>
@endverbatim
@endpush

@php
$hasPending = $summary['pending_approvals'] > 0 || $summary['pending_reimbursements'] > 0;
$pendingParts = array_filter([
    $summary['pending_approvals']      > 0 ? $summary['pending_approvals'].' approval'.($summary['pending_approvals'] != 1 ? 's' : '') : null,
    $summary['pending_reimbursements'] > 0 ? '₹'.number_format($summary['pending_reimbursements'], 0).' reimb.' : null,
]);
$pendingTxt = implode(' · ', $pendingParts);
@endphp

{{-- ══════════════════════════════════════════════════════
     MOBILE REPORTS VIEW  (hidden ≥ 768px)
     ══════════════════════════════════════════════════════ --}}
<div class="ef-rpm-view">

    {{-- ── Analytics Hero ──────────────────────────────── --}}
    <div class="ef-rpm-hero">
        <div class="ef-rpm-toprow">
            <span class="ef-rpm-eyebrow">
                <i class="bi bi-file-earmark-bar-graph"></i> Financial Reports
            </span>
            <span class="ef-rpm-period-badge">
                <i class="bi bi-calendar3"></i> {{ now()->format('M Y') }}
            </span>
        </div>

        <div class="ef-rpm-hero-lbl">Total Processed</div>
        <div class="ef-rpm-hero-amount">
            <span class="ef-rpm-hero-currency">₹</span>
            <span class="ef-rpm-hero-num">{{ number_format($summary['total_expenses'], 0) }}</span>
        </div>
        <div class="ef-rpm-hero-sub">Paid &amp; settled expenses, all time</div>

        <div class="ef-rpm-hero-month-tag">
            <span class="lbl">This month:</span>
            <span class="val">₹{{ number_format($summary['month_expenses'], 0) }}</span>
            <span style="color:rgba(245,240,232,.28);margin:0 1px">·</span>
            <span class="lbl">{{ now()->format('F') }}</span>
        </div>

        @if($hasPending)
        <div class="ef-rpm-hero-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $pendingTxt }} pending action
        </div>
        @endif

        <hr class="ef-rpm-hero-divider">

        <div class="ef-rpm-qnav">
            <a href="{{ route('admin.reports.employee') }}" class="ef-rpm-qchip">
                <i class="bi bi-person-lines-fill"></i> Employees
            </a>
            <a href="{{ route('admin.reports.category') }}" class="ef-rpm-qchip">
                <i class="bi bi-tag"></i> Categories
            </a>
            <a href="{{ route('admin.reports.monthly') }}" class="ef-rpm-qchip">
                <i class="bi bi-calendar-month"></i> Monthly
            </a>
            <a href="{{ route('admin.reports.vendor') }}" class="ef-rpm-qchip">
                <i class="bi bi-shop"></i> Vendors
            </a>
            <a href="{{ route('admin.reports.ledger') }}" class="ef-rpm-qchip">
                <i class="bi bi-journal-text"></i> Ledger
            </a>
        </div>
    </div>

    {{-- ── Primary KPI grid (2×2) ──────────────────────── --}}
    <div class="ef-rpm-kpi-grid">
        <div class="ef-rpm-kpi --gold">
            <div class="ef-rpm-kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="ef-rpm-kpi-val">₹{{ number_format($summary['total_expenses'], 0) }}</div>
            <div class="ef-rpm-kpi-lbl">Total Expenses</div>
            <div class="ef-rpm-kpi-note">Paid &amp; completed</div>
        </div>
        <div class="ef-rpm-kpi --indigo">
            <div class="ef-rpm-kpi-icon"><i class="bi bi-calendar2-check"></i></div>
            <div class="ef-rpm-kpi-val">₹{{ number_format($summary['month_expenses'], 0) }}</div>
            <div class="ef-rpm-kpi-lbl">This Month</div>
            <div class="ef-rpm-kpi-note">{{ now()->format('F') }}</div>
        </div>
        <div class="ef-rpm-kpi --amber">
            <div class="ef-rpm-kpi-icon"><i class="bi bi-arrow-return-left"></i></div>
            <div class="ef-rpm-kpi-val {{ $summary['pending_reimbursements'] > 0 ? '--alert' : '' }}">
                ₹{{ number_format($summary['pending_reimbursements'], 0) }}
            </div>
            <div class="ef-rpm-kpi-lbl">Pending Reimb.</div>
            <div class="ef-rpm-kpi-note">Awaiting payment</div>
        </div>
        <div class="ef-rpm-kpi --emerald">
            <div class="ef-rpm-kpi-icon"><i class="bi bi-wallet2"></i></div>
            <div class="ef-rpm-kpi-val">₹{{ number_format($summary['total_wallet_balance'], 0) }}</div>
            <div class="ef-rpm-kpi-lbl">Wallet Balance</div>
            <div class="ef-rpm-kpi-note">All employees</div>
        </div>
    </div>

    {{-- ── Secondary metrics strip (3-col) ─────────────── --}}
    <div class="ef-rpm-sec-strip">
        <div class="ef-rpm-sec">
            <div class="ef-rpm-sec-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="ef-rpm-sec-val {{ $summary['pending_approvals'] > 0 ? '--alert' : '' }}">
                {{ $summary['pending_approvals'] }}
            </div>
            <div class="ef-rpm-sec-lbl">Pending</div>
        </div>
        <div class="ef-rpm-sec">
            <div class="ef-rpm-sec-icon"><i class="bi bi-people"></i></div>
            <div class="ef-rpm-sec-val">{{ $summary['active_employees'] }}</div>
            <div class="ef-rpm-sec-lbl">Employees</div>
        </div>
        <div class="ef-rpm-sec">
            <div class="ef-rpm-sec-icon"><i class="bi bi-shop"></i></div>
            <div class="ef-rpm-sec-val">{{ $summary['active_vendors'] }}</div>
            <div class="ef-rpm-sec-lbl">Vendors</div>
        </div>
    </div>

    {{-- ── Report modules ───────────────────────────────── --}}
    <div class="ef-rpm-modules">
        <div class="ef-rpm-modules-head">
            <i class="bi bi-grid" style="color:var(--ef-faint);font-size:.8rem"></i>
            <span class="ef-rpm-modules-head-lbl">Report Modules</span>
        </div>

        <a href="{{ route('admin.reports.employee') }}" class="ef-rpm-mod --employee">
            <div class="ef-rpm-mod-icon --employee"><i class="bi bi-person-lines-fill"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Employee Report</div>
                <div class="ef-rpm-mod-desc">Expenses ranked by employee</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

        <a href="{{ route('admin.reports.category') }}" class="ef-rpm-mod --category">
            <div class="ef-rpm-mod-icon --category"><i class="bi bi-tag"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Category Report</div>
                <div class="ef-rpm-mod-desc">Spend breakdown by category</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

        <a href="{{ route('admin.reports.vendor') }}" class="ef-rpm-mod --vendor">
            <div class="ef-rpm-mod-icon --vendor"><i class="bi bi-shop"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Vendor Report</div>
                <div class="ef-rpm-mod-desc">Expenses grouped by supplier</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

        <a href="{{ route('admin.reports.reimbursement') }}" class="ef-rpm-mod --reimb">
            <div class="ef-rpm-mod-icon --reimb"><i class="bi bi-arrow-return-left"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Reimbursement Report</div>
                <div class="ef-rpm-mod-desc">Track reimbursement status</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

        <a href="{{ route('admin.reports.ledger') }}" class="ef-rpm-mod --ledger">
            <div class="ef-rpm-mod-icon --ledger"><i class="bi bi-journal-text"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Wallet Ledger</div>
                <div class="ef-rpm-mod-desc">Full transaction history</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

        <a href="{{ route('admin.reports.daily') }}" class="ef-rpm-mod --daily">
            <div class="ef-rpm-mod-icon --daily"><i class="bi bi-calendar-day"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Daily Report</div>
                <div class="ef-rpm-mod-desc">Day-wise expense totals</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

        <a href="{{ route('admin.reports.monthly') }}" class="ef-rpm-mod --monthly">
            <div class="ef-rpm-mod-icon --monthly"><i class="bi bi-calendar-month"></i></div>
            <div class="ef-rpm-mod-body">
                <div class="ef-rpm-mod-title">Monthly Report</div>
                <div class="ef-rpm-mod-desc">Month-over-month trends</div>
            </div>
            <i class="bi bi-chevron-right ef-rpm-mod-arrow"></i>
        </a>

    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     DESKTOP VIEW  (hidden ≤ 767px)
     ══════════════════════════════════════════════════════ --}}
<div class="ef-rp-desktop-view">

{{-- Hero --}}
<header class="ef-rp-hero">
    <div style="position:relative;z-index:1">
        <p class="ef-rp-kicker">Analytics</p>
        <h1 class="ef-rp-title">Financial Reports</h1>
        <p class="ef-rp-subtitle">Expense analytics, wallet ledger, reimbursements &amp; workforce insights</p>
    </div>
</header>

{{-- KPI strip --}}
<div class="ef-rp-kpi">
    <div class="ef-rp-kpi-card kpi-alltime">
        <p class="ef-rp-kpi-label">Total Expenses</p>
        <div class="ef-rp-kpi-value">₹{{ number_format($summary['total_expenses'], 0) }}</div>
        <p class="ef-rp-kpi-sub">Paid &amp; completed</p>
    </div>
    <div class="ef-rp-kpi-card kpi-month">
        <p class="ef-rp-kpi-label">This Month</p>
        <div class="ef-rp-kpi-value">₹{{ number_format($summary['month_expenses'], 0) }}</div>
        <p class="ef-rp-kpi-sub">{{ now()->format('F Y') }}</p>
    </div>
    <div class="ef-rp-kpi-card kpi-reimb">
        <p class="ef-rp-kpi-label">Pending Reimb.</p>
        <div class="ef-rp-kpi-value {{ $summary['pending_reimbursements'] > 0 ? 'is-alert' : '' }}">
            ₹{{ number_format($summary['pending_reimbursements'], 0) }}
        </div>
        <p class="ef-rp-kpi-sub">Awaiting payment</p>
    </div>
    <div class="ef-rp-kpi-card kpi-wallet">
        <p class="ef-rp-kpi-label">Wallet Balance</p>
        <div class="ef-rp-kpi-value">₹{{ number_format($summary['total_wallet_balance'], 0) }}</div>
        <p class="ef-rp-kpi-sub">Across all employees</p>
    </div>
</div>

{{-- Secondary KPIs --}}
<div class="ef-rp-kpi" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 32px;">
    <div class="ef-rp-kpi-card kpi-pending">
        <p class="ef-rp-kpi-label">Pending Approvals</p>
        <div class="ef-rp-kpi-value {{ $summary['pending_approvals'] > 0 ? 'is-alert' : '' }}">
            {{ $summary['pending_approvals'] }}
        </div>
        <p class="ef-rp-kpi-sub">Requests awaiting review</p>
    </div>
    <div class="ef-rp-kpi-card kpi-employees">
        <p class="ef-rp-kpi-label">Active Employees</p>
        <div class="ef-rp-kpi-value">{{ $summary['active_employees'] }}</div>
        <p class="ef-rp-kpi-sub">Staff &amp; managers</p>
    </div>
    <div class="ef-rp-kpi-card kpi-vendors">
        <p class="ef-rp-kpi-label">Active Vendors</p>
        <div class="ef-rp-kpi-value">{{ $summary['active_vendors'] }}</div>
        <p class="ef-rp-kpi-sub">Enabled suppliers</p>
    </div>
</div>

{{-- Report navigation --}}
<p class="ef-rp-section-head">Report Modules</p>
<div class="ef-rp-nav-grid">
    <a href="{{ route('admin.reports.employee') }}" class="ef-rp-nav-card nav-employee">
        <div class="ef-rp-nav-icon icon-employee"><i class="bi bi-person-lines-fill"></i></div>
        <div>
            <div class="ef-rp-nav-title">Employee Report</div>
            <div class="ef-rp-nav-desc">Expenses ranked by employee</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
    <a href="{{ route('admin.reports.category') }}" class="ef-rp-nav-card nav-category">
        <div class="ef-rp-nav-icon icon-category"><i class="bi bi-tag"></i></div>
        <div>
            <div class="ef-rp-nav-title">Category Report</div>
            <div class="ef-rp-nav-desc">Spend breakdown by category</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
    <a href="{{ route('admin.reports.vendor') }}" class="ef-rp-nav-card nav-vendor">
        <div class="ef-rp-nav-icon icon-vendor"><i class="bi bi-shop"></i></div>
        <div>
            <div class="ef-rp-nav-title">Vendor Report</div>
            <div class="ef-rp-nav-desc">Expenses grouped by supplier</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
    <a href="{{ route('admin.reports.ledger') }}" class="ef-rp-nav-card nav-ledger">
        <div class="ef-rp-nav-icon icon-ledger"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="ef-rp-nav-title">Wallet Ledger</div>
            <div class="ef-rp-nav-desc">Full transaction history</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
    <a href="{{ route('admin.reports.reimbursement') }}" class="ef-rp-nav-card nav-reimb">
        <div class="ef-rp-nav-icon icon-reimb"><i class="bi bi-arrow-return-left"></i></div>
        <div>
            <div class="ef-rp-nav-title">Reimbursement Report</div>
            <div class="ef-rp-nav-desc">Track reimbursement status</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
    <a href="{{ route('admin.reports.daily') }}" class="ef-rp-nav-card nav-daily">
        <div class="ef-rp-nav-icon icon-daily"><i class="bi bi-calendar-day"></i></div>
        <div>
            <div class="ef-rp-nav-title">Daily Report</div>
            <div class="ef-rp-nav-desc">Day-wise expense totals</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
    <a href="{{ route('admin.reports.monthly') }}" class="ef-rp-nav-card nav-monthly">
        <div class="ef-rp-nav-icon icon-monthly"><i class="bi bi-calendar-month"></i></div>
        <div>
            <div class="ef-rp-nav-title">Monthly Report</div>
            <div class="ef-rp-nav-desc">Month-over-month trends</div>
        </div>
        <i class="bi bi-chevron-right ef-rp-nav-arrow"></i>
    </a>
</div>

</div>

</x-admin-layout>
