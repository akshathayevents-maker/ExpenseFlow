<x-admin-layout title="Hall Operations">
@php
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $today = today();
    $todayHasEvents = $operationMoments->isNotEmpty();
@endphp

@push('styles')
<style>
/* ── Hall Dashboard page-level styles ────────────────────────── */
:root {
    --hd-gold:     #B8893E;
    --hd-gold-hi:  #D6B97A;
    --hd-emerald:  #0F7B5F;
    --hd-danger:   #b91c1c;
    --hd-ink:      #1a1612;
    --hd-muted:    #6b6560;
    --hd-faint:    #ede9e3;
    --hd-border:   rgba(160,114,56,.15);
    --hd-border-s: rgba(160,114,56,.30);
    --hd-shadow:   0 1px 3px rgba(26,22,18,.08),0 4px 12px rgba(26,22,18,.06);
    --hd-shadow-h: 0 4px 16px rgba(26,22,18,.14),0 1px 4px rgba(26,22,18,.08);
    --hd-radius:   14px;
    --hd-ease:     cubic-bezier(.25,.46,.45,.94);
}

/* ── Hero: dark dramatic override ─────────────────────────────── */
.ef-dash-hero {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%) !important;
    border-color: rgba(255,255,255,.07) !important;
    overflow: hidden;
    position: relative;
}
.ef-dash-hero::before,
.ef-dash-hero::after {
    border-radius: 50%;
    content: "";
    pointer-events: none;
    position: absolute;
}
.ef-dash-hero::before {
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    height: 500px;
    right: -100px;
    top: -160px;
    width: 500px;
}
.ef-dash-hero::after {
    background: radial-gradient(circle, rgba(26,102,69,.12) 0%, transparent 68%);
    bottom: -110px;
    height: 320px;
    left: 22%;
    width: 320px;
}
.ef-eyebrow {
    color: rgba(160,114,56,.9);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .18em;
    text-transform: uppercase;
}
.ef-dash-title          { color: #fffdfa !important; }
.ef-dash-summary        { color: rgba(255,253,250,.55) !important; }
.ef-dash-date           { color: rgba(255,253,250,.3) !important; }
.ef-dash-focus          { color: #fffdfa !important; font-size: 1.15rem !important; }
.ef-dash-hero .ef-shell-note { color: rgba(255,253,250,.48) !important; }
.ef-dash-hero-side      { background: rgba(255,255,255,.03) !important; border-left-color: rgba(255,255,255,.07) !important; }
.ef-dash-hero .ef-btn   { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.14); color: rgba(255,253,250,.88); }
.ef-dash-hero .ef-btn:hover { background: rgba(255,255,255,.14); color: #fffdfa; }
.ef-dash-hero .ef-btn-dark  { background: var(--hd-gold); border-color: var(--hd-gold); color: #fff; }
.ef-dash-hero .ef-btn-dark:hover { background: var(--hd-gold-hi); border-color: var(--hd-gold-hi); }

/* ── Metrics strip ─────────────────────────────────────────────── */
.ef-dash-metrics        { margin-bottom: 24px; }
.ef-dash-metric         { background: #fff; position: relative; }
.ef-hd-m-icon {
    color: var(--hd-gold);
    float: right;
    font-size: 1rem;
    opacity: .6;
}
.ef-hd-m-label {
    color: var(--hd-muted);
    font-size: .7rem;
    font-weight: 720;
    letter-spacing: .05em;
    text-transform: uppercase;
}
.ef-dash-metric-value.c-gold    { color: var(--hd-gold); }
.ef-dash-metric-value.c-danger  { color: var(--hd-danger); }
.ef-dash-metric-value.c-emerald { color: var(--hd-emerald); }
a.ef-dash-metric { text-decoration: none; }
a.ef-dash-metric:hover { border-color: var(--hd-border-s); box-shadow: var(--hd-shadow-h); transform: translateY(-1px); }

/* ── Inline cards (replace x-premium.card) ────────────────────── */
.ef-hd-card {
    background: #fff;
    border: 1px solid var(--hd-border);
    border-radius: 16px;
    box-shadow: var(--hd-shadow);
    overflow: hidden;
}
.ef-hd-card-head {
    align-items: center;
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 17px 22px;
}
.ef-hd-card-title {
    color: var(--hd-ink);
    font-size: .88rem;
    font-weight: 760;
}
.ef-hd-card-aside {
    color: var(--hd-muted);
    font-size: .82rem;
    font-weight: 720;
}
.ef-hd-card-body { padding: 18px 22px; }
.ef-hd-card-body.no-pad-v { padding-top: 0; padding-bottom: 0; }

/* ── Hall state: left accent stripe ────────────────────────────── */
.ef-hall-card {
    border-left: 3px solid var(--hd-faint);
    transition: border-left-color .18s var(--hd-ease);
}
.ef-hall-card[data-state="Busy today"]    { border-left-color: var(--hd-gold); }
.ef-hall-card[data-state="Upcoming event"]{ border-left-color: #6366f1; }
.ef-hall-card[data-state="Available"]     { border-left-color: var(--hd-emerald); }

/* ── Occupancy bar: slightly taller ─────────────────────────────── */
.ef-occ-track { height: 11px; }
.ef-occ-fill  { transition: width .5s var(--hd-ease); }

/* ── Section divider labels ─────────────────────────────────────── */
.ef-hd-section-label {
    color: var(--hd-muted);
    font-size: .7rem;
    font-weight: 720;
    letter-spacing: .06em;
    margin-bottom: 12px;
    text-transform: uppercase;
}

/* ── Mobile bottom bar ──────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .ef-mobile-dash-actions {
        background: rgba(26,20,16,.95) !important;
        border-top-color: rgba(255,255,255,.07) !important;
    }
    .ef-mobile-dash-actions .ef-btn {
        background: rgba(255,255,255,.07);
        border-color: rgba(255,255,255,.12);
        color: rgba(255,253,250,.82);
    }
    .ef-mobile-dash-actions .ef-btn-dark {
        background: var(--hd-gold);
        border-color: var(--hd-gold);
        color: #fff;
    }
}
</style>
@endpush

<div class="ef-dashboard">

    {{-- ── Hero ─────────────────────────────────────────────────────── --}}
    <section class="ef-dash-hero">
        <div class="ef-dash-hero-main">
            <div class="ef-eyebrow">Luxury Venue Operations</div>
            <h1 class="ef-dash-title">Today's Control Room</h1>
            <div class="ef-dash-summary">
                <span>{{ now()->format('l, d F Y') }}</span>
                <span>·</span>
                <span>{{ $operations['today_bookings'] }} events today</span>
                <span>·</span>
                <span>{{ number_format($kitchenLoad['today']['total']) }} guest covers</span>
                <span>·</span>
                <span>{{ $attentionItems->count() }} attention signal{{ $attentionItems->count() === 1 ? '' : 's' }}</span>
            </div>
        </div>
        <aside class="ef-dash-hero-side">
            <div>
                <div class="ef-dash-date">Now Matters</div>
                <div class="ef-dash-focus">
                    @if($todayHasEvents){{ $operationMoments->first()['title'] }}
                    @else Calm operating day
                    @endif
                </div>
                <div class="ef-shell-note mt-2">
                    @if($todayHasEvents)
                        Next moment at {{ \Carbon\Carbon::parse($operationMoments->first()['time'])->format('h:i A') }}.
                    @else
                        No event moments scheduled. Stay ready for enquiries.
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

    {{-- ── KPI Metrics Strip ───────────────────────────────────────── --}}
    <div class="ef-dash-metrics">
        <div class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-calendar-event ef-hd-m-icon"></i>Today's Events</div>
            <div class="ef-dash-metric-value {{ $operations['today_bookings'] > 0 ? 'c-gold' : '' }}">{{ $operations['today_bookings'] }}</div>
            <div class="ef-dash-metric-note">{{ $operations['upcoming_bookings'] }} more this week</div>
        </div>
        <div class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-people ef-hd-m-icon"></i>Guest Covers</div>
            <div class="ef-dash-metric-value">{{ number_format($kitchenLoad['today']['total']) }}</div>
            <div class="ef-dash-metric-note">{{ number_format($operations['catering_load']) }} this week total</div>
        </div>
        <div class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-bar-chart ef-hd-m-icon"></i>Week Occupancy</div>
            <div class="ef-dash-metric-value {{ $operations['occupancy_rate'] >= 75 ? 'c-danger' : ($operations['occupancy_rate'] >= 35 ? 'c-gold' : 'c-emerald') }}">{{ $operations['occupancy_rate'] }}%</div>
            <div class="ef-dash-metric-note">hall utilisation rate</div>
        </div>
        <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-credit-card ef-hd-m-icon"></i>Payment Follow-Ups</div>
            <div class="ef-dash-metric-value {{ $operations['pending_payments'] > 0 ? 'c-danger' : 'c-emerald' }}">{{ $operations['pending_payments'] }}</div>
            <div class="ef-dash-metric-note">₹{{ number_format($operations['pending_balance'], 0) }} pending</div>
        </a>
        <a href="{{ route('hall.bookings.kitchen') }}" class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-cup-hot ef-hd-m-icon"></i>Food Only Today</div>
            <div class="ef-dash-metric-value {{ ($operations['food_only_today'] ?? 0) > 0 ? 'c-gold' : '' }}">{{ $operations['food_only_today'] ?? 0 }}</div>
            <div class="ef-dash-metric-note">external catering orders</div>
        </a>
        <div class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-currency-rupee ef-hd-m-icon"></i>Month Revenue</div>
            <div class="ef-dash-metric-value c-gold">₹{{ number_format($operations['month_revenue'], 0) }}</div>
            <div class="ef-dash-metric-note">{{ now()->format('F Y') }}</div>
        </div>
        <div class="ef-dash-metric">
            <div class="ef-hd-m-label"><i class="bi bi-calendar-week ef-hd-m-icon"></i>Upcoming Bookings</div>
            <div class="ef-dash-metric-value">{{ $operations['upcoming_bookings'] }}</div>
            <div class="ef-dash-metric-note">next 7 days excl. today</div>
        </div>
    </div>

    {{-- ── Command Grid ─────────────────────────────────────────────── --}}
    <div class="ef-command-grid">
        <main class="d-flex flex-column gap-4">

            {{-- Today's Operational Timeline --}}
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
                            <div class="ef-empty-orb" style="background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:rgba(255,255,255,.5)">
                                <i class="bi bi-calendar2-check"></i>
                            </div>
                            <p class="mb-4" style="color:rgba(255,253,250,.52)">No kitchen prep, arrivals, or payment moments queued for today.</p>
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

            {{-- Next Events --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Next Events</span>
                    <a href="{{ route('hall.bookings.calendar') }}" class="ef-hd-card-aside" style="text-decoration:none;color:var(--hd-gold)">View calendar →</a>
                </div>
                <div class="ef-hd-card-body">
                    <div class="ef-event-strip">
                        @forelse($nextEvents as $booking)
                            @php
                                $mealText = collect([
                                    'Breakfast' => $booking->has_breakfast,
                                    'Lunch'     => $booking->has_lunch,
                                    'Dinner'    => $booking->has_dinner,
                                ])->filter()->keys()->join(', ') ?: 'No meals';
                            @endphp
                            <a href="{{ route('hall.bookings.show', $booking) }}" class="ef-event-card">
                                <div class="ef-event-type">{{ $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type) }}</div>
                                <div class="ef-event-name">{{ $booking->customer_name }}</div>
                                <div class="ef-event-detail">
                                    {{ $booking->booking_date->format('d M') }} · {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}<br>
                                    {{ $booking->hall?->name ?? $booking->service_location ?? 'Food Only' }} · {{ number_format($booking->number_of_people) }} guests<br>
                                    {{ $mealText }}
                                </div>
                            </a>
                        @empty
                            <p class="ef-shell-note mb-0">No upcoming events in the next seven days.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Hall Occupancy --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Hall Occupancy</span>
                    <span class="ef-hd-card-aside">7-day view</span>
                </div>
                <div class="ef-hd-card-body">
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
                </div>
            </div>

            {{-- Recent Booking Activity --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Recent Booking Activity</span>
                    <a href="{{ route('hall.bookings.index') }}" class="ef-hd-card-aside" style="text-decoration:none;color:var(--hd-gold)">All bookings →</a>
                </div>
                <div class="ef-hd-card-body no-pad-v">
                    @forelse($recentBookings as $booking)
                        @php $statusTone = ['confirmed' => 'emerald', 'completed' => 'bluegray', 'cancelled' => 'danger'][$booking->status] ?? 'neutral'; @endphp
                        <a href="{{ route('hall.bookings.show', $booking) }}" class="ef-feed-item">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="ef-feed-title">{{ $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type) }}</div>
                                    <x-premium.chip :tone="$statusTone">{{ \App\Models\HallBooking::statuses()[$booking->status] ?? Str::headline($booking->status) }}</x-premium.chip>
                                </div>
                                <div class="ef-feed-meta">
                                    {{ $booking->customer_name }} · {{ $booking->hall?->name ?? $booking->service_location ?? 'Food Only' }} · {{ $booking->booking_date->format('d M Y') }} · {{ number_format($booking->number_of_people) }} guests
                                </div>
                            </div>
                            <div class="ef-feed-amount">
                                ₹{{ number_format($booking->total_amount, 0) }}
                                <div class="ef-muted small">{{ ucfirst($booking->payment_status) }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="ef-shell-note mb-0 py-4">No booking activity yet.</p>
                    @endforelse
                </div>
            </div>
        </main>

        {{-- ── Sidebar ──────────────────────────────────────────────── --}}
        <aside class="d-flex flex-column gap-4">

            {{-- Attention Required --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Attention Required</span>
                    @php $urgentCount = $attentionItems->where('tone', 'danger')->count(); @endphp
                    @if($urgentCount > 0)
                        <span class="ef-hd-card-aside" style="color:var(--hd-danger)"><i class="bi bi-exclamation-circle-fill"></i> {{ $urgentCount }} urgent</span>
                    @endif
                </div>
                <div class="ef-hd-card-body no-pad-v">
                    @foreach($attentionItems as $item)
                        <a href="{{ $item['url'] }}" class="ef-attention-item" data-tone="{{ $item['tone'] }}">
                            <span class="ef-attention-rail"></span>
                            <span>
                                <span class="ef-value-strong d-block">{{ $item['title'] }}</span>
                                <span class="ef-shell-note">{{ $item['body'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Kitchen Load --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Kitchen Load</span>
                    <span class="ef-hd-card-aside"><i class="bi bi-cup-hot"></i> {{ number_format($kitchenLoad['today']['total']) }} covers today</span>
                </div>
                <div class="ef-hd-card-body">
                    <div class="ef-hd-section-label">Today</div>
                    <div class="ef-kitchen-grid mb-4">
                        <div class="ef-kitchen-meal">
                            <span class="ef-label" style="font-size:.7rem;color:var(--hd-muted)">Breakfast</span>
                            <div class="ef-kitchen-value">{{ number_format($kitchenLoad['today']['breakfast']) }}</div>
                        </div>
                        <div class="ef-kitchen-meal">
                            <span class="ef-label" style="font-size:.7rem;color:var(--hd-muted)">Lunch</span>
                            <div class="ef-kitchen-value">{{ number_format($kitchenLoad['today']['lunch']) }}</div>
                        </div>
                        <div class="ef-kitchen-meal">
                            <span class="ef-label" style="font-size:.7rem;color:var(--hd-muted)">Dinner</span>
                            <div class="ef-kitchen-value">{{ number_format($kitchenLoad['today']['dinner']) }}</div>
                        </div>
                    </div>
                    <div class="ef-hd-section-label">Tomorrow</div>
                    <div class="ef-kitchen-grid">
                        <div class="ef-kitchen-meal">
                            <span class="ef-label" style="font-size:.7rem;color:var(--hd-muted)">Breakfast</span>
                            <div class="ef-kitchen-value">{{ number_format($kitchenLoad['tomorrow']['breakfast']) }}</div>
                        </div>
                        <div class="ef-kitchen-meal">
                            <span class="ef-label" style="font-size:.7rem;color:var(--hd-muted)">Lunch</span>
                            <div class="ef-kitchen-value">{{ number_format($kitchenLoad['tomorrow']['lunch']) }}</div>
                        </div>
                        <div class="ef-kitchen-meal">
                            <span class="ef-label" style="font-size:.7rem;color:var(--hd-muted)">Dinner</span>
                            <div class="ef-kitchen-value">{{ number_format($kitchenLoad['tomorrow']['dinner']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Follow-Up --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Payment Follow-Up</span>
                    @if($operations['pending_balance'] > 0)
                        <span class="ef-hd-card-aside" style="color:var(--hd-danger);font-weight:760">₹{{ number_format($operations['pending_balance'], 0) }} due</span>
                    @endif
                </div>
                <div class="ef-hd-card-body no-pad-v">
                    @forelse($pendingPaymentBookings as $booking)
                        <a href="{{ route('hall.bookings.show', $booking) }}#record-payment" class="ef-feed-item">
                            <div>
                                <div class="ef-feed-title">{{ $booking->customer_name }}</div>
                                <div class="ef-feed-meta">{{ $booking->booking_date->format('d M') }} · {{ $booking->hall?->name ?? $booking->service_location ?? 'Food Only' }} · {{ ucfirst($booking->payment_status) }}</div>
                            </div>
                            <div class="ef-feed-amount" style="color:var(--hd-danger)">
                                ₹{{ number_format(max(0, $booking->balance_amount), 0) }}
                                <div class="ef-muted small">due</div>
                            </div>
                        </a>
                    @empty
                        <p class="ef-shell-note mb-0 py-4">No pending payment alerts.</p>
                    @endforelse
                </div>
            </div>

            {{-- Hall State --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Hall State</span>
                    <span class="ef-hd-card-aside">{{ $hallStatuses->where('state', 'Busy today')->count() }} busy today</span>
                </div>
                <div class="ef-hd-card-body">
                    <div class="ef-hall-grid">
                        @foreach($hallStatuses as $status)
                            @php $next = $status['next_booking']; @endphp
                            <div class="ef-hall-card" data-state="{{ $status['state'] }}">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="ef-value-strong">{{ $status['hall']->name }}</div>
                                        <div class="ef-muted small">Cap {{ number_format($status['hall']->capacity) }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="ef-value-strong">{{ $status['upcoming_count'] }}</div>
                                        <div class="ef-muted small">upcoming</div>
                                    </div>
                                </div>
                                <div class="ef-hall-state"><span class="ef-hall-dot"></span>{{ $status['state'] }}</div>
                                @if($next)
                                    <div class="ef-shell-note mt-2" style="font-size:.76rem">Next: {{ $next->customer_name }} · {{ $next->booking_date->format('d M') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Operator Actions --}}
            <div class="ef-hd-card">
                <div class="ef-hd-card-head">
                    <span class="ef-hd-card-title">Operator Actions</span>
                </div>
                <div class="ef-hd-card-body">
                    <div class="ef-quick-grid">
                        <a href="{{ route('hall.bookings.create') }}" class="ef-quick-action"><i class="bi bi-plus-lg"></i> New Booking</a>
                        <a href="{{ route('hall.bookings.calendar') }}" class="ef-quick-action"><i class="bi bi-calendar3"></i> Calendar</a>
                        <a href="{{ route('hall.bookings.kitchen') }}" class="ef-quick-action"><i class="bi bi-cup-hot"></i> Kitchen</a>
                        <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="ef-quick-action"><i class="bi bi-credit-card"></i> Payments</a>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="ef-mobile-dash-actions">
    <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark"><i class="bi bi-plus-lg"></i> New</a>
    <a href="{{ route('hall.bookings.calendar') }}" class="ef-btn"><i class="bi bi-calendar3"></i> Calendar</a>
    <a href="{{ route('hall.bookings.kitchen') }}" class="ef-btn"><i class="bi bi-cup-hot"></i> Kitchen</a>
</div>
</x-admin-layout>
