<x-admin-layout title="My Workspace">
@push('styles')
<style>
/* ════════════════════════════════════════════════════════════
   EMPLOYEE WORKSPACE — ef-ew-* namespace
   Attendance/HR-first workspace, expenses de-emphasized
   ════════════════════════════════════════════════════════════ */
:root {
    --ew-emerald:    #0F7B5F;
    --ew-emerald-hi: #22845a;
    --ew-gold:       #B8893E;
    --ew-gold-hi:    #D6B97A;
    --ew-danger:     #b91c1c;
    --ew-amber:      #d97706;
    --ew-indigo:     #4338ca;
    --ew-surface:    #fff;
    --ew-border:     #e8e3dc;
    --ew-text:       #1c1612;
    --ew-muted:      #9c8e7e;
}

/* ── Personal Hero ────────────────────────────────────────── */
.ef-ew-hero {
    background: linear-gradient(135deg, #0f1c14 0%, #152a1e 45%, #0d1f16 100%);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 24px;
    padding: 32px 36px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.ef-ew-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(26,102,69,.32) 0%, transparent 65%);
    height: 520px; width: 520px;
    right: -60px; top: -180px;
    pointer-events: none;
}
.ef-ew-hero-inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
}
.ef-ew-greeting {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: rgba(26,180,96,.8);
    margin-bottom: 6px;
}
.ef-ew-hero-name {
    font-size: 1.6rem;
    font-weight: 800;
    color: #f0fdf4;
    line-height: 1.1;
    margin-bottom: 2px;
}
.ef-ew-hero-role {
    font-size: .8rem;
    font-weight: 600;
    color: rgba(255,253,250,.4);
    text-transform: capitalize;
    margin-bottom: 4px;
    letter-spacing: .03em;
}
.ef-ew-hero-date {
    font-size: .78rem;
    color: rgba(255,253,250,.55);
    margin-bottom: 18px;
}

/* Today attendance chip in hero */
.ef-ew-today-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 14px;
    padding: 10px 16px;
    font-size: .82rem;
    font-weight: 700;
    color: #f0fdf4;
}
.ef-ew-today-chip i { color: #4ade80; }

/* Split-day two-slot visual */
.ef-ew-split-row {
    display: flex;
    gap: 10px;
    margin-top: 14px;
}
.ef-ew-split-slot {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 12px;
    padding: 10px 14px;
    font-size: .78rem;
    color: rgba(255,253,250,.75);
    flex: 1 1 0;
    min-width: 140px;
}
.ef-ew-split-slot .slot-icon { font-size: 1rem; flex-shrink: 0; }
.ef-ew-split-slot.slot-done .slot-icon { color: #4ade80; }
.ef-ew-split-slot.slot-open .slot-icon { color: rgba(255,253,250,.35); }
.ef-ew-split-slot .slot-lbl { font-weight: 700; display: block; }
.ef-ew-split-slot .slot-sub { display: block; color: rgba(255,253,250,.5); font-size: .72rem; margin-top: 1px; }

.ef-ew-hero-cta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    flex-shrink: 0;
}
.ef-ew-btn-primary {
    background: var(--ew-emerald);
    border: 1px solid var(--ew-emerald-hi);
    color: #fff;
    font-size: .88rem;
    font-weight: 700;
    padding: 13px 22px;
    border-radius: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    letter-spacing: .02em;
    box-shadow: 0 4px 16px rgba(26,102,69,.35);
    transition: background .15s, transform .12s, box-shadow .15s;
    min-height: 44px;
    justify-content: center;
}
.ef-ew-btn-primary:hover {
    background: var(--ew-emerald-hi);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26,102,69,.45);
}
.ef-ew-btn-confirmed {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #4ade80;
    font-weight: 700;
    font-size: .85rem;
    background: rgba(74,222,128,.1);
    border: 1px solid rgba(74,222,128,.25);
    padding: 12px 20px;
    border-radius: 14px;
}
.ef-ew-btn-ghost {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,253,250,.7);
    font-size: .78rem;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    transition: background .15s, color .15s;
}
.ef-ew-btn-ghost:hover { background: rgba(255,255,255,.12); color: #fff; }

/* ── Panels ────────────────────────────────────────── */
.ef-ew-panel {
    background: var(--ew-surface);
    border: 1px solid var(--ew-border);
    border-radius: 20px;
    overflow: hidden;
}
.ef-ew-panel-head {
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f5f1eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ef-ew-panel-title {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--ew-muted);
}
.ef-ew-panel-link {
    font-size: .76rem;
    font-weight: 600;
    color: var(--ew-emerald);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ef-ew-panel-link:hover { color: var(--ew-emerald-hi); }
.ef-ew-panel-body { padding: 16px 20px; }

/* ── Dashboard grid layout ──────────────────────────── */
.ef-ew-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.ef-ew-grid .span-2 { grid-column: 1 / -1; }

/* Quick actions */
.ef-ew-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.ef-ew-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 14px 8px;
    min-height: 44px;
    border-radius: 12px;
    border: 1px solid var(--ew-border);
    background: #faf8f5;
    text-decoration: none;
    text-align: center;
    transition: all .15s;
}
.ef-ew-action-card:hover {
    box-shadow: 0 4px 16px rgba(26,102,69,.1);
    border-color: rgba(26,102,69,.2);
    text-decoration: none;
}
.ef-ew-action-card i { font-size: 1.05rem; color: var(--ew-emerald); }
.ef-ew-action-lbl { font-size: .74rem; font-weight: 700; color: var(--ew-text); line-height: 1.2; }

/* Leave balance list */
.ef-ew-lb-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #faf7f4;
    font-size: .84rem;
}
.ef-ew-lb-row:last-child { border-bottom: none; }
.ef-ew-lb-name { font-weight: 600; color: var(--ew-text); }
.ef-ew-lb-sub { font-size: .72rem; color: var(--ew-muted); }
.ef-ew-lb-avail { font-weight: 800; color: var(--ew-emerald); font-size: .95rem; }

/* Compact list items (pending requests / activity) */
.ef-ew-mini-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #faf7f4;
    text-decoration: none;
    color: inherit;
}
.ef-ew-mini-item:last-child { border-bottom: none; }
.ef-ew-mini-item:hover { background: #faf7f4; text-decoration: none; }
.ef-ew-mini-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-pending  { background: var(--ew-amber); }
.dot-approved { background: #22c55e; }
.dot-rejected { background: var(--ew-danger); }
.dot-cancelled{ background: #94a3b8; }
.ef-ew-mini-title { font-size: .84rem; font-weight: 700; color: var(--ew-text); line-height: 1.3; }
.ef-ew-mini-meta { font-size: .72rem; color: var(--ew-muted); }
.ef-ew-mini-status {
    font-size: .64rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 10px;
    margin-left: auto;
    flex-shrink: 0;
}
.stat-pending   { background: #fef3c7; color: #b45309; }
.stat-approved  { background: #dcfce7; color: #15803d; }
.stat-rejected  { background: #fee2e2; color: #b91c1c; }
.stat-cancelled { background: #f1f5f9; color: #475569; }

/* Chips row */
.ef-ew-chips-row { display: flex; gap: 10px; }
.ef-ew-chip-tile {
    flex: 1;
    text-align: center;
    padding: 12px 8px;
    border-radius: 12px;
    background: #faf8f5;
    border: 1px solid var(--ew-border);
}
.ef-ew-chip-val { font-size: 1.2rem; font-weight: 800; color: var(--ew-text); }
.ef-ew-chip-lbl { font-size: .66rem; font-weight: 700; text-transform: uppercase; color: var(--ew-muted); letter-spacing: .04em; }

/* Empty state (small, inline) */
.ef-ew-empty-line {
    text-align: center;
    padding: 18px 12px;
    color: var(--ew-muted);
    font-size: .82rem;
}

/* Advance card */
.ef-ew-advance-amt { font-size: 1.6rem; font-weight: 800; color: var(--ew-emerald); }

/* Expenses shortcut footer card */
.ef-ew-expense-shortcut {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    flex-wrap: wrap;
}

/* ── Alert banner ─────────────────────────────────────────── */
.ef-ew-alert {
    border-radius: 14px;
    padding: 14px 18px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 16px;
    font-size: .84rem;
    font-weight: 500;
}
.ef-ew-alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.ef-ew-alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.ef-ew-alert i { flex-shrink: 0; margin-top: 1px; }
.ef-ew-alert-close {
    margin-left: auto; background: none; border: none; color: inherit;
    opacity: .5; cursor: pointer; padding: 0; flex-shrink: 0; font-size: 1rem;
}
.ef-ew-alert-close:hover { opacity: 1; }

/* ── Floating FAB (mobile only) ────────────────────────────── */
.ef-ew-fab {
    display: none;
    position: fixed;
    bottom: 24px;
    right: 20px;
    z-index: 1030;
    background: var(--ew-emerald);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 58px; height: 58px;
    font-size: 1.4rem;
    box-shadow: 0 4px 20px rgba(26,102,69,.45);
    align-items: center;
    justify-content: center;
    text-decoration: none;
}
.ef-ew-fab:hover { background: var(--ew-emerald-hi); color: #fff; }

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .ef-ew-grid { grid-template-columns: 1fr; }
    .ef-ew-grid .span-2 { grid-column: auto; }
}
@media (max-width: 767.98px) {
    .ef-ew-hero        { padding: 20px 18px; border-radius: 18px; }
    .ef-ew-hero-inner  { flex-direction: column; gap: 16px; }
    .ef-ew-hero-cta    { align-items: stretch; width: 100%; }
    .ef-ew-hero-cta .ef-ew-btn-primary,
    .ef-ew-hero-cta .ef-ew-btn-confirmed { width: 100%; }
    .ef-ew-hero-name   { font-size: 1.3rem; }
    .ef-ew-split-row   { flex-direction: column; }
    .ef-ew-actions-grid{ grid-template-columns: 1fr 1fr; }
    .ef-ew-chips-row   { flex-wrap: wrap; }
    .ef-ew-chip-tile   { min-width: 30%; }
    .ef-ew-fab         { display: flex; bottom: calc(16px + env(safe-area-inset-bottom, 0px)); z-index: 1050; }
    body { padding-bottom: 0; }
}
</style>
@endpush

@php
    $statusChips = [
        'present'        => ['label' => 'Present',        'bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
        'half_day'       => ['label' => 'Half Day',       'bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
        'leave'          => ['label' => 'On Leave',       'bg' => 'rgba(47,111,237,.10)', 'color' => '#1E4DB7'],
        'half_day_leave' => ['label' => 'Half-day Leave', 'bg' => 'rgba(47,111,237,.10)', 'color' => '#1E4DB7'],
        'leave_pending'  => ['label' => 'Leave Pending',  'bg' => 'rgba(184,137,62,.12)', 'color' => '#6B4A12'],
        'absent'         => ['label' => 'Absent',         'bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
        'weekly_off'     => ['label' => 'Weekly Off',     'bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
        'holiday'        => ['label' => 'Holiday',        'bg' => 'rgba(184,137,62,.12)', 'color' => '#6B4A12'],
        'not_marked'     => ['label' => 'Not Marked',     'bg' => 'rgba(100,116,139,.08)','color' => '#64748B'],
    ];

    $attendance = $dayState['attendance'] ?? null;
    if ($attendance) {
        $todayStatus = $attendance->status;
    } elseif ($todayIsNonWorking) {
        $todayStatus = $todayCategory === 'holiday' ? 'holiday' : 'weekly_off';
    } elseif ($dayState['has_pending_leave'] ?? false) {
        $todayStatus = 'leave_pending';
    } else {
        $todayStatus = 'not_marked';
    }
    $todayChip = $statusChips[$todayStatus] ?? $statusChips['not_marked'];
    $todayChipText = $todayStatus === 'not_marked' ? $todayChip['label'] : $todayChip['label'].' today';

    $isHalfDayFamily = $attendance && in_array($attendance->status, ['half_day', 'half_day_leave', 'half_day_lop'], true);
    $firstHalfDone  = $attendance && ($attendance->status === 'present' || ($isHalfDayFamily && $attendance->half_day_period === 'first_half'));
    $secondHalfDone = $attendance && ($attendance->status === 'present' || ($isHalfDayFamily && $attendance->half_day_period === 'second_half'));

    $hasPendingItems = ($leaveCounts['pending'] ?? 0) > 0
        || $pendingRegularizations->isNotEmpty()
        || $pendingOvertime->isNotEmpty();
    $hasRejected = ($leaveCounts['rejected'] ?? 0) > 0;
@endphp

{{-- ── Wallet alerts ──────────────────────────────────────────── --}}
@if($stats['wallet_negative'])
<div class="ef-ew-alert ef-ew-alert-danger" role="alert">
    <i class="bi bi-exclamation-circle-fill"></i>
    <div>Wallet balance is <strong>negative (₹{{ number_format($stats['wallet_balance'], 2) }})</strong>. Contact admin to resolve.</div>
    <button class="ef-ew-alert-close" onclick="this.closest('.ef-ew-alert').remove()"><i class="bi bi-x"></i></button>
</div>
@elseif($stats['wallet_low'])
<div class="ef-ew-alert ef-ew-alert-warning" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>Wallet balance is low (<strong>₹{{ number_format($stats['wallet_balance'], 2) }}</strong>). Contact admin to top up.</div>
    <button class="ef-ew-alert-close" onclick="this.closest('.ef-ew-alert').remove()"><i class="bi bi-x"></i></button>
</div>
@endif

{{-- ── Pending-action alert ───────────────────────────────────── --}}
@if($hasPendingItems || $hasRejected)
<div class="ef-ew-alert ef-ew-alert-warning" role="alert">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        @if($hasPendingItems)
            You have requests awaiting a decision — check <strong>Pending Requests</strong> below.
        @endif
        @if($hasRejected)
            {{ $hasPendingItems ? 'Also, ' : '' }}one or more of your leave requests was rejected — see <strong>Recent Activity</strong>.
        @endif
    </div>
    <button class="ef-ew-alert-close" onclick="this.closest('.ef-ew-alert').remove()"><i class="bi bi-x"></i></button>
</div>
@endif

{{-- ── Personal Hero ───────────────────────────────────────────── --}}
<div class="ef-ew-hero">
    <div class="ef-ew-hero-inner">
        <div>
            @php
                $hour = now()->hour;
                $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
            <p class="ef-ew-greeting">{{ $greet }}</p>
            <h1 class="ef-ew-hero-name">{{ auth()->user()->name }}</h1>
            <p class="ef-ew-hero-role">{{ ucfirst(auth()->user()->role) }}</p>
            <p class="ef-ew-hero-date">{{ now()->format('l, d F Y') }}</p>

            <div class="ef-ew-today-chip">
                <i class="bi bi-calendar-check"></i>
                {{ $todayChipText }}
            </div>

            {{-- Split-day two-slot visual --}}
            <div class="ef-ew-split-row">
                <div class="ef-ew-split-slot {{ $firstHalfDone ? 'slot-done' : 'slot-open' }}">
                    <i class="bi {{ $firstHalfDone ? 'bi-check-circle-fill' : 'bi-circle' }} slot-icon"></i>
                    <span>
                        <span class="slot-lbl">First Half</span>
                        <span class="slot-sub">{{ $firstHalfDone ? $todayChip['label'] : 'Not marked' }}</span>
                    </span>
                </div>
                <div class="ef-ew-split-slot {{ $secondHalfDone ? 'slot-done' : 'slot-open' }}">
                    <i class="bi {{ $secondHalfDone ? 'bi-check-circle-fill' : 'bi-circle' }} slot-icon"></i>
                    <span>
                        <span class="slot-lbl">Second Half</span>
                        <span class="slot-sub">{{ $secondHalfDone ? $todayChip['label'] : 'Not marked' }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="ef-ew-hero-cta">
            @if(!$attendance && !$todayIsNonWorking)
                <a href="{{ route('employee.attendance.index') }}" class="ef-ew-btn-primary">
                    <i class="bi bi-calendar-plus"></i> Mark Attendance
                </a>
            @elseif($markableOtherHalf)
                <a href="{{ route('employee.attendance.index') }}" class="ef-ew-btn-primary">
                    <i class="bi bi-calendar-plus"></i> Mark {{ $markableOtherHalf === 'first_half' ? 'Morning' : 'Afternoon' }}
                </a>
            @elseif($attendance)
                <span class="ef-ew-btn-confirmed">
                    <i class="bi bi-check-circle-fill"></i> Day Marked
                </span>
            @endif
            <a href="{{ route('employee.attendance.index') }}" class="ef-ew-btn-ghost">
                <i class="bi bi-clock-history"></i> Attendance History
            </a>
        </div>
    </div>
</div>

{{-- ── Secondary actions row ──────────────────────────────────── --}}
<div class="ef-ew-panel" style="margin-bottom:16px">
    <div class="ef-ew-panel-head"><span class="ef-ew-panel-title">Quick Actions</span></div>
    <div class="ef-ew-panel-body">
        <div class="ef-ew-actions-grid">
            <a href="{{ route('employee.leave.create') }}" class="ef-ew-action-card">
                <i class="bi bi-calendar-plus"></i>
                <span class="ef-ew-action-lbl">Apply Leave</span>
            </a>
            <a href="{{ route('employee.attendance-regularizations.create') }}" class="ef-ew-action-card">
                <i class="bi bi-pencil-square"></i>
                <span class="ef-ew-action-lbl">Regularize</span>
            </a>
            <a href="{{ route('employee.overtime.create') }}" class="ef-ew-action-card">
                <i class="bi bi-clock-history"></i>
                <span class="ef-ew-action-lbl">Request OT</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Main grid ───────────────────────────────────────────────── --}}
<div class="ef-ew-grid" style="margin-bottom:16px">

    {{-- Leave Balance --}}
    <div class="ef-ew-panel">
        <div class="ef-ew-panel-head">
            <span class="ef-ew-panel-title">Leave Balance</span>
            <a href="{{ route('employee.leave.index') }}" class="ef-ew-panel-link">My Leave <i class="bi bi-arrow-right" style="font-size:.68rem"></i></a>
        </div>
        <div class="ef-ew-panel-body">
            @forelse($leaveBalances as $lb)
                <div class="ef-ew-lb-row">
                    <div>
                        <div class="ef-ew-lb-name">{{ $lb['leave_type']->name }}</div>
                        <div class="ef-ew-lb-sub">Used {{ $lb['used'] }} · Pending {{ $lb['pending'] }}</div>
                    </div>
                    <div class="ef-ew-lb-avail">{{ $lb['available'] }}</div>
                </div>
            @empty
                <div class="ef-ew-empty-line">No leave types configured.</div>
            @endforelse
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="ef-ew-panel">
        <div class="ef-ew-panel-head"><span class="ef-ew-panel-title">Pending Requests</span></div>
        <div class="ef-ew-panel-body">
            @php $anyPending = $pendingLeave->isNotEmpty() || $pendingRegularizations->isNotEmpty() || $pendingOvertime->isNotEmpty(); @endphp
            @if(!$anyPending)
                <div class="ef-ew-empty-line">You're all caught up.</div>
            @else
                @foreach($pendingLeave as $r)
                <a href="{{ route('employee.leave.show', $r) }}" class="ef-ew-mini-item">
                    <span class="ef-ew-mini-dot dot-pending"></span>
                    <span>
                        <span class="ef-ew-mini-title">{{ $r->leaveType->name ?? 'Leave' }}</span>
                        <span class="ef-ew-mini-meta d-block">{{ $r->start_date->format('d M') }} – {{ $r->end_date->format('d M') }}</span>
                    </span>
                    <span class="ef-ew-mini-status stat-pending">Pending</span>
                </a>
                @endforeach
                @foreach($pendingRegularizations as $r)
                <a href="{{ route('employee.attendance-regularizations.show', $r) }}" class="ef-ew-mini-item">
                    <span class="ef-ew-mini-dot dot-pending"></span>
                    <span>
                        <span class="ef-ew-mini-title">Regularization</span>
                        <span class="ef-ew-mini-meta d-block">{{ $r->attendance_date->format('d M') }} — {{ ucfirst(str_replace('_',' ',$r->requested_status)) }}</span>
                    </span>
                    <span class="ef-ew-mini-status stat-pending">Pending</span>
                </a>
                @endforeach
                @foreach($pendingOvertime as $r)
                <a href="{{ route('employee.overtime.show', $r) }}" class="ef-ew-mini-item">
                    <span class="ef-ew-mini-dot dot-pending"></span>
                    <span>
                        <span class="ef-ew-mini-title">Overtime</span>
                        <span class="ef-ew-mini-meta d-block">{{ $r->ot_date->format('d M') }} — {{ $r->hours }}h</span>
                    </span>
                    <span class="ef-ew-mini-status stat-pending">Pending</span>
                </a>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Request summary chips --}}
    <div class="ef-ew-panel span-2">
        <div class="ef-ew-panel-head"><span class="ef-ew-panel-title">Leave Request Summary</span></div>
        <div class="ef-ew-panel-body">
            <div class="ef-ew-chips-row">
                <div class="ef-ew-chip-tile">
                    <div class="ef-ew-chip-val">{{ $leaveCounts['pending'] }}</div>
                    <div class="ef-ew-chip-lbl">Pending</div>
                </div>
                <div class="ef-ew-chip-tile">
                    <div class="ef-ew-chip-val">{{ $leaveCounts['approved'] }}</div>
                    <div class="ef-ew-chip-lbl">Approved</div>
                </div>
                <div class="ef-ew-chip-tile">
                    <div class="ef-ew-chip-val">{{ $leaveCounts['rejected'] }}</div>
                    <div class="ef-ew-chip-lbl">Rejected</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming approved leave --}}
    <div class="ef-ew-panel">
        <div class="ef-ew-panel-head"><span class="ef-ew-panel-title">Upcoming Leave</span></div>
        <div class="ef-ew-panel-body">
            @if($upcomingLeave)
                <div class="ef-ew-lb-row" style="border-bottom:none">
                    <div>
                        <div class="ef-ew-lb-name">{{ $upcomingLeave->leaveType->name ?? 'Leave' }}</div>
                        <div class="ef-ew-lb-sub">{{ $upcomingLeave->start_date->format('d M Y') }} – {{ $upcomingLeave->end_date->format('d M Y') }}</div>
                    </div>
                    <a href="{{ route('employee.leave.show', $upcomingLeave) }}" class="ef-ew-panel-link">View</a>
                </div>
            @else
                <div class="ef-ew-empty-line">
                    No upcoming leave.
                    <a href="{{ route('employee.leave.create') }}" class="ef-ew-panel-link">Apply Leave</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Overtime summary (hours only) --}}
    <div class="ef-ew-panel">
        <div class="ef-ew-panel-head">
            <span class="ef-ew-panel-title">Overtime</span>
            <a href="{{ route('employee.overtime.index') }}" class="ef-ew-panel-link">View all <i class="bi bi-arrow-right" style="font-size:.68rem"></i></a>
        </div>
        <div class="ef-ew-panel-body">
            <div class="ef-ew-chips-row">
                <div class="ef-ew-chip-tile">
                    <div class="ef-ew-chip-val">{{ number_format($otSummary['approved_hours_this_month'], 1) }}</div>
                    <div class="ef-ew-chip-lbl">Hrs Approved ({{ now()->format('M') }})</div>
                </div>
                <div class="ef-ew-chip-tile">
                    <div class="ef-ew-chip-val">{{ $otSummary['pending_count'] }}</div>
                    <div class="ef-ew-chip-lbl">Pending</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="ef-ew-panel span-2">
        <div class="ef-ew-panel-head"><span class="ef-ew-panel-title">Recent Activity</span></div>
        <div class="ef-ew-panel-body">
            @forelse($recentActivity as $a)
                @php
                    $adot = match($a['status']) {
                        'approved' => 'dot-approved',
                        'rejected' => 'dot-rejected',
                        'cancelled' => 'dot-cancelled',
                        default => 'dot-pending',
                    };
                    $astat = match($a['status']) {
                        'approved' => 'stat-approved',
                        'rejected' => 'stat-rejected',
                        'cancelled' => 'stat-cancelled',
                        default => 'stat-pending',
                    };
                @endphp
                <a href="{{ $a['route'] }}" class="ef-ew-mini-item">
                    <span class="ef-ew-mini-dot {{ $adot }}"></span>
                    <span>
                        <span class="ef-ew-mini-title">{{ $a['label'] }}</span>
                        <span class="ef-ew-mini-meta d-block">{{ $a['date']->diffForHumans(['short' => true, 'parts' => 1]) }}</span>
                    </span>
                    <span class="ef-ew-mini-status {{ $astat }}">{{ ucfirst($a['status']) }}</span>
                </a>
            @empty
                <div class="ef-ew-empty-line">No recent activity yet.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Expenses shortcut (de-emphasized) ──────────────────────── --}}
<div class="ef-ew-panel">
    <div class="ef-ew-expense-shortcut">
        <div>
            <div class="ef-ew-panel-title" style="margin-bottom:4px">Expenses</div>
            <div class="ef-ew-lb-sub">Submit and track expense claims. Wallet balance: ₹{{ number_format($stats['wallet_balance'], 2) }}</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('employee.expense-requests.create') }}" class="ef-ew-btn-ghost" style="background:#f5f1eb;color:var(--ew-text);border-color:var(--ew-border)">
                <i class="bi bi-plus-lg"></i> New Expense
            </a>
            <a href="{{ route('employee.expense-requests.index') }}" class="ef-ew-btn-ghost" style="background:#f5f1eb;color:var(--ew-text);border-color:var(--ew-border)">
                <i class="bi bi-list-ul"></i> View Expenses
            </a>
            <a href="{{ route('employee.wallet.show') }}" class="ef-ew-btn-ghost" style="background:#f5f1eb;color:var(--ew-text);border-color:var(--ew-border)">
                <i class="bi bi-wallet2"></i> Wallet
            </a>
        </div>
    </div>
</div>

{{-- Floating FAB (mobile only) --}}
<a href="{{ route('employee.expense-requests.create') }}" class="ef-ew-fab ef-mobile-fab" title="New Expense Request">
    <i class="bi bi-plus-lg"></i>
</a>

</x-admin-layout>
