<x-admin-layout title="Reports">
@push('styles')
<style>
:root {
    --rp-gold: #a07238;
    --rp-gold-hi: #b8854a;
    --rp-emerald: #1a6645;
    --rp-danger: #b91c1c;
    --rp-indigo: #4338ca;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-rp-hero {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.ef-rp-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
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
    text-transform: uppercase; color: rgba(160,114,56,.9); margin-bottom: 6px;
}
.ef-rp-title {
    font-size: 1.6rem; font-weight: 700; color: #fffdfa;
    margin-bottom: 4px; line-height: 1.2;
}
.ef-rp-subtitle { font-size: .85rem; color: rgba(255,253,250,.48); margin-bottom: 0; }

/* ── KPI strip ────────────────────────────────────────── */
.ef-rp-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.ef-rp-kpi-card {
    background: #fff; border: 1px solid #e8e3dc; border-radius: 14px;
    padding: 18px 20px; position: relative; overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.ef-rp-kpi-card:hover { box-shadow: 0 4px 16px rgba(160,114,56,.1); transform: translateY(-1px); }
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
    text-transform: uppercase; color: #9c8e7e; margin-bottom: 6px;
}
.ef-rp-kpi-value { font-size: 1.5rem; font-weight: 700; color: #1c1612; line-height: 1; }
.ef-rp-kpi-value.is-alert { color: #dc2626; }
.ef-rp-kpi-sub { font-size: .72rem; color: #b0a090; margin-top: 4px; }

/* ── Section heading ──────────────────────────────────── */
.ef-rp-section-head {
    font-size: .7rem; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: #9c8e7e;
    margin-bottom: 14px; padding-bottom: 10px;
    border-bottom: 1px solid #f0ece6;
}

/* ── Report nav cards ─────────────────────────────────── */
.ef-rp-nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.ef-rp-nav-card {
    background: #fff; border: 1px solid #e8e3dc; border-radius: 16px;
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
    box-shadow: 0 6px 24px rgba(160,114,56,.1);
    transform: translateY(-2px);
    border-color: rgba(160,114,56,.25);
    text-decoration: none;
}
.ef-rp-nav-card:hover::before { opacity: 1; }
.ef-rp-nav-card.nav-employee::before  { background: #0891b2; }
.ef-rp-nav-card.nav-category::before  { background: var(--rp-emerald); }
.ef-rp-nav-card.nav-vendor::before    { background: #d97706; }
.ef-rp-nav-card.nav-ledger::before    { background: #1c1612; }
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

.ef-rp-nav-title {
    font-size: .9rem; font-weight: 700; color: #1c1612; margin-bottom: 2px;
}
.ef-rp-nav-desc { font-size: .75rem; color: #9c8e7e; }
.ef-rp-nav-arrow {
    margin-left: auto; color: #c8bfb0; font-size: .85rem;
    transition: transform .18s, color .18s;
}
.ef-rp-nav-card:hover .ef-rp-nav-arrow {
    transform: translateX(3px);
    color: var(--rp-gold);
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-rp-hero { padding: 28px; }
    .ef-rp-kpi  { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 767.98px) {
    .ef-rp-hero  { padding: 20px; }
    .ef-rp-title { font-size: 1.3rem; }
    .ef-rp-kpi   { grid-template-columns: repeat(2, 1fr); }
    .ef-rp-nav-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

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

</x-admin-layout>
