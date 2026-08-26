<x-admin-layout title="Advance #{{ $advance->id }}">

@php
    $canCancel = auth()->user()->can('cancel', $advance);

    $statusChips = [
        'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
        'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
        'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
        'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
    ];
    $chip = $statusChips[$advance->request_status] ?? $statusChips['pending'];

    $paymentStatusLabel = $advance->payment_status === 'paid' ? 'Paid' : 'Unpaid';
    $originLabel = $advance->origin === 'admin_recorded' ? 'Admin Recorded' : 'Employee Request';
    $isReviewed = (bool) ($advance->approved_by || $advance->paid_by);
@endphp

@push('styles')
<style>
    .advd-wrap { margin: 0 auto; max-width: 920px; padding: 0 16px; }

    .advd-header {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 20px 0 4px;
    }
    .advd-header-main { display: flex; gap: 12px; flex: 1 1 220px; min-width: 0; }
    .advd-title { font-size: 1.25rem; font-weight: 760; letter-spacing: -.02em; line-height: 1.2; margin: 0; overflow-wrap: anywhere; }
    .advd-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; margin: 2px 0 0; }
    .advd-badge {
        align-items: center; border-radius: 6px; display: inline-flex; flex-shrink: 0;
        font-size: .78rem; font-weight: 700; padding: 5px 13px; text-transform: capitalize;
        white-space: nowrap;
    }

    .advd-grid { display: flex; flex-direction: column; gap: 14px; margin-top: 14px; }
    @media (min-width: 992px) {
        .advd-grid { flex-direction: row; align-items: flex-start; }
        .advd-grid-main { flex: 1 1 62%; min-width: 0; }
        .advd-grid-side { flex: 1 1 38%; min-width: 0; }
    }
    .advd-grid-main, .advd-grid-side { display: flex; flex-direction: column; gap: 14px; }

    /* Request info: compact label/value rows, no wasted vertical space */
    .advd-amount-hero { padding: 2px 0 14px; }
    .advd-amount-lbl { color: var(--ef-faint, #6b7280); font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .advd-amount-val { font-size: 1.9rem; font-weight: 800; letter-spacing: -.02em; margin-top: 2px; }
    .advd-kv-list { display: flex; flex-direction: column; }
    .advd-kv-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-top: 1px solid var(--ef-border, #e5e7eb); }
    .advd-kv-row:first-child { border-top: none; }
    .advd-kv-lbl { color: var(--ef-faint, #6b7280); font-size: .78rem; font-weight: 600; }
    .advd-kv-val { font-weight: 650; text-align: right; overflow-wrap: anywhere; }
    .advd-notes { border-top: 1px solid var(--ef-border, #e5e7eb); margin-top: 4px; padding-top: 10px; }
    .advd-notes-lbl { color: var(--ef-faint, #6b7280); font-size: .78rem; font-weight: 600; margin-bottom: 4px; }
    .advd-notes-val { white-space: pre-wrap; word-break: break-word; }

    /* Empty states — compact, not oversized blank cards */
    .advd-empty { align-items: center; display: flex; flex-direction: column; gap: 6px; padding: 22px 12px; text-align: center; }
    .advd-empty i { color: var(--ef-faint, #6b7280); font-size: 1.4rem; }
    .advd-empty-title { font-weight: 650; }
    .advd-empty-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; }

    /* Transactions — cards on mobile, table from 576px up */
    .advd-txn-cards { display: flex; flex-direction: column; gap: 8px; }
    .advd-txn-card { border: 1px solid var(--ef-border, #e5e7eb); border-radius: 8px; padding: 10px 12px; }
    .advd-txn-card-top { align-items: center; display: flex; justify-content: space-between; gap: 8px; }
    .advd-txn-type { font-weight: 650; text-transform: capitalize; }
    .advd-txn-date { color: var(--ef-faint, #6b7280); font-size: .78rem; }
    .advd-txn-card-bottom { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-top: 6px; font-size: .84rem; }
    .advd-txn-amount { font-weight: 700; }
    .advd-txn-balance { color: var(--ef-faint, #6b7280); }
    .advd-txn-ref { color: var(--ef-faint, #6b7280); font-size: .78rem; margin-top: 4px; overflow-wrap: anywhere; }
    .advd-txn-table-wrap { display: none; }
    @media (min-width: 576px) {
        .advd-txn-cards { display: none; }
        .advd-txn-table-wrap { display: block; overflow-x: auto; }
    }

    /* Review info */
    .advd-review-list { display: flex; flex-direction: column; gap: 10px; }

    /* Cancel — clearly separated, full width on mobile */
    .advd-cancel-wrap { margin-top: 4px; }
    .advd-cancel-btn {
        align-items: center; background: #fff; border: 1px solid rgba(200,75,68,.35); border-radius: 10px;
        color: var(--ef-danger, #C84B44); display: flex; font-size: .86rem; font-weight: 680; gap: 8px;
        justify-content: center; min-height: 44px; padding: 0 16px; text-decoration: none; width: 100%;
        transition: background .15s, border-color .15s;
    }
    .advd-cancel-btn:hover { background: rgba(200,75,68,.06); border-color: rgba(200,75,68,.55); color: var(--ef-danger, #C84B44); }
    .advd-cancel-btn:focus-visible { outline: 2px solid var(--ef-danger, #C84B44); outline-offset: 2px; }
</style>
@endpush

<div class="advd-wrap">
    <div class="advd-header">
        <div class="advd-header-main">
            <a href="{{ route('employee.advances.index') }}" class="ef-back" title="Back to Advances" aria-label="Back to Advances">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
            <div style="min-width:0">
                <div class="advd-sub" style="margin-bottom:2px">Advances</div>
                <h1 class="advd-title">Advance #{{ $advance->id }}</h1>
                <p class="advd-sub">Requested {{ $advance->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <span class="advd-badge" style="background:{{ $chip['bg'] }};color:{{ $chip['color'] }}">
            {{ $advance->request_status }}
        </span>
    </div>

    <div class="advd-grid">
        <div class="advd-grid-main">
            <x-ds.card title="Request Information">
                <div class="advd-amount-hero">
                    <div class="advd-amount-lbl">Requested Amount</div>
                    <div class="advd-amount-val">₹{{ number_format((float) $advance->requested_amount, 2) }}</div>
                </div>

                <div class="advd-kv-list" style="border-top:1px solid var(--ef-border,#e5e7eb)">
                    @if($advance->approved_amount !== null)
                    <div class="advd-kv-row">
                        <div class="advd-kv-lbl">Approved Amount</div>
                        <div class="advd-kv-val">₹{{ number_format((float) $advance->approved_amount, 2) }}</div>
                    </div>
                    @endif
                    <div class="advd-kv-row">
                        <div class="advd-kv-lbl">Origin</div>
                        <div class="advd-kv-val">{{ $originLabel }}</div>
                    </div>
                    <div class="advd-kv-row">
                        <div class="advd-kv-lbl">Payment Status</div>
                        <div class="advd-kv-val">{{ $paymentStatusLabel }}</div>
                    </div>
                </div>

                @if($advance->notes)
                <div class="advd-notes">
                    <div class="advd-notes-lbl">Notes</div>
                    <div class="advd-notes-val">{{ $advance->notes }}</div>
                </div>
                @endif
            </x-ds.card>

            @if($advance->isApproved() || $advance->isPaid())
            <x-ds.card title="Financial Summary">
                <div class="advd-kv-list">
                    <div class="advd-kv-row">
                        <div class="advd-kv-lbl">Disbursed</div>
                        <div class="advd-kv-val">₹{{ number_format((float) $advance->original_amount, 2) }}</div>
                    </div>
                    <div class="advd-kv-row">
                        <div class="advd-kv-lbl">Outstanding</div>
                        <div class="advd-kv-val" style="color:var(--ef-danger)">₹{{ number_format((float) $advance->outstanding_amount, 2) }}</div>
                    </div>
                    <div class="advd-kv-row">
                        <div class="advd-kv-lbl">Repaid So Far</div>
                        <div class="advd-kv-val" style="color:var(--ef-emerald,#0F7B5F)">₹{{ number_format((float) $advance->original_amount - (float) $advance->outstanding_amount, 2) }}</div>
                    </div>
                </div>
            </x-ds.card>
            @endif

            <x-ds.card title="Transaction History" :no-pad="true">
                @if($advance->transactions->isEmpty())
                    <div class="advd-empty">
                        <i class="bi bi-receipt" aria-hidden="true"></i>
                        <div class="advd-empty-title">No transactions yet</div>
                        <div class="advd-empty-sub">Disbursement and repayment activity for this advance will appear here.</div>
                    </div>
                @else
                    {{-- Mobile: stacked cards --}}
                    <div class="advd-txn-cards" style="padding:16px">
                        @foreach($advance->transactions as $txn)
                        <div class="advd-txn-card">
                            <div class="advd-txn-card-top">
                                <span class="advd-txn-type">{{ $txn->type }}</span>
                                <span class="advd-txn-date">{{ $txn->transaction_date->format('d M Y') }}</span>
                            </div>
                            <div class="advd-txn-card-bottom">
                                <span class="advd-txn-amount">₹{{ number_format((float) $txn->amount, 2) }}</span>
                                <span class="advd-txn-balance">Balance: ₹{{ number_format((float) $txn->balance_after, 2) }}</span>
                            </div>
                            @if($txn->reference)
                            <div class="advd-txn-ref">Ref: {{ $txn->reference }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop/tablet: compact table --}}
                    <div class="advd-txn-table-wrap">
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
                                @foreach($advance->transactions as $txn)
                                <tr>
                                    <td style="white-space:nowrap">{{ $txn->transaction_date->format('d M Y') }}</td>
                                    <td style="text-transform:capitalize">{{ $txn->type }}</td>
                                    <td class="r">₹{{ number_format((float) $txn->amount, 2) }}</td>
                                    <td class="r">₹{{ number_format((float) $txn->balance_after, 2) }}</td>
                                    <td>{{ $txn->reference ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ds.card>
        </div>

        <div class="advd-grid-side">
            <x-ds.card title="Review Information">
                @if(!$isReviewed)
                    <div class="advd-empty" style="padding:14px 4px">
                        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                        <div class="advd-empty-title">Not reviewed yet</div>
                        <div class="advd-empty-sub">Your request is awaiting admin review.</div>
                    </div>
                @else
                    <div class="advd-review-list">
                        @if($advance->approved_by)
                        <div class="advd-kv-row" style="border-top:none;padding-top:0">
                            <div class="advd-kv-lbl">Reviewed By</div>
                            <div class="advd-kv-val">{{ $advance->approver->name ?? '—' }}</div>
                        </div>
                        <div class="advd-kv-row">
                            <div class="advd-kv-lbl">Reviewed At</div>
                            <div class="advd-kv-val">{{ $advance->approved_at?->format('d M Y, h:i A') ?? '—' }}</div>
                        </div>
                        <div class="advd-kv-row">
                            <div class="advd-kv-lbl">Review Status</div>
                            <div class="advd-kv-val" style="text-transform:capitalize">{{ $advance->request_status }}</div>
                        </div>
                        @endif

                        @if($advance->paid_by)
                        <div class="advd-kv-row">
                            <div class="advd-kv-lbl">Disbursed By</div>
                            <div class="advd-kv-val">{{ $advance->payer->name ?? '—' }}</div>
                        </div>
                        <div class="advd-kv-row">
                            <div class="advd-kv-lbl">Disbursed At</div>
                            <div class="advd-kv-val">{{ $advance->paid_at?->format('d M Y, h:i A') ?? '—' }}</div>
                        </div>
                        @endif
                    </div>
                @endif
            </x-ds.card>

            @if($advance->isPending())
            <div class="advd-cancel-wrap">
                <a href="{{ $advance->whatsAppShareUrl() }}" target="_blank" rel="noopener" class="ef-btn" style="width:100%;justify-content:center;background:#25D366;border-color:#25D366;color:#fff">
                    <i class="bi bi-whatsapp" aria-hidden="true"></i>
                    <span>Submit via WhatsApp</span>
                </a>
            </div>
            @endif

            @if($canCancel)
            <div class="advd-cancel-wrap">
                <button type="button" class="advd-cancel-btn" data-bs-toggle="modal" data-bs-target="#cancelAdvModal">
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    <span>Cancel Request</span>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

@if($canCancel)
<div class="modal fade" id="cancelAdvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2" style="color:var(--ef-danger)" aria-hidden="true"></i>Cancel Advance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="margin:0">Are you sure you want to cancel this advance request for ₹{{ number_format((float) $advance->requested_amount, 2) }}? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Keep Request</button>
                <form method="POST" action="{{ route('employee.advances.cancel', $advance) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="ef-btn" style="color:var(--ef-danger);border-color:rgba(200,75,68,.35)">
                        <i class="bi bi-x-circle" aria-hidden="true"></i> Cancel Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

</x-admin-layout>
