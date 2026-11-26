<x-admin-layout title="Material Requests">
@include('inventory._styles')

@push('styles')
<style>
/* Material requests workspace — queue strip, toolbar, data table (cards on phones), rail. Same language as Store home / Purchases. */
.mw{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.mw-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.mw-title{flex:1 1 240px;min-width:0}
.mw-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.mw-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.mw-title p b{color:var(--ef-danger)}
.mw-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.mw-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.mw-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.mw-btn.--main .bi{color:#f0c97a}
@media (max-width:639.98px){.mw-head>.mw-btn{width:100%}.mw-title h1{font-size:1.25rem}.mw-title p .--long{display:none}}

/* queue strip = tabs */
.mw-q{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
@media (min-width:900px){.mw-q{grid-template-columns:repeat(var(--n),minmax(0,1fr))}}
.mw-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);border-bottom:1px solid var(--ef-border);min-width:0;background:var(--tint,transparent)}
@media (min-width:900px){.mw-qi{border-bottom:0}.mw-qi:last-child{border-right:0}}
@media (max-width:899.98px){.mw-qi:nth-child(3n){border-right:0}}
.mw-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.mw-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mw-qi small .bi{color:var(--acc)}
.mw-qi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.mw-qi em{font-style:normal;font-size:.72rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mw-qi.--zero strong{color:var(--ef-faint)}
.mw-qi.--zero{--tint:transparent}
.mw-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.mw-qi[data-k=mine]{--acc:#4f4396;--tint:rgba(107,91,181,.05)}
.mw-qi[data-k=approve]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.05)}
.mw-qi[data-k=review]{--acc:#9a6413;--tint:rgba(216,154,61,.08)}
.mw-qi[data-k=handover]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.mw-qi[data-k=out]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.mw-qi[data-k=all]{--acc:var(--ef-ink);--tint:transparent}
@media (max-width:639.98px){
  .mw-q{display:flex;overflow-x:auto;scrollbar-width:none;scroll-snap-type:x proximity}
  .mw-q::-webkit-scrollbar{display:none}
  .mw-qi{flex:0 0 auto;min-width:104px;padding:8px 12px;border-bottom:0!important;border-right:1px solid var(--ef-border)!important;scroll-snap-align:start}
  .mw-qi strong{font-size:1.1rem}.mw-qi em{display:none}
  .mw-count{display:none}
  .mw-rail .mw-flowcard{display:none}
}

/* body */
.mw-body{display:grid;gap:14px}
@media (min-width:1700px){.mw-body{grid-template-columns:minmax(0,1fr) 320px;align-items:start}}
.mw-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}

/* toolbar */
.mw-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border);background:var(--tint,transparent)}
.mw-search{position:relative;flex:1 1 220px;min-width:0}
.mw-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.mw-search input{width:100%;min-height:40px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink)}
.mw-more>summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;min-height:40px;padding:0 12px;border:1px solid var(--ef-border);border-radius:10px;font-weight:700;font-size:.84rem;color:var(--ef-ink);background:var(--ef-surface)}
.mw-more>summary::-webkit-details-marker{display:none}
.mw-badge{min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:var(--ef-gold);color:#fff;font-size:.66rem;display:inline-flex;align-items:center;justify-content:center}
.mw-more[open]{flex-basis:100%;order:3}
.mw-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding-top:10px}
@media (min-width:720px){.mw-fields{grid-template-columns:repeat(4,minmax(0,1fr)) auto;align-items:end}}
.mw-fields label{display:flex;flex-direction:column;gap:3px;font-size:.72rem;font-weight:700;color:var(--ef-muted);min-width:0}
.mw-fields input,.mw-fields select{min-height:40px;padding:6px 10px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink);width:100%;min-width:0}
.mw-go{display:flex;gap:6px;grid-column:1/-1}
@media (min-width:720px){.mw-go{grid-column:auto}}
.mw-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.mw-count b{color:var(--ef-ink)}

/* rows */
.mw-th,.mw-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:11px 14px}
.mw-th{display:none}
.mw-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent)}
.mw-row:first-child{border-top:0}
.mw-row:hover{background:var(--ef-bg-subtle)}
.mw-row[data-tone=warn]{--tone:#d89a3d}
.mw-row[data-tone=info]{--tone:#3b6ea5}
.mw-row[data-tone=ok]{--tone:var(--ef-emerald)}
.mw-row[data-tone=bad]{--tone:var(--ef-danger)}
.mw-what{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;color:var(--ef-ink)}
.mw-ico{flex:0 0 auto;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem}
.mw-ico[data-src=meal]{background:rgba(216,154,61,.15);color:#9a6413}
.mw-ico[data-src=hall]{background:rgba(59,110,165,.12);color:#2c5784}
.mw-ico[data-src=other]{background:rgba(107,91,181,.1);color:#4f4396}
.mw-what-b{min-width:0}
.mw-t{display:block;font-weight:750;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mw-what:hover .mw-t{text-decoration:underline}
.mw-s{display:block;font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mw-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.72rem}
.mw-due{display:inline-flex;flex-direction:column;line-height:1.2;font-size:.84rem;font-variant-numeric:tabular-nums;white-space:nowrap}
.mw-due small{font-size:.7rem;color:var(--ef-muted)}
.mw-due.--today{color:#7D5218;font-weight:750}
.mw-due.--late{color:var(--ef-danger);font-weight:750}
.mw-num{font-size:.84rem;font-variant-numeric:tabular-nums;text-align:center}
.mw-who{font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mw-st{display:flex;gap:4px;flex-wrap:wrap}
.mw-st .ic-chip{font-size:.68rem}
.mw-c-due,.mw-c-items,.mw-c-ppl,.mw-c-who{display:none}
.mw-mmeta{grid-column:1/-1;padding-left:46px;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.76rem;color:var(--ef-muted);align-items:center}
.mw-mmeta .--today{color:#7D5218;font-weight:700}
.mw-mmeta .--late{color:var(--ef-danger);font-weight:700}
.mw-act{display:flex;justify-content:flex-end}
.mw-do{display:inline-flex;align-items:center;gap:5px;min-height:34px;padding:4px 12px;border-radius:9px;font-size:.78rem;font-weight:750;text-decoration:none;border:1px solid var(--ef-border);color:var(--ef-ink);background:var(--ef-surface);white-space:nowrap}
.mw-do:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.mw-do.--primary{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.mw-do.--primary .bi{color:#f0c97a}
.mw-do.--ghost{border-color:transparent;color:var(--ef-muted)}
@media (max-width:1199.98px){.mw-what{grid-column:1/-1}.mw-st{grid-column:1;padding-left:46px;min-width:0}.mw-act{grid-column:2;justify-content:flex-end}.mw-act .mw-do.--ghost{padding:4px 8px}}
@media (min-width:1200px){
  .mw-th,.mw-row{grid-template-columns:minmax(200px,1fr) 100px 52px 60px 120px 170px 116px;gap:12px}
  .mw-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .mw-th .--c{text-align:center}.mw-th .--r{text-align:right}
  .mw-c-due,.mw-c-items,.mw-c-ppl,.mw-c-who{display:block}
  .mw-mmeta{display:none}
}
@media (max-width:399.98px){.mw-st,.mw-mmeta{padding-left:0}}
.mw-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.mw-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.mw-empty a{font-weight:700}
.mw-foot{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:8px;padding:10px 14px;border-top:1px solid var(--ef-border);font-size:.8rem;color:var(--ef-muted)}
.mw-foot b{color:var(--ef-ink)}
.mw-foot nav{overflow-x:auto;max-width:100%}

/* rail */
.mw-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:640px) and (max-width:1699.98px){.mw-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.mw-card-h{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.mw-card-h h2{margin:0;font-size:.92rem;font-weight:800;color:var(--ef-ink);display:flex;align-items:center;gap:8px}
.mw-card-h h2 .bi{color:var(--acc)}
.mw-card-b{padding:6px 14px 10px}
.mw-up{display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none}
.mw-up:first-child{border-top:0}
.mw-up:hover .mw-t{text-decoration:underline}
.mw-when{flex:0 0 auto;width:48px;text-align:center;border-radius:9px;padding:4px 0;background:var(--ef-bg-subtle);line-height:1.15}
.mw-when b{display:block;font-size:.74rem;font-weight:800}
.mw-when span{font-size:.66rem;color:var(--ef-muted)}
.mw-when.--today{background:rgba(216,154,61,.18)}
.mw-when.--today b{color:#7D5218}
.mw-when.--late{background:rgba(192,57,43,.1)}
.mw-when.--late b{color:var(--ef-danger)}
.mw-flow{list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--ef-muted);display:grid;gap:7px}
.mw-flow li{display:flex;gap:8px;align-items:flex-start}
.mw-flow b{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.7rem;display:flex;align-items:center;justify-content:center}
.mw-hint{font-size:.82rem;color:var(--ef-muted);padding:8px 0}
</style>
@endpush

@php
    $u = auth()->user();
    $isStoreSide = $u->isAdmin() || $u->isManager() || $u->isStorekeeper();
    $meta = [
        'mine' => ['bi-person', 'open of yours'],
        'approve' => ['bi-check2-square', 'waiting for you'],
        'review' => ['bi-clipboard-data', 'usage to check'],
        'handover' => ['bi-box-seam', 'ready to collect'],
        'out' => ['bi-arrow-left-right', 'with kitchen'],
        'all' => ['bi-list-ul', 'all requests'],
    ];
    $empty = [
        'mine' => 'You have not requested any materials yet.',
        'approve' => 'No requests are waiting for approval.',
        'review' => 'No usage reports need review.',
        'handover' => 'Nothing is waiting to be handed over.',
        'out' => 'No materials are out with the kitchen right now.',
        'all' => 'No material requests yet.',
    ][$tab] ?? 'Nothing here.';
    $panelAcc = ['mine' => '#6b5bb5', 'approve' => 'var(--ef-danger)', 'review' => '#d89a3d', 'handover' => '#3b6ea5', 'out' => 'var(--ef-emerald)', 'all' => 'var(--ef-ink)'][$tab] ?? 'var(--ef-gold)';
    $srcIcon = fn ($r) => match ($r->source) { 'meal' => 'bi-egg-fried', 'hall' => 'bi-building', default => 'bi-basket' };
    $tabUrl = fn ($k) => route('inventory.material-requests.index', array_merge(array_diff_key($filters, ['status' => 1]), ['tab' => $k]));
    $filterCount = count(array_diff_key($filters, ['q' => 1]));
    $due = function ($r) {
        $d = $r->required_date;
        $open = in_array($r->status, ['submitted', 'approved', 'draft'], true);
        return [
            'cls' => $open && $d->lt(today()) ? '--late' : ($d->isToday() ? '--today' : ''),
            'day' => $d->isToday() ? 'Today' : ($d->isTomorrow() ? 'Tomorrow' : $d->format('D d M')),
            'time' => $r->required_time ? \Carbon\Carbon::parse($r->required_time)->format('h:i A') : null,
        ];
    };
@endphp

<div class="mw">
    <header class="mw-head">
        <div class="mw-title">
            <h1>Material requests</h1>
            <p><span class="--long">Kitchen asks → admin approves → store hands over → kitchen records usage &amp; returns.</span>@if($overdue) <b><i class="bi bi-exclamation-circle"></i> {{ $overdue }} past their needed date</b>@endif</p>
        </div>
        <a href="{{ route('inventory.material-requests.create') }}" class="mw-btn --main"><i class="bi bi-plus-lg"></i> Request materials</a>
    </header>

    {{-- Queues (role-based tabs) --}}
    <nav class="mw-q" aria-label="Request lists" style="--n:{{ count($tabs) }}">
        @foreach($tabs as $k => $l)
            @php $n = $counts[$k] ?? 0; @endphp
            <a class="mw-qi {{ $tab === $k ? '--on' : '' }} {{ $n ? '' : '--zero' }}" data-k="{{ $k }}" href="{{ $tabUrl($k) }}" @if($tab === $k) aria-current="page" @endif>
                <small><i class="bi {{ $meta[$k][0] ?? 'bi-dot' }}"></i> {{ $l }}</small><strong>{{ $n }}</strong><em>{{ $meta[$k][1] ?? '' }}</em></a>
        @endforeach
    </nav>

    <div class="mw-body">
        <section class="mw-panel" style="--acc:{{ $panelAcc }}" aria-label="{{ $tabs[$tab] }}">
            <form method="GET" action="{{ route('inventory.material-requests.index') }}" class="mw-bar" role="search">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="mw-search"><i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search dish, person or MR-00017" aria-label="Search requests"></div>
                <details class="mw-more" @if($filterCount) open @endif>
                    <summary><i class="bi bi-sliders"></i> Filters @if($filterCount)<span class="mw-badge">{{ $filterCount }}</span>@endif</summary>
                    <div class="mw-fields">
                        <label>Needed from<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
                        <label>Needed to<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
                        <label>Department
                            <select name="department"><option value="">Any</option>
                                @foreach(\App\Models\MaterialRequest::DEPARTMENTS as $k => $l)<option value="{{ $k }}" @selected(($filters['department'] ?? '') === $k)>{{ $l }}</option>@endforeach
                            </select></label>
                        @if($tab === 'all')
                            <label>Status
                                <select name="status"><option value="">Any</option>
                                    @foreach(\App\Models\MaterialRequest::STATUSES as $k => $l)<option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $l }}</option>@endforeach
                                </select></label>
                        @else
                            <span></span>
                        @endif
                        <div class="mw-go">
                            <button class="mw-btn --main" type="submit" style="flex:1">Apply</button>
                            @if($filters)<a class="mw-btn" href="{{ route('inventory.material-requests.index', ['tab' => $tab]) }}">Reset</a>@endif
                        </div>
                    </div>
                </details>
                <span class="mw-count"><b>{{ $requests->total() }}</b> {{ \Illuminate\Support\Str::plural('request', $requests->total()) }} · {{ $tabs[$tab] }}</span>
            </form>

            <div class="mw-th" aria-hidden="true">
                <span>Request</span><span>Needed</span><span class="--c">Items</span><span class="--c">People</span><span>Requested by</span><span>Status</span><span class="--r">Action</span>
            </div>

            <div>
            @forelse($requests as $r)
                @php
                    $tone = \App\Models\MaterialRequest::statusTone($r->status);
                    $w = $due($r);
                    $for = \Illuminate\Support\Str::replaceLast(' · '.$r->required_date->format('d M'), '', $r->forLabel());   // date is shown in "Needed"
                    $title = $r->preparation_name ?: $for;
                @endphp
                <div class="mw-row" data-tone="{{ $tone }}" data-request>
                    <a class="mw-what" href="{{ route('inventory.material-requests.show', $r) }}">
                        <span class="mw-ico" data-src="{{ in_array($r->source, ['meal', 'hall'], true) ? $r->source : 'other' }}" aria-hidden="true"><i class="bi {{ $srcIcon($r) }}"></i></span>
                        <span class="mw-what-b">
                            <span class="mw-t">{{ $title }}</span>
                            <span class="mw-s"><span class="mw-ref">{{ $r->reference() }}</span>@if($r->preparation_name) · {{ $for }}@endif</span>
                        </span>
                    </a>
                    <div class="mw-c-due"><span class="mw-due {{ $w['cls'] }}">{{ $w['day'] }}@if($w['time'])<small>{{ $w['time'] }}</small>@endif</span></div>
                    <div class="mw-c-items mw-num">{{ $r->items_count }}</div>
                    <div class="mw-c-ppl mw-num">{{ $r->guest_count ?: '—' }}</div>
                    <div class="mw-c-who mw-who">{{ $r->requester?->name ?? '—' }}</div>
                    <div class="mw-mmeta">
                        <span class="{{ $w['cls'] }}"><i class="bi bi-calendar3"></i> {{ $w['day'] }}{{ $w['time'] ? ' · '.$w['time'] : '' }}</span>
                        <span><i class="bi bi-box"></i> {{ $r->items_count }} {{ \Illuminate\Support\Str::plural('item', $r->items_count) }}</span>
                        @if($r->guest_count)<span><i class="bi bi-people"></i> {{ $r->guest_count }}</span>@endif
                        @if($r->requester)<span><i class="bi bi-person"></i> {{ $r->requester->name }}</span>@endif
                    </div>
                    <div class="mw-st">
                        @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => $tone])
                        @if($r->reconciliation_status === 'pending_review')<span class="ic-chip ic-chip--warn"><i class="bi bi-clipboard-data"></i> Usage review</span>@endif
                    </div>
                    <div class="mw-act">
                        @php $show = route('inventory.material-requests.show', $r); @endphp
                        @can('approve', $r)
                            <a class="mw-do --primary" href="{{ $show }}"><i class="bi bi-check2-circle"></i> Review</a>
                        @elsecan('handover', $r)
                            <a class="mw-do --primary" href="{{ $show }}"><i class="bi bi-box-arrow-up-right"></i> Hand over</a>
                        @elsecan('reconcile', $r)
                            <a class="mw-do --primary" href="{{ $show }}"><i class="bi bi-clipboard-data"></i> Record usage</a>
                        @else
                            @if($u->isAdmin() && $r->reconciliation_status === 'pending_review')
                                <a class="mw-do --primary" href="{{ $show }}"><i class="bi bi-search"></i> Check</a>
                            @else
                                <a class="mw-do --ghost" href="{{ $show }}">View <i class="bi bi-chevron-right"></i></a>
                            @endif
                        @endcan
                    </div>
                </div>
            @empty
                <div class="mw-empty"><i class="bi bi-inbox"></i>
                    @if($filters) No requests match these filters. <a href="{{ route('inventory.material-requests.index', ['tab' => $tab]) }}">Clear filters</a>
                    @else {{ $empty }} @if($tab === 'mine')<a href="{{ route('inventory.material-requests.create') }}">Request materials</a>@endif
                    @endif
                </div>
            @endforelse
            </div>

            <div class="mw-foot">
                <span>@if($requests->total())Showing <b>{{ $requests->firstItem() }}–{{ $requests->lastItem() }}</b> of {{ $requests->total() }}@endif</span>
                @if($requests->hasPages()){{ $requests->links() }}@endif
            </div>
        </section>

        <aside class="mw-rail" aria-label="Request insights">
            @if($isStoreSide)
                <section class="mw-panel" style="--acc:#3b6ea5;--tint:rgba(59,110,165,.07)">
                    <div class="mw-card-h"><h2><i class="bi bi-calendar-event"></i> Coming up</h2></div>
                    <div class="mw-card-b">
                        @forelse($upcoming as $r)
                            @php $d = $r->required_date; @endphp
                            <a class="mw-up" href="{{ route('inventory.material-requests.show', $r) }}">
                                @php $late = $d->lt(today()); @endphp
                                <span class="mw-when {{ $late ? '--late' : ($d->isToday() ? '--today' : '') }}"><b>{{ $late ? 'Late' : ($d->isToday() ? 'Today' : ($d->isTomorrow() ? 'Tmrw' : $d->format('D'))) }}</b><span>{{ $r->required_time ? \Carbon\Carbon::parse($r->required_time)->format('h:i A') : $d->format('d M') }}</span></span>
                                <span class="mw-what-b"><span class="mw-t">{{ $r->preparation_name ?: $r->forLabel() }}</span><span class="mw-s">{{ $r->statusLabel() }}@if($r->guest_count) · {{ $r->guest_count }} people @endif</span></span>
                            </a>
                        @empty
                            <div class="mw-hint">Nothing open or scheduled.</div>
                        @endforelse
                    </div>
                </section>
            @endif
            <section class="mw-panel mw-flowcard" style="--acc:#8a827a;--tint:rgba(100,82,42,.05)">
                <div class="mw-card-h"><h2><i class="bi bi-diagram-3"></i> How a request flows</h2></div>
                <div class="mw-card-b">
                    <ol class="mw-flow">
                        <li><b>1</b><span>Kitchen requests items — menu &amp; recipes can pre-fill them.</span></li>
                        <li><b>2</b><span>Admin approves; stock is reserved and handed over.</span></li>
                        <li><b>3</b><span>Requester records used, wasted and returned — returns go back to stock.</span></li>
                        <li><b>4</b><span>Unusual usage is flagged for admin review.</span></li>
                    </ol>
                </div>
            </section>
        </aside>
    </div>
</div>
</x-admin-layout>
