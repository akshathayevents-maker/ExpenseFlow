<x-admin-layout title="Edit Booking #{{ $booking->id }}">
@push('styles')
<style>
/* ── Edit Booking — ef-eb-* ──────────────────────────────────────── */
.ef-eb-shell { max-width: 780px; margin: 0 auto; padding-bottom: 80px; }

.ef-eb-card {
    background: rgba(255,253,250,.96);
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: var(--ef-shadow);
    margin-bottom: 16px;
    overflow: hidden;
}
.ef-eb-section {
    padding: 22px 26px;
}
.ef-eb-section + .ef-eb-section { border-top: 1px solid var(--ef-border); }

.ef-eb-section-label {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .15em;
    margin-bottom: 16px;
    text-transform: uppercase;
}

.ef-eb-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, 1fr); }
.ef-eb-span2 { grid-column: 1 / -1; }

/* Meal plan cards */
.ef-eb-meal-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    margin-top: 10px;
}
.ef-eb-meal-card {
    border: 2px solid var(--ef-border);
    border-radius: 12px;
    cursor: pointer;
    padding: 12px 14px;
    transition: border-color .15s, background .15s, box-shadow .15s;
    user-select: none;
    position: relative;
}
.ef-eb-meal-card:hover { border-color: var(--ef-border-strong); background: rgba(20,20,18,.02); }
.ef-eb-meal-card.selected {
    border-color: #3d7358;
    background: rgba(61,115,88,.06);
    box-shadow: 0 0 0 3px rgba(61,115,88,.12);
}
.ef-eb-meal-card.no-plan {
    border-style: dashed;
    color: var(--ef-muted);
}
.ef-eb-meal-card.no-plan.selected { border-color: var(--ef-faint); background: rgba(20,20,18,.03); }
.ef-eb-meal-name { font-size: .88rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 3px; }
.ef-eb-meal-price { font-size: .78rem; color: var(--ef-emerald); font-weight: 640; }
.ef-eb-meal-cat {
    display: inline-block;
    font-size: .56rem; font-weight: 760; letter-spacing: .1em; text-transform: uppercase;
    padding: 2px 7px; border-radius: 5px; margin-bottom: 6px;
    background: rgba(20,20,18,.04); border: 1px solid rgba(20,20,18,.07); color: var(--ef-muted);
}
.ef-eb-meal-cat.--premium { background: rgba(169,131,56,.09); border-color: rgba(169,131,56,.22); color: var(--ef-gold); }
.ef-eb-meal-cat.--custom  { background: rgba(61,115,88,.08);  border-color: rgba(61,115,88,.2);  color: var(--ef-emerald); }
.ef-eb-meal-check {
    position: absolute; top: 10px; right: 10px;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--ef-emerald); color: #fff;
    display: none; align-items: center; justify-content: center;
    font-size: .55rem;
}
.ef-eb-meal-card.selected .ef-eb-meal-check { display: flex; }

/* Catering estimate */
.ef-eb-meal-estimate {
    align-items: center;
    background: rgba(61,115,88,.06);
    border: 1px solid rgba(61,115,88,.2);
    border-radius: 12px;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-top: 14px;
    padding: 12px 16px;
}
.ef-eb-meal-estimate-lbl { font-size: .8rem; font-weight: 600; color: var(--ef-ink-2); }
.ef-eb-meal-estimate-sub { font-size: .72rem; color: var(--ef-muted); margin-top: 2px; }
.ef-eb-meal-estimate-amt { font-size: 1.05rem; font-weight: 760; color: var(--ef-emerald); }

/* Cost breakdown strip */
.ef-eb-breakdown {
    background: rgba(20,20,18,.025);
    border: 1px solid var(--ef-border);
    border-radius: 12px;
    padding: 14px 16px;
    margin-top: 6px;
}
.ef-eb-breakdown-row {
    align-items: center;
    display: flex;
    font-size: .82rem;
    justify-content: space-between;
    padding: 4px 0;
}
.ef-eb-breakdown-row + .ef-eb-breakdown-row { border-top: 1px solid rgba(20,20,18,.05); }
.ef-eb-breakdown-row.total {
    border-top: 2px solid var(--ef-border-strong) !important;
    font-weight: 760;
    margin-top: 4px;
    padding-top: 8px;
    font-size: .9rem;
}

/* Avail indicator */
#availStatus { min-height: 24px; }

/* ── Add-On Services ── */
.ef-addon-section-header { display:flex; align-items:flex-start; gap:14px; margin-bottom:20px; }
.ef-addon-icon-orb { width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#1a1a1a,#3d3d3d);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;margin-top:2px;box-shadow:0 3px 10px rgba(0,0,0,.18); }
.ef-addon-title-block { flex:1;min-width:0; }
.ef-addon-chips { display:flex;flex-wrap:wrap;align-items:center;gap:7px;padding:12px 14px;background:#fafaf8;border:1px solid var(--ef-border);border-radius:10px;margin-bottom:18px; }
.ef-addon-chip-label { font-size:.68rem;font-weight:800;color:var(--ef-faint);letter-spacing:.09em;text-transform:uppercase;white-space:nowrap;margin-right:4px; }
.ef-addon-chip { padding:5px 13px;border:1px solid var(--ef-border);border-radius:20px;background:#fff;color:var(--ef-ink-2);font-size:.78rem;font-weight:600;cursor:pointer;transition:background .14s,color .14s,border-color .14s,transform .12s;white-space:nowrap; }
.ef-addon-chip:hover { background:#1a1a1a;color:#fff;border-color:#1a1a1a;transform:translateY(-2px); }
.ef-addon-col-headers { display:flex;gap:10px;padding:0 16px 10px;border-bottom:1.5px solid var(--ef-border);margin-bottom:10px; }
.ef-addon-col-h { font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:var(--ef-faint); }
.ef-addon-col-name { flex:0 0 38%;min-width:0; }
.ef-addon-col-desc { flex:0 0 32%;min-width:0; }
.ef-addon-col-amt  { flex:0 0 22%;min-width:0; }
.ef-addon-col-del  { flex:0 0 8%;display:flex;align-items:center;justify-content:center; }
.ef-addon-card { background:#fff;border:1px solid var(--ef-border);border-radius:12px;padding:12px 14px;margin-bottom:8px;box-shadow:0 1px 4px rgba(0,0,0,.04);transition:border-color .15s,box-shadow .15s;animation:efAddonIn .22s cubic-bezier(.34,1.28,.64,1); }
.ef-addon-card:hover { border-color:#c8c4bb;box-shadow:0 3px 14px rgba(0,0,0,.08); }
@keyframes efAddonIn { from{opacity:0;transform:translateY(-10px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
.ef-addon-row { display:flex;align-items:flex-end;gap:10px; }
.ef-addon-mobile-label { display:none;font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:var(--ef-faint);margin-bottom:5px; }
.ef-addon-amount-input { text-align:right!important;font-weight:700!important;font-variant-numeric:tabular-nums; }
.ef-addon-amount-input:focus { text-align:left!important; }
.ef-addon-remove { width:36px;height:36px;border:1.5px solid var(--ef-border);border-radius:50%;background:transparent;color:var(--ef-faint);cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.78rem;transition:background .14s,color .14s,border-color .14s,transform .12s; }
.ef-addon-remove:hover { background:#fee2e2;color:#dc2626;border-color:#fca5a5;transform:scale(1.12); }
.ef-addon-empty { border:2px dashed var(--ef-border);border-radius:14px;padding:40px 24px;text-align:center;background:#fafaf8;margin:2px 0 14px; }
.ef-addon-empty-icon { font-size:2rem;color:var(--ef-faint);opacity:.45;margin-bottom:10px; }
.ef-addon-empty-title { font-size:.9rem;font-weight:700;color:#999;margin-bottom:5px; }
.ef-addon-empty-body { font-size:.78rem;color:var(--ef-faint);max-width:320px;margin:0 auto;line-height:1.55; }
.ef-addon-subtotal { display:flex;align-items:center;gap:8px;padding:10px 14px;background:#fafaf8;border:1px solid var(--ef-border);border-radius:8px;margin-top:10px;font-size:.84rem;color:var(--ef-ink-2); }
.ef-addon-subtotal strong { margin-left:auto;font-size:1rem;font-variant-numeric:tabular-nums; }
.ef-addon-actions { margin-top:14px; }
.ef-addon-add-btn { display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:#1a1a1a;color:#fff;border:none;border-radius:10px;font-size:.84rem;font-weight:700;cursor:pointer;transition:background .14s,transform .12s,box-shadow .14s;box-shadow:0 2px 8px rgba(0,0,0,.16); }
.ef-addon-add-btn:hover { background:#333;transform:translateY(-2px);box-shadow:0 5px 18px rgba(0,0,0,.2); }

/* Booking type picker */
.ef-type-picker { display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; }
.ef-type-opt {
    flex:1; min-width:120px;
    display:flex; flex-direction:column; align-items:center; gap:6px;
    padding:14px 10px; border-radius:14px; cursor:pointer;
    border:2px solid var(--ef-border); background:transparent;
    font-size:.78rem; font-weight:700; color:var(--ef-ink-2);
    transition:border-color .15s, background .15s, box-shadow .15s;
}
.ef-type-opt:hover { border-color:var(--ef-border-strong); }
.ef-type-icon, .ef-type-icon-stack { font-size:1.2rem; }
.ef-type-icon-stack { display:inline-flex; gap:2px; }
.ef-type-opt[data-type="hall_only"].--selected { border-color:#1d4ed8; background:rgba(29,78,216,.07); color:#1d4ed8; box-shadow:0 0 0 3px rgba(29,78,216,.13); }
.ef-type-opt[data-type="hall_food"].--selected { border-color:#15803d; background:rgba(21,128,61,.07);  color:#15803d; box-shadow:0 0 0 3px rgba(21,128,61,.13); }
.ef-type-opt[data-type="food_only"].--selected { border-color:#c2410c; background:rgba(194,65,12,.07);  color:#c2410c; box-shadow:0 0 0 3px rgba(194,65,12,.13); }

@media (max-width: 767.98px) {
    .ef-eb-section { padding: 16px; }
    .ef-eb-grid { grid-template-columns: 1fr; }
    .ef-eb-meal-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

<div class="ef-eb-shell">

    {{-- Page header --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <a href="{{ route('hall.bookings.show', $booking) }}" class="ef-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 style="font-size:1.22rem;font-weight:760;color:var(--ef-ink);margin:0;letter-spacing:-.01em">
                Edit Booking #{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}
            </h1>
            <div style="font-size:.8rem;color:var(--ef-muted);margin-top:2px">{{ $booking->customer_name }} · {{ $booking->booking_date->format('d M Y') }}</div>
        </div>
    </div>

    @if ($errors->any())
        <div style="background:rgba(141,74,60,.06);border:1px solid rgba(141,74,60,.18);border-radius:12px;color:var(--ef-danger);font-size:.82rem;margin-bottom:16px;padding:14px 18px">
            <strong>Please fix:</strong>
            <ul style="margin:6px 0 0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('hall.bookings.update', $booking) }}" id="bookingForm" novalidate>
    @csrf @method('PUT')

    {{-- ── Customer ── --}}
    <div class="ef-eb-card">
        <div class="ef-eb-section">
            <p class="ef-eb-section-label">Customer</p>
            <div class="ef-eb-grid">
                <div>
                    <label class="ef-label">Customer Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" name="customer_name" class="ef-input @error('customer_name') --error @enderror"
                           value="{{ old('customer_name', $booking->customer_name) }}" required>
                    @error('customer_name')<div class="ef-field-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="ef-label">Mobile <span style="color:var(--ef-danger)">*</span></label>
                    <input type="tel" name="customer_mobile" class="ef-input @error('customer_mobile') --error @enderror"
                           value="{{ old('customer_mobile', $booking->customer_mobile) }}" placeholder="10-digit mobile number" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" required>
                    @error('customer_mobile')<div class="ef-field-error">{{ $message }}</div>@enderror
                </div>
                <div class="ef-eb-span2">
                    <label class="ef-label">Alternate Mobile</label>
                    <input type="tel" name="customer_alt_mobile" class="ef-input @error('customer_alt_mobile') --error @enderror"
                           value="{{ old('customer_alt_mobile', $booking->customer_alt_mobile) }}" placeholder="Optional (10 digits)" pattern="[0-9]{10}" maxlength="10" inputmode="numeric">
                    @error('customer_alt_mobile')<div class="ef-field-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── Booking Type ── --}}
    <div class="ef-eb-card">
        <div class="ef-eb-section">
            <p class="ef-eb-section-label">Booking Type</p>
            <input type="hidden" name="booking_type" id="booking_type" value="{{ old('booking_type', $booking->booking_type ?? 'hall_food') }}">
            <div class="ef-type-picker" role="radiogroup">
                @foreach(\App\Models\HallBooking::bookingTypes() as $typeVal => $typeLabel)
                @php $isSelected = old('booking_type', $booking->booking_type ?? 'hall_food') === $typeVal; @endphp
                <button type="button" class="ef-type-opt {{ $isSelected ? '--selected' : '' }}" data-type="{{ $typeVal }}"
                        role="radio" aria-checked="{{ $isSelected ? 'true' : 'false' }}">
                    @if($typeVal === 'hall_only')
                        <i class="bi bi-building ef-type-icon"></i>
                    @elseif($typeVal === 'hall_food')
                        <span class="ef-type-icon-stack"><i class="bi bi-building"></i><i class="bi bi-cup-hot"></i></span>
                    @else
                        <i class="bi bi-cup-hot ef-type-icon"></i>
                    @endif
                    <span class="ef-type-label">{{ $typeLabel }}</span>
                </button>
                @endforeach
            </div>
            @error('booking_type')<div class="ef-field-error mt-2">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Event ── --}}
    <div class="ef-eb-card">
        <div class="ef-eb-section">
            <p class="ef-eb-section-label">Event Details</p>
            <div class="ef-eb-grid">
                <div id="field-hall">
                    <label class="ef-label">Hall <span style="color:var(--ef-danger)">*</span></label>
                    <select id="hall_id" name="hall_id" class="ef-select">
                        @foreach($halls as $hall)
                            <option value="{{ $hall->id }}" {{ old('hall_id', $booking->hall_id) == $hall->id ? 'selected' : '' }}>{{ $hall->name }}</option>
                        @endforeach
                    </select>
                    @error('hall_id')<div class="ef-field-error">{{ $message }}</div>@enderror
                </div>
                <div id="field-service-location" style="display:none">
                    <label class="ef-label">Service Location <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="service_location" name="service_location" class="ef-input @error('service_location') --error @enderror"
                           value="{{ old('service_location', $booking->service_location) }}"
                           placeholder="e.g. TCS Office, Temple, Client Residence">
                    @error('service_location')<div class="ef-field-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="ef-label">Event Type <span style="color:var(--ef-danger)">*</span></label>
                    <select name="event_type" class="ef-select" required>
                        @foreach(\App\Models\HallBooking::eventTypes() as $v => $l)
                            <option value="{{ $v }}" {{ old('event_type', $booking->event_type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-label">Booking Date <span style="color:var(--ef-danger)">*</span></label>
                    <input type="date" id="booking_date" name="booking_date" class="ef-input @error('booking_date') --error @enderror"
                           value="{{ old('booking_date', $booking->booking_date->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="ef-label">Guests <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" id="number_of_people" name="number_of_people" class="ef-input @error('number_of_people') --error @enderror"
                           value="{{ old('number_of_people', $booking->number_of_people) }}" min="1" required>
                </div>
                <div>
                    <label class="ef-label">Start Time <span style="color:var(--ef-danger)">*</span></label>
                    <input type="time" id="start_time" name="start_time" class="ef-input @error('start_time') --error @enderror"
                           value="{{ old('start_time', $booking->start_time) }}" required>
                </div>
                <div>
                    <label class="ef-label">End Time <span style="color:var(--ef-danger)">*</span></label>
                    <input type="time" id="end_time" name="end_time" class="ef-input @error('end_time') --error @enderror"
                           value="{{ old('end_time', $booking->end_time) }}" required>
                </div>
                <div class="ef-eb-span2" id="field-availability"><div id="availStatus"></div></div>
            </div>
        </div>
    </div>

    {{-- ── Meal Plan ── --}}
    <div id="section-meals" class="ef-eb-card">
        <div class="ef-eb-section">
            <p class="ef-eb-section-label">Catering Package</p>

            {{-- Hidden input carries the selected ID --}}
            <input type="hidden" id="meal_plan_id" name="meal_plan_id" value="{{ old('meal_plan_id', $booking->meal_plan_id ?? '') }}">

            <div class="ef-eb-meal-grid">
                {{-- No plan option --}}
                <div class="ef-eb-meal-card no-plan {{ old('meal_plan_id', $booking->meal_plan_id) == '' ? 'selected' : '' }}"
                     data-id="" data-price="0" onclick="selectMealCard(this)">
                    <div class="ef-eb-meal-check"><i class="bi bi-check"></i></div>
                    <div class="ef-eb-meal-name" style="color:var(--ef-muted)">No Package</div>
                    <div class="ef-eb-meal-price" style="color:var(--ef-faint)">No catering cost</div>
                </div>
                @foreach($mealPlans as $mp)
                @php
                    $catClass = $mp->category === 'premium' ? '--premium' : ($mp->category === 'custom' ? '--custom' : '');
                    $selected = old('meal_plan_id', $booking->meal_plan_id) == $mp->id;
                @endphp
                <div class="ef-eb-meal-card {{ $selected ? 'selected' : '' }}"
                     data-id="{{ $mp->id }}" data-price="{{ $mp->price_per_person }}"
                     onclick="selectMealCard(this)">
                    <div class="ef-eb-meal-check"><i class="bi bi-check"></i></div>
                    <span class="ef-eb-meal-cat {{ $catClass }}">{{ ucfirst($mp->category) }}</span>
                    <div class="ef-eb-meal-name">{{ $mp->name }}</div>
                    <div class="ef-eb-meal-price">₹{{ number_format($mp->price_per_person, 0) }} / guest</div>
                    @if($mp->description)
                        <div style="font-size:.7rem;color:var(--ef-muted);margin-top:4px;line-height:1.4">{{ Str::limit($mp->description, 50) }}</div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Catering estimate box --}}
            <div class="ef-eb-meal-estimate" id="mealEstimateBox" style="display:none">
                <div>
                    <div class="ef-eb-meal-estimate-lbl"><i class="bi bi-calculator me-1"></i>Catering Estimate</div>
                    <div class="ef-eb-meal-estimate-sub" id="mealEstimateSub">—</div>
                </div>
                <div class="ef-eb-meal-estimate-amt" id="mealEstimateAmt">₹0</div>
            </div>

            {{-- Meal checkboxes --}}
            <div style="display:flex;gap:20px;margin-top:16px;flex-wrap:wrap">
                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.85rem;font-weight:600;color:var(--ef-ink-2)">
                    <input type="checkbox" name="has_breakfast" value="1" {{ old('has_breakfast', $booking->has_breakfast) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--ef-emerald)">
                    <i class="bi bi-cup-hot" style="color:#d97706"></i> Breakfast
                </label>
                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.85rem;font-weight:600;color:var(--ef-ink-2)">
                    <input type="checkbox" name="has_lunch" value="1" {{ old('has_lunch', $booking->has_lunch) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--ef-emerald)">
                    <i class="bi bi-sun" style="color:#f59e0b"></i> Lunch
                </label>
                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.85rem;font-weight:600;color:var(--ef-ink-2)">
                    <input type="checkbox" name="has_dinner" value="1" {{ old('has_dinner', $booking->has_dinner) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--ef-emerald)">
                    <i class="bi bi-moon-stars" style="color:#6366f1"></i> Dinner
                </label>
            </div>
        </div>
    </div>

    {{-- ── Payment & Status ── --}}
    <div class="ef-eb-card">
        <div class="ef-eb-section">
            <p class="ef-eb-section-label">Payment & Status</p>

            <div class="ef-eb-grid">
                <div>
                    <label class="ef-label">Hall Rental Cost (₹)</label>
                    <input type="number" id="hall_cost" name="hall_cost" step="0.01" min="0" class="ef-input"
                           value="{{ old('hall_cost', $booking->hall_cost ?? 0) }}" placeholder="0.00"
                           oninput="recalcTotal()">
                </div>
                <div>
                    <label class="ef-label">
                        Total Amount (₹) <span style="color:var(--ef-danger)">*</span>
                        <span style="font-size:.62rem;font-weight:500;color:var(--ef-muted);margin-left:4px">auto-calculated</span>
                    </label>
                    <input type="number" id="total_amount" name="total_amount" step="0.01" min="0" class="ef-input"
                           value="{{ old('total_amount', $booking->total_amount) }}"
                           style="background:rgba(20,20,18,.03);color:var(--ef-muted)" readonly tabindex="-1">
                </div>
                <div>
                    <label class="ef-label">Advance Amount (₹) <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" id="advance_amount" name="advance_amount" step="0.01" min="0" class="ef-input @error('advance_amount') --error @enderror"
                           value="{{ old('advance_amount', $booking->advance_amount) }}" required oninput="recalcTotal()">
                    @error('advance_amount')<div class="ef-field-error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="ef-label">Payment Status</label>
                    <select name="payment_status" class="ef-select">
                        @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                            <option value="{{ $v }}" {{ old('payment_status', $booking->payment_status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-label">Booking Status</label>
                    <select name="status" class="ef-select">
                        @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                            <option value="{{ $v }}" {{ old('status', $booking->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div></div>
            </div>

            {{-- Cost breakdown --}}
            <div class="ef-eb-breakdown" id="costBreakdown">
                <div class="ef-eb-breakdown-row">
                    <span style="color:var(--ef-muted)">Hall Rental</span>
                    <span id="bdHall">₹0</span>
                </div>
                <div class="ef-eb-breakdown-row" id="bdMealRow" style="display:none">
                    <span style="color:var(--ef-muted)">Catering</span>
                    <span id="bdMeal" style="color:var(--ef-emerald)">₹0</span>
                </div>
                <div class="ef-eb-breakdown-row" id="bdAddonsRow" style="display:none">
                    <span style="color:var(--ef-muted)">Add-Ons</span>
                    <span id="bdAddons">₹0</span>
                </div>
                <div class="ef-eb-breakdown-row total">
                    <span>Total</span>
                    <span id="bdTotal" style="color:var(--ef-ink)">₹0</span>
                </div>
                <div class="ef-eb-breakdown-row" style="border-top:none;padding-top:2px">
                    <span style="color:var(--ef-muted)">Balance Due</span>
                    <span id="bdBalance" style="color:var(--ef-danger)">₹0</span>
                </div>
            </div>
        </div>

        {{-- ─── Add-On Services ─── --}}
        <div class="ef-eb-section">
            <div class="ef-addon-section-header">
                <div class="ef-addon-icon-orb"><i class="bi bi-stars"></i></div>
                <div class="ef-addon-title-block">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span class="ef-eb-section-label" style="margin-bottom:0">4</span>
                        <strong style="font-size:.95rem;color:var(--ef-ink)">Add-On Services</strong>
                    </div>
                    <p style="font-size:.78rem;color:var(--ef-muted);margin:0;line-height:1.5">Enhance the event with premium extras — decoration, photography, DJ, lighting &amp; more. Each service is itemized separately on the invoice.</p>
                </div>
            </div>

            <div class="ef-addon-chips" id="addonChips">
                <span class="ef-addon-chip-label">Quick add:</span>
                <button type="button" class="ef-addon-chip" data-name="Decoration">🎨 Decoration</button>
                <button type="button" class="ef-addon-chip" data-name="Photography">📷 Photography</button>
                <button type="button" class="ef-addon-chip" data-name="DJ">🎵 DJ</button>
                <button type="button" class="ef-addon-chip" data-name="Lighting">💡 Lighting</button>
                <button type="button" class="ef-addon-chip" data-name="Stage Setup">🎪 Stage Setup</button>
                <button type="button" class="ef-addon-chip" data-name="Sound System">🔊 Sound System</button>
                <button type="button" class="ef-addon-chip" data-name="Flower Setup">🌸 Flower Setup</button>
                <button type="button" class="ef-addon-chip" data-name="Generator">⚡ Generator</button>
            </div>

            <datalist id="addonServiceSuggestions">
                <option value="Decoration"><option value="Photography"><option value="Videography">
                <option value="DJ"><option value="Live Band"><option value="Lighting">
                <option value="Stage Setup"><option value="Sound System"><option value="Flower Setup">
                <option value="Generator"><option value="Catering Add-on"><option value="Tent / Shamiana">
                <option value="Security"><option value="Valet Parking"><option value="Welcome Arch">
                <option value="Cake"><option value="Fireworks"><option value="Kids Entertainment">
            </datalist>

            <div class="ef-addon-col-headers">
                <div class="ef-addon-col-h ef-addon-col-name">Service</div>
                <div class="ef-addon-col-h ef-addon-col-desc">Description <span style="font-weight:400;text-transform:none">(optional)</span></div>
                <div class="ef-addon-col-h ef-addon-col-amt">Amount</div>
                <div class="ef-addon-col-h ef-addon-col-del"></div>
            </div>

            <div id="servicesContainer">
                @foreach($oldServices as $i => $svc)
                <div class="ef-addon-card" data-index="{{ $i }}">
                    <div class="ef-addon-row">
                        <div class="ef-addon-col-name">
                            <label class="ef-addon-mobile-label">Service</label>
                            <input type="text" name="services[{{ $i }}][service_name]"
                                   class="ef-input ef-addon-name-input"
                                   value="{{ $svc['service_name'] ?? '' }}"
                                   placeholder="e.g. Decoration, DJ…"
                                   list="addonServiceSuggestions" autocomplete="off" required>
                        </div>
                        <div class="ef-addon-col-desc">
                            <label class="ef-addon-mobile-label">Description (optional)</label>
                            <input type="text" name="services[{{ $i }}][description]"
                                   class="ef-input" value="{{ $svc['description'] ?? '' }}"
                                   placeholder="Brief note…">
                        </div>
                        <div class="ef-addon-col-amt">
                            <label class="ef-addon-mobile-label">Amount</label>
                            <div class="ef-input-prefix-wrap">
                                <span class="ef-input-prefix">₹</span>
                                <input type="number" name="services[{{ $i }}][amount]"
                                       class="ef-input ef-input-prefixed ef-service-amount ef-addon-amount-input"
                                       value="{{ $svc['amount'] ?? '' }}"
                                       step="0.01" min="0" placeholder="0" required>
                            </div>
                        </div>
                        <div class="ef-addon-col-del">
                            <button type="button" class="ef-addon-remove" onclick="removeService(this)" title="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div id="servicesEmpty" class="ef-addon-empty" style="{{ count($oldServices) ? 'display:none' : '' }}">
                <div class="ef-addon-empty-icon"><i class="bi bi-bag-plus"></i></div>
                <div class="ef-addon-empty-title">No add-on services yet</div>
                <div class="ef-addon-empty-body">Use the quick-add chips above or click the button below to include extras like decoration, DJ, or photography.</div>
            </div>

            <div class="ef-addon-subtotal" id="addonSubtotalBar" style="{{ count($oldServices) ? '' : 'display:none' }}">
                <i class="bi bi-receipt"></i>
                <span>Add-on subtotal</span>
                <strong id="addonSubtotalValue">₹0</strong>
            </div>

            <div class="ef-addon-actions">
                <button type="button" class="ef-addon-add-btn" id="addServiceBtn">
                    <i class="bi bi-plus-circle-fill"></i> <span>Add Service</span>
                </button>
            </div>
        </div>

        <div class="ef-eb-section">
            <label class="ef-label">Notes</label>
            <textarea name="notes" rows="2" class="ef-textarea"
                      placeholder="Seating, decor, catering preferences, customer requests…">{{ old('notes', $booking->notes) }}</textarea>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:10px;align-items:center">
        <button type="submit" class="ef-btn ef-btn-dark" data-loading-text="Updating…">
            <i class="bi bi-check-circle"></i> Update Booking
        </button>
        <a href="{{ route('hall.bookings.show', $booking) }}" class="ef-btn">Cancel</a>
    </div>

    </form>
</div>

@push('scripts')
<script>
(function () {

    /* ── DOM refs ── */
    const hallSel        = document.getElementById('hall_id');
    const dateFld        = document.getElementById('booking_date');
    const startFld       = document.getElementById('start_time');
    const endFld         = document.getElementById('end_time');
    const statusDiv      = document.getElementById('availStatus');
    const bookingTypeInput  = document.getElementById('booking_type');
    const fieldHall         = document.getElementById('field-hall');
    const fieldServiceLoc   = document.getElementById('field-service-location');
    const serviceLocInput   = document.getElementById('service_location');
    const fieldAvailability = document.getElementById('field-availability');
    const sectionMeals      = document.getElementById('section-meals');
    const hallCostInput     = document.getElementById('hall_cost');
    let checkTimer = null;

    /* ── Booking type picker ── */
    document.querySelectorAll('.ef-type-opt').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.ef-type-opt').forEach(b => {
                b.classList.remove('--selected');
                b.setAttribute('aria-checked', 'false');
            });
            this.classList.add('--selected');
            this.setAttribute('aria-checked', 'true');
            applyBookingType(this.dataset.type);
        });
    });

    function applyBookingType(type) {
        const needsHall = type !== 'food_only';
        const hasFood   = type !== 'hall_only';

        fieldHall.style.display         = needsHall ? '' : 'none';
        hallSel.required                = needsHall;
        fieldServiceLoc.style.display   = needsHall ? 'none' : '';
        serviceLocInput.required        = !needsHall;
        fieldAvailability.style.display = needsHall ? '' : 'none';
        sectionMeals.style.display      = hasFood   ? '' : 'none';

        if (!needsHall && hallCostInput) hallCostInput.value = '0';
        bookingTypeInput.value = type;

        if (needsHall) checkAvailability();
        else statusDiv.innerHTML = '';
        recalcTotal();
    }

    /* ── Availability check ── */
    function checkAvailability() {
        const currentType = bookingTypeInput ? bookingTypeInput.value : 'hall_food';
        if (currentType === 'food_only') {
            statusDiv.innerHTML = '<span style="color:var(--ef-emerald);font-size:.82rem;font-weight:600"><i class="bi bi-check-circle me-1"></i>No hall required</span>';
            return;
        }
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
                    ? '<span style="color:var(--ef-emerald);font-size:.82rem;font-weight:600"><i class="bi bi-check-circle me-1"></i>Hall is available</span>'
                    : '<span style="color:var(--ef-danger);font-size:.82rem;font-weight:600"><i class="bi bi-x-circle me-1"></i>Conflict detected</span>';
            } catch { statusDiv.innerHTML = ''; }
        }, 500);
    }

    [hallSel, dateFld, startFld, endFld].forEach(el => el.addEventListener('change', checkAvailability));

    /* ── Meal plan card selector ── */
    window.selectMealCard = function(card) {
        document.querySelectorAll('.ef-eb-meal-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('meal_plan_id').value = card.dataset.id || '';
        recalcTotal();
    };

    /* ── Financial recalculation ── */
    function rupee(n) {
        return '₹' + Math.round(n).toLocaleString('en-IN');
    }

    /* ── Add-on services ── */
    let serviceIndex = {{ count($oldServices) }};

    function updateAddonSubtotal() {
        const amounts = document.querySelectorAll('.ef-addon-amount-input');
        let sum = 0;
        amounts.forEach(el => { sum += parseFloat(el.value || 0); });
        const bar = document.getElementById('addonSubtotalBar');
        const val = document.getElementById('addonSubtotalValue');
        if (sum > 0) {
            val.textContent = '₹' + Math.round(sum).toLocaleString('en-IN');
            bar.style.display = '';
        } else {
            bar.style.display = 'none';
        }
        recalcTotal();
    }

    function updateAddonEmpty() {
        const rows = document.querySelectorAll('.ef-addon-card');
        document.getElementById('servicesEmpty').style.display = rows.length ? 'none' : '';
    }

    window.removeService = function(btn) {
        const row = btn.closest('.ef-addon-card');
        row.style.transition = 'opacity .18s, transform .18s';
        row.style.opacity = '0'; row.style.transform = 'translateY(-6px) scale(.97)';
        setTimeout(() => { row.remove(); updateAddonEmpty(); updateAddonSubtotal(); }, 190);
    };

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function addServiceRow(name = '', desc = '', amount = '') {
        const idx = serviceIndex++;
        const container = document.getElementById('servicesContainer');
        const card = document.createElement('div');
        card.className = 'ef-addon-card'; card.dataset.index = idx;
        card.innerHTML = `
            <div class="ef-addon-row">
                <div class="ef-addon-col-name">
                    <label class="ef-addon-mobile-label">Service</label>
                    <input type="text" name="services[${idx}][service_name]"
                           class="ef-input ef-addon-name-input"
                           value="${escHtml(name)}" placeholder="e.g. Decoration, DJ…"
                           list="addonServiceSuggestions" autocomplete="off" required>
                </div>
                <div class="ef-addon-col-desc">
                    <label class="ef-addon-mobile-label">Description (optional)</label>
                    <input type="text" name="services[${idx}][description]"
                           class="ef-input" value="${escHtml(desc)}" placeholder="Brief note…">
                </div>
                <div class="ef-addon-col-amt">
                    <label class="ef-addon-mobile-label">Amount</label>
                    <div class="ef-input-prefix-wrap">
                        <span class="ef-input-prefix">₹</span>
                        <input type="number" name="services[${idx}][amount]"
                               class="ef-input ef-input-prefixed ef-service-amount ef-addon-amount-input"
                               value="${escHtml(amount)}" step="0.01" min="0" placeholder="0" required>
                    </div>
                </div>
                <div class="ef-addon-col-del">
                    <button type="button" class="ef-addon-remove" onclick="removeService(this)" title="Remove">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>`;
        container.appendChild(card);
        card.querySelector('.ef-addon-amount-input').addEventListener('input', updateAddonSubtotal);
        card.querySelector('.ef-addon-name-input').focus();
        updateAddonEmpty(); updateAddonSubtotal();
    }

    document.getElementById('addServiceBtn').addEventListener('click', () => addServiceRow());
    document.querySelectorAll('.ef-addon-chip').forEach(chip => {
        chip.addEventListener('click', () => addServiceRow(chip.dataset.name));
    });
    document.querySelectorAll('.ef-service-amount').forEach(el => {
        el.addEventListener('input', updateAddonSubtotal);
    });
    updateAddonSubtotal();

    window.recalcTotal = function() {
        const currentType = bookingTypeInput ? bookingTypeInput.value : 'hall_food';
        const guests    = parseInt(document.getElementById('number_of_people').value || 0);
        const rawHall   = currentType === 'food_only' ? 0 : parseFloat(hallCostInput ? hallCostInput.value : 0);
        const advance   = parseFloat(document.getElementById('advance_amount').value || 0);

        const mealCard  = document.querySelector('.ef-eb-meal-card.selected');
        const mealPrice = mealCard ? parseFloat(mealCard.dataset.price || 0) : 0;
        const mealCost  = currentType === 'hall_only' ? 0 : mealPrice * guests;

        let addonTotal = 0;
        document.querySelectorAll('.ef-addon-amount-input').forEach(el => { addonTotal += parseFloat(el.value || 0); });

        const total   = rawHall + mealCost + addonTotal;
        const balance = Math.max(0, total - advance);

        document.getElementById('total_amount').value = total.toFixed(2);

        document.getElementById('bdHall').textContent    = rupee(rawHall);
        document.getElementById('bdMeal').textContent    = rupee(mealCost);
        document.getElementById('bdAddons').textContent  = rupee(addonTotal);
        document.getElementById('bdTotal').textContent   = rupee(total);
        document.getElementById('bdBalance').textContent = rupee(balance);
        document.getElementById('bdMealRow').style.display   = mealCost    > 0 ? '' : 'none';
        document.getElementById('bdAddonsRow').style.display = addonTotal  > 0 ? '' : 'none';

        const box = document.getElementById('mealEstimateBox');
        const sub = document.getElementById('mealEstimateSub');
        const amt = document.getElementById('mealEstimateAmt');
        if (mealPrice > 0 && guests > 0 && currentType !== 'hall_only') {
            sub.textContent = guests.toLocaleString('en-IN') + ' guests × ₹' + mealPrice.toLocaleString('en-IN');
            amt.textContent = rupee(mealCost);
            box.style.display = 'flex';
        } else {
            box.style.display = 'none';
        }
    };

    document.getElementById('number_of_people').addEventListener('input', recalcTotal);
    if (hallCostInput) hallCostInput.addEventListener('input', recalcTotal);
    document.getElementById('advance_amount').addEventListener('input', recalcTotal);

    /* ── Init ── */
    applyBookingType(bookingTypeInput ? bookingTypeInput.value : 'hall_food');

})();
</script>
@endpush

</x-admin-layout>
