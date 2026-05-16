<x-admin-layout title="Hall Reports">

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Hall Reports</h5>
        <p class="text-muted mb-0" style="font-size:.8rem">{{ $bookings->count() }} booking(s) in selection</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary rounded-3 btn-sm">
        <i class="bi bi-printer me-1"></i>Print
    </button>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold" style="font-size:.72rem;text-transform:uppercase">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm rounded-3" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold" style="font-size:.72rem;text-transform:uppercase">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm rounded-3" value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-md-2">
                <select name="hall_id" class="form-select form-select-sm rounded-3">
                    <option value="">All Halls</option>
                    @foreach($halls as $h)
                        <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select form-select-sm rounded-3">
                    <option value="">All Payments</option>
                    @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                        <option value="{{ $v }}" {{ request('payment_status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="event_type" class="form-select form-select-sm rounded-3">
                    <option value="">All Events</option>
                    @foreach(\App\Models\HallBooking::eventTypes() as $v => $l)
                        <option value="{{ $v }}" {{ request('event_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">Status</option>
                    @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                        <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary rounded-3">Filter</button>
                <a href="{{ route('hall.reports.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2">
            <h4 class="mb-0 fw-bold text-primary">{{ $summary['total_bookings'] }}</h4>
            <p class="text-muted small mb-0">Total Bookings</p>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2">
            <h4 class="mb-0 fw-bold text-success">₹{{ number_format($summary['total_revenue']) }}</h4>
            <p class="text-muted small mb-0">Total Revenue</p>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2">
            <h4 class="mb-0 fw-bold text-info">₹{{ number_format($summary['total_collected']) }}</h4>
            <p class="text-muted small mb-0">Amount Collected</p>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2">
            <h4 class="mb-0 fw-bold text-warning">₹{{ number_format($summary['total_balance']) }}</h4>
            <p class="text-muted small mb-0">Balance Pending</p>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- By hall --}}
    @if($summary['by_hall']->count())
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 fw-semibold small py-3">Revenue by Hall</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Hall</th><th class="text-end">Bookings</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                        @foreach($summary['by_hall'] as $row)
                            <tr>
                                <td class="small fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-end small">{{ $row['count'] }}</td>
                                <td class="text-end small fw-semibold text-success">₹{{ number_format($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- By event type --}}
    @if($summary['by_event']->count())
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 fw-semibold small py-3">Revenue by Event Type</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Event</th><th class="text-end">Bookings</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                        @foreach($summary['by_event'] as $type => $row)
                            <tr>
                                <td class="small fw-semibold">{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                <td class="text-end small">{{ $row['count'] }}</td>
                                <td class="text-end small fw-semibold text-success">₹{{ number_format($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Detailed table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 fw-semibold small py-3">Booking Details</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Hall</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Collected</th>
                    <th class="text-end">Balance</th>
                    <th>Pay Status</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    @php $collected = $b->payments->sum('amount'); $balance = $b->total_amount - $collected; @endphp
                    <tr onclick="location.href='{{ route('hall.bookings.show', $b) }}'" style="cursor:pointer">
                        <td class="small text-muted">{{ $b->id }}</td>
                        <td class="small fw-semibold">{{ $b->customer_name }}</td>
                        <td class="small">{{ $b->hall->name }}</td>
                        <td class="small">{{ ucfirst(str_replace('_', ' ', $b->event_type)) }}</td>
                        <td class="small">{{ $b->booking_date->format('d M Y') }}</td>
                        <td class="text-end small">₹{{ number_format($b->total_amount) }}</td>
                        <td class="text-end small text-success">₹{{ number_format($collected) }}</td>
                        <td class="text-end small {{ $balance > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($balance) }}</td>
                        <td><span class="badge bg-{{ \App\Models\HallBooking::paymentStatusColors()[$b->payment_status] ?? 'secondary' }} rounded-pill" style="font-size:.65rem">{{ ucfirst($b->payment_status) }}</span></td>
                        <td><span class="badge bg-{{ \App\Models\HallBooking::statusColors()[$b->status] ?? 'secondary' }} rounded-pill" style="font-size:.65rem">{{ ucfirst($b->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No bookings in this filter</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</x-admin-layout>
