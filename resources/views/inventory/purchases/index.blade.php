<x-admin-layout title="Purchases">
@include('inventory._styles')

@push('styles')
<style>
/* Purchases workspace — lifecycle overview, toolbar, data table (cards on phones), insight rail. --ef-* tokens. */
.pw{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.pw-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.pw-title{flex:1 1 240px;min-width:0}
.pw-eyebrow{font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ef-gold)}
.pw-title h1{margin:2px 0 0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.pw-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.pw-acts{display:flex;gap:8px;flex-wrap:wrap}
.pw-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.pw-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.pw-btn .bi{color:var(--ef-gold)}
.pw-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.pw-btn.--main .bi{color:#f0c97a}
@media (max-width:639.98px){.pw-acts{width:100%;display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.pw-acts .--main{grid-column:1/-1}.pw-title p{display:none}.pw-title h1{font-size:1.25rem}}

/* lifecycle overview: one connected strip */
.pw-stages{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
@media (min-width:1000px){.pw-stages{grid-template-columns:repeat(6,minmax(0,1fr))}}
.pw-stage{position:relative;display:flex;flex-direction:column;gap:2px;padding:12px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);border-bottom:1px solid var(--ef-border);min-width:0}
@media (min-width:1000px){.pw-stage{border-bottom:0}.pw-stage:last-child{border-right:0}}
@media (max-width:999.98px){.pw-stage:nth-child(3n){border-right:0}.pw-stage:nth-child(n+4){border-bottom:0}}
.pw-stage:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.pw-stage small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pw-stage small .bi{font-size:.85rem}
.pw-stage strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15}
.pw-stage em{font-style:normal;font-size:.74rem;color:var(--ef-muted);font-variant-numeric:tabular-nums;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pw-stage.--on{background:var(--ef-bg-subtle)}
.pw-stage.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--ef-ink)}
.pw-stage.--act strong{color:#9a6413}
.pw-stage.--act small::after{content:"Action";margin-left:auto;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;padding:1px 6px;border-radius:6px;background:rgba(216,154,61,.18);color:#7D5218}
.pw-stage.--zero strong{color:var(--ef-faint)}
@media (max-width:639.98px){.pw-stage{padding:9px 10px}.pw-stage strong{font-size:1.1rem}.pw-stage em{display:none}.pw-stage.--act small::after{display:none}}

/* body: table + rail */
.pw-body{display:grid;gap:14px}
@media (min-width:1500px){.pw-body{grid-template-columns:minmax(0,1fr) 300px;align-items:start}}
.pw-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0}

/* toolbar */
.pw-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.pw-search{position:relative;flex:1 1 240px;min-width:0}
.pw-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.pw-search input{width:100%;min-height:40px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-bg-subtle);font-size:16px;color:var(--ef-ink)}
.pw-search input:focus{background:var(--ef-surface);outline:2px solid rgba(200,144,42,.35);border-color:var(--ef-gold)}
.pw-more{position:static}
.pw-more>summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;min-height:40px;padding:0 12px;border:1px solid var(--ef-border);border-radius:10px;font-weight:700;font-size:.84rem;color:var(--ef-ink);background:var(--ef-surface)}
.pw-more>summary::-webkit-details-marker{display:none}
.pw-more .pw-badge{min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:var(--ef-gold);color:#fff;font-size:.66rem;display:inline-flex;align-items:center;justify-content:center}
.pw-more[open]{flex-basis:100%;order:3}
.pw-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding-top:10px}
@media (min-width:720px){.pw-fields{grid-template-columns:repeat(4,minmax(0,1fr)) auto;align-items:end}}
.pw-fields label{display:flex;flex-direction:column;gap:3px;font-size:.72rem;font-weight:700;color:var(--ef-muted);min-width:0}
.pw-fields input,.pw-fields select{min-height:40px;padding:6px 10px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink);width:100%;min-width:0}
.pw-go{display:flex;gap:6px;grid-column:1/-1}
@media (min-width:720px){.pw-go{grid-column:auto}}
.pw-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.pw-count b{color:var(--ef-ink)}

/* table (desktop) / cards (phone) */
.pw-th,.pw-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:11px 14px}
.pw-th{display:none}
.pw-row{border-top:1px solid var(--ef-border)}
@media (max-width:899.98px){.pw-sup{order:0}.pw-amt{order:1}.pw-state{order:2}.pw-act{order:3}}
.pw-row:first-child{border-top:0}
.pw-row:hover{background:var(--ef-bg-subtle)}
.pw-sup{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;color:var(--ef-ink)}
.pw-av{flex:0 0 auto;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;color:#7D5218;background:rgba(216,154,61,.16);letter-spacing:.02em}
.pw-sup-b{min-width:0}
.pw-sup-n{display:block;font-weight:750;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pw-sup:hover .pw-sup-n{text-decoration:underline}
.pw-sup-s{display:block;font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pw-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.74rem}
.pw-c-date,.pw-c-items{display:none}
.pw-amt{text-align:right;font-weight:800;font-variant-numeric:tabular-nums;font-size:.94rem;white-space:nowrap}
.pw-state{display:flex;align-items:center;gap:8px;flex-wrap:wrap;grid-column:1/-1;padding-left:46px}
.pw-state .ic-chip{font-size:.68rem}
.pw-prog{display:inline-flex;align-items:center;gap:6px;font-size:.72rem;color:var(--ef-muted);font-variant-numeric:tabular-nums;white-space:nowrap}
.pw-prog i{display:block;width:56px;height:5px;border-radius:5px;background:rgba(100,82,42,.12);overflow:hidden}
.pw-prog i span{display:block;height:100%;background:#3b6ea5}
.pw-prog.--done i span{background:var(--ef-emerald)}
.pw-bill{display:inline-flex;align-items:center;gap:4px;font-size:.72rem;color:var(--ef-faint);white-space:nowrap}
.pw-bill.--yes{color:var(--ef-emerald)}
.pw-act{display:flex;justify-content:flex-end}
.pw-do{display:inline-flex;align-items:center;gap:5px;min-height:34px;padding:4px 12px;border-radius:9px;font-size:.78rem;font-weight:750;text-decoration:none;border:1px solid var(--ef-border);color:var(--ef-ink);background:var(--ef-surface);white-space:nowrap}
.pw-do:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.pw-do.--primary{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.pw-do.--primary .bi{color:#f0c97a}
.pw-do.--ghost{border-color:transparent;color:var(--ef-muted)}
@media (max-width:899.98px){.pw-act{grid-column:1/-1;padding-left:46px;justify-content:flex-start}.pw-act:empty{display:none}}
@media (min-width:900px){
  .pw-th,.pw-row{grid-template-columns:minmax(200px,1fr) 100px 200px 100px 104px;gap:14px}
  .pw-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.68rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .pw-th .--r{text-align:right}
  .pw-c-date,.pw-c-items{display:block;font-size:.84rem;font-variant-numeric:tabular-nums;color:var(--ef-ink)}
  .pw-c-items{text-align:center}
  .pw-c-date small{display:block;font-size:.7rem;color:var(--ef-muted)}
  .pw-state{grid-column:auto;padding-left:0;flex-direction:column;align-items:flex-start;gap:4px}
  .pw-amt{order:0}
  .pw-sup-s .pw-when{display:none}
}
.pw-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.pw-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.pw-empty a{font-weight:700}
.pw-foot{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:8px;padding:10px 14px;border-top:1px solid var(--ef-border);font-size:.8rem;color:var(--ef-muted)}
.pw-foot b{color:var(--ef-ink)}
.pw-foot nav{overflow-x:auto;max-width:100%}
.pw-foot .pagination{margin:0}

/* insight rail */
.pw-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:640px) and (max-width:1499.98px){.pw-rail{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:start}}
@media (min-width:640px) and (max-width:899.98px){.pw-rail{grid-template-columns:repeat(2,minmax(0,1fr))}}
.pw-card{padding:14px}
.pw-card h2{margin:0 0 10px;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted);display:flex;align-items:center;gap:6px}
.pw-big{font-size:1.5rem;font-weight:800;font-variant-numeric:tabular-nums;color:var(--ef-ink);line-height:1.1}
.pw-sub{font-size:.78rem;color:var(--ef-muted);margin-top:2px}
.pw-top{list-style:none;margin:0;padding:0}
.pw-top li{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:2px 8px;padding:7px 0;border-top:1px solid var(--ef-border);font-size:.84rem}
.pw-top li:first-child{border-top:0;padding-top:0}
.pw-top span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:650;color:var(--ef-ink)}
.pw-top b{font-variant-numeric:tabular-nums}
.pw-top i{grid-column:1/-1;display:block;height:4px;border-radius:4px;background:rgba(100,82,42,.1);overflow:hidden}
.pw-top i em{display:block;height:100%;background:var(--ef-gold)}
.pw-flow{list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--ef-muted);display:grid;gap:6px}
.pw-flow li{display:flex;gap:8px;align-items:flex-start}
.pw-flow b{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.7rem;display:flex;align-items:center;justify-content:center}
</style>
@endpush

@php
    $u = auth()->user();
    $stages = \App\Http\Controllers\Inventory\PurchaseController::STAGES;
    $icons = ['approve' => 'bi-hourglass-split', 'awaiting' => 'bi-truck', 'received' => 'bi-box-seam', 'draft' => 'bi-pencil-square', 'closed' => 'bi-x-circle'];
    $allCount = $counts->sum();
    $current = $status ? null : $stage;
    $actionFor = array_filter(['approve' => $u->isAdmin(), 'awaiting' => $u->isStorekeeper()]);
    $tabUrl = fn ($s) => route('inventory.purchases.index', array_merge($filters, $s ? ['stage' => $s] : []));
    $filterCount = count(array_diff_key($filters, ['q' => 1])) + ($status ? 1 : 0);
    $rs = fn ($v) => '₹'.number_format((float) $v, 0);
    $initials = fn ($name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '—';
    $topMax = max((float) ($topSuppliers->max('t') ?? 0), 1);
@endphp

<div class="pw">
    <header class="pw-head">
        <div class="pw-title">
            <h1>Purchases</h1>
            <p>Record what you buy, get it approved, then receive goods — stock increases only on receipt.</p>
        </div>
        <div class="pw-acts">
            <a href="{{ route('inventory.purchases.create') }}" class="pw-btn --main"><i class="bi bi-plus-lg"></i> New purchase</a>
            @if($u->isAdmin())
                <a href="{{ route('admin.inventory.bills.index') }}" class="pw-btn"><i class="bi bi-upc-scan"></i> Scan bill</a>
                <a href="{{ route('admin.purchase-plans.index') }}" class="pw-btn"><i class="bi bi-list-check"></i> Plans</a>
            @endif
        </div>
    </header>

    {{-- Lifecycle overview (also the tabs) --}}
    <nav class="pw-stages" aria-label="Purchase stages">
        <a class="pw-stage {{ ! $current && ! $status ? '--on' : '' }}" href="{{ $tabUrl(null) }}" @if(! $current && ! $status) aria-current="page" @endif>
            <small><i class="bi bi-collection"></i> All</small><strong>{{ $allCount }}</strong><em>{{ $rs($committed) }} committed</em></a>
        @foreach($stages as $k => [$label, $sts])
            @php $n = $stageCounts[$k] ?? 0; $on = $current === $k || ($status && in_array($status, $sts, true)); @endphp
            <a class="pw-stage {{ $on ? '--on' : '' }} {{ isset($actionFor[$k]) && $n ? '--act' : '' }} {{ $n ? '' : '--zero' }}" href="{{ $tabUrl($k) }}" data-stage="{{ $k }}" @if($on) aria-current="page" @endif>
                <small><i class="bi {{ $icons[$k] }}"></i> {{ $label }}</small><strong>{{ $n }}</strong><em>{{ $n ? $rs($stageTotals[$k] ?? 0) : '—' }}</em></a>
        @endforeach
    </nav>

    <div class="pw-body">
        <section class="pw-panel" aria-label="Purchase list">
            <form method="GET" action="{{ route('inventory.purchases.index') }}" class="pw-bar" role="search">
                @if($current)<input type="hidden" name="stage" value="{{ $current }}">@endif
                <div class="pw-search"><i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search supplier, bill no. or PO-00012" aria-label="Search purchases"></div>
                <details class="pw-more" @if($filterCount) open @endif>
                    <summary><i class="bi bi-sliders"></i> Filters @if($filterCount)<span class="pw-badge">{{ $filterCount }}</span>@endif</summary>
                    <div class="pw-fields">
                        <label>From<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
                        <label>To<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
                        <label>Bill
                            <select name="bill">
                                <option value="">Any</option>
                                <option value="with" @selected(($filters['bill'] ?? '') === 'with')>Bill attached</option>
                                <option value="without" @selected(($filters['bill'] ?? '') === 'without')>No bill</option>
                            </select></label>
                        <label>Exact status
                            <select name="status">
                                <option value="">Any</option>
                                @foreach(\App\Models\Purchase::STATUSES as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach
                            </select></label>
                        <div class="pw-go">
                            <button class="pw-btn --main" type="submit" style="flex:1">Apply</button>
                            @if($filters || $status || $current)<a class="pw-btn" href="{{ route('inventory.purchases.index') }}">Reset</a>@endif
                        </div>
                    </div>
                </details>
                <span class="pw-count"><b>{{ $purchases->total() }}</b> {{ $purchases->total() === 1 ? 'purchase' : 'purchases' }}@if($current) · {{ $stages[$current][0] }}@elseif($status) · {{ \App\Models\Purchase::STATUSES[$status] }}@endif</span>
            </form>

            <div class="pw-th" aria-hidden="true">
                <span>Supplier</span><span>Date</span><span>Status &amp; receipt</span><span class="--r">Amount</span><span class="--r">Action</span>
            </div>

            <div class="pw-rows">
            @forelse($purchases as $p)
                @php
                    $lines = $p->items_count; $done = min($p->lines_done, $lines);
                    $receiving = in_array($p->status, ['approved', 'partially_received', 'received', 'completed'], true);
                    $hasBill = $p->bill || $p->bills_on_receipts > 0;
                    $sup = $p->supplierLabel();
                @endphp
                <div class="pw-row" data-purchase>
                    <a class="pw-sup" href="{{ route('inventory.purchases.show', $p) }}">
                        <span class="pw-av" aria-hidden="true">{{ $initials($sup) }}</span>
                        <span class="pw-sup-b">
                            <span class="pw-sup-n">{{ $sup }}</span>
                            <span class="pw-sup-s"><span class="pw-ref">{{ $p->reference() }}</span><span class="pw-when"> · {{ $p->purchase_date->format('d M') }}</span> · {{ $lines }} item{{ $lines === 1 ? '' : 's' }} · {{ $p->creator?->name }}</span>
                        </span>
                    </a>
                    <div class="pw-c-date">{{ $p->purchase_date->format('d M Y') }}<small>{{ $p->purchase_date->diffForHumans(['parts' => 1, 'short' => false]) }}</small></div>
                    <div class="pw-state">
                        @include('inventory._status', ['label' => $p->statusLabel(), 'tone' => \App\Models\Purchase::statusTone($p->status)])
                        <span style="display:inline-flex;gap:10px;flex-wrap:wrap">
                            @if($receiving && $lines)
                                <span class="pw-prog {{ $done >= $lines ? '--done' : '' }}" title="Lines fully received or closed"><i><span style="width:{{ round($done / $lines * 100) }}%"></span></i>{{ $done }}/{{ $lines }} recv.</span>
                            @endif
                            <span class="pw-bill {{ $hasBill ? '--yes' : '' }}"><i class="bi {{ $hasBill ? 'bi-file-earmark-check' : 'bi-file-earmark' }}"></i>{{ $hasBill ? ($p->invoice_number ?: 'Bill attached') : 'No bill' }}</span>
                        </span>
                    </div>
                    <div class="pw-amt">{{ $rs($p->grand_total) }}</div>
                    <div class="pw-act">
                        @can('receive', $p)
                            <a class="pw-do --primary" href="{{ route('inventory.purchases.receive-form', $p) }}"><i class="bi bi-truck"></i> Receive</a>
                        @elsecan('approve', $p)
                            <a class="pw-do --primary" href="{{ route('inventory.purchases.show', $p) }}"><i class="bi bi-check2-circle"></i> Review</a>
                        @elsecan('update', $p)
                            @if($p->status === 'draft')<a class="pw-do" href="{{ route('inventory.purchases.edit', $p) }}"><i class="bi bi-pencil"></i> Continue</a>@endif
                        @else
                            <a class="pw-do --ghost" href="{{ route('inventory.purchases.show', $p) }}">View <i class="bi bi-chevron-right"></i></a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="pw-empty"><i class="bi bi-bag"></i>
                    @if($filters || $status || $current)
                        No purchases match these filters. <a href="{{ route('inventory.purchases.index') }}">Clear filters</a>
                    @else
                        No purchases yet. <a href="{{ route('inventory.purchases.create') }}">Record your first purchase</a>
                    @endif
                </div>
            @endforelse
            </div>

            <div class="pw-foot">
                <span>@if($purchases->total())Showing <b>{{ $purchases->firstItem() }}–{{ $purchases->lastItem() }}</b> of {{ $purchases->total() }}@endif</span>
                @if($purchases->hasPages()){{ $purchases->links() }}@endif
            </div>
        </section>

        {{-- Insight rail: real figures only --}}
        <aside class="pw-rail" aria-label="Purchase insights">
            <div class="pw-panel pw-card">
                <h2><i class="bi bi-wallet2"></i> Owed to suppliers</h2>
                <div class="pw-big">{{ $rs(max((float) ($dues->due ?? 0), 0)) }}</div>
                <div class="pw-sub">{{ (int) ($dues->c ?? 0) }} purchase{{ (int) ($dues->c ?? 0) === 1 ? '' : 's' }} unpaid or part-paid</div>
            </div>
            <div class="pw-panel pw-card">
                <h2><i class="bi bi-shop"></i> Top suppliers · 30 days</h2>
                @if($topSuppliers->isNotEmpty())
                    <ul class="pw-top">
                        @foreach($topSuppliers as $t)
                            <li><span>{{ $t->name }}</span><b>{{ $rs($t->t) }}</b><i><em style="width:{{ round($t->t / $topMax * 100) }}%"></em></i></li>
                        @endforeach
                    </ul>
                @else
                    <div class="pw-sub">No purchases in the last 30 days.</div>
                @endif
            </div>
            <div class="pw-panel pw-card">
                <h2><i class="bi bi-diagram-3"></i> How a purchase flows</h2>
                <ol class="pw-flow">
                    <li><b>1</b><span>Create &amp; submit — or scan a supplier bill.</span></li>
                    <li><b>2</b><span>Admin approves.</span></li>
                    <li><b>3</b><span>Store receives goods; short or extra quantities are recorded. <strong style="color:var(--ef-ink)">Stock is added here.</strong></span></li>
                </ol>
            </div>
        </aside>
    </div>
</div>
</x-admin-layout>
