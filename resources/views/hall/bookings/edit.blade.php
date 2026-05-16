<x-admin-layout title="Edit Booking #{{ $booking->id }}">

<style>
.booking-card { border-radius: 16px; border: none; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
.section-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 1px solid #f1f5f9; }
</style>

<div class="page-header d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('hall.bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-bold">Edit Booking #{{ $booking->id }}</h5>
        <p class="text-muted mb-0" style="font-size:.8rem">{{ $booking->customer_name }}</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-3 mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Please fix:</strong>
        <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('hall.bookings.update', $booking) }}" id="bookingForm" novalidate>
@csrf @method('PUT')

<div class="row g-3" style="max-width:900px">
    <div class="col-12">
        <div class="booking-card card">
            <div class="card-body p-4">
                <p class="section-label">Customer Information</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3 @error('customer_name') is-invalid @enderror"
                               value="{{ old('customer_name', $booking->customer_name) }}" required>
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Mobile <span class="text-danger">*</span></label>
                        <input type="tel" name="customer_mobile" class="form-control rounded-3 @error('customer_mobile') is-invalid @enderror"
                               value="{{ old('customer_mobile', $booking->customer_mobile) }}" required>
                        @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Alt. Mobile</label>
                        <input type="tel" name="customer_alt_mobile" class="form-control rounded-3"
                               value="{{ old('customer_alt_mobile', $booking->customer_alt_mobile) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="booking-card card">
            <div class="card-body p-4">
                <p class="section-label">Event Details</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Event Type <span class="text-danger">*</span></label>
                        <select name="event_type" class="form-select rounded-3 @error('event_type') is-invalid @enderror" required>
                            @foreach(\App\Models\HallBooking::eventTypes() as $v => $l)
                                <option value="{{ $v }}" {{ old('event_type', $booking->event_type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                        @error('event_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Hall <span class="text-danger">*</span></label>
                        <select id="hall_id" name="hall_id" class="form-select rounded-3" required>
                            @foreach($halls as $h)
                                <option value="{{ $h->id }}" {{ old('hall_id', $booking->hall_id) == $h->id ? 'selected' : '' }}>{{ $h->name }} ({{ $h->capacity }} pax)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Number of People <span class="text-danger">*</span></label>
                        <input type="number" name="number_of_people" min="1" class="form-control rounded-3"
                               value="{{ old('number_of_people', $booking->number_of_people) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Booking Date <span class="text-danger">*</span></label>
                        <input type="date" id="booking_date" name="booking_date" class="form-control rounded-3 @error('booking_date') is-invalid @enderror"
                               value="{{ old('booking_date', $booking->booking_date->toDateString()) }}" required>
                        @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Start Time <span class="text-danger">*</span></label>
                        <input type="time" id="start_time" name="start_time" class="form-control rounded-3"
                               value="{{ old('start_time', $booking->start_time) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">End Time <span class="text-danger">*</span></label>
                        <input type="time" id="end_time" name="end_time" class="form-control rounded-3"
                               value="{{ old('end_time', $booking->end_time) }}" required>
                    </div>
                    <div class="col-12"><div id="availStatus"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="booking-card card">
            <div class="card-body p-4">
                <p class="section-label">Meal Plan</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Meal Plan</label>
                        <select name="meal_plan_id" class="form-select rounded-3">
                            <option value="">No meal plan</option>
                            @foreach($mealPlans as $mp)
                                <option value="{{ $mp->id }}" {{ old('meal_plan_id', $booking->meal_plan_id) == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->name }} — ₹{{ number_format($mp->price_per_person) }}/person
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-4 pb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_breakfast" value="1" {{ old('has_breakfast', $booking->has_breakfast) ? 'checked' : '' }}>
                            <label class="form-check-label small"><i class="bi bi-cup me-1"></i>Breakfast</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_lunch" value="1" {{ old('has_lunch', $booking->has_lunch) ? 'checked' : '' }}>
                            <label class="form-check-label small"><i class="bi bi-sun me-1"></i>Lunch</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_dinner" value="1" {{ old('has_dinner', $booking->has_dinner) ? 'checked' : '' }}>
                            <label class="form-check-label small"><i class="bi bi-moon-stars me-1"></i>Dinner</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="booking-card card">
            <div class="card-body p-4">
                <p class="section-label">Payment & Status</p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Total Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="total_amount" name="total_amount" step="0.01" min="0" class="form-control rounded-3"
                               value="{{ old('total_amount', $booking->total_amount) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Advance Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="advance_amount" name="advance_amount" step="0.01" min="0" class="form-control rounded-3"
                               value="{{ old('advance_amount', $booking->advance_amount) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Payment Status</label>
                        <select name="payment_status" class="form-select rounded-3">
                            @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                                <option value="{{ $v }}" {{ old('payment_status', $booking->payment_status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Booking Status</label>
                        <select name="status" class="form-select rounded-3">
                            @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                                <option value="{{ $v }}" {{ old('status', $booking->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Notes</label>
                        <textarea name="notes" rows="2" class="form-control rounded-3">{{ old('notes', $booking->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex gap-2 pb-4">
        <button type="submit" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-check-circle me-1"></i>Update Booking
        </button>
        <a href="{{ route('hall.bookings.show', $booking) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
    </div>
</div>
</form>

@push('scripts')
<script>
(function () {
    const hallSel   = document.getElementById('hall_id');
    const dateFld   = document.getElementById('booking_date');
    const startFld  = document.getElementById('start_time');
    const endFld    = document.getElementById('end_time');
    const statusDiv = document.getElementById('availStatus');
    let checkTimer  = null;

    function checkAvailability() {
        if (!hallSel.value || !dateFld.value || !startFld.value || !endFld.value) return;
        clearTimeout(checkTimer);
        checkTimer = setTimeout(async () => {
            statusDiv.innerHTML = '<span class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span>Checking…</span>';
            try {
                const params = new URLSearchParams({
                    hall_id: hallSel.value, booking_date: dateFld.value,
                    start_time: startFld.value, end_time: endFld.value,
                    exclude_id: {{ $booking->id }},
                });
                const res  = await fetch('{{ route("hall.bookings.check-availability") }}?' + params);
                const data = await res.json();
                statusDiv.innerHTML = data.available
                    ? '<span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Hall is available</span>'
                    : `<span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>Conflict detected</span>`;
            } catch { statusDiv.innerHTML = ''; }
        }, 500);
    }

    [hallSel, dateFld, startFld, endFld].forEach(el => el.addEventListener('change', checkAvailability));
})();
</script>
@endpush

</x-admin-layout>
