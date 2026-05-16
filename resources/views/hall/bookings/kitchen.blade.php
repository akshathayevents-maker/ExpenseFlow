<x-admin-layout title="Kitchen Summary">

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-cup-hot me-2 text-primary"></i>Kitchen Summary</h5>
        <p class="text-muted mb-0" style="font-size:.8rem">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm rounded-3">
        <button type="submit" class="btn btn-sm btn-primary rounded-3">Go</button>
    </form>
</div>

@if($bookings->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-cup-hot d-block fs-2 mb-2 opacity-40"></i>
            No bookings on {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
        </div>
    </div>
@else

{{-- Meal summary tallies --}}
<div class="row g-3 mb-4">
    @php
        $bfCount = $bookings->where('has_breakfast', true)->sum('number_of_people');
        $lnCount = $bookings->where('has_lunch', true)->sum('number_of_people');
        $dnCount = $bookings->where('has_dinner', true)->sum('number_of_people');
    @endphp
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <i class="bi bi-cup fs-2 text-warning mb-1"></i>
            <h4 class="mb-0 fw-bold">{{ $bfCount }}</h4>
            <p class="text-muted small mb-0">Breakfast covers</p>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <i class="bi bi-sun fs-2 text-info mb-1"></i>
            <h4 class="mb-0 fw-bold">{{ $lnCount }}</h4>
            <p class="text-muted small mb-0">Lunch covers</p>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <i class="bi bi-moon-stars fs-2 text-primary mb-1"></i>
            <h4 class="mb-0 fw-bold">{{ $dnCount }}</h4>
            <p class="text-muted small mb-0">Dinner covers</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 fw-semibold small py-3">Booking Details</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Hall</th>
                    <th>Customer</th>
                    <th>Event</th>
                    <th>Pax</th>
                    <th>Meal Plan</th>
                    <th class="text-center">BF</th>
                    <th class="text-center">LN</th>
                    <th class="text-center">DN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $b)
                    <tr onclick="location.href='{{ route('hall.bookings.show', $b) }}'" style="cursor:pointer">
                        <td class="small fw-semibold">{{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}</td>
                        <td class="small">{{ $b->hall->name }}</td>
                        <td>
                            <p class="mb-0 fw-semibold small">{{ $b->customer_name }}</p>
                            <p class="mb-0 text-muted" style="font-size:.72rem">{{ $b->customer_mobile }}</p>
                        </td>
                        <td class="small">{{ ucfirst(str_replace('_', ' ', $b->event_type)) }}</td>
                        <td class="small fw-semibold">{{ $b->number_of_people }}</td>
                        <td class="small text-muted">{{ $b->mealPlan?->name ?? '—' }}</td>
                        <td class="text-center">
                            @if($b->has_breakfast)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-dash text-muted"></i>@endif
                        </td>
                        <td class="text-center">
                            @if($b->has_lunch)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-dash text-muted"></i>@endif
                        </td>
                        <td class="text-center">
                            @if($b->has_dinner)<i class="bi bi-check-circle-fill text-success"></i>@else<i class="bi bi-dash text-muted"></i>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif

</x-admin-layout>
