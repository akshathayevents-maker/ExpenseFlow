<x-admin-layout title="Create Booking">
@php
    $prefillDate = old('booking_date', request('date'));
    $prefillHall = old('hall_id', request('hall_id'));
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
                            <input id="customer_mobile" name="customer_mobile" type="tel" class="ef-input" value="{{ old('customer_mobile') }}" placeholder="Primary number" required>
                            @error('customer_mobile')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="ef-span-3">
                            <label class="ef-label" for="customer_alt_mobile">Alternate Mobile</label>
                            <input id="customer_alt_mobile" name="customer_alt_mobile" type="tel" class="ef-input" value="{{ old('customer_alt_mobile') }}" placeholder="Optional">
                            @error('customer_alt_mobile')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </x-premium.card>

                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">2</span>
                        <h2 class="ef-section-heading">Event</h2>
                        <p class="ef-section-copy">Choose the hall, schedule, and guest count. Availability updates as the operational details become clear.</p>
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

                        <div class="ef-span-4">
                            <label class="ef-label" for="hall_id">Hall</label>
                            <select id="hall_id" name="hall_id" class="ef-select" required>
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

                        <div class="ef-span-12">
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

                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">3</span>
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
                            <div class="ef-shell-note" id="mealEstimateText">Select a meal plan and guest count to preview catering value.</div>
                        </div>
                    </div>
                </x-premium.card>

                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">4</span>
                        <h2 class="ef-section-heading">Payment</h2>
                        <p class="ef-section-copy">Record the expected booking value, advance collected, and initial payment mode.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-4">
                            <label class="ef-label" for="total_amount">Total Amount</label>
                            <input id="total_amount" name="total_amount" type="number" step="0.01" min="0" class="ef-input" value="{{ old('total_amount', 0) }}" required>
                            @error('total_amount')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="advance_amount">Advance Amount</label>
                            <input id="advance_amount" name="advance_amount" type="number" step="0.01" min="0" class="ef-input" value="{{ old('advance_amount', 0) }}" required>
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

                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">5</span>
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
                        <span class="ef-label">Financial Preview</span>
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
                        <button type="button" class="ef-btn" id="useEstimateBtn">
                            Use Catering Estimate
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bookingForm');
    const fields = {
        customer: document.getElementById('customer_name'),
        mobile: document.getElementById('customer_mobile'),
        hall: document.getElementById('hall_id'),
        event: document.getElementById('event_type'),
        people: document.getElementById('number_of_people'),
        date: document.getElementById('booking_date'),
        start: document.getElementById('start_time'),
        end: document.getElementById('end_time'),
        mealPlan: document.getElementById('meal_plan_id'),
        total: document.getElementById('total_amount'),
        advance: document.getElementById('advance_amount'),
        paymentStatus: document.getElementById('payment_status'),
        notes: document.getElementById('notes'),
    };
    const meals = [
        document.getElementById('has_breakfast'),
        document.getElementById('has_lunch'),
        document.getElementById('has_dinner'),
    ];
    const mealLabels = {
        has_breakfast: 'Breakfast',
        has_lunch: 'Lunch',
        has_dinner: 'Dinner',
    };
    const draftKey = 'akshathay.booking.create.draft';
    const canRestoreDraft = @json(! session()->hasOldInput() && ! request()->hasAny(['date', 'hall_id']));
    const availability = document.getElementById('availability-panel');
    const availabilityTitle = document.getElementById('availabilityTitle');
    const availabilityCopy = document.getElementById('availabilityCopy');
    const nearbyBookings = document.getElementById('nearbyBookings');
    let availabilityTimer;

    const rupee = value => '₹' + Number(value || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const selectedText = select => select.options[select.selectedIndex]?.text?.split('·')[0]?.trim() || '';
    const selectedData = (select, key) => select.options[select.selectedIndex]?.dataset?.[key] || '';

    function formatDate(value) {
        if (!value) return 'Not selected';
        return new Date(value + 'T00:00:00').toLocaleDateString('en-IN', {
            weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
        });
    }

    function formatTime(value) {
        if (!value) return '';
        const [hour, minute] = value.split(':');
        return new Date(2000, 0, 1, hour, minute).toLocaleTimeString('en-IN', {
            hour: '2-digit', minute: '2-digit', hour12: true
        });
    }

    function selectedMeals() {
        return meals.filter(input => input.checked).map(input => mealLabels[input.id]);
    }

    function cateringEstimate() {
        const price = Number(fields.mealPlan.options[fields.mealPlan.selectedIndex]?.dataset?.price || 0);
        const guests = Number(fields.people.value || 0);
        return price * guests;
    }

    function updateSummary() {
        const name = fields.customer.value.trim();
        const event = selectedText(fields.event);
        const hall = selectedData(fields.hall, 'name') || selectedText(fields.hall);
        const guests = Number(fields.people.value || 0);
        const start = formatTime(fields.start.value);
        const end = formatTime(fields.end.value);
        const mealList = selectedMeals();
        const total = Number(fields.total.value || 0);
        const advance = Number(fields.advance.value || 0);
        const balance = Math.max(0, total - advance);
        const estimate = cateringEstimate();

        document.getElementById('summaryName').textContent = name || 'New event booking';
        document.getElementById('summaryMeta').textContent = [event || 'Event type', hall || 'Hall', guests ? `${guests} guests` : null].filter(Boolean).join(' · ');
        document.getElementById('summaryHall').textContent = hall || 'Not selected';
        document.getElementById('summaryDate').textContent = formatDate(fields.date.value);
        document.getElementById('summaryTime').textContent = start && end ? `${start} - ${end}` : 'Not selected';
        document.getElementById('summaryGuests').textContent = guests ? guests.toLocaleString('en-IN') : '0';
        document.getElementById('summaryMeals').textContent = mealList.length ? mealList.join(', ') : 'None';
        document.getElementById('summaryTotal').textContent = rupee(total);
        document.getElementById('summaryAdvance').textContent = rupee(advance);
        document.getElementById('summaryBalance').textContent = rupee(balance);
        document.getElementById('mobileBalance').textContent = rupee(balance);

        document.getElementById('mealEstimateText').textContent = estimate
            ? `Catering estimate: ${rupee(estimate)} based on ${guests.toLocaleString('en-IN')} guests.`
            : 'Select a meal plan and guest count to preview catering value.';
    }

    function setAvailability(state, title, copy) {
        availability.dataset.state = state;
        availabilityTitle.textContent = title;
        availabilityCopy.textContent = copy;
    }

    async function checkAvailability() {
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
                    hall_id: fields.hall.value,
                    booking_date: fields.date.value,
                    start_time: fields.start.value,
                    end_time: fields.end.value,
                });
                const checkResponse = await fetch(@json(route('hall.bookings.check-availability')) + '?' + checkParams);
                const checkData = await checkResponse.json();

                const dayParams = new URLSearchParams({
                    start: fields.date.value,
                    end: fields.date.value,
                    hall_id: fields.hall.value,
                });
                const dayResponse = await fetch(@json(route('hall.bookings.calendar-events')) + '?' + dayParams);
                const dayEvents = await dayResponse.json();

                const density = dayEvents.length;
                if (!checkData.available) {
                    const conflicts = checkData.conflicts.map(item => `${item.customer} · ${item.start}-${item.end}`).join(', ');
                    setAvailability('blocked', 'Time conflict detected', conflicts || 'This slot overlaps an existing booking.');
                } else if (density > 0) {
                    setAvailability('partial', 'Available with nearby bookings', `${density} booking${density === 1 ? '' : 's'} already scheduled for this hall on this date.`);
                } else {
                    setAvailability('available', 'Hall available', 'No existing bookings found for this hall on the selected date.');
                }

                if (dayEvents.length) {
                    nearbyBookings.innerHTML = dayEvents.slice(0, 4).map(event => {
                        const p = event.extendedProps || {};
                        return `<div class="ef-nearby-item"><span>${p.start_time || ''} · ${p.customer || event.title}</span><strong>${p.people || 0} guests</strong></div>`;
                    }).join('');
                    nearbyBookings.classList.add('show');
                } else {
                    nearbyBookings.classList.remove('show');
                    nearbyBookings.innerHTML = '';
                }
            } catch (error) {
                setAvailability('idle', 'Availability check unavailable', 'Could not reach the availability service. You can still submit after manual review.');
            }
        }, 450);
    }

    function collectDraft() {
        const data = new FormData(form);
        return Object.fromEntries(data.entries());
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
                const elements = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);
                elements.forEach(element => {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = element.value === value;
                    } else {
                        element.value = value;
                    }
                });
            });
        } catch {}
    }

    Object.values(fields).forEach(field => {
        field.addEventListener('input', updateSummary);
        field.addEventListener('change', () => {
            updateSummary();
            if (['hall_id', 'booking_date', 'start_time', 'end_time'].includes(field.id)) checkAvailability();
        });
    });
    meals.forEach(meal => meal.addEventListener('change', updateSummary));
    document.querySelectorAll('input[name="status"], input[name="payment_method"]').forEach(input => input.addEventListener('change', updateSummary));
    document.getElementById('saveDraftBtn').addEventListener('click', saveDraft);
    document.getElementById('saveDraftBtnBottom').addEventListener('click', saveDraft);
    document.getElementById('useEstimateBtn').addEventListener('click', function () {
        const estimate = cateringEstimate();
        if (estimate > 0) {
            fields.total.value = estimate.toFixed(2);
            updateSummary();
        }
    });

    restoreDraft();
    updateSummary();
    checkAvailability();
});
</script>
@endpush
</x-admin-layout>
