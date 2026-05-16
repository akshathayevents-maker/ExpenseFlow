<x-admin-layout title="Hall Bookings">

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar2-event me-2 text-primary"></i>Hall Bookings</h5>
        <p class="text-muted mb-0" style="font-size:.8rem">{{ $bookings->total() }} booking(s) found</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('hall.bookings.calendar') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-calendar3 me-1"></i>Calendar
        </a>
        <a href="{{ route('hall.bookings.kitchen') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-cup-hot me-1"></i>Kitchen
        </a>
        <a href="{{ route('hall.bookings.create') }}" class="btn btn-primary rounded-3">
            <i class="bi bi-plus-circle me-1"></i>New Booking
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-lg-2">
                <input type="text" name="search" class="form-control form-control-sm rounded-3"
                       placeholder="Name / Mobile" value="{{ request('search') }}">
            </div>
            <div class="col-6 col-lg-2">
                <select name="hall_id" class="form-select form-select-sm rounded-3">
                    <option value="">All Halls</option>
                    @foreach($halls as $h)
                        <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">All Status</option>
                    @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                        <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="payment_status" class="form-select form-select-sm rounded-3">
                    <option value="">Payment Status</option>
                    @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                        <option value="{{ $v }}" {{ request('payment_status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <input type="date" name="date_from" class="form-control form-control-sm rounded-3" value="{{ request('date_from') }}" placeholder="From">
            </div>
            <div class="col-6 col-lg-1">
                <input type="date" name="date_to" class="form-control form-control-sm rounded-3" value="{{ request('date_to') }}" placeholder="To">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary rounded-3">Filter</button>
                <a href="{{ route('hall.bookings.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Hall</th>
                    <th>Event</th>
                    <th>Date & Time</th>
                    <th>Pax</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    <tr>
                        <td class="text-muted small">{{ $b->id }}</td>
                        <td>
                            <p class="mb-0 fw-semibold small">{{ $b->customer_name }}</p>
                            <p class="mb-0 text-muted" style="font-size:.75rem">{{ $b->customer_mobile }}</p>
                        </td>
                        <td class="small">{{ $b->hall->name }}</td>
                        <td class="small">{{ ucfirst(str_replace('_', ' ', $b->event_type)) }}</td>
                        <td class="small">
                            {{ $b->booking_date->format('d M Y') }}<br>
                            <span class="text-muted">{{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($b->end_time)->format('h:i A') }}</span>
                        </td>
                        <td class="small">{{ $b->number_of_people }}</td>
                        <td class="fw-semibold small">₹{{ number_format($b->total_amount) }}</td>
                        <td>
                            <span class="badge bg-{{ \App\Models\HallBooking::paymentStatusColors()[$b->payment_status] ?? 'secondary' }} rounded-pill">
                                {{ ucfirst($b->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ \App\Models\HallBooking::statusColors()[$b->status] ?? 'secondary' }} rounded-pill">
                                {{ ucfirst($b->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('hall.bookings.show', $b) }}" class="btn btn-sm btn-outline-primary rounded-2" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('hall.bookings.edit', $b) }}" class="btn btn-sm btn-outline-secondary rounded-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x d-block fs-2 mb-2 opacity-40"></i>
                            No bookings found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $bookings->links() }}
        </div>
    @endif
</div>

</x-admin-layout>
