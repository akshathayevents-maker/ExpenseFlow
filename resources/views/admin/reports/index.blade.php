<x-admin-layout title="Reports">
<div class="page-header">
    <h4 class="mb-0 fw-bold">Reports</h4>
    <p class="text-muted mb-0 small">Financial analytics and summaries</p>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="text-muted small">Total Expenses (All Time)</div>
                    <div class="fw-bold fs-5">₹{{ number_format($summary['total_expenses'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-primary-subtle text-primary"><i class="bi bi-calendar-month"></i></div>
                <div>
                    <div class="text-muted small">This Month</div>
                    <div class="fw-bold fs-5">₹{{ number_format($summary['month_expenses'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-warning-subtle text-warning"><i class="bi bi-arrow-return-left"></i></div>
                <div>
                    <div class="text-muted small">Pending Reimbursements</div>
                    <div class="fw-bold fs-5">₹{{ number_format($summary['pending_reimbursements'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-info-subtle text-info"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="text-muted small">Total Wallet Balance</div>
                    <div class="fw-bold fs-5">₹{{ number_format($summary['total_wallet_balance'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Report links --}}
<div class="row g-3">
    @php
    $reports = [
        ['route' => 'admin.reports.employee',      'icon' => 'bi-person-lines-fill', 'color' => 'primary',   'title' => 'Employee Report',      'desc' => 'Expenses by employee'],
        ['route' => 'admin.reports.category',      'icon' => 'bi-tag',               'color' => 'success',   'title' => 'Category Report',      'desc' => 'Expenses by category'],
        ['route' => 'admin.reports.vendor',        'icon' => 'bi-shop',              'color' => 'warning',   'title' => 'Vendor Report',        'desc' => 'Expenses by vendor'],
        ['route' => 'admin.reports.ledger',        'icon' => 'bi-journal-text',      'color' => 'dark',      'title' => 'Wallet Ledger',        'desc' => 'All wallet transactions'],
        ['route' => 'admin.reports.reimbursement', 'icon' => 'bi-arrow-return-left', 'color' => 'danger',    'title' => 'Reimbursement Report', 'desc' => 'Reimbursement tracking'],
        ['route' => 'admin.reports.daily',         'icon' => 'bi-calendar-day',      'color' => 'info',      'title' => 'Daily Report',         'desc' => 'Day-wise totals'],
        ['route' => 'admin.reports.monthly',       'icon' => 'bi-calendar-month',    'color' => 'secondary', 'title' => 'Monthly Report',       'desc' => 'Month-wise totals'],
    ];
    @endphp
    @foreach($reports as $r)
    <div class="col-md-6 col-xl-4">
        <a href="{{ route($r['route']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-{{ $r['color'] }}-subtle text-{{ $r['color'] }}">
                        <i class="bi {{ $r['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-dark">{{ $r['title'] }}</div>
                        <div class="text-muted small">{{ $r['desc'] }}</div>
                    </div>
                    <i class="bi bi-chevron-right text-muted ms-auto"></i>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
</x-admin-layout>
