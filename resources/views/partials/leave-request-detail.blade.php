{{--
    Shared leave-request detail page — used by admin/manager show views.
    Expects: $leaveRequest (LeaveRequest, with user/leaveType/reviewer loaded), $routePrefix.
--}}
@php
    $canApprove = auth()->user()->can('approve', $leaveRequest);
    $canReject  = auth()->user()->can('reject', $leaveRequest);

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
        <a href="{{ route($routePrefix . '.leave.requests.index') }}" class="ef-back" title="Back to Leave Requests">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">{{ $leaveRequest->user->name ?? 'Leave Request' }}</h1>
            <p class="ef-form-page-sub">{{ $leaveRequest->leaveType->name ?? 'Leave' }} &middot; Submitted {{ $leaveRequest->created_at->format('d M Y') }}</p>
        </div>
        <span style="margin-left:auto;display:inline-flex;align-items:center;border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize">
            {{ $leaveRequest->status }}
        </span>
    </div>

    <x-ds.card title="Request Details">
        <div class="ef-form-grid ef-form-grid-2">
            <div>
                <div class="ef-label" style="margin-bottom:2px">Employee</div>
                <div style="font-weight:600">{{ $leaveRequest->user->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Leave Type</div>
                <div style="font-weight:600">{{ $leaveRequest->leaveType->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">From</div>
                <div style="font-weight:600">{{ $leaveRequest->start_date->format('d M Y') }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">To</div>
                <div style="font-weight:600">{{ $leaveRequest->end_date->format('d M Y') }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Days Requested</div>
                <div style="font-weight:600">{{ rtrim(rtrim(number_format((float) $leaveRequest->days_requested, 1), '0'), '.') }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Paid / LOP Split</div>
                <div style="font-weight:600">
                    {{ rtrim(rtrim(number_format((float) $leaveRequest->paid_leave_days, 1), '0'), '.') }} paid
                    @if($leaveRequest->hasLop())
                        &middot; <span style="color:var(--ef-danger)">{{ rtrim(rtrim(number_format((float) $leaveRequest->lop_days, 1), '0'), '.') }} LOP</span>
                    @endif
                </div>
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

        @if($leaveRequest->reviewed_by)
        <hr class="ef-form-divider">
        <div class="ef-form-grid ef-form-grid-2">
            <div>
                <div class="ef-label" style="margin-bottom:2px">Reviewed By</div>
                <div style="font-weight:600">{{ $leaveRequest->reviewer->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Reviewed At</div>
                <div style="font-weight:600">{{ $leaveRequest->reviewed_at?->format('d M Y, h:i A') ?? '—' }}</div>
            </div>
        </div>
        @endif

        @if($canApprove || $canReject)
        <hr class="ef-form-divider">
        <div class="ef-form-actions">
            @if($canReject)
            <button type="button" class="ef-btn" style="color:var(--ef-danger)" data-bs-toggle="modal" data-bs-target="#rejectLeaveModal">
                <i class="bi bi-x-lg"></i> Reject
            </button>
            @endif
            @if($canApprove)
            <form method="POST" action="{{ route($routePrefix . '.leave.requests.approve', $leaveRequest) }}" onsubmit="return confirm('Approve this leave request?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-check-lg"></i> Approve
                </button>
            </form>
            @endif
        </div>
        @endif
    </x-ds.card>
</div>

@if($canReject)
<div class="modal fade" id="rejectLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle-fill me-2" style="color:var(--ef-danger)"></i>Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.leave.requests.reject', $leaveRequest) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <label class="ef-label" for="review_note">Reason (optional)</label>
                    <textarea id="review_note" name="review_note" rows="3" class="ef-textarea"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn" style="color:var(--ef-danger)">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
