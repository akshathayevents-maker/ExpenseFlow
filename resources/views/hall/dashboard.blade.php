<x-admin-layout title="Hall Management Dashboard">

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Hall Management</h5>
        <p class="text-muted mb-0" style="font-size:.8rem">Overview for {{ today()->format('d F Y') }}</p>
    </div>
    <a href="{{ route('hall.bookings.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-1"></i>New Booking
    </a>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <p class="text-muted mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em">Today's Bookings</p>
                    <h4 class="mb-0 fw-bold">{{ $todayBookings }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-info bg-opacity-10 text-info">
                    <i class="bi bi-calendar2-event"></i>
                </div>
                <div>
                    <p class="text-muted mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em">Upcoming Events</p>
                    <h4 class="mb-0 fw-bold">{{ $upcomingBookings }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-muted mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em">Pending Payments</p>
                    <h4 class="mb-0 fw-bold">{{ $pendingPayments }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-success bg-opacity-10 text-success">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div>
                    <p class="text-muted mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em">Month Revenue</p>
                    <h4 class="mb-0 fw-bold">₹{{ number_format($monthRevenue) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Today's schedule --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3">
                <span class="fw-semibold">Today's Schedule</span>
                <a href="{{ route('hall.bookings.kitchen') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-cup-hot me-1"></i>Kitchen View
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($todayList as $b)
                    <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom">
                        <div class="text-center" style="min-width:48px">
                            <div class="small fw-bold text-primary">{{ \Carbon\Carbon::parse($b->start_time)->format('h:i') }}</div>
                            <div style="font-size:.65rem;color:#94a3b8">{{ \Carbon\Carbon::parse($b->start_time)->format('A') }}</div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-semibold small">{{ $b->customer_name }}</p>
                            <p class="mb-0 text-muted" style="font-size:.78rem">
                                {{ $b->hall->name }} · {{ ucfirst(str_replace('_', ' ', $b->event_type)) }} · {{ $b->number_of_people }} pax
                            </p>
                        </div>
                        <span class="badge bg-{{ \App\Models\HallBooking::paymentStatusColors()[$b->payment_status] ?? 'secondary' }} rounded-pill">
                            {{ ucfirst($b->payment_status) }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x d-block fs-2 mb-2 opacity-50"></i>
                        No bookings today
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent bookings --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3">
                <span class="fw-semibold">Recent Bookings</span>
                <a href="{{ route('hall.bookings.index') }}" class="btn btn-sm btn-outline-primary rounded-3">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Hall</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $b)
                            <tr style="cursor:pointer" onclick="location.href='{{ route('hall.bookings.show', $b) }}'">
                                <td class="fw-semibold">{{ $b->customer_name }}</td>
                                <td class="text-muted small">{{ $b->hall->name }}</td>
                                <td class="small">{{ $b->booking_date->format('d M Y') }}</td>
                                <td>₹{{ number_format($b->total_amount) }}</td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\HallBooking::statusColors()[$b->status] ?? 'secondary' }} rounded-pill">
                                        {{ ucfirst($b->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No bookings yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Hall utilisation --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0 py-3">
                <span class="fw-semibold">Hall Status</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($halls as $hall)
                        <div class="col-6">
                            <div class="border rounded-3 p-3">
                                <p class="mb-1 fw-semibold small">{{ $hall->name }}</p>
                                <p class="mb-0 text-muted" style="font-size:.78rem">
                                    Capacity: {{ $hall->capacity }} · Upcoming: {{ $hall->upcoming_count }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

</x-admin-layout>
