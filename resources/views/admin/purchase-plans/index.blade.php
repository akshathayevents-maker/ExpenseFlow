<x-admin-layout title="Purchase Plans">
@include('inventory._styles')
@include('admin.purchase-plans._tone')

@push('styles')
<style>
/* Purchase plans workspace — status strip, toolbar, table (cards on phones), "needs buying" rail. Same language as Purchases. */
.pp{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.pp-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.pp-title{flex:1 1 260px;min-width:0}
.pp-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.pp-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.pp-title p a{font-weight:700}
.pp-acts{display:flex;gap:8px;flex-wrap:wrap}
.pp-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.pp-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.pp-btn .bi{color:var(--ef-gold)}
.pp-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.pp-btn.--main .bi{color:#f0c97a}
.pp-btn b{min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:var(--ef-danger);color:#fff;font-size:.7rem;display:inline-flex;align-items:center;justify-content:center}
@media (max-width:639.98px){.pp-acts{width:100%;display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.pp-title h1{font-size:1.25rem}.pp-title p .--long{display:none}}

/* status strip */
.pp-q{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.pp-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);min-width:0;background:var(--tint,transparent)}
.pp-qi:last-child{border-right:0}
.pp-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.pp-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pp-qi small .bi{color:var(--acc)}
.pp-qi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.pp-qi.--zero strong{color:var(--ef-faint)}
.pp-qi.--zero{--tint:transparent}
.pp-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.pp-qi[data-k=all]{--acc:var(--ef-ink)}
.pp-qi[data-k=draft]{--acc:#6b5bb5;--tint:rgba(107,91,181,.05)}
.pp-qi[data-k=approved]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.pp-qi[data-k=ordered]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.pp-qi[data-k=completed]{--acc:#5f6b5a;--tint:rgba(95,107,90,.05)}
.pp-qi[data-k=cancelled]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.04)}
@media (max-width:899.98px){
  .pp-q{display:flex;overflow-x:auto;scrollbar-width:none}
  .pp-q::-webkit-scrollbar{display:none}
  .pp-qi{flex:0 0 auto;min-width:104px;padding:8px 12px}
  .pp-qi strong{font-size:1.1rem}
}

/* body */
.pp-body{display:grid;gap:14px}
@media (min-width:1600px){.pp-body{grid-template-columns:minmax(0,1fr) 320px;align-items:start}}
.pp-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}

.pp-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.pp-search{position:relative;flex:1 1 220px;min-width:0}
.pp-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.pp-search input{width:100%;min-height:40px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink)}
.pp-more>summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;min-height:40px;padding:0 12px;border:1px solid var(--ef-border);border-radius:10px;font-weight:700;font-size:.84rem;color:var(--ef-ink);background:var(--ef-surface)}
.pp-more>summary::-webkit-details-marker{display:none}
.pp-more[open]{flex-basis:100%;order:3}
.pp-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding-top:10px}
@media (min-width:720px){.pp-fields{grid-template-columns:repeat(2,minmax(0,200px)) auto;align-items:end}}
.pp-fields label{display:flex;flex-direction:column;gap:3px;font-size:.72rem;font-weight:700;color:var(--ef-muted);min-width:0}
.pp-fields input{min-height:40px;padding:6px 10px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink);width:100%;min-width:0}
.pp-go{display:flex;gap:6px;grid-column:1/-1}
@media (min-width:720px){.pp-go{grid-column:auto}}
.pp-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.pp-count b{color:var(--ef-ink)}

.pp-th,.pp-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:11px 14px}
.pp-th{display:none}
.pp-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent)}
.pp-row:first-child{border-top:0}
.pp-row:hover{background:var(--ef-bg-subtle)}
.pp-row[data-tone=ok]{--tone:var(--ef-emerald)}
.pp-row[data-tone=info]{--tone:#3b6ea5}
.pp-row[data-tone=bad]{--tone:var(--ef-danger)}
.pp-row[data-tone=neutral]{--tone:#6b5bb5}
.pp-what{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;color:var(--ef-ink)}
.pp-ico{flex:0 0 auto;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(200,144,42,.12);color:#9a6413}
.pp-what-b{min-width:0}
.pp-t{display:block;font-weight:750;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pp-what:hover .pp-t{text-decoration:underline}
.pp-s{display:block;font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pp-date{font-size:.84rem;white-space:nowrap;line-height:1.2}
.pp-date small{display:block;font-size:.7rem;color:var(--ef-muted)}
.pp-date.--late{color:var(--ef-danger);font-weight:700}
.pp-items{font-size:.84rem;font-variant-numeric:tabular-nums;white-space:nowrap}
.pp-urg{display:inline-block;margin-left:4px;padding:1px 7px;border-radius:8px;font-size:.66rem;font-weight:800;background:rgba(192,57,43,.1);color:var(--ef-danger)}
.pp-amt{text-align:right;font-weight:800;font-variant-numeric:tabular-nums;font-size:.94rem;white-space:nowrap}
.pp-amt.--none{color:var(--ef-faint);font-weight:600}
.pp-c-date,.pp-c-items,.pp-c-by{display:none}
.pp-st{display:flex}
.pp-st .ic-chip{font-size:.68rem}
.pp-mmeta{grid-column:1/-1;padding-left:46px;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.76rem;color:var(--ef-muted);align-items:center}
.pp-mmeta .--late{color:var(--ef-danger);font-weight:700}
@media (max-width:1099.98px){.pp-what{grid-column:1;grid-row:1}.pp-amt{grid-column:2;grid-row:1}.pp-st{grid-column:1/-1;padding-left:46px}}
@media (max-width:399.98px){.pp-st,.pp-mmeta{padding-left:0}}
@media (min-width:1100px){
  .pp-th,.pp-row{grid-template-columns:minmax(220px,1fr) 120px 110px 140px 120px 110px;gap:14px}
  .pp-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .pp-th .--r{text-align:right}
  .pp-c-date,.pp-c-items,.pp-c-by{display:block}
  .pp-c-by{font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .pp-mmeta{display:none}
  .pp-amt{order:9}
}
.pp-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.pp-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.pp-empty a{font-weight:700}
.pp-foot{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:8px;padding:10px 14px;border-top:1px solid var(--ef-border);font-size:.8rem;color:var(--ef-muted)}
.pp-foot b{color:var(--ef-ink)}
.pp-foot nav{overflow-x:auto;max-width:100%}

/* rail */
.pp-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:640px) and (max-width:1599.98px){.pp-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.pp-card-h{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.pp-card-h h2{margin:0;font-size:.92rem;font-weight:800;color:var(--ef-ink);display:flex;align-items:center;gap:8px}
.pp-card-h h2 .bi{color:var(--acc)}
.pp-card-h a{margin-left:auto;font-size:.78rem;font-weight:700;color:var(--ef-gold);text-decoration:none}
.pp-card-b{padding:6px 14px 12px}
.pp-low{display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid var(--ef-border)}
.pp-low:first-child{border-top:0}
.pp-low-b{flex:1;min-width:0}
.pp-low-b b{display:block;font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pp-low-b i{display:block;height:4px;border-radius:4px;background:rgba(100,82,42,.1);margin-top:4px;overflow:hidden}
.pp-low-b i span{display:block;height:100%;background:#d89a3d}
.pp-low.--out .pp-low-b i span{background:var(--ef-danger)}
.pp-low-q{font-size:.8rem;font-variant-numeric:tabular-nums;text-align:right;white-space:nowrap}
.pp-low.--out .pp-low-q b{color:var(--ef-danger)}
.pp-big{font-size:1.5rem;font-weight:800;color:var(--ef-ink);font-variant-numeric:tabular-nums}
.pp-hint{font-size:.82rem;color:var(--ef-muted);margin:2px 0 8px}
.pp-flow{list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--ef-muted);display:grid;gap:7px}
.pp-flow li{display:flex;gap:8px;align-items:flex-start}
.pp-flow b{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.7rem;display:flex;align-items:center;justify-content:center}
@media (max-width:639.98px){.pp-flowcard{display:none}.pp-count{display:none}}
</style>
@endpush

@php
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');
    $tabs = ['all' => ['All', 'bi-collection'], 'draft' => ['Draft', 'bi-pencil-square'], 'approved' => ['Approved', 'bi-check2-circle'],
        'ordered' => ['Ordered', 'bi-truck'], 'completed' => ['Completed', 'bi-box-seam'], 'cancelled' => ['Cancelled', 'bi-x-circle']];
    $tabUrl = fn ($k) => route('admin.purchase-plans.index', array_merge($filters, $k === 'all' ? [] : ['status' => $k]));
    $filterCount = count(array_diff_key($filters, ['q' => 1]));
@endphp

<div class="pp">
    <header class="pp-head">
        <div class="pp-title">
            <h1>Purchase plans</h1>
            <p><span class="--long">Shopping lists built from low stock — planning only, stock changes when goods are received in <a href="{{ route('inventory.purchases.index') }}">Purchases</a>.</span></p>
        </div>
        <div class="pp-acts">
            <a href="{{ route('admin.purchase-plans.suggestions') }}" class="pp-btn"><i class="bi bi-lightbulb"></i> Suggestions @if($lowCount)<b>{{ $lowCount }}</b>@endif</a>
            <a href="{{ route('admin.purchase-plans.create') }}" class="pp-btn --main"><i class="bi bi-plus-lg"></i> New plan</a>
        </div>
    </header>

    <nav class="pp-q" aria-label="Plan status">
        @foreach($tabs as $k => [$label, $icon])
            @php $n = $k === 'all' ? $stats['total'] : ($stats[$k] ?? 0); $on = $k === 'all' ? ! $status : $status === $k; @endphp
            <a class="pp-qi {{ $on ? '--on' : '' }} {{ $n ? '' : '--zero' }}" data-k="{{ $k }}" href="{{ $tabUrl($k) }}" @if($on) aria-current="page" @endif>
                <small><i class="bi {{ $icon }}"></i> {{ $label }}</small><strong>{{ $n }}</strong></a>
        @endforeach
    </nav>

    <div class="pp-body">
        <section class="pp-panel" aria-label="Plans">
            <form method="GET" action="{{ route('admin.purchase-plans.index') }}" class="pp-bar" role="search">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <div class="pp-search"><i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search plan title" aria-label="Search plans"></div>
                <details class="pp-more" @if($filterCount) open @endif>
                    <summary><i class="bi bi-sliders"></i> Filters</summary>
                    <div class="pp-fields">
                        <label>Planned from<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
                        <label>Planned to<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
                        <div class="pp-go">
                            <button class="pp-btn --main" type="submit" style="flex:1">Apply</button>
                            @if($filters || $status)<a class="pp-btn" href="{{ route('admin.purchase-plans.index') }}">Reset</a>@endif
                        </div>
                    </div>
                </details>
                <span class="pp-count"><b>{{ $plans->total() }}</b> {{ \Illuminate\Support\Str::plural('plan', $plans->total()) }}</span>
            </form>

            <div class="pp-th" aria-hidden="true"><span>Plan</span><span>Planned for</span><span>Items</span><span>Created by</span><span>Status</span><span class="--r">Estimate</span></div>

            <div>
            @forelse($plans as $plan)
                @php
                    $tone = $planTone[$plan->status] ?? 'neutral';
                    $late = in_array($plan->status, ['draft', 'approved'], true) && $plan->planned_date->lt(today());
                    $est = (float) $plan->est_total;
                @endphp
                <div class="pp-row" data-tone="{{ $tone }}" data-plan>
                    <a class="pp-what" href="{{ route('admin.purchase-plans.show', $plan) }}">
                        <span class="pp-ico" aria-hidden="true"><i class="bi bi-cart-check"></i></span>
                        <span class="pp-what-b"><span class="pp-t">{{ $plan->title }}</span>
                            <span class="pp-s">Created {{ $plan->created_at?->format('d M Y') }}@if($plan->notes) · {{ \Illuminate\Support\Str::limit($plan->notes, 60) }}@endif</span></span>
                    </a>
                    <div class="pp-c-date"><span class="pp-date {{ $late ? '--late' : '' }}">{{ $plan->planned_date->format('d M Y') }}<small>{{ $late ? 'past planned date' : $plan->planned_date->diffForHumans(['parts' => 1]) }}</small></span></div>
                    <div class="pp-c-items pp-items">{{ $plan->items_count }} {{ \Illuminate\Support\Str::plural('item', $plan->items_count) }}@if($plan->urgent_count)<span class="pp-urg" title="Urgent or high priority">{{ $plan->urgent_count }} urgent</span>@endif</div>
                    <div class="pp-c-by">{{ $plan->creator?->name ?? '—' }}</div>
                    <div class="pp-mmeta">
                        <span class="{{ $late ? '--late' : '' }}"><i class="bi bi-calendar3"></i> {{ $plan->planned_date->format('d M') }}</span>
                        <span><i class="bi bi-box"></i> {{ $plan->items_count }} {{ \Illuminate\Support\Str::plural('item', $plan->items_count) }}@if($plan->urgent_count)<span class="pp-urg">{{ $plan->urgent_count }} urgent</span>@endif</span>
                        @if($plan->creator)<span><i class="bi bi-person"></i> {{ $plan->creator->name }}</span>@endif
                    </div>
                    <div class="pp-st">@include('inventory._status', ['label' => ucfirst($plan->status), 'tone' => $tone])</div>
                    <div class="pp-amt {{ $est > 0 ? '' : '--none' }}">{{ $est > 0 ? '₹'.number_format($est, 0) : 'No estimate' }}</div>
                </div>
            @empty
                <div class="pp-empty"><i class="bi bi-cart"></i>
                    @if($filters || $status) No plans match. <a href="{{ route('admin.purchase-plans.index') }}">Clear filters</a>
                    @else No purchase plans yet. <a href="{{ route('admin.purchase-plans.suggestions') }}">Start from low-stock suggestions</a>
                    @endif
                </div>
            @endforelse
            </div>

            @if($plans->total())
            <div class="pp-foot">
                <span>@if($plans->total())Showing <b>{{ $plans->firstItem() }}–{{ $plans->lastItem() }}</b> of {{ $plans->total() }}@endif</span>
                @if($plans->hasPages()){{ $plans->links() }}@endif
            </div>
            @endif
        </section>

        <aside class="pp-rail" aria-label="Planning help">
            <section class="pp-panel" style="--acc:#d89a3d;--tint:rgba(216,154,61,.09)">
                <div class="pp-card-h"><h2><i class="bi bi-exclamation-triangle"></i> Needs buying</h2>
                    @if($lowCount)<a href="{{ route('admin.purchase-plans.suggestions') }}">Plan these <i class="bi bi-arrow-right"></i></a>@endif</div>
                <div class="pp-card-b">
                    <div class="pp-big">{{ $lowCount }}</div>
                    <div class="pp-hint">{{ $lowCount === 1 ? 'item is' : 'items are' }} at or below the minimum stock you set</div>
                    @foreach($lowItems as $it)
                        @php $out = $it->current_stock <= 0; $pct = max(0, min(100, round($it->current_stock / $it->minimum_stock * 100))); @endphp
                        <div class="pp-low {{ $out ? '--out' : '' }}">
                            <div class="pp-low-b"><b>{{ $it->name }}</b><i><span style="width:{{ $pct }}%"></span></i></div>
                            <div class="pp-low-q"><b>{{ $q($it->current_stock) }}</b> / {{ $q($it->minimum_stock) }} {{ $it->unit }}</div>
                        </div>
                    @endforeach
                    @if(! $lowCount)<div class="pp-hint" style="margin:0">Set a minimum stock on items in the <a href="{{ route('admin.inventory.items.index') }}">catalogue</a> to get suggestions.</div>@endif
                </div>
            </section>
            <section class="pp-panel pp-flowcard" style="--acc:#8a827a;--tint:rgba(100,82,42,.05)">
                <div class="pp-card-h"><h2><i class="bi bi-diagram-3"></i> How plans work</h2></div>
                <div class="pp-card-b">
                    <ol class="pp-flow">
                        <li><b>1</b><span>Build a plan from suggestions or add items yourself.</span></li>
                        <li><b>2</b><span>Approve it, then mark it ordered when you place orders.</span></li>
                        <li><b>3</b><span>Record what arrives in Purchases — that is when stock goes up.</span></li>
                        <li><b>4</b><span>Mark the plan completed.</span></li>
                    </ol>
                </div>
            </section>
        </aside>
    </div>
</div>
</x-admin-layout>
