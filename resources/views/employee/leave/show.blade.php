<x-admin-layout title="Leave Request">

@php
$statusChips = [
    'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
    'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
    'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
];
$chip = $statusChips[$leaveRequest->status] ?? $statusChips['pending'];
@endphp

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('employee.leave.index') }}" class="ef-back" title="Back to Leave">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">{{ $leaveRequest->leaveType->name ?? 'Leave' }} Request</h1>
            <p class="ef-form-page-sub">Submitted {{ $leaveRequest->created_at->format('d M Y') }}</p>
        </div>
        <span style="margin-left:auto;display:inline-flex;align-items:center;border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize">
            {{ $leaveRequest->status }}
        </span>
    </div>

    <x-ds.card title="Request Details">
        <div class="ef-form-grid ef-form-grid-2">
            <div>
                <div class="ef-label" style="margin-bottom:2px">Leave Type</div>
                <div style="font-weight:600">{{ $leaveRequest->leaveType->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Days</div>
                <div style="font-weight:600">{{ rtrim(rtrim(number_format((float) $leaveRequest->days_requested, 1), '0'), '.') }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">From</div>
                <div style="font-weight:600">{{ $leaveRequest->start_date->format('d M Y') }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">To</div>
                <div style="font-weight:600">{{ $leaveRequest->end_date->format('d M Y') }}</div>
            </div>
            @if($leaveRequest->is_half_day)
            <div>
                <div class="ef-label" style="margin-bottom:2px">Half Day Period</div>
                <div style="font-weight:600;text-transform:capitalize">{{ str_replace('_', ' ', $leaveRequest->half_day_period) }}</div>
            </div>
            @endif
            <div style="grid-column: 1 / -1">
                <div class="ef-label" style="margin-bottom:2px">Reason</div>
                <div style="overflow-wrap:anywhere">{{ $leaveRequest->reason }}</div>
            </div>
            @if($leaveRequest->review_note)
            <div style="grid-column: 1 / -1">
                <div class="ef-label" style="margin-bottom:2px">Reviewer Note</div>
                <div style="overflow-wrap:anywhere">{{ $leaveRequest->review_note }}</div>
            </div>
            @endif
        </div>

        @can('cancel', $leaveRequest)
            <hr class="ef-form-divider">
            <form method="POST" action="{{ route('employee.leave.cancel', $leaveRequest) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="ef-btn">
                    <i class="bi bi-x-lg"></i> Cancel Request
                </button>
            </form>
        @endcan
    </x-ds.card>
</div>

</x-admin-layout>
