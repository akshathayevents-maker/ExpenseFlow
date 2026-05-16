<x-admin-layout title="Create Hall Booking">

<style>
/* ── Design tokens ────────────────────────────────────────────────── */
:root {
    --hb-radius:  16px;
    --hb-shadow:  0 1px 12px rgba(0,0,0,.07);
    --hb-border:  #e8edf3;
    --hb-bg:      #f6f8fb;
    --hb-accent:  #3b82f6;
    --hb-green:   #16a34a;
    --hb-orange:  #d97706;
    --hb-red:     #dc2626;
    --hb-text:    #0f172a;
    --hb-muted:   #64748b;
}

/* ── Section cards ───────────────────────────────────────────────── */
.hb-card {
    background: #fff;
    border-radius: var(--hb-radius);
    border: 1px solid var(--hb-border);
    box-shadow: var(--hb-shadow);
    transition: box-shadow .2s;
}
.hb-card:focus-within { box-shadow: 0 4px 24px rgba(59,130,246,.10); }

.hb-card-header {
    display: flex; align-items: center; gap: .9rem;
    padding: 1.25rem 1.5rem .75rem;
    border-bottom: 1px solid var(--hb-border);
}
.hb-card-header .sec-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.hb-card-header .sec-title { font-weight: 700; color: var(--hb-text); font-size: .95rem; margin: 0; }
.hb-card-header .sec-sub   { font-size: .75rem; color: var(--hb-muted); margin: 0; }
.hb-card-body { padding: 1.25rem 1.5rem 1.5rem; }

/* ── Form fields ─────────────────────────────────────────────────── */
.hb-label {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--hb-muted);
    margin-bottom: .4rem; display: block;
}
.hb-input {
    width: 100%; border: 1.5px solid var(--hb-border);
    border-radius: 10px; padding: .65rem .9rem;
    font-size: .9rem; color: var(--hb-text);
    background: #fff; transition: border-color .15s, box-shadow .15s;
    outline: none; appearance: none;
}
.hb-input:focus {
    border-color: var(--hb-accent);
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.hb-input.is-invalid { border-color: var(--hb-red); }
.hb-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.12); }
.invalid-feedback { font-size: .75rem; color: var(--hb-red); margin-top: .25rem; }

.hb-input-wrap { position: relative; }
.hb-input-wrap .hb-input-icon {
    position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
    color: #94a3b8; font-size: .9rem; pointer-events: none;
}
.hb-input-wrap .hb-input { padding-left: 2.25rem; }

/* ── Meal chips ──────────────────────────────────────────────────── */
.meal-chips { display: flex; gap: .75rem; flex-wrap: wrap; }
.meal-chip   { cursor: pointer; user-select: none; }
.meal-chip input { position: absolute; opacity: 0; width: 0; height: 0; }
.meal-chip-card {
    display: flex; flex-direction: column; align-items: center;
    gap: .4rem; padding: .9rem 1.4rem;
    border: 2px solid var(--hb-border); border-radius: 14px;
    background: var(--hb-bg); transition: all .18s;
    min-width: 90px; text-align: center;
}
.meal-chip-card .meal-chip-icon { font-size: 1.5rem; line-height: 1; }
.meal-chip-card .meal-chip-label { font-size: .78rem; font-weight: 600; color: var(--hb-muted); }

.meal-chip input:checked + .meal-chip-card {
    border-color: var(--hb-accent); background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.meal-chip input:checked + .meal-chip-card .meal-chip-label { color: #1d4ed8; }
.meal-chip:hover .meal-chip-card { border-color: #94a3b8; }

/* ── Status selector ─────────────────────────────────────────────── */
.status-chips { display: flex; gap: .6rem; flex-wrap: wrap; }
.status-chip  { cursor: pointer; user-select: none; }
.status-chip input { position: absolute; opacity: 0; width: 0; height: 0; }
.status-chip-badge {
    padding: .45rem 1rem; border-radius: 50px;
    border: 2px solid var(--hb-border); font-size: .8rem; font-weight: 600;
    background: var(--hb-bg); color: var(--hb-muted);
    transition: all .15s; white-space: nowrap;
    display: flex; align-items: center; gap: .35rem;
}
.status-chip input:checked + .status-chip-badge {
    box-shadow: 0 0 0 3px rgba(0,0,0,.06);
}
.status-chip[data-status="confirmed"]  input:checked + .status-chip-badge { border-color: var(--hb-green); background: #f0fdf4; color: var(--hb-green); }
.status-chip[data-status="completed"]  input:checked + .status-chip-badge { border-color: var(--hb-accent); background: #eff6ff; color: #1d4ed8; }
.status-chip[data-status="cancelled"]  input:checked + .status-chip-badge { border-color: var(--hb-red); background: #fef2f2; color: var(--hb-red); }
.status-chip:hover .status-chip-badge  { border-color: #94a3b8; }

/* ── Payment method chips ────────────────────────────────────────── */
.method-chips  { display: flex; gap: .5rem; flex-wrap: wrap; }
.method-chip   { cursor: pointer; }
.method-chip input { position: absolute; opacity: 0; width: 0; height: 0; }
.method-chip-btn {
    padding: .4rem .9rem; border-radius: 8px;
    border: 1.5px solid var(--hb-border); font-size: .8rem; font-weight: 600;
    background: var(--hb-bg); color: var(--hb-muted); transition: all .15s;
}
.method-chip input:checked + .method-chip-btn {
    border-color: var(--hb-accent); background: #eff6ff; color: #1d4ed8;
}

/* ── Financial display ───────────────────────────────────────────── */
.fin-row {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: .5rem 0; border-bottom: 1px solid var(--hb-border);
}
.fin-row:last-child { border-bottom: none; }
.fin-label  { font-size: .8rem; color: var(--hb-muted); font-weight: 500; }
.fin-value  { font-weight: 700; font-size: 1rem; color: var(--hb-text); }
.fin-value.green  { color: var(--hb-green); }
.fin-value.orange { color: var(--hb-orange); }
.fin-value.red    { color: var(--hb-red); }
.fin-row.total .fin-label { font-weight: 700; color: var(--hb-text); font-size: .85rem; }
.fin-row.total .fin-value { font-size: 1.25rem; }

/* ── Summary sidebar ─────────────────────────────────────────────── */
.booking-summary-wrap {
    position: sticky;
    top: calc(56px + 1.5rem); /* topbar height + padding */
}
.summary-pill {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 700; padding: .3rem .75rem;
    border-radius: 50px;
}
.summary-meta-row {
    display: flex; align-items: flex-start; gap: .6rem;
    padding: .45rem 0; border-bottom: 1px solid var(--hb-border);
    font-size: .82rem;
}
.summary-meta-row:last-child { border-bottom: none; }
.summary-meta-row .smr-icon { color: #94a3b8; width: 16px; flex-shrink: 0; margin-top: .1rem; }
.summary-meta-row .smr-label { color: var(--hb-muted); font-weight: 500; min-width: 70px; }
.summary-meta-row .smr-val   { color: var(--hb-text); font-weight: 600; }

/* ── Availability card ───────────────────────────────────────────── */
.avail-card {
    border-radius: 12px; padding: .85rem 1rem;
    font-size: .82rem; font-weight: 600;
    display: flex; align-items: center; gap: .6rem;
    border: 1.5px solid var(--hb-border);
    background: var(--hb-bg);
    transition: all .25s;
    min-height: 52px;
}
.avail-card.avail-loading { color: var(--hb-muted); }
.avail-card.avail-ok      { border-color: #86efac; background: #f0fdf4; color: var(--hb-green); }
.avail-card.avail-conflict { border-color: #fca5a5; background: #fef2f2; color: var(--hb-red); }
.avail-card.avail-idle    { color: #94a3b8; }
.avail-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.avail-ok .avail-dot      { background: var(--hb-green); }
.avail-conflict .avail-dot { background: var(--hb-red); }
.avail-loading .avail-dot  { background: #94a3b8; animation: pulse 1s infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }

/* ── Primary CTA ─────────────────────────────────────────────────── */
.btn-create {
    background: var(--hb-accent); color: #fff; border: none;
    border-radius: 12px; font-weight: 700; font-size: .95rem;
    padding: .8rem 1.5rem; width: 100%;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .15s, transform .1s, box-shadow .15s;
    cursor: pointer;
}
.btn-create:hover:not(:disabled) {
    background: #2563eb; box-shadow: 0 4px 16px rgba(59,130,246,.35);
    transform: translateY(-1px);
}
.btn-create:active:not(:disabled) { transform: translateY(0); }
.btn-create:disabled { background: #93c5fd; cursor: not-allowed; }

/* ── Mobile sticky footer ────────────────────────────────────────── */
@media (max-width: 991.98px) {
    .booking-summary-wrap { position: static; }
    .mobile-cta-bar {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
        background: #fff; padding: .875rem 1rem calc(.875rem + env(safe-area-inset-bottom));
        border-top: 1px solid var(--hb-border);
        box-shadow: 0 -4px 20px rgba(0,0,0,.08);
    }
    .mobile-cta-spacer { height: 76px; }
}
@media (min-width: 992px) {
    .mobile-cta-bar { display: none !important; }
}

/* ── Breadcrumb polish ───────────────────────────────────────────── */
.hb-breadcrumb { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: var(--hb-muted); margin-bottom: .25rem; }
.hb-breadcrumb a { color: var(--hb-muted); text-decoration: none; }
.hb-breadcrumb a:hover { color: var(--hb-accent); }
.hb-breadcrumb .sep { opacity: .4; }
</style>

{{-- ── PAGE HEADER ─────────────────────────────────────────────────── --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <div class="hb-breadcrumb">
            <a href="{{ route('hall.dashboard') }}">Hall Management</a>
            <span class="sep">/</span>
            <a href="{{ route('hall.bookings.index') }}">Hall Bookings</a>
            <span class="sep">/</span>
            <span style="color:var(--hb-text);font-weight:600">New Booking</span>
        </div>
        <h4 class="fw-bold mb-0" style="color:var(--hb-text)">Create Hall Booking</h4>
        <p class="text-muted mb-0 small">Fill in the details to create a new event booking</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('hall.bookings.calendar') }}" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="bi bi-calendar3 me-1"></i>Calendar
        </a>
        <a href="{{ route('hall.bookings.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

{{-- Validation errors --}}
@if ($errors->any())
    <div class="alert alert-danger rounded-3 mb-4 d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li class="small">{{ $e }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('hall.bookings.store') }}" id="bookingForm" novalidate>
@csrf

<div class="row g-4 align-items-start">

{{-- ═══════════════════════════════════════
     LEFT COLUMN — Form sections
══════════════════════════════════════════ --}}
<div class="col-lg-8">
<div class="d-flex flex-column gap-3">

{{-- ── 1. Customer Information ──────────────────────────────── --}}
<div class="hb-card">
    <div class="hb-card-header">
        <div class="sec-icon bg-primary bg-opacity-10">
            <i class="bi bi-person-fill text-primary"></i>
        </div>
        <div>
            <p class="sec-title">Customer Information</p>
            <p class="sec-sub">Primary contact details for this booking</p>
        </div>
    </div>
    <div class="hb-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="hb-label" for="customer_name">Full Name <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-person hb-input-icon"></i>
                    <input type="text" id="customer_name" name="customer_name"
                           class="hb-input @error('customer_name') is-invalid @enderror"
                           value="{{ old('customer_name') }}"
                           placeholder="Customer's full name" required autofocus>
                </div>
                @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="hb-label" for="customer_mobile">Mobile <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-telephone hb-input-icon"></i>
                    <input type="tel" id="customer_mobile" name="customer_mobile"
                           class="hb-input @error('customer_mobile') is-invalid @enderror"
                           value="{{ old('customer_mobile') }}"
                           placeholder="Primary number" required>
                </div>
                @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="hb-label" for="customer_alt_mobile">Alt. Mobile</label>
                <div class="hb-input-wrap">
                    <i class="bi bi-telephone-plus hb-input-icon"></i>
                    <input type="tel" id="customer_alt_mobile" name="customer_alt_mobile"
                           class="hb-input"
                           value="{{ old('customer_alt_mobile') }}"
                           placeholder="Optional">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 2. Event Details ─────────────────────────────────────── --}}
<div class="hb-card">
    <div class="hb-card-header">
        <div class="sec-icon bg-warning bg-opacity-10">
            <i class="bi bi-stars text-warning"></i>
        </div>
        <div>
            <p class="sec-title">Event Details</p>
            <p class="sec-sub">Hall, date, time and guest information</p>
        </div>
    </div>
    <div class="hb-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="hb-label" for="event_type">Event Type <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-calendar-event hb-input-icon"></i>
                    <select id="event_type" name="event_type"
                            class="hb-input @error('event_type') is-invalid @enderror" required>
                        <option value="">Select event type…</option>
                        @foreach(\App\Models\HallBooking::eventTypes() as $v => $l)
                            <option value="{{ $v }}" {{ old('event_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                @error('event_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="hb-label" for="hall_id">Hall <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-building hb-input-icon"></i>
                    <select id="hall_id" name="hall_id"
                            class="hb-input @error('hall_id') is-invalid @enderror" required>
                        <option value="">Select hall…</option>
                        @foreach($halls as $h)
                            <option value="{{ $h->id }}"
                                    {{ old('hall_id') == $h->id ? 'selected' : '' }}
                                    data-capacity="{{ $h->capacity }}"
                                    data-name="{{ $h->name }}">
                                {{ $h->name }} ({{ $h->capacity }} pax)
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('hall_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="hb-label" for="number_of_people">Guests <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-people hb-input-icon"></i>
                    <input type="number" id="number_of_people" name="number_of_people" min="1"
                           class="hb-input @error('number_of_people') is-invalid @enderror"
                           value="{{ old('number_of_people') }}"
                           placeholder="0" required>
                </div>
                @error('number_of_people')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="hb-label" for="booking_date">Date <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-calendar3 hb-input-icon"></i>
                    <input type="date" id="booking_date" name="booking_date"
                           class="hb-input @error('booking_date') is-invalid @enderror"
                           value="{{ old('booking_date', request('date')) }}"
                           min="{{ today()->toDateString() }}" required>
                </div>
                @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="hb-label" for="start_time">Start Time <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-clock hb-input-icon"></i>
                    <input type="time" id="start_time" name="start_time"
                           class="hb-input @error('start_time') is-invalid @enderror"
                           value="{{ old('start_time') }}" required>
                </div>
                @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="hb-label" for="end_time">End Time <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-clock-history hb-input-icon"></i>
                    <input type="time" id="end_time" name="end_time"
                           class="hb-input @error('end_time') is-invalid @enderror"
                           value="{{ old('end_time') }}" required>
                </div>
                @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Availability indicator --}}
            <div class="col-12">
                <div class="avail-card avail-idle" id="availCard">
                    <div class="avail-dot"></div>
                    <span id="availText">Select hall, date and time to check availability</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 3. Meal Plan ─────────────────────────────────────────── --}}
<div class="hb-card">
    <div class="hb-card-header">
        <div class="sec-icon bg-success bg-opacity-10">
            <i class="bi bi-egg-fried text-success"></i>
        </div>
        <div>
            <p class="sec-title">Meal Plan</p>
            <p class="sec-sub">Select catering options for this event</p>
        </div>
    </div>
    <div class="hb-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="hb-label" for="meal_plan_id">Catering Package</label>
                <div class="hb-input-wrap">
                    <i class="bi bi-journal-richtext hb-input-icon"></i>
                    <select id="meal_plan_id" name="meal_plan_id" class="hb-input">
                        <option value="">No meal plan</option>
                        @foreach($mealPlans as $mp)
                            <option value="{{ $mp->id }}"
                                    {{ old('meal_plan_id') == $mp->id ? 'selected' : '' }}
                                    data-price="{{ $mp->price_per_person }}">
                                {{ $mp->name }} — ₹{{ number_format($mp->price_per_person) }}/person
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12">
                <label class="hb-label mb-2">Meals Included</label>
                <div class="meal-chips">
                    <label class="meal-chip" for="has_breakfast">
                        <input type="checkbox" id="has_breakfast" name="has_breakfast" value="1"
                               {{ old('has_breakfast') ? 'checked' : '' }}>
                        <div class="meal-chip-card">
                            <span class="meal-chip-icon">☀️</span>
                            <span class="meal-chip-label">Breakfast</span>
                        </div>
                    </label>
                    <label class="meal-chip" for="has_lunch">
                        <input type="checkbox" id="has_lunch" name="has_lunch" value="1"
                               {{ old('has_lunch') ? 'checked' : '' }}>
                        <div class="meal-chip-card">
                            <span class="meal-chip-icon">🍛</span>
                            <span class="meal-chip-label">Lunch</span>
                        </div>
                    </label>
                    <label class="meal-chip" for="has_dinner">
                        <input type="checkbox" id="has_dinner" name="has_dinner" value="1"
                               {{ old('has_dinner') ? 'checked' : '' }}>
                        <div class="meal-chip-card">
                            <span class="meal-chip-icon">🌙</span>
                            <span class="meal-chip-label">Dinner</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 4. Payment Details ───────────────────────────────────── --}}
<div class="hb-card">
    <div class="hb-card-header">
        <div class="sec-icon bg-info bg-opacity-10">
            <i class="bi bi-currency-rupee text-info"></i>
        </div>
        <div>
            <p class="sec-title">Payment Details</p>
            <p class="sec-sub">Amount, advance and payment method</p>
        </div>
    </div>
    <div class="hb-card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="hb-label" for="total_amount">Total Amount (₹) <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-currency-rupee hb-input-icon"></i>
                    <input type="number" id="total_amount" name="total_amount"
                           step="0.01" min="0"
                           class="hb-input @error('total_amount') is-invalid @enderror"
                           value="{{ old('total_amount', 0) }}"
                           placeholder="0.00" required>
                </div>
                @error('total_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="hb-label" for="advance_amount">Advance Amount (₹) <span class="text-danger">*</span></label>
                <div class="hb-input-wrap">
                    <i class="bi bi-currency-rupee hb-input-icon"></i>
                    <input type="number" id="advance_amount" name="advance_amount"
                           step="0.01" min="0"
                           class="hb-input @error('advance_amount') is-invalid @enderror"
                           value="{{ old('advance_amount', 0) }}"
                           placeholder="0.00" required>
                </div>
                @error('advance_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="hb-label" for="payment_status">Payment Status</label>
                <div class="hb-input-wrap">
                    <i class="bi bi-check2-circle hb-input-icon"></i>
                    <select id="payment_status" name="payment_status" class="hb-input">
                        @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                            <option value="{{ $v }}" {{ old('payment_status', 'pending') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Payment method chips --}}
            <div class="col-12">
                <label class="hb-label mb-2">Payment Method</label>
                <div class="method-chips">
                    @foreach(\App\Models\BookingPayment::methods() as $v => $l)
                        <label class="method-chip" for="pm_{{ $v }}">
                            <input type="radio" id="pm_{{ $v }}" name="payment_method"
                                   value="{{ $v }}" {{ $loop->first ? 'checked' : '' }}>
                            <span class="method-chip-btn">{{ $l }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Live balance summary --}}
            <div class="col-12">
                <div style="background:var(--hb-bg);border-radius:12px;padding:1rem 1.25rem">
                    <div class="fin-row total">
                        <span class="fin-label">Total Amount</span>
                        <span class="fin-value" id="dispTotal">₹0.00</span>
                    </div>
                    <div class="fin-row">
                        <span class="fin-label">Advance Collected</span>
                        <span class="fin-value green" id="dispAdv">₹0.00</span>
                    </div>
                    <div class="fin-row">
                        <span class="fin-label">Balance Due</span>
                        <span class="fin-value red" id="dispBal">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 5. Booking Status & Notes ────────────────────────────── --}}
<div class="hb-card">
    <div class="hb-card-header">
        <div class="sec-icon" style="background:#f3f4f6">
            <i class="bi bi-sticky-fill text-secondary"></i>
        </div>
        <div>
            <p class="sec-title">Status & Notes</p>
            <p class="sec-sub">Booking confirmation state and additional remarks</p>
        </div>
    </div>
    <div class="hb-card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="hb-label mb-2">Booking Status</label>
                <div class="status-chips">
                    @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                        @php
                            $icon = match($v) { 'confirmed' => 'bi-check-circle-fill', 'completed' => 'bi-trophy-fill', 'cancelled' => 'bi-x-circle-fill', default => 'bi-circle' };
                        @endphp
                        <label class="status-chip" data-status="{{ $v }}" for="status_{{ $v }}">
                            <input type="radio" id="status_{{ $v }}" name="status"
                                   value="{{ $v }}" {{ old('status', 'confirmed') === $v ? 'checked' : '' }}>
                            <span class="status-chip-badge">
                                <i class="bi {{ $icon }}"></i> {{ $l }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="col-12">
                <label class="hb-label" for="notes">Notes & Special Requirements</label>
                <textarea id="notes" name="notes" rows="3"
                          class="hb-input @error('notes') is-invalid @enderror"
                          style="resize:vertical;min-height:80px"
                          placeholder="Any special requirements, seating preferences, decorations…">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Desktop CTA (inside left column) --}}
<div class="d-none d-lg-flex gap-2 pb-2">
    <button type="submit" class="btn-create" id="btnSubmit">
        <i class="bi bi-check-circle-fill"></i>
        <span id="btnText">Create Booking</span>
        <span id="btnSpinner" class="d-none spinner-border spinner-border-sm"></span>
    </button>
    <a href="{{ route('hall.bookings.index') }}"
       class="btn btn-outline-secondary rounded-3 px-4 fw-semibold">
        Cancel
    </a>
</div>

{{-- Mobile spacer --}}
<div class="mobile-cta-spacer d-lg-none"></div>

</div>{{-- /flex-column --}}
</div>{{-- /col-lg-8 --}}

{{-- ═══════════════════════════════════════
     RIGHT COLUMN — Sticky Summary
══════════════════════════════════════════ --}}
<div class="col-lg-4">
<div class="booking-summary-wrap">
<div class="d-flex flex-column gap-3">

    {{-- Availability status --}}
    <div class="hb-card" id="availCardSidebar">
        <div class="hb-card-body" style="padding:1rem 1.25rem">
            <p class="hb-label mb-2">Availability</p>
            <div class="avail-card avail-idle" id="availCardSide">
                <div class="avail-dot"></div>
                <span id="availTextSide" style="font-size:.8rem">Select hall & date to check</span>
            </div>
        </div>
    </div>

    {{-- Booking summary --}}
    <div class="hb-card">
        <div class="hb-card-body" style="padding:1rem 1.25rem">
            <p class="hb-label mb-3">Booking Summary</p>

            <div class="summary-meta-row">
                <i class="bi bi-building smr-icon"></i>
                <span class="smr-label">Hall</span>
                <span class="smr-val" id="sumHall">—</span>
            </div>
            <div class="summary-meta-row">
                <i class="bi bi-stars smr-icon"></i>
                <span class="smr-label">Event</span>
                <span class="smr-val" id="sumEvent">—</span>
            </div>
            <div class="summary-meta-row">
                <i class="bi bi-calendar3 smr-icon"></i>
                <span class="smr-label">Date</span>
                <span class="smr-val" id="sumDate">—</span>
            </div>
            <div class="summary-meta-row">
                <i class="bi bi-clock smr-icon"></i>
                <span class="smr-label">Time</span>
                <span class="smr-val" id="sumTime">—</span>
            </div>
            <div class="summary-meta-row">
                <i class="bi bi-people smr-icon"></i>
                <span class="smr-label">Guests</span>
                <span class="smr-val" id="sumGuests">—</span>
            </div>
            <div class="summary-meta-row">
                <i class="bi bi-egg-fried smr-icon"></i>
                <span class="smr-label">Meals</span>
                <span class="smr-val" id="sumMeals">None</span>
            </div>
        </div>
    </div>

    {{-- Financial summary --}}
    <div class="hb-card">
        <div class="hb-card-body" style="padding:1rem 1.25rem">
            <p class="hb-label mb-2">Payment Summary</p>
            <div class="fin-row total">
                <span class="fin-label">Total</span>
                <span class="fin-value" id="sumTotal">₹0.00</span>
            </div>
            <div class="fin-row">
                <span class="fin-label">Advance</span>
                <span class="fin-value green" id="sumAdv">₹0.00</span>
            </div>
            <div class="fin-row">
                <span class="fin-label">Balance</span>
                <span class="fin-value red" id="sumBal">₹0.00</span>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap" id="sumBadges">
                <span class="summary-pill bg-warning bg-opacity-10 text-warning" id="sumPayBadge">
                    <i class="bi bi-clock-fill" style="font-size:.6rem"></i> Pending
                </span>
                <span class="summary-pill bg-success bg-opacity-10 text-success" id="sumStatusBadge">
                    <i class="bi bi-check-circle-fill" style="font-size:.6rem"></i> Confirmed
                </span>
            </div>
        </div>
    </div>

    {{-- Desktop CTA (inside right panel) --}}
    <div class="d-none d-lg-block">
        <button type="submit" form="bookingForm" class="btn-create" id="btnSubmitSide">
            <i class="bi bi-check-circle-fill"></i>
            <span>Create Booking</span>
        </button>
        <a href="{{ route('hall.bookings.index') }}"
           class="btn btn-outline-secondary rounded-3 w-100 mt-2 fw-semibold">
            Cancel
        </a>
    </div>

</div>
</div>
</div>{{-- /col-lg-4 --}}

</div>{{-- /row --}}
</form>

{{-- Mobile sticky CTA --}}
<div class="mobile-cta-bar d-lg-none">
    <button type="submit" form="bookingForm" class="btn-create">
        <i class="bi bi-check-circle-fill"></i> Create Booking
    </button>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Element refs ──────────────────────────────────────────────────────────
    const hallSel   = document.getElementById('hall_id');
    const dateFld   = document.getElementById('booking_date');
    const startFld  = document.getElementById('start_time');
    const endFld    = document.getElementById('end_time');
    const peopleFld = document.getElementById('number_of_people');
    const eventSel  = document.getElementById('event_type');
    const totalFld  = document.getElementById('total_amount');
    const advFld    = document.getElementById('advance_amount');
    const bfChk     = document.getElementById('has_breakfast');
    const lnChk     = document.getElementById('has_lunch');
    const dnChk     = document.getElementById('has_dinner');

    // Availability elements (main + sidebar mirror)
    const availCards = [
        document.getElementById('availCard'),
        document.getElementById('availCardSide'),
    ];
    const availTexts = [
        document.getElementById('availText'),
        document.getElementById('availTextSide'),
    ];

    // Financial display
    const dispTotal = document.getElementById('dispTotal');
    const dispAdv   = document.getElementById('dispAdv');
    const dispBal   = document.getElementById('dispBal');

    // Summary sidebar
    const sumHall   = document.getElementById('sumHall');
    const sumEvent  = document.getElementById('sumEvent');
    const sumDate   = document.getElementById('sumDate');
    const sumTime   = document.getElementById('sumTime');
    const sumGuests = document.getElementById('sumGuests');
    const sumMeals  = document.getElementById('sumMeals');
    const sumTotal  = document.getElementById('sumTotal');
    const sumAdv    = document.getElementById('sumAdv');
    const sumBal    = document.getElementById('sumBal');
    const sumPayBadge    = document.getElementById('sumPayBadge');
    const sumStatusBadge = document.getElementById('sumStatusBadge');

    // ── Utilities ─────────────────────────────────────────────────────────────
    function fmt(n) {
        return '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }
    function fmtDate(s) {
        if (!s) return '—';
        const d = new Date(s);
        return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function fmtTime(t) {
        if (!t) return '';
        const [h, m] = t.split(':');
        const d = new Date(2000, 0, 1, h, m);
        return d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
    }
    function setAvailState(state, text) {
        availCards.forEach(el => {
            el.className = 'avail-card avail-' + state;
        });
        availTexts.forEach(el => { el.textContent = text; });
    }

    // ── Live financial update ─────────────────────────────────────────────────
    function updateFinancials() {
        const total = parseFloat(totalFld.value) || 0;
        const adv   = parseFloat(advFld.value)   || 0;
        const bal   = Math.max(0, total - adv);

        dispTotal.textContent = fmt(total);
        dispAdv.textContent   = fmt(adv);
        dispBal.textContent   = fmt(bal);
        dispBal.className     = 'fin-value ' + (bal > 0 ? 'red' : 'green');

        sumTotal.textContent = fmt(total);
        sumAdv.textContent   = fmt(adv);
        sumBal.textContent   = fmt(bal);
        sumBal.className     = 'fin-value ' + (bal > 0 ? 'red' : 'green');
    }

    // ── Live summary update ───────────────────────────────────────────────────
    function updateSummary() {
        // Hall
        const hallOpt = hallSel.options[hallSel.selectedIndex];
        sumHall.textContent = hallOpt && hallOpt.value ? hallOpt.dataset.name || hallOpt.text.split('(')[0].trim() : '—';

        // Event
        const evtOpt = eventSel.options[eventSel.selectedIndex];
        sumEvent.textContent = evtOpt && evtOpt.value ? evtOpt.text : '—';

        // Date
        sumDate.textContent = dateFld.value ? fmtDate(dateFld.value) : '—';

        // Time
        const st = startFld.value, et = endFld.value;
        sumTime.textContent = (st && et) ? `${fmtTime(st)} – ${fmtTime(et)}` : (st ? fmtTime(st) : '—');

        // Guests
        sumGuests.textContent = peopleFld.value ? peopleFld.value + ' guests' : '—';

        // Meals
        const meals = [];
        if (bfChk.checked) meals.push('☀️ Breakfast');
        if (lnChk.checked) meals.push('🍛 Lunch');
        if (dnChk.checked) meals.push('🌙 Dinner');
        sumMeals.textContent = meals.length ? meals.join(' · ') : 'None';

        updateFinancials();
        updateStatusBadges();
    }

    // ── Status badges in summary ──────────────────────────────────────────────
    const payStatusColors = { pending: ['warning', 'clock-fill'], partial: ['info', 'hourglass-split'], paid: ['success', 'check-circle-fill'] };
    const bkStatusColors  = { confirmed: ['success', 'check-circle-fill'], completed: ['primary', 'trophy-fill'], cancelled: ['danger', 'x-circle-fill'] };

    function updateStatusBadges() {
        const payVal = document.getElementById('payment_status').value;
        const bkVal  = document.querySelector('input[name="status"]:checked')?.value || 'confirmed';

        const [pc, pi] = payStatusColors[payVal] || ['secondary', 'circle'];
        sumPayBadge.className = `summary-pill bg-${pc} bg-opacity-10 text-${pc}`;
        sumPayBadge.innerHTML = `<i class="bi bi-${pi}" style="font-size:.6rem"></i> ${document.querySelector(`option[value="${payVal}"]`)?.text || payVal}`;

        const [bc, bi] = bkStatusColors[bkVal] || ['secondary', 'circle'];
        sumStatusBadge.className = `summary-pill bg-${bc} bg-opacity-10 text-${bc}`;
        sumStatusBadge.innerHTML = `<i class="bi bi-${bi}" style="font-size:.6rem"></i> ${bkVal.charAt(0).toUpperCase() + bkVal.slice(1)}`;
    }

    // ── Availability check ────────────────────────────────────────────────────
    let availTimer = null;

    function checkAvailability() {
        if (!hallSel.value || !dateFld.value || !startFld.value || !endFld.value) {
            setAvailState('idle', 'Select hall, date and time to check availability');
            return;
        }
        clearTimeout(availTimer);
        setAvailState('loading', 'Checking availability…');

        availTimer = setTimeout(async () => {
            try {
                const params = new URLSearchParams({
                    hall_id:      hallSel.value,
                    booking_date: dateFld.value,
                    start_time:   startFld.value,
                    end_time:     endFld.value,
                });
                const res  = await fetch('{{ route("hall.bookings.check-availability") }}?' + params);
                const data = await res.json();

                if (data.available) {
                    setAvailState('ok', '✓ Hall is available for the selected time slot');
                } else {
                    const c = data.conflicts.map(x => `${x.customer} (${x.start}–${x.end})`).join(', ');
                    setAvailState('conflict', `✗ Conflict: ${c}`);
                }
            } catch {
                setAvailState('idle', 'Could not check availability');
            }
        }, 600);
    }

    // ── Event bindings ────────────────────────────────────────────────────────
    [hallSel, dateFld, startFld, endFld].forEach(el => {
        el.addEventListener('change', () => { checkAvailability(); updateSummary(); });
    });
    [peopleFld, eventSel].forEach(el => el.addEventListener('input', updateSummary));
    [bfChk, lnChk, dnChk].forEach(el => el.addEventListener('change', updateSummary));
    [totalFld, advFld].forEach(el => el.addEventListener('input', updateFinancials));
    document.getElementById('payment_status').addEventListener('change', updateStatusBadges);
    document.querySelectorAll('input[name="status"]').forEach(r => r.addEventListener('change', updateStatusBadges));

    // ── Init ──────────────────────────────────────────────────────────────────
    updateSummary();
    // Re-check availability if old values restored (validation failure)
    if (hallSel.value && dateFld.value && startFld.value && endFld.value) {
        checkAvailability();
    }
})();
</script>
@endpush

</x-admin-layout>
