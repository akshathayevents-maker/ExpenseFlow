<x-admin-layout title="Create Booking">
@php
    $prefillDate    = old('booking_date', request('date'));
    $prefillHall    = old('hall_id', request('hall_id'));
    $oldServices    = old('services', []);
    $oldBookingType = old('booking_type', 'hall_food');
@endphp

<div class="ef-booking-create">
    <header class="ef-create-hero">
        <div>
            <a href="{{ route('hall.bookings.index') }}" class="ef-back mb-3">
                <i class="bi bi-arrow-left"></i>
                Hall bookings
            </a>
            <div class="ef-eyebrow">Akshathay Mini Hall</div>
            <h1 class="ef-create-title">Create Booking</h1>
            <div class="ef-shell-note">Schedule a new event booking with availability intelligence, catering context, and payment preview.</div>
        </div>

        <div class="ef-create-actions">
            <a href="{{ route('hall.bookings.calendar') }}" class="ef-btn">
                <i class="bi bi-calendar3"></i> Calendar
            </a>
            <a href="#availability-panel" class="ef-btn">
                <i class="bi bi-search"></i> Availability
            </a>
            <button type="button" class="ef-btn" id="saveDraftBtn">
                <i class="bi bi-bookmark"></i> Save Draft
            </button>
        </div>
    </header>

    @if ($errors->any())
        <div class="ef-card mb-4">
            <div class="ef-card-body">
                <h2 class="ef-card-title mb-3">Review Required</h2>
                <div class="ef-shell-note">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('hall.bookings.store') }}" id="bookingForm" novalidate>
        @csrf

        <div class="ef-create-layout">
            <main class="ef-flow">

                {{-- ─── Section 1: Customer ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">1</span>
                        <h2 class="ef-section-heading">Customer</h2>
                        <p class="ef-section-copy">Capture the primary contact for confirmations, payments, and event coordination.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-6">
                            <label class="ef-label" for="customer_name">Customer Name</label>
                            <input id="customer_name" name="customer_name" class="ef-input" value="{{ old('customer_name') }}" placeholder="Customer full name" required autofocus>
                            @error('customer_name')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="ef-span-3">
                            <label class="ef-label" for="customer_mobile">Mobile</label>
                            <input id="customer_mobile" name="customer_mobile" type="tel" class="ef-input" value="{{ old('customer_mobile') }}" placeholder="10-digit mobile number" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" required>
                            @error('customer_mobile')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="ef-span-3">
                            <label class="ef-label" for="customer_alt_mobile">Alternate Mobile</label>
                            <input id="customer_alt_mobile" name="customer_alt_mobile" type="tel" class="ef-input" value="{{ old('customer_alt_mobile') }}" placeholder="Optional (10 digits)" pattern="[0-9]{10}" maxlength="10" inputmode="numeric">
                            @error('customer_alt_mobile')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </x-premium.card>

                {{-- ─── Section 2: Booking Type ─── --}}
                <x-premium.card id="section-booking-type">
                    <div class="ef-section-intro">
                        <span class="ef-section-number">2</span>
                        <h2 class="ef-section-heading">Booking Type</h2>
                        <p class="ef-section-copy">Choose what this booking covers — hall reservation, catering, or both.</p>
                    </div>

                    <input type="hidden" name="booking_type" id="booking_type" value="{{ $oldBookingType }}">

                    <div class="ef-type-picker" role="radiogroup" aria-label="Booking type">
                        <button type="button" class="ef-type-opt {{ $oldBookingType === 'hall_only' ? '--selected' : '' }}" data-type="hall_only" role="radio" aria-checked="{{ $oldBookingType === 'hall_only' ? 'true' : 'false' }}">
                            <i class="bi bi-building ef-type-icon"></i>
                            <span class="ef-type-label">Hall Only</span>
                            <span class="ef-type-sub">Venue only, no catering</span>
                        </button>
                        <button type="button" class="ef-type-opt {{ $oldBookingType === 'hall_food' ? '--selected' : '' }}" data-type="hall_food" role="radio" aria-checked="{{ $oldBookingType === 'hall_food' ? 'true' : 'false' }}">
                            <span class="ef-type-icon-stack">
                                <i class="bi bi-building"></i><i class="bi bi-cup-hot"></i>
                            </span>
                            <span class="ef-type-label">Hall + Food</span>
                            <span class="ef-type-sub">Full service — venue &amp; catering</span>
                        </button>
                        <button type="button" class="ef-type-opt {{ $oldBookingType === 'food_only' ? '--selected' : '' }}" data-type="food_only" role="radio" aria-checked="{{ $oldBookingType === 'food_only' ? 'true' : 'false' }}">
                            <i class="bi bi-cup-hot ef-type-icon"></i>
                            <span class="ef-type-label">Food Only</span>
                            <span class="ef-type-sub">External catering order</span>
                        </button>
                    </div>
                    @error('booking_type')<div class="ef-field-error mt-2">{{ $message }}</div>@enderror
                </x-premium.card>

                {{-- ─── Section 3: Event ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">3</span>
                        <h2 class="ef-section-heading">Event</h2>
                        <p class="ef-section-copy">Schedule the event, assign the venue or service location, and set the guest count.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-4">
                            <label class="ef-label" for="event_type">Event Type</label>
                            <select id="event_type" name="event_type" class="ef-select" required>
                                <option value="">Select event</option>
                                @foreach(\App\Models\HallBooking::eventTypes() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('event_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('event_type')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- Hall field — hidden for food_only --}}
                        <div class="ef-span-4" id="field-hall">
                            <label class="ef-label" for="hall_id">Hall</label>
                            <select id="hall_id" name="hall_id" class="ef-select">
                                <option value="">Select hall</option>
                                @foreach($halls as $hall)
                                    <option value="{{ $hall->id }}"
                                        data-name="{{ $hall->name }}"
                                        data-capacity="{{ $hall->capacity }}"
                                        @selected((string) $prefillHall === (string) $hall->id)>
                                        {{ $hall->name }} · {{ number_format($hall->capacity) }} guests
                                    </option>
                                @endforeach
                            </select>
                            @error('hall_id')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- Service location — shown only for food_only --}}
                        <div class="ef-span-4" id="field-service-location" style="display:none">
                            <label class="ef-label" for="service_location">Service Location <span style="color:var(--ef-danger)">*</span></label>
                            <input id="service_location" name="service_location" class="ef-input"
                                   value="{{ old('service_location') }}"
                                   placeholder="e.g. TCS Office, Murugan Temple, Client Residence"
                                   list="serviceLocationSuggestions">
                            <datalist id="serviceLocationSuggestions">
                                <option value="Client Residence">
                                <option value="Corporate Office">
                                <option value="Temple">
                                <option value="Outdoor Venue">
                                <option value="School Campus">
                                <option value="Farmhouse">
                            </datalist>
                            @error('service_location')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="number_of_people">Guest Count</label>
                            <input id="number_of_people" name="number_of_people" type="number" min="1" class="ef-input" value="{{ old('number_of_people') }}" placeholder="Expected guests" required>
                            @error('number_of_people')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="booking_date">Event Date</label>
                            <input id="booking_date" name="booking_date" type="date" class="ef-input" value="{{ $prefillDate }}" min="{{ today()->toDateString() }}" required>
                            @error('booking_date')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="start_time">Start Time</label>
                            <input id="start_time" name="start_time" type="time" class="ef-input" value="{{ old('start_time') }}" required>
                            @error('start_time')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="end_time">End Time</label>
                            <input id="end_time" name="end_time" type="time" class="ef-input" value="{{ old('end_time') }}" required>
                            @error('end_time')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        {{-- Availability panel — hidden for food_only --}}
                        <div class="ef-span-12" id="field-availability">
                            <div class="ef-availability" id="availability-panel" data-state="idle">
                                <div class="ef-availability-status">
                                    <span class="ef-availability-dot"></span>
                                    <div>
                                        <div class="ef-availability-title" id="availabilityTitle">Availability awaiting schedule</div>
                                        <div class="ef-availability-copy" id="availabilityCopy">Select a hall, date, start time, and end time to check occupancy.</div>
                                    </div>
                                </div>
                                <div class="ef-nearby-list" id="nearbyBookings"></div>
                            </div>
                        </div>
                    </div>
                </x-premium.card>

                {{-- ─── Section 4: Meals ─── --}}
                <x-premium.card id="section-meals">
                    <div class="ef-section-intro">
                        <span class="ef-section-number">4</span>
                        <h2 class="ef-section-heading">Meals</h2>
                        <p class="ef-section-copy">Attach catering context so the kitchen can understand the operational load from the booking calendar.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-6">
                            <label class="ef-label" for="meal_plan_id">Catering Package</label>
                            <select id="meal_plan_id" name="meal_plan_id" class="ef-select">
                                <option value="" data-price="0">No meal plan</option>
                                @foreach($mealPlans as $plan)
                                    <option value="{{ $plan->id }}" data-price="{{ $plan->price_per_person }}" @selected(old('meal_plan_id') == $plan->id)>
                                        {{ $plan->name }} · ₹{{ number_format($plan->price_per_person, 0) }} per guest
                                    </option>
                                @endforeach
                            </select>
                            @error('meal_plan_id')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-6">
                            <span class="ef-label">Meal Selection</span>
                            <div class="ef-choice-row">
                                <label class="ef-choice ef-meal-choice">
                                    <input type="checkbox" id="has_breakfast" name="has_breakfast" value="1" @checked(old('has_breakfast'))>
                                    <span class="ef-choice-surface">Breakfast</span>
                                </label>
                                <label class="ef-choice ef-meal-choice">
                                    <input type="checkbox" id="has_lunch" name="has_lunch" value="1" @checked(old('has_lunch'))>
                                    <span class="ef-choice-surface">Lunch</span>
                                </label>
                                <label class="ef-choice ef-meal-choice">
                                    <input type="checkbox" id="has_dinner" name="has_dinner" value="1" @checked(old('has_dinner'))>
                                    <span class="ef-choice-surface">Dinner</span>
                                </label>
                            </div>
                        </div>

                        <div class="ef-span-12">
                            <div class="ef-meal-estimate-box" id="mealEstimateBox" style="display:none">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <i class="bi bi-calculator" style="color:var(--ef-accent);font-size:1.1rem"></i>
                                    <div>
                                        <div style="font-weight:600;font-size:.85rem;color:var(--ef-ink-2)" id="mealEstimateLabel">Catering estimate</div>
                                        <div style="font-size:.75rem;color:var(--ef-faint)" id="mealEstimateText">Select a meal plan and guest count to preview catering value.</div>
                                    </div>
                                </div>
                                <div style="font-size:1.1rem;font-weight:700;color:var(--ef-ink-1)" id="mealEstimateAmount"></div>
                            </div>
                            <div class="ef-shell-note" id="mealEstimateFallback">Select a meal plan and guest count to preview catering value.</div>
                        </div>
                    </div>
                </x-premium.card>

                {{-- ─── Section 4: Additional Services ─── --}}
                <x-premium.card>
                    {{-- Section header --}}
                    <div class="ef-addon-section-header">
                        <div class="ef-addon-icon-orb">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div class="ef-addon-title-block">
                            <div class="ef-section-intro" style="margin-bottom:4px;padding:0">
                                <span class="ef-section-number">4</span>
                                <h2 class="ef-section-heading">Add-On Services</h2>
                            </div>
                            <p class="ef-section-copy" style="margin:0">Enhance the event with premium extras — decoration, photography, DJ, lighting &amp; more. Each service is itemized separately on the invoice.</p>
                        </div>
                    </div>

                    {{-- Quick-add suggestion chips --}}
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

                    {{-- Datalist for autocomplete --}}
                    <datalist id="addonServiceSuggestions">
                        <option value="Decoration">
                        <option value="Photography">
                        <option value="Videography">
                        <option value="DJ">
                        <option value="Live Band">
                        <option value="Lighting">
                        <option value="Stage Setup">
                        <option value="Sound System">
                        <option value="Flower Setup">
                        <option value="Generator">
                        <option value="Catering Add-on">
                        <option value="Tent / Shamiana">
                        <option value="Security">
                        <option value="Valet Parking">
                        <option value="Welcome Arch">
                        <option value="Cake">
                        <option value="Fireworks">
                        <option value="Kids Entertainment">
                    </datalist>

                    {{-- Column headers (desktop only) --}}
                    <div class="ef-addon-col-headers">
                        <div class="ef-addon-col-h ef-addon-col-name">Service</div>
                        <div class="ef-addon-col-h ef-addon-col-desc">Description <span class="ef-optional">(optional)</span></div>
                        <div class="ef-addon-col-h ef-addon-col-amt">Amount</div>
                        <div class="ef-addon-col-h ef-addon-col-del"></div>
                    </div>

                    {{-- Service rows container --}}
                    <div id="servicesContainer">
                        @forelse($oldServices as $i => $svc)
                        <div class="ef-addon-card" data-index="{{ $i }}">
                            <div class="ef-addon-row">
                                <div class="ef-addon-col-name">
                                    <label class="ef-addon-mobile-label">Service</label>
                                    <input type="text"
                                           name="services[{{ $i }}][service_name]"
                                           class="ef-input ef-addon-name-input"
                                           value="{{ $svc['service_name'] ?? '' }}"
                                           placeholder="e.g. Decoration, DJ…"
                                           list="addonServiceSuggestions"
                                           autocomplete="off"
                                           required>
                                    @error("services.{$i}.service_name")<div class="ef-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="ef-addon-col-desc">
                                    <label class="ef-addon-mobile-label">Description <span class="ef-optional">(optional)</span></label>
                                    <input type="text"
                                           name="services[{{ $i }}][description]"
                                           class="ef-input"
                                           value="{{ $svc['description'] ?? '' }}"
                                           placeholder="Brief note…">
                                </div>
                                <div class="ef-addon-col-amt">
                                    <label class="ef-addon-mobile-label">Amount</label>
                                    <div class="ef-input-prefix-wrap">
                                        <span class="ef-input-prefix">₹</span>
                                        <input type="number"
                                               name="services[{{ $i }}][amount]"
                                               class="ef-input ef-input-prefixed ef-service-amount ef-addon-amount-input"
                                               value="{{ $svc['amount'] ?? '' }}"
                                               step="0.01" min="0"
                                               placeholder="0"
                                               required>
                                    </div>
                                    @error("services.{$i}.amount")<div class="ef-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="ef-addon-col-del">
                                    <button type="button" class="ef-addon-remove" title="Remove service" onclick="removeService(this)" aria-label="Remove this service">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        {{-- empty state shown below --}}
                        @endforelse
                    </div>

                    {{-- Empty state --}}
                    <div id="servicesEmpty" class="ef-addon-empty" style="{{ count($oldServices) ? 'display:none' : '' }}">
                        <div class="ef-addon-empty-icon"><i class="bi bi-bag-plus"></i></div>
                        <div class="ef-addon-empty-title">No add-on services yet</div>
                        <div class="ef-addon-empty-body">Use the quick-add chips above or click the button below to include extras like decoration, DJ, or photography.</div>
                    </div>

                    {{-- Live subtotal bar --}}
                    <div class="ef-addon-subtotal" id="addonSubtotalBar" style="display:none">
                        <i class="bi bi-receipt"></i>
                        <span>Add-on subtotal</span>
                        <strong id="addonSubtotalValue">₹0</strong>
                    </div>

                    {{-- Add button --}}
                    <div class="ef-addon-actions">
                        <button type="button" class="ef-addon-add-btn" id="addServiceBtn">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>Add Service</span>
                        </button>
                    </div>
                </x-premium.card>

                {{-- ─── Section 5: Payment ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">5</span>
                        <h2 class="ef-section-heading">Payment</h2>
                        <p class="ef-section-copy">Enter the hall rental cost and advance collected. The total is computed automatically from all line items.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-4">
                            <label class="ef-label" for="hall_cost">Hall Rental Cost</label>
                            <div class="ef-input-prefix-wrap">
                                <span class="ef-input-prefix">₹</span>
                                <input id="hall_cost" name="hall_cost" type="number" step="0.01" min="0"
                                       class="ef-input ef-input-prefixed"
                                       value="{{ old('hall_cost', 0) }}"
                                       placeholder="0.00">
                            </div>
                            @error('hall_cost')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="total_amount">
                                Total Amount
                                <span class="ef-label-badge">Auto-calculated</span>
                            </label>
                            <div class="ef-input-prefix-wrap">
                                <span class="ef-input-prefix">₹</span>
                                <input id="total_amount" name="total_amount" type="number" step="0.01" min="0"
                                       class="ef-input ef-input-prefixed ef-input-computed"
                                       value="{{ old('total_amount', 0) }}"
                                       readonly
                                       tabindex="-1">
                            </div>
                            <div class="ef-field-hint">Hall + Meals + Services</div>
                            @error('total_amount')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="advance_amount">Advance Amount</label>
                            <div class="ef-input-prefix-wrap">
                                <span class="ef-input-prefix">₹</span>
                                <input id="advance_amount" name="advance_amount" type="number" step="0.01" min="0"
                                       class="ef-input ef-input-prefixed"
                                       value="{{ old('advance_amount', 0) }}"
                                       placeholder="0.00"
                                       required>
                            </div>
                            @error('advance_amount')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="payment_status">Payment Status</label>
                            <select id="payment_status" name="payment_status" class="ef-select" required>
                                @foreach(\App\Models\HallBooking::paymentStatuses() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_status', 'pending') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_status')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-12">
                            <span class="ef-label">Payment Method</span>
                            <div class="ef-choice-row">
                                @foreach(\App\Models\BookingPayment::methods() as $value => $label)
                                    <label class="ef-choice">
                                        <input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', 'cash') === $value)>
                                        <span class="ef-choice-surface">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-premium.card>

                {{-- ─── Section 6: Confirmation ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">6</span>
                        <h2 class="ef-section-heading">Confirmation</h2>
                        <p class="ef-section-copy">Set the booking state and add any operational notes for decoration, seating, catering, or customer requests.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-12">
                            <span class="ef-label">Booking Status</span>
                            <div class="ef-choice-row">
                                @foreach(\App\Models\HallBooking::statuses() as $value => $label)
                                    <label class="ef-choice">
                                        <input type="radio" name="status" value="{{ $value }}" @checked(old('status', 'confirmed') === $value)>
                                        <span class="ef-choice-surface">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="ef-span-12">
                            <label class="ef-label" for="notes">Notes & Special Requirements</label>
                            <textarea id="notes" name="notes" class="ef-textarea" placeholder="Seating, decor, catering preferences, customer requests...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </x-premium.card>

                <div class="ef-submit-bar">
                    <a href="{{ route('hall.bookings.index') }}" class="ef-btn">Cancel</a>
                    <button type="button" class="ef-btn" id="saveDraftBtnBottom">
                        <i class="bi bi-bookmark"></i> Save Draft
                    </button>
                    <button type="submit" class="ef-btn ef-btn-dark" id="submitBtn">
                        <i class="bi bi-check2"></i> Create Booking
                    </button>
                </div>
            </main>

            {{-- ─── Sidebar: Financial Preview ─── --}}
            <aside class="ef-summary-rail">
                <x-premium.card>
                    <div class="ef-summary-identity">
                        <span class="ef-label">Booking Preview</span>
                        <div class="ef-summary-name" id="summaryName">New event booking</div>
                        <div class="ef-summary-meta" id="summaryMeta">Customer and event details will appear here as you type.</div>
                    </div>

                    <div class="ef-summary-line">
                        <span class="ef-muted">Hall</span>
                        <strong id="summaryHall">Not selected</strong>
                    </div>
                    <div class="ef-summary-line">
                        <span class="ef-muted">Date</span>
                        <strong id="summaryDate">Not selected</strong>
                    </div>
                    <div class="ef-summary-line">
                        <span class="ef-muted">Time</span>
                        <strong id="summaryTime">Not selected</strong>
                    </div>
                    <div class="ef-summary-line">
                        <span class="ef-muted">Guests</span>
                        <strong id="summaryGuests">0</strong>
                    </div>
                    <div class="ef-summary-line">
                        <span class="ef-muted">Meals</span>
                        <strong id="summaryMeals">None</strong>
                    </div>

                    <div class="ef-summary-total">
                        <span class="ef-label">Financial Breakdown</span>

                        <div class="ef-summary-line ef-fin-line">
                            <span class="ef-muted">Hall Rental</span>
                            <strong id="finHallCost">₹0</strong>
                        </div>
                        <div class="ef-summary-line ef-fin-line">
                            <span class="ef-muted">Meals</span>
                            <strong id="finMealCost">₹0</strong>
                        </div>
                        <div class="ef-summary-line ef-fin-line" id="finServicesRow" style="display:none">
                            <span class="ef-muted">Add-on Services</span>
                            <strong id="finServicesTotal">₹0</strong>
                        </div>
                        <div class="ef-summary-line ef-fin-subtotal">
                            <span class="ef-muted">Subtotal</span>
                            <strong id="finSubtotal">₹0</strong>
                        </div>

                        <div class="ef-summary-total-value" id="summaryTotal">₹0</div>
                        <div class="ef-summary-line">
                            <span class="ef-muted">Advance</span>
                            <strong id="summaryAdvance">₹0</strong>
                        </div>
                        <div class="ef-summary-line">
                            <span class="ef-muted">Balance</span>
                            <strong id="summaryBalance">₹0</strong>
                        </div>
                    </div>

                    <div class="mt-4 d-grid gap-2">
                        <button type="submit" form="bookingForm" class="ef-btn ef-btn-dark">
                            <i class="bi bi-check2"></i> Create Booking
                        </button>
                    </div>
                </x-premium.card>
            </aside>
        </div>
    </form>
</div>

<div class="ef-mobile-submit">
    <div>
        <span class="ef-label mb-1">Balance</span>
        <strong id="mobileBalance">₹0</strong>
    </div>
    <button type="submit" form="bookingForm" class="ef-btn ef-btn-dark">
        Create Booking
    </button>
</div>

@push('styles')
<style>
/* ══════════════════════════════════════════════════
   ADD-ON SERVICES — Premium Service Builder
   ══════════════════════════════════════════════════ */

/* Section header */
.ef-addon-section-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 20px;
}
.ef-addon-icon-orb {
    width: 46px; height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--ef-ink-1, #1a1a1a), #3d3d3d);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    margin-top: 2px;
    box-shadow: 0 3px 10px rgba(0,0,0,.18);
}
.ef-addon-title-block { flex: 1; min-width: 0; }

/* Quick-add chips */
.ef-addon-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 7px;
    padding: 12px 14px;
    background: var(--ef-surface-2, #fafaf8);
    border: 1px solid var(--ef-border);
    border-radius: 10px;
    margin-bottom: 18px;
}
.ef-addon-chip-label {
    font-size: .68rem;
    font-weight: 800;
    color: var(--ef-faint);
    letter-spacing: .09em;
    text-transform: uppercase;
    white-space: nowrap;
    margin-right: 4px;
}
.ef-addon-chip {
    padding: 5px 13px;
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    background: #fff;
    color: var(--ef-ink-2);
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .14s, color .14s, border-color .14s, transform .12s, box-shadow .14s;
    white-space: nowrap;
    line-height: 1.4;
}
.ef-addon-chip:hover {
    background: var(--ef-ink-1, #1a1a1a);
    color: #fff;
    border-color: var(--ef-ink-1, #1a1a1a);
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,.14);
}
.ef-addon-chip:active { transform: translateY(0); box-shadow: none; }

/* Column headers — desktop */
.ef-addon-col-headers {
    display: flex;
    gap: 10px;
    padding: 0 16px 10px;
    border-bottom: 1.5px solid var(--ef-border);
    margin-bottom: 10px;
}
.ef-addon-col-h {
    font-size: .67rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--ef-faint);
}

/* Column widths — shared between headers and rows */
.ef-addon-col-name { flex: 0 0 38%; min-width: 0; }
.ef-addon-col-desc { flex: 0 0 32%; min-width: 0; }
.ef-addon-col-amt  { flex: 0 0 22%; min-width: 0; }
.ef-addon-col-del  { flex: 0 0 8%;  display: flex; align-items: center; justify-content: center; }

/* Service card */
.ef-addon-card {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    transition: border-color .15s, box-shadow .15s;
    animation: efAddonIn .22s cubic-bezier(.34,1.28,.64,1);
}
.ef-addon-card:hover {
    border-color: var(--ef-ink-4, #c8c4bb);
    box-shadow: 0 3px 14px rgba(0,0,0,.08);
}
@keyframes efAddonIn {
    from { opacity: 0; transform: translateY(-10px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Row inside card */
.ef-addon-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

/* Mobile labels (hidden on desktop, shown on mobile) */
.ef-addon-mobile-label {
    display: none;
    font-size: .67rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: var(--ef-faint);
    margin-bottom: 5px;
}

/* Amount — right-aligned tabular figures */
.ef-addon-amount-input {
    text-align: right !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums;
}
.ef-addon-amount-input:focus {
    text-align: left !important;
}

/* Remove button — circular danger */
.ef-addon-remove {
    width: 36px; height: 36px;
    border: 1.5px solid var(--ef-border);
    border-radius: 50%;
    background: transparent;
    color: var(--ef-faint);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: .78rem;
    transition: background .14s, color .14s, border-color .14s, transform .12s;
}
.ef-addon-remove:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
    transform: scale(1.12);
}
.ef-addon-remove:active { transform: scale(.95); }

/* Empty state */
.ef-addon-empty {
    border: 2px dashed var(--ef-border);
    border-radius: 14px;
    padding: 40px 24px;
    text-align: center;
    background: var(--ef-surface-2, #fafaf8);
    margin: 2px 0 14px;
}
.ef-addon-empty-icon {
    font-size: 2rem;
    color: var(--ef-faint);
    opacity: .45;
    margin-bottom: 10px;
}
.ef-addon-empty-title {
    font-size: .9rem;
    font-weight: 700;
    color: var(--ef-ink-3, #999);
    margin-bottom: 5px;
}
.ef-addon-empty-body {
    font-size: .78rem;
    color: var(--ef-faint);
    max-width: 320px;
    margin: 0 auto;
    line-height: 1.55;
}

/* Live subtotal bar */
.ef-addon-subtotal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: var(--ef-surface-2, #fafaf8);
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    margin-top: 10px;
    font-size: .84rem;
    color: var(--ef-ink-2);
    animation: efFadeUp .18s ease;
}
.ef-addon-subtotal i { color: var(--ef-accent, #1a1a1a); font-size: .9rem; }
.ef-addon-subtotal strong {
    margin-left: auto;
    font-size: 1rem;
    color: var(--ef-ink-1);
    font-variant-numeric: tabular-nums;
}

/* Add button — premium dark */
.ef-addon-actions { margin-top: 14px; }
.ef-addon-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: var(--ef-ink-1, #1a1a1a);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: .84rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .14s, transform .12s, box-shadow .14s;
    box-shadow: 0 2px 8px rgba(0,0,0,.16);
}
.ef-addon-add-btn:hover {
    background: #333;
    transform: translateY(-2px);
    box-shadow: 0 5px 18px rgba(0,0,0,.2);
}
.ef-addon-add-btn:active { transform: translateY(0); box-shadow: 0 1px 4px rgba(0,0,0,.12); }
.ef-addon-add-btn i { font-size: 1rem; }

@keyframes efFadeUp {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── MOBILE: stacked card layout ─────────────────────── */
@media (max-width: 640px) {
    .ef-addon-col-headers { display: none; }

    .ef-addon-row {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .ef-addon-col-name,
    .ef-addon-col-desc { flex: none; width: 100%; }

    .ef-addon-col-amt {
        flex: none;
        width: 100%;
    }
    .ef-addon-col-del {
        flex: none;
        justify-content: flex-end;
        margin-top: 2px;
    }
    .ef-addon-mobile-label { display: block; }

    /* Expand remove button to pill on mobile */
    .ef-addon-remove {
        width: auto;
        height: 40px;
        border-radius: 8px;
        padding: 0 16px;
        gap: 7px;
    }
    .ef-addon-remove-label { display: inline; }

    .ef-addon-amount-input { text-align: left !important; font-size: 1rem; }

    .ef-addon-add-btn { width: 100%; justify-content: center; padding: 14px; font-size: .9rem; }
    .ef-addon-chips { gap: 6px; }
}
/* ── Computed total field ── */
.ef-input-computed {
    background: var(--ef-surface-2, #f7f6f3) !important;
    color: var(--ef-ink-2) !important;
    font-weight: 700 !important;
    cursor: default;
}
.ef-label-badge {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    background: var(--ef-accent, #1a1a1a);
    color: #fff;
    border-radius: 4px;
    padding: 1px 6px;
    margin-left: 6px;
    vertical-align: middle;
}
.ef-field-hint {
    font-size: .7rem;
    color: var(--ef-faint);
    margin-top: 4px;
}
/* ── Prefixed input wrapper ── */
.ef-input-prefix-wrap {
    position: relative;
}
.ef-input-prefix {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ef-faint);
    font-size: .9rem;
    pointer-events: none;
    z-index: 1;
}
.ef-input-prefixed { padding-left: 26px !important; }
/* ── Meal estimate box ── */
.ef-meal-estimate-box {
    border: 1px solid var(--ef-border);
    border-radius: 10px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: var(--ef-surface-2, #fafaf8);
}
/* ── Sidebar financial lines ── */
.ef-fin-line { opacity: .85; }
.ef-fin-subtotal {
    margin-top: 6px;
    padding-top: 8px;
    border-top: 1px solid var(--ef-border);
}
.ef-optional {
    font-weight: 400;
    color: var(--ef-faint);
    font-size: .75em;
}

/* ── Booking Type Picker ────────────────────────────────────────── */
.ef-type-picker {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 640px) {
    .ef-type-picker { grid-template-columns: 1fr; }
}
.ef-type-opt {
    align-items: flex-start;
    background: var(--ef-surface);
    border: 2px solid var(--ef-border);
    border-radius: var(--ef-radius);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 16px 18px;
    text-align: left;
    transition: border-color .15s, background .15s, box-shadow .15s;
    -webkit-appearance: none;
    appearance: none;
}
.ef-type-opt:hover {
    border-color: rgba(160,114,56,.4);
    box-shadow: 0 0 0 3px rgba(160,114,56,.08);
}
.ef-type-opt.--selected {
    background: rgba(160,114,56,.06);
    border-color: rgba(160,114,56,.6);
    box-shadow: 0 0 0 3px rgba(160,114,56,.1);
}
.ef-type-icon {
    color: var(--ef-muted);
    font-size: 1.25rem;
    margin-bottom: 4px;
    transition: color .15s;
}
.ef-type-icon-stack {
    color: var(--ef-muted);
    display: flex;
    font-size: 1.1rem;
    gap: 3px;
    margin-bottom: 4px;
    transition: color .15s;
}
.ef-type-opt.--selected .ef-type-icon,
.ef-type-opt.--selected .ef-type-icon-stack { color: #a07238; }
.ef-type-label {
    color: var(--ef-ink);
    font-size: .88rem;
    font-weight: 720;
}
.ef-type-sub {
    color: var(--ef-faint);
    font-size: .72rem;
}
/* hall_only = blue, hall_food = green, food_only = orange */
.ef-type-opt[data-type="hall_only"].--selected { border-color: rgba(59,130,246,.55); background: rgba(59,130,246,.05); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.ef-type-opt[data-type="hall_only"].--selected .ef-type-icon { color: #1d4ed8; }
.ef-type-opt[data-type="hall_food"].--selected { border-color: rgba(22,163,74,.55); background: rgba(22,163,74,.05); box-shadow: 0 0 0 3px rgba(22,163,74,.1); }
.ef-type-opt[data-type="hall_food"].--selected .ef-type-icon-stack { color: #15803d; }
.ef-type-opt[data-type="food_only"].--selected { border-color: rgba(234,88,12,.55); background: rgba(234,88,12,.05); box-shadow: 0 0 0 3px rgba(234,88,12,.1); }
.ef-type-opt[data-type="food_only"].--selected .ef-type-icon { color: #c2410c; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    /* ───── DOM refs ───── */
    const form = document.getElementById('bookingForm');
    const fields = {
        customer:      document.getElementById('customer_name'),
        mobile:        document.getElementById('customer_mobile'),
        hall:          document.getElementById('hall_id'),
        event:         document.getElementById('event_type'),
        people:        document.getElementById('number_of_people'),
        date:          document.getElementById('booking_date'),
        start:         document.getElementById('start_time'),
        end:           document.getElementById('end_time'),
        mealPlan:      document.getElementById('meal_plan_id'),
        hallCost:      document.getElementById('hall_cost'),
        total:         document.getElementById('total_amount'),
        advance:       document.getElementById('advance_amount'),
        paymentStatus: document.getElementById('payment_status'),
        notes:         document.getElementById('notes'),
    };
    const meals = [
        document.getElementById('has_breakfast'),
        document.getElementById('has_lunch'),
        document.getElementById('has_dinner'),
    ];
    const mealLabels = { has_breakfast: 'Breakfast', has_lunch: 'Lunch', has_dinner: 'Dinner' };

    const draftKey      = 'akshathay.booking.create.draft';
    const canRestoreDraft = @json(! session()->hasOldInput() && ! request()->hasAny(['date', 'hall_id']));
    const availability     = document.getElementById('availability-panel');
    const availabilityTitle = document.getElementById('availabilityTitle');
    const availabilityCopy  = document.getElementById('availabilityCopy');
    const nearbyBookings    = document.getElementById('nearbyBookings');
    let availabilityTimer;

    let serviceIndex = {{ count($oldServices) }};

    /* ───── Helpers ───── */
    const rupee = v => '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const selectedText = sel => sel.options[sel.selectedIndex]?.text?.split('·')[0]?.trim() || '';
    const selectedData = (sel, key) => sel.options[sel.selectedIndex]?.dataset?.[key] || '';

    function formatDate(value) {
        if (!value) return 'Not selected';
        return new Date(value + 'T00:00:00').toLocaleDateString('en-IN', {
            weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
        });
    }
    function formatTime(value) {
        if (!value) return '';
        const [h, m] = value.split(':');
        return new Date(2000, 0, 1, h, m).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
    }
    function selectedMeals() {
        return meals.filter(i => i.checked).map(i => mealLabels[i.id]);
    }
    function mealCost() {
        const price  = Number(fields.mealPlan.options[fields.mealPlan.selectedIndex]?.dataset?.price || 0);
        const guests = Number(fields.people.value || 0);
        return price * guests;
    }
    function servicesTotal() {
        return [...document.querySelectorAll('.ef-service-amount')]
            .reduce((sum, el) => sum + Number(el.value || 0), 0);
    }

    /* ───── Financial engine ───── */
    function recalcFinancials() {
        const hallCost = Number(fields.hallCost.value || 0);
        const meal     = mealCost();
        const services = servicesTotal();
        const subtotal = hallCost + meal + services;

        // Auto-fill total (readonly)
        fields.total.value = subtotal.toFixed(2);

        const advance = Number(fields.advance.value || 0);
        const balance = Math.max(0, subtotal - advance);

        return { hallCost, meal, services, subtotal, advance, balance };
    }

    /* ───── Update summary sidebar ───── */
    function updateSummary() {
        const { hallCost, meal, services, subtotal, advance, balance } = recalcFinancials();

        const name  = fields.customer.value.trim();
        const event = selectedText(fields.event);
        const hall  = selectedData(fields.hall, 'name') || selectedText(fields.hall);
        const guests = Number(fields.people.value || 0);
        const start  = formatTime(fields.start.value);
        const end    = formatTime(fields.end.value);
        const mealList = selectedMeals();

        // Identity
        document.getElementById('summaryName').textContent = name || 'New event booking';
        document.getElementById('summaryMeta').textContent =
            [event || 'Event type', hall || 'Hall', guests ? `${guests} guests` : null].filter(Boolean).join(' · ');

        // Event details
        document.getElementById('summaryHall').textContent  = hall || 'Not selected';
        document.getElementById('summaryDate').textContent  = formatDate(fields.date.value);
        document.getElementById('summaryTime').textContent  = start && end ? `${start} - ${end}` : 'Not selected';
        document.getElementById('summaryGuests').textContent = guests ? guests.toLocaleString('en-IN') : '0';
        document.getElementById('summaryMeals').textContent = mealList.length ? mealList.join(', ') : 'None';

        // Financial breakdown
        document.getElementById('finHallCost').textContent    = rupee(hallCost);
        document.getElementById('finMealCost').textContent    = rupee(meal);
        document.getElementById('finServicesTotal').textContent = rupee(services);
        document.getElementById('finSubtotal').textContent    = rupee(subtotal);

        const servicesRow = document.getElementById('finServicesRow');
        servicesRow.style.display = services > 0 ? '' : 'none';

        // Grand totals
        document.getElementById('summaryTotal').textContent   = rupee(subtotal);
        document.getElementById('summaryAdvance').textContent = rupee(advance);
        document.getElementById('summaryBalance').textContent = rupee(balance);
        document.getElementById('mobileBalance').textContent  = rupee(balance);

        // Meal estimate display
        const mealBox      = document.getElementById('mealEstimateBox');
        const mealFallback = document.getElementById('mealEstimateFallback');
        const planSelected = fields.mealPlan.value;
        if (planSelected && guests > 0) {
            const price = Number(fields.mealPlan.options[fields.mealPlan.selectedIndex]?.dataset?.price || 0);
            document.getElementById('mealEstimateLabel').textContent = 'Catering estimate';
            document.getElementById('mealEstimateText').textContent  =
                `${guests.toLocaleString('en-IN')} guests × ₹${price.toLocaleString('en-IN')}/person`;
            document.getElementById('mealEstimateAmount').textContent = rupee(meal);
            mealBox.style.display = 'flex';
            mealFallback.style.display = 'none';
        } else {
            mealBox.style.display = 'none';
            mealFallback.style.display = '';
        }

        // Add-on subtotal bar
        const addonBar = document.getElementById('addonSubtotalBar');
        const addonVal = document.getElementById('addonSubtotalValue');
        if (addonBar) {
            if (services > 0) {
                addonVal.textContent = rupee(services);
                addonBar.style.display = 'flex';
            } else {
                addonBar.style.display = 'none';
            }
        }
    }

    /* ───── Services repeater ───── */
    function updateEmptyState() {
        const rows = document.querySelectorAll('.ef-addon-card');
        document.getElementById('servicesEmpty').style.display = rows.length ? 'none' : '';
    }

    window.removeService = function (btn) {
        const row = btn.closest('.ef-addon-card');
        row.style.transition = 'opacity .18s ease, transform .18s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateY(-6px) scale(.97)';
        setTimeout(() => { row.remove(); updateEmptyState(); updateSummary(); }, 190);
    };

    function addServiceRow(name = '', desc = '', amount = '') {
        const idx = serviceIndex++;
        const container = document.getElementById('servicesContainer');
        const card = document.createElement('div');
        card.className = 'ef-addon-card';
        card.dataset.index = idx;
        card.innerHTML = `
            <div class="ef-addon-row">
                <div class="ef-addon-col-name">
                    <label class="ef-addon-mobile-label">Service</label>
                    <input type="text"
                           name="services[${idx}][service_name]"
                           class="ef-input ef-addon-name-input"
                           value="${escHtml(name)}"
                           placeholder="e.g. Decoration, DJ…"
                           list="addonServiceSuggestions"
                           autocomplete="off"
                           required>
                </div>
                <div class="ef-addon-col-desc">
                    <label class="ef-addon-mobile-label">Description <span class="ef-optional">(optional)</span></label>
                    <input type="text"
                           name="services[${idx}][description]"
                           class="ef-input"
                           value="${escHtml(desc)}"
                           placeholder="Brief note…">
                </div>
                <div class="ef-addon-col-amt">
                    <label class="ef-addon-mobile-label">Amount</label>
                    <div class="ef-input-prefix-wrap">
                        <span class="ef-input-prefix">₹</span>
                        <input type="number"
                               name="services[${idx}][amount]"
                               class="ef-input ef-input-prefixed ef-service-amount ef-addon-amount-input"
                               value="${escHtml(amount)}"
                               step="0.01" min="0"
                               placeholder="0"
                               required>
                    </div>
                </div>
                <div class="ef-addon-col-del">
                    <button type="button" class="ef-addon-remove" title="Remove service"
                            onclick="removeService(this)" aria-label="Remove this service">
                        <i class="bi bi-x-lg"></i>
                        <span class="ef-addon-remove-label"></span>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card);
        card.querySelector('.ef-service-amount').addEventListener('input', updateSummary);
        card.querySelector('.ef-addon-name-input').focus();
        updateEmptyState();
        updateSummary();
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    document.getElementById('addServiceBtn').addEventListener('click', () => addServiceRow());

    // Quick-add chips
    document.querySelectorAll('.ef-addon-chip').forEach(chip => {
        chip.addEventListener('click', () => addServiceRow(chip.dataset.name));
    });

    // Attach listeners to existing service rows restored from old()
    document.querySelectorAll('.ef-service-amount').forEach(el => {
        el.addEventListener('input', updateSummary);
    });

    /* ───── Availability check ───── */
    function setAvailability(state, title, copy) {
        availability.dataset.state = state;
        availabilityTitle.textContent = title;
        availabilityCopy.textContent  = copy;
    }

    async function checkAvailability() {
        const currentType = bookingTypeInput?.value || 'hall_food';
        // food_only never occupies a hall — no conflict possible
        if (currentType === 'food_only') {
            setAvailability('available', 'No hall required', 'Food-only orders do not reserve a hall slot.');
            nearbyBookings.classList.remove('show');
            nearbyBookings.innerHTML = '';
            return;
        }
        if (!fields.hall.value || !fields.date.value || !fields.start.value || !fields.end.value) {
            setAvailability('idle', 'Availability awaiting schedule', 'Select a hall, date, start time, and end time to check occupancy.');
            nearbyBookings.classList.remove('show');
            nearbyBookings.innerHTML = '';
            return;
        }
        clearTimeout(availabilityTimer);
        setAvailability('loading', 'Checking hall occupancy', 'Reviewing the selected date and time against existing bookings.');

        availabilityTimer = setTimeout(async () => {
            try {
                const checkParams = new URLSearchParams({
                    hall_id: fields.hall.value, booking_date: fields.date.value,
                    start_time: fields.start.value, end_time: fields.end.value,
                });
                const checkData = await fetch(@json(route('hall.bookings.check-availability')) + '?' + checkParams).then(r => r.json());

                const dayParams = new URLSearchParams({ start: fields.date.value, end: fields.date.value, hall_id: fields.hall.value });
                const dayEvents = await fetch(@json(route('hall.bookings.calendar-events')) + '?' + dayParams).then(r => r.json());

                const density = dayEvents.length;
                if (!checkData.available) {
                    const conflicts = checkData.conflicts.map(i => `${i.customer} · ${i.start}-${i.end}`).join(', ');
                    setAvailability('blocked', 'Time conflict detected', conflicts || 'This slot overlaps an existing booking.');
                } else if (density > 0) {
                    setAvailability('partial', 'Available with nearby bookings', `${density} booking${density === 1 ? '' : 's'} already scheduled for this hall on this date.`);
                } else {
                    setAvailability('available', 'Hall available', 'No existing bookings found for this hall on the selected date.');
                }

                if (dayEvents.length) {
                    nearbyBookings.innerHTML = dayEvents.slice(0, 4).map(ev => {
                        const p = ev.extendedProps || {};
                        return `<div class="ef-nearby-item"><span>${p.start_time || ''} · ${p.customer || ev.title}</span><strong>${p.people || 0} guests</strong></div>`;
                    }).join('');
                    nearbyBookings.classList.add('show');
                } else {
                    nearbyBookings.classList.remove('show');
                    nearbyBookings.innerHTML = '';
                }
            } catch {
                setAvailability('idle', 'Availability check unavailable', 'Could not reach the availability service. You can still submit after manual review.');
            }
        }, 450);
    }

    /* ───── Draft ───── */
    function collectDraft() {
        return Object.fromEntries(new FormData(form).entries());
    }
    function saveDraft() {
        localStorage.setItem(draftKey, JSON.stringify(collectDraft()));
        setAvailability('available', 'Draft saved locally', 'This booking draft has been saved in this browser.');
    }
    function restoreDraft() {
        if (!canRestoreDraft) return;
        const raw = localStorage.getItem(draftKey);
        if (!raw) return;
        try {
            const draft = JSON.parse(raw);
            Object.entries(draft).forEach(([name, value]) => {
                form.querySelectorAll(`[name="${CSS.escape(name)}"]`).forEach(el => {
                    if (el.type === 'checkbox' || el.type === 'radio') el.checked = el.value === value;
                    else el.value = value;
                });
            });
        } catch {}
    }

    /* ───── Wire up field listeners ───── */
    Object.values(fields).forEach(field => {
        if (!field) return;
        field.addEventListener('input', updateSummary);
        field.addEventListener('change', () => {
            updateSummary();
            if (['hall_id', 'booking_date', 'start_time', 'end_time'].includes(field.id)) checkAvailability();
        });
    });
    meals.forEach(meal => meal.addEventListener('change', updateSummary));
    document.querySelectorAll('input[name="status"], input[name="payment_method"]').forEach(el => el.addEventListener('change', updateSummary));

    document.getElementById('saveDraftBtn').addEventListener('click', saveDraft);
    document.getElementById('saveDraftBtnBottom').addEventListener('click', saveDraft);

    /* ───── Booking type switcher ───── */
    const bookingTypeInput   = document.getElementById('booking_type');
    const fieldHall          = document.getElementById('field-hall');
    const fieldServiceLoc    = document.getElementById('field-service-location');
    const fieldAvailability  = document.getElementById('field-availability');
    const sectionMeals       = document.getElementById('section-meals');
    const hallInput          = document.getElementById('hall_id');
    const serviceLocInput    = document.getElementById('service_location');

    function applyBookingType(type) {
        const needsHall = type !== 'food_only';
        const hasFood   = type !== 'hall_only';

        // Hall field
        if (fieldHall) fieldHall.style.display = needsHall ? '' : 'none';
        if (hallInput) hallInput.required = needsHall;

        // Service location (food_only only)
        if (fieldServiceLoc) fieldServiceLoc.style.display = !needsHall ? '' : 'none';
        if (serviceLocInput) serviceLocInput.required = !needsHall;

        // Availability widget — irrelevant for food_only (no hall conflict possible)
        if (fieldAvailability) fieldAvailability.style.display = needsHall ? '' : 'none';

        // Meals section
        if (sectionMeals) sectionMeals.style.display = hasFood ? '' : 'none';

        // Zero hall_cost when not booking a hall
        if (!needsHall && fields.hallCost) fields.hallCost.value = '0';

        // Update hidden input
        if (bookingTypeInput) bookingTypeInput.value = type;

        // Re-run availability if switching back to a hall type
        if (needsHall) checkAvailability();

        updateSummary();
    }

    document.querySelectorAll('.ef-type-opt').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.ef-type-opt').forEach(b => {
                b.classList.remove('--selected');
                b.setAttribute('aria-checked', 'false');
            });
            btn.classList.add('--selected');
            btn.setAttribute('aria-checked', 'true');
            applyBookingType(btn.dataset.type);
        });
    });

    /* ───── Init ───── */
    restoreDraft();
    updateSummary();
    applyBookingType(bookingTypeInput?.value || 'hall_food');
    // checkAvailability is called inside applyBookingType for hall types
})();
</script>
@endpush
</x-admin-layout>
