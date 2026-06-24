<x-admin-layout title="Hall Operations">
@php
    $eventTypes  = \App\Models\HallBooking::eventTypes();
    $today       = today();
    $hour        = now()->hour;
    $greeting    = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $nextEvent   = $todayList->first();
    $todayGuests = $kitchenLoad['today']['total'];
    $calStrip    = $occupancyTimeline->take(5);

    // Pending collection metadata
    $oldestPending = $pendingPaymentBookings->first(); // ordered by booking_date asc
    $oldestDays    = $oldestPending ? (int) now()->diffInDays($oldestPending->booking_date) : 0;
    $topDebtor     = $pendingPaymentBookings->sortByDesc(fn($b) => $b->balance_amount)->first();
    $overdueCount  = $pendingPaymentBookings->filter(fn($b) => $b->booking_date->lt($today))->count();
@endphp

@push('styles')
<style>
/* Hall Dashboard — Operations Center */
:root {
    --hd-gold:     #B8893E;
    --hd-gold-hi:  #D6B97A;
    --hd-gold-bg:  #fdf8f0;
    --hd-green:    #0F7B5F;
    --hd-green-bg: #f0faf6;
    --hd-orange:   #c2600a;
    --hd-orange-bg:#fff7ed;
    --hd-blue:     #1d5fa8;
    --hd-blue-bg:  #eff6ff;
    --hd-red:      #b91c1c;
    --hd-red-bg:   #fef2f2;
    --hd-ink:      #1a1612;
    --hd-sub:      #4a4540;
    --hd-muted:    #6b6560;
    --hd-faint:    #ede9e3;
    --hd-border:   rgba(160,114,56,.12);
    --hd-border-s: rgba(160,114,56,.28);
    --hd-shadow:   0 1px 3px rgba(26,22,18,.07),0 4px 12px rgba(26,22,18,.05);
    --hd-shadow-h: 0 4px 16px rgba(26,22,18,.13),0 1px 4px rgba(26,22,18,.07);
    --hd-radius:   14px;
    --hd-ease:     cubic-bezier(.25,.46,.45,.94);
}
#main-content { background: #f5f4f0 !important; }

/* ── Hero ── */
.hd-hero {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border-radius: 0 0 20px 20px;
    overflow: hidden;
    padding: 18px 20px 0;
    position: relative;
}
.hd-hero::before {
    background: radial-gradient(circle, rgba(160,114,56,.18) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 360px;
    pointer-events: none;
    position: absolute;
    right: -80px;
    top: -120px;
    width: 360px;
}
.hd-hero-main { position: relative; z-index: 1; }
.hd-greeting {
    color: rgba(255,253,250,.38);
    font-size: .64rem;
    font-weight: 720;
    letter-spacing: .16em;
    text-transform: uppercase;
}
.hd-hero-title {
    color: #fffdfa;
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 2px 0 8px;
}
.hd-hero-bullets {
    display: flex;
    flex-wrap: wrap;
    gap: 5px 12px;
    margin-bottom: 12px;
}
.hd-hero-bullet {
    align-items: center;
    color: rgba(255,253,250,.68);
    display: flex;
    font-size: .73rem;
    gap: 5px;
}
.hd-hero-bullet i { color: var(--hd-gold); font-size: .76rem; }
.hd-hero-bullet.--red i { color: #f87171; }
.hd-next-bar {
    align-items: center;
    background: rgba(255,255,255,.05);
    border-top: 1px solid rgba(255,255,255,.07);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin: 0 -20px;
    padding: 10px 20px;
}
.hd-next-label {
    color: rgba(255,253,250,.35);
    font-size: .6rem;
    font-weight: 720;
    letter-spacing: .12em;
    text-transform: uppercase;
}
.hd-next-name { color: #fffdfa; font-size: .87rem; font-weight: 700; }
.hd-next-time { color: var(--hd-gold); font-size: .74rem; font-weight: 640; }
.hd-next-view {
    background: var(--hd-gold);
    border-radius: 8px;
    color: #fff;
    font-size: .7rem;
    font-weight: 720;
    padding: 5px 11px;
    text-decoration: none;
    white-space: nowrap;
}
.hd-next-view:hover { background: var(--hd-gold-hi); color: #fff; }

/* ── KPI Strip ── */
.hd-kpi-strip {
    display: grid;
    gap: 9px;
    grid-template-columns: repeat(2, 1fr);
    margin: 12px 0 0;
}
.hd-kpi {
    background: #fff;
    border: 1px solid var(--hd-border);
    border-radius: 12px;
    box-shadow: var(--hd-shadow);
    padding: 10px 13px;
    text-decoration: none;
}
.hd-kpi:hover { box-shadow: var(--hd-shadow-h); }
.hd-kpi-head { align-items: center; display: flex; justify-content: space-between; margin-bottom: 3px; }
.hd-kpi-lbl { color: var(--hd-muted); font-size: .61rem; font-weight: 720; letter-spacing: .04em; text-transform: uppercase; }
.hd-kpi-ico { border-radius: 7px; font-size: .75rem; height: 24px; line-height: 24px; text-align: center; width: 24px; }
.hd-kpi-ico.--gold   { background: var(--hd-gold-bg);   color: var(--hd-gold);   }
.hd-kpi-ico.--green  { background: var(--hd-green-bg);  color: var(--hd-green);  }
.hd-kpi-ico.--red    { background: var(--hd-red-bg);    color: var(--hd-red);    }
.hd-kpi-ico.--blue   { background: var(--hd-blue-bg);   color: var(--hd-blue);   }
.hd-kpi-val { font-size: 1.45rem; font-weight: 900; line-height: 1; color: var(--hd-ink); }
.hd-kpi-val.--gold  { color: var(--hd-gold);  }
.hd-kpi-val.--green { color: var(--hd-green); }
.hd-kpi-val.--red   { color: var(--hd-red);   }
.hd-kpi-val.--blue  { color: var(--hd-blue);  }
.hd-kpi-val.--empty { color: var(--hd-muted); font-size: .8rem; font-weight: 600; }
.hd-kpi-note { color: var(--hd-muted); font-size: .61rem; margin-top: 2px; }

/* ── Calendar Strip ── */
.hd-cal-wrap {
    background: #fff;
    border: 1px solid var(--hd-border);
    border-radius: 14px;
    box-shadow: var(--hd-shadow);
    margin: 10px 0 0;
    padding: 10px 12px;
}
.hd-cal-label { color: var(--hd-muted); font-size: .6rem; font-weight: 720; letter-spacing: .1em; margin-bottom: 8px; text-transform: uppercase; }
.hd-cal-days { display: flex; gap: 4px; }
.hd-cal-day {
    align-items: center;
    border-radius: 10px;
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 3px;
    padding: 7px 3px;
    text-align: center;
    text-decoration: none;
    transition: background .15s;
}
.hd-cal-day.--today { background: var(--hd-gold); }
.hd-cal-wd { color: var(--hd-muted); font-size: .58rem; font-weight: 640; text-transform: uppercase; }
.hd-cal-day.--today .hd-cal-wd { color: rgba(255,255,255,.7); }
.hd-cal-num { color: var(--hd-ink); font-size: .88rem; font-weight: 780; }
.hd-cal-day.--today .hd-cal-num { color: #fff; }
.hd-cal-dots { display: flex; gap: 2px; justify-content: center; min-height: 6px; }
.hd-cal-dot { background: var(--hd-gold); border-radius: 50%; height: 5px; width: 5px; }
.hd-cal-day.--today .hd-cal-dot { background: rgba(255,255,255,.7); }
.hd-cal-day:not(.--today):hover { background: var(--hd-faint); }

/* ── Generic card ── */
.hd-card {
    background: #fff;
    border: 1px solid var(--hd-border);
    border-radius: var(--hd-radius);
    box-shadow: var(--hd-shadow);
    overflow: hidden;
}
.hd-card-head {
    align-items: center;
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 8px;
    justify-content: space-between;
    padding: 11px 15px;
}
.hd-card-title { color: var(--hd-ink); font-size: .82rem; font-weight: 760; }
.hd-card-title-icon { font-size: .76rem; margin-right: 4px; }
.hd-card-aside { color: var(--hd-gold); font-size: .73rem; font-weight: 680; text-decoration: none; }
.hd-card-aside:hover { color: var(--hd-gold-hi); }
.hd-card-body { padding: 11px 15px; }

/* ── Section 1: Needs Attention ── */
.hd-attn-item {
    align-items: flex-start;
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 10px;
    padding: 10px 15px;
}
.hd-attn-item:last-child { border-bottom: none; }
.hd-attn-rail {
    align-self: stretch;
    border-radius: 3px;
    flex-shrink: 0;
    margin-top: 2px;
    min-height: 32px;
    width: 3px;
}
.hd-attn-rail.--red    { background: var(--hd-red);    }
.hd-attn-rail.--gold   { background: var(--hd-gold);   }
.hd-attn-rail.--green  { background: var(--hd-green);  }
.hd-attn-body { flex: 1; min-width: 0; }
.hd-attn-name { color: var(--hd-ink); font-size: .82rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hd-attn-sub { color: var(--hd-muted); font-size: .69rem; margin-top: 1px; }
.hd-attn-btns { display: flex; gap: 5px; margin-top: 6px; flex-wrap: wrap; }
.hd-attn-btn {
    align-items: center;
    border: 1px solid var(--hd-border);
    border-radius: 7px;
    color: var(--hd-sub);
    display: inline-flex;
    font-size: .67rem;
    font-weight: 680;
    gap: 3px;
    padding: 3px 9px;
    text-decoration: none;
    white-space: nowrap;
}
.hd-attn-btn:hover { border-color: var(--hd-border-s); color: var(--hd-ink); }
.hd-attn-btn.--wa   { border-color: #d1f7e4; color: #128c7e; }
.hd-attn-btn.--wa:hover { background: #f0fdf4; }
.hd-attn-btn.--view { border-color: rgba(160,114,56,.3); color: var(--hd-gold); font-weight: 720; }
.hd-attn-amt { color: var(--hd-red); flex-shrink: 0; font-size: .85rem; font-weight: 800; }

/* op-level attention item (no customer actions) */
.hd-op-item {
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 10px;
    padding: 10px 15px;
    text-decoration: none;
}
.hd-op-item:last-child { border-bottom: none; }
.hd-op-item:hover { background: #fafaf8; }
.hd-op-title { color: var(--hd-ink); font-size: .8rem; font-weight: 700; }
.hd-op-sub { color: var(--hd-muted); font-size: .68rem; margin-top: 2px; }

/* ── Section 2: Today's Operations ── */
.hd-ops-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, 1fr);
}
.hd-ops-cell {
    background: var(--hd-gold-bg);
    border-radius: 10px;
    padding: 10px 12px;
}
.hd-ops-cell.--green-bg { background: var(--hd-green-bg); }
.hd-ops-cell.--red-bg   { background: var(--hd-red-bg); }
.hd-ops-lbl { color: var(--hd-muted); font-size: .63rem; font-weight: 680; text-transform: uppercase; }
.hd-ops-val { color: var(--hd-ink); font-size: 1.3rem; font-weight: 900; line-height: 1.1; }
.hd-ops-val.--muted { color: var(--hd-muted); font-size: .78rem; font-weight: 600; margin-top: 2px; }
.hd-ops-sub { color: var(--hd-muted); font-size: .62rem; margin-top: 1px; }
/* meals row */
.hd-meals-row {
    border-top: 1px solid var(--hd-border);
    display: flex;
    gap: 0;
    margin-top: 10px;
}
.hd-meal-cell {
    border-right: 1px solid var(--hd-border);
    flex: 1;
    padding: 8px 10px;
    text-align: center;
}
.hd-meal-cell:last-child { border-right: none; }
.hd-meal-lbl { color: var(--hd-muted); font-size: .6rem; font-weight: 680; text-transform: uppercase; }
.hd-meal-val { color: var(--hd-ink); font-size: 1.05rem; font-weight: 800; line-height: 1.2; }
.hd-meal-val.--empty { color: var(--hd-faint); font-size: .72rem; font-weight: 600; }
.hd-ops-empty {
    align-items: center;
    color: var(--hd-muted);
    display: flex;
    font-size: .78rem;
    gap: 8px;
    padding: 16px 15px;
}
.hd-ops-empty i { color: var(--hd-faint); font-size: 1.1rem; }

/* ── Section 3: Upcoming Events ── */
.hd-tl-item {
    align-items: center;
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 10px;
    padding: 9px 15px;
    text-decoration: none;
}
.hd-tl-item:last-child { border-bottom: none; }
.hd-tl-item:hover { background: #fafaf8; }
.hd-tl-time { color: var(--hd-gold); font-size: .7rem; font-weight: 760; flex-shrink: 0; width: 50px; }
.hd-tl-dot { background: var(--hd-faint); border-radius: 50%; flex-shrink: 0; height: 7px; width: 7px; }
.hd-tl-dot.--confirmed { background: var(--hd-green); }
.hd-tl-dot.--pending   { background: var(--hd-gold); }
.hd-tl-name { color: var(--hd-ink); flex: 1; font-size: .81rem; font-weight: 700; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hd-tl-meta { color: var(--hd-muted); font-size: .67rem; flex-shrink: 0; text-align: right; }
.hd-tl-date { color: var(--hd-muted); font-size: .67rem; }

/* ── Section 4: Recent Activity ── */
.hd-feed-item {
    align-items: center;
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 8px 15px;
    text-decoration: none;
}
.hd-feed-item:last-child { border-bottom: none; }
.hd-feed-item:hover { background: #fafaf8; }
.hd-feed-title { color: var(--hd-ink); font-size: .8rem; font-weight: 700; }
.hd-feed-meta  { color: var(--hd-muted); font-size: .67rem; margin-top: 1px; }
.hd-feed-amt   { color: var(--hd-ink); font-size: .83rem; font-weight: 760; flex-shrink: 0; text-align: right; }
.hd-feed-pay   { color: var(--hd-muted); font-size: .62rem; text-align: right; }

/* ── Pending Collection card ── */
.hd-collect-card { border: 1px solid rgba(194,96,10,.2); }
.hd-collect-card .hd-card-head { background: #fff7ed; border-color: rgba(194,96,10,.15); }
.hd-collect-body { padding: 12px 15px; }
.hd-collect-amt { color: var(--hd-orange); font-size: 1.7rem; font-weight: 900; line-height: 1; }
.hd-collect-meta { color: var(--hd-muted); font-size: .7rem; margin: 4px 0 10px; }
.hd-collect-meta b { color: var(--hd-ink); font-weight: 720; }
.hd-collect-meta span { margin-right: 10px; }
.hd-collect-top {
    background: rgba(194,96,10,.06);
    border: 1px solid rgba(194,96,10,.12);
    border-radius: 8px;
    font-size: .72rem;
    margin-bottom: 10px;
    padding: 6px 10px;
}
.hd-collect-top i { color: var(--hd-orange); margin-right: 4px; }
.hd-collect-btn {
    background: var(--hd-orange);
    border-radius: 9px;
    color: #fff;
    display: inline-block;
    font-size: .75rem;
    font-weight: 720;
    padding: 7px 16px;
    text-decoration: none;
}
.hd-collect-btn:hover { background: #a85209; color: #fff; }

/* ── Status chips ── */
.hd-chip {
    border-radius: 100px;
    display: inline-block;
    font-size: .6rem;
    font-weight: 720;
    padding: 2px 7px;
    white-space: nowrap;
}
.hd-chip.--emerald  { background: #f0fdf4; color: #166534; }
.hd-chip.--red      { background: #fef2f2; color: #991b1b; }
.hd-chip.--neutral  { background: #f3f4f6; color: #374151; }
.hd-chip.--bluegray { background: #f1f5f9; color: #475569; }
.hd-chip.--gold     { background: var(--hd-gold-bg); color: var(--hd-gold); }

/* ── Sidebar: Kitchen Load ── */
.hd-kitchen-section { padding: 10px 15px; }
.hd-section-lbl { color: var(--hd-muted); font-size: .6rem; font-weight: 720; letter-spacing: .06em; margin-bottom: 7px; text-transform: uppercase; }
.hd-kitchen-row { align-items: center; border-bottom: 1px solid var(--hd-border); display: flex; gap: 8px; padding: 7px 0; }
.hd-kitchen-row:last-child { border-bottom: none; }
.hd-kitchen-meal-lbl { color: var(--hd-sub); flex: 1; font-size: .76rem; font-weight: 640; }
.hd-kitchen-meal-val { color: var(--hd-ink); font-size: .88rem; font-weight: 800; }
.hd-kitchen-empty { align-items: center; color: var(--hd-muted); display: flex; font-size: .73rem; gap: 6px; padding: 10px 0; }

/* ── Sidebar: Hall State ── */
.hd-hall-row {
    align-items: center;
    border-bottom: 1px solid var(--hd-border);
    display: flex;
    gap: 9px;
    padding: 8px 15px;
}
.hd-hall-row:last-child { border-bottom: none; }
.hd-hall-indicator {
    font-size: .72rem;
    flex-shrink: 0;
}
.hd-hall-name { color: var(--hd-ink); flex: 1; font-size: .79rem; font-weight: 700; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hd-hall-status { color: var(--hd-muted); font-size: .67rem; flex-shrink: 0; text-align: right; }
.hd-hall-status.--busy   { color: var(--hd-red); font-weight: 680; }
.hd-hall-status.--avail  { color: var(--hd-green); font-weight: 680; }

/* ── Quick Actions (full-width grid) ── */
.hd-quick-grid {
    display: grid;
    gap: 7px;
    grid-template-columns: repeat(3, 1fr);
}
.hd-quick-btn {
    align-items: center;
    background: var(--hd-gold-bg);
    border: 1px solid rgba(160,114,56,.18);
    border-radius: 10px;
    color: var(--hd-gold);
    display: flex;
    flex-direction: column;
    font-size: .68rem;
    font-weight: 700;
    gap: 5px;
    padding: 10px 8px;
    text-align: center;
    text-decoration: none;
    transition: background .15s;
}
.hd-quick-btn i { font-size: 1rem; }
.hd-quick-btn:hover { background: #fdf0d8; color: var(--hd-gold); }
.hd-quick-btn.--primary {
    background: var(--hd-ink);
    border-color: var(--hd-ink);
    color: #fff;
}
.hd-quick-btn.--primary:hover { background: #2d2620; color: #fff; }
.hd-quick-btn.--red { background: var(--hd-red-bg); border-color: rgba(185,28,28,.18); color: var(--hd-red); }
.hd-quick-btn.--red:hover { background: #fee2e2; }

/* ── Layout ── */
.hd-page  { display: flex; flex-direction: column; gap: 10px; padding: 0 0 88px; }
.hd-grid  { display: grid; gap: 10px; grid-template-columns: 1fr; }

/* ── Mobile bottom bar ── */
.hd-bottom-bar {
    align-items: center;
    background: rgba(26,20,16,.96);
    border-top: 1px solid rgba(255,255,255,.07);
    bottom: 0;
    display: flex;
    gap: 7px;
    left: 0;
    padding: 9px 14px;
    position: fixed;
    right: 0;
    z-index: 200;
}
.hd-bottom-btn {
    align-items: center;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px;
    color: rgba(255,253,250,.78);
    display: flex;
    flex: 1;
    font-size: .72rem;
    font-weight: 680;
    gap: 5px;
    justify-content: center;
    padding: 8px 6px;
    text-decoration: none;
}
.hd-bottom-btn:hover { background: rgba(255,255,255,.07); color: #fffdfa; }
.hd-bottom-btn.--primary { background: var(--hd-gold); border-color: var(--hd-gold); color: #fff; font-weight: 720; }
.hd-bottom-btn.--primary:hover { background: var(--hd-gold-hi); color: #fff; }

@media (min-width: 768px) {
    .hd-kpi-strip      { grid-template-columns: repeat(4, 1fr); }
    .hd-grid           { grid-template-columns: 1fr 320px; }
    .hd-page           { padding: 16px 0 20px; }
    .hd-bottom-bar     { display: none; }
    .hd-quick-grid     { grid-template-columns: repeat(3, 1fr); }
}
</style>
@endpush

<div class="hd-page px-3 px-md-4">

    {{-- ── Hero ── --}}
    <div class="hd-hero">
        <div class="hd-hero-main">
            <div class="hd-greeting">{{ $greeting }}</div>
            <div class="hd-hero-title">{{ now()->format('l, d F') }}</div>
            <div class="hd-hero-bullets">
                @if($operations['today_bookings'] > 0)
                <span class="hd-hero-bullet">
                    <i class="bi bi-calendar-event"></i>
                    {{ $operations['today_bookings'] }} Event{{ $operations['today_bookings'] !== 1 ? 's' : '' }} Today
                </span>
                @if($todayGuests > 0)
                <span class="hd-hero-bullet">
                    <i class="bi bi-people"></i>{{ number_format($todayGuests) }} Guests
                </span>
                @endif
                @else
                <span class="hd-hero-bullet" style="color:rgba(255,253,250,.38)">
                    <i class="bi bi-calendar-x" style="color:rgba(255,253,250,.25)"></i>No events scheduled today
                </span>
                @endif
                @if($operations['pending_balance'] > 0)
                <span class="hd-hero-bullet --red">
                    <i class="bi bi-exclamation-circle"></i>₹{{ number_format($operations['pending_balance'], 0) }} Pending
                </span>
                @endif
            </div>
        </div>
        @if($nextEvent)
        <div class="hd-next-bar">
            <div>
                <div class="hd-next-label">Next Event</div>
                <div class="hd-next-name">{{ $nextEvent->customer_name }}</div>
                <div class="hd-next-time">{{ \Carbon\Carbon::parse($nextEvent->start_time)->format('h:i A') }} · {{ $nextEvent->location_label }}</div>
            </div>
            <a href="{{ route('hall.bookings.show', $nextEvent) }}" class="hd-next-view">View →</a>
        </div>
        @else
        <div class="hd-next-bar">
            <div>
                <div class="hd-next-label">Today</div>
                <div class="hd-next-name" style="color:rgba(255,253,250,.38)">No events scheduled</div>
            </div>
            <a href="{{ route('hall.bookings.create') }}" class="hd-next-view">+ New</a>
        </div>
        @endif
    </div>

    {{-- ── KPI Strip ── --}}
    <div class="hd-kpi-strip">
        <div class="hd-kpi">
            <div class="hd-kpi-head">
                <span class="hd-kpi-lbl">Today's Events</span>
                <span class="hd-kpi-ico --gold"><i class="bi bi-calendar-event"></i></span>
            </div>
            @if($operations['today_bookings'] > 0)
            <div class="hd-kpi-val --gold">{{ $operations['today_bookings'] }}</div>
            <div class="hd-kpi-note">{{ $operations['upcoming_bookings'] }} more this week</div>
            @else
            <div class="hd-kpi-val --empty">No events</div>
            <div class="hd-kpi-note">{{ $operations['upcoming_bookings'] }} upcoming</div>
            @endif
        </div>
        <div class="hd-kpi">
            <div class="hd-kpi-head">
                <span class="hd-kpi-lbl">Guests Today</span>
                <span class="hd-kpi-ico --green"><i class="bi bi-people"></i></span>
            </div>
            @if($todayGuests > 0)
            <div class="hd-kpi-val --green">{{ number_format($todayGuests) }}</div>
            <div class="hd-kpi-note">{{ number_format($operations['catering_load']) }} this week</div>
            @else
            <div class="hd-kpi-val --empty">No covers</div>
            <div class="hd-kpi-note">Hall available</div>
            @endif
        </div>
        <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="hd-kpi">
            <div class="hd-kpi-head">
                <span class="hd-kpi-lbl">Pending Collect.</span>
                <span class="hd-kpi-ico --red"><i class="bi bi-credit-card"></i></span>
            </div>
            @if($operations['pending_payments'] > 0)
            <div class="hd-kpi-val --red">{{ $operations['pending_payments'] }}</div>
            <div class="hd-kpi-note">₹{{ number_format($operations['pending_balance'], 0) }} due</div>
            @else
            <div class="hd-kpi-val --empty">All clear</div>
            <div class="hd-kpi-note">No dues</div>
            @endif
        </a>
        <div class="hd-kpi">
            <div class="hd-kpi-head">
                <span class="hd-kpi-lbl">Month Revenue</span>
                <span class="hd-kpi-ico --blue"><i class="bi bi-currency-rupee"></i></span>
            </div>
            @if($operations['month_revenue'] > 0)
            <div class="hd-kpi-val --blue">₹{{ number_format($operations['month_revenue'] / 1000, 1) }}K</div>
            <div class="hd-kpi-note">{{ now()->format('M Y') }}</div>
            @else
            <div class="hd-kpi-val --empty">₹0</div>
            <div class="hd-kpi-note">{{ now()->format('M Y') }}</div>
            @endif
        </div>
    </div>

    {{-- ── 5-Day Calendar Strip ── --}}
    <div class="hd-cal-wrap">
        <div class="hd-cal-label">This Week</div>
        <div class="hd-cal-days">
            @foreach($calStrip as $i => $day)
            <a href="{{ route('hall.bookings.calendar') }}?date={{ $day['date']->toDateString() }}"
               class="hd-cal-day {{ $i === 0 ? '--today' : '' }}">
                <span class="hd-cal-wd">{{ $day['date']->format('D') }}</span>
                <span class="hd-cal-num">{{ $day['day'] }}</span>
                <div class="hd-cal-dots">
                    @for($d = 0; $d < min($day['bookings'], 3); $d++)
                    <span class="hd-cal-dot"></span>
                    @endfor
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ── Main Grid ── --}}
    <div class="hd-grid">

        {{-- ── Left: main column ── --}}
        <div class="d-flex flex-column gap-3">

            {{-- Section 1: Needs Attention --}}
            <div class="hd-card">
                <div class="hd-card-head">
                    <span class="hd-card-title">
                        <i class="bi bi-exclamation-triangle-fill hd-card-title-icon" style="color:var(--hd-red)"></i>Needs Attention
                    </span>
                    @if($operations['pending_payments'] > 0)
                    <span style="color:var(--hd-red);font-size:.7rem;font-weight:720">{{ $operations['pending_payments'] }} pending</span>
                    @endif
                </div>

                @php
                    $attnCx  = $pendingPaymentBookings->take(3);
                    $opExtra = $attentionItems
                        ->reject(fn($i) => str_starts_with($i['title'], 'Balance payments'))
                        ->take(max(0, 3 - $attnCx->count()));
                @endphp

                @forelse($attnCx as $ab)
                @php
                    $abWa   = 'https://wa.me/91' . preg_replace('/\D/', '', $ab->customer_mobile ?? '');
                    $abTel  = 'tel:' . preg_replace('/\D/', '', $ab->customer_mobile ?? '');
                @endphp
                <div class="hd-attn-item">
                    <div class="hd-attn-rail --red"></div>
                    <div class="hd-attn-body">
                        <div class="hd-attn-name">{{ $ab->customer_name }}</div>
                        <div class="hd-attn-sub">
                            {{ $ab->booking_date->format('d M') }}
                            @if($ab->booking_date->lt($today)) · <b style="color:var(--hd-red)">Overdue</b>@endif
                            · ₹{{ number_format(max(0, $ab->balance_amount), 0) }} due · {{ ucfirst($ab->payment_status) }}
                        </div>
                        <div class="hd-attn-btns">
                            @if($ab->customer_mobile)
                            <a href="{{ $abTel }}" class="hd-attn-btn"><i class="bi bi-telephone-fill"></i> Call</a>
                            <a href="{{ $abWa }}" class="hd-attn-btn --wa" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WA</a>
                            @endif
                            <a href="{{ route('hall.bookings.show', $ab) }}#record-payment" class="hd-attn-btn --view">View</a>
                        </div>
                    </div>
                    <div class="hd-attn-amt">₹{{ number_format(max(0, $ab->balance_amount), 0) }}</div>
                </div>
                @empty
                @endforelse

                @foreach($opExtra as $item)
                <a href="{{ $item['url'] }}" class="hd-op-item">
                    <div class="hd-attn-rail --{{ $item['tone'] === 'danger' ? 'red' : ($item['tone'] === 'emerald' ? 'green' : 'gold') }}"></div>
                    <div>
                        <div class="hd-op-title">{{ $item['title'] }}</div>
                        <div class="hd-op-sub">{{ $item['body'] }}</div>
                    </div>
                </a>
                @endforeach

                @if($attnCx->isEmpty() && $opExtra->isEmpty())
                <div class="hd-op-item" style="pointer-events:none">
                    <div class="hd-attn-rail --green"></div>
                    <div>
                        <div class="hd-op-title" style="color:var(--hd-green)">Operations are calm</div>
                        <div class="hd-op-sub">No urgent payment or occupancy issues.</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Section 2: Today's Operations --}}
            <div class="hd-card">
                <div class="hd-card-head">
                    <span class="hd-card-title">
                        <i class="bi bi-lightning-charge-fill hd-card-title-icon" style="color:var(--hd-gold)"></i>Today's Operations
                    </span>
                    <span style="color:var(--hd-muted);font-size:.7rem">{{ now()->format('d M') }}</span>
                </div>
                @if($todayList->isNotEmpty())
                <div style="padding: 10px 15px;">
                    <div class="hd-ops-grid">
                        <div class="hd-ops-cell">
                            <div class="hd-ops-lbl">Events</div>
                            <div class="hd-ops-val">{{ $todayList->count() }}</div>
                            <div class="hd-ops-sub">{{ number_format($todayGuests) }} guests total</div>
                        </div>
                        <div class="hd-ops-cell {{ $operations['food_only_today'] > 0 ? '--green-bg' : '' }}">
                            <div class="hd-ops-lbl">Food Only</div>
                            @if($operations['food_only_today'] > 0)
                            <div class="hd-ops-val">{{ $operations['food_only_today'] }}</div>
                            <div class="hd-ops-sub">catering orders</div>
                            @else
                            <div class="hd-ops-val --muted">None today</div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Meal breakdown --}}
                <div class="hd-meals-row">
                    <div class="hd-meal-cell">
                        <div class="hd-meal-lbl">Breakfast</div>
                        @if($kitchenLoad['today']['breakfast'] > 0)
                        <div class="hd-meal-val">{{ number_format($kitchenLoad['today']['breakfast']) }}</div>
                        @else
                        <div class="hd-meal-val --empty">—</div>
                        @endif
                    </div>
                    <div class="hd-meal-cell">
                        <div class="hd-meal-lbl">Lunch</div>
                        @if($kitchenLoad['today']['lunch'] > 0)
                        <div class="hd-meal-val">{{ number_format($kitchenLoad['today']['lunch']) }}</div>
                        @else
                        <div class="hd-meal-val --empty">—</div>
                        @endif
                    </div>
                    <div class="hd-meal-cell">
                        <div class="hd-meal-lbl">Dinner</div>
                        @if($kitchenLoad['today']['dinner'] > 0)
                        <div class="hd-meal-val">{{ number_format($kitchenLoad['today']['dinner']) }}</div>
                        @else
                        <div class="hd-meal-val --empty">—</div>
                        @endif
                    </div>
                </div>
                {{-- Today's schedule timeline --}}
                @foreach($todayList as $b)
                <a href="{{ route('hall.bookings.show', $b) }}" class="hd-tl-item">
                    <span class="hd-tl-time">{{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}</span>
                    <span class="hd-tl-dot {{ $b->status === 'confirmed' ? '--confirmed' : '--pending' }}"></span>
                    <span class="hd-tl-name">{{ $b->customer_name }}</span>
                    <span class="hd-tl-meta">{{ $b->location_label }} · {{ number_format($b->number_of_people) }}</span>
                </a>
                @endforeach
                @else
                <div class="hd-ops-empty">
                    <i class="bi bi-calendar2-x"></i>
                    No operations scheduled today. Ready for new bookings.
                </div>
                @endif
            </div>

            {{-- Pending Collection (only when balance exists) --}}
            @if($operations['pending_balance'] > 0)
            <div class="hd-card hd-collect-card">
                <div class="hd-card-head">
                    <span class="hd-card-title"><i class="bi bi-clock-history hd-card-title-icon" style="color:var(--hd-orange)"></i>Pending Collection</span>
                    @if($overdueCount > 0)
                    <span class="hd-chip --red">{{ $overdueCount }} overdue</span>
                    @endif
                </div>
                <div class="hd-collect-body">
                    <div class="hd-collect-amt">₹{{ number_format($operations['pending_balance'], 0) }}</div>
                    <div class="hd-collect-meta">
                        <span><b>{{ $operations['pending_payments'] }}</b> customer{{ $operations['pending_payments'] !== 1 ? 's' : '' }}</span>
                        @if($oldestPending && $oldestDays > 0)
                        <span>Oldest: <b>{{ $oldestDays }} day{{ $oldestDays !== 1 ? 's' : '' }}</b></span>
                        @endif
                    </div>
                    @if($topDebtor)
                    <div class="hd-collect-top">
                        <i class="bi bi-person-fill"></i>
                        Highest: <b>{{ $topDebtor->customer_name }}</b> — ₹{{ number_format(max(0, $topDebtor->balance_amount), 0) }}
                    </div>
                    @endif
                    <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="hd-collect-btn">
                        Collect Now →
                    </a>
                </div>
            </div>
            @endif

            {{-- Section 3: Upcoming Events --}}
            @php
                $upcoming = $nextEvents->filter(fn($b) => $b->booking_date->gt($today))->take(5)->values();
            @endphp
            @if($upcoming->isNotEmpty())
            <div class="hd-card">
                <div class="hd-card-head">
                    <span class="hd-card-title"><i class="bi bi-calendar3 hd-card-title-icon" style="color:var(--hd-blue)"></i>Upcoming Events</span>
                    <a href="{{ route('hall.bookings.calendar') }}" class="hd-card-aside">Calendar →</a>
                </div>
                @foreach($upcoming as $ub)
                <a href="{{ route('hall.bookings.show', $ub) }}" class="hd-tl-item">
                    <span class="hd-tl-time" style="color:var(--hd-blue)">{{ $ub->booking_date->format('d M') }}</span>
                    <span class="hd-tl-dot {{ $ub->status === 'confirmed' ? '--confirmed' : '--pending' }}"></span>
                    <span class="hd-tl-name">{{ $ub->customer_name }}</span>
                    <span class="hd-tl-meta">
                        {{ \Carbon\Carbon::parse($ub->start_time)->format('h:i A') }} · {{ number_format($ub->number_of_people) }}
                    </span>
                </a>
                @endforeach
            </div>
            @endif

            {{-- Section 4: Recent Activity --}}
            <div class="hd-card">
                <div class="hd-card-head">
                    <span class="hd-card-title">Recent Activity</span>
                    <a href="{{ route('hall.bookings.index') }}" class="hd-card-aside">View All →</a>
                </div>
                @forelse($recentBookings->take(3) as $rb)
                @php
                    $ct = ['confirmed' => 'emerald', 'completed' => 'bluegray', 'cancelled' => 'red'][$rb->status] ?? 'neutral';
                    $cl = \App\Models\HallBooking::statuses()[$rb->status] ?? ucfirst($rb->status);
                @endphp
                <a href="{{ route('hall.bookings.show', $rb) }}" class="hd-feed-item">
                    <div style="min-width:0;flex:1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="hd-feed-title">{{ $rb->customer_name }}</div>
                            <span class="hd-chip --{{ $ct }}">{{ $cl }}</span>
                        </div>
                        <div class="hd-feed-meta">
                            {{ $eventTypes[$rb->event_type] ?? ucfirst($rb->event_type) }} · {{ $rb->booking_date->format('d M') }}
                        </div>
                    </div>
                    <div>
                        <div class="hd-feed-amt">₹{{ number_format($rb->total_amount, 0) }}</div>
                        <div class="hd-feed-pay">{{ ucfirst($rb->payment_status) }}</div>
                    </div>
                </a>
                @empty
                <div class="hd-card-body" style="color:var(--hd-muted);font-size:.78rem">No recent bookings.</div>
                @endforelse
            </div>

            {{-- Section 5: Quick Actions (mobile: inline, desktop: sidebar) --}}
            <div class="hd-card d-md-none">
                <div class="hd-card-head">
                    <span class="hd-card-title">Quick Actions</span>
                </div>
                <div class="hd-card-body">
                    <div class="hd-quick-grid">
                        <a href="{{ route('hall.bookings.create') }}" class="hd-quick-btn --primary">
                            <i class="bi bi-plus-circle"></i>New Booking
                        </a>
                        <a href="{{ route('hall.bookings.calendar') }}" class="hd-quick-btn">
                            <i class="bi bi-calendar3"></i>Calendar
                        </a>
                        <a href="{{ route('hall.bookings.kitchen') }}" class="hd-quick-btn">
                            <i class="bi bi-cup-hot"></i>Kitchen
                        </a>
                        <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="hd-quick-btn --red">
                            <i class="bi bi-credit-card"></i>Follow-up
                        </a>
                        <a href="{{ route('meal-register.entries.index') }}" class="hd-quick-btn">
                            <i class="bi bi-journal-check"></i>Meal Register
                        </a>
                        <a href="{{ route('hall.bookings.index') }}" class="hd-quick-btn">
                            <i class="bi bi-star"></i>Reviews
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Right: sidebar (desktop only) ── --}}
        <div class="d-flex flex-column gap-3">

            {{-- Kitchen Load: Tomorrow --}}
            <div class="hd-card">
                <div class="hd-card-head">
                    <span class="hd-card-title"><i class="bi bi-cup-hot hd-card-title-icon"></i>Kitchen Load</span>
                    <span style="color:var(--hd-muted);font-size:.7rem">Tomorrow</span>
                </div>
                <div class="hd-kitchen-section">
                    @if($kitchenLoad['tomorrow']['total'] > 0)
                    @if($kitchenLoad['tomorrow']['breakfast'] > 0)
                    <div class="hd-kitchen-row">
                        <span class="hd-kitchen-meal-lbl">Breakfast</span>
                        <span class="hd-kitchen-meal-val">{{ number_format($kitchenLoad['tomorrow']['breakfast']) }}</span>
                    </div>
                    @endif
                    @if($kitchenLoad['tomorrow']['lunch'] > 0)
                    <div class="hd-kitchen-row">
                        <span class="hd-kitchen-meal-lbl">Lunch</span>
                        <span class="hd-kitchen-meal-val">{{ number_format($kitchenLoad['tomorrow']['lunch']) }}</span>
                    </div>
                    @endif
                    @if($kitchenLoad['tomorrow']['dinner'] > 0)
                    <div class="hd-kitchen-row">
                        <span class="hd-kitchen-meal-lbl">Dinner</span>
                        <span class="hd-kitchen-meal-val">{{ number_format($kitchenLoad['tomorrow']['dinner']) }}</span>
                    </div>
                    @endif
                    @else
                    <div class="hd-kitchen-empty">
                        <i class="bi bi-moon-stars"></i>No meals planned tomorrow
                    </div>
                    @endif
                </div>
            </div>

            {{-- Hall State --}}
            <div class="hd-card">
                <div class="hd-card-head">
                    <span class="hd-card-title">Hall Status</span>
                    <span style="color:var(--hd-muted);font-size:.7rem">{{ now()->format('d M') }}</span>
                </div>
                @foreach($hallStatuses as $hs)
                @php
                    $isBusy    = $hs['state'] === 'Busy today';
                    $indicator = $isBusy ? '🔴' : ($hs['state'] === 'Upcoming event' ? '🟡' : '🟢');
                    $stLabel   = $isBusy ? 'Busy today' : ($hs['next_booking'] ? 'Next: ' . $hs['next_booking']->booking_date->format('d M') : 'Available');
                    $stCls     = $isBusy ? '--busy' : ($hs['state'] === 'Available' ? '--avail' : '');
                @endphp
                <div class="hd-hall-row">
                    <span class="hd-hall-indicator">{{ $indicator }}</span>
                    <div class="hd-hall-name">{{ $hs['hall']->name }}</div>
                    <div class="hd-hall-status {{ $stCls }}">{{ $stLabel }}</div>
                </div>
                @endforeach
            </div>

            {{-- Quick Actions (desktop sidebar) --}}
            <div class="hd-card d-none d-md-block">
                <div class="hd-card-head">
                    <span class="hd-card-title">Quick Actions</span>
                </div>
                <div class="hd-card-body">
                    <div class="hd-quick-grid">
                        <a href="{{ route('hall.bookings.create') }}" class="hd-quick-btn --primary">
                            <i class="bi bi-plus-circle"></i>New Booking
                        </a>
                        <a href="{{ route('hall.bookings.calendar') }}" class="hd-quick-btn">
                            <i class="bi bi-calendar3"></i>Calendar
                        </a>
                        <a href="{{ route('hall.bookings.kitchen') }}" class="hd-quick-btn">
                            <i class="bi bi-cup-hot"></i>Kitchen
                        </a>
                        <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}" class="hd-quick-btn --red">
                            <i class="bi bi-credit-card"></i>Follow-up
                        </a>
                        <a href="{{ route('meal-register.entries.index') }}" class="hd-quick-btn">
                            <i class="bi bi-journal-check"></i>Meal Register
                        </a>
                        <a href="{{ route('hall.bookings.index') }}" class="hd-quick-btn">
                            <i class="bi bi-star"></i>Reviews
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- Mobile bottom bar --}}
<div class="hd-bottom-bar">
    <a href="{{ route('hall.bookings.create') }}" class="hd-bottom-btn --primary"><i class="bi bi-plus-lg"></i> New</a>
    <a href="{{ route('hall.bookings.calendar') }}" class="hd-bottom-btn"><i class="bi bi-calendar3"></i> Calendar</a>
    <a href="{{ route('hall.bookings.kitchen') }}" class="hd-bottom-btn"><i class="bi bi-cup-hot"></i> Kitchen</a>
</div>

</x-admin-layout>
