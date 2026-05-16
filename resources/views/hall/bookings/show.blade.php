<x-admin-layout title="Booking #{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}">
@php
    $totalPaid = $booking->total_paid;
    $balance = max(0, $booking->balance_amount);
    $paidPct = (float) $booking->total_amount > 0 ? min(100, round(($totalPaid / (float) $booking->total_amount) * 100)) : 0;
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $eventLabel = $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type);
    $start = \Carbon\Carbon::parse($booking->start_time);
    $end = \Carbon\Carbon::parse($booking->end_time);
    $duration = $start->diff($end);
    $durationLabel = trim(($duration->h ? "{$duration->h}h " : '') . ($duration->i ? "{$duration->i}m" : ''));
    $bookingRef = '#' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
    $statusTone = ['confirmed' => 'emerald', 'completed' => 'bluegray', 'cancelled' => 'danger'][$booking->status] ?? 'neutral';
    $paymentTone = ['pending' => 'gold', 'partial' => 'bluegray', 'paid' => 'emerald'][$booking->payment_status] ?? 'neutral';
    $mealSelections = collect([
        'Breakfast' => $booking->has_breakfast,
        'Lunch' => $booking->has_lunch,
        'Dinner' => $booking->has_dinner,
    ])->filter();
    $cleanMobile = preg_replace('/\D/', '', $booking->customer_mobile);
    $waMessage = "Akshathay Mini Hall booking confirmation\n\n"
        . "Booking: {$bookingRef}\n"
        . "Customer: {$booking->customer_name}\n"
        . "Event: {$eventLabel}\n"
        . "Date: {$booking->booking_date->format('d M Y')}\n"
        . "Time: {$start->format('h:i A')} - {$end->format('h:i A')}\n"
        . "Hall: {$booking->hall->name}\n"
        . "Guests: {$booking->number_of_people}\n\n"
        . "Total: Rs. " . number_format($booking->total_amount, 2) . "\n"
        . "Paid: Rs. " . number_format($totalPaid, 2) . "\n"
        . "Balance: Rs. " . number_format($balance, 2) . "\n\n"
        . route('hall.bookings.invoice', $booking);
    $waUrl = 'https://wa.me/?text=' . rawurlencode($waMessage);
@endphp

<div class="ef-page">
    <section class="ef-hero">
        <div class="ef-hero-main">
            <a href="{{ route('hall.bookings.index') }}" class="ef-back">
                <i class="bi bi-arrow-left"></i>
                Hall bookings
            </a>

            <div class="ef-eyebrow">Booking {{ $bookingRef }}</div>
            <h1 class="ef-title">{{ $booking->customer_name }}</h1>

            <div class="ef-hero-meta">
                <span>{{ $eventLabel }}</span>
                <span>{{ $booking->booking_date->format('D, d M Y') }}</span>
                <span>{{ $start->format('h:i A') }} - {{ $end->format('h:i A') }}</span>
                <span>{{ $booking->hall->name }}</span>
                <span>{{ number_format($booking->number_of_people) }} guests</span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <x-premium.chip :tone="$statusTone">{{ \App\Models\HallBooking::statuses()[$booking->status] ?? Str::headline($booking->status) }}</x-premium.chip>
                <x-premium.chip :tone="$paymentTone">{{ \App\Models\HallBooking::paymentStatuses()[$booking->payment_status] ?? Str::headline($booking->payment_status) }} payment</x-premium.chip>
            </div>
        </div>

        <aside class="ef-hero-finance">
            <div>
                <div class="ef-amount-label">Total booking value</div>
                <div class="ef-amount">₹{{ number_format($booking->total_amount, 0) }}</div>
                <div class="ef-balance">
                    @if($balance > 0)
                        ₹{{ number_format($balance, 0) }} balance due
                    @else
                        Fully settled
                    @endif
                </div>
            </div>

            <div class="ef-action-row">
                <a href="{{ route('hall.bookings.invoice', $booking) }}?print=1" target="_blank" class="ef-btn">
                    <i class="bi bi-printer"></i> Print Invoice
                </a>
                <a href="{{ route('hall.bookings.invoice.pdf', $booking) }}" class="ef-btn">
                    <i class="bi bi-file-earmark-arrow-down"></i> PDF
                </a>
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="ef-btn">
                    <i class="bi bi-whatsapp"></i> Share
                </a>
                <a href="{{ route('hall.bookings.edit', $booking) }}" class="ef-btn">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                @if($balance > 0 && !$booking->isCancelled())
                    <a href="#record-payment" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-lg"></i> Record Payment
                    </a>
                @endif
            </div>
        </aside>
    </section>

    <div class="ef-metric-row">
        <div class="ef-metric">
            <span class="ef-label">Total</span>
            <div class="ef-metric-value">₹{{ number_format($booking->total_amount, 0) }}</div>
            <div class="ef-metric-caption">booking value</div>
        </div>
        <div class="ef-metric">
            <span class="ef-label">Collected</span>
            <div class="ef-metric-value">₹{{ number_format($totalPaid, 0) }}</div>
            <div class="ef-metric-caption">{{ $paidPct }}% received</div>
        </div>
        <div class="ef-metric">
            <span class="ef-label">Balance</span>
            <div class="ef-metric-value">₹{{ number_format($balance, 0) }}</div>
            <div class="ef-metric-caption">{{ $balance > 0 ? 'pending collection' : 'nothing pending' }}</div>
        </div>
        <div class="ef-metric">
            <span class="ef-label">Guests</span>
            <div class="ef-metric-value">{{ number_format($booking->number_of_people) }}</div>
            <div class="ef-metric-caption">{{ $durationLabel ?: 'event duration set' }}</div>
        </div>
    </div>

    <div class="ef-grid-main">
        <div class="d-flex flex-column gap-4">
            <x-premium.card title="Booking Overview">
                <div class="ef-info-grid">
                    <x-premium.field label="Customer" class="ef-value ef-value-strong">{{ $booking->customer_name }}</x-premium.field>
                    <x-premium.field label="Event Type" class="ef-value ef-value-strong">{{ $eventLabel }}</x-premium.field>
                    <x-premium.field label="Primary Mobile">
                        <a href="tel:{{ $booking->customer_mobile }}" class="text-decoration-none text-reset">{{ $booking->customer_mobile }}</a>
                    </x-premium.field>
                    <x-premium.field label="Alternate Mobile">{{ $booking->customer_alt_mobile ?: 'Not provided' }}</x-premium.field>
                    <x-premium.field label="Booking Reference">{{ $bookingRef }}</x-premium.field>
                    <x-premium.field label="Created By">{{ $booking->creator?->name ?? 'System' }}</x-premium.field>
                </div>
            </x-premium.card>

            <x-premium.card title="Event Details">
                <div class="ef-info-grid">
                    <x-premium.field label="Venue" class="ef-value ef-value-strong">{{ $booking->hall->name }}</x-premium.field>
                    <x-premium.field label="Date">{{ $booking->booking_date->format('l, d M Y') }}</x-premium.field>
                    <x-premium.field label="Time">{{ $start->format('h:i A') }} - {{ $end->format('h:i A') }}</x-premium.field>
                    <x-premium.field label="Duration">{{ $durationLabel ?: 'Not calculated' }}</x-premium.field>
                    <x-premium.field label="Booking Status">
                        <x-premium.chip :tone="$statusTone">{{ \App\Models\HallBooking::statuses()[$booking->status] ?? Str::headline($booking->status) }}</x-premium.chip>
                    </x-premium.field>
                    <x-premium.field label="Recorded On">{{ $booking->created_at->format('d M Y, h:i A') }}</x-premium.field>
                </div>
            </x-premium.card>

            <x-premium.card title="Meal Plan">
                <div class="ef-info-grid mb-4">
                    <x-premium.field label="Guest Count" class="ef-value ef-value-strong">{{ number_format($booking->number_of_people) }} guests</x-premium.field>
                    <x-premium.field label="Plan">{{ $booking->mealPlan?->name ?? 'No meal plan selected' }}</x-premium.field>
                    @if($booking->mealPlan?->price_per_person)
                        <x-premium.field label="Rate Per Person">₹{{ number_format($booking->mealPlan->price_per_person, 2) }}</x-premium.field>
                    @endif
                </div>

                <span class="ef-label">Meal Selections</span>
                <div class="ef-meal-list">
                    @forelse($mealSelections as $meal => $enabled)
                        <span class="ef-meal-chip">{{ $meal }}</span>
                    @empty
                        <span class="ef-shell-note">No meal selections have been attached to this booking.</span>
                    @endforelse
                </div>

                @if($booking->mealPlan?->description)
                    <div class="border-top mt-4 pt-4 ef-shell-note">{{ $booking->mealPlan->description }}</div>
                @endif
            </x-premium.card>

            <x-premium.card title="Payment History" :aside="$booking->payments->count() . ' ' . Str::plural('transaction', $booking->payments->count())">
                @forelse($booking->payments->sortByDesc('paid_at') as $payment)
                    <div class="ef-timeline-row">
                        <div class="ef-txn-date">
                            {{ $payment->paid_at->format('d M') }}<br>
                            {{ $payment->paid_at->format('Y') }}
                        </div>
                        <div>
                            <div class="ef-txn-title">{{ \App\Models\BookingPayment::types()[$payment->payment_type] ?? Str::headline($payment->payment_type) }}</div>
                            <div class="ef-txn-meta">
                                {{ \App\Models\BookingPayment::methods()[$payment->payment_method] ?? Str::headline($payment->payment_method) }}
                                @if($payment->reference_number)
                                    · Ref {{ $payment->reference_number }}
                                @endif
                                · Recorded by {{ $payment->recorder?->name ?? 'System' }}
                                @if($payment->notes)
                                    · {{ $payment->notes }}
                                @endif
                            </div>
                        </div>
                        <div class="ef-txn-amount">₹{{ number_format($payment->amount, 2) }}</div>
                    </div>
                @empty
                    <p class="ef-shell-note mb-0">No payments have been recorded yet.</p>
                @endforelse
            </x-premium.card>

            <x-premium.card title="Notes & Activity">
                @if($booking->notes)
                    <p class="ef-shell-note mb-0">{{ $booking->notes }}</p>
                @else
                    <p class="ef-shell-note mb-0">No internal notes added for this booking.</p>
                @endif
            </x-premium.card>
        </div>

        <aside class="d-flex flex-column gap-4">
            <x-premium.card title="Financial Summary">
                <x-premium.chip :tone="$paymentTone">{{ \App\Models\HallBooking::paymentStatuses()[$booking->payment_status] ?? Str::headline($booking->payment_status) }} payment</x-premium.chip>

                <div class="ef-progress-meta">
                    <span>{{ $paidPct }}% collected</span>
                    <span>₹{{ number_format($totalPaid, 0) }} / ₹{{ number_format($booking->total_amount, 0) }}</span>
                </div>
                <div class="ef-progress">
                    <div class="ef-progress-bar" style="width: {{ $paidPct }}%"></div>
                </div>

                <div class="mt-4">
                    <div class="ef-fin-row">
                        <span class="ef-muted">Total Booking Value</span>
                        <strong>₹{{ number_format($booking->total_amount, 2) }}</strong>
                    </div>
                    <div class="ef-fin-row">
                        <span class="ef-muted">Advance Amount</span>
                        <strong>₹{{ number_format($booking->advance_amount, 2) }}</strong>
                    </div>
                    <div class="ef-fin-row">
                        <span class="ef-muted">Total Paid</span>
                        <strong>₹{{ number_format($totalPaid, 2) }}</strong>
                    </div>
                    <div class="ef-fin-row ef-fin-total">
                        <span class="ef-value-strong">Balance Due</span>
                        <strong>₹{{ number_format($balance, 2) }}</strong>
                    </div>
                </div>

                <div class="ef-balance-panel" data-clear="{{ $balance <= 0 ? 'true' : 'false' }}">
                    <span class="ef-label">{{ $balance > 0 ? 'Collection needed' : 'Payment complete' }}</span>
                    <div class="ef-value-strong">
                        {{ $balance > 0 ? '₹' . number_format($balance, 2) . ' remaining' : 'No balance due' }}
                    </div>
                </div>
            </x-premium.card>

            @if($balance > 0 && !$booking->isCancelled())
                <x-premium.card title="Record Payment" id="record-payment">
                    <form method="POST" action="{{ route('hall.bookings.payments.add', $booking) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="ef-label" for="amount">Amount</label>
                            <input id="amount" type="number" name="amount" class="ef-form-control" step="0.01" min="0.01" value="{{ old('amount', $balance) }}" required>
                            @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="ef-label" for="payment_method">Method</label>
                                <select id="payment_method" name="payment_method" class="ef-form-control" required>
                                    @foreach(\App\Models\BookingPayment::methods() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="ef-label" for="payment_type">Type</label>
                                <select id="payment_type" name="payment_type" class="ef-form-control" required>
                                    @foreach(\App\Models\BookingPayment::types() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('payment_type', 'balance') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="ef-label" for="paid_at">Payment Date</label>
                            <input id="paid_at" type="date" name="paid_at" class="ef-form-control" value="{{ old('paid_at', today()->toDateString()) }}" required>
                        </div>

                        <div class="mt-3">
                            <label class="ef-label" for="reference_number">Reference / UTR</label>
                            <input id="reference_number" type="text" name="reference_number" class="ef-form-control" value="{{ old('reference_number') }}" placeholder="Optional">
                        </div>

                        <div class="mt-3">
                            <label class="ef-label" for="notes">Notes</label>
                            <input id="notes" type="text" name="notes" class="ef-form-control" value="{{ old('notes') }}" placeholder="Optional">
                        </div>

                        <button type="submit" class="ef-btn ef-btn-dark w-100 mt-4" data-loading-text="Recording...">
                            <i class="bi bi-check2"></i> Record Payment
                        </button>
                    </form>
                </x-premium.card>
            @endif

            <x-premium.card title="Contact Actions">
                <div class="ef-contact-row">
                    <div>
                        <div class="ef-value-strong">{{ $booking->customer_name }}</div>
                        <div class="ef-muted small">{{ $booking->customer_mobile }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="ef-btn ef-btn-icon" href="tel:{{ $booking->customer_mobile }}" aria-label="Call customer"><i class="bi bi-telephone"></i></a>
                        <a class="ef-btn ef-btn-icon" href="https://wa.me/91{{ $cleanMobile }}" target="_blank" rel="noopener" aria-label="WhatsApp customer"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                @if($booking->customer_alt_mobile)
                    <div class="ef-contact-row">
                        <div>
                            <div class="ef-value-strong">Alternate contact</div>
                            <div class="ef-muted small">{{ $booking->customer_alt_mobile }}</div>
                        </div>
                        <a class="ef-btn ef-btn-icon" href="tel:{{ $booking->customer_alt_mobile }}" aria-label="Call alternate contact"><i class="bi bi-telephone"></i></a>
                    </div>
                @endif
            </x-premium.card>

            <x-premium.card title="Booking Actions">
                <div class="d-grid gap-2">
                    <a href="{{ route('hall.bookings.invoice', $booking) }}?print=1" target="_blank" class="ef-btn justify-content-start"><i class="bi bi-printer"></i> Print invoice</a>
                    <a href="{{ route('hall.bookings.invoice.pdf', $booking) }}" class="ef-btn justify-content-start"><i class="bi bi-file-earmark-arrow-down"></i> Download PDF</a>
                    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="ef-btn justify-content-start"><i class="bi bi-whatsapp"></i> Share confirmation</a>
                    <a href="{{ route('hall.bookings.edit', $booking) }}" class="ef-btn justify-content-start"><i class="bi bi-pencil"></i> Edit booking</a>
                    <form method="POST" action="{{ route('hall.bookings.destroy', $booking) }}" onsubmit="return confirm('Permanently delete this booking?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ef-btn justify-content-start w-100" data-no-loading>
                            <i class="bi bi-trash3"></i> Delete booking
                        </button>
                    </form>
                </div>
            </x-premium.card>
        </aside>
    </div>
</div>

<div class="ef-mobile-actions">
    <a href="{{ route('hall.bookings.invoice', $booking) }}?print=1" target="_blank" class="ef-btn"><i class="bi bi-printer"></i> Print</a>
    <a href="{{ route('hall.bookings.invoice.pdf', $booking) }}" class="ef-btn"><i class="bi bi-file-earmark-arrow-down"></i> PDF</a>
    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="ef-btn"><i class="bi bi-whatsapp"></i> Share</a>
    <a href="{{ route('hall.bookings.edit', $booking) }}" class="ef-btn"><i class="bi bi-pencil"></i> Edit</a>
</div>
</x-admin-layout>
