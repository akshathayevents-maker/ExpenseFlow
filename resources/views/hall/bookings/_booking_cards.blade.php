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
        $stLabel = 'Cancelled'; $stCls = '--cancelled'; $stIcon = 'bi-x-circle';
    } elseif ($b->status === 'completed') {
        $stLabel = 'Completed'; $stCls = '--completed'; $stIcon = 'bi-check2-all';
    } elseif ($b->status === 'confirmed') {
        $stLabel = 'Confirmed'; $stCls = '--confirmed'; $stIcon = 'bi-check-circle';
    } else {
        $stLabel = ucfirst($b->status); $stCls = '--pending'; $stIcon = 'bi-clock';
    }

    // Payment pill (only when not cancelled/employee)
    if ($b->payment_status === 'paid') {
        $pyLabel = 'Paid'; $pyCls = '--paid'; $pyIcon = 'bi-check-circle-fill';
    } elseif ($b->payment_status === 'partial') {
        $pyLabel = 'Partial'; $pyCls = '--partial'; $pyIcon = 'bi-pie-chart';
    } else {
        $pyLabel = 'Pending Pay'; $pyCls = '--unpaid'; $pyIcon = 'bi-hourglass-split';
    }
@endphp

<article class="hb-card --{{ $b->status }}" data-id="{{ $b->id }}">

    <div class="hb-card-body">

        {{-- Primary: name + amount --}}
        <div class="hb-card-top">
            <div class="hb-title">
                <div class="hb-name" title="{{ $b->customer_name }}">{{ $b->customer_name }}</div>
                <div class="hb-evt">{{ $evType }}</div>
            </div>
            @if(!$isEmployee)
            <div class="hb-amt">₹{{ number_format($b->total_amount) }}</div>
            @endif
        </div>

        {{-- Status: icon + label so state never relies on colour alone --}}
        <div class="hb-badges">
            <span class="hb-badge {{ $stCls }}"><i class="bi {{ $stIcon }}"></i>{{ $stLabel }}</span>
            @if(!$isEmployee && $b->status !== 'cancelled')
            <span class="hb-badge {{ $pyCls }}"><i class="bi {{ $pyIcon }}"></i>{{ $pyLabel }}</span>
            @endif
        </div>

        {{-- Secondary: metadata --}}
        <div class="hb-meta">
            <div class="hb-mi --wide" title="{{ $b->location_label }}">
                <i class="bi {{ $b->isFoodOnly() ? 'bi-cup-hot' : 'bi-building' }}"></i>
                <span>{{ $b->location_label }}</span>
            </div>
            <div class="hb-mi">
                <i class="bi bi-calendar3"></i><span>{{ $dateLabel }}</span>
            </div>
            <div class="hb-mi">
                <i class="bi bi-clock"></i><span>{{ $timeLabel }}</span>
            </div>
            <div class="hb-mi">
                <i class="bi bi-people"></i><span>{{ number_format($b->number_of_people) }} guests</span>
            </div>
            @if($meals)
            <div class="hb-mi">
                <i class="bi bi-egg-fried"></i><span>{{ $meals }}</span>
            </div>
            @endif
        </div>

    </div>

    {{-- Actions: primary / secondary / tertiary --}}
    <div class="hb-card-foot">
        <a href="{{ route('hall.bookings.show', $b) }}" class="hb-act --view">
            View Details
        </a>
        <a href="{{ $waUrl }}" class="hb-act --wa" target="_blank" rel="noopener" title="WhatsApp" aria-label="WhatsApp {{ $b->customer_name }}">
            <i class="bi bi-whatsapp"></i>
        </a>
        <div class="dropdown">
            <button class="hb-act --more" type="button"
                    data-bs-toggle="dropdown"
                    data-bs-offset="0,4"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="More actions for {{ $b->customer_name }}">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end hb-dropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}">
                        <i class="bi bi-eye"></i>View Booking
                    </a>
                </li>
                @if(!$isEmployee)
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.edit', $b) }}">
                        <i class="bi bi-pencil"></i>Edit Booking
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.show', $b) }}#record-payment">
                        <i class="bi bi-cash-coin"></i>Record Payment
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.invoice', $b) }}" target="_blank">
                        <i class="bi bi-receipt"></i>View Invoice
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                        <i class="bi bi-file-pdf"></i>Download PDF
                    </a>
                </li>
                @endif
                <li>
                    <a class="dropdown-item" href="{{ $waUrl }}" target="_blank" rel="noopener">
                        <i class="bi bi-whatsapp"></i>WhatsApp
                    </a>
                </li>
            </ul>
        </div>
    </div>

</article>
@endforeach
