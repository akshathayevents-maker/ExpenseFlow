<x-admin-layout title="Regularization #{{ $regularization->id }}">

@php
$statusChips = [
    'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
    'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
    'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
];
$chip = $statusChips[$regularization->request_status] ?? $statusChips['pending'];
$canCancel = auth()->user()->can('cancel', $regularization);
@endphp

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('employee.attendance.index') }}" class="ef-back" title="Back to Attendance">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Regularization #{{ $regularization->id }}</h1>
            <p class="ef-form-page-sub">{{ $regularization->attendance_date->format('d M Y (l)') }}</p>
        </div>
        <span style="margin-left:auto;display:inline-flex;align-items:center;border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize">
            {{ $regularization->request_status }}
        </span>
    </div>

    <x-ds.card title="Request Details">
        <div class="ef-form-grid ef-form-grid-2">
            <div>
                <div class="ef-label" style="margin-bottom:2px">Attendance Date</div>
                <div style="font-weight:600">{{ $regularization->attendance_date->format('d M Y (l)') }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Requested Status</div>
                <div style="font-weight:600;text-transform:capitalize">{{ str_replace('_', ' ', $regularization->requested_status) }}</div>
            </div>
            <div style="grid-column:1 / -1">
                <div class="ef-label" style="margin-bottom:2px">Reason</div>
                <div style="white-space:pre-wrap;word-break:break-word">{{ $regularization->reason }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Submitted At</div>
                <div style="font-weight:600">{{ $regularization->created_at->format('d M Y, h:i A') }}</div>
            </div>

            @if($regularization->reviewed_by)
            <div>
                <div class="ef-label" style="margin-bottom:2px">Reviewed By</div>
                <div style="font-weight:600">{{ $regularization->reviewer->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Reviewed At</div>
                <div style="font-weight:600">{{ $regularization->reviewed_at?->format('d M Y, h:i A') ?? '—' }}</div>
            </div>
            @endif

            @if($regularization->review_note)
            <div style="grid-column:1 / -1">
                <div class="ef-label" style="margin-bottom:2px">Review Note</div>
                <div style="white-space:pre-wrap;word-break:break-word">{{ $regularization->review_note }}</div>
            </div>
            @endif
        </div>

        @if($canCancel)
        <hr class="ef-form-divider">
        <form method="POST" action="{{ route('employee.attendance-regularizations.cancel', $regularization) }}"
              onsubmit="return confirm('Cancel this regularization request?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="ef-btn" style="color:var(--ef-danger)">
                <i class="bi bi-x-circle"></i> Cancel Request
            </button>
        </form>
        @endif
    </x-ds.card>
</div>

</x-admin-layout>
