<x-admin-layout title="Booking #{{ $booking->id }}">

<style>
.detail-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; margin-bottom:.15rem; }
.detail-value { font-weight:600; color:#1e293b; font-size:.9rem; }
.amount-big { font-size:1.8rem; font-weight:800; color:#16a34a; }
.pay-row { padding:.6rem 0; border-bottom:1px solid #f1f5f9; }
.pay-row:last-child { border-bottom:none; }
</style>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hall.bookings.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-bold">Booking #{{ $booking->id }} — {{ $booking->customer_name }}</h5>
            <p class="text-muted mb-0" style="font-size:.8rem">{{ $booking->booking_date->format('d M Y') }} · {{ $booking->hall->name }}</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('hall.bookings.edit', $booking) }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('hall.bookings.destroy', $booking) }}"
              onsubmit="return confirm('Delete this booking?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger rounded-3">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-3" style="max-width:900px">

    {{-- Main info card --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div>
                        @php $sc = \App\Models\HallBooking::statusColors()[$booking->status] ?? 'secondary'; @endphp
                        <span class="badge bg-{{ $sc }} rounded-pill px-3 py-2">{{ ucfirst($booking->status) }}</span>
                        @php $pc = \App\Models\HallBooking::paymentStatusColors()[$booking->payment_status] ?? 'secondary'; @endphp
                        <span class="badge bg-{{ $pc }} rounded-pill px-3 py-2 ms-1">{{ ucfirst($booking->payment_status) }} Payment</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Customer</p>
                        <p class="detail-value mb-0">{{ $booking->customer_name }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Mobile</p>
                        <p class="detail-value mb-0">{{ $booking->customer_mobile }}</p>
                        @if($booking->customer_alt_mobile)
                            <p class="text-muted mb-0" style="font-size:.8rem">Alt: {{ $booking->customer_alt_mobile }}</p>
                        @endif
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Event Type</p>
                        <p class="detail-value mb-0">{{ ucfirst(str_replace('_', ' ', $booking->event_type)) }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Hall</p>
                        <p class="detail-value mb-0">{{ $booking->hall->name }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Date</p>
                        <p class="detail-value mb-0">{{ $booking->booking_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Time</p>
                        <p class="detail-value mb-0">{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">People</p>
                        <p class="detail-value mb-0">{{ $booking->number_of_people }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Meal Plan</p>
                        <p class="detail-value mb-0">{{ $booking->mealPlan?->name ?? '—' }}</p>
                    </div>
                    <div class="col-6 col-md-4">
                        <p class="detail-label">Meals</p>
                        <p class="detail-value mb-0">
                            @if($booking->has_breakfast)<span class="badge bg-light text-dark me-1">Breakfast</span>@endif
                            @if($booking->has_lunch)<span class="badge bg-light text-dark me-1">Lunch</span>@endif
                            @if($booking->has_dinner)<span class="badge bg-light text-dark me-1">Dinner</span>@endif
                            @if(!$booking->has_breakfast && !$booking->has_lunch && !$booking->has_dinner)—@endif
                        </p>
                    </div>
                    @if($booking->notes)
                        <div class="col-12">
                            <p class="detail-label">Notes</p>
                            <p class="detail-value mb-0 fw-normal">{{ $booking->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Payment summary --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4 text-center">
                <p class="detail-label">Total Amount</p>
                <div class="amount-big">₹{{ number_format($booking->total_amount, 2) }}</div>
                <hr>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Paid</span>
                    <span class="fw-semibold text-success">₹{{ number_format($booking->total_paid, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Balance</span>
                    <span class="fw-semibold {{ $booking->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                        ₹{{ number_format($booking->balance_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Add payment --}}
        @if($booking->balance_amount > 0 && !$booking->isCancelled())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 fw-semibold small py-3">Record Payment</div>
            <div class="card-body p-3">
                <form method="POST" action="{{ route('hall.bookings.payments.add', $booking) }}">
                    @csrf
                    <div class="mb-2">
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="Amount (₹)"
                               value="{{ $booking->balance_amount }}" class="form-control form-control-sm rounded-3" required>
                    </div>
                    <div class="mb-2">
                        <select name="payment_method" class="form-select form-select-sm rounded-3">
                            @foreach(\App\Models\BookingPayment::methods() as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="payment_type" class="form-select form-select-sm rounded-3">
                            @foreach(\App\Models\BookingPayment::types() as $v => $l)
                                <option value="{{ $v }}" {{ $v === 'balance' ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="date" name="paid_at" value="{{ today()->toDateString() }}" class="form-control form-control-sm rounded-3" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="reference_number" placeholder="Reference / UTR (optional)" class="form-control form-control-sm rounded-3">
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100 rounded-3">
                        <i class="bi bi-plus-circle me-1"></i>Record Payment
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Payment history --}}
    @if($booking->payments->count())
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 fw-semibold small py-3">Payment History</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Date</th><th>Type</th><th>Method</th><th>Reference</th><th>Amount</th><th>By</th></tr></thead>
                    <tbody>
                        @foreach($booking->payments as $p)
                            <tr>
                                <td class="small">{{ $p->paid_at->format('d M Y') }}</td>
                                <td class="small">{{ ucfirst($p->payment_type) }}</td>
                                <td class="small">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
                                <td class="small text-muted">{{ $p->reference_number ?: '—' }}</td>
                                <td class="small fw-semibold text-success">₹{{ number_format($p->amount, 2) }}</td>
                                <td class="small text-muted">{{ $p->recorder->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

</x-admin-layout>
