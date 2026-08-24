{{--
    Shared OT detail page — used by employee/manager/admin show views.
    Expects: $ot (EmployeeOvertime, with user/reviewer/creator loaded), $routePrefix ('employee'|'manager'|'admin').
--}}
@php
    $isEmployeeView = $routePrefix === 'employee';
    $canApprove = auth()->user()->can('approve', $ot);
    $canReject  = auth()->user()->can('reject', $ot);
    $canCancel  = auth()->user()->can('cancel', $ot);

@endphp

{{-- Header only uses .ef-form-page's max-width — the 2-column body below
     must NOT inherit that 640px cap (that class is sized for single-column
     forms), so the row lives outside this wrapper, matching how
     partials/expense-request-detail.blade.php lets its row/col-lg-8/4 use
     the full #main-content width. --}}
<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route($routePrefix . '.overtime.index') }}" class="ef-back" title="Back to Overtime">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Overtime #{{ $ot->id }}</h1>
            <p class="ef-form-page-sub">
                {{ $ot->ot_date->format('d M Y') }} · {{ $ot->hours }}h ·
                <span style="text-transform:capitalize">{{ $ot->category }}</span>
            </p>
        </div>
        <div style="margin-left:auto"><x-overtime-status-badge :status="$ot->request_status" /></div>
    </div>
</div>

<div class="row g-4">
      <div class="col-lg-8">
        <x-ds.card title="Overtime Details">
            <div class="ef-form-grid ef-form-grid-2">
                @unless($isEmployeeView)
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Employee</div>
                    <div style="font-weight:600">{{ $ot->user->name }}</div>
                </div>
                @endunless

                <div>
                    <div class="ef-label" style="margin-bottom:2px">OT Date</div>
                    <div style="font-weight:600">{{ $ot->ot_date->format('d M Y (l)') }}</div>
                </div>

                <div>
                    <div class="ef-label" style="margin-bottom:2px">Hours</div>
                    <div style="font-weight:600">{{ $ot->hours }}</div>
                </div>

                <div>
                    <div class="ef-label" style="margin-bottom:2px">Category</div>
                    <div style="font-weight:600;text-transform:capitalize">{{ $ot->category }}</div>
                </div>

                @if($ot->hourly_rate_snapshot !== null)
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Hourly Rate</div>
                    <div style="font-weight:600">₹{{ number_format((float) $ot->hourly_rate_snapshot, 2) }}</div>
                </div>
                @endif

                @if($ot->rate_multiplier !== null)
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Multiplier</div>
                    <div style="font-weight:600">{{ number_format((float) $ot->rate_multiplier, 2) }}x</div>
                </div>
                @endif

                @if($ot->calculated_amount !== null)
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Calculated Amount</div>
                    <div style="font-weight:700;color:var(--ef-emerald,#0F7B5F)">₹{{ number_format((float) $ot->calculated_amount, 2) }}</div>
                </div>
                @endif

                <div style="grid-column:1 / -1">
                    <div class="ef-label" style="margin-bottom:2px">Reason</div>
                    <div style="white-space:pre-wrap;word-break:break-word">{{ $ot->reason }}</div>
                </div>
            </div>
        </x-ds.card>
      </div>

      <div class="col-lg-4">
        <x-ds.card title="Review Info">
            <div style="display:flex;flex-direction:column;gap:12px">
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Submitted By</div>
                    <div style="font-weight:600">{{ $ot->creator->name ?? '—' }}</div>
                    <div style="color:var(--ef-faint);font-size:.8rem">{{ $ot->origin === 'admin_recorded' ? 'Recorded by admin' : 'Employee request' }}</div>
                </div>
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Created At</div>
                    <div style="font-weight:600">{{ $ot->created_at->format('d M Y, h:i A') }}</div>
                </div>

                @if($ot->reviewed_by)
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Reviewed By</div>
                    <div style="font-weight:600">{{ $ot->reviewer->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Reviewed At</div>
                    <div style="font-weight:600">{{ $ot->reviewed_at?->format('d M Y, h:i A') ?? '—' }}</div>
                </div>
                @endif

                @if($ot->review_note)
                <div>
                    <div class="ef-label" style="margin-bottom:2px">Review Note</div>
                    <div style="white-space:pre-wrap;word-break:break-word">{{ $ot->review_note }}</div>
                </div>
                @endif
            </div>

            @if($canCancel || $canApprove || $canReject)
            <hr class="ef-form-divider">
            <div style="display:flex;flex-direction:column;gap:8px">
                @if($canCancel)
                <form method="POST" action="{{ route('employee.overtime.cancel', $ot) }}"
                      onsubmit="return confirm('Cancel this overtime request?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="ef-btn" style="width:100%;justify-content:center;color:var(--ef-danger)">
                        <i class="bi bi-x-circle"></i> Cancel Request
                    </button>
                </form>
                @endif

                @if($canApprove)
                <button type="button" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center"
                        data-bs-toggle="modal" data-bs-target="#approveOtModal">
                    <i class="bi bi-check-lg"></i> Approve
                </button>
                @endif

                @if($canReject)
                <button type="button" class="ef-btn" style="width:100%;justify-content:center;color:var(--ef-danger)"
                        data-bs-toggle="modal" data-bs-target="#rejectOtModal">
                    <i class="bi bi-x-lg"></i> Reject
                </button>
                @endif
            </div>
            @endif
        </x-ds.card>
      </div>
    </div>

@if($canApprove)
<div class="modal fade" id="approveOtModal" tabindex="-1" aria-labelledby="approveOtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveOtModalLabel"><i class="bi bi-check-circle-fill me-2" style="color:var(--ef-emerald,#0F7B5F)"></i>Approve Overtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.overtime.approve', $ot) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Approve {{ $ot->hours }}h overtime for {{ $ot->user->name }} on {{ $ot->ot_date->format('d M Y') }}?</p>
                    <label class="ef-label" for="approve_review_note">Note (optional)</label>
                    <textarea class="ef-textarea" id="approve_review_note" name="review_note" rows="2"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($canReject)
<div class="modal fade" id="rejectOtModal" tabindex="-1" aria-labelledby="rejectOtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectOtModalLabel"><i class="bi bi-x-circle-fill me-2" style="color:var(--ef-danger)"></i>Reject Overtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.overtime.reject', $ot) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Rejecting {{ $ot->hours }}h overtime for {{ $ot->user->name }} on {{ $ot->ot_date->format('d M Y') }}.</p>
                    <label class="ef-label" for="reject_review_note">Reason <span style="color:var(--ef-danger)">*</span></label>
                    <textarea class="ef-textarea" id="reject_review_note" name="review_note" rows="3" minlength="5" required></textarea>
                    @error('review_note') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
