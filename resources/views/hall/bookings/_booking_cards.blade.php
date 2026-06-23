{{--
  Booking card partial — used by index.blade.php (initial render) and AJAX scroll.
  Requires: $bookings (LengthAwarePaginator|Collection), $today (Y-m-d string)
  Optional: $isEmployee (bool, default false)
--}}
@php
$isEmployee = $isEmployee ?? false;
$evTypes    = \App\Models\HallBooking::eventTypes();
$payLabels  = \App\Models\HallBooking::paymentStatuses();
@endphp

@foreach($bookings as $b)
@php
    $bDate  = $b->booking_date->toDateString();
    $waUrl  = 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile ?? '');
    $evType = $evTypes[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type ?? ''));
    $meals  = collect([
        'B' => $b->has_breakfast,
        'L' => $b->has_lunch,
        'D' => $b->has_dinner,
    ])->filter()->keys()->implode('·');
    $payLabel = $payLabels[$b->payment_status] ?? ucfirst($b->payment_status ?? '');
    if ($bDate === $today) {
        $dateLabel = 'Today';
    } elseif ($bDate === now()->addDay()->toDateString()) {
        $dateLabel = 'Tomorrow';
    } else {
        $dateLabel = $b->booking_date->format('d M Y');
    }
@endphp

<div class="hb-card --{{ $b->status }}">
    <div class="hb-card-inner">

        {{-- Row 1: Name + badges --}}
        <div class="hb-card-r1">
            <div class="hb-card-name" title="{{ $b->customer_name }}">{{ $b->customer_name }}</div>
            <div class="hb-card-badges">
                @if($b->status !== 'confirmed')
                    <span class="hb-status-badge --{{ $b->status }}">{{ $b->status }}</span>
                @endif
                <span class="hb-evt-tag" title="{{ $evType }}">{{ $evType }}</span>
            </div>
        </div>

        {{-- Row 2: Amount + payment chip --}}
        @if(!$isEmployee)
        <div class="hb-card-r2">
            <div class="hb-amount">₹{{ number_format($b->total_amount) }}</div>
            <span class="hb-pay-chip --{{ $b->payment_status }}">{{ $payLabel }}</span>
        </div>
        @else
        <div class="hb-card-r2">
            <span class="hb-pay-chip --{{ $b->payment_status }}">{{ $payLabel }}</span>
        </div>
        @endif

        {{-- Row 3: Meta --}}
        <div class="hb-card-meta">
            <span class="hb-meta-item" title="{{ $b->location_label }}">
                <i class="bi {{ $b->isFoodOnly() ? 'bi-cup-hot' : 'bi-building' }}"></i>
                {{ Str::limit($b->location_label, 18) }}
            </span>
            <span class="hb-meta-item">
                <i class="bi bi-calendar3"></i> {{ $dateLabel }}
            </span>
            <span class="hb-meta-item">
                <i class="bi bi-people"></i> {{ number_format($b->number_of_people) }}
            </span>
            @if($meals)
                <span class="hb-meta-item">
                    <i class="bi bi-egg-fried"></i> {{ $meals }}
                </span>
            @endif
            <span class="hb-meta-item">
                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}
            </span>
        </div>

        {{-- Row 4: Actions --}}
        <div class="hb-card-foot">
            <a href="{{ route('hall.bookings.show', $b) }}" class="hb-act --view">
                <i class="bi bi-eye"></i> View
            </a>
            @if(!$isEmployee)
            <a href="{{ route('hall.bookings.invoice.pdf', $b) }}" class="hb-act --ico" title="Download PDF">
                <i class="bi bi-file-pdf"></i>
            </a>
            @endif
            <a href="{{ $waUrl }}" class="hb-act --wa" target="_blank" rel="noopener" title="WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
            <div class="dropdown">
                <button class="hb-more-btn" type="button"
                        data-bs-toggle="dropdown"
                        data-bs-offset="0,4"
                        aria-expanded="false"
                        aria-label="More actions">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}">
                            <i class="bi bi-eye me-2" style="color:var(--hb-faint)"></i>View Booking
                        </a>
                    </li>
                    @if(!$isEmployee)
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.edit', $b) }}">
                            <i class="bi bi-pencil me-2" style="color:var(--hb-faint)"></i>Edit Booking
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}#record-payment">
                            <i class="bi bi-cash-coin me-2" style="color:var(--hb-faint)"></i>Record Payment
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.invoice', $b) }}" target="_blank">
                            <i class="bi bi-receipt me-2" style="color:var(--hb-faint)"></i>View Invoice
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                            <i class="bi bi-file-pdf me-2" style="color:var(--hb-faint)"></i>Download PDF
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="dropdown-item" href="{{ $waUrl }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp me-2" style="color:#25d366"></i>WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
@endforeach
