<x-admin-layout title="Store Stock">
@include('inventory._styles')

@push('styles')
<style>
/* Store stock — mobile-first, same language as bill review / material requests (--ef-* tokens). */
.st-page{max-width:1100px;margin:0 auto}
.st-head{display:flex;align-items:center;gap:10px;margin:2px 0 14px}
.st-head h1{margin:0;font-size:1.15rem;font-weight:800;letter-spacing:-.01em;color:var(--ef-ink)}
.st-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
@media (min-width:768px){.st-head h1{font-size:1.35rem}}
@media (max-width:519.98px){.st-head p{display:none}}
.st-head-act{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
@media (max-width:519.98px){.st-head-act .st-txt{display:none}}

/* tiles = filters */
.st-tiles{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:12px}
@media (max-width:519.98px){.st-tiles{grid-template-columns:repeat(2,minmax(0,1fr))}}
.st-tile{display:flex;flex-direction:column;gap:2px;padding:10px 12px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:var(--ef-shadow);color:var(--ef-ink);text-decoration:none;text-align:left;cursor:pointer;min-height:64px;font:inherit}
.st-tile:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.st-tile:focus-visible{outline:3px solid var(--ef-gold-soft);outline-offset:2px}
.st-tile small{font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--ef-muted);display:flex;align-items:center;gap:6px}
.st-tile strong{font-size:1.35rem;font-weight:800;line-height:1.1;font-variant-numeric:tabular-nums}
.st-tile[data-k=low] strong{color:#9a6413}
.st-tile[data-k=in] strong{color:var(--ef-emerald)}
.st-tile[data-k=incoming] strong{color:#3b6ea5}
.st-tile.--on{border-color:var(--ef-ink);box-shadow:0 0 0 1px var(--ef-ink) inset,var(--ef-shadow)}

/* search bar (sticky under header) */
.st-tools{position:sticky;top:var(--ef-topbar-h,64px);z-index:20;background:var(--ef-bg,#f5f3ef);padding:6px 0 8px;margin:0 0 4px}
.st-search{position:relative}
.st-search .bi-search{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--ef-faint);pointer-events:none}
.st-search input{width:100%;min-height:48px;padding:10px 44px 10px 40px;font-size:16px;border-radius:12px}
.st-clear{position:absolute;right:4px;top:4px;width:40px;height:40px;border:0;background:none;color:var(--ef-faint);border-radius:10px;cursor:pointer;font-size:1.05rem}
.st-clear[hidden]{display:none}
.st-cats{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;margin:8px -16px 0;padding:0 16px 2px}
.st-cats::-webkit-scrollbar{display:none}
@media (min-width:768px){.st-cats{margin:8px 0 0;padding:0 0 2px}}
.st-cat{flex:0 0 auto;min-height:36px;padding:5px 12px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-muted);font-weight:700;font-size:.82rem;cursor:pointer;white-space:nowrap;text-decoration:none;display:inline-flex;align-items:center}
.st-cat:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.st-cat.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.st-count{font-size:.8rem;color:var(--ef-muted);margin:6px 2px 8px}

/* item rows: card on mobile, row on desktop */
.st-list{display:flex;flex-direction:column;gap:8px}
.st-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:12px 14px;border:1px solid var(--ef-border);border-left:3px solid var(--st-tone,var(--ef-border));border-radius:14px;background:var(--ef-surface);box-shadow:var(--ef-shadow);color:var(--ef-ink)}
.st-item[hidden]{display:none}
.st-item[data-state=low]{--st-tone:var(--ef-amber)}
.st-item[data-state=out]{--st-tone:var(--ef-danger)}
.st-name{min-width:0;font-weight:750;font-size:.98rem;line-height:1.3;overflow-wrap:anywhere}
.st-name a{color:inherit;text-decoration:none}
.st-name a:hover{text-decoration:underline}
.st-ta{display:block;font-weight:500;font-size:.84rem;color:var(--ef-muted)}
.st-cat-l{font-size:.76rem;color:var(--ef-faint);font-weight:600}
.st-qty{text-align:right;line-height:1.1}
.st-qty strong{display:block;font-size:1.3rem;font-weight:800;font-variant-numeric:tabular-nums}
.st-qty span{font-size:.78rem;color:var(--ef-muted);font-weight:600}
.st-item[data-state=out] .st-qty strong{color:var(--ef-danger)}
.st-item[data-state=low] .st-qty strong{color:#9a6413}
.st-bar{grid-column:1/-1;height:6px;border-radius:99px;background:var(--ef-bg-subtle);overflow:hidden;margin-top:4px}
.st-bar i{display:block;height:100%;border-radius:inherit;background:var(--ef-emerald)}
.st-item[data-state=low] .st-bar i{background:var(--ef-amber)}
.st-item[data-state=out] .st-bar i{background:var(--ef-danger)}
.st-meta{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:4px 6px;margin-top:4px}
.st-tag{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;font-size:.76rem;font-weight:650;background:var(--ef-bg-subtle);color:var(--ef-muted)}
.st-tag.--in{background:rgba(59,110,165,.12);color:#2c5784}
.st-tag.--warn{background:rgba(216,154,61,.16);color:#7D5218}
.st-tag.--bad{background:rgba(200,60,60,.1);color:var(--ef-danger)}
.st-tag.--state{font-weight:800}

@media (min-width:900px){
  .st-list{gap:0;border:1px solid var(--ef-border);border-radius:14px;overflow:hidden;background:var(--ef-surface);box-shadow:var(--ef-shadow)}
  .st-item{grid-template-columns:minmax(0,1.6fr) 140px minmax(0,1.4fr) 110px;border:0;border-left:3px solid var(--st-tone,transparent);border-bottom:1px solid var(--ef-border);border-radius:0;box-shadow:none;padding:10px 14px}
  .st-item:last-child{border-bottom:0}
  .st-item:hover{background:var(--ef-bg-subtle)}
  .st-bar{grid-column:auto;grid-row:1;grid-column-start:2;margin:0}
  .st-meta{grid-column:3;grid-row:1;margin:0}
  .st-qty{grid-column:4;grid-row:1}
  .st-head-row{display:grid!important}
}
.st-head-row{display:none;grid-template-columns:minmax(0,1.6fr) 140px minmax(0,1.4fr) 110px;gap:12px;padding:8px 17px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--ef-muted);border-bottom:1px solid var(--ef-border);background:var(--ef-bg-subtle)}
.st-head-row span:last-child{text-align:right}

.st-empty{text-align:center;padding:36px 16px;border:1px dashed var(--ef-border);border-radius:14px;background:var(--ef-surface);color:var(--ef-muted);margin-top:8px}
.st-empty[hidden]{display:none}
.st-empty .bi{font-size:1.8rem;color:var(--ef-faint)}
.st-help{margin-top:14px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);padding:4px 14px;font-size:.85rem;color:var(--ef-muted)}
.st-help summary{cursor:pointer;font-weight:700;color:var(--ef-ink);min-height:40px;display:flex;align-items:center;gap:6px}
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
        'incoming' => ['Incoming', 'bi-truck', $counts['incoming']],
    ];
    $url = fn ($over) => route('inventory.stock', array_filter(array_merge(['q' => $search, 'show' => $show, 'cat' => $cat], $over)));
@endphp

<div class="st-page" id="stPage">
    <div class="st-head">
        <div style="min-width:0">
            <h1>Store stock</h1>
            <p>What is on the shelf, what is low and what is on the way.</p>
        </div>
        <div class="st-head-act">
            <a class="ic-btn ic-btn--sm" href="{{ route('inventory.reports.show', 'movements') }}" title="Every stock in / out"><i class="bi bi-arrow-left-right"></i><span class="st-txt"> Movements</span></a>
            @if(auth()->user()->isAdmin())
                <a class="ic-btn ic-btn--sm" href="{{ route('admin.inventory.alerts.index') }}" title="Low-stock alerts"><i class="bi bi-bell"></i><span class="st-txt"> Alerts</span></a>
            @endif
            <a class="ic-btn ic-btn--sm" href="{{ route('inventory.purchases.index') }}"><i class="bi bi-truck"></i><span class="st-txt"> Purchases</span></a>
            <a class="ic-btn ic-btn--sm ic-btn--gold" href="{{ route('inventory.material-requests.index') }}"><i class="bi bi-basket"></i><span class="st-txt"> Requests</span></a>
        </div>
    </div>

    <div class="st-tiles" role="group" aria-label="Filter by stock status">
        @foreach($tiles as $k => [$label, $icon, $n])
            <a class="st-tile {{ $show === $k ? '--on' : '' }}" data-k="{{ $k ?: 'all' }}" data-show="{{ $k }}" href="{{ $url(['show' => $k]) }}" @if($show === $k) aria-current="true" @endif>
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $n }}</strong>
            </a>
        @endforeach
    </div>

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
                <a class="st-cat {{ ! $cat ? '--on' : '' }}" data-cat="" href="{{ $url(['cat' => null]) }}">All</a>
                @foreach($categories as $c)
                    <a class="st-cat {{ $cat === $c->id ? '--on' : '' }}" data-cat="{{ $c->id }}" href="{{ $url(['cat' => $c->id]) }}">{{ $c->name }}</a>
                @endforeach
            </div>
        @endif
    </form>

    <div class="st-count" aria-live="polite"><span data-count>{{ $visible }}</span> of {{ $counts['all'] }} items</div>

    <div class="st-list" data-list>
        <div class="st-head-row" aria-hidden="true"><span>Item</span><span>Level</span><span>Also</span><span>Available</span></div>
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
        <a class="ic-btn ic-btn--sm" href="{{ route('inventory.stock') }}" data-reset>Show all items</a>
    </div>

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
