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
    $bDate    = $b->booking_date->toDateString();
    $waUrl    = 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile ?? '');
    $evType   = $evTypes[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type ?? ''));
    $meals    = collect(['B' => $b->has_breakfast, 'L' => $b->has_lunch, 'D' => $b->has_dinner])
                    ->filter()->keys()->implode('·');

    if ($bDate === $today) {
        $dateLabel = 'Today';
    } elseif ($bDate === now()->addDay()->toDateString()) {
        $dateLabel = 'Tomorrow';
    } else {
        $dateLabel = $b->booking_date->format('d M');
    }
    $timeLabel = \Carbon\Carbon::parse($b->start_time)->format('h:i A');

    // Booking status pill
    if ($b->status === 'cancelled') {
        $stLabel = 'Cancelled'; $stCls = '--cancelled';
    } elseif ($b->status === 'completed') {
        $stLabel = 'Completed'; $stCls = '--completed';
    } elseif ($b->status === 'confirmed') {
        $stLabel = 'Confirmed'; $stCls = '--confirmed';
    } else {
        $stLabel = ucfirst($b->status); $stCls = '--pending';
    }

    // Payment pill (only when not cancelled/employee)
    if ($b->payment_status === 'paid') {
        $pyLabel = 'Paid'; $pyCls = '--paid';
    } elseif ($b->payment_status === 'partial') {
        $pyLabel = 'Partial'; $pyCls = '--partial';
    } else {
        $pyLabel = 'Pending Pay'; $pyCls = '--unpaid';
    }
@endphp

<div class="hb-card --{{ $b->status }}" data-id="{{ $b->id }}">

    <div class="hb-card-body">

        {{-- Top: name + amount --}}
        <div class="hb-card-top">
            <div class="hb-name" title="{{ $b->customer_name }}">{{ $b->customer_name }}</div>
            @if(!$isEmployee)
            <div class="hb-amt">₹{{ number_format($b->total_amount) }}</div>
            @endif
        </div>

        {{-- Sub: event type + status pills --}}
        <div class="hb-card-sub">
            <span class="hb-evt-tag">{{ $evType }}</span>
            <div class="hb-pills">
                <span class="hb-pill {{ $stCls }}">{{ $stLabel }}</span>
                @if(!$isEmployee && $b->status !== 'cancelled')
                <span class="hb-pill {{ $pyCls }}">{{ $pyLabel }}</span>
                @endif
            </div>
        </div>

        {{-- Meta row --}}
        <div class="hb-meta">
            <span class="hb-mi">
                <i class="bi {{ $b->isFoodOnly() ? 'bi-cup-hot' : 'bi-building' }}"></i>
                {{ Str::limit($b->location_label, 16) }}
            </span>
            <span class="hb-mi">
                <i class="bi bi-calendar3"></i>{{ $dateLabel }}
            </span>
            <span class="hb-mi">
                <i class="bi bi-people"></i>{{ number_format($b->number_of_people) }}
            </span>
            <span class="hb-mi">
                <i class="bi bi-clock"></i>{{ $timeLabel }}
            </span>
            @if($meals)
            <span class="hb-mi">
                <i class="bi bi-egg-fried"></i>{{ $meals }}
            </span>
            @endif
        </div>

    </div>

    {{-- Footer actions --}}
    <div class="hb-card-footer">
        <a href="{{ route('hall.bookings.show', $b) }}" class="hb-act --view">
            View Details
        </a>
        <a href="{{ $waUrl }}" class="hb-act --wa" target="_blank" rel="noopener" title="WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </a>
        <div class="dropdown">
            <button class="hb-act --more" type="button"
                    data-bs-toggle="dropdown"
                    data-bs-offset="0,4"
                    aria-expanded="false"
                    aria-label="More actions">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end hb-dropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}">
                        <i class="bi bi-eye me-2"></i>View Booking
                    </a>
                </li>
                @if(!$isEmployee)
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.edit', $b) }}">
                        <i class="bi bi-pencil me-2"></i>Edit Booking
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}#record-payment">
                        <i class="bi bi-cash-coin me-2"></i>Record Payment
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.invoice', $b) }}" target="_blank">
                        <i class="bi bi-receipt me-2"></i>View Invoice
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                        <i class="bi bi-file-pdf me-2"></i>Download PDF
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
@endforeach
