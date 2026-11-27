<x-admin-layout title="Store Stock">
@include('inventory._styles')

@push('styles')
<style>
/* Store stock — same language as Purchases / Material requests: header, connected colour strip,
   one panel (search + category chips + table on desktop / cards on phones). Filtering is instant (JS). */
.st-page{max-width:1400px;display:flex;flex-direction:column;gap:14px}
.st-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.st-head h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.st-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.st-head p b{color:var(--ef-ink)}
.st-head-act{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.st-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.st-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.st-btn .bi{color:var(--ef-gold)}
.st-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.st-btn.--main .bi{color:#f0c97a}
@media (max-width:639.98px){.st-head h1{font-size:1.25rem}.st-head p .--long{display:none}.st-head-act{width:100%;display:grid;grid-template-columns:repeat(4,minmax(0,1fr))}.st-btn{padding:6px;flex-direction:column;gap:2px;font-size:.7rem;min-height:52px}}

/* status strip = filters */
.st-tiles{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.st-tile{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;min-width:0;background:linear-gradient(var(--tint,transparent),var(--tint,transparent)),var(--ef-surface)}
.st-tile:hover{color:var(--ef-ink);filter:brightness(.98)}
.st-tile small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.st-tile small .bi{color:var(--acc)}
.st-tile strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.st-tile.--zero strong{color:var(--ef-faint)}
.st-tile.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.st-tile[data-k=all]{--acc:var(--ef-ink)}
.st-tile[data-k=in]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.st-tile[data-k=low]{--acc:#9a6413;--tint:rgba(200,144,42,.08)}
.st-tile[data-k=out]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.05)}
.st-tile[data-k=incoming]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.st-tile:focus-visible{outline:2px solid var(--ef-emerald);outline-offset:-2px}
@media (max-width:719.98px){.st-tiles{grid-template-columns:repeat(3,minmax(0,1fr))}.st-tile{padding:8px 10px}.st-tile strong{font-size:1.15rem}.st-tile[data-k=incoming]{grid-column:span 2}}

/* panel */
.st-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);border-top:3px solid var(--ef-gold);min-width:0}
.st-tools{position:sticky;top:0;z-index:5;background:var(--ef-surface);border-radius:11px 11px 0 0;padding:10px 12px 8px;border-bottom:1px solid var(--ef-border)}
.st-search{position:relative}
.st-search .bi-search{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint);pointer-events:none}
.st-search input{width:100%;min-height:44px;padding:8px 44px 8px 36px;font-size:16px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink)}
.st-search input:focus{outline:none;border-color:var(--ef-gold)}
.st-clear{position:absolute;right:2px;top:2px;width:40px;height:40px;border:0;background:none;color:var(--ef-faint);border-radius:10px;cursor:pointer}
.st-clear[hidden]{display:none}
.st-cats{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;margin-top:8px}
.st-cats::-webkit-scrollbar{display:none}
@media (min-width:1100px){.st-cats{flex-wrap:wrap;overflow:visible}}
.st-cat{flex:0 0 auto;min-height:34px;padding:0 12px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink-2);font-weight:700;font-size:.8rem;white-space:nowrap;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.st-cat em{font-style:normal;font-size:.7rem;color:var(--ef-muted)}
.st-cat:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.st-cat.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.st-cat.--on em{color:#f0c97a}
.st-count{font-size:.78rem;color:var(--ef-muted);margin-top:8px}
.st-count b{color:var(--ef-ink)}

/* rows */
.st-list{display:flex;flex-direction:column}
.st-head-row{display:none}
.st-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:11px 14px;border-top:1px solid var(--ef-border);border-left:3px solid var(--st-tone,transparent);color:var(--ef-ink)}
.st-list>.st-item:first-of-type{border-top:0}
.st-item[hidden]{display:none}
.st-item:hover{background:var(--ef-bg-subtle)}
.st-item[data-state=low]{--st-tone:#d89a3d}
.st-item[data-state=out]{--st-tone:var(--ef-danger)}
.st-name{min-width:0;font-weight:750;font-size:.94rem;line-height:1.3;overflow-wrap:anywhere}
.st-name a{color:inherit;text-decoration:none}
.st-name a:hover{text-decoration:underline}
.st-ta{display:block;font-weight:500;font-size:.8rem;color:var(--ef-muted)}
.st-cat-l{display:inline-block;margin-top:2px;padding:0 7px;border-radius:6px;font-size:.66rem;font-weight:700;background:var(--ef-bg-subtle);color:var(--ef-muted)}
.st-qty{text-align:right;line-height:1.1;white-space:nowrap}
.st-qty strong{font-size:1.2rem;font-weight:800;font-variant-numeric:tabular-nums}
.st-qty span{font-size:.76rem;color:var(--ef-muted);font-weight:600;margin-left:3px}
.st-qty small{display:block;font-size:.68rem;color:var(--ef-faint);font-weight:600;margin-top:2px}
.st-item[data-state=out] .st-qty strong{color:var(--ef-danger)}
.st-item[data-state=low] .st-qty strong{color:#9a6413}
.st-bar{grid-column:1/-1;height:6px;border-radius:99px;background:var(--ef-bg-subtle);overflow:hidden}
.st-bar i{display:block;height:100%;border-radius:inherit;background:var(--ef-emerald)}
.st-item[data-state=low] .st-bar i{background:#d89a3d}
.st-item[data-state=out] .st-bar i{background:var(--ef-danger)}
.st-meta{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:4px 6px}
.st-meta:empty{display:none}
.st-tag{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:8px;font-size:.72rem;font-weight:700;background:var(--ef-bg-subtle);color:var(--ef-muted)}
.st-tag.--in{background:rgba(59,110,165,.12);color:#2c5784}
.st-tag.--warn{background:rgba(216,154,61,.16);color:#7D5218}
.st-tag.--bad{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.st-tag.--state{font-weight:800}
@media (min-width:900px){
  .st-head-row{display:grid;grid-template-columns:minmax(0,1.5fr) 150px minmax(0,1.5fr) 130px;gap:14px;padding:8px 17px;font-size:.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ef-muted);border-bottom:1px solid var(--ef-border);background:var(--ef-bg-subtle)}
  .st-head-row span:last-child{text-align:right}
  .st-item{grid-template-columns:minmax(0,1.5fr) 150px minmax(0,1.5fr) 130px;gap:14px}
  .st-head-row+.st-item{border-top:0}
  .st-bar{grid-column:2;grid-row:1}
  .st-meta{grid-column:3;grid-row:1}
  .st-qty{grid-column:4;grid-row:1}
}
.st-empty{text-align:center;padding:36px 16px;color:var(--ef-muted)}
.st-empty[hidden]{display:none}
.st-empty .bi{font-size:1.8rem;color:var(--ef-faint)}
.st-help{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);padding:4px 14px;font-size:.85rem;color:var(--ef-muted);border-top:3px solid #2c5784}
.st-help summary{cursor:pointer;font-weight:800;color:var(--ef-ink);min-height:42px;display:flex;align-items:center;gap:6px}
.st-help dl{margin:4px 0 10px;display:grid;grid-template-columns:auto 1fr;gap:4px 12px}
.st-help dt{font-weight:700;color:var(--ef-ink)}
.st-help dd{margin:0}
</style>
@endpush

@php
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0';
    $isAdmin = auth()->user()->isAdmin();
    $visible = $shown->count();
    $tiles = [
        '' => ['All items', 'bi-box-seam', $counts['all']],
        'in' => ['In stock', 'bi-check2-circle', $counts['in']],
        'low' => ['Low / out', 'bi-exclamation-triangle', $counts['low']],
        'out' => ['Out of stock', 'bi-x-octagon', $counts['out']],
        'incoming' => ['Arriving', 'bi-truck', $counts['incoming']],
    ];
    $catCounts = $items->countBy('inventory_category_id');
    $value = $isAdmin ? $items->sum(fn ($i) => $i->current_stock > 0 ? $i->current_stock * (float) ($i->average_cost ?? 0) : 0) : 0;
    $url = fn ($over) => route('inventory.stock', array_filter(array_merge(['q' => $search, 'show' => $show, 'cat' => $cat], $over)));
@endphp

<div class="st-page" id="stPage">
    <header class="st-head">
        <div style="min-width:0;flex:1 1 260px">
            <h1>Store stock</h1>
            <p><b>{{ $counts['in'] }}</b> in stock · <b>{{ $counts['low'] }}</b> low or out @if($isAdmin && $value > 0) · worth <b>₹{{ number_format($value, 0) }}</b>@endif<span class="--long"> — what is on the shelf, what is low and what is on the way.</span></p>
        </div>
        <div class="st-head-act">
            <a class="st-btn" href="{{ route('inventory.reports.show', 'movements') }}" title="Every stock in / out"><i class="bi bi-arrow-left-right"></i> Movements</a>
            @if($isAdmin)
                <a class="st-btn" href="{{ route('admin.inventory.alerts.index') }}" title="Low-stock alerts"><i class="bi bi-bell"></i> Alerts</a>
            @endif
            <a class="st-btn" href="{{ route('inventory.purchases.index') }}"><i class="bi bi-truck"></i> Purchases</a>
            <a class="st-btn --main" href="{{ route('inventory.material-requests.index') }}"><i class="bi bi-basket"></i> Requests</a>
        </div>
    </header>

    <div class="st-tiles" role="group" aria-label="Filter by stock status">
        @foreach($tiles as $k => [$label, $icon, $n])
            <a class="st-tile {{ $show === $k ? '--on' : '' }} {{ $n ? '' : '--zero' }}" data-k="{{ $k ?: 'all' }}" data-show="{{ $k }}" href="{{ $url(['show' => $k]) }}" @if($show === $k) aria-current="true" @endif>
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $n }}</strong>
            </a>
        @endforeach
    </div>

    <section class="st-panel" aria-label="Items">
    <form class="st-tools" method="GET" action="{{ route('inventory.stock') }}" role="search">
        <input type="hidden" name="show" value="{{ $show }}" data-show-in>
        <input type="hidden" name="cat" value="{{ $cat }}" data-cat-in>
        <div class="st-search">
            <i class="bi bi-search"></i>
            <input class="ic-input" type="search" name="q" value="{{ $search }}" placeholder="Search item — English / தமிழ்" aria-label="Search items" autocomplete="off" data-q>
            <button type="button" class="st-clear" aria-label="Clear search" data-clear @if($search === '') hidden @endif><i class="bi bi-x-lg"></i></button>
        </div>
        @if($categories->count() > 1)
            <div class="st-cats" role="group" aria-label="Category">
                <a class="st-cat {{ ! $cat ? '--on' : '' }}" data-cat="" href="{{ $url(['cat' => null]) }}">All <em>{{ $counts['all'] }}</em></a>
                @foreach($categories as $c)
                    <a class="st-cat {{ $cat === $c->id ? '--on' : '' }}" data-cat="{{ $c->id }}" href="{{ $url(['cat' => $c->id]) }}">{{ $c->name }} <em>{{ $catCounts[$c->id] ?? 0 }}</em></a>
                @endforeach
            </div>
        @endif
        <div class="st-count" aria-live="polite"><span data-count>{{ $visible }}</span> of {{ $counts['all'] }} items</div>
    </form>

    <div class="st-list" data-list>
        <div class="st-head-row" aria-hidden="true"><span>Item</span><span>Level vs minimum</span><span>Status &amp; movement</span><span>Available</span></div>
        @foreach($items as $i)
            @php
                $ref = max((float) $i->maximum_stock, (float) $i->minimum_stock * 2, (float) $i->physical, 0.001);
                $pct = $i->state === 'out' ? 0 : max(4, min(100, round($i->current_stock / $ref * 100)));
            @endphp
            <div class="st-item" data-item data-state="{{ $i->state }}" data-cat="{{ $i->inventory_category_id }}"
                 data-in="{{ $i->incoming > 0 ? 1 : 0 }}" data-s="{{ $i->haystack }}" @unless($shown->has($i->id)) hidden @endunless>
                <div class="st-name">
                    @if($isAdmin)<a href="{{ route('admin.inventory.items.show', $i) }}">{{ $i->name }}</a>@else{{ $i->name }}@endif
                    @if($i->name_ta)<span class="st-ta">{{ $i->name_ta }}</span>@endif
                    <span class="st-cat-l">{{ $i->category?->name }}</span>
                </div>
                <div class="st-qty">
                    <strong>{{ $q($i->current_stock) }}</strong><span>{{ $i->unit }}</span>
                    @if((float) $i->minimum_stock > 0)<small>min {{ $q($i->minimum_stock) }}</small>@endif
                </div>
                <div class="st-bar" title="Minimum {{ $q($i->minimum_stock) }} {{ $i->unit }}"><i style="width:{{ $pct }}%"></i></div>
                <div class="st-meta">
                    @if($i->state === 'out')<span class="st-tag --bad --state"><i class="bi bi-x-octagon"></i>Out of stock</span>
                    @elseif($i->state === 'low')<span class="st-tag --warn --state"><i class="bi bi-exclamation-triangle"></i>Low · min {{ $q($i->minimum_stock) }}</span>@endif
                    @if($i->incoming > 0)<span class="st-tag --in"><i class="bi bi-truck"></i>{{ $q($i->incoming) }} {{ $i->unit }} arriving</span>@endif
                    @if($i->awaiting > 0)<span class="st-tag"><i class="bi bi-box-arrow-up"></i>{{ $q($i->awaiting) }} to hand over · {{ $q($i->physical) }} on shelf</span>@endif
                    @if($i->pending > 0)<span class="st-tag {{ $i->pending > $i->current_stock ? '--bad' : '' }}"><i class="bi bi-hourglass-split"></i>{{ $q($i->pending) }} requested</span>@endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="st-empty" data-empty @if($visible > 0) hidden @endif>
        <i class="bi bi-search"></i>
        <p style="margin:6px 0 12px">No items match.</p>
        <a class="st-btn" href="{{ route('inventory.stock') }}" data-reset>Show all items</a>
    </div>
    </section>

    <details class="st-help">
        <summary><i class="bi bi-info-circle"></i> What do these numbers mean?</summary>
        <dl>
            <dt>Available</dt><dd>Free to approve for new requests.</dd>
            <dt>On shelf</dt><dd>Physically in the store, incl. items approved but not yet collected.</dd>
            <dt>To hand over</dt><dd>Approved requests waiting to be collected (already deducted).</dd>
            <dt>Arriving</dt><dd>Approved purchases not yet received. Stock rises when goods are received.</dd>
            <dt>Requested</dt><dd>Requests waiting for approval. Red when more than available.</dd>
        </dl>
    </details>
</div>

@push('scripts')
<script>
(() => {
    const page = document.getElementById('stPage'); if (!page) return;
    const qIn = page.querySelector('[data-q]'), clear = page.querySelector('[data-clear]');
    const rows = [...page.querySelectorAll('[data-item]')];
    const st = { q: qIn.value.trim().toLowerCase(), show: page.querySelector('[data-show-in]').value, cat: page.querySelector('[data-cat-in]').value };
    const okShow = r => st.show === 'low' ? r.dataset.state !== 'ok' : st.show === 'out' ? r.dataset.state === 'out' : st.show === 'in' ? r.dataset.state !== 'out' : st.show === 'incoming' ? r.dataset.in === '1' : true;

    function apply(push = true) {
        let n = 0;
        rows.forEach(r => {
            const v = (!st.q || r.dataset.s.includes(st.q)) && (!st.cat || r.dataset.cat === st.cat) && okShow(r);
            r.hidden = !v; if (v) n++;
        });
        page.querySelector('[data-count]').textContent = n;
        page.querySelector('[data-empty]').hidden = n > 0;
        clear.hidden = !qIn.value;
        page.querySelectorAll('[data-show]').forEach(t => { const on = t.dataset.show === st.show; t.classList.toggle('--on', on); on ? t.setAttribute('aria-current', 'true') : t.removeAttribute('aria-current'); });
        page.querySelectorAll('[data-cat]:not([data-item])').forEach(c => c.classList.toggle('--on', c.dataset.cat === st.cat));
        page.querySelector('[data-show-in]').value = st.show; page.querySelector('[data-cat-in]').value = st.cat;
        if (push) {
            const u = new URL(location.href);
            [['q', qIn.value.trim()], ['show', st.show], ['cat', st.cat]].forEach(([k, v]) => v ? u.searchParams.set(k, v) : u.searchParams.delete(k));
            history.replaceState(null, '', u);
        }
    }
    let t; qIn.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { st.q = qIn.value.trim().toLowerCase(); apply(); }, 120); });
    qIn.closest('form').addEventListener('submit', e => { e.preventDefault(); qIn.blur(); });
    clear.addEventListener('click', () => { qIn.value = ''; st.q = ''; apply(); qIn.focus(); });
    page.addEventListener('click', e => {
        const tile = e.target.closest('[data-show]'), cat = e.target.closest('.st-cat'), reset = e.target.closest('[data-reset]');
        if (tile) { e.preventDefault(); st.show = tile.dataset.show === st.show && st.show ? '' : tile.dataset.show; apply(); }
        else if (cat) { e.preventDefault(); st.cat = cat.dataset.cat; apply(); cat.scrollIntoView({ inline: 'nearest', block: 'nearest' }); }
        else if (reset) { e.preventDefault(); qIn.value = ''; Object.assign(st, { q: '', show: '', cat: '' }); apply(); }
    });
})();
</script>
@endpush
</x-admin-layout>
