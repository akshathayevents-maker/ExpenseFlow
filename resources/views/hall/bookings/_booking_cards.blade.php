{{--
  Booking card partial — used by index.blade.php (initial render) and AJAX scroll.
  Requires: $bookings (LengthAwarePaginator), $today (Y-m-d string)
--}}
@php
$avatarTones = ['#7a5a28','#3e6a5a','#4a5e8a','#6a4e7a','#5a6840'];
@endphp

@foreach($bookings as $b)
@php
    $bDate  = $b->booking_date->toDateString();
    $group  = match(true) { $bDate === $today => 'today', $bDate > $today => 'upcoming', default => 'past' };
    $tone   = $avatarTones[ord(strtoupper($b->customer_name[0] ?? 'A')) % count($avatarTones)];
    $meals  = collect(['Breakfast' => $b->has_breakfast, 'Lunch' => $b->has_lunch, 'Dinner' => $b->has_dinner])->filter()->keys();
    $waUrl  = 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile ?? '');
    $evType = \App\Models\HallBooking::eventTypes()[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type));
@endphp

<div class="ef-bk-card --{{ $b->status }}" data-group="{{ $group }}" data-date="{{ $bDate }}">

    {{-- Row 1: Avatar · Name · Event type · Status --}}
    <div class="ef-bk-r1">
        <div class="ef-bk-av" style="background:{{ $tone }}">
            {{ strtoupper(mb_substr($b->customer_name, 0, 1)) }}
        </div>
        <div class="ef-bk-r1-text">
            <div class="ef-bk-name">{{ $b->customer_name }}</div>
            <div class="ef-bk-evtype">{{ $evType }}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
            <span class="ef-bk-badge --{{ $b->status }}">{{ $b->status }}</span>
            <x-booking-type-badge :type="$b->booking_type" size="xs" />
        </div>
    </div>

    {{-- Amount + payment status --}}
    <div class="ef-bk-amount-row">
        <div class="ef-bk-amt">₹{{ number_format($b->total_amount) }}</div>
        <span class="ef-bk-pchip --{{ $b->payment_status }}">
            {{ \App\Models\HallBooking::paymentStatuses()[$b->payment_status] ?? $b->payment_status }}
        </span>
    </div>

    {{-- Meta: Hall · Date · Time · Guests --}}
    <div class="ef-bk-rows">
        <div class="ef-bk-mrow">
            <span class="ef-bk-mitem">
                <i class="bi {{ $b->isFoodOnly() ? 'bi-cup-hot' : 'bi-building' }}"></i>
                {{ $b->location_label }}
            </span>
            <span class="ef-bk-mdot"></span>
            <span class="ef-bk-mitem"><i class="bi bi-calendar3"></i> {{ $b->booking_date->format('d M') }}</span>
        </div>
        <div class="ef-bk-mrow">
            <span class="ef-bk-mitem">
                <i class="bi bi-clock"></i>
                {{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}
            </span>
            <span class="ef-bk-mdot"></span>
            <span class="ef-bk-mitem"><i class="bi bi-people"></i> {{ number_format($b->number_of_people) }}</span>
            @if($b->mealPlan || $meals->isNotEmpty())
                <span class="ef-bk-mdot"></span>
                <span class="ef-bk-mitem"><i class="bi bi-egg-fried"></i> Catering</span>
            @endif
        </div>
    </div>

    {{-- Meal tags --}}
    @if($meals->isNotEmpty())
        <div class="ef-bk-meal-tags">
            @foreach($meals as $m)
                <span class="ef-bk-meal-tag">{{ $m }}</span>
            @endforeach
        </div>
    @endif

    {{-- Footer: actions --}}
    <div class="ef-bk-foot">
        <div class="ef-bk-acts">
            <a href="{{ route('hall.bookings.show', $b) }}" class="ef-bk-act --primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('hall.bookings.invoice', $b) }}" class="ef-bk-act --ico"
               target="_blank" title="Invoice">
                <i class="bi bi-receipt"></i>
            </a>
            <a href="{{ $waUrl }}" class="ef-bk-act --ico --wa" target="_blank" title="WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
            <div class="dropdown">
                <button class="ef-bk-more" type="button"
                        data-bs-toggle="dropdown"
                        data-bs-offset="0,4"
                        aria-expanded="false"
                        aria-label="More actions">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}">
                            <i class="bi bi-eye me-2" style="color:var(--bk-faint)"></i>View Booking
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.edit', $b) }}">
                            <i class="bi bi-pencil me-2" style="color:var(--bk-faint)"></i>Edit Booking
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}#record-payment">
                            <i class="bi bi-cash-coin me-2" style="color:var(--bk-faint)"></i>Record Payment
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.invoice', $b) }}" target="_blank">
                            <i class="bi bi-receipt me-2" style="color:var(--bk-faint)"></i>View Invoice
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                            <i class="bi bi-file-pdf me-2" style="color:var(--bk-faint)"></i>Download PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ $waUrl }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp me-2" style="color:#25d366"></i>Share via WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endforeach
