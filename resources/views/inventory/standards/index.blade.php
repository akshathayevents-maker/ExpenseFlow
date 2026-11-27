<x-admin-layout title="Recipe Standards">
@php
    $ready = \App\Services\Inventory\RecipeStandardService::READY_AFTER;
    $review = $preparations->filter(fn ($p) => $p['state'] === 'standard' && ($p['drift_pct'] ?? 0) >= 20);
    $counts = [
        'all' => $preparations->count(),
        'ready' => $preparations->where('state', 'ready')->count(),
        'review' => $review->count(),
        'standard' => $preparations->where('state', 'standard')->count(),
        'collecting' => $preparations->where('state', 'collecting')->count(),
    ];
    $tiles = [
        'all' => ['All dishes', 'bi-journal-text'],
        'ready' => ['Ready', 'bi-stars'],
        'review' => ['Drifted', 'bi-arrow-left-right'],
        'standard' => ['Standard set', 'bi-patch-check'],
        'collecting' => ['Collecting', 'bi-hourglass-split'],
    ];
    $avgWaste = $preparations->count() ? (int) round($preparations->avg('waste_pct')) : 0;
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

@push('styles')
<style>
/* Recipe standards — same language as the other list pages (rs-*). Filtering is instant (JS). */
.rs{display:flex;flex-direction:column;gap:14px;max-width:1500px}
.rs-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.rs-head h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.rs-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted);max-width:760px}
.rs-head p b{color:var(--ef-ink)}
.rs-acts{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.rs-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap}
.rs-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.rs-btn .bi{color:var(--ef-gold)}
.rs-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.rs-btn.--main .bi{color:#f0c97a}
.rs-btn.--sm{min-height:34px;padding:4px 12px;font-size:.8rem}
@media (max-width:639.98px){.rs-head h1{font-size:1.25rem}.rs-head p .--long{display:none}.rs-acts{width:100%}.rs-acts .rs-btn{flex:1}}

.rs-q{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden}
.rs-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;min-width:0;border:0;text-align:left;cursor:pointer;font:inherit;background:linear-gradient(var(--tint,transparent),var(--tint,transparent)),var(--ef-surface)}
.rs-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rs-qi small .bi{color:var(--acc)}
.rs-qi strong{font-size:1.35rem;font-weight:800;line-height:1.15;color:var(--acc);font-variant-numeric:tabular-nums}
.rs-qi.--zero strong{color:var(--ef-faint)}
.rs-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.rs-qi[data-k=all]{--acc:var(--ef-ink)}
.rs-qi[data-k=ready]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.06)}
.rs-qi[data-k=review]{--acc:#b4531c;--tint:rgba(214,110,40,.07)}
.rs-qi[data-k=standard]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.rs-qi[data-k=collecting]{--acc:#7a6e62;--tint:rgba(122,110,98,.05)}
@media (max-width:1199.98px){.rs-q{grid-template-columns:repeat(3,minmax(0,1fr))}.rs-qi{padding:8px 10px}.rs-qi strong{font-size:1.15rem}.rs-qi[data-k=all]{grid-column:1/-1}}
@media (max-width:639.98px){.rs-q{grid-template-columns:repeat(2,minmax(0,1fr))}}

.rs-body{display:grid;gap:14px}
@media (min-width:1500px){.rs-body{grid-template-columns:minmax(0,1fr) 320px;align-items:start}}
.rs-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}
.rs-bar{display:flex;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.rs-search{position:relative;flex:1;min-width:0;margin:0}
.rs-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.rs-search input{width:100%;min-height:42px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);font-size:16px;background:var(--ef-surface);color:var(--ef-ink)}
.rs-count{font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.rs-th,.rs-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:11px 14px}
.rs-th{display:none}
.rs-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent);text-decoration:none;color:var(--ef-ink)}
.rs-row:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.rs-row[hidden]{display:none}
.rs-row[data-state=ready]{--tone:var(--ef-emerald)}
.rs-row[data-state=standard]{--tone:#3b6ea5}
.rs-row[data-review="1"]{--tone:#d66e28}
.rs-t{display:block;font-weight:750;font-size:.94rem}
.rs-s{display:block;font-size:.75rem;color:var(--ef-muted)}
.rs-c{font-size:.84rem;white-space:nowrap}
.rs-c small{display:block;font-size:.7rem;color:var(--ef-muted)}
.rs-c .--bad{color:var(--ef-danger);font-weight:700}
.rs-c .--warn{color:#9a6413;font-weight:700}
.rs-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:8px;font-size:.7rem;font-weight:800;white-space:nowrap}
.rs-chip.--standard{background:rgba(59,110,165,.12);color:#2c5784}
.rs-chip.--ready{background:rgba(30,122,60,.12);color:var(--ef-emerald-dk)}
.rs-chip.--collecting{background:var(--ef-bg-subtle);color:var(--ef-muted)}
.rs-chip.--review{background:rgba(214,110,40,.12);color:#a3480f}
.rs-meter{display:block;height:5px;border-radius:4px;background:var(--ef-bg-subtle);overflow:hidden;margin-top:3px;max-width:90px}
.rs-meter i{display:block;height:100%;background:#9aa39a}
.rs-cell-m{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.76rem;color:var(--ef-muted)}
.rs-c-uses,.rs-c-waste,.rs-c-cost,.rs-c-st{display:none}
@media (min-width:1200px){
  .rs-th,.rs-row{grid-template-columns:minmax(180px,1fr) 110px 100px 110px 170px 120px;gap:12px}
  .rs-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .rs-c-uses,.rs-c-waste,.rs-c-cost,.rs-c-st{display:block}
  .rs-cell-m{display:none}
  .rs-go{text-align:right}
}
.rs-empty{padding:30px 16px;text-align:center;color:var(--ef-muted);font-size:.88rem}
.rs-empty .bi{display:block;font-size:1.8rem;color:var(--ef-faint);margin-bottom:6px}
.rs-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:700px) and (max-width:1499.98px){.rs-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.rs-ph{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.rs-ph h2{margin:0;font-size:.92rem;font-weight:800;display:flex;gap:8px;align-items:center}
.rs-ph h2 .bi{color:var(--acc)}
.rs-pb{padding:8px 14px 12px;font-size:.82rem;color:var(--ef-muted)}
.rs-flow{list-style:none;margin:0;padding:0;display:grid;gap:8px}
.rs-flow li{display:flex;gap:8px}
.rs-flow b{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.7rem;display:flex;align-items:center;justify-content:center}
.rs-ver{display:flex;justify-content:space-between;gap:8px;padding:7px 0;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none}
.rs-ver:first-child{border-top:0}
.rs-ver span{color:var(--ef-muted);font-size:.76rem;white-space:nowrap}
</style>
@endpush

<div class="rs" id="rsPage">
    <header class="rs-head">
        <div style="flex:1 1 320px;min-width:0">
            <h1>Recipe standards</h1>
            <p>Per-person quantities learned from what the kitchen <b>actually used</b>.<span class="--long"> Staff always see and can change suggested quantities; saving creates a new version and earlier requests keep theirs.</span></p>
        </div>
        <div class="rs-acts">
            <a class="rs-btn" href="{{ route('inventory.reports.index') }}"><i class="bi bi-bar-chart"></i> Reports</a>
            <a class="rs-btn --main" href="{{ route('inventory.material-requests.index') }}"><i class="bi bi-basket"></i> Material requests</a>
        </div>
    </header>

    <nav class="rs-q" aria-label="Filter dishes">
        @foreach($tiles as $k => [$label, $icon])
            <button type="button" class="rs-qi {{ $k === 'all' ? '--on' : '' }} {{ $counts[$k] ? '' : '--zero' }}" data-k="{{ $k }}" data-filter="{{ $k }}">
                <small title="{{ ['ready' => 'Ready to standardise', 'review' => 'Standard drifted from recent usage', 'standard' => 'Has a standard', 'collecting' => 'Collecting data', 'all' => 'All dishes'][$k] }}"><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $counts[$k] }}</strong>
            </button>
        @endforeach
    </nav>

    <div class="rs-body">
        <section class="rs-panel" aria-label="Dishes with recorded usage">
            <div class="rs-bar">
                <label class="rs-search"><i class="bi bi-search"></i><input type="search" placeholder="Search dish" aria-label="Search dish" data-q autocomplete="off"></label>
                <span class="rs-count"><b data-count>{{ $counts['all'] }}</b> dishes</span>
            </div>
            @if($preparations->isNotEmpty())
                <div class="rs-th" aria-hidden="true"><span>Dish</span><span>Recorded</span><span>Waste</span><span>Cost / person</span><span>Standard</span><span></span></div>
            @endif
            @forelse($preparations as $p)
                @php
                    $rev = $p['state'] === 'standard' && ($p['drift_pct'] ?? 0) >= 20;
                    $chip = match (true) {
                        $rev => ['review', 'v'.$p['standard']->version.' · off by '.$p['drift_pct'].'%'],
                        $p['state'] === 'standard' => ['standard', 'Standard v'.$p['standard']->version],
                        $p['state'] === 'ready' => ['ready', 'Ready — '.$p['times'].' uses'],
                        default => ['collecting', $p['times'].' of '.$ready.' uses'],
                    };
                    $go = $p['state'] === 'ready' ? 'Create standard' : ($rev ? 'Review' : 'Open');
                @endphp
                <a class="rs-row" href="{{ route('inventory.standards.show', ['name' => $p['name']]) }}" data-row data-state="{{ $p['state'] }}" data-review="{{ $rev ? 1 : 0 }}" data-s="{{ mb_strtolower($p['name']) }}">
                    <span style="min-width:0">
                        <span class="rs-t">{{ $p['name'] }}</span>
                        <span class="rs-s">about {{ number_format($p['avg_guests']) }} people · last {{ optional($p['last_used'])->format('d M Y') ?? '—' }}</span>
                    </span>
                    <span class="rs-c rs-c-uses">{{ $p['times'] }} {{ Str::plural('time', $p['times']) }}<small>avg {{ number_format($p['avg_guests']) }} people</small></span>
                    <span class="rs-c rs-c-waste"><span class="{{ $p['waste_pct'] >= 15 ? '--bad' : ($p['waste_pct'] >= 8 ? '--warn' : '') }}">{{ $p['waste_pct'] }}%</span><span class="rs-meter"><i style="width:{{ min(100, $p['waste_pct'] * 3) }}%;{{ $p['waste_pct'] >= 15 ? 'background:var(--ef-danger)' : ($p['waste_pct'] >= 8 ? 'background:#d89a3d' : '') }}"></i></span></span>
                    <span class="rs-c rs-c-cost">{{ $p['cost_per_guest'] > 0 ? '₹'.$q($p['cost_per_guest']) : '—' }}<small>at today's prices</small></span>
                    <span class="rs-c-st"><span class="rs-chip --{{ $chip[0] }}">{{ $chip[1] }}</span></span>
                    <span class="rs-go"><span class="rs-btn --sm">{{ $go }}</span></span>
                    <span class="rs-cell-m">
                        <span class="rs-chip --{{ $chip[0] }}">{{ $chip[1] }}</span>
                        <span>{{ $p['times'] }} {{ Str::plural('time', $p['times']) }}</span>
                        <span class="{{ $p['waste_pct'] >= 15 ? 'text-danger' : '' }}">{{ $p['waste_pct'] }}% waste</span>
                        @if($p['cost_per_guest'] > 0)<span>₹{{ $q($p['cost_per_guest']) }}/person</span>@endif
                    </span>
                </a>
            @empty
                <div class="rs-empty"><i class="bi bi-journal-x"></i>No usage recorded yet. Standards appear after the kitchen records usage on material requests that have a dish name and number of people.</div>
            @endforelse
            <div class="rs-empty" data-none hidden><i class="bi bi-search"></i>No dishes match.</div>
        </section>

        <aside class="rs-rail">
            <section class="rs-panel" style="--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)">
                <div class="rs-ph"><h2><i class="bi bi-lightbulb"></i> Insights</h2></div>
                <div class="rs-pb">
                    <p style="margin:0 0 6px"><b style="color:var(--ef-ink)">{{ $counts['ready'] }}</b> {{ Str::plural('dish', $counts['ready']) }} recorded {{ $ready }}+ times without a standard — create one so requests get suggested quantities.</p>
                    @if($counts['review'])<p style="margin:0 0 6px"><b style="color:#a3480f">{{ $counts['review'] }}</b> standard(s) differ by 20%+ from recent usage — review them.</p>@endif
                    <p style="margin:0">Average waste across dishes: <b style="color:var(--ef-ink)">{{ $avgWaste }}%</b> (wasted + unexplained, of what went out).</p>
                </div>
            </section>
            <section class="rs-panel" style="--acc:#2c5784;--tint:rgba(59,110,165,.05)">
                <div class="rs-ph"><h2><i class="bi bi-signpost-split"></i> How it works</h2></div>
                <div class="rs-pb">
                    <ol class="rs-flow">
                        <li><b>1</b><span>Kitchen records what was used for a dish and how many people.</span></li>
                        <li><b>2</b><span>After {{ $ready }} records, the median per person becomes a reliable starting point.</span></li>
                        <li><b>3</b><span>Save it as a standard (a new version each time).</span></li>
                        <li><b>4</b><span>New material requests get suggested quantities; staff can still change them.</span></li>
                    </ol>
                </div>
            </section>
            @if($versions->isNotEmpty())
                <section class="rs-panel" style="--acc:var(--ef-gold);--tint:rgba(200,144,42,.05)">
                    <div class="rs-ph"><h2><i class="bi bi-clock-history"></i> Version history</h2></div>
                    <div class="rs-pb">
                        @foreach($versions->take(12) as $v)
                            <a class="rs-ver" href="{{ route('inventory.standards.show', ['name' => $v->name]) }}"><b>{{ $v->name }} v{{ $v->version }}</b><span>{{ $v->superseded_at ? 'replaced '.$v->superseded_at->format('d M') : 'current · '.$v->created_at->format('d M Y') }}</span></a>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const page = document.getElementById('rsPage'); if (!page) return;
    const rows = [...page.querySelectorAll('[data-row]')], q = page.querySelector('[data-q]');
    let f = 'all';
    const ok = r => f === 'all' || (f === 'review' ? r.dataset.review === '1' : r.dataset.state === f);
    function apply() {
        const t = q.value.trim().toLowerCase(); let n = 0;
        rows.forEach(r => { const v = ok(r) && (!t || r.dataset.s.includes(t)); r.hidden = !v; if (v) n++; });
        page.querySelector('[data-count]').textContent = n;
        page.querySelector('[data-none]').hidden = n > 0 || !rows.length;
        page.querySelectorAll('[data-filter]').forEach(b => b.classList.toggle('--on', b.dataset.filter === f));
    }
    page.querySelectorAll('[data-filter]').forEach(b => b.addEventListener('click', () => { f = b.dataset.filter; apply(); }));
    q.addEventListener('input', apply);
})();
</script>
@endpush
</x-admin-layout>
