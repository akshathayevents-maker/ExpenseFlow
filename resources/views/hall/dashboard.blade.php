<x-admin-layout title="Hall Operations">
@php
    $eventTypes  = \App\Models\HallBooking::eventTypes();
    $statusNames = \App\Models\HallBooking::statuses();
    $today       = today();
    $nextEvent   = $todayList->first();
    $todayGuests = $kitchenLoad['today']['total'];
    $calStrip    = $occupancyTimeline->take(5);

    // Pending collection metadata
    $oldestPending = $pendingPaymentBookings->first(); // ordered by booking_date asc
    $oldestDays    = $oldestPending ? (int) now()->diffInDays($oldestPending->booking_date) : 0;
    $topDebtor     = $pendingPaymentBookings->sortByDesc(fn($b) => $b->balance_amount)->first();
    $overdueCount  = $pendingPaymentBookings->filter(fn($b) => $b->booking_date->lt($today))->count();

    $statusTone = fn(string $s) => ['confirmed' => '--success', 'completed' => '--info', 'cancelled' => '--danger'][$s] ?? '--warn';
    $statusIcon = fn(string $s) => ['confirmed' => 'bi-check-circle', 'completed' => 'bi-check2-all', 'cancelled' => 'bi-x-circle'][$s] ?? 'bi-clock';

    $pendingMeta = [['icon' => 'bi-calendar3', 'text' => now()->format('l, j F Y')]];
    $pendingMeta[] = $operations['today_bookings'] > 0
        ? ['icon' => 'bi-calendar-event', 'text' => $operations['today_bookings'] . ' event' . ($operations['today_bookings'] !== 1 ? 's' : '') . ' today' . ($todayGuests > 0 ? ' · ' . number_format($todayGuests) . ' guests' : '')]
        : ['icon' => 'bi-calendar-x', 'text' => 'No events scheduled today'];
    if ($operations['pending_balance'] > 0) {
        $pendingMeta[] = ['icon' => 'bi-exclamation-circle', 'text' => '₹' . number_format($operations['pending_balance'], 0) . ' pending'];
    }

    $quick = [
        ['route' => route('hall.bookings.create'),   'icon' => 'bi-plus-circle',   'label' => 'New Booking',   'primary' => true],
        ['route' => route('hall.bookings.calendar'), 'icon' => 'bi-calendar3',     'label' => 'Calendar'],
        ['route' => route('hall.bookings.kitchen'),  'icon' => 'bi-cup-hot',       'label' => 'Kitchen'],
        ['route' => route('hall.bookings.index', ['payment_status' => 'pending']), 'icon' => 'bi-credit-card', 'label' => 'Follow-up'],
        ['route' => route('meal-register.entries.index'), 'icon' => 'bi-journal-check', 'label' => 'Meal Register'],
        ['route' => route('hall.bookings.index'),    'icon' => 'bi-star',          'label' => 'Reviews'],
    ];
@endphp

@push('styles')
<style>
/* Hall Dashboard — hd-*
   Hero, KPI strip and cards are the shared x-ds.* components. This block only
   adds what has no shared equivalent (week strip, list rows, badges) and
   consumes the same --ef-* tokens as /admin/wallets. Spacing: 4/8/12/16/20. */
.hd-page { display: flex; flex-direction: column; gap: 16px; }
.hd-page .ef-ds-hero,
.hd-page .ef-ds-kpi-wrap { margin-bottom: 0; }
.hd-page .ef-ds-hero-main { padding: 20px 24px; min-width: 0; }
.hd-page .ef-ds-hero-side { padding: 20px 24px; min-width: 240px; justify-content: center; }
.hd-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; align-items: start; }
.hd-col { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
@media (min-width: 1200px) { .hd-layout { grid-template-columns: minmax(0, 1fr) 340px; } }

.hd-page a:focus-visible, .hd-page button:focus-visible, .hd-bottom-bar a:focus-visible {
    outline: 2px solid var(--ef-emerald); outline-offset: 2px;
}

/* Hero month navigation (on dark) */
.hd-month { display: inline-flex; align-items: center; gap: 8px; }
.hd-month-label { color: var(--ef-on-dark); font-size: .84rem; font-weight: 700; min-width: 108px; text-align: center; }
.hd-side-name { color: var(--ef-on-dark); font-size: 1rem; font-weight: 760; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hd-side-sub { color: var(--ef-on-dark-muted); font-size: .78rem; margin-top: 2px; }

/* Week strip */
.hd-week { padding: 16px 20px; }
.hd-week-head { display: flex; align-items: baseline; gap: 8px; margin-bottom: 12px; }
.hd-label { font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-muted); }
.hd-label-sub { font-size: .72rem; color: var(--ef-muted); }
.hd-week-days { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
.hd-day {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;
    min-height: 68px; padding: 8px 4px; border-radius: var(--ef-radius-sm);
    border: 1px solid var(--ef-border); background: var(--ef-surface-2);
    color: var(--ef-ink); text-decoration: none;
    transition: background .18s var(--ef-ease), border-color .18s var(--ef-ease);
}
.hd-day:hover { border-color: var(--ef-border-strong); background: var(--ef-surface); color: var(--ef-ink); }
.hd-day-wd { font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--ef-muted); }
.hd-day-num { font-size: 1.15rem; font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }
.hd-day-n { font-size: .68rem; color: var(--ef-muted); white-space: nowrap; }
.hd-day.--has .hd-day-n { color: var(--ef-emerald-dk); font-weight: 700; }
.hd-day.--today { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.hd-day.--today:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }
.hd-day.--today .hd-day-wd, .hd-day.--today .hd-day-n { color: rgba(255,255,255,.85); }

/* Rows inside cards */
.hd-row { display: block; padding: 12px 20px; border-bottom: 1px solid var(--ef-border); color: inherit; text-decoration: none; }
.hd-row:last-child { border-bottom: 0; }
a.hd-row { transition: background .18s var(--ef-ease); }
a.hd-row:hover { background: var(--ef-surface-2); color: inherit; }
.hd-name { font-size: .9rem; font-weight: 700; color: var(--ef-ink); line-height: 1.25; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hd-sub { font-size: .75rem; color: var(--ef-muted); margin-top: 2px; }
.hd-amt { font-size: 1.05rem; font-weight: 800; letter-spacing: -.02em; font-variant-numeric: tabular-nums; white-space: nowrap; color: var(--ef-ink); }
.hd-amt.--danger { color: var(--ef-danger); }
.hd-split { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.hd-split > div:first-child { min-width: 0; flex: 1; }

/* Badges (same geometry as .ef-wlt-health-chip) */
.hd-badge {
    display: inline-flex; align-items: center; gap: 4px; min-height: 24px; padding: 0 8px;
    border-radius: 6px; border: 1px solid transparent;
    font-size: .72rem; font-weight: 700; line-height: 1; white-space: nowrap;
}
.hd-badge i { font-size: .72rem; }
.hd-badge.--success { background: rgba(15,123,95,.10); color: var(--ef-emerald-dk); border-color: rgba(15,123,95,.2); }
.hd-badge.--info    { background: rgba(47,111,237,.08); color: #2350b8; border-color: rgba(47,111,237,.2); }
.hd-badge.--warn    { background: rgba(216,154,61,.10); color: #7D5218; border-color: rgba(216,154,61,.28); }
.hd-badge.--danger  { background: rgba(200,75,68,.08); color: var(--ef-danger); border-color: rgba(200,75,68,.22); }
.hd-badge.--muted   { background: var(--ef-surface-2); color: var(--ef-muted); border-color: var(--ef-border); }
.hd-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }

/* Buttons — .ef-btn geometry */
.hd-btn { min-height: 36px; padding: 0 12px; border-radius: var(--ef-radius-sm); background: var(--ef-surface); font-size: .78rem; }
.hd-btn.--primary { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.hd-btn.--primary:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }
.hd-btn.--wa { color: #1c9c56; }
.hd-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }

/* Stat cells */
.hd-cells { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; padding: 16px 20px 8px; }
.hd-cells.--meals { grid-template-columns: repeat(3, minmax(0, 1fr)); padding: 0 20px 16px; }
.hd-cell { background: var(--ef-surface-2); border: 1px solid var(--ef-border); border-radius: var(--ef-radius-sm); padding: 12px; min-width: 0; }
.hd-cell-lbl { font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--ef-muted); }
.hd-cell-val { font-size: 1.25rem; font-weight: 800; line-height: 1.2; margin-top: 4px; font-variant-numeric: tabular-nums; color: var(--ef-ink); }
.hd-cell-val.--none { font-size: .8rem; font-weight: 600; color: var(--ef-muted); }
.hd-cell-sub { font-size: .72rem; color: var(--ef-muted); margin-top: 2px; }

/* Timeline rows */
.hd-tl { display: grid; grid-template-columns: 72px minmax(0, 1fr) auto; gap: 4px 12px; align-items: center; }
.hd-tl-time { font-size: .8rem; font-weight: 700; color: var(--ef-ink-2); font-variant-numeric: tabular-nums; }
.hd-tl-meta { grid-column: 2 / -1; font-size: .75rem; color: var(--ef-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Empty state (same as .ef-wlt-empty) */
.hd-empty { padding: 32px 20px; text-align: center; }
.hd-empty-icon {
    width: 56px; height: 56px; margin: 0 auto 12px; border-radius: 16px;
    background: var(--ef-surface-2); border: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--ef-faint);
}
.hd-empty-title { font-size: .95rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 4px; }
.hd-empty-text { font-size: .82rem; color: var(--ef-muted); margin-bottom: 16px; }
.hd-empty.--compact { padding: 20px; }
.hd-empty.--compact .hd-empty-text { margin-bottom: 0; }

/* Pending collection */
.hd-collect { padding: 16px 20px 20px; }
.hd-collect-amt { font-size: 2rem; font-weight: 800; letter-spacing: -.03em; line-height: 1.1; color: var(--ef-ink); font-variant-numeric: tabular-nums; }
.hd-collect-amt.--danger { color: var(--ef-danger); }
.hd-collect-meta { display: flex; flex-wrap: wrap; gap: 4px 16px; margin-top: 4px; font-size: .8rem; color: var(--ef-muted); }
.hd-collect-meta b { color: var(--ef-ink); }
.hd-collect-top {
    display: flex; align-items: center; gap: 8px; margin-top: 16px; padding: 12px;
    background: var(--ef-surface-2); border: 1px solid var(--ef-border); border-radius: var(--ef-radius-sm);
    font-size: .82rem; color: var(--ef-ink-2); min-width: 0;
}
.hd-collect-top i { color: var(--ef-muted); flex-shrink: 0; }
.hd-collect-top span { min-width: 0; overflow-wrap: anywhere; }
.hd-collect .hd-btn { margin-top: 16px; }

/* Sidebar lists */
.hd-line { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--ef-border); font-size: .84rem; }
.hd-line:last-child { border-bottom: 0; }
.hd-line-lbl { color: var(--ef-ink-2); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hd-line-val { font-weight: 800; font-variant-numeric: tabular-nums; color: var(--ef-ink); }
.hd-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; background: var(--ef-faint); }
.hd-dot.--success { background: var(--ef-emerald); }
.hd-dot.--warn { background: var(--ef-warning); }
.hd-dot.--danger { background: var(--ef-danger); }
.hd-hall { display: flex; align-items: center; gap: 8px; min-width: 0; }

.hd-quick { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; padding: 16px 20px; }
.hd-quick .ef-btn { min-height: 44px; justify-content: flex-start; padding: 0 12px; background: var(--ef-surface); }
.hd-quick .ef-btn.--primary { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.hd-quick .ef-btn.--primary:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); }
.hd-quick .ef-btn i { color: var(--ef-muted); }
.hd-quick .ef-btn.--primary i { color: #fff; }

/* Mobile bottom bar — hidden on tablet+ and whenever the PWA install banner is up */
.hd-bottom-bar {
    display: none; position: fixed; left: 0; right: 0; bottom: 0; z-index: 200; gap: 8px;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
    background: var(--ef-surface); border-top: 1px solid var(--ef-border); box-shadow: 0 -8px 24px rgba(24,22,18,.06);
}
.hd-bottom-bar .ef-btn { flex: 1; min-height: 44px; background: var(--ef-surface); }
.hd-bottom-bar .ef-btn.--primary { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
body:has(#pwa-install-banner:not([aria-hidden="true"])) .hd-bottom-bar { display: none !important; }

.hd-only-lg { display: none; }
@media (min-width: 1200px) { .hd-only-lg { display: block; } .hd-only-sm { display: none; } }

@media (max-width: 380px) {
    .hd-page .ef-ds-hero-acts { flex-wrap: wrap; overflow: visible; }
}
@media (max-width: 767.98px) {
    .hd-bottom-bar { display: flex; }
    .hd-page { padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px)); }
    .hd-page .ef-ds-hero-acts .ef-ds-btn { min-height: 44px; min-width: 44px; justify-content: center; }
    .hd-btn { min-height: 44px; }
    .hd-page .ef-ds-card-link { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; min-width: 44px; padding: 0 4px; margin: -12px 0; }
    .hd-week { padding: 12px; }
    .hd-week-days { gap: 4px; }
    .hd-row, .hd-line { padding-left: 16px; padding-right: 16px; }
    .hd-cells { padding: 12px 16px 8px; }
    .hd-cells.--meals { padding: 0 16px 12px; }
    .hd-collect { padding: 16px; }
    .hd-collect .hd-btn { width: 100%; }
    .hd-quick { padding: 12px 16px; }
    .hd-collect-amt { font-size: 1.75rem; }
}
</style>
@endpush

<div class="hd-page">

    {{-- Header — shared x-ds.hero --}}
    <x-ds.hero eyebrow="Hall Operations" title="Dashboard" :meta="$pendingMeta">
        <x-slot:actions>
            <div class="hd-month">
                <a href="{{ route('hall.dashboard', ['month' => $prevMonth]) }}" class="ef-ds-btn --icon" aria-label="Previous month">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <span class="hd-month-label" aria-live="polite">{{ $month->format('F Y') }}</span>
                <a href="{{ route('hall.dashboard', ['month' => $nextMonth]) }}" class="ef-ds-btn --icon" aria-label="Next month">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <a href="{{ route('hall.bookings.create') }}" class="ef-ds-btn --primary" aria-label="New booking">
                <i class="bi bi-plus-lg"></i> <span>New Booking</span>
            </a>
        </x-slot:actions>

        <x-slot:side>
            @if($nextEvent)
            <div>
                <div class="ef-ds-side-label">Next Event</div>
                <div class="hd-side-name">{{ $nextEvent->customer_name }}</div>
                <div class="hd-side-sub">{{ \Carbon\Carbon::parse($nextEvent->start_time)->format('h:i A') }} · {{ $nextEvent->location_label }}</div>
            </div>
            <a href="{{ route('hall.bookings.show', $nextEvent) }}" class="ef-ds-btn">View booking <i class="bi bi-arrow-right"></i></a>
            @else
            <div>
                <div class="ef-ds-side-label">Today</div>
                <div class="hd-side-name">No events scheduled</div>
                <div class="hd-side-sub">Hall is open for bookings</div>
            </div>
            <a href="{{ route('hall.bookings.create') }}" class="ef-ds-btn">Add a booking <i class="bi bi-plus-lg"></i></a>
            @endif
        </x-slot:side>

    </x-ds.hero>

    {{-- KPI strip — shared x-ds.kpi-card --}}
    <div class="ef-ds-kpi-wrap">
        <div class="ef-ds-kpi-grid" style="--kpi-cols:4">
            <x-ds.kpi-card
                icon="bi-calendar-event"
                label="Today's Events"
                :value="$operations['today_bookings'] > 0 ? (string) $operations['today_bookings'] : '0'"
                :note="$operations['today_bookings'] > 0 ? $operations['upcoming_bookings'] . ' more this week' : $operations['upcoming_bookings'] . ' upcoming'"
                accent="emerald"
                :value-color="$operations['today_bookings'] > 0 ? 'c-emerald' : ''"
                href="{{ route('hall.bookings.index', ['date_from' => $today->toDateString(), 'date_to' => $today->toDateString()]) }}"
            />
            <x-ds.kpi-card
                icon="bi-people"
                label="Guests Today"
                :value="number_format($todayGuests)"
                :note="$todayGuests > 0 ? number_format($operations['catering_load']) . ' this week' : 'Hall available'"
                accent="bluegray"
            />
            <x-ds.kpi-card
                icon="bi-currency-rupee"
                label="Month Revenue"
                :value="$operations['month_revenue'] > 0 ? '₹' . number_format($operations['month_revenue'] / 1000, 1) . 'K' : '₹0'"
                :note="$operations['month_revenue'] > 0
                    ? $operations['month_bookings_count'] . ' booking' . ($operations['month_bookings_count'] !== 1 ? 's' : '') . ($operations['month_payment_due'] > 0 ? ' · ₹' . number_format($operations['month_payment_due'], 0) . ' due' : '')
                    : 'No bookings in ' . $month->format('M Y')"
                accent="gold"
                :value-color="$operations['month_revenue'] > 0 ? 'c-emerald' : ''"
            />
            <x-ds.kpi-card
                icon="bi-credit-card"
                label="Pending Collection"
                :value="$operations['pending_payments'] > 0 ? '₹' . number_format($operations['pending_balance'], 0) : '₹0'"
                :note="$operations['pending_payments'] > 0 ? $operations['pending_payments'] . ' customer' . ($operations['pending_payments'] !== 1 ? 's' : '') . ' due' : 'All clear · no dues'"
                :accent="$operations['pending_payments'] > 0 ? 'danger' : 'muted'"
                :value-color="$operations['pending_payments'] > 0 ? 'c-danger' : ''"
                href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
            />
        </div>
    </div>

    {{-- Week strip --}}
    <x-ds.card :no-pad="true">
        <div class="hd-week">
            <div class="hd-week-head">
                <span class="hd-label">This Week</span>
                <span class="hd-label-sub">next 5 days from today</span>
            </div>
            <div class="hd-week-days">
                @foreach($calStrip as $i => $day)
                <a href="{{ route('hall.bookings.calendar') }}?date={{ $day['date']->toDateString() }}"
                   class="hd-day {{ $i === 0 ? '--today' : '' }} {{ $day['bookings'] > 0 ? '--has' : '' }}"
                   @if($i === 0) aria-current="date" @endif
                   aria-label="{{ $day['date']->format('l j F') }}{{ $i === 0 ? ', today' : '' }}, {{ $day['bookings'] }} booking{{ $day['bookings'] !== 1 ? 's' : '' }}">
                    <span class="hd-day-wd">{{ $day['date']->format('D') }}</span>
                    <span class="hd-day-num">{{ $day['day'] }}</span>
                    <span class="hd-day-n">{{ $day['bookings'] > 0 ? $day['bookings'] . ' booked' : ($i === 0 ? 'Today' : 'Free') }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </x-ds.card>

    <div class="hd-layout">

        {{-- ── Main column ── --}}
        <div class="hd-col">

            {{-- Needs Attention --}}
            @php
                $attnCx  = $pendingPaymentBookings->take(3);
                $opExtra = $attentionItems
                    ->reject(fn($i) => str_starts_with($i['title'], 'Balance payments'))
                    ->take(max(0, 3 - $attnCx->count()));
            @endphp
            <x-ds.card title="Needs Attention" :no-pad="true">
                <x-slot:head_right>
                    <div style="display:flex;align-items:center;gap:12px">
                        @if($operations['pending_payments'] > 0)
                        <span class="hd-badge --warn"><i class="bi bi-hourglass-split"></i>{{ $operations['pending_payments'] }} pending</span>
                        @endif
                        @if($operations['pending_payments'] > $attnCx->count())
                        <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="ef-ds-card-link">View all</a>
                        @endif
                    </div>
                </x-slot:head_right>

                @foreach($attnCx as $ab)
                @php
                    $abDigits = preg_replace('/\D/', '', $ab->customer_mobile ?? '');
                    $abDue    = max(0, $ab->balance_amount);
                    $abLate   = $ab->booking_date->lt($today) ? (int) $ab->booking_date->diffInDays($today) : 0;
                @endphp
                <div class="hd-row">
                    <div class="hd-split">
                        <div>
                            <div class="hd-name" title="{{ $ab->customer_name }}">{{ $ab->customer_name }}</div>
                            <div class="hd-sub">Event {{ $ab->booking_date->format('d M Y') }} · balance due</div>
                        </div>
                        <div class="hd-amt --danger">₹{{ number_format($abDue, 0) }}</div>
                    </div>
                    <div class="hd-badges">
                        @if($abLate > 0)
                        <span class="hd-badge --danger"><i class="bi bi-exclamation-circle"></i>Overdue {{ $abLate }} day{{ $abLate !== 1 ? 's' : '' }}</span>
                        @endif
                        <span class="hd-badge --warn"><i class="bi bi-hourglass-split"></i>{{ ucfirst($ab->payment_status) }}</span>
                    </div>
                    <div class="hd-actions">
                        @if($abDigits)
                        <a href="tel:{{ $abDigits }}" class="ef-btn hd-btn"><i class="bi bi-telephone"></i> Call</a>
                        <a href="https://wa.me/91{{ $abDigits }}" class="ef-btn hd-btn --wa" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                        @endif
                        <a href="{{ route('hall.bookings.show', $ab) }}#record-payment" class="ef-btn hd-btn --primary">View</a>
                    </div>
                </div>
                @endforeach

                @foreach($opExtra as $item)
                @php $tone = $item['tone'] === 'danger' ? '--danger' : ($item['tone'] === 'emerald' ? '--success' : '--warn'); @endphp
                <a href="{{ $item['url'] }}" class="hd-row">
                    <div class="hd-split">
                        <div>
                            <div class="hd-name" style="white-space:normal">{{ $item['title'] }}</div>
                            <div class="hd-sub">{{ $item['body'] }}</div>
                        </div>
                        <span class="hd-dot {{ $tone }}" aria-hidden="true" style="margin-top:6px"></span>
                    </div>
                </a>
                @endforeach

                @if($attnCx->isEmpty() && $opExtra->isEmpty())
                <div class="hd-empty --compact">
                    <div class="hd-empty-icon"><i class="bi bi-check2-circle"></i></div>
                    <div class="hd-empty-title">Operations are calm</div>
                    <div class="hd-empty-text">No urgent payment or occupancy issues.</div>
                </div>
                @endif
            </x-ds.card>

            {{-- Today's Operations --}}
            <x-ds.card title="Today's Operations" :no-pad="true">
                <x-slot:head_right><span class="hd-label-sub">{{ now()->format('d M') }}</span></x-slot:head_right>
                @if($todayList->isNotEmpty())
                <div class="hd-cells">
                    <div class="hd-cell">
                        <div class="hd-cell-lbl">Events</div>
                        <div class="hd-cell-val">{{ $todayList->count() }}</div>
                        <div class="hd-cell-sub">{{ number_format($todayGuests) }} guests total</div>
                    </div>
                    <div class="hd-cell">
                        <div class="hd-cell-lbl">Food Only</div>
                        @if($operations['food_only_today'] > 0)
                        <div class="hd-cell-val">{{ $operations['food_only_today'] }}</div>
                        <div class="hd-cell-sub">catering orders</div>
                        @else
                        <div class="hd-cell-val --none">None today</div>
                        @endif
                    </div>
                </div>
                <div class="hd-cells --meals">
                    @foreach(['breakfast' => 'Breakfast', 'lunch' => 'Lunch', 'dinner' => 'Dinner'] as $mk => $ml)
                    <div class="hd-cell">
                        <div class="hd-cell-lbl">{{ $ml }}</div>
                        @if($kitchenLoad['today'][$mk] > 0)
                        <div class="hd-cell-val">{{ number_format($kitchenLoad['today'][$mk]) }}</div>
                        @else
                        <div class="hd-cell-val --none">—</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @foreach($todayList as $b)
                <a href="{{ route('hall.bookings.show', $b) }}" class="hd-row hd-tl">
                    <span class="hd-tl-time">{{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}</span>
                    <span class="hd-name" title="{{ $b->customer_name }}">{{ $b->customer_name }}</span>
                    <span class="hd-badge {{ $statusTone($b->status) }}"><i class="bi {{ $statusIcon($b->status) }}"></i>{{ $statusNames[$b->status] ?? ucfirst($b->status) }}</span>
                    <span class="hd-tl-meta">{{ $b->location_label }} · {{ number_format($b->number_of_people) }} guests</span>
                </a>
                @endforeach
                @else
                <div class="hd-empty">
                    <div class="hd-empty-icon"><i class="bi bi-calendar2-check"></i></div>
                    <div class="hd-empty-title">No operations scheduled today</div>
                    <div class="hd-empty-text">You're ready for new bookings.</div>
                    <a href="{{ route('hall.bookings.create') }}" class="ef-btn hd-btn --primary"><i class="bi bi-plus-lg"></i> New Booking</a>
                </div>
                @endif
            </x-ds.card>

            {{-- Pending Collection --}}
            @if($operations['pending_balance'] > 0)
            <x-ds.card title="Pending Collection" :no-pad="true">
                <x-slot:head_right>
                    @if($overdueCount > 0)
                    <span class="hd-badge --danger"><i class="bi bi-exclamation-circle"></i>{{ $overdueCount }} overdue</span>
                    @endif
                </x-slot:head_right>
                <div class="hd-collect">
                    <div class="hd-collect-amt {{ $overdueCount > 0 ? '--danger' : '' }}">₹{{ number_format($operations['pending_balance'], 0) }}</div>
                    <div class="hd-collect-meta">
                        <span><b>{{ $operations['pending_payments'] }}</b> customer{{ $operations['pending_payments'] !== 1 ? 's' : '' }}</span>
                        @if($oldestPending && $oldestDays > 0)
                        <span>Oldest: <b>{{ $oldestDays }} day{{ $oldestDays !== 1 ? 's' : '' }}</b></span>
                        @endif
                    </div>
                    @if($topDebtor)
                    <div class="hd-collect-top">
                        <i class="bi bi-person"></i>
                        <span>Highest: <b>{{ $topDebtor->customer_name }}</b> — ₹{{ number_format(max(0, $topDebtor->balance_amount), 0) }}</span>
                    </div>
                    @endif
                    <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="ef-btn hd-btn --primary">
                        Collect Now <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </x-ds.card>
            @endif

            {{-- Upcoming Events --}}
            @php $upcoming = $nextEvents->filter(fn($b) => $b->booking_date->gt($today))->take(5)->values(); @endphp
            @if($upcoming->isNotEmpty())
            <x-ds.card title="Upcoming Events" :no-pad="true">
                <x-slot:head_right><a href="{{ route('hall.bookings.calendar') }}" class="ef-ds-card-link">Calendar</a></x-slot:head_right>
                @foreach($upcoming as $ub)
                <a href="{{ route('hall.bookings.show', $ub) }}" class="hd-row hd-tl">
                    <span class="hd-tl-time">{{ $ub->booking_date->format('d M') }}</span>
                    <span class="hd-name" title="{{ $ub->customer_name }}">{{ $ub->customer_name }}</span>
                    <span class="hd-badge {{ $statusTone($ub->status) }}"><i class="bi {{ $statusIcon($ub->status) }}"></i>{{ $statusNames[$ub->status] ?? ucfirst($ub->status) }}</span>
                    <span class="hd-tl-meta">{{ \Carbon\Carbon::parse($ub->start_time)->format('h:i A') }} · {{ number_format($ub->number_of_people) }} guests</span>
                </a>
                @endforeach
            </x-ds.card>
            @endif

            {{-- Recent Activity --}}
            <x-ds.card title="Recent Activity" :no-pad="true">
                <x-slot:head_right><a href="{{ route('hall.bookings.index') }}" class="ef-ds-card-link">View all</a></x-slot:head_right>
                @forelse($recentBookings->take(3) as $rb)
                <a href="{{ route('hall.bookings.show', $rb) }}" class="hd-row">
                    <div class="hd-split">
                        <div>
                            <div class="hd-name" title="{{ $rb->customer_name }}">{{ $rb->customer_name }}</div>
                            <div class="hd-sub">{{ $eventTypes[$rb->event_type] ?? ucfirst($rb->event_type) }} · {{ $rb->booking_date->format('d M') }}</div>
                        </div>
                        <div style="text-align:right">
                            <div class="hd-amt">₹{{ number_format($rb->total_amount, 0) }}</div>
                            <div class="hd-sub">{{ ucfirst($rb->payment_status) }}</div>
                        </div>
                    </div>
                    <div class="hd-badges">
                        <span class="hd-badge {{ $statusTone($rb->status) }}"><i class="bi {{ $statusIcon($rb->status) }}"></i>{{ $statusNames[$rb->status] ?? ucfirst($rb->status) }}</span>
                    </div>
                </a>
                @empty
                <div class="hd-empty --compact">
                    <div class="hd-empty-title">No recent bookings</div>
                </div>
                @endforelse
            </x-ds.card>

            {{-- Quick Actions (below desktop breakpoint) --}}
            <div class="hd-only-sm">
                <x-ds.card title="Quick Actions" :no-pad="true">
                    <div class="hd-quick">
                        @foreach($quick as $q)
                        <a href="{{ $q['route'] }}" class="ef-btn {{ !empty($q['primary']) ? '--primary' : '' }}"><i class="bi {{ $q['icon'] }}"></i> {{ $q['label'] }}</a>
                        @endforeach
                    </div>
                </x-ds.card>
            </div>
        </div>

        {{-- ── Sidebar column ── --}}
        <div class="hd-col">

            <x-ds.card title="Kitchen Load" :no-pad="true">
                <x-slot:head_right><span class="hd-label-sub">Tomorrow</span></x-slot:head_right>
                @if($kitchenLoad['tomorrow']['total'] > 0)
                    @foreach(['breakfast' => 'Breakfast', 'lunch' => 'Lunch', 'dinner' => 'Dinner'] as $mk => $ml)
                        @if($kitchenLoad['tomorrow'][$mk] > 0)
                        <div class="hd-line">
                            <span class="hd-line-lbl">{{ $ml }}</span>
                            <span class="hd-line-val">{{ number_format($kitchenLoad['tomorrow'][$mk]) }}</span>
                        </div>
                        @endif
                    @endforeach
                @else
                <div class="hd-empty --compact">
                    <div class="hd-empty-icon"><i class="bi bi-moon-stars"></i></div>
                    <div class="hd-empty-text">No meals planned tomorrow</div>
                </div>
                @endif
            </x-ds.card>

            <x-ds.card title="Hall Status" :no-pad="true">
                <x-slot:head_right><span class="hd-label-sub">{{ now()->format('d M') }}</span></x-slot:head_right>
                @foreach($hallStatuses as $hs)
                @php
                    $isBusy = $hs['state'] === 'Busy today';
                    $isAvail = $hs['state'] === 'Available';
                    $stLabel = $isBusy ? 'Busy today' : ($hs['next_booking'] ? 'Next: ' . $hs['next_booking']->booking_date->format('d M') : 'Available');
                    $stTone = $isBusy ? '--danger' : ($isAvail ? '--success' : '--warn');
                @endphp
                <div class="hd-line">
                    <span class="hd-hall"><span class="hd-dot {{ $stTone }}" aria-hidden="true"></span><span class="hd-line-lbl">{{ $hs['hall']->name }}</span></span>
                    <span class="hd-badge {{ $stTone }}">{{ $stLabel }}</span>
                </div>
                @endforeach
            </x-ds.card>

            <div class="hd-only-lg">
                <x-ds.card title="Quick Actions" :no-pad="true">
                    <div class="hd-quick">
                        @foreach($quick as $q)
                        <a href="{{ $q['route'] }}" class="ef-btn {{ !empty($q['primary']) ? '--primary' : '' }}"><i class="bi {{ $q['icon'] }}"></i> {{ $q['label'] }}</a>
                        @endforeach
                    </div>
                </x-ds.card>
            </div>
        </div>
    </div>

</div>

{{-- Mobile bottom bar --}}
<nav class="hd-bottom-bar" aria-label="Quick actions">
    <a href="{{ route('hall.bookings.create') }}" class="ef-btn --primary"><i class="bi bi-plus-lg"></i> New</a>
    <a href="{{ route('hall.bookings.calendar') }}" class="ef-btn"><i class="bi bi-calendar3"></i> Calendar</a>
    <a href="{{ route('hall.bookings.kitchen') }}" class="ef-btn"><i class="bi bi-cup-hot"></i> Kitchen</a>
</nav>

</x-admin-layout>
