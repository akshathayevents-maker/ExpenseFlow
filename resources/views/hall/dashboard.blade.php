<x-admin-layout title="Hall Operations">
@php
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $today = today();
    $todayHasEvents = $operationMoments->isNotEmpty();
@endphp

<div class="ef-dashboard">
    <section class="ef-dash-hero">
        <div class="ef-dash-hero-main">
            <div class="ef-eyebrow">Luxury Venue Operations</div>
            <h1 class="ef-dash-title">Today’s Control Room</h1>
            <div class="ef-dash-summary">
                <span>{{ now()->format('l, d F Y') }}</span>
                <span>{{ $operations['today_bookings'] }} events today</span>
                <span>{{ number_format($kitchenLoad['today']['total']) }} guest covers</span>
                <span>{{ $attentionItems->count() }} attention signal{{ $attentionItems->count() === 1 ? '' : 's' }}</span>
            </div>
        </div>
        <aside class="ef-dash-hero-side">
            <div>
                <div class="ef-dash-date">Now Matters</div>
                <div class="ef-dash-focus">
                    @if($todayHasEvents)
                        {{ $operationMoments->first()['title'] }}
                    @else
                        Calm operating day
                    @endif
                </div>
                <div class="ef-shell-note mt-2">
                    @if($todayHasEvents)
                        Next operational moment at {{ \Carbon\Carbon::parse($operationMoments->first()['time'])->format('h:i A') }}.
                    @else
                        No event moments are scheduled for today. Stay ready for enquiries and payment follow-ups.
                    @endif
                </div>
            </div>
            <div class="ef-dash-actions">
                <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark"><i class="bi bi-plus-lg"></i> New Booking</a>
                <a href="{{ route('hall.bookings.calendar') }}" class="ef-btn"><i class="bi bi-calendar3"></i> Calendar</a>
                <a href="{{ route('hall.bookings.kitchen') }}" class="ef-btn"><i class="bi bi-cup-hot"></i> Kitchen</a>
            </div>
        </aside>
    </section>

    <div class="ef-command-grid">
        <main class="d-flex flex-column gap-4">
            <section class="ef-today-command">
                <div class="ef-card-body">
                    <div class="ef-command-label">Today</div>
                    <h2 class="ef-command-title">{{ $todayHasEvents ? 'Operational Timeline' : 'No Events Scheduled' }}</h2>
                    <div class="ef-command-sub">
                        @if($todayHasEvents)
                            Follow the day by operational moment: kitchen prep, event start, and payment follow-up.
                        @else
                            Enjoy a calm operational day. The command center will light up as soon as bookings land.
                        @endif
                    </div>

                    @if($todayHasEvents)
                        <div class="ef-moment-list">
                            @foreach($operationMoments as $moment)
                                <a href="{{ $moment['url'] }}" class="ef-moment" data-tone="{{ $moment['tone'] }}">
                                    <div class="ef-moment-time">{{ \Carbon\Carbon::parse($moment['time'])->format('h:i A') }}</div>
                                    <div class="ef-moment-track"><span class="ef-moment-dot"></span></div>
                                    <div>
                                        <div class="ef-moment-kicker">{{ $moment['label'] }}</div>
                                        <div class="ef-moment-title">{{ $moment['title'] }}</div>
                                        <div class="ef-moment-meta">{{ $moment['meta'] }}</div>
                                    </div>
                                    <x-premium.chip :tone="$moment['tone']">Open</x-premium.chip>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="ef-empty-state" style="color:rgba(255,253,250,.78)">
                            <div class="ef-empty-orb" style="background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:rgba(255,255,255,.55)">
                                <i class="bi bi-calendar2-check"></i>
                            </div>
                            <p class="mb-4" style="color:rgba(255,253,250,.58)">No kitchen prep, arrivals, or payment moments are queued for today.</p>
                            <a href="{{ route('hall.bookings.create', ['date' => today()->toDateString()]) }}" class="ef-btn ef-btn-dark">Add Booking</a>
                        </div>
                    @endif

                    <div class="ef-command-pulse">
                        <div class="ef-pulse-cell">
                            <div class="ef-pulse-value">{{ $operations['today_bookings'] }}</div>
                            <div class="ef-pulse-label">events today</div>
                        </div>
                        <div class="ef-pulse-cell">
                            <div class="ef-pulse-value">{{ number_format($kitchenLoad['today']['total']) }}</div>
                            <div class="ef-pulse-label">guest covers</div>
                        </div>
                        <div class="ef-pulse-cell">
                            <div class="ef-pulse-value">{{ $operations['pending_payments'] }}</div>
                            <div class="ef-pulse-label">payment follow-ups</div>
                        </div>
                        <div class="ef-pulse-cell">
                            <div class="ef-pulse-value">{{ $operations['occupancy_rate'] }}%</div>
                            <div class="ef-pulse-label">week occupancy</div>
                        </div>
                    </div>
                </div>
            </section>

            <x-premium.card title="Next Events">
                <div class="ef-event-strip">
                    @forelse($nextEvents as $booking)
                        @php
                            $mealText = collect([
                                'Breakfast' => $booking->has_breakfast,
                                'Lunch' => $booking->has_lunch,
                                'Dinner' => $booking->has_dinner,
                            ])->filter()->keys()->join(', ') ?: 'No meals';
                        @endphp
                        <a href="{{ route('hall.bookings.show', $booking) }}" class="ef-event-card">
                            <div class="ef-event-type">{{ $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type) }}</div>
                            <div class="ef-event-name">{{ $booking->customer_name }}</div>
                            <div class="ef-event-detail">
                                {{ $booking->booking_date->format('d M') }} · {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}<br>
                                {{ $booking->hall->name }} · {{ number_format($booking->number_of_people) }} guests<br>
                                {{ $mealText }}
                            </div>
                        </a>
                    @empty
                        <p class="ef-shell-note mb-0">No upcoming events in the next seven days.</p>
                    @endforelse
                </div>
            </x-premium.card>

            <x-premium.card title="Hall Occupancy">
                <div class="ef-occupancy-bars">
                    @foreach($occupancyTimeline as $day)
                        <div class="ef-occ-row" data-load="{{ $day['load'] }}">
                            <div class="ef-occ-day">{{ $day['label'] }} {{ $day['day'] }}</div>
                            <div class="ef-occ-track">
                                <div class="ef-occ-fill" style="width: {{ $day['percent'] }}%"></div>
                            </div>
                            <div class="ef-muted small text-end">{{ $day['bookings'] }} evt</div>
                        </div>
                    @endforeach
                </div>
            </x-premium.card>

            <x-premium.card title="Recent Booking Activity">
                @forelse($recentBookings as $booking)
                    @php $statusTone = ['confirmed' => 'emerald', 'completed' => 'bluegray', 'cancelled' => 'danger'][$booking->status] ?? 'neutral'; @endphp
                    <a href="{{ route('hall.bookings.show', $booking) }}" class="ef-feed-item">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="ef-feed-title">{{ $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type) }}</div>
                                <x-premium.chip :tone="$statusTone">{{ \App\Models\HallBooking::statuses()[$booking->status] ?? Str::headline($booking->status) }}</x-premium.chip>
                            </div>
                            <div class="ef-feed-meta">
                                {{ $booking->customer_name }} · {{ $booking->hall->name }} · {{ $booking->booking_date->format('d M Y') }} · {{ number_format($booking->number_of_people) }} guests
                            </div>
                        </div>
                        <div class="ef-feed-amount">
                            ₹{{ number_format($booking->total_amount, 0) }}
                            <div class="ef-muted small">{{ ucfirst($booking->payment_status) }}</div>
                        </div>
                    </a>
                @empty
                    <p class="ef-shell-note mb-0">No booking activity yet.</p>
                @endforelse
            </x-premium.card>
        </main>

        <aside class="d-flex flex-column gap-4">
            <x-premium.card title="Attention Required">
                @foreach($attentionItems as $item)
                    <a href="{{ $item['url'] }}" class="ef-attention-item" data-tone="{{ $item['tone'] }}">
                        <span class="ef-attention-rail"></span>
                        <span>
                            <span class="ef-value-strong d-block">{{ $item['title'] }}</span>
                            <span class="ef-shell-note">{{ $item['body'] }}</span>
                        </span>
                    </a>
                @endforeach
            </x-premium.card>

            <x-premium.card title="Kitchen Load">
                <div class="ef-label mb-3">Today</div>
                <div class="ef-kitchen-grid mb-4">
                    <div class="ef-kitchen-meal">
                        <span class="ef-label">Breakfast</span>
                        <div class="ef-kitchen-value">{{ number_format($kitchenLoad['today']['breakfast']) }}</div>
                    </div>
                    <div class="ef-kitchen-meal">
                        <span class="ef-label">Lunch</span>
                        <div class="ef-kitchen-value">{{ number_format($kitchenLoad['today']['lunch']) }}</div>
                    </div>
                    <div class="ef-kitchen-meal">
                        <span class="ef-label">Dinner</span>
                        <div class="ef-kitchen-value">{{ number_format($kitchenLoad['today']['dinner']) }}</div>
                    </div>
                </div>

                <div class="ef-label mb-3">Tomorrow</div>
                <div class="ef-kitchen-grid">
                    <div class="ef-kitchen-meal">
                        <span class="ef-label">Breakfast</span>
                        <div class="ef-kitchen-value">{{ number_format($kitchenLoad['tomorrow']['breakfast']) }}</div>
                    </div>
                    <div class="ef-kitchen-meal">
                        <span class="ef-label">Lunch</span>
                        <div class="ef-kitchen-value">{{ number_format($kitchenLoad['tomorrow']['lunch']) }}</div>
                    </div>
                    <div class="ef-kitchen-meal">
                        <span class="ef-label">Dinner</span>
                        <div class="ef-kitchen-value">{{ number_format($kitchenLoad['tomorrow']['dinner']) }}</div>
                    </div>
                </div>
            </x-premium.card>

            <x-premium.card title="Payment Follow-Up" :aside="'₹' . number_format($operations['pending_balance'], 0)">
                @forelse($pendingPaymentBookings as $booking)
                    <a href="{{ route('hall.bookings.show', $booking) }}#record-payment" class="ef-feed-item">
                        <div>
                            <div class="ef-feed-title">{{ $booking->customer_name }}</div>
                            <div class="ef-feed-meta">{{ $booking->booking_date->format('d M') }} · {{ $booking->hall->name }} · {{ ucfirst($booking->payment_status) }}</div>
                        </div>
                        <div class="ef-feed-amount">
                            ₹{{ number_format(max(0, $booking->balance_amount), 0) }}
                            <div class="ef-muted small">due</div>
                        </div>
                    </a>
                @empty
                    <p class="ef-shell-note mb-0">No pending payment alerts.</p>
                @endforelse
            </x-premium.card>

            <x-premium.card title="Hall State">
                <div class="ef-hall-grid">
                    @foreach($hallStatuses as $status)
                        @php $next = $status['next_booking']; @endphp
                        <div class="ef-hall-card" data-state="{{ $status['state'] }}">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="ef-value-strong">{{ $status['hall']->name }}</div>
                                    <div class="ef-muted small">Capacity {{ number_format($status['hall']->capacity) }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="ef-value-strong">{{ $status['upcoming_count'] }}</div>
                                    <div class="ef-muted small">upcoming</div>
                                </div>
                            </div>
                            <div class="ef-hall-state"><span class="ef-hall-dot"></span>{{ $status['state'] }}</div>
                            @if($next)
                                <div class="ef-shell-note mt-3">Next: {{ $next->customer_name }} · {{ $next->booking_date->format('d M') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-premium.card>

            <x-premium.card title="Operator Actions">
                <div class="ef-quick-grid">
                    <a href="{{ route('hall.bookings.create') }}" class="ef-quick-action"><i class="bi bi-plus-lg"></i> New Booking</a>
                    <a href="{{ route('hall.bookings.calendar') }}" class="ef-quick-action"><i class="bi bi-calendar3"></i> Calendar</a>
                    <a href="{{ route('hall.bookings.kitchen') }}" class="ef-quick-action"><i class="bi bi-cup-hot"></i> Kitchen</a>
                    <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="ef-quick-action"><i class="bi bi-credit-card"></i> Payments</a>
                </div>
            </x-premium.card>
        </aside>
    </div>
</div>

<div class="ef-mobile-dash-actions">
    <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark"><i class="bi bi-plus-lg"></i> New</a>
    <a href="{{ route('hall.bookings.calendar') }}" class="ef-btn"><i class="bi bi-calendar3"></i> Calendar</a>
    <a href="{{ route('hall.bookings.kitchen') }}" class="ef-btn"><i class="bi bi-cup-hot"></i> Kitchen</a>
</div>
</x-admin-layout>
