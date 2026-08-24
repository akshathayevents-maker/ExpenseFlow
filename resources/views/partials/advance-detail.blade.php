{{--
    Shared advance detail page — used by employee/manager/admin show views.
    Expects: $advance (EmployeeAdvance, with transactions/user/approver/payer loaded), $routePrefix.
--}}
@php
    $isEmployeeView = $routePrefix === 'employee';
    $canCancel  = auth()->user()->can('cancel', $advance);
    $canApprove = auth()->user()->can('approve', $advance);
    $canReject  = auth()->user()->can('reject', $advance);
    $canDisburse = auth()->user()->can('disburse', $advance);
    $canRepay   = auth()->user()->can('recordRepayment', $advance);

    $statusChips = [
        'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
        'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
        'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
        'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
    ];
    $chip = $statusChips[$advance->request_status] ?? $statusChips['pending'];
@endphp

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route($routePrefix . '.advances.index') }}" class="ef-back" title="Back to Advances">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Advance #{{ $advance->id }}</h1>
            <p class="ef-form-page-sub">Requested {{ $advance->created_at->format('d M Y') }}</p>
        </div>
        <span style="margin-left:auto;display:inline-flex;align-items:center;border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize">
            {{ $advance->request_status }}
        </span>
    </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <x-ds.card title="Request Information">
        <div class="ef-form-grid ef-form-grid-2">
            @unless($isEmployeeView)
            <div>
                <div class="ef-label" style="margin-bottom:2px">Employee</div>
                <div style="font-weight:600">{{ $advance->user->name }}</div>
            </div>
            @endunless

            <div>
                <div class="ef-label" style="margin-bottom:2px">Requested Amount</div>
                <div style="font-weight:600">₹{{ number_format((float) $advance->requested_amount, 2) }}</div>
            </div>

            @if($advance->approved_amount !== null)
            <div>
                <div class="ef-label" style="margin-bottom:2px">Approved Amount</div>
                <div style="font-weight:600">₹{{ number_format((float) $advance->approved_amount, 2) }}</div>
            </div>
            @endif

            <div>
                <div class="ef-label" style="margin-bottom:2px">Origin</div>
                <div style="font-weight:600">{{ $advance->origin === 'admin_recorded' ? 'Admin Recorded' : 'Employee Request' }}</div>
            </div>

            <div>
                <div class="ef-label" style="margin-bottom:2px">Payment Status</div>
                <div style="font-weight:600;text-transform:capitalize">{{ $advance->payment_status }}</div>
            </div>

            @if($advance->notes)
            <div style="grid-column:1 / -1">
                <div class="ef-label" style="margin-bottom:2px">Notes</div>
                <div style="white-space:pre-wrap;word-break:break-word">{{ $advance->notes }}</div>
            </div>
            @endif
        </div>
    </x-ds.card>

    @if($advance->isApproved() || $advance->isPaid())
    <div class="mt-3">
    <x-ds.card title="Financial Summary">
        <div class="ef-form-grid ef-form-grid-2">
            <div>
                <div class="ef-label" style="margin-bottom:2px">Disbursed</div>
                <div style="font-weight:700">₹{{ number_format((float) $advance->original_amount, 2) }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Outstanding</div>
                <div style="font-weight:700;color:var(--ef-danger)">₹{{ number_format((float) $advance->outstanding_amount, 2) }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Repaid So Far</div>
                <div style="font-weight:700;color:var(--ef-emerald,#0F7B5F)">₹{{ number_format((float) $advance->original_amount - (float) $advance->outstanding_amount, 2) }}</div>
            </div>
        </div>
    </x-ds.card>
    </div>
    @endif

    <div class="mt-3">
    <x-ds.card title="Transaction History" :no-pad="true">
        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th class="r">Amount</th>
                        <th class="r">Balance After</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advance->transactions as $txn)
                    <tr>
                        <td style="white-space:nowrap">{{ $txn->transaction_date->format('d M Y') }}</td>
                        <td style="text-transform:capitalize">{{ $txn->type }}</td>
                        <td class="r">₹{{ number_format((float) $txn->amount, 2) }}</td>
                        <td class="r">₹{{ number_format((float) $txn->balance_after, 2) }}</td>
                        <td>{{ $txn->reference ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:30px;color:var(--ef-faint)">No transactions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ds.card>
    </div>
  </div>

  <div class="col-lg-4">
    <x-ds.card title="Review Info">
        <div style="display:flex;flex-direction:column;gap:12px">
            @if($advance->approved_by)
            <div>
                <div class="ef-label" style="margin-bottom:2px">Reviewed By</div>
                <div style="font-weight:600">{{ $advance->approver->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Reviewed At</div>
                <div style="font-weight:600">{{ $advance->approved_at?->format('d M Y, h:i A') ?? '—' }}</div>
            </div>
            @endif

            @if($advance->paid_by)
            <div>
                <div class="ef-label" style="margin-bottom:2px">Disbursed By</div>
                <div style="font-weight:600">{{ $advance->payer->name ?? '—' }}</div>
            </div>
            <div>
                <div class="ef-label" style="margin-bottom:2px">Disbursed At</div>
                <div style="font-weight:600">{{ $advance->paid_at?->format('d M Y, h:i A') ?? '—' }}</div>
            </div>
            @endif

            @unless($advance->approved_by || $advance->paid_by)
            <p style="color:var(--ef-faint,#6b7280);font-size:.86rem;margin:0">Not yet reviewed.</p>
            @endunless
        </div>

        @if($canCancel || $canApprove || $canReject || $canDisburse || $canRepay)
        <hr class="ef-form-divider">
        <div style="display:flex;flex-direction:column;gap:8px">
            @if($canCancel)
            <form method="POST" action="{{ route('employee.advances.cancel', $advance) }}" onsubmit="return confirm('Cancel this advance request?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="ef-btn" style="width:100%;justify-content:center;color:var(--ef-danger)">
                    <i class="bi bi-x-circle"></i> Cancel Request
                </button>
            </form>
            @endif

            @if($canApprove)
            <button type="button" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center" data-bs-toggle="modal" data-bs-target="#approveAdvModal">
                <i class="bi bi-check-lg"></i> Approve
            </button>
            @endif

            @if($canReject)
            <form method="POST" action="{{ route($routePrefix . '.advances.reject', $advance) }}" onsubmit="return confirm('Reject this advance request?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="ef-btn" style="width:100%;justify-content:center;color:var(--ef-danger)">
                    <i class="bi bi-x-lg"></i> Reject
                </button>
            </form>
            @endif

            @if($canDisburse)
            <form method="POST" action="{{ route($routePrefix . '.advances.disburse', $advance) }}" onsubmit="return confirm('Disburse ₹{{ number_format((float) $advance->approved_amount, 2) }} to {{ $advance->user->name }}?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                    <i class="bi bi-cash-coin"></i> Disburse
                </button>
            </form>
            @endif

            @if($canRepay)
            <button type="button" class="ef-btn" style="width:100%;justify-content:center" data-bs-toggle="modal" data-bs-target="#repayAdvModal">
                <i class="bi bi-arrow-down-circle"></i> Record Repayment
            </button>
            @endif
        </div>
        @endif
    </x-ds.card>
  </div>
</div>

@if($canApprove)
<div class="modal fade" id="approveAdvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2" style="color:var(--ef-emerald,#0F7B5F)"></i>Approve Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.advances.approve', $advance) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Approve advance for {{ $advance->user->name }}. Requested: ₹{{ number_format((float) $advance->requested_amount, 2) }}</p>
                    <label class="ef-label" for="approved_amount">Approved Amount <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="approved_amount" name="approved_amount" class="ef-input"
                           value="{{ number_format((float) $advance->requested_amount, 2, '.', '') }}" required>
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

@if($canRepay)
<div class="modal fade" id="repayAdvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-down-circle-fill me-2"></i>Record Repayment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.advances.repayment.store', $advance) }}">
                @csrf
                <div class="modal-body">
                    <p>Outstanding balance: ₹{{ number_format((float) $advance->outstanding_amount, 2) }}</p>
                    <label class="ef-label" for="repay_amount">Amount <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" step="0.01" min="0.01" max="{{ number_format((float) $advance->outstanding_amount, 2, '.', '') }}"
                           id="repay_amount" name="amount" class="ef-input" required>
                    @error('amount') <div class="ef-field-error">{{ $message }}</div> @enderror

                    <label class="ef-label" for="repay_reference" style="margin-top:10px">Reference (optional)</label>
                    <input type="text" id="repay_reference" name="reference" class="ef-input" placeholder="e.g. salary deduction — Aug 2026">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark">Record Repayment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
