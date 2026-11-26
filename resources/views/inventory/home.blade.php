<x-admin-layout title="Store & Inventory">
@include('inventory._styles')
@include('inventory._item-picker')

@push('styles')
<style>
/* Store home — operational workspace (same language as Purchases): header, KPI strip, search, inbox, work panels + rail. */
.sd{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.sd-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.sd-title{flex:1 1 240px;min-width:0}
.sd-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.sd-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.sd-title p b{color:var(--ef-ink)}
.sd-acts{display:flex;flex-wrap:wrap;gap:8px}
.sd-act{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.sd-act:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.sd-act .bi{color:var(--ef-gold)}
.sd-act.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.sd-act.--main .bi{color:#f0c97a}
@media (max-width:639.98px){.sd-acts{width:100%;display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.sd-act.--main{grid-column:1/-1}.sd-title h1{font-size:1.25rem}}

/* KPI strip */
.sd-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
@media (min-width:1000px){.sd-kpis{grid-template-columns:repeat(var(--n,6),minmax(0,1fr))}}
.sd-kpi{display:flex;flex-direction:column;gap:2px;padding:12px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);border-bottom:1px solid var(--ef-border);min-width:0}
@media (min-width:1000px){.sd-kpi{border-bottom:0}.sd-kpi:last-child{border-right:0}}
@media (max-width:999.98px){.sd-kpi:nth-child(3n){border-right:0}.sd-kpi:nth-last-child(-n+3):nth-child(3n+1),.sd-kpi:nth-last-child(-n+3):nth-child(3n+1)~.sd-kpi{border-bottom:0}}
a.sd-kpi:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.sd-kpi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-kpi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15}
.sd-kpi em{font-style:normal;font-size:.74rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-kpi[data-tone=ok] strong{color:var(--ef-emerald)}
.sd-kpi[data-tone=warn] strong{color:#9a6413}
.sd-kpi[data-tone=bad] strong{color:var(--ef-danger)}
.sd-kpi[data-tone=info] strong{color:#2c5784}
.sd-kpi.--zero strong{color:var(--ef-faint)}
@media (max-width:639.98px){.sd-kpi{padding:9px 10px}.sd-kpi strong{font-size:1.1rem}.sd-kpi em{display:none}}

/* search */
.sd-search{position:relative}
.sd-search>.bi-search{position:absolute;left:14px;top:22px;transform:translateY(-50%);color:var(--ef-faint);z-index:2;pointer-events:none}
.sd-search .ic-input{min-height:44px;padding-left:40px;font-size:16px;border-radius:12px;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.sd-found{display:none;align-items:center;gap:10px;margin-top:8px;padding:10px 12px;border-radius:12px;border:1px solid var(--ef-border);background:var(--ef-surface)}
.sd-found.--on{display:flex;flex-wrap:wrap}
.sd-found-main{flex:1 1 180px;min-width:0}
.sd-found-main strong{display:block;overflow-wrap:anywhere}
.sd-found-main span{font-size:.8rem;color:var(--ef-muted)}
.sd-found-acts{display:flex;gap:6px;flex-wrap:wrap}

/* inbox */
.sd-inbox{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.sd-inbox-h{display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px solid var(--ef-border);background:var(--ef-bg-subtle);font-size:.7rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
.sd-inbox-h b{min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:var(--ef-danger);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.68rem;letter-spacing:0}
.sd-inbox-g{display:grid}
@media (min-width:900px){.sd-inbox-g{grid-template-columns:repeat(auto-fit,minmax(340px,1fr))}}
.sd-task{display:flex;align-items:center;gap:12px;padding:12px 14px;min-height:62px;color:var(--ef-ink);text-decoration:none;border-top:1px solid var(--ef-border)}
@media (min-width:900px){.sd-task{border-right:1px solid var(--ef-border)}}
.sd-task:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.sd-task-n{flex:0 0 auto;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;font-weight:800;font-variant-numeric:tabular-nums;background:var(--ef-bg-subtle)}
.sd-task[data-tone=bad] .sd-task-n{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.sd-task[data-tone=warn] .sd-task-n{background:rgba(216,154,61,.16);color:#9a6413}
.sd-task[data-tone=info] .sd-task-n{background:rgba(59,110,165,.12);color:#2c5784}
.sd-task-b{flex:1;min-width:0}
.sd-task-t{display:block;font-weight:750;font-size:.9rem;line-height:1.25}
.sd-task-s{display:block;font-size:.76rem;color:var(--ef-muted);line-height:1.3}
.sd-task-go{flex:0 0 auto;display:inline-flex;align-items:center;gap:4px;min-height:32px;padding:3px 10px;border-radius:8px;border:1px solid var(--ef-border);font-size:.76rem;font-weight:750;white-space:nowrap}
.sd-task:hover .sd-task-go{border-color:var(--ef-gold)}
.sd-clear{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:14px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-muted);font-size:.9rem}
.sd-clear .bi{font-size:1.4rem;color:var(--ef-emerald)}

/* body */
.sd-grid{display:grid;gap:14px}
@media (min-width:1100px){.sd-grid{grid-template-columns:minmax(0,1fr) minmax(320px,380px);align-items:start}}
.sd-col{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (max-width:1099.98px){.sd-col{display:contents}.sd-grid>.sd-col>section{order:var(--o,9)}}
.sd-card{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);min-width:0;overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.sd-card-h{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border)}
.sd-card-h h2{margin:0;font-size:.95rem;font-weight:800;color:var(--ef-ink);display:flex;align-items:center;gap:8px}
.sd-card-h h2 .bi{color:var(--ef-gold)}
.sd-more{margin-left:auto;font-size:.78rem;font-weight:700;color:var(--ef-gold);text-decoration:none;white-space:nowrap}
.sd-card-b{padding:4px 14px 8px}
.sd-empty{font-size:.86rem;color:var(--ef-muted);padding:14px 0;text-align:center}
.sd-empty .bi{display:block;font-size:1.4rem;margin-bottom:4px;color:var(--ef-faint)}
.sd-empty a{font-weight:700}

/* request tabs */
.sd-tabs{display:flex;gap:2px;overflow-x:auto;scrollbar-width:none;padding:0 10px;border-bottom:1px solid var(--ef-border)}
.sd-tabs::-webkit-scrollbar{display:none}
.sd-tab{flex:0 0 auto;display:inline-flex;align-items:center;gap:6px;min-height:42px;padding:6px 10px;border:0;border-bottom:2px solid transparent;background:transparent;color:var(--ef-muted);font-weight:700;font-size:.82rem;cursor:pointer;margin-bottom:-1px}
.sd-tab b{min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.68rem;display:inline-flex;align-items:center;justify-content:center;font-variant-numeric:tabular-nums}
.sd-tab[aria-selected=true]{color:var(--ef-ink);border-bottom-color:var(--ef-ink)}
.sd-tab[aria-selected=true] b{background:var(--ef-ink);color:#fff}
.sd-pane[hidden]{display:none}
.sd-req{display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none;min-height:54px}
.sd-pane>.sd-req:first-child{border-top:0}
.sd-req:hover .sd-req-t{text-decoration:underline}
.sd-when{flex:0 0 auto;width:52px;text-align:center;border-radius:10px;padding:5px 0;background:var(--ef-bg-subtle);line-height:1.15}
.sd-when b{display:block;font-size:.78rem;font-weight:800}
.sd-when span{font-size:.68rem;color:var(--ef-muted)}
.sd-when.--today{background:rgba(216,154,61,.18)}
.sd-when.--today b{color:#7D5218}
.sd-when.--late{background:rgba(192,57,43,.1)}
.sd-when.--late b{color:var(--ef-danger)}
.sd-req-b{flex:1;min-width:0}
.sd-req-t{display:block;font-weight:700;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-req-s{display:block;font-size:.76rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-req .ic-chip{flex:0 0 auto;font-size:.68rem}
.sd-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.72rem}

/* movement table */
.sd-mv-h{display:none}
@media (min-width:760px){
  .sd-mv-h,.sd-mv{display:grid;grid-template-columns:34px minmax(0,1fr) minmax(0,1.2fr) 90px 110px;gap:12px;align-items:center}
  .sd-mv-h{padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .sd-mv-h .--r{text-align:right}
}
.sd-mv{display:flex;align-items:center;gap:10px;padding:9px 14px;border-top:1px solid var(--ef-border);min-height:50px}
.sd-mv:first-of-type{border-top:0}
.sd-ico{flex:0 0 auto;width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;background:var(--ef-bg-subtle);font-size:.9rem}
.sd-ico.--add{color:var(--ef-emerald);background:rgba(30,122,60,.08)}
.sd-ico.--sub{color:var(--ef-danger);background:rgba(192,57,43,.08)}
.sd-ico.--neutral{color:var(--ef-muted)}
.sd-mv-item{min-width:0;flex:1}
.sd-mv-item b{display:block;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-mv-item span,.sd-mv-ref{font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}
.sd-mv-ref{display:none;text-align:left}
.sd-mv-when{display:none;font-size:.75rem;color:var(--ef-muted);text-align:right;white-space:nowrap}
@media (min-width:760px){.sd-mv-ref,.sd-mv-when{display:block}.sd-mv-item span.--m{display:none}}
.sd-delta{flex:0 0 auto;font-weight:800;font-size:.86rem;font-variant-numeric:tabular-nums;text-align:right;white-space:nowrap}
.sd-delta.--add{color:var(--ef-emerald)}
.sd-delta.--sub{color:var(--ef-danger)}

/* running low */
.sd-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-top:1px solid var(--ef-border);min-height:52px}
.sd-item:first-of-type{border-top:0}
.sd-item-b{flex:1;min-width:0}
.sd-item-t{display:block;font-weight:700;font-size:.88rem;overflow-wrap:anywhere;color:var(--ef-ink);text-decoration:none}
a.sd-item-t:hover{text-decoration:underline}
.sd-item-s{display:block;font-size:.74rem;color:var(--ef-muted)}
.sd-item-s .--in{color:#2c5784;font-weight:700}
.sd-meter{height:4px;border-radius:4px;background:rgba(100,82,42,.1);margin-top:4px;overflow:hidden}
.sd-meter span{display:block;height:100%;background:#d89a3d}
.sd-item.--out .sd-meter span{background:var(--ef-danger)}
.sd-qty{flex:0 0 auto;text-align:right;font-variant-numeric:tabular-nums;line-height:1.15}
.sd-qty b{display:block;font-size:.92rem}
.sd-item.--out .sd-qty b{color:var(--ef-danger)}
.sd-item.--low .sd-qty b{color:#9a6413}
.sd-qty small{font-size:.68rem;color:var(--ef-muted)}

/* arriving + links */
.sd-row{display:flex;align-items:center;gap:10px;padding:9px 0;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none;min-height:50px}
.sd-row:first-of-type{border-top:0}
a.sd-row:hover .sd-req-t{text-decoration:underline}
.sd-links{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;padding:10px 14px 14px}
.sd-link{display:flex;align-items:center;gap:8px;min-height:42px;padding:6px 10px;border-radius:10px;border:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none;font-size:.82rem;font-weight:700;min-width:0}
.sd-link span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd-link .bi{color:var(--ef-gold)}
.sd-link:hover{border-color:var(--ef-gold);color:var(--ef-ink)}

/* section colour coding: each panel has an accent (top edge, tinted header, icon) */
.sd-card,.sd-inbox{--acc:var(--ef-gold);--acc-bg:rgba(200,144,42,.07);border-top:3px solid var(--acc)}
.sd-card-h,.sd-inbox-h{background:var(--acc-bg)}
.sd-card-h h2 .bi,.sd-inbox-h>.bi{color:var(--acc)}
.sd-inbox{--acc:var(--ef-danger);--acc-bg:rgba(192,57,43,.06)}
.sd-inbox-h{color:#8f2d22}
.sd-card[data-acc=req]{--acc:#3b6ea5;--acc-bg:rgba(59,110,165,.07)}
.sd-card[data-acc=low]{--acc:#d89a3d;--acc-bg:rgba(216,154,61,.09)}
.sd-card[data-acc=mv]{--acc:var(--ef-emerald);--acc-bg:rgba(30,122,60,.06)}
.sd-card[data-acc=arr]{--acc:#6b5bb5;--acc-bg:rgba(107,91,181,.07)}
.sd-card[data-acc=go]{--acc:#8a827a;--acc-bg:rgba(100,82,42,.05)}
.sd-tab[aria-selected=true]{border-bottom-color:var(--acc)}
.sd-tab[aria-selected=true] b{background:var(--acc)}
/* KPI tints by meaning */
.sd-kpi[data-tone=ok]{background:rgba(30,122,60,.05)}
.sd-kpi[data-tone=warn]{background:rgba(216,154,61,.07)}
.sd-kpi[data-tone=bad]{background:rgba(192,57,43,.05)}
.sd-kpi[data-tone=info]{background:rgba(59,110,165,.06)}
.sd-kpi[data-tone=req]{background:rgba(107,91,181,.05)}
.sd-kpi[data-tone=req] strong{color:#4f4396}
.sd-kpi[data-tone=val]{background:var(--ef-ink);color:#fff}
.sd-kpi[data-tone=val] small,.sd-kpi[data-tone=val] em{color:rgba(255,255,255,.7)}
.sd-kpi[data-tone=val] .bi{color:#f0c97a}
a.sd-kpi[data-tone=val]:hover{background:#2a2622;color:#fff}
.sd-kpi.--zero{background:var(--ef-surface)}
.sd-kpi small .bi{opacity:.9}
</style>
@endpush

@php
    $u = auth()->user();
    $isStoreSide = $u->isAdmin() || $u->isManager() || $u->isStorekeeper();
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');

    // Actionable inbox — highest priority first; zero rows never shown.
    $todo = [];
    if ($admin) {
        $todo[] = ['n' => $admin['materials'], 'tone' => 'bad', 't' => 'Material requests to approve', 's' => 'Kitchen is waiting — approve or adjust quantities', 'go' => 'Review', 'href' => route('inventory.material-requests.index', ['tab' => 'approve'])];
        $todo[] = ['n' => $admin['alerts'], 'tone' => 'bad', 't' => 'Items at or below minimum', 's' => ($snapshot['outTracked'] ? $snapshot['outTracked'].' out of stock · ' : '').'reorder before the next event', 'go' => 'Plan purchase', 'href' => route('admin.purchase-plans.create')];
        $todo[] = ['n' => $admin['review'], 'tone' => 'bad', 't' => 'Unusual usage to check', 's' => 'Wastage or unexplained loss reported', 'go' => 'Check', 'href' => route('inventory.material-requests.index', ['tab' => 'review'])];
        $todo[] = ['n' => $admin['purchases'], 'tone' => 'warn', 't' => 'Purchases to approve', 's' => 'Submitted purchases need your OK', 'go' => 'Approve', 'href' => route('inventory.purchases.index', ['status' => 'submitted'])];
    }
    if ($store) {
        $todo[] = ['n' => $store['handover'], 'tone' => 'warn', 't' => 'Materials to hand over', 's' => 'Approved, not yet collected by kitchen', 'go' => 'Hand over', 'href' => route('inventory.material-requests.index', ['tab' => 'handover'])];
        $todo[] = ['n' => $store['receive'], 'tone' => 'info', 't' => 'Deliveries to receive', 's' => 'Approved purchases not fully received', 'go' => 'Receive', 'href' => route('inventory.purchases.index', ['status' => 'approved'])];
        $todo[] = ['n' => $store['out'], 'tone' => '', 't' => 'Materials out with kitchen', 's' => 'Record returns when they come back', 'go' => 'Open', 'href' => route('inventory.material-requests.index', ['tab' => 'out'])];
    }
    if ($admin) {
        $todo[] = ['n' => $admin['unreconciled'], 'tone' => 'warn', 't' => 'Usage not recorded', 's' => 'Past events without a usage report', 'go' => 'See', 'href' => route('inventory.reports.show', 'reconciliation')];
    }
    $todo[] = ['n' => $kitchen['usage'], 'tone' => 'info', 't' => 'Record your usage', 's' => 'Your issued materials — report used / returned', 'go' => 'Record', 'href' => route('inventory.material-requests.index', ['tab' => 'mine'])];
    $todo = array_values(array_filter($todo, fn ($t) => $t['n'] > 0));

    $when = function ($r) {
        $d = $r->required_date;
        return [
            'cls' => $d->isToday() ? '--today' : ($d->isPast() ? '--late' : ''),
            'day' => $d->isToday() ? 'Today' : ($d->isTomorrow() ? 'Tmrw' : $d->format('D')),
            'sub' => $r->required_time ? \Carbon\Carbon::parse($r->required_time)->format('h:i A') : $d->format('d M'),
        ];
    };

    // request tabs: only the ones relevant to this role, first non-empty is selected
    $tabs = [];
    if ($admin) $tabs['approve'] = ['To approve', $admin['queue'], $admin['materials'], route('inventory.material-requests.index', ['tab' => 'approve'])];
    if ($store) $tabs['handover'] = ['To hand over', $store['queue'], $store['handover'], route('inventory.material-requests.index', ['tab' => 'handover'])];
    if ($isStoreSide) $tabs['soon'] = ['Today & tomorrow', $upcoming, $upcoming->count(), route('inventory.material-requests.index', ['tab' => 'all'])];
    $tabs['mine'] = ['Mine', $kitchen['recent'], $kitchen['waiting'], route('inventory.material-requests.index', ['tab' => 'mine'])];
    $activeTab = collect($tabs)->search(fn ($t) => $t[1]->isNotEmpty()) ?: array_key_first($tabs);

@endphp

<div class="sd">
    <header class="sd-head">
        <div class="sd-title">
            <h1>Store &amp; Inventory</h1>
            <p>{{ now('Asia/Kolkata')->format('l, d M') }}@if($todo) · <b>{{ count($todo) }}</b> {{ count($todo) === 1 ? 'thing needs' : 'things need' }} your attention @else · all clear @endif</p>
        </div>
        <nav class="sd-acts" aria-label="Quick actions">
            <a class="sd-act --main" href="{{ route('inventory.material-requests.create') }}"><i class="bi bi-basket"></i> Request materials</a>
            <a class="sd-act" href="{{ route('inventory.purchases.create') }}"><i class="bi bi-bag-plus"></i> New purchase</a>
            @if($u->isAdmin())
                <a class="sd-act" href="{{ route('admin.inventory.bills.index') }}"><i class="bi bi-upc-scan"></i> Scan bill</a>
            @elseif($isStoreSide)
                <a class="sd-act" href="{{ route('inventory.stock') }}"><i class="bi bi-boxes"></i> Stock</a>
            @else
                <a class="sd-act" href="{{ route('inventory.material-requests.index', ['tab' => 'mine']) }}"><i class="bi bi-list-check"></i> My requests</a>
            @endif
        </nav>
    </header>

    {{-- Stock health strip (store-side only) --}}
    @if($snapshot)
        @php $kpis = array_values(array_filter([
            ['k' => 'in', 'l' => 'In stock', 'i' => 'bi-check2-circle', 'n' => $snapshot['in'], 'e' => 'of '.$snapshot['total'].' active items', 'tone' => 'ok', 'href' => route('inventory.stock', ['show' => 'in'])],
            ['k' => 'low', 'l' => 'Low', 'i' => 'bi-exclamation-triangle', 'n' => $snapshot['low'], 'e' => 'at or below minimum', 'tone' => 'warn', 'href' => route('inventory.stock', ['show' => 'low'])],
            ['k' => 'out', 'l' => 'Out', 'i' => 'bi-x-octagon', 'n' => $snapshot['outTracked'], 'e' => 'need reorder · '.$snapshot['out'].' empty in catalogue', 'tone' => 'bad', 'href' => route('inventory.stock', ['show' => 'out'])],
            ['k' => 'incoming', 'l' => 'Arriving', 'i' => 'bi-truck', 'n' => $snapshot['incoming'], 'e' => 'items on open purchases', 'tone' => 'info', 'href' => route('inventory.stock', ['show' => 'incoming'])],
            ['k' => 'req', 'l' => 'Requests', 'i' => 'bi-clipboard-check', 'n' => $openRequests, 'e' => 'submitted, approved or issued', 'tone' => 'req', 'href' => route('inventory.material-requests.index', ['tab' => 'all'])],
            $snapshot['value'] !== null ? ['k' => 'value', 'l' => 'Stock value', 'i' => 'bi-currency-rupee', 'n' => '₹'.number_format($snapshot['value'], 0), 'e' => 'at average cost', 'tone' => 'val', 'href' => route('inventory.stock')] : null,
        ])); @endphp
        <nav class="sd-kpis" aria-label="Stock health" data-snapshot style="--n:{{ count($kpis) }}">
            @foreach($kpis as $k)
                <a class="sd-kpi {{ $k['n'] === 0 ? '--zero' : '' }}" href="{{ $k['href'] }}" data-kpi="{{ $k['k'] }}" data-tone="{{ $k['tone'] }}">
                    <small><i class="bi {{ $k['i'] }}"></i> {{ $k['l'] }}</small><strong>{{ $k['n'] }}</strong><em>{{ $k['e'] }}</em></a>
            @endforeach
        </nav>
    @endif

    {{-- Find an item (shared English / தமிழ் / Tanglish item picker) --}}
    <div>
        <div class="sd-search"><i class="bi bi-search"></i>
            <div><input type="text" class="ic-input" id="icFindItem" placeholder="Find an item — English, தமிழ் or Tanglish (e.g. parupu)" autocomplete="off" aria-label="Find an item"></div>
        </div>
        <div class="sd-found" id="icFindResult" aria-live="polite"></div>
    </div>

    {{-- Needs attention --}}
    @if($todo)
        <section class="sd-inbox" aria-labelledby="sdTodo">
            <div class="sd-inbox-h" id="sdTodo"><i class="bi bi-lightning-charge"></i> Needs attention <b>{{ count($todo) }}</b></div>
            <div class="sd-inbox-g" data-inbox>
                @foreach($todo as $t)
                    <a class="sd-task" href="{{ $t['href'] }}" data-tone="{{ $t['tone'] }}">
                        <span class="sd-task-n">{{ $t['n'] }}</span>
                        <span class="sd-task-b"><span class="sd-task-t">{{ $t['t'] }}</span><span class="sd-task-s">{{ $t['s'] }}</span></span>
                        <span class="sd-task-go">{{ $t['go'] }} <i class="bi bi-arrow-right"></i></span>
                    </a>
                @endforeach
            </div>
        </section>
    @else
        <div class="sd-clear"><i class="bi bi-check-circle-fill"></i><span><b style="color:var(--ef-ink)">All clear.</b> Nothing is waiting on you right now.@if($kitchen['waiting']) {{ $kitchen['waiting'] }} of your requests await approval. @endif</span></div>
    @endif

    <div class="sd-grid">
        <div class="sd-col">
            {{-- Material requests --}}
            <section class="sd-card" data-acc="req" aria-labelledby="sdReq" style="--o:1">
                <div class="sd-card-h"><h2 id="sdReq"><i class="bi bi-clipboard-check"></i> Material requests</h2>
                    <a class="sd-more" id="sdReqAll" href="{{ $tabs[$activeTab][3] }}">View all <i class="bi bi-arrow-right"></i></a></div>
                @if(count($tabs) > 1)
                    <div class="sd-tabs" role="tablist" aria-label="Request lists">
                        @foreach($tabs as $k => $t)
                            <button type="button" class="sd-tab" role="tab" id="sdTab-{{ $k }}" aria-controls="sdPane-{{ $k }}"
                                    aria-selected="{{ $k === $activeTab ? 'true' : 'false' }}" data-all="{{ $t[3] }}">{{ $t[0] }} @if($t[2])<b>{{ $t[2] }}</b>@endif</button>
                        @endforeach
                    </div>
                @endif
                <div class="sd-card-b">
                @foreach($tabs as $k => $t)
                    <div class="sd-pane" role="tabpanel" id="sdPane-{{ $k }}" aria-labelledby="sdTab-{{ $k }}" @if($k !== $activeTab) hidden @endif>
                        @forelse($t[1] as $r)
                            @php $w = $when($r); @endphp
                            <a class="sd-req" href="{{ route('inventory.material-requests.show', $r) }}">
                                <span class="sd-when {{ $w['cls'] }}"><b>{{ $w['day'] }}</b><span>{{ $w['sub'] }}</span></span>
                                <span class="sd-req-b"><span class="sd-req-t">{{ $r->preparation_name ?: $r->forLabel() }}</span>
                                    <span class="sd-req-s"><span class="sd-ref">{{ $r->reference() }}</span>@if($k !== 'mine') · {{ $r->requester?->name }}@endif @if($r->guest_count) · {{ $r->guest_count }} people @endif</span></span>
                                @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($r->status)])
                            </a>
                        @empty
                            <div class="sd-empty"><i class="bi bi-inbox"></i>
                                @if($k === 'approve') No requests waiting for approval.
                                @elseif($k === 'handover') Nothing to hand over.
                                @elseif($k === 'soon') Nothing needed today or tomorrow.
                                @else You have not requested anything yet. <a href="{{ route('inventory.material-requests.create') }}">Request materials</a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                @endforeach
                </div>
            </section>

            {{-- Recent stock movement --}}
            @if($isStoreSide)
                <section class="sd-card" data-acc="mv" aria-labelledby="sdAct" style="--o:5">
                    <div class="sd-card-h"><h2 id="sdAct"><i class="bi bi-clock-history"></i> Recent stock movement</h2>
                        @if($u->isAdmin() || $u->isManager())<a class="sd-more" href="{{ route('inventory.reports.index') }}">Reports <i class="bi bi-arrow-right"></i></a>@endif</div>
                    @if($activity->isNotEmpty())
                        <div class="sd-mv-h" aria-hidden="true"><span></span><span>Item</span><span>Movement</span><span class="--r">Qty</span><span class="--r">When</span></div>
                    @endif
                    @forelse($activity as $tx)
                        @php $sq = $tx->signedQuantity(); $dir = $sq > 0 ? '--add' : ($sq < 0 ? '--sub' : '--neutral'); @endphp
                        <div class="sd-mv">
                            <span class="sd-ico {{ $dir }}"><i class="bi {{ match($tx->type) { 'purchase_receipt', 'purchase' => 'bi-truck', 'material_issue', 'usage' => 'bi-box-arrow-up-right', 'material_return' => 'bi-arrow-return-left', 'wastage' => 'bi-trash3', 'reversal' => 'bi-arrow-counterclockwise', default => 'bi-sliders' } }}"></i></span>
                            <span class="sd-mv-item"><b>{{ $tx->item?->name ?? 'Item' }}</b>
                                <span class="--m">{{ $tx->typeLabel() }}@if($tx->reference_label) · {{ $tx->reference_label }}@endif · {{ $tx->created_at?->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span></span>
                            <span class="sd-mv-ref">{{ $tx->typeLabel() }}@if($tx->reference_label) · {{ $tx->reference_label }}@endif @if($tx->creator) · {{ $tx->creator->name }}@endif</span>
                            <span class="sd-delta {{ $dir }}">{{ $sq > 0 ? '+' : ($sq < 0 ? '−' : '') }}{{ $q(abs($sq)) }} {{ $tx->item?->unit }}</span>
                            <span class="sd-mv-when">{{ $tx->created_at?->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                        </div>
                    @empty
                        <div class="sd-empty"><i class="bi bi-clock-history"></i> No stock movement recorded yet.</div>
                    @endforelse
                </section>
            @endif
        </div>

        <div class="sd-col">
            @if($snapshot)
                {{-- Running low --}}
                <section class="sd-card" data-acc="low" aria-labelledby="sdLow" style="--o:2">
                    <div class="sd-card-h"><h2 id="sdLow"><i class="bi bi-exclamation-triangle"></i> Running low</h2>
                        <a class="sd-more" href="{{ route('inventory.stock', ['show' => 'low']) }}">View all <i class="bi bi-arrow-right"></i></a></div>
                    <div class="sd-card-b">
                    @forelse($critical as $it)
                        @php
                            $out = $it->current_stock <= 0;
                            $pct = $it->minimum_stock > 0 ? max(0, min(100, round($it->current_stock / $it->minimum_stock * 100))) : 0;
                            $inc = $incomingFor[$it->id] ?? 0;
                        @endphp
                        <div class="sd-item {{ $out ? '--out' : '--low' }}" data-critical>
                            <div class="sd-item-b">
                                <a class="sd-item-t" href="{{ $u->isAdmin() ? route('admin.inventory.items.show', $it->id) : route('inventory.stock', ['q' => $it->name]) }}">{{ $it->name }}</a>
                                <span class="sd-item-s">{{ $out ? 'Out of stock' : 'Low' }} · min {{ $q($it->minimum_stock) }} {{ $it->unit }}@if($inc > 0) · <span class="--in"><i class="bi bi-truck"></i> {{ $q($inc) }} arriving</span>@endif</span>
                                <div class="sd-meter"><span style="width:{{ $pct }}%"></span></div>
                            </div>
                            <div class="sd-qty"><b>{{ $q($it->current_stock) }}</b><small>{{ $it->unit }}</small></div>
                        </div>
                    @empty
                        <div class="sd-empty"><i class="bi bi-check-circle" style="color:var(--ef-emerald)"></i> No item is below its minimum stock level.
                            @if($u->isAdmin() && $snapshot['outTracked'] === 0 && $snapshot['low'] === 0)<br><small>Set a minimum on items in the <a href="{{ route('admin.inventory.items.index') }}">catalogue</a> to get alerts.</small>@endif</div>
                    @endforelse
                    </div>
                </section>
            @endif

            {{-- Arriving purchases --}}
            @if($arriving->isNotEmpty())
                <section class="sd-card" data-acc="arr" aria-labelledby="sdArr" style="--o:3">
                    <div class="sd-card-h"><h2 id="sdArr"><i class="bi bi-truck"></i> Arriving</h2>
                        <a class="sd-more" href="{{ route('inventory.purchases.index', ['stage' => 'awaiting']) }}">All <i class="bi bi-arrow-right"></i></a></div>
                    <div class="sd-card-b">
                    @foreach($arriving as $p)
                        <a class="sd-row" href="{{ route('inventory.purchases.show', $p) }}">
                            <span class="sd-ico --neutral"><i class="bi bi-bag"></i></span>
                            <span class="sd-req-b"><span class="sd-req-t">{{ $p->supplierLabel() }}</span>
                                <span class="sd-req-s"><span class="sd-ref">{{ $p->reference() }}</span> · {{ $p->items_count }} item{{ $p->items_count === 1 ? '' : 's' }} · {{ $p->purchase_date?->format('d M') }}</span></span>
                            @include('inventory._status', ['label' => $p->statusLabel(), 'tone' => \App\Models\Purchase::statusTone($p->status)])
                        </a>
                    @endforeach
                    </div>
                </section>
            @endif

            {{-- Shortcuts (only pages this role can open) --}}
            @php $links = array_values(array_filter([
                $isStoreSide ? ['bi-boxes', 'Stock', route('inventory.stock')] : null,
                ['bi-list-check', 'Requests', route('inventory.material-requests.index')],
                ['bi-bag', 'Purchases', route('inventory.purchases.index')],
                $u->isAdmin() ? ['bi-upc-scan', 'Supplier bills', route('admin.inventory.bills.index')] : null,
                $u->isAdmin() ? ['bi-cart-check', 'Purchase plans', route('admin.purchase-plans.index')] : null,
                $u->isAdmin() ? ['bi-journal-text', 'Item catalogue', route('admin.inventory.items.index')] : null,
                ($u->isAdmin() || $u->isManager()) ? ['bi-graph-up', 'Loss reports', route('inventory.reports.index')] : null,
            ])); @endphp
            <section class="sd-card" data-acc="go" aria-labelledby="sdGo" style="--o:6">
                <div class="sd-card-h"><h2 id="sdGo"><i class="bi bi-grid"></i> Go to</h2></div>
                <div class="sd-links">
                    @foreach($links as [$i, $l, $h])<a class="sd-link" href="{{ $h }}"><i class="bi {{ $i }}"></i><span>{{ $l }}</span></a>@endforeach
                </div>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // request tabs
    const all = document.getElementById('sdReqAll');
    document.querySelectorAll('.sd-tab').forEach(tab => tab.addEventListener('click', () => {
        document.querySelectorAll('.sd-tab').forEach(t => {
            const on = t === tab;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
        });
        if (all) all.href = tab.dataset.all;
    }));

    // find an item
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
            const main = document.createElement('div'); main.className = 'sd-found-main';
            const s = document.createElement('strong'); s.textContent = r.name + (r.name_ta ? ' · ' + r.name_ta : '');
            const sub = document.createElement('span'); sub.textContent = (r.category ? r.category + ' · ' : '') + 'stock unit ' + r.unit;
            main.append(s, sub);
            const acts = document.createElement('div'); acts.className = 'sd-found-acts';
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
