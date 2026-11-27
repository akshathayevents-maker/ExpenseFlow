<x-admin-layout title="Admin Dashboard">
@php
    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $name     = explode(' ', auth()->user()->name)[0];
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $att      = $attendanceToday;
    $attTotal = max(1, array_sum($att));
    $processed = $stats['total_processed'] ?: 1;
    $approvalRate = (int) round($stats['approved_total'] / $processed * 100);
    $prioTone = ['urgent' => 'bad', 'high' => 'warn', 'medium' => 'info', 'low' => 'muted'];
    $time = fn ($t) => $t ? \Illuminate\Support\Carbon::parse($t)->format('g:i A') : '—';
@endphp

@push('styles')
<style>
/* Admin dashboard — same language as Purchases / Meal plans / Event requests:
   header, coloured attention strip, panels with a 3px top accent, rail. */
.ad{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.ad a:focus-visible{outline:2px solid var(--ef-emerald);outline-offset:2px}
.ad-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.ad-title{flex:1 1 280px;min-width:0}
.ad-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.ad-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.ad-title p b{color:var(--ef-ink)}
.ad-title p .--warn{color:#b4531c}
.ad-acts{display:flex;gap:8px;flex-wrap:wrap}
.ad-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.ad-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.ad-btn .bi{color:var(--ef-gold)}
.ad-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.ad-btn.--main .bi{color:#f0c97a}
@media (max-width:639.98px){.ad-acts{width:100%;display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.ad-title h1{font-size:1.25rem}}

/* attention strip */
.ad-q{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.ad-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;min-width:0;background:linear-gradient(var(--tint,transparent),var(--tint,transparent)),var(--ef-surface)}
.ad-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.ad-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ad-qi small .bi{color:var(--acc)}
.ad-qi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.ad-qi span{font-size:.7rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ad-qi.--zero strong{color:var(--ef-faint)}
.ad-qi.--zero{--tint:transparent}
.ad-qi.--hot::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.ad-qi[data-k=expenses]{--acc:#9a6413;--tint:rgba(200,144,42,.08)}
.ad-qi[data-k=overtime]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.ad-qi[data-k=advances]{--acc:#6b5bb5;--tint:rgba(107,91,181,.05)}
.ad-qi[data-k=leave]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.ad-qi[data-k=regularizations]{--acc:#1f6f6a;--tint:rgba(31,111,106,.06)}
.ad-qi[data-k=wallets]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.05)}
/* all six stay visible (no swipe) — 3 × 2 on tablets and phones */
@media (max-width:1099.98px){.ad-q{grid-template-columns:repeat(3,minmax(0,1fr))}.ad-qi{padding:8px 10px}.ad-qi strong{font-size:1.15rem}}
@media (max-width:399.98px){.ad-qi span{display:none}.ad-qi small{font-size:.68rem}}

/* layout */
.ad-body{display:grid;gap:14px}
@media (min-width:1300px){.ad-body{grid-template-columns:minmax(0,1fr) 340px;align-items:start}}
.ad-main,.ad-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:700px) and (max-width:1299.98px){.ad-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.ad-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}
.ad-ph{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.ad-ph h2{margin:0;font-size:.95rem;font-weight:800;color:var(--ef-ink);display:flex;align-items:center;gap:8px;min-width:0}
.ad-ph h2 .bi{color:var(--acc)}
.ad-ph h2 em{font-style:normal;font-size:.68rem;font-weight:800;padding:2px 8px;border-radius:8px;background:rgba(200,144,42,.14);color:#8a5a10}
.ad-ph a{margin-left:auto;font-size:.78rem;font-weight:700;color:var(--ef-gold);text-decoration:none;white-space:nowrap}
.ad-pb{padding:6px 14px 12px}
.ad-empty{padding:22px 14px;text-align:center;color:var(--ef-muted);font-size:.86rem}
.ad-empty .bi{display:block;font-size:1.6rem;color:var(--ef-faint);margin-bottom:4px}

/* rows (events + requests) */
.ad-row{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:10px 14px;border-top:1px solid var(--ef-border);text-decoration:none;color:var(--ef-ink);border-left:3px solid var(--tone,transparent)}
.ad-row:first-child{border-top:0}
.ad-row:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.ad-row[data-tone=bad]{--tone:var(--ef-danger)}
.ad-row[data-tone=warn]{--tone:#d89a3d}
.ad-row[data-tone=info]{--tone:#3b6ea5}
.ad-row[data-tone=food]{--tone:#d66e28}
.ad-row[data-tone=hall]{--tone:#3b6ea5}
.ad-row[data-tone=both]{--tone:var(--ef-emerald)}
.ad-time{width:62px;text-align:center;line-height:1.15;font-weight:800;font-size:.84rem;font-variant-numeric:tabular-nums}
.ad-time small{display:block;font-size:.66rem;font-weight:600;color:var(--ef-muted)}
.ad-av{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(200,144,42,.12);color:#9a6413;font-weight:800;font-size:.78rem}
.ad-t{display:block;font-weight:750;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ad-s{display:block;font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ad-r{text-align:right;white-space:nowrap}
.ad-r b{display:block;font-size:.92rem;font-variant-numeric:tabular-nums}
.ad-r small{font-size:.7rem;color:var(--ef-muted)}
.ad-chip{display:inline-block;padding:1px 7px;border-radius:7px;font-size:.64rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;vertical-align:1px}
.ad-chip.--bad{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.ad-chip.--warn{background:rgba(200,144,42,.15);color:#8a5a10}
.ad-chip.--info{background:rgba(59,110,165,.1);color:#2c5784}
.ad-chip.--muted{background:var(--ef-bg-subtle);color:var(--ef-muted)}
.ad-foot{display:flex;flex-wrap:wrap;gap:6px 14px;padding:9px 14px;border-top:1px solid var(--ef-border);background:var(--ef-bg-subtle);font-size:.78rem;color:var(--ef-muted)}
.ad-foot a{color:var(--ef-ink);font-weight:700;text-decoration:none}
.ad-foot a:hover{text-decoration:underline}
@media (max-width:479.98px){.ad-row{grid-template-columns:auto minmax(0,1fr)}.ad-r{grid-column:2;text-align:left;display:flex;gap:8px;align-items:center}.ad-r b{display:inline}}

/* rail widgets */
.ad-bar{display:flex;height:8px;border-radius:6px;overflow:hidden;background:var(--ef-bg-subtle);margin:8px 0 10px}
.ad-bar i{display:block;height:100%}
.ad-legend{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px 10px;font-size:.8rem}
.ad-legend span{display:flex;align-items:center;gap:6px;color:var(--ef-muted)}
.ad-legend span b{margin-left:auto;color:var(--ef-ink);font-variant-numeric:tabular-nums}
.ad-legend i{width:8px;height:8px;border-radius:50%;flex:0 0 auto}
.ad-kv{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-top:1px solid var(--ef-border);font-size:.82rem;color:var(--ef-muted);text-decoration:none}
.ad-kv:first-child{border-top:0}
.ad-kv b{color:var(--ef-ink);font-variant-numeric:tabular-nums;font-size:.9rem}
a.ad-kv:hover b{text-decoration:underline}
.ad-kv b.--bad{color:var(--ef-danger)}
.ad-kv b.--warn{color:#9a6413}
.ad-kv b.--zero{color:var(--ef-faint)}
.ad-meter{height:5px;border-radius:4px;background:var(--ef-bg-subtle);overflow:hidden;margin-top:-2px;margin-bottom:6px}
.ad-meter i{display:block;height:100%;background:var(--ef-emerald)}
.ad-quick{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding:12px 14px}
.ad-quick a{display:flex;align-items:center;gap:8px;min-height:42px;padding:8px 10px;border-radius:10px;border:1px solid var(--ef-border);font-size:.8rem;font-weight:700;color:var(--ef-ink);text-decoration:none;min-width:0}
.ad-quick a .bi{color:var(--ef-gold)}
.ad-quick a:hover{border-color:var(--ef-gold)}
.ad-quick a span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>
@endpush

<div class="ad">
    <header class="ad-head">
        <div class="ad-title">
            <h1>{{ $greeting }}, {{ $name }}</h1>
            <p>{{ now()->format('l, j F Y') }}
                · @if($needsActionTotal > 0)<b class="--warn">{{ $needsActionTotal }} waiting for you</b>@else<b>all caught up</b>@endif
                · {{ $ops['bookings_today'] }} {{ Str::plural('event', $ops['bookings_today']) }} today</p>
        </div>
        <div class="ad-acts">
            <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}" class="ad-btn"><i class="bi bi-check2-square"></i> Review queue</a>
            <a href="{{ route('hall.bookings.create') }}" class="ad-btn --main"><i class="bi bi-plus-lg"></i> New booking</a>
        </div>
    </header>

    <nav class="ad-q" aria-label="Needs your attention">
        @foreach($attentionItems as $it)
            <a href="{{ $it['url'] }}" data-k="{{ $it['key'] }}" class="ad-qi {{ $it['count'] ? '--hot' : '--zero' }}" data-attn="{{ $it['key'] }}">
                <small><i class="bi {{ $it['icon'] }}"></i>{{ $it['label'] }}</small>
                <strong>{{ $it['count'] }}</strong>
                <span>{{ $it['count'] ? $it['cta'] : 'Nothing pending' }}</span>
            </a>
        @endforeach
    </nav>

    <div class="ad-body">
        <div class="ad-main">
            {{-- Today's events --}}
            <section class="ad-panel" style="--acc:#1f6f6a;--tint:rgba(31,111,106,.05)">
                <div class="ad-ph">
                    <h2><i class="bi bi-calendar-event"></i> Today's events @if($ops['guests_today'])<em>{{ number_format($ops['guests_today']) }} guests</em>@endif</h2>
                    <a href="{{ route('hall.bookings.kitchen') }}">Kitchen schedule →</a>
                </div>
                <div>
                    @forelse($todayBookings as $b)
                        <a class="ad-row" data-tone="{{ $b->booking_type === 'food_only' ? 'food' : ($b->booking_type === 'hall_only' ? 'hall' : 'both') }}" href="{{ route('hall.bookings.show', $b) }}" data-booking>
                            <span class="ad-time">{{ $time($b->start_time) }}<small>→ {{ $time($b->end_time) }}</small></span>
                            <span style="min-width:0">
                                <span class="ad-t">{{ $b->customer_name }}</span>
                                <span class="ad-s">{{ $eventTypes[$b->event_type] ?? ucfirst(str_replace('_', ' ', (string) $b->event_type)) }} · {{ $b->booking_type === 'food_only' ? ($b->service_location ?: 'Food only') : ($b->hall?->name ?? '—') }}@if($b->mealPlan) · {{ $b->mealPlan->name }}@endif</span>
                            </span>
                            <span class="ad-r"><b>{{ number_format($b->number_of_people) }}</b><small>guests</small></span>
                        </a>
                    @empty
                        <div class="ad-empty"><i class="bi bi-calendar2-check"></i>No events today.</div>
                    @endforelse
                </div>
                <div class="ad-foot">
                    <span><a href="{{ route('hall.bookings.index') }}">{{ $ops['bookings_week'] }}</a> in the next 7 days</span>
                    @if($ops['bookings_today'] > $todayBookings->count())<span>+{{ $ops['bookings_today'] - $todayBookings->count() }} more today</span>@endif
                    <span><a href="{{ route('admin.event-requests.index', ['status' => 'submitted']) }}">{{ $ops['event_requests'] }}</a> new event {{ Str::plural('request', $ops['event_requests']) }}</span>
                </div>
            </section>

            {{-- Recent expense requests --}}
            <section class="ad-panel" style="--acc:var(--ef-gold);--tint:rgba(200,144,42,.05)">
                <div class="ad-ph">
                    <h2><i class="bi bi-receipt"></i> Expense requests @if($stats['pending_approvals'])<em>{{ $stats['pending_approvals'] }} pending</em>@endif</h2>
                    <a href="{{ route('admin.expense-requests.index') }}">View all →</a>
                </div>
                <div>
                    @forelse($recentRequests as $req)
                        @php
                            $ini = collect(explode(' ', $req->requester->name ?? 'U'))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
                            $pt = $prioTone[$req->priority ?? 'low'] ?? 'muted';
                        @endphp
                        <a class="ad-row" data-tone="{{ $req->status === 'pending' ? $pt : '' }}" href="{{ route('admin.expense-requests.show', $req) }}" data-request>
                            <span class="ad-av">{{ $ini }}</span>
                            <span style="min-width:0">
                                <span class="ad-t">{{ $req->title }}</span>
                                <span class="ad-s"><span class="ad-chip --{{ $pt }}">{{ ucfirst($req->priority ?? 'low') }}</span> {{ $req->requester->name ?? '—' }}@if($req->category) · {{ $req->category->name }}@endif</span>
                            </span>
                            <span class="ad-r"><b>₹{{ number_format($req->amount, 0) }}</b><x-status-badge :status="$req->status" /></span>
                        </a>
                    @empty
                        <div class="ad-empty"><i class="bi bi-inbox"></i>No expense requests yet.</div>
                    @endforelse
                </div>
                <div class="ad-foot">
                    <span>{{ $stats['approved_today'] }} approved today (₹{{ number_format($stats['approved_today_amount'], 0) }})</span>
                    <span>{{ $stats['pending_reimb_count'] }} awaiting reimbursement</span>
                </div>
            </section>
        </div>

        <aside class="ad-rail">
            {{-- Team today --}}
            <section class="ad-panel" style="--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)">
                <div class="ad-ph"><h2><i class="bi bi-people"></i> Attendance Today</h2><a href="{{ route('admin.attendance.index') }}">Attendance →</a></div>
                <div class="ad-pb">
                    <div class="ad-bar" role="img" aria-label="{{ $att['present'] }} present, {{ $att['absent'] }} absent, {{ $att['on_leave'] }} on leave, {{ $att['not_marked'] }} not marked">
                        <i style="width:{{ $att['present'] / $attTotal * 100 }}%;background:var(--ef-emerald)"></i>
                        <i style="width:{{ $att['on_leave'] / $attTotal * 100 }}%;background:#3b6ea5"></i>
                        <i style="width:{{ $att['absent'] / $attTotal * 100 }}%;background:var(--ef-danger)"></i>
                        <i style="width:{{ $att['not_marked'] / $attTotal * 100 }}%;background:#d8cfc0"></i>
                    </div>
                    <div class="ad-legend">
                        <span><i style="background:var(--ef-emerald)"></i>Present<b>{{ $att['present'] }}</b></span>
                        <span><i style="background:#3b6ea5"></i>On leave<b>{{ $att['on_leave'] }}</b></span>
                        <span><i style="background:var(--ef-danger)"></i>Absent<b>{{ $att['absent'] }}</b></span>
                        <span><i style="background:#d8cfc0"></i>Not marked<b>{{ $att['not_marked'] }}</b></span>
                    </div>
                    <a class="ad-kv" href="{{ route('admin.leave.overview') }}" style="margin-top:8px">Today's Leave<b class="{{ $leaveToday ? '' : '--zero' }}">{{ $leaveToday }} on approved leave</b></a>
                    <a class="ad-kv" href="{{ route('admin.employees.index') }}">Team<b>{{ $stats['total_employees'] }} employees · {{ $stats['total_managers'] }} managers</b></a>
                </div>
            </section>

            {{-- Money --}}
            <section class="ad-panel" style="--acc:#9a6413;--tint:rgba(200,144,42,.06)">
                <div class="ad-ph"><h2><i class="bi bi-currency-rupee"></i> Money</h2></div>
                <div class="ad-pb">
                    <div class="ad-kv">Spend this month<b>₹{{ number_format($stats['total_expenses_month'], 0) }}</b></div>
                    <a class="ad-kv" href="{{ route('admin.wallets.index') }}">Wallet balance<b>₹{{ number_format($stats['total_wallet_balance'], 0) }}</b></a>
                    <div class="ad-kv">Reimbursement pending<b class="{{ $stats['pending_reimb_count'] ? '--warn' : '--zero' }}">₹{{ number_format($stats['pending_reimb_amount'], 0) }}</b></div>
                    <div class="ad-kv">Approval rate <span style="font-size:.72rem">({{ $stats['approved_total'] }} approved · {{ $stats['rejected'] }} rejected)</span><b>{{ $approvalRate }}%</b></div>
                    <div class="ad-meter"><i style="width:{{ $approvalRate }}%"></i></div>
                </div>
            </section>

            {{-- Store & kitchen --}}
            <section class="ad-panel" style="--acc:#2c5784;--tint:rgba(59,110,165,.05)">
                <div class="ad-ph"><h2><i class="bi bi-box-seam"></i> Store &amp; kitchen</h2><a href="{{ route('inventory.home') }}">Store →</a></div>
                <div class="ad-pb">
                    <a class="ad-kv" href="{{ route('inventory.material-requests.index', ['tab' => 'approve']) }}">Material requests to approve<b class="{{ $ops['mr_to_approve'] ? '--bad' : '--zero' }}">{{ $ops['mr_to_approve'] }}</b></a>
                    <a class="ad-kv" href="{{ route('inventory.home') }}">Items at or below minimum<b class="{{ $ops['low_stock'] ? '--warn' : '--zero' }}">{{ $ops['low_stock'] }}</b></a>
                    <a class="ad-kv" href="{{ route('admin.event-requests.index', ['status' => 'submitted']) }}">New event requests<b class="{{ $ops['event_requests'] ? '--warn' : '--zero' }}">{{ $ops['event_requests'] }}</b></a>
                </div>
            </section>

            {{-- Quick actions --}}
            <section class="ad-panel" style="--acc:var(--ef-ink);--tint:var(--ef-bg-subtle)">
                <div class="ad-ph"><h2><i class="bi bi-lightning-charge"></i> Quick actions</h2></div>
                <div class="ad-quick">
                    <a href="{{ route('hall.bookings.create') }}"><i class="bi bi-calendar-plus"></i><span>New booking</span></a>
                    <a href="{{ route('admin.employees.create') }}"><i class="bi bi-person-plus"></i><span>Add employee</span></a>
                    <a href="{{ route('admin.vendors.create') }}"><i class="bi bi-shop"></i><span>Add vendor</span></a>
                    <a href="{{ route('admin.categories.create') }}"><i class="bi bi-tag"></i><span>Add category</span></a>
                </div>
            </section>
        </aside>
    </div>
</div>
</x-admin-layout>
