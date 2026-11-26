<x-admin-layout title="Store & Inventory">
@include('inventory._styles')
@include('inventory._item-picker')

@push('styles')
<style>
/* Store home — one "today" page: act, see what needs attention, check stock. Mobile-first, --ef-* tokens. */
.sh{max-width:1120px;margin:0 auto}
.sh-hero{display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px 16px;margin:2px 0 14px}
.sh-hero h1{margin:0;font-size:1.3rem;font-weight:800;letter-spacing:-.015em;color:var(--ef-ink)}
.sh-hero p{margin:2px 0 0;font-size:.86rem;color:var(--ef-muted)}
@media (min-width:768px){.sh-hero h1{font-size:1.6rem}}

.sh-acts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-bottom:14px}
.sh-act{display:flex;flex-direction:column;align-items:flex-start;gap:6px;padding:12px;min-height:84px;border-radius:16px;border:1px solid var(--ef-border);background:var(--ef-surface);box-shadow:var(--ef-shadow);color:var(--ef-ink);text-decoration:none;font-weight:750;font-size:.9rem;line-height:1.2}
.sh-act:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.sh-act .bi{font-size:1.3rem;width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:var(--ef-bg-subtle);color:var(--ef-gold)}
.sh-act small{font-weight:500;font-size:.76rem;color:var(--ef-muted);display:none}
.sh-act.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.sh-act.--main .bi{background:rgba(255,255,255,.12);color:#f0c97a}
.sh-act.--main small{color:rgba(255,255,255,.65)}
@media (min-width:640px){.sh-act{flex-direction:row;align-items:center;min-height:64px}.sh-act small{display:block}}
@media (max-width:380px){.sh-act{font-size:.82rem;padding:10px}}

.sh-grid{display:grid;gap:14px}
@media (min-width:960px){.sh-grid{grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);align-items:start}}
/* phones: one column in task order — attention, find, stock, coming up, mine */
.sh-col{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (max-width:959.98px){.sh-col{display:contents}.sh-grid>.sh-col>section{order:var(--o,9)}}
.sh-card{border:1px solid var(--ef-border);border-radius:16px;background:var(--ef-surface);box-shadow:var(--ef-shadow);padding:14px}
.sh-card h2{display:flex;align-items:center;gap:8px;margin:0 0 10px;font-size:1rem;font-weight:800;color:var(--ef-ink)}
.sh-card h2 .sh-more{margin-left:auto;font-size:.8rem;font-weight:700;color:var(--ef-gold);text-decoration:none}

/* search */
.sh-search{position:relative}
.sh-search>.bi-search{position:absolute;left:14px;top:24px;transform:translateY(-50%);color:var(--ef-faint);z-index:2;pointer-events:none}
.sh-search .ic-input{min-height:48px;padding-left:40px;font-size:16px;border-radius:12px}
.sh-found{display:none;align-items:center;gap:12px;margin-top:10px;padding:10px 12px;border-radius:12px;background:var(--ef-bg-subtle)}
.sh-found.--on{display:flex;flex-wrap:wrap}
.sh-found-main{flex:1 1 180px;min-width:0}
.sh-found-main strong{display:block;overflow-wrap:anywhere}
.sh-found-main span{font-size:.8rem;color:var(--ef-muted)}
.sh-found-acts{display:flex;gap:6px;flex-wrap:wrap}

/* attention list */
.sh-todo{display:flex;flex-direction:column}
.sh-todo a{display:flex;align-items:center;gap:12px;padding:10px 4px;min-height:56px;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none}
.sh-todo a:first-child{border-top:0}
.sh-todo a:hover .sh-todo-t{text-decoration:underline}
.sh-todo-i{flex:0 0 auto;width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:var(--ef-bg-subtle);color:var(--ef-gold)}
.sh-todo a[data-tone=bad] .sh-todo-i{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.sh-todo a[data-tone=warn] .sh-todo-i{background:rgba(216,154,61,.16);color:#9a6413}
.sh-todo a[data-tone=info] .sh-todo-i{background:rgba(59,110,165,.12);color:#2c5784}
.sh-todo-b{flex:1;min-width:0}
.sh-todo-t{display:block;font-weight:750;font-size:.94rem}
.sh-todo-s{display:block;font-size:.8rem;color:var(--ef-muted)}
.sh-todo-n{flex:0 0 auto;min-width:30px;height:30px;padding:0 9px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:.88rem;background:var(--ef-ink);color:#fff;font-variant-numeric:tabular-nums}
.sh-clear{display:flex;align-items:center;gap:12px;padding:6px 2px;color:var(--ef-muted);font-size:.9rem}
.sh-clear .bi{font-size:1.6rem;color:var(--ef-emerald)}

/* stock snapshot */
.sh-snap{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.sh-snap a{display:flex;flex-direction:column;gap:2px;padding:10px 12px;border-radius:12px;background:var(--ef-bg-subtle);color:var(--ef-ink);text-decoration:none;border:1px solid transparent}
.sh-snap a:hover{border-color:var(--ef-gold)}
.sh-snap small{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--ef-muted);display:flex;align-items:center;gap:5px}
.sh-snap strong{font-size:1.4rem;font-weight:800;line-height:1.1;font-variant-numeric:tabular-nums}
.sh-snap [data-k=in] strong{color:var(--ef-emerald)}
.sh-snap [data-k=low] strong{color:#9a6413}
.sh-snap [data-k=out] strong{color:var(--ef-danger)}
.sh-snap [data-k=incoming] strong{color:#2c5784}
.sh-value{margin-top:10px;font-size:.84rem;color:var(--ef-muted)}
.sh-value b{color:var(--ef-ink)}

/* request rows */
.sh-req{display:flex;align-items:center;gap:10px;padding:10px 2px;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none;min-height:54px}
.sh-req:first-of-type{border-top:0}
.sh-req:hover .sh-req-t{text-decoration:underline}
.sh-when{flex:0 0 auto;width:50px;text-align:center;border-radius:10px;padding:4px 0;background:var(--ef-bg-subtle);line-height:1.1}
.sh-when b{display:block;font-size:.8rem;font-weight:800}
.sh-when span{font-size:.7rem;color:var(--ef-muted)}
.sh-when.--today{background:rgba(216,154,61,.18)}
.sh-when.--today b{color:#7D5218}
.sh-req-b{flex:1;min-width:0}
.sh-req-t{display:block;font-weight:700;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sh-req-s{display:block;font-size:.78rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sh-req .ic-chip{flex:0 0 auto;font-size:.7rem}
.sh-empty{font-size:.88rem;color:var(--ef-muted);padding:4px 2px}
.sh-empty a{font-weight:700}
</style>
@endpush

@php
    $u = auth()->user();
    $isStoreSide = $u->isAdmin() || $u->isManager() || $u->isStorekeeper();
    $hour = (int) now('Asia/Kolkata')->format('G');
    $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    // one prioritised list of things waiting on this person (only non-zero rows are shown)
    $todo = [];
    if ($admin) {
        $todo[] = ['n' => $admin['materials'], 'tone' => 'bad', 'icon' => 'bi-clipboard-check', 't' => 'Approve material requests', 's' => 'Kitchen is waiting for materials', 'href' => route('inventory.material-requests.index', ['tab' => 'approve'])];
        $todo[] = ['n' => $admin['purchases'], 'tone' => 'warn', 'icon' => 'bi-cart-check', 't' => 'Approve purchases', 's' => 'Submitted purchases need your OK', 'href' => route('inventory.purchases.index', ['status' => 'submitted'])];
    }
    if ($store) {
        $todo[] = ['n' => $store['receive'], 'tone' => 'info', 'icon' => 'bi-truck', 't' => 'Receive goods', 's' => 'Approved purchases not yet received', 'href' => route('inventory.purchases.index', ['status' => 'approved'])];
        $todo[] = ['n' => $store['handover'], 'tone' => 'warn', 'icon' => 'bi-box-arrow-up-right', 't' => 'Hand over materials', 's' => 'Approved earlier, not yet collected', 'href' => route('inventory.material-requests.index', ['tab' => 'handover'])];
        $todo[] = ['n' => $store['out'], 'tone' => '', 'icon' => 'bi-arrow-return-left', 't' => 'Materials out with kitchen', 's' => 'Record returns when they come back', 'href' => route('inventory.material-requests.index', ['tab' => 'out'])];
    }
    if ($admin) {
        $todo[] = ['n' => $admin['review'], 'tone' => 'bad', 'icon' => 'bi-graph-up-arrow', 't' => 'Check unusual usage', 's' => 'Wastage or unexplained loss reported', 'href' => route('inventory.material-requests.index', ['tab' => 'review'])];
        $todo[] = ['n' => $admin['unreconciled'], 'tone' => 'warn', 'icon' => 'bi-hourglass-split', 't' => 'Usage not recorded', 's' => 'Past events without a usage report', 'href' => route('inventory.reports.show', 'reconciliation')];
        $todo[] = ['n' => $admin['alerts'], 'tone' => 'warn', 'icon' => 'bi-exclamation-triangle', 't' => 'Low or out of stock', 's' => 'Items at or below the minimum you set', 'href' => route('inventory.stock', ['show' => 'low'])];
    }
    $todo[] = ['n' => $kitchen['usage'], 'tone' => 'info', 'icon' => 'bi-clipboard-data', 't' => 'Record your usage', 's' => 'Your issued materials — report used / returned', 'href' => route('inventory.material-requests.index', ['tab' => 'mine'])];
    $todo[] = ['n' => $kitchen['waiting'], 'tone' => '', 'icon' => 'bi-hourglass', 't' => 'Your requests waiting approval', 's' => 'Nothing to do — just for your info', 'href' => route('inventory.material-requests.index', ['tab' => 'mine'])];
    $todo = array_values(array_filter($todo, fn ($t) => $t['n'] > 0));

    $reqRow = function ($r) {
        $d = $r->required_date;
        return [
            'today' => $d->isToday(),
            'day' => $d->isToday() ? 'Today' : ($d->isTomorrow() ? 'Tmrw' : $d->format('D')),
            'date' => $d->format('d M'),
            'time' => $r->required_time ? \Carbon\Carbon::parse($r->required_time)->format('h:i A') : null,
        ];
    };
@endphp

<div class="sh">
    <div class="sh-hero">
        <div style="flex:1;min-width:0">
            <h1>{{ $greet }}, {{ collect(explode(' ', trim($u->name)))->first(fn ($w) => $w !== '' && ! str_ends_with($w, '.')) ?? $u->name }}</h1>
            <p>{{ now('Asia/Kolkata')->format('l, d F') }} · Store &amp; Inventory</p>
        </div>
    </div>

    {{-- Main actions --}}
    <div class="sh-acts">
        <a class="sh-act --main" href="{{ route('inventory.material-requests.create') }}"><i class="bi bi-basket"></i><span>Request materials<small>Ask the store for items</small></span></a>
        <a class="sh-act" href="{{ route('inventory.purchases.create') }}"><i class="bi bi-bag-plus"></i><span>New purchase<small>Enter what you bought</small></span></a>
        @if($u->isAdmin())
            <a class="sh-act" href="{{ route('admin.inventory.bills.index') }}"><i class="bi bi-upc-scan"></i><span>Scan a bill<small>Photo → purchase</small></span></a>
        @elseif($isStoreSide)
            <a class="sh-act" href="{{ route('inventory.stock') }}"><i class="bi bi-boxes"></i><span>Check stock<small>What is in store</small></span></a>
        @else
            <a class="sh-act" href="{{ route('inventory.material-requests.index', ['tab' => 'mine']) }}"><i class="bi bi-list-check"></i><span>My requests<small>Status and usage</small></span></a>
        @endif
    </div>

    <div class="sh-grid">
        <div class="sh-col">
            {{-- Needs attention --}}
            <section class="sh-card" aria-labelledby="shTodo" style="--o:1">
                <h2 id="shTodo"><i class="bi bi-lightning-charge" style="color:var(--ef-gold)"></i> Needs attention</h2>
                @if($todo)
                    <div class="sh-todo">
                        @foreach($todo as $t)
                            <a href="{{ $t['href'] }}" data-tone="{{ $t['tone'] }}">
                                <span class="sh-todo-i"><i class="bi {{ $t['icon'] }}"></i></span>
                                <span class="sh-todo-b"><span class="sh-todo-t">{{ $t['t'] }}</span><span class="sh-todo-s">{{ $t['s'] }}</span></span>
                                <span class="sh-todo-n">{{ $t['n'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="sh-clear"><i class="bi bi-check-circle-fill"></i><span><b style="color:var(--ef-ink)">All clear.</b> Nothing is waiting on you right now.</span></div>
                @endif
            </section>

            {{-- Coming up --}}
            @if($isStoreSide)
                <section class="sh-card" aria-labelledby="shSoon" style="--o:4">
                    <h2 id="shSoon"><i class="bi bi-calendar-event" style="color:var(--ef-gold)"></i> Needed today &amp; tomorrow
                        <a class="sh-more" href="{{ route('inventory.material-requests.index', ['tab' => 'all']) }}">All</a></h2>
                    @forelse($upcoming as $r)
                        @php $w = $reqRow($r); @endphp
                        <a class="sh-req" href="{{ route('inventory.material-requests.show', $r) }}">
                            <span class="sh-when {{ $w['today'] ? '--today' : '' }}"><b>{{ $w['day'] }}</b><span>{{ $w['time'] ?? $w['date'] }}</span></span>
                            <span class="sh-req-b"><span class="sh-req-t">{{ $r->preparation_name ?: $r->forLabel() }}</span>
                                <span class="sh-req-s">{{ $r->requester?->name }}@if($r->guest_count) · {{ $r->guest_count }} people @endif · {{ $r->reference() }}</span></span>
                            @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($r->status)])
                        </a>
                    @empty
                        <div class="sh-empty">No material requests for today or tomorrow.</div>
                    @endforelse
                </section>
            @endif

            {{-- My requests --}}
            <section class="sh-card" aria-labelledby="shMine" style="--o:5">
                <h2 id="shMine"><i class="bi bi-person" style="color:var(--ef-gold)"></i> My recent requests
                    @if($kitchen['recent']->isNotEmpty())<a class="sh-more" href="{{ route('inventory.material-requests.index', ['tab' => 'mine']) }}">All</a>@endif</h2>
                @forelse($kitchen['recent'] as $r)
                    @php $w = $reqRow($r); @endphp
                    <a class="sh-req" href="{{ route('inventory.material-requests.show', $r) }}">
                        <span class="sh-when {{ $w['today'] ? '--today' : '' }}"><b>{{ $w['day'] }}</b><span>{{ $w['date'] }}</span></span>
                        <span class="sh-req-b"><span class="sh-req-t">{{ $r->preparation_name ?: $r->forLabel() }}</span><span class="sh-req-s">{{ $r->reference() }}</span></span>
                        @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($r->status)])
                    </a>
                @empty
                    <div class="sh-empty">You have not requested anything yet. <a href="{{ route('inventory.material-requests.create') }}">Request materials</a></div>
                @endforelse
            </section>
        </div>

        <div class="sh-col">
            {{-- Find an item --}}
            <section class="sh-card" aria-labelledby="shFind" style="--o:2">
                <h2 id="shFind"><i class="bi bi-search" style="color:var(--ef-gold)"></i> Find an item</h2>
                <div class="sh-search"><i class="bi bi-search"></i>
                    <div><input type="text" class="ic-input" id="icFindItem" placeholder="English, தமிழ் or Tanglish — e.g. parupu" autocomplete="off" aria-label="Find an item"></div>
                </div>
                <div class="sh-found" id="icFindResult" aria-live="polite"></div>
            </section>

            {{-- Stock snapshot --}}
            @if($snapshot)
                <section class="sh-card" aria-labelledby="shStock" style="--o:3">
                    <h2 id="shStock"><i class="bi bi-boxes" style="color:var(--ef-gold)"></i> Stock
                        <a class="sh-more" href="{{ route('inventory.stock') }}">Open</a></h2>
                    <div class="sh-snap">
                        <a data-k="in" href="{{ route('inventory.stock', ['show' => 'in']) }}"><small><i class="bi bi-check2-circle"></i>In stock</small><strong>{{ $snapshot['in'] }}</strong></a>
                        <a data-k="low" href="{{ route('inventory.stock', ['show' => 'low']) }}"><small><i class="bi bi-exclamation-triangle"></i>Low</small><strong>{{ $snapshot['low'] }}</strong></a>
                        <a data-k="out" href="{{ route('inventory.stock', ['show' => 'out']) }}"><small><i class="bi bi-x-octagon"></i>Out</small><strong>{{ $snapshot['out'] }}</strong></a>
                        <a data-k="incoming" href="{{ route('inventory.stock', ['show' => 'incoming']) }}"><small><i class="bi bi-truck"></i>Arriving</small><strong>{{ $snapshot['incoming'] }}</strong></a>
                    </div>
                    @if($snapshot['value'] !== null && $snapshot['value'] > 0)
                        <div class="sh-value">Stock value (at average cost): <b>₹{{ number_format($snapshot['value'], 0) }}</b></div>
                    @endif
                </section>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const out = document.getElementById('icFindResult');
    const admin = @json($u->isAdmin());
    const storeSide = @json($isStoreSide);
    const itemBase = @json(url('admin/inventory/items'));
    const stockUrl = @json(route('inventory.stock'));
    const reqUrl = @json(route('inventory.material-requests.create'));
    const btn = (href, icon, text, gold) => {
        const a = document.createElement('a');
        a.className = 'ic-btn ic-btn--sm' + (gold ? ' ic-btn--gold' : '');
        a.href = href; a.innerHTML = '<i class="bi ' + icon + '"></i> ';
        a.append(text);
        return a;
    };
    window.IcItemPicker.attach(document.getElementById('icFindItem'), {
        onPick(r) {
            out.textContent = '';
            const main = document.createElement('div'); main.className = 'sh-found-main';
            const s = document.createElement('strong'); s.textContent = r.name + (r.name_ta ? ' · ' + r.name_ta : '');
            const sub = document.createElement('span'); sub.textContent = (r.category ? r.category + ' · ' : '') + 'stock unit ' + r.unit;
            main.append(s, sub);
            const acts = document.createElement('div'); acts.className = 'sh-found-acts';
            if (storeSide) acts.append(btn(stockUrl + '?q=' + encodeURIComponent(r.name), 'bi-boxes', 'Stock'));
            if (admin) acts.append(btn(itemBase + '/' + r.id, 'bi-journal-text', 'Item'));
            acts.append(btn(reqUrl, 'bi-basket', 'Request', true));
            out.append(main, acts);
            out.classList.add('--on');
        },
        onClear() { out.classList.remove('--on'); out.textContent = ''; },
    });
})();
</script>
@endpush
</x-admin-layout>
