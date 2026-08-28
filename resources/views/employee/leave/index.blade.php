<x-admin-layout title="Leave">

<div class="lv-akshathay">

<x-ds.hero eyebrow="Employee Self-Service" title="Leave"
    :meta="[['icon' => 'bi-calendar-minus', 'text' => 'Track your leave requests']]">
    <x-slot:actions>
        <a href="{{ route('employee.leave.create') }}" class="ef-ds-btn --primary lv-apply-btn">
            <i class="bi bi-plus-lg"></i> <span>Apply Leave</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

@php
$statusChips = [
    'pending'   => ['bg' => 'rgba(180,83,9,.13)',  'color' => '#B45309'],
    'approved'  => ['bg' => 'rgba(21,128,61,.12)', 'color' => '#15803D'],
    'rejected'  => ['bg' => 'rgba(185,28,28,.12)', 'color' => '#B91C1C'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.12)','color' => '#334155'],
];
@endphp

@push('styles')
<style>
    /* Page-scoped Akshathay teal palette — scoped to this page only
       (mirrors the login layout's page-local wrapper convention),
       not a global :root/app-wide rebrand. */
    .lv-akshathay {
        --lv-primary: #0F766E;
        --lv-primary-hover: #0D5F59;
        --lv-accent: #14B8A6;
        --lv-dark: #0F172A;
        --lv-page-bg: #F6F7F5;
        --lv-card-bg: #FFFFFF;
        --lv-text: #111827;
        --lv-text-secondary: #64748B;
        --lv-border: #E5E7EB;
        --lv-divider: #EEF0ED;
        --lv-success: #15803D;
        --lv-warning: #B45309;
        --lv-danger: #B91C1C;
    }

    /* Card system — override the shared .ef-ds-card look within this page only */
    .lv-akshathay .ef-ds-card {
        background: var(--lv-card-bg);
        border: 1px solid var(--lv-border);
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(15,23,42,.04);
    }
    .lv-akshathay .ef-ds-card + .ef-ds-card,
    .lv-akshathay .lv-card-gap { margin-top: 16px; }
    .lv-akshathay .ef-ds-card-head {
        padding: 14px 16px 12px;
        border-bottom: 1px solid var(--lv-divider);
    }
    .lv-akshathay .ef-ds-card-title {
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--lv-text);
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }
    .lv-akshathay .ef-ds-card-title::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--lv-accent);
        flex-shrink: 0;
    }
    .lv-akshathay .ef-ds-card-body { padding: 16px; }

    .lv-apply-btn { background: var(--lv-primary) !important; border-color: var(--lv-primary) !important; }
    .lv-apply-btn:hover { background: var(--lv-primary-hover) !important; border-color: var(--lv-primary-hover) !important; }

    .lv-summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .lv-summary-tile { text-align: center; padding: 4px 2px; }
    .lv-summary-val { font-size: 1.35rem; font-weight: 700; color: var(--lv-text); line-height: 1.15; }
    .lv-summary-lbl { font-size: .68rem; font-weight: 600; color: var(--lv-text-secondary); text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }
    .lv-summary-tile.is-pending .lv-summary-val,
    .lv-summary-tile.is-pending .lv-summary-lbl { color: var(--lv-warning); }
    .lv-summary-tile.is-approved .lv-summary-val,
    .lv-summary-tile.is-approved .lv-summary-lbl { color: var(--lv-success); }
    .lv-summary-tile.is-rejected .lv-summary-val,
    .lv-summary-tile.is-rejected .lv-summary-lbl { color: var(--lv-danger); }

    .lv-list { display: flex; flex-direction: column; }
    .lv-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 4px;
        border-top: 1px solid var(--lv-divider);
        text-decoration: none; color: inherit;
        transition: background .12s;
        margin: 0 -4px;
        padding-left: 4px; padding-right: 4px;
        border-radius: 8px;
    }
    .lv-row:first-child { border-top: none; }
    .lv-row:hover { background: #F6F7F5; }
    .lv-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: nowrap; }
    .lv-row-title { font-weight: 700; font-size: .96rem; overflow-wrap: anywhere; color: var(--lv-text); }
    .lv-row-meta { color: var(--lv-text-secondary); font-size: .8rem; font-weight: 500; margin-top: 2px; }
    @media (min-width: 576px) {
        .lv-row { flex-direction: row; align-items: center; }
        .lv-row-top { flex: 1; }
    }

    .lv-bal-list { display: flex; flex-direction: column; }
    .lv-bal-row { padding: 12px 0; border-top: 1px solid var(--lv-divider); }
    .lv-bal-row:first-child { padding-top: 0; border-top: none; }
    .lv-bal-name { font-weight: 700; margin-bottom: 8px; color: var(--lv-text); font-size: .95rem; }
    .lv-bal-tiles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .lv-bal-tile { text-align: center; }
    .lv-bal-tile-val { font-size: .98rem; font-weight: 700; color: var(--lv-text); }
    .lv-bal-tile-lbl { font-size: .62rem; font-weight: 600; color: var(--lv-text-secondary); text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }
    .lv-bal-tile.is-available .lv-bal-tile-val { color: var(--lv-primary); font-weight: 800; font-size: 1.05rem; }

    .lv-pill {
        display: inline-flex; align-items: center; border-radius: 999px;
        font-size: .7rem; font-weight: 700; padding: 4px 11px;
        text-transform: capitalize; white-space: nowrap;
    }

    .lv-empty { text-align: center; padding: 40px 16px; color: var(--lv-text-secondary); }
</style>
@endpush

<x-ds.card title="Summary">
    <div class="lv-summary-grid">
        <div class="lv-summary-tile is-pending">
            <div class="lv-summary-val">{{ $summary['pending'] }}</div>
            <div class="lv-summary-lbl">Pending</div>
        </div>
        <div class="lv-summary-tile is-approved">
            <div class="lv-summary-val">{{ $summary['approved'] }}</div>
            <div class="lv-summary-lbl">Approved</div>
        </div>
        <div class="lv-summary-tile is-rejected">
            <div class="lv-summary-val">{{ $summary['rejected'] }}</div>
            <div class="lv-summary-lbl">Rejected</div>
        </div>
    </div>
</x-ds.card>

<div class="lv-card-gap">
<x-ds.card title="My Leave Balances">
    @if(empty($balances))
        <div class="lv-empty">
            No active leave types configured yet.
        </div>
    @else
        <div class="lv-bal-list">
            @foreach($balances as $row)
                <div class="lv-bal-row">
                    <div class="lv-bal-name">{{ $row['leave_type']->name }}</div>
                    <div class="lv-bal-tiles">
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['allocated'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Allocated</div>
                        </div>
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['used'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Used</div>
                        </div>
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['pending'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Pending</div>
                        </div>
                        <div class="lv-bal-tile is-available">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['available'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Available</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-ds.card>
</div>

<div class="lv-card-gap">
<x-ds.card title="My Leave Requests">
    <div class="lv-list">
        @forelse($requests as $leaveRequest)
            @php $chip = $statusChips[$leaveRequest->status] ?? $statusChips['pending']; @endphp
            <a href="{{ route('employee.leave.show', $leaveRequest) }}" class="lv-row">
                <div class="lv-row-top">
                    <div>
                        <div class="lv-row-title">{{ $leaveRequest->leaveType->name ?? 'Leave' }}</div>
                        <div class="lv-row-meta">
                            {{ $leaveRequest->start_date->format('d M Y') }}
                            @if(!$leaveRequest->start_date->equalTo($leaveRequest->end_date))
                                &ndash; {{ $leaveRequest->end_date->format('d M Y') }}
                            @endif
                            &middot; {{ rtrim(rtrim(number_format((float) $leaveRequest->days_requested, 1), '0'), '.') }} day(s)
                        </div>
                    </div>
                    <span class="lv-pill" style="background:{{ $chip['bg'] }};color:{{ $chip['color'] }}">
                        {{ $leaveRequest->status }}
                    </span>
                </div>
            </a>
        @empty
            <div class="lv-empty">
                <i class="bi bi-calendar-minus" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No leave requests yet.
                <div style="margin-top:12px">
                    <a href="{{ route('employee.leave.create') }}" class="ef-btn ef-btn-dark lv-apply-btn">
                        <i class="bi bi-plus-lg"></i> Apply Leave
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-ds.card>
</div>

</div>

</x-admin-layout>
