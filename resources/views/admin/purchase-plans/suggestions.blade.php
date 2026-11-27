<x-admin-layout title="Purchase Suggestions">
@include('inventory._styles')
@php
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0';
    $toBuy = $suggestions->where('covered', false);
    $counts0 = null;
    $counts = [
        'all' => $toBuy->count(),
        'urgent' => $toBuy->where('priority', 'urgent')->count(),
        'high' => $toBuy->where('priority', 'high')->count(),
        'normal' => $toBuy->where('priority', 'normal')->count(),
        'empty' => $toBuy->where('priority', 'empty')->count(),
        'covered' => $suggestions->where('covered', true)->count(),
    ];
    $estimate = $toBuy->sum(fn ($i) => (float) ($i->average_cost ?? 0) * (float) $i->suggested_quantity);
    $tiles = [
        'all' => ['To buy', 'bi-cart', 'var(--ef-ink)', 'transparent'],
        'urgent' => ['Urgent', 'bi-exclamation-octagon', 'var(--ef-danger)', 'rgba(192,57,43,.05)'],
        'high' => ['High', 'bi-exclamation-triangle', '#b4531c', 'rgba(214,110,40,.07)'],
        'normal' => ['Below minimum', 'bi-arrow-down-circle', '#9a6413', 'rgba(200,144,42,.08)'],
        'empty' => ['Out · no minimum', 'bi-box', '#5f6b5a', 'rgba(95,107,90,.06)'],
        'covered' => ['Already on order', 'bi-truck', '#2c5784', 'rgba(59,110,165,.06)'],
    ];
@endphp
@push('styles')
<style>
/* Purchase suggestions — list-page standard (sg-*): filter tiles, search + category chips, select → plan. */
.sg{display:flex;flex-direction:column;gap:14px;max-width:1500px;padding-bottom:84px}
.sg-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.sg-head h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.sg-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted);max-width:780px}
.sg-head p b{color:var(--ef-ink)}
.sg-acts{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.sg-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer}
.sg-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.sg-btn .bi{color:var(--ef-gold)}
.sg-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.sg-btn.--main .bi{color:#f0c97a}
.sg-btn:disabled{opacity:.45;cursor:not-allowed}
@media (max-width:639.98px){.sg-head h1{font-size:1.25rem}.sg-acts{width:100%}.sg-acts .sg-btn{flex:1}}
.sg-q{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden}
.sg-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);border:0;text-align:left;font:inherit;cursor:pointer;min-width:0;background:linear-gradient(var(--tint),var(--tint)),var(--ef-surface)}
.sg-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sg-qi small .bi{color:var(--acc)}
.sg-qi strong{font-size:1.35rem;font-weight:800;color:var(--acc);line-height:1.15}
.sg-qi.--zero strong{color:var(--ef-faint)}
.sg-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
@media (max-width:899.98px){.sg-q{grid-template-columns:repeat(2,minmax(0,1fr))}.sg-qi[data-f=all],.sg-qi[data-f=covered]{grid-column:1/-1}.sg-qi strong{font-size:1.15rem}}
.sg-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);min-width:0;overflow:hidden;border-top:3px solid var(--ef-gold)}
.sg-bar{padding:10px 12px;border-bottom:1px solid var(--ef-border);display:flex;flex-direction:column;gap:8px}
.sg-search{position:relative;margin:0}
.sg-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.sg-search input{width:100%;min-height:42px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);font-size:16px;background:var(--ef-surface)}
.sg-cats{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none}
.sg-cats::-webkit-scrollbar{display:none}
@media (min-width:1100px){.sg-cats{flex-wrap:wrap;overflow:visible}}
.sg-cat{flex:0 0 auto;min-height:32px;padding:0 12px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:.78rem;font-weight:700;color:var(--ef-ink-2);cursor:pointer}
.sg-cat.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.sg-th,.sg-row{display:grid;grid-template-columns:30px minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:11px 14px}
.sg-th{display:none}
.sg-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent);cursor:pointer}
.sg-row[hidden]{display:none}
.sg-row:hover{background:var(--ef-bg-subtle)}
.sg-row.--sel{background:rgba(200,144,42,.07)}
.sg-row[data-p=urgent]{--tone:var(--ef-danger)}
.sg-row[data-p=high]{--tone:#d66e28}
.sg-row[data-p=normal]{--tone:#d89a3d}
.sg-row[data-p=empty]{--tone:var(--ef-border-strong)}
.sg-row[data-p=low]{--tone:#3b6ea5;opacity:.75}
.sg-row input[type=checkbox]{width:20px;height:20px;accent-color:var(--ef-gold)}
.sg-n b{display:block;font-size:.92rem;font-weight:750}
.sg-n b a{color:inherit;text-decoration:none}
.sg-n b a:hover{text-decoration:underline}
.sg-ta{font-weight:600;color:var(--ef-ink-2);font-size:.88rem;margin-left:4px}
.sg-n small{display:block;font-size:.74rem;color:var(--ef-muted)}
.sg-c{font-size:.82rem;white-space:nowrap;font-variant-numeric:tabular-nums}
.sg-c small{display:block;font-size:.7rem;color:var(--ef-muted)}
.sg-sug{text-align:right;white-space:nowrap}
.sg-sug b{font-size:1.05rem;font-weight:800;font-variant-numeric:tabular-nums}
.sg-sug small{display:block;font-size:.7rem;color:var(--ef-muted)}
.sg-chip{display:inline-block;padding:1px 7px;border-radius:7px;font-size:.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em}
.sg-chip.--urgent{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.sg-chip.--high{background:rgba(214,110,40,.12);color:#a3480f}
.sg-chip.--normal{background:rgba(200,144,42,.15);color:#8a5a10}
.sg-chip.--empty{background:var(--ef-bg-subtle);color:var(--ef-muted)}
.sg-chip.--low{background:rgba(59,110,165,.12);color:#2c5784}
.sg-m{grid-column:2/-1;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.74rem;color:var(--ef-muted)}
.sg-c-stock,.sg-c-use,.sg-c-coming,.sg-c-cost{display:none}
@media (min-width:1100px){
  .sg-th,.sg-row{grid-template-columns:30px minmax(200px,1fr) 120px 120px 130px 110px 120px;gap:12px}
  .sg-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .sg-th .--r{text-align:right}
  .sg-c-stock,.sg-c-use,.sg-c-coming,.sg-c-cost{display:block}
  .sg-c-cost{text-align:right}
  .sg-m{display:none}
}
.sg-empty{padding:34px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.sg-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.sg-note{border:1px dashed var(--ef-border-strong);border-radius:12px;padding:10px 14px;font-size:.82rem;color:var(--ef-muted);background:var(--ef-surface)}
.sg-note b{color:var(--ef-ink)}
.sg-go{position:fixed;left:0;right:0;bottom:0;z-index:60;display:flex;gap:10px;align-items:center;justify-content:flex-end;padding:10px 16px calc(10px + env(safe-area-inset-bottom,0px));background:var(--ef-surface);border-top:1px solid var(--ef-border);box-shadow:0 -6px 20px rgba(19,17,16,.08)}
@media (min-width:992px){.sg-go{left:var(--sb-width,280px)}}
.sg-go span{margin-right:auto;font-size:.84rem;color:var(--ef-muted)}
.sg-go span b{color:var(--ef-ink)}
</style>
@endpush

<form method="GET" action="{{ route('inventory.plans.create') }}" class="sg ic-wrap" id="sgPage">
    <header class="sg-head">
        <div style="flex:1 1 300px;min-width:0">
            <h1>Purchase suggestions</h1>
            <p>Items <b>below their minimum</b> and items <b>out of stock</b> — the ones the kitchen used in the last {{ \App\Services\PurchasePlanningService::USAGE_DAYS }} days first. Quantities already on order or in a plan are taken off. Tick what you want and create a plan.</p>
        </div>
        <div class="sg-acts">
            <a href="{{ route('inventory.plans.index') }}" class="sg-btn"><i class="bi bi-cart3"></i> Purchase plans</a>
            <a href="{{ route('inventory.stock', ['show' => 'low']) }}" class="sg-btn"><i class="bi bi-boxes"></i> Stock</a>
        </div>
    </header>

    <nav class="sg-q" aria-label="Filter suggestions">
        @foreach($tiles as $k => [$label, $icon, $acc, $tint])
            <button type="button" class="sg-qi {{ $k === 'all' ? '--on' : '' }} {{ $counts[$k] ? '' : '--zero' }}" data-f="{{ $k }}" style="--acc:{{ $acc }};--tint:{{ $tint }}">
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $counts[$k] }}</strong>
            </button>
        @endforeach
    </nav>

    @if($counts['empty'] > 0)
        <div class="sg-note"><i class="bi bi-info-circle"></i> <b>{{ $counts['empty'] }}</b> empty {{ Str::plural('item', $counts['empty']) }} have <b>no minimum level</b>, so there's no suggested quantity — tick them and type how much you need.
            Setting a minimum on items you always keep makes them appear with a quantity (and higher priority) when they run low.
            <a href="{{ route('admin.inventory.items.index') }}" style="font-weight:700">Item catalogue →</a></div>
    @endif

    <section class="sg-panel" aria-label="Suggested items">
        @if($suggestions->isNotEmpty())
            <div class="sg-bar">
                <label class="sg-search"><i class="bi bi-search"></i><input type="search" placeholder="Search — English, தமிழ், Tanglish (e.g. mundhiri, badam)" data-q aria-label="Search items" autocomplete="off"></label>
                @if($categories->count() > 1)
                    <div class="sg-cats" role="group" aria-label="Category">
                        <button type="button" class="sg-cat --on" data-cat="">All</button>
                        @foreach($categories as $c)<button type="button" class="sg-cat" data-cat="{{ $c->id }}">{{ $c->name }}</button>@endforeach
                    </div>
                @endif
            </div>
            <div class="sg-th" aria-hidden="true">
                <span><input type="checkbox" data-all aria-label="Select all shown" style="width:18px;height:18px;accent-color:var(--ef-gold)"></span>
                <span>Item</span><span>Stock</span><span>Used {{ \App\Services\PurchasePlanningService::USAGE_DAYS }}d</span><span>Coming</span><span class="--r">Suggested</span><span class="--r">Est. cost</span>
            </div>
        @endif
        @forelse($suggestions as $item)
            @php $cost = (float) ($item->average_cost ?? 0) * (float) $item->suggested_quantity; @endphp
            <label class="sg-row" data-row data-id="{{ $item->id }}" data-p="{{ $item->priority }}" data-covered="{{ $item->covered ? 1 : 0 }}" data-cat="{{ $item->inventory_category_id }}"
                   data-s="{{ mb_strtolower($item->name.' '.$item->name_ta) }}" data-cost="{{ $cost }}">
                <input type="checkbox" name="pick[]" value="{{ $item->id }}" aria-label="Select {{ $item->name }}" @disabled($item->covered)>
                <span class="sg-n">
                    <b><a href="{{ route('admin.inventory.items.show', $item) }}" onclick="event.stopPropagation()">{{ $item->name }}</a>@if($item->name_ta) <span class="sg-ta" lang="ta">{{ $item->name_ta }}</span>@endif</b>
                    <small><span class="sg-chip --{{ $item->priority }}">{{ $item->covered ? 'on order' : ($item->priority === 'empty' ? 'empty' : $item->priority) }}</span> {{ $item->reason }}@if($item->category) · {{ $item->category->name }}@endif</small>
                </span>
                <span class="sg-c sg-c-stock">{{ $q($item->current_stock) }} {{ $item->unit }}@if($item->tracked)<small>min {{ $q($item->minimum_stock) }}@if($item->maximum_stock > 0) · max {{ $q($item->maximum_stock) }}@endif</small>@else<small>no minimum set</small>@endif</span>
                <span class="sg-c sg-c-use">{{ $item->used_recent > 0 ? $q($item->used_recent).' '.$item->unit : '—' }}</span>
                <span class="sg-c sg-c-coming">@if($item->on_order > 0){{ $q($item->on_order) }} on order @endif @if($item->in_plans > 0)<small>{{ $q($item->in_plans) }} in plans</small>@endif @if(! $item->on_order && ! $item->in_plans)—@endif</span>
                <span class="sg-sug"><b>{{ $item->covered ? '—' : ($item->suggested_quantity === null ? 'you decide' : $q($item->suggested_quantity)) }}</b><small>{{ $item->covered ? 'covered' : $item->unit }}</small></span>
                <span class="sg-c sg-c-cost">{{ $cost > 0 ? '₹'.number_format($cost, 0) : '—' }}</span>
                <span class="sg-m">
                    <span>Stock {{ $q($item->current_stock) }} {{ $item->unit }}</span>
                    @if($item->used_recent > 0)<span>used {{ $q($item->used_recent) }}</span>@endif
                    @if($item->on_order > 0 || $item->in_plans > 0)<span>coming {{ $q($item->on_order + $item->in_plans) }}</span>@endif
                    @if($cost > 0)<span>≈ ₹{{ number_format($cost, 0) }}</span>@endif
                </span>
            </label>
        @empty
            <div class="sg-empty"><i class="bi bi-check-circle" style="color:var(--ef-emerald)"></i>Nothing to buy right now — no item is below its minimum and nothing in use has run out.</div>
        @endforelse
        <div class="sg-empty" data-none hidden><i class="bi bi-search"></i>No items match.</div>
    </section>

    @if($suggestions->isNotEmpty())
        <div class="sg-go">
            <span><b data-sel>0</b> selected · est. <b data-est>₹0</b> · total to buy ≈ ₹{{ number_format($estimate, 0) }}</span>
            <button type="submit" class="sg-btn --main" data-go disabled><i class="bi bi-cart-plus"></i> Create plan with selected</button>
        </div>
    @endif
</form>

@push('scripts')
<script>
(() => {
    const page = document.getElementById('sgPage'); if (!page) return;
    const rows = [...page.querySelectorAll('[data-row]')], q = page.querySelector('[data-q]'), all = page.querySelector('[data-all]');
    let f = 'all', cat = '', smart = null, timer, seq = 0;
    // Tamil / Tanglish / aliases / near-spellings: ask the shared item search (same engine as the item pickers)
    const searchUrl = @json(route('inventory.items.search'));
    function smartSearch(t) {
        clearTimeout(timer);
        if (t.length < 2) { smart = null; return apply(); }
        const my = ++seq;
        timer = setTimeout(async () => {
            try {
                const r = await fetch(searchUrl + '?limit=25&q=' + encodeURIComponent(t), { headers: { Accept: 'application/json' } });
                const d = r.ok ? await r.json() : { results: [] };
                if (my === seq) { smart = new Set((d.results || []).map(x => String(x.id))); apply(); }
            } catch (e) { /* offline: plain text match still works */ }
        }, 250);
    }
    const ok = r => (f === 'all' ? r.dataset.covered === '0' : f === 'covered' ? r.dataset.covered === '1' : r.dataset.p === f && r.dataset.covered === '0');
    function apply() {
        const t = (q?.value || '').trim().toLowerCase(); let n = 0;
        rows.forEach(r => { const v = ok(r) && (!cat || r.dataset.cat === cat) && (!t || r.dataset.s.includes(t) || (smart && smart.has(r.dataset.id))); r.hidden = !v; if (v) n++; });
        const none = page.querySelector('[data-none]'); if (none) none.hidden = n > 0 || !rows.length;
        page.querySelectorAll('[data-f]').forEach(b => b.classList.toggle('--on', b.dataset.f === f));
        page.querySelectorAll('[data-cat]').forEach(b => { if (b.classList.contains('sg-cat')) b.classList.toggle('--on', b.dataset.cat === cat); });
        sync();
    }
    function sync() {
        let c = 0, est = 0;
        rows.forEach(r => { const cb = r.querySelector('input'); r.classList.toggle('--sel', cb.checked); if (cb.checked) { c++; est += +r.dataset.cost || 0; } });
        page.querySelector('[data-sel]').textContent = c;
        page.querySelector('[data-est]').textContent = '₹' + Math.round(est).toLocaleString('en-IN');
        page.querySelector('[data-go]').disabled = c === 0;
        if (all) { const vis = rows.filter(r => !r.hidden && !r.querySelector('input').disabled); all.checked = vis.length > 0 && vis.every(r => r.querySelector('input').checked); }
    }
    page.querySelectorAll('[data-f]').forEach(b => b.addEventListener('click', () => { f = b.dataset.f; apply(); }));
    page.querySelectorAll('.sg-cat').forEach(b => b.addEventListener('click', () => { cat = b.dataset.cat; apply(); }));
    q?.addEventListener('input', () => { apply(); smartSearch(q.value.trim()); });
    all?.addEventListener('change', () => { rows.forEach(r => { const cb = r.querySelector('input'); if (!r.hidden && !cb.disabled) cb.checked = all.checked; }); sync(); });
    page.addEventListener('change', e => { if (e.target.name === 'pick[]') sync(); });
    apply();
})();
</script>
@endpush
</x-admin-layout>
