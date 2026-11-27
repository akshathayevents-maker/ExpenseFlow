<x-admin-layout title="Dishes">
@php
    $q     = $filters['search'];
    $cat   = $filters['category'];
    $base  = array_filter(['search' => $q ?: null, 'category' => $cat]);
    $tiles = [
        'all'               => ['All dishes', 'bi-journal-text', [], $stats['total']],
        'recipe_none'       => ['No recipe', 'bi-journal-x', ['recipe' => 'none'], $stats['no_recipe']],
        'recipe_unverified' => ['Recipe draft', 'bi-hourglass-split', ['recipe' => 'unverified'], $stats['unverified']],
        'recipe_verified'   => ['Recipe verified', 'bi-patch-check', ['recipe' => 'verified'], $stats['verified']],
        'tamil'             => ['Tamil needed', 'bi-translate', ['tamil' => 'missing'], $stats['tamil_missing']],
        'inactive'          => ['Hidden', 'bi-eye-slash', ['state' => 'inactive'], $stats['inactive']],
    ];
    $quickQuery = $tiles[$quick][2] ?? [];
    $catTotal = $catCounts->sum();
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

@push('styles')
<style>
/* Dishes — same language as Purchases / Meal plans / Event requests. */
.dl{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.dl a:focus-visible,.dl button:focus-visible{outline:2px solid var(--ef-emerald);outline-offset:2px}
.dl-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.dl-title{flex:1 1 300px;min-width:0}
.dl-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.dl-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.dl-acts{display:flex;gap:8px;flex-wrap:wrap}
.dl-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.dl-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.dl-btn .bi{color:var(--ef-gold)}
.dl-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.dl-btn.--main .bi{color:#f0c97a}
.dl-btn.--sm{min-height:34px;padding:4px 11px;font-size:.8rem}
.dl-btn.--icon{width:34px;min-height:34px;padding:0}
.dl-btn.--del{color:var(--ef-danger)}
.dl-btn.--del .bi{color:var(--ef-danger)}
.dl-btn.--del:hover{border-color:var(--ef-danger);background:rgba(192,57,43,.06)}
@media (max-width:639.98px){.dl-acts{width:100%;display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.dl-acts .dl-btn{padding:8px 8px;font-size:.8rem}.dl-title h1{font-size:1.25rem}.dl-title p .--long{display:none}}

.dl-q{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.dl-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);min-width:0;background:var(--tint,transparent)}
.dl-qi:last-child{border-right:0}
.dl-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.dl-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dl-qi small .bi{color:var(--acc)}
.dl-qi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.dl-qi.--zero strong{color:var(--ef-faint)}
.dl-qi.--zero{--tint:transparent}
.dl-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.dl-qi[data-k=all]{--acc:var(--ef-ink)}
.dl-qi[data-k=recipe_none]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.04)}
.dl-qi[data-k=recipe_unverified]{--acc:#9a6413;--tint:rgba(200,144,42,.08)}
.dl-qi[data-k=recipe_verified]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.dl-qi[data-k=tamil]{--acc:#6b5bb5;--tint:rgba(107,91,181,.05)}
.dl-qi[data-k=inactive]{--acc:#5f6b5a;--tint:rgba(95,107,90,.05)}
@media (max-width:1199.98px){.dl-q{display:flex;overflow-x:auto;scrollbar-width:none}.dl-q::-webkit-scrollbar{display:none}.dl-qi{flex:0 0 auto;min-width:112px;padding:8px 12px}.dl-qi strong{font-size:1.1rem}}

.dl-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0;overflow:hidden;border-top:3px solid var(--ef-gold)}
.dl-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.dl-search{position:relative;flex:1 1 240px;min-width:0;margin:0}
.dl-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.dl-search input{width:100%;min-height:40px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink)}
.dl-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.dl-count b{color:var(--ef-ink)}
.dl-cats{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;padding:8px 12px;border-bottom:1px solid var(--ef-border);background:var(--ef-bg-subtle)}
.dl-cats::-webkit-scrollbar{display:none}
@media (min-width:1100px){.dl-cats{flex-wrap:wrap;overflow:visible}}
.dl-cat{flex:0 0 auto;display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:0 12px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink-2);font-size:.8rem;font-weight:700;text-decoration:none;white-space:nowrap}
.dl-cat span{font-size:.7rem;color:var(--ef-muted);font-variant-numeric:tabular-nums}
.dl-cat:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.dl-cat.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.dl-cat.--on span{color:#f0c97a}

.dl-th,.dl-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:11px 14px}
.dl-th{display:none}
.dl-row{border-top:1px solid var(--ef-border)}
.dl-row:first-child{border-top:0}
.dl-row:hover{background:var(--ef-bg-subtle)}
.dl-row.--off .dl-en{color:var(--ef-muted)}
.dl-dish{min-width:0}
.dl-en{display:block;font-weight:750;font-size:.94rem;color:var(--ef-ink);overflow-wrap:anywhere}
.dl-ta{display:block;font-size:.8rem;color:var(--ef-muted);overflow-wrap:anywhere}
.dl-flag{display:inline-flex;align-items:center;gap:4px;padding:1px 7px;border-radius:8px;font-size:.68rem;font-weight:800;white-space:nowrap;background:var(--bg);color:var(--fg)}
.dl-flag.--tamil{--bg:rgba(107,91,181,.1);--fg:#5a4aa3}
.dl-flag.--ok{--bg:rgba(30,122,60,.1);--fg:var(--ef-emerald-dk)}
.dl-flag.--draft{--bg:rgba(200,144,42,.14);--fg:#8a5a10}
.dl-flag.--none{--bg:rgba(192,57,43,.08);--fg:var(--ef-danger)}
.dl-flag.--muted{--bg:var(--ef-bg-subtle);--fg:var(--ef-muted)}
.dl-flag.--cat{--bg:rgba(200,144,42,.1);--fg:#7d5218}
a.dl-flag{text-decoration:none}
a.dl-flag:hover{filter:brightness(.95)}
.dl-c{font-size:.82rem;white-space:nowrap;min-width:0}
.dl-c small{display:block;font-size:.7rem;color:var(--ef-muted)}
.dl-c.--faint{color:var(--ef-faint)}
.dl-price b{font-variant-numeric:tabular-nums}
.dl-off{color:#9a6413;font-weight:700}
.dl-c-cat,.dl-c-price,.dl-c-recipe,.dl-c-use,.dl-c-state{display:none}
.dl-toggle{display:inline-flex;align-items:center;gap:6px;padding:0;border:0;background:none;font-size:.8rem;font-weight:700;color:var(--ef-ink-2);cursor:pointer;min-height:34px}
.dl-sw{position:relative;width:32px;height:18px;border-radius:9px;background:var(--ef-border-strong);transition:background .15s}
.dl-sw::after{content:"";position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.2);transition:left .15s}
.dl-toggle.--on .dl-sw{background:var(--ef-emerald)}
.dl-toggle.--on .dl-sw::after{left:16px}
.dl-do{display:flex;gap:6px;align-items:center;justify-content:flex-end}
.dl-do form{margin:0}
.dl-mmeta{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:6px 8px;align-items:center;font-size:.76rem;color:var(--ef-muted)}
.dl-mfoot{grid-column:1/-1;display:flex;align-items:center;gap:8px;justify-content:space-between}
@media (min-width:1100px){
  .dl-th,.dl-row{grid-template-columns:minmax(220px,1.4fr) 130px 130px 120px 110px 110px 120px;gap:14px}
  .dl-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .dl-c-cat,.dl-c-price,.dl-c-recipe,.dl-c-use,.dl-c-state{display:block}
  .dl-mmeta,.dl-mfoot{display:none}
}
@media (max-width:1099.98px){.dl-row>.dl-do{display:none}}
.dl-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.dl-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.dl-empty a{font-weight:700}
.dl-foot{padding:10px 14px;border-top:1px solid var(--ef-border)}
.dl-foot nav{overflow-x:auto;max-width:100%}
.dl-note{font-size:.8rem;color:var(--ef-muted);margin:0}
.dl-note b{color:var(--ef-ink-2)}
@media (max-width:639.98px){.dl-count,.dl-sbtn{display:none}}
</style>
@endpush

<div class="dl">
    <header class="dl-head">
        <div class="dl-title">
            <h1>Dishes</h1>
            <p>One list of dishes for the whole app.<span class="--long"> The same dishes appear on the <b>Customer Menu &amp; Prices</b> and in the <b>Menu Composer</b>; names and categories stay in sync.</span></p>
        </div>
        <div class="dl-acts">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.event-request-menu.items.index') }}" class="dl-btn" title="Same dishes, with prices"><i class="bi bi-tags"></i> Event Request Menu</a>
            @endif
            <a href="{{ route('menu.items.create') }}" class="dl-btn --main"><i class="bi bi-plus-lg"></i> Add dish</a>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <nav class="dl-q" aria-label="Dish checks">
        @foreach($tiles as $k => [$label, $icon, $qq, $n])
            <a href="{{ route('menu.items.index', $base + $qq) }}" data-k="{{ $k }}" class="dl-qi {{ $quick === $k ? '--on' : '' }} {{ $n ? '' : '--zero' }}" @if($quick === $k) aria-current="page" @endif>
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $n }}</strong>
            </a>
        @endforeach
    </nav>

    <section class="dl-panel" aria-label="Dishes">
        <form method="GET" action="{{ route('menu.items.index') }}" class="dl-bar" role="search" id="dlSearch">
            @foreach($quickQuery + array_filter(['category' => $cat]) as $hk => $hv)<input type="hidden" name="{{ $hk }}" value="{{ $hv }}">@endforeach
            <label class="dl-search"><i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $q }}" placeholder="Search dish (English or Tamil)" aria-label="Search dishes" autocomplete="off" @if($q) autofocus @endif>
            </label>
            <button class="dl-btn dl-sbtn" type="submit">Search</button>
            @if($q)<a class="dl-btn" href="{{ route('menu.items.index', $quickQuery + array_filter(['category' => $cat])) }}">Clear</a>@endif
            <span class="dl-count"><b>{{ $items->total() }}</b> {{ Str::plural('dish', $items->total()) }} shown · {{ $stats['no_recipe'] }} without recipe · {{ $stats['used'] }} used in requests / plans</span>
        </form>

        <div class="dl-cats" role="group" aria-label="Category">
            <a href="{{ route('menu.items.index', $quickQuery + array_filter(['search' => $q ?: null])) }}" class="dl-cat {{ $cat ? '' : '--on' }}">All <span>{{ $catTotal }}</span></a>
            @foreach($categories as $key => $c)
                @php $n = (int) ($catCounts[$key] ?? 0); @endphp
                @if($n || $cat === $key)
                    <a href="{{ route('menu.items.index', $quickQuery + array_filter(['search' => $q ?: null, 'category' => $key])) }}" class="dl-cat {{ $cat === $key ? '--on' : '' }}" @if($cat === $key) aria-current="true" @endif>{{ $c['en'] }} <span>{{ $n }}</span></a>
                @endif
            @endforeach
        </div>

        @if($items->count())
            <div class="dl-th" aria-hidden="true">
                <span>Dish</span><span>Category</span><span>Customer menu</span><span>Recipe</span><span>Used in</span><span>Composer</span><span></span>
            </div>
        @endif
        <div>
        @forelse($items as $item)
            @php
                $er = $item->eventRequestItem;
                $rc = $item->recipe;
                $noTa = $item->item_ta === $item->item_en;
                $use = collect([
                    $item->used_requests ? $item->used_requests.' req' : null,
                    $item->used_plans ? $item->used_plans.' '.Str::plural('plan', $item->used_plans) : null,
                ])->filter()->implode(' · ');
                $recipeHtml = $rc
                    ? '<a class="dl-flag '.($rc->isVerified() ? '--ok' : '--draft').'" href="'.e(route('kitchen.recipes.show', $rc)).'">'.($rc->isVerified() ? '✓ Verified' : 'Draft').'</a>'
                    : '<a class="dl-flag --none" href="'.e(route('kitchen.recipes.create', ['name' => $item->item_en])).'"><i class="bi bi-plus"></i>Add recipe</a>';
            @endphp
            <article class="dl-row {{ $item->is_active ? '' : '--off' }}" data-dish="{{ $item->id }}">
                <div class="dl-dish">
                    <span class="dl-en">{{ $item->item_en }}</span>
                    @if($noTa)<span class="dl-ta"><span class="dl-flag --tamil"><i class="bi bi-translate"></i> Tamil needed</span></span>
                    @else<span class="dl-ta">{{ $item->item_ta }}</span>@endif
                </div>
                <span class="dl-c dl-c-cat"><span class="dl-flag --cat">{{ $item->category_en }}</span></span>
                <span class="dl-c dl-c-price {{ $er ? '' : '--faint' }}">
                    @if($er)<b>₹{{ $fmt($er->price_per_person) }}</b><small class="{{ $er->is_active ? '' : 'dl-off' }}">{{ $er->is_active ? 'offered' : 'not offered' }}</small>@else—@endif
                </span>
                <span class="dl-c dl-c-recipe">{!! $recipeHtml !!}</span>
                <span class="dl-c dl-c-use {{ $use ? '' : '--faint' }}">{{ $use ?: '—' }}</span>
                <span class="dl-c-state">
                    <form method="POST" action="{{ route('menu.items.toggle', $item) }}">@csrf @method('PATCH')
                        <button type="submit" class="dl-toggle {{ $item->is_active ? '--on' : '' }}" aria-pressed="{{ $item->is_active ? 'true' : 'false' }}" title="{{ $item->is_active ? 'Hide from Menu Composer' : 'Show in Menu Composer' }}"><span class="dl-sw"></span>{{ $item->is_active ? 'Active' : 'Hidden' }}</button>
                    </form>
                </span>
                <div class="dl-do">
                    <a href="{{ route('menu.items.edit', $item) }}" class="dl-btn --sm" aria-label="Edit {{ $item->item_en }}"><i class="bi bi-pencil"></i> Edit</a>
                    <form method="POST" action="{{ route('menu.items.destroy', $item) }}" onsubmit="return confirm('Delete {{ addslashes($item->item_en) }}?')">@csrf @method('DELETE')
                        <button type="submit" class="dl-btn --icon --del" aria-label="Delete {{ $item->item_en }}"><i class="bi bi-trash3"></i></button>
                    </form>
                </div>
                {{-- phones / tablets --}}
                <div class="dl-mmeta">
                    <span class="dl-flag --cat">{{ $item->category_en }}</span>
                    {!! $recipeHtml !!}
                    @if($er)<span><b style="color:var(--ef-ink)">₹{{ $fmt($er->price_per_person) }}</b> <span class="{{ $er->is_active ? '' : 'dl-off' }}">{{ $er->is_active ? 'offered' : 'not offered' }}</span></span>@endif
                    @if($use)<span>{{ $use }}</span>@endif
                </div>
                <div class="dl-mfoot">
                    <form method="POST" action="{{ route('menu.items.toggle', $item) }}">@csrf @method('PATCH')
                        <button type="submit" class="dl-toggle {{ $item->is_active ? '--on' : '' }}" aria-pressed="{{ $item->is_active ? 'true' : 'false' }}"><span class="dl-sw"></span>{{ $item->is_active ? 'Active' : 'Hidden' }}</button>
                    </form>
                    <div class="dl-do">
                        <a href="{{ route('menu.items.edit', $item) }}" class="dl-btn --sm" aria-label="Edit {{ $item->item_en }}"><i class="bi bi-pencil"></i> Edit</a>
                        <form method="POST" action="{{ route('menu.items.destroy', $item) }}" onsubmit="return confirm('Delete {{ addslashes($item->item_en) }}?')">@csrf @method('DELETE')
                            <button type="submit" class="dl-btn --icon --del" aria-label="Delete {{ $item->item_en }}"><i class="bi bi-trash3"></i></button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="dl-empty">
                @if($q || $cat || $quick !== 'all')
                    <i class="bi bi-search"></i>No dishes match. <a href="{{ route('menu.items.index') }}">Show all dishes</a>
                @else
                    <i class="bi bi-journal-plus"></i>No dishes yet. <a href="{{ route('menu.items.create') }}">Add the first dish</a>
                @endif
            </div>
        @endforelse
        </div>
        @if($items->hasPages())<div class="dl-foot">{{ $items->links() }}</div>@endif
    </section>

    <p class="dl-note"><b>Active</b> dishes can be picked in the Menu Composer; <b>Hidden</b> ones stay on old menus. <b>Customer menu</b> price comes from Customer Menu &amp; Prices.</p>
</div>

@push('scripts')
<script>
// Search as you type (debounced) — keeps the cursor at the end after reload.
(function () {
    var f = document.getElementById('dlSearch'); if (!f) return;
    var i = f.querySelector('input[name=search]'), t;
    if (i.value) { var v = i.value; i.value = ''; i.value = v; }
    i.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { f.submit(); }, 500); });
})();
</script>
@endpush
</x-admin-layout>
