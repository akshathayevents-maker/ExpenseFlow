{{--
    Shared OT list — mobile-first card list (matches the existing
    expense-request card-feed convention: no <table>, cards reflow at every
    width, no horizontal page scroll). Reused by employee/manager/admin index
    views.

    Expects:
        $records       — Collection of EmployeeOvertime (with user/reviewer loaded)
        $showEmployee  — bool, show the employee name row (manager/admin only)
        $showOrigin    — bool, show origin tag (admin only)
        $showRoutePrefix — route prefix for the "view" link ('employee'|'manager'|'admin')
        $emptyRoute    — optional route name for the empty-state CTA
        $emptyLabel    — optional label for the empty-state CTA
--}}
@php
    $showEmployee = $showEmployee ?? false;
    $showOrigin   = $showOrigin ?? false;
    $showDelete   = $showDelete ?? false;
    $stripeColors = [
        'pending' => '#D89A3D', 'approved' => '#0F7B5F',
        'rejected' => '#C84B44', 'cancelled' => '#64748B',
    ];
@endphp

@once
    @push('styles')
    <style>
        .ot-list { display: flex; flex-direction: column; gap: 10px; }
        .ot-list-card {
            background: #fff; border: 1px solid var(--ef-border, #e5e7eb);
            border-left: 4px solid var(--ef-border-strong, #cbd5e1);
            border-radius: 10px; padding: 12px 14px;
            display: flex; flex-direction: column; gap: 10px;
        }
        .ot-list-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap; }
        .ot-list-name { font-weight: 700; word-break: break-word; }
        .ot-list-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
        .ot-list-bottom { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .ot-list-amount { font-weight: 700; font-size: 1rem; }
        .ot-list-origin { font-size: .72rem; color: var(--ef-faint, #6b7280); text-transform: uppercase; letter-spacing: .03em; }
        .ot-list-empty { text-align: center; padding: 48px 16px; color: var(--ef-faint, #6b7280); }
        .ot-list-empty i { font-size: 1.6rem; display: block; margin-bottom: 10px; }
        @media (min-width: 576px) {
            .ot-list-card { flex-direction: row; align-items: center; justify-content: space-between; }
            .ot-list-top, .ot-list-bottom { flex: 1; }
            .ot-list-bottom { justify-content: flex-end; }
        }
    </style>
    @endpush
@endonce

<div class="ot-list">
    @forelse($records as $record)
        @php $stripe = $stripeColors[$record->request_status] ?? $stripeColors['pending']; @endphp
        <div class="ot-list-card" style="border-left-color:{{ $stripe }}">
            <div class="ot-list-top">
                <div>
                    @if($showEmployee)
                        <div class="ot-list-name">{{ $record->user->name }}</div>
                    @endif
                    <div class="ot-list-meta">
                        {{ $record->ot_date->format('d M Y (D)') }} · {{ $record->hours }}h ·
                        <span style="text-transform:capitalize">{{ $record->category }}</span>
                        @if($showOrigin)
                            · <span class="ot-list-origin">{{ $record->origin === 'admin_recorded' ? 'Admin' : 'Employee' }}</span>
                        @endif
                    </div>
                </div>
                <x-overtime-status-badge :status="$record->request_status" />
            </div>
            <div class="ot-list-bottom">
                <div class="ot-list-amount">
                    @php
                        // Approved records are payable at approved_amount (the
                        // final authorized figure, which may differ from
                        // calculated_amount when a manual override was used at
                        // approval time). Pending/non-approved records have no
                        // approved_amount yet, so calculated_amount — where
                        // present — is only ever shown as a pre-approval preview.
                        $displayAmount = $record->request_status === 'approved'
                            ? $record->approved_amount
                            : $record->calculated_amount;
                    @endphp
                    {{ $displayAmount !== null ? '₹' . number_format((float) $displayAmount, 2) : '—' }}
                </div>
                <a href="{{ route($showRoutePrefix . '.overtime.show', $record) }}" class="ef-btn" style="padding:6px 12px; gap:6px; display:inline-flex; align-items:center;" title="View">
                    <i class="bi bi-eye"></i> <span>View</span>
                </a>
                @if($showDelete && auth()->user()->can('delete', $record))
                <form method="POST" action="{{ route('admin.overtime.destroy', $record) }}"
                      onsubmit="return confirm('Delete this overtime entry?\n\nThis will remove it from payroll calculations for this pay period. This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ef-btn" style="padding:6px 12px; gap:6px; display:inline-flex; align-items:center; color:var(--ef-danger)" title="Delete Overtime">
                        <i class="bi bi-trash"></i> <span>Delete</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    @empty
        <div class="ot-list-empty">
            <i class="bi bi-clock-history"></i>
            No overtime requests found.
            @if(!empty($emptyRoute))
                <div style="margin-top:14px">
                    <a href="{{ route($emptyRoute) }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-lg"></i> {{ $emptyLabel ?? 'Request Overtime' }}
                    </a>
                </div>
            @endif
        </div>
    @endforelse
</div>
