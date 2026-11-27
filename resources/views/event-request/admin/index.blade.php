<x-admin-layout title="Event Requests">
@php
    $st      = request('status') ?: null;
    $tiles   = [
        null           => ['All', 'bi-inbox', 'all'],
        'submitted'    => ['New', 'bi-envelope', 'submitted'],
        'under_review' => ['Under review', 'bi-hourglass-split', 'under_review'],
        'need_changes' => ['Need changes', 'bi-pencil-square', 'need_changes'],
        'resubmitted'  => ['Resubmitted', 'bi-arrow-repeat', 'resubmitted'],
        'approved'     => ['Approved', 'bi-check-circle', 'approved'],
        'scheduled'    => ['Scheduled', 'bi-calendar-check', 'scheduled'],
        'rejected'     => ['Rejected', 'bi-x-circle', 'rejected'],
    ];
    $tone    = fn ($s) => match ($s) {
        'submitted' => 'new', 'under_review', 'resubmitted' => 'warn', 'need_changes' => 'orange',
        'approved', 'scheduled' => 'ok', 'rejected' => 'bad', default => 'muted',
    };
    $more    = collect(request()->only(['meal_type', 'menu_type', 'date']))->filter()->count();
    $filtered = $more || request()->filled('client');
    $total   = (int) $counts->sum();
    $toReview = (int) (($counts['submitted'] ?? 0) + ($counts['resubmitted'] ?? 0));
@endphp

@push('styles')
<style>
/* Event requests — same language as Purchases / Meal plans: header, status strip, toolbar, table (cards on phones), rail. */
.er{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.er-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.er-title{flex:1 1 260px;min-width:0}
.er-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.er-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.er-title p b{color:var(--ef-danger)}
.er-acts{display:flex;gap:8px;flex-wrap:wrap}
.er-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.er-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.er-btn .bi{color:var(--ef-gold)}
.er-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.er-btn.--main .bi{color:#f0c97a}
.er-btn.--sm{min-height:34px;padding:4px 12px;font-size:.8rem}
.er-btn b{min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:var(--ef-ink);color:#fff;font-size:.7rem;display:inline-flex;align-items:center;justify-content:center}
@media (max-width:639.98px){.er-acts{width:100%}.er-acts .er-btn{flex:1}.er-title h1{font-size:1.25rem}}

.er-q{display:grid;grid-template-columns:repeat(8,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.er-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);min-width:0;background:var(--tint,transparent)}
.er-qi:last-child{border-right:0}
.er-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.er-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.er-qi small .bi{color:var(--acc)}
.er-qi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.er-qi.--zero strong{color:var(--ef-faint)}
.er-qi.--zero{--tint:transparent}
.er-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.er-qi[data-k=all]{--acc:var(--ef-ink)}
.er-qi[data-k=submitted]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.er-qi[data-k=under_review]{--acc:#9a6413;--tint:rgba(200,144,42,.08)}
.er-qi[data-k=need_changes]{--acc:#b4531c;--tint:rgba(214,110,40,.07)}
.er-qi[data-k=resubmitted]{--acc:#6b5bb5;--tint:rgba(107,91,181,.05)}
.er-qi[data-k=approved]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.er-qi[data-k=scheduled]{--acc:#1f6f6a;--tint:rgba(31,111,106,.06)}
.er-qi[data-k=rejected]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.04)}
@media (max-width:1199.98px){.er-q{display:flex;overflow-x:auto;scrollbar-width:none}.er-q::-webkit-scrollbar{display:none}.er-qi{flex:0 0 auto;min-width:112px;padding:8px 12px}.er-qi strong{font-size:1.1rem}}

.er-body{display:grid;gap:14px}
@media (min-width:1600px){.er-body{grid-template-columns:minmax(0,1fr) 320px;align-items:start}}
.er-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}
.er-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.er-search{position:relative;flex:1 1 220px;min-width:0;margin:0}
.er-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.er-search input{width:100%;min-height:40px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink)}
.er-more>summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;min-height:40px;padding:0 12px;border:1px solid var(--ef-border);border-radius:10px;font-weight:700;font-size:.84rem;color:var(--ef-ink);background:var(--ef-surface)}
.er-more>summary::-webkit-details-marker{display:none}
.er-more>summary b{min-width:18px;height:18px;border-radius:9px;background:var(--ef-gold);color:#fff;font-size:.68rem;display:inline-flex;align-items:center;justify-content:center}
.er-more[open]{flex-basis:100%;order:3}
.er-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding-top:10px}
@media (min-width:720px){.er-fields{grid-template-columns:repeat(3,minmax(0,200px)) auto;align-items:end}}
.er-fields label{display:flex;flex-direction:column;gap:3px;font-size:.72rem;font-weight:700;color:var(--ef-muted);min-width:0;margin:0}
.er-fields input,.er-fields select{min-height:40px;padding:6px 10px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink);width:100%;min-width:0}
.er-go{display:flex;gap:6px;grid-column:1/-1}
@media (min-width:720px){.er-go{grid-column:auto}}
.er-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.er-count b{color:var(--ef-ink)}

.er-th,.er-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:12px 14px}
.er-th{display:none}
.er-row{position:relative;border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent)}
.er-row:first-child{border-top:0}
.er-row:hover{background:var(--ef-bg-subtle)}
.er-row[data-tone=new]{--tone:#3b6ea5}
.er-row[data-tone=warn]{--tone:#d89a3d}
.er-row[data-tone=orange]{--tone:#d66e28}
.er-row[data-tone=ok]{--tone:var(--ef-emerald)}
.er-row[data-tone=bad]{--tone:var(--ef-danger)}
.er-row[data-tone=muted]{--tone:var(--ef-border-strong)}
.er-who{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;color:var(--ef-ink)}
.er-who::after{content:"";position:absolute;inset:0}
.er-av{flex:0 0 auto;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(200,144,42,.12);color:#9a6413;font-weight:800;font-size:.8rem}
.er-who-b{min-width:0}
.er-t{display:block;font-weight:750;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.er-who:hover .er-t{text-decoration:underline}
.er-s{display:block;font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.er-c{font-size:.84rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.er-c small{display:block;font-size:.7rem;color:var(--ef-muted)}
.er-c.--past{color:var(--ef-muted)}
.er-c-ev,.er-c-date,.er-c-guest,.er-c-st,.er-c-do{display:none}
.er-amt{text-align:right;font-weight:800;font-variant-numeric:tabular-nums;font-size:.94rem;white-space:nowrap}
.er-amt.--none{color:var(--ef-faint);font-weight:600}
.er-chip{display:inline-flex;align-items:center;padding:2px 8px;border-radius:8px;font-size:.68rem;font-weight:800;white-space:nowrap;background:color-mix(in srgb,var(--tone) 14%,transparent);color:color-mix(in srgb,var(--tone) 70%,#000)}
.er-row[data-tone=muted] .er-chip{color:var(--ef-muted)}
.er-mmeta{grid-column:1/-1;padding-left:46px;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.76rem;color:var(--ef-muted);align-items:center}
.er-mmeta .bi{margin-right:3px}
@media (max-width:399.98px){.er-mmeta{padding-left:0}}
@media (min-width:1100px){
  .er-th,.er-row{grid-template-columns:minmax(200px,1fr) minmax(120px,170px) 110px 80px 120px 110px 84px;gap:14px}
  .er-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .er-th .--r{text-align:right}
  .er-c-ev,.er-c-date,.er-c-guest,.er-c-st,.er-c-do{display:block}
  .er-c-do{text-align:right;position:relative;z-index:1}
  .er-mmeta{display:none}
  .er-amt{order:5}
  .er-c-st{order:4}
  .er-c-do{order:6}
}
.er-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.er-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.er-empty a{font-weight:700}
.er-foot{padding:10px 14px;border-top:1px solid var(--ef-border)}
.er-foot nav{overflow-x:auto;max-width:100%}

.er-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:640px) and (max-width:1599.98px){.er-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.er-card-h{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.er-card-h h2{margin:0;font-size:.92rem;font-weight:800;color:var(--ef-ink);display:flex;align-items:center;gap:8px}
.er-card-h h2 .bi{color:var(--acc)}
.er-card-b{padding:6px 14px 12px}
.er-up{display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid var(--ef-border);text-decoration:none;color:var(--ef-ink)}
.er-up:first-child{border-top:0}
.er-up-d{flex:0 0 auto;width:42px;text-align:center;border-radius:8px;background:rgba(31,111,106,.08);color:#1f6f6a;line-height:1.1;padding:4px 0}
.er-up-d b{display:block;font-size:1rem}
.er-up-d small{font-size:.62rem;font-weight:800;text-transform:uppercase}
.er-up-b{flex:1;min-width:0}
.er-up-b b{display:block;font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.er-up-b small{display:block;font-size:.74rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.er-hint{font-size:.82rem;color:var(--ef-muted);margin:6px 0}
.er-flow{list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--ef-muted);display:grid;gap:7px}
.er-flow li{display:flex;gap:8px;align-items:flex-start}
.er-flow b{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.7rem;display:flex;align-items:center;justify-content:center}
@media (max-width:639.98px){.er-flowcard,.er-count,.er-sbtn{display:none}}
</style>
@endpush

<div class="er">
    <header class="er-head">
        <div class="er-title">
            <h1>Event requests</h1>
            <p>Customer enquiries from the request portal.@if($toReview) <b>{{ $toReview }} waiting for review.</b>@endif</p>
        </div>
        <div class="er-acts">
            <a href="{{ route('admin.event-requests.create') }}" class="er-btn --main"><i class="bi bi-plus-lg"></i> New event request</a>
        </div>
    </header>

    @if(session('status'))
        <div class="alert alert-success mb-0">{{ session('status') }}</div>
    @endif

    <nav class="er-q" aria-label="Request status">
        @foreach($tiles as $val => [$label, $icon, $k])
            @php $val = $val ?: null; $n = $val ? (int) ($counts[$val] ?? 0) : $total; $on = $st === $val; @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => $val, 'page' => null]) }}" data-k="{{ $k }}" class="er-qi {{ $on ? '--on' : '' }} {{ $n ? '' : '--zero' }}" @if($on) aria-current="page" @endif>
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $n }}</strong>
            </a>
        @endforeach
    </nav>

    <div class="er-body">
        <section class="er-panel" aria-label="Event requests">
            <form method="GET" class="er-bar" role="search">
                @if($st)<input type="hidden" name="status" value="{{ $st }}">@endif
                <label class="er-search"><i class="bi bi-search"></i>
                    <input type="search" name="client" value="{{ request('client') }}" placeholder="Search name, phone, ER-number or event" aria-label="Search event requests">
                </label>
                <details class="er-more" @if($more) open @endif>
                    <summary><i class="bi bi-sliders"></i> Filters @if($more)<b>{{ $more }}</b>@endif</summary>
                    <div class="er-fields">
                        <label>Meal type
                            <select name="meal_type"><option value="">Any</option>
                                @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)<option value="{{ $value }}" @selected(request('meal_type') === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </label>
                        <label>Menu
                            <select name="menu_type"><option value="">Any</option>
                                @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)<option value="{{ $value }}" @selected(request('menu_type') === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </label>
                        <label>Event date<input type="date" name="date" value="{{ request('date') }}"></label>
                        <div class="er-go">
                            <button class="er-btn --sm --main" type="submit">Apply</button>
                            @if($filtered)<a class="er-btn --sm" href="{{ route('admin.event-requests.index', array_filter(['status' => $st])) }}">Clear</a>@endif
                        </div>
                    </div>
                </details>
                <button class="er-btn er-sbtn" type="submit">Search</button>
                @if($requests->total())<span class="er-count"><b>{{ $requests->total() }}</b> {{ Str::plural('request', $requests->total()) }}</span>@endif
            </form>

            @if($requests->count())
                <div class="er-th" aria-hidden="true">
                    <span>Customer</span><span>Event</span><span>Date</span><span>Guests</span><span>Status</span><span class="--r">Estimate</span><span></span>
                </div>
            @endif
            <div>
            @forelse($requests as $req)
                @php
                    $name  = $req->client_name ?: 'Untitled request';
                    $ini   = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                    $event = $req->event_name ?: $req->mealTypeLabel();
                    $past  = $req->event_date && $req->event_date->lt(today());
                    $guests = $req->guest_count ? number_format($req->guest_count) : '—';
                @endphp
                <article class="er-row" data-tone="{{ $tone($req->status) }}" data-request="{{ $req->id }}">
                    <a class="er-who" href="{{ route('admin.event-requests.show', $req) }}" aria-label="Review {{ $req->referenceNumber() }} for {{ $name }}, {{ $req->statusLabel() }}">
                        <span class="er-av" aria-hidden="true">{{ $ini ?: '?' }}</span>
                        <span class="er-who-b">
                            <span class="er-t">{{ $name }}</span>
                            <span class="er-s">{{ $req->referenceNumber() }}@if($req->client_mobile) · {{ $req->client_mobile }}@endif</span>
                        </span>
                    </a>
                    <span class="er-c er-c-ev">{{ $event }}<small>{{ $req->menuTypeLabel() }}</small></span>
                    <span class="er-c er-c-date {{ $past ? '--past' : '' }}">{{ $req->event_date?->format('d M Y') ?? '—' }}<small>{{ $req->event_date?->format('l') ?? '' }}</small></span>
                    <span class="er-c er-c-guest">{{ $guests }}</span>
                    <span class="er-c-st"><span class="er-chip">{{ $req->statusLabel() }}</span></span>
                    <span class="er-amt {{ (float) $req->estimated_total ? '' : '--none' }}">{{ (float) $req->estimated_total ? '₹'.number_format($req->estimated_total, 0) : 'No estimate' }}</span>
                    <span class="er-c-do"><a href="{{ route('admin.event-requests.show', $req) }}" class="er-btn --sm">Review</a></span>
                    <div class="er-mmeta">
                        <span class="er-chip">{{ $req->statusLabel() }}</span>
                        <span><i class="bi bi-calendar-event"></i>{{ $req->event_date?->format('d M') ?? '—' }}</span>
                        <span><i class="bi bi-people"></i>{{ $guests }}</span>
                        <span>{{ $event }}</span>
                        <span>{{ $req->created_at->diffForHumans() }}</span>
                    </div>
                </article>
            @empty
                <div class="er-empty">
                    @if($filtered || $st)
                        <i class="bi bi-search"></i>No event requests match these filters. <a href="{{ route('admin.event-requests.index') }}">Show all</a>
                    @else
                        <i class="bi bi-inbox"></i>No event requests yet. <a href="{{ route('admin.event-requests.create') }}">Create the first one</a>
                    @endif
                </div>
            @endforelse
            </div>
            @if($requests->hasPages())<div class="er-foot">{{ $requests->links() }}</div>@endif
        </section>

        <aside class="er-rail">
            <section class="er-panel" style="--acc:#1f6f6a;--tint:rgba(31,111,106,.05)">
                <div class="er-card-h"><h2><i class="bi bi-calendar-week"></i> Coming up · 14 days</h2></div>
                <div class="er-card-b">
                    @forelse($upcoming as $u)
                        <a class="er-up" href="{{ route('admin.event-requests.show', $u) }}" data-upcoming>
                            <span class="er-up-d"><b>{{ $u->event_date->format('j') }}</b><small>{{ $u->event_date->format('M') }}</small></span>
                            <span class="er-up-b"><b>{{ $u->client_name ?: 'Untitled request' }}</b><small>{{ $u->event_name ?: $u->mealTypeLabel() }} · {{ $u->guest_count ? number_format($u->guest_count).' guests' : 'guests —' }} · {{ $u->statusLabel() }}</small></span>
                        </a>
                    @empty
                        <p class="er-hint">No approved or scheduled events in the next 14 days.</p>
                    @endforelse
                </div>
            </section>
            <section class="er-panel er-flowcard" style="--acc:#2c5784;--tint:rgba(59,110,165,.05)">
                <div class="er-card-h"><h2><i class="bi bi-signpost-split"></i> How requests move</h2></div>
                <div class="er-card-b">
                    <ol class="er-flow">
                        <li><b>1</b><span>Customer submits a request (New).</span></li>
                        <li><b>2</b><span>Review it; ask for changes if needed — they resubmit.</span></li>
                        <li><b>3</b><span>Approve or reject the request.</span></li>
                        <li><b>4</b><span>Approved requests are scheduled for their event date.</span></li>
                    </ol>
                </div>
            </section>
        </aside>
    </div>
</div>
</x-admin-layout>
