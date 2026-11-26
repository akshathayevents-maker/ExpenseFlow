<x-admin-layout :title="$mr->reference()">
@include('inventory._styles')
@php
    $u = auth()->user();
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');
@endphp
@push('styles')
<style>
/* Request detail — header + progress stepper + facts strip; each workflow section colour-coded (forms unchanged). */
.mrd{max-width:1240px;margin:0 auto;padding-bottom:90px}
.mrd-head{display:flex;flex-wrap:wrap;align-items:flex-start;gap:8px 14px;margin-bottom:14px}
.mrd-head .ic-back{flex-basis:100%;font-size:.82rem}
.mrd-tt{flex:1 1 260px;min-width:0}
.mrd-tt h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink);overflow-wrap:anywhere}
.mrd-tt p{margin:3px 0 0;font-size:.84rem;color:var(--ef-muted)}
.mrd-tt .mrd-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.78rem;padding:1px 7px;border-radius:6px;background:var(--ef-bg-subtle);color:var(--ef-ink);margin-right:6px}
.mrd-chips{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
@media (max-width:639.98px){.mrd-tt h1{font-size:1.2rem}}

/* stepper */
.mrd-steps{display:grid;grid-template-columns:repeat(var(--n),minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;margin-bottom:12px;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.mrd-step{position:relative;display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-right:1px solid var(--ef-border);min-width:0}
.mrd-step:last-child{border-right:0}
.mrd-dot{flex:0 0 auto;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;border:2px solid var(--ef-border);color:var(--ef-faint);background:var(--ef-surface)}
.mrd-step.--done .mrd-dot{background:var(--ef-emerald);border-color:var(--ef-emerald);color:#fff}
.mrd-step.--now{background:rgba(216,154,61,.08)}
.mrd-step.--now .mrd-dot{border-color:#d89a3d;color:#9a6413}
.mrd-step.--stop{background:rgba(192,57,43,.06)}
.mrd-step.--stop .mrd-dot{background:var(--ef-danger);border-color:var(--ef-danger);color:#fff}
.mrd-step b{display:block;font-size:.84rem;color:var(--ef-ink)}
.mrd-step span:not(.mrd-dot){display:block;font-size:.74rem;color:var(--ef-muted);overflow-wrap:anywhere}
.mrd-step>span:not(.mrd-dot){min-width:0}
.mrd-step.--now b{color:#7D5218}
.mrd-step::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:transparent}
.mrd-step.--done::after{background:var(--ef-emerald)}
.mrd-step.--now::after{background:#d89a3d}
@media (max-width:719.98px){
  .mrd-steps{grid-template-columns:1fr}
  .mrd-step{border-right:0;border-bottom:1px solid var(--ef-border);padding:9px 12px}
  .mrd-step:last-child{border-bottom:0}
  .mrd-step::after{left:0;top:0;bottom:0;right:auto;width:3px;height:auto}
}

/* facts */
.mrd-facts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;margin-bottom:14px}
@media (min-width:900px){.mrd-facts{grid-template-columns:repeat(var(--n),minmax(0,1fr))}}
.mrd-fact{padding:11px 14px;border-right:1px solid var(--ef-border);border-bottom:1px solid var(--ef-border);min-width:0}
@media (min-width:900px){.mrd-fact{border-bottom:0}.mrd-fact:last-child{border-right:0}}
@media (max-width:899.98px){.mrd-fact:nth-child(2n){border-right:0}}
.mrd-fact small{display:flex;align-items:center;gap:5px;font-size:.68rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:var(--ef-muted)}
.mrd-fact small .bi{color:var(--ef-gold)}
.mrd-fact strong{display:block;margin-top:3px;font-size:.95rem;color:var(--ef-ink);overflow-wrap:anywhere}
.mrd-fact strong a{color:inherit}
.mrd-fact.--wide{grid-column:1/-1}

/* section colour coding (targets the existing forms; behaviour unchanged) */
.mrd .ic-card{border-radius:14px;border-top:3px solid var(--acc,var(--ef-border));box-shadow:0 1px 2px rgba(19,17,16,.04)}
.mrd .ic-card>h2,.mrd details.ic-card>summary{display:flex;align-items:center;gap:8px;margin:-16px -16px 14px;padding:12px 16px;background:var(--tint,transparent);border-bottom:1px solid var(--ef-border);border-radius:11px 11px 0 0;font-size:.98rem;font-weight:800}
.mrd details.ic-card:not([open])>summary{margin-bottom:-16px;border-bottom:0;border-radius:11px}
.mrd details.ic-card>summary{cursor:pointer}
.mrd .mrd-materials{--acc:#3b6ea5;--tint:rgba(59,110,165,.07)}
.mrd .mrd-details{--acc:#8a827a;--tint:rgba(100,82,42,.05)}
.mrd form[action$="/approve"].ic-card{--acc:var(--ef-danger);--tint:rgba(192,57,43,.06)}
.mrd form[action$="/reject"].ic-card{--acc:var(--ef-danger)}
.mrd form[action$="/handover"].ic-card{--acc:#3b6ea5;--tint:rgba(59,110,165,.07)}
.mrd details.ic-card{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.06)}
.mrd form[action$="/reconcile"].ic-card{--acc:#d89a3d;--tint:rgba(216,154,61,.09)}
.mrd form[action$="/review"].ic-card{--acc:#6b5bb5}
.mrd .ic-table th{background:var(--ef-bg-subtle)}
</style>
@endpush

@php
    $prepList = $mr->preparationList();
    $stopped = in_array($mr->status, ['rejected', 'cancelled'], true);
    $order = ['draft' => 0, 'submitted' => 1, 'approved' => 2, 'issued' => 3, 'reconciled' => 4];
    $at = $order[$mr->status] ?? 1;
    $fmt = fn ($d) => $d?->timezone('Asia/Kolkata')->format('d M, h:i A');
    $steps = [
        ['Requested', $mr->requester?->name, $fmt($mr->submitted_at), 1],
        ['Approved', $mr->approver?->name, $fmt($mr->approved_at), 2],
        ['Handed over', $mr->handedOverBy ? $mr->handedOverBy->name.' → '.($mr->collector?->name ?? $mr->collected_by_name) : null, $fmt($mr->handed_over_at), 3],
        ['Usage recorded', $mr->reconciler?->name, $fmt($mr->reconciled_at), 4],
    ];
    if ($mr->reconciliation_status === 'pending_review' || $mr->reviewed_at) {
        $steps[] = ['Reviewed', $mr->reviewer?->name, $fmt($mr->reviewed_at), 5];
        $at = $mr->reviewed_at ? 5 : $at;
    }
    $for = $mr->forLabel();
    $forLink = $mr->source === 'meal' && $mr->mealEntry ? route('meal-register.entries.show', $mr->mealEntry)
        : ($mr->source === 'hall' && $mr->hallBooking && ($u->isAdmin() || $u->isManager()) ? route('hall.bookings.show', $mr->hallBooking) : null);
    $lines = $mr->items->where('line_status', '!=', 'rejected')->count();
    $showCost = in_array($mr->status, ['approved', 'issued', 'reconciled']) && ($u->isAdmin() || $u->isManager());
    $facts = array_values(array_filter([
        ['bi-geo-alt', 'For', $for, $forLink],
        ['bi-calendar3', 'Needed', $mr->required_date->format('D d M Y').($mr->required_time ? ', '.\Carbon\Carbon::parse($mr->required_time)->format('h:i A') : ''), null],
        count($prepList) > 1 ? null : ['bi-people', 'People', $mr->guest_count ?: '—', null],
        ['bi-box', 'Materials', $lines.' '.\Illuminate\Support\Str::plural('item', $lines), null],
        $showCost ? ['bi-currency-rupee', 'Material cost', '₹'.number_format($mr->issuedCost(), 2).($mr->guest_count ? ' · ₹'.number_format($mr->issuedCost() / max(1, $mr->guest_count), 2).' per person' : ''), null] : null,
    ]));
@endphp

<div class="mrd">
    <header class="mrd-head">
        <a class="ic-back" href="{{ route('inventory.material-requests.index') }}"><i class="bi bi-arrow-left"></i> Material requests</a>
        <div class="mrd-tt">
            <h1>{{ $mr->preparation_name ?: $for }}</h1>
            <p><span class="mrd-ref">{{ $mr->reference() }}</span>{{ \App\Models\MaterialRequest::DEPARTMENTS[$mr->department] ?? '' }}</p>
        </div>
        <div class="mrd-chips">
            @include('inventory._status', ['label' => $mr->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($mr->status)])
            @if($mr->reconciliation_status === 'pending_review')
                <span class="ic-chip ic-chip--warn"><i class="bi bi-eye"></i> Usage needs review</span>
            @endif
        </div>
    </header>

    @if($errors->any())
        <div class="ic-err"><ul>@foreach(collect($errors->all())->unique() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- Progress --}}
    <ol class="mrd-steps" style="--n:{{ count($steps) }};list-style:none;padding:0" aria-label="Progress">
        @foreach($steps as $i => [$label, $who, $when, $n])
            @php
                $state = $stopped && $n === $at + 1 ? '--stop' : ($n <= $at && $mr->status !== 'draft' ? '--done' : ($n === $at + 1 && ! $stopped ? '--now' : ''));
            @endphp
            <li class="mrd-step {{ $state }}" @if($state === '--now') aria-current="step" @endif>
                <span class="mrd-dot">@if($state === '--done')<i class="bi bi-check-lg"></i>@elseif($state === '--stop')<i class="bi bi-x-lg"></i>@else{{ $i + 1 }}@endif</span>
                <span>
                    <b>{{ $state === '--stop' ? ucfirst($mr->status) : $label }}</b>
                    @if($state === '--stop')
                        <span>{{ $mr->canceller?->name ?? $mr->approver?->name }} {{ $fmt($mr->cancelled_at) }}</span>
                    @elseif($who || $when)
                        <span>{{ $who }}@if($when) · {{ $when }}@endif</span>
                    @elseif($state === '--now')
                        <span>Waiting</span>
                    @endif
                </span>
            </li>
        @endforeach
    </ol>

    {{-- Key facts --}}
    <div class="mrd-facts" style="--n:{{ count($facts) }}">
        @foreach($facts as [$icon, $label, $value, $link])
            <div class="mrd-fact"><small><i class="bi {{ $icon }}"></i> {{ $label }}</small>
                <strong>@if($link)<a href="{{ $link }}">{{ $value }}</a>@else{{ $value }}@endif</strong></div>
        @endforeach
        @if(count($prepList) > 1)
            <div class="mrd-fact --wide"><small><i class="bi bi-list-ul"></i> Preparations</small>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:5px">
                    @foreach($prepList as $p)
                        <span class="ic-chip ic-chip--neutral" style="font-size:.82rem">{{ $p['name'] ?: 'Preparation' }}@if($p['people']) · <i class="bi bi-people"></i> {{ $p['people'] }}@endif</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @php
        $hasDetails = $mr->approval_remarks || $mr->cancel_reason || $mr->notes || $mr->revisionOf || $mr->revisions->isNotEmpty()
            || ($mr->menu_snapshot && (! empty($mr->menu_snapshot['items']) || ! empty($mr->menu_snapshot['plans'] ?? [])));
    @endphp
    @if($hasDetails)
    <div class="ic-card mrd-details">
        <h2><i class="bi bi-info-circle"></i> Notes &amp; history</h2>
        @if($mr->approval_remarks)<p class="ic-note" style="margin:0 0 8px"><strong>Approver:</strong> {{ $mr->approval_remarks }}</p>@endif
        @if($mr->cancel_reason)<div class="ic-err" style="margin:0 0 8px">Cancelled by {{ $mr->canceller?->name }}: {{ $mr->cancel_reason }}</div>@endif
        @if($mr->notes)<p class="ic-note" style="margin:0 0 8px;white-space:pre-line">{{ $mr->notes }}</p>@endif
        @if($mr->revisionOf)<p class="ic-note" style="margin:0 0 8px">Revision of <a href="{{ route('inventory.material-requests.show', $mr->revisionOf) }}">{{ $mr->revisionOf->reference() }}</a></p>@endif
        @if($mr->revisions->isNotEmpty())
            <p class="ic-note" style="margin:0 0 8px">Revisions: @foreach($mr->revisions as $rv)<a href="{{ route('inventory.material-requests.show', $rv) }}">{{ $rv->reference() }}</a> ({{ $rv->statusLabel() }}){{ $loop->last ? '' : ', ' }}@endforeach</p>
        @endif
        @if($mr->menu_snapshot && ! empty($mr->menu_snapshot['items']) || ! empty($mr->menu_snapshot['plans'] ?? []))
            <details><summary class="ic-note" style="cursor:pointer;font-weight:700">Final menu at time of request</summary>
                <div class="ic-note" style="margin-top:6px">
                    @foreach($mr->menu_snapshot['plans'] ?? [] as $p) Plan: <strong>{{ $p['plan'] }}</strong> ({{ $p['guests'] }})@if(! $loop->last), @endif @endforeach
                    <ul style="margin:6px 0 0;padding-left:18px">
                        @foreach($mr->menu_snapshot['items'] ?? [] as $mi)
                            <li style="{{ ! empty($mi['removed']) ? 'text-decoration:line-through' : '' }}">{{ $mi['name'] }} @if(($mi['change'] ?? '') && $mi['change'] !== 'selected' && $mi['change'] !== 'plan')<span class="ic-chip ic-chip--info">{{ $mi['change'] }}</span>@endif @if(! empty($mi['note']))<span>· {{ $mi['note'] }}</span>@endif</li>
                        @endforeach
                    </ul>
                </div>
            </details>
        @endif
    </div>
    @endif

    @php
        // stock check per line (only meaningful before approval deducts stock)
        $checkStock = in_array($mr->status, ['draft', 'submitted'], true);
        $avail = $mr->items->mapWithKeys(function ($i) use ($incoming) {
            $need = (float) $i->requested_base_quantity;
            $have = max(0, (float) $i->item->current_stock);
            $state = $have <= 0 ? 'out' : ($have + 0.0005 < $need ? 'short' : 'ok');
            $f = $i->requested_base_quantity > 0 ? $i->requested_quantity / $i->requested_base_quantity : 1;   // base -> requested unit
            return [$i->id => [
                'state' => $state, 'need' => $need, 'have' => $have, 'gap' => max(0, $need - $have),
                'pct' => $need > 0 ? (int) min(100, round($have / $need * 100)) : 100,
                'fit' => round(min($need, $have) * $f, 3), 'incoming' => (float) ($incoming[$i->inventory_item_id] ?? 0),
            ]];
        });
        $shortLines = $checkStock ? $mr->items->filter(fn ($i) => $avail[$i->id]['state'] !== 'ok') : collect();
    @endphp
    @push('styles')
    <style>
    .sa-banner{display:flex;gap:12px;align-items:flex-start;padding:12px 14px;margin-bottom:12px;border-radius:14px;border:1px solid rgba(192,57,43,.28);background:rgba(192,57,43,.06)}
    .sa-banner.--warn{border-color:rgba(216,154,61,.45);background:rgba(216,154,61,.09)}
    .sa-banner.--ok{border-color:rgba(46,125,90,.3);background:rgba(46,125,90,.06)}
    .sa-banner>.bi{font-size:1.35rem;color:var(--ef-danger);line-height:1}
    .sa-banner.--warn>.bi{color:var(--ef-amber)}
    .sa-banner.--ok>.bi{color:var(--ef-emerald)}
    .sa-banner strong{display:block;font-size:.95rem;color:var(--ef-ink)}
    .sa-banner ul{margin:6px 0 0;padding-left:18px;font-size:.86rem;color:var(--ef-ink)}
    .sa-banner li{margin:2px 0}
    .sa-banner .sa-in{color:#2c5784;font-weight:650}
    .sa-banner .ic-btn{margin-top:10px}
    .sa-row[data-avail=out]>td:first-child{box-shadow:inset 4px 0 0 var(--ef-danger)}
    .sa-row[data-avail=short]>td:first-child{box-shadow:inset 4px 0 0 var(--ef-amber)}
    .sa-row[data-avail=out]{background:rgba(192,57,43,.045)}
    .sa-row[data-avail=short]{background:rgba(216,154,61,.06)}
    .sa-chip{display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:2px 9px;border-radius:999px;font-size:.74rem;font-weight:750;white-space:nowrap}
    .sa-chip.--ok{background:rgba(46,125,90,.1);color:#24684a}
    .sa-chip.--short{background:rgba(216,154,61,.16);color:#7D5218}
    .sa-chip.--out{background:rgba(192,57,43,.1);color:#9B2C2C}
    .sa-chip.--in{background:rgba(59,110,165,.12);color:#2c5784}
    .sa-stock{display:inline-flex;flex-direction:column;align-items:flex-end;gap:4px;min-width:90px}
    .sa-bar{width:90px;height:6px;border-radius:99px;background:rgba(20,20,18,.08);overflow:hidden}
    .sa-bar i{display:block;height:100%;background:var(--ef-emerald);border-radius:inherit}
    .sa-row[data-avail=short] .sa-bar i{background:var(--ef-amber)}
    .sa-row[data-avail=out] .sa-stock strong{color:var(--ef-danger)}
    .sa-row[data-avail=short] .sa-stock strong{color:#9a6413}
    .sa-stock small{font-size:.74rem;color:var(--ef-muted)}
    .us-line{padding:12px 0;border-bottom:1px solid var(--ef-border)}
    .us-top{display:flex;justify-content:space-between;align-items:baseline;gap:8px;flex-wrap:wrap}
    .us-top strong{font-size:1rem}
    .us-net{font-size:.86rem;color:var(--ef-muted)}
    .us-net b{color:var(--ef-ink)}
    .us-net small{font-size:.76rem}
    .us-grid{display:grid;grid-template-columns:1fr 1fr 0.8fr;gap:8px;margin-top:8px}
    .us-f{display:flex;flex-direction:column;gap:4px;min-width:0}
    .us-f>span,.us-cap{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:var(--ef-muted)}
    .us-f .ic-input{min-height:46px;font-size:16px}
    .us-left{display:flex;align-items:center;min-height:46px;padding:0 10px;border-radius:10px;background:var(--ef-bg-subtle);font-weight:800;font-variant-numeric:tabular-nums}
    .us-left.--warn{color:#9a6413;background:rgba(216,154,61,.14)}
    .us-left.--bad{color:var(--ef-danger);background:rgba(192,57,43,.1)}
    .us-bar{display:flex;height:8px;border-radius:99px;overflow:hidden;background:rgba(20,20,18,.07);margin-top:10px}
    .us-bar i{display:block;height:100%;transition:width .15s}
    .us-bar .--c{background:var(--ef-emerald)}
    .us-bar .--w{background:var(--ef-danger)}
    .us-bar .--u{background:var(--ef-amber)}
    .us-msg{font-size:.82rem;font-weight:600;margin-top:6px}
    .us-msg .--bad{color:var(--ef-danger)}
    .us-msg .--warn{color:#7D5218}
    .us-why{margin-top:10px;display:flex;flex-direction:column;gap:6px}
    .us-why[hidden]{display:none}
    .us-chips{display:flex;flex-wrap:wrap;gap:6px}
    .us-chip{min-height:36px;padding:4px 12px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:.82rem;font-weight:650;cursor:pointer}
    .us-chip.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
    .us-err{color:var(--ef-danger);font-size:.84rem;font-weight:650;margin-top:6px}
    @media (max-width:767.98px){.sa-stock{align-items:flex-end}}
    </style>
    @endpush

    @if($checkStock && $mr->items->isNotEmpty())
        @php $outN = $shortLines->filter(fn ($i) => $avail[$i->id]['state'] === 'out')->count(); @endphp
        @if($shortLines->isEmpty())
            <div class="sa-banner --ok" role="status"><i class="bi bi-check-circle-fill"></i>
                <div><strong>All {{ $mr->items->count() }} items are in store.</strong></div></div>
        @else
            <div class="sa-banner {{ $outN ? '' : '--warn' }}" role="alert"><i class="bi bi-exclamation-triangle-fill"></i>
                <div style="flex:1;min-width:0">
                    <strong>{{ $shortLines->count() }} of {{ $mr->items->count() }} {{ \Illuminate\Support\Str::plural('item', $mr->items->count()) }} not available in full</strong>
                    <ul>
                        @foreach($shortLines as $i)
                            @php $a = $avail[$i->id]; $un = $i->item->unit; @endphp
                            <li><b>{{ $i->item->name }}</b> —
                                @if($a['state'] === 'out') out of stock, need {{ $q($a['need']) }} {{ $un }}
                                @else only {{ $q($a['have']) }} of {{ $q($a['need']) }} {{ $un }} (short {{ $q($a['gap']) }})@endif
                                @if($a['incoming'] > 0)<span class="sa-in"> · {{ $q($a['incoming']) }} {{ $un }} arriving</span>@endif
                            </li>
                        @endforeach
                    </ul>
                    @can('approve', $mr)
                        <button type="button" class="ic-btn ic-btn--sm" data-fit-stock><i class="bi bi-magic"></i> Approve only what's in store</button>
                    @else
                        <div class="ic-note" style="margin:6px 0 0">The store can approve only what is available; the rest needs a purchase.</div>
                    @endcan
                </div>
            </div>
        @endif
    @endif

    {{-- ── Admin approval ───────────────────────────────────────── --}}
    @can('approve', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.approve', $mr) }}" class="ic-card">
            @csrf
            <h2>Approve materials</h2>
            @if(collect($expected)->isNotEmpty() && $mr->items->contains(fn ($i) => isset($expected[$i->inventory_item_id]) && $i->requested_base_quantity > $expected[$i->inventory_item_id]['qty'] * 1.15))
                <div class="sa-banner --warn" style="margin:0 0 10px"><i class="bi bi-speedometer2"></i>
                    <div style="flex:1;min-width:0"><strong>Some quantities are above the recipe standard / usual amount</strong>
                        <div class="ic-note" style="margin:2px 0 0">Approving only what the dish needs is the easiest way to cut wastage.</div>
                        <button type="button" class="ic-btn ic-btn--sm" data-fit-std><i class="bi bi-sliders2"></i> Approve at standard</button></div></div>
            @endif
            <p class="ic-note">Change a quantity to approve partly. Enter 0 to reject a line. Approving deducts stock and issues the materials to {{ $mr->requester?->name ?? 'the requester' }}, who is responsible for them (returns and usage are reported against them).</p>
            <table class="ic-table">
                <thead><tr><th>Material</th><th class="r">Requested</th><th class="r">In store</th><th class="r">Already issued for this</th><th>Approve</th><th>Remark</th></tr></thead>
                <tbody>
                @foreach($mr->items as $i)
                    @php
                        $a = $avail[$i->id];
                        $ex = $expected[$i->inventory_item_id] ?? null;
                        $f = $i->requested_base_quantity > 0 ? $i->requested_quantity / $i->requested_base_quantity : 1;
                        $over = $ex && $ex['qty'] > 0 ? round(($i->requested_base_quantity / $ex['qty'] - 1) * 100) : null;
                    @endphp
                    <tr class="sa-row" data-avail="{{ $a['state'] }}" data-fit="{{ $a['fit'] }}" data-line="{{ $i->id }}"
                        @if($ex) data-std="{{ round($ex['qty'] * $f, 3) }}" @endif @if($over !== null && $over > 15) data-over @endif>
                        <td data-l="Material"><strong>{{ $i->item->name }}</strong>
                            @include('inventory.material-requests._avail-chip', ['a' => $a, 'unit' => $i->item->unit])</td>
                        <td data-l="Requested" class="r">{{ $q($i->requested_quantity) }} {{ $i->requested_unit }}
                            @if($ex)
                                <div class="ic-note" title="{{ $ex['label'] }}">{{ $ex['source'] === 'standard' ? 'Standard' : 'Usual' }}: {{ $q($ex['qty']) }} {{ $i->item->unit }}</div>
                                @if($over > 15)<span class="sa-chip --short" title="More than the {{ $ex['source'] === 'standard' ? 'recipe standard' : 'usual amount' }}">+{{ $over }}% over</span>@endif
                            @endif</td>
                        <td data-l="In store" class="r"><span class="sa-stock"><strong>{{ $q($i->item->current_stock) }} {{ $i->item->unit }}</strong>
                            <span class="sa-bar" aria-hidden="true"><i style="width:{{ $a['pct'] }}%"></i></span></span></td>
                        <td data-l="Issued before" class="r">{{ isset($previous[$i->inventory_item_id]) ? $q($previous[$i->inventory_item_id]) . ' ' . $i->item->unit : '—' }}</td>
                        <td data-l="Approve"><div style="display:flex;gap:6px;align-items:center">
                            <input class="ic-input" style="max-width:120px" type="number" min="0" step="0.001" data-approve-qty name="lines[{{ $i->id }}][quantity]" value="{{ old("lines.{$i->id}.quantity", $q($i->requested_quantity)) }}" inputmode="decimal">
                            <span class="ic-note">{{ $i->requested_unit }}</span></div></td>
                        <td data-l="Remark"><input class="ic-input" data-approve-remark name="lines[{{ $i->id }}][remark]" maxlength="300" value="{{ old("lines.{$i->id}.remark") }}"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
                <input class="ic-input" name="remarks" placeholder="Remarks (optional)" maxlength="500" style="flex:1;min-width:220px">
                <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-check2-circle"></i> Approve</button>
            </div>
        </form>
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.reject', $mr) }}" class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap">
            @csrf
            <input class="ic-input" name="reason" placeholder="Reason for rejecting the whole request" required minlength="3" maxlength="500" style="flex:1;min-width:220px">
            <button class="ic-btn ic-btn--danger" type="submit">Reject request</button>
        </form>
    @else
        {{-- ── Lines (all other states) ───────────────────────────── --}}
        <div class="ic-card mrd-materials">
            <h2><i class="bi bi-box-seam"></i> Materials</h2>
            <table class="ic-table">
                @php
                    // only show the stages that have happened — no columns of dashes
                    $colFlow = ! in_array($mr->status, ['draft', 'submitted'], true);
                    $colIssued = $mr->items->contains(fn ($i) => $i->issued_base_quantity !== null);
                    $colReturn = $mr->items->contains(fn ($i) => $i->returned_base_quantity > 0);
                    $colUsage = $mr->items->contains(fn ($i) => $i->consumed_base_quantity !== null);
                @endphp
                <thead><tr><th>Material</th><th class="r">Requested</th>@if($colFlow)<th class="r">Approved</th>@endif @if($colIssued)<th class="r">Handed over</th>@endif @if($colReturn)<th class="r">Returned</th>@endif @if($colUsage)<th class="r">Used</th><th class="r">Wasted</th><th class="r">Unexplained</th>@endif</tr></thead>
                <tbody>
                @foreach($mr->items as $i)
                    @php $unit = $i->item->unit; @endphp
                    <tr class="sa-row" @if($checkStock) data-avail="{{ $avail[$i->id]['state'] }}" @endif style="{{ $i->line_status === 'rejected' ? 'opacity:.55' : '' }}">
                        <td data-l="Material"><strong>{{ $i->item->name }}</strong>
                            @if($checkStock)@include('inventory.material-requests._avail-chip', ['a' => $avail[$i->id], 'unit' => $i->item->unit])@endif
                            @if($i->line_status === 'rejected')<span class="ic-chip ic-chip--bad">rejected</span>@elseif($i->line_status === 'reduced')<span class="ic-chip ic-chip--warn">reduced</span>@endif
                            @if($i->admin_remark)<div class="ic-note">{{ $i->admin_remark }}</div>@endif
                            @if($i->wastage_reason)<div class="ic-note">Waste: {{ $i->wastage_reason }}</div>@endif
                            @if($i->variance_reason)<div class="ic-note">Difference: {{ $i->variance_reason }}</div>@endif
                        </td>
                        <td data-l="Requested" class="r">{{ $q($i->requested_quantity) }} {{ $i->requested_unit }}</td>
                        @if($colFlow)<td data-l="Approved" class="r">{{ $i->approved_base_quantity !== null ? $q($i->approved_base_quantity) . ' ' . $unit : '—' }}</td>@endif
                        @if($colIssued)<td data-l="Handed over" class="r">{{ $i->issued_base_quantity !== null ? $q($i->issued_base_quantity) . ' ' . $unit : '—' }}</td>@endif
                        @if($colReturn)<td data-l="Returned" class="r">{{ $i->returned_base_quantity > 0 ? $q($i->returned_base_quantity) . ' ' . $unit : '—' }}</td>@endif
                        @if($colUsage)
                        <td data-l="Used" class="r">{{ $i->consumed_base_quantity !== null ? $q($i->consumed_base_quantity) : '—' }}</td>
                        <td data-l="Wasted" class="r" style="{{ $i->wasted_base_quantity > 0 ? 'color:#9B2C2C;font-weight:700' : '' }}">{{ $i->wasted_base_quantity !== null ? $q($i->wasted_base_quantity) : '—' }}</td>
                        <td data-l="Unexplained" class="r" style="{{ $i->unexplained_base_quantity > 0 ? 'color:#9B2C2C;font-weight:700' : '' }}">{{ $i->unexplained_base_quantity !== null ? $q($i->unexplained_base_quantity) : '—' }}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endcan

    {{-- ── Storekeeper handover ──────────────────────────────────── --}}
    @can('handover', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.handover', $mr) }}" class="ic-card">
            @csrf
            <h2>Hand over to kitchen</h2>
            <p class="ic-note">Stock was already deducted at approval. If you give less than approved, change the number — the difference goes back into stock automatically.</p>
            @foreach($mr->items->where('line_status', '!=', 'rejected') as $i)
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:8px 0;border-bottom:1px solid var(--ef-border)">
                    <strong style="flex:1;min-width:140px">{{ $i->item->name }}</strong>
                    <input class="ic-input" style="max-width:130px" type="number" min="0" max="{{ $i->approved_base_quantity }}" step="0.001" name="issued[{{ $i->id }}]" value="{{ old("issued.{$i->id}", $q($i->approved_base_quantity)) }}" inputmode="decimal">
                    <span class="ic-note">{{ $i->item->unit }} (approved {{ $q($i->approved_base_quantity) }})</span>
                </div>
            @endforeach
            <div class="ic-fields" style="margin-top:12px">
                <div>
                    <label class="ic-label" for="collected_by">Collected by</label>
                    <select class="ic-select" id="collected_by" name="collected_by">
                        <option value="">— not in list —</option>
                        @foreach($staff as $s)<option value="{{ $s->id }}" @selected($s->id === $mr->requested_by)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="ic-label" for="collected_by_name">Or name</label>
                    <input class="ic-input" id="collected_by_name" name="collected_by_name" maxlength="120">
                </div>
                <div>
                    <label class="ic-label" for="ho_notes">Notes</label>
                    <input class="ic-input" id="ho_notes" name="notes" maxlength="500">
                </div>
            </div>
            <button class="ic-btn ic-btn--gold" type="submit" style="margin-top:12px;width:100%"><i class="bi bi-box-arrow-up-right"></i> Confirm handover</button>
        </form>
    @endcan

    {{-- ── Returns ───────────────────────────────────────────────── --}}
    @can('recordReturn', $mr)
        <details class="ic-card" @if($errors->has('returns') || $errors->has('returns.*')) open @endif>
            <summary style="cursor:pointer;font-weight:750">Record materials returned to store</summary>
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.returns', $mr) }}" style="margin-top:10px">
                @csrf
                @foreach($mr->items->where('line_status', '!=', 'rejected') as $i)
                    @continue($i->netIssued() <= 0)
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:6px 0">
                        <strong style="flex:1;min-width:140px">{{ $i->item->name }}</strong>
                        <input class="ic-input" style="max-width:130px" type="number" min="0" max="{{ $i->netIssued() }}" step="0.001" name="returns[{{ $i->id }}]" inputmode="decimal">
                        <span class="ic-note">{{ $i->item->unit }} (out: {{ $q($i->netIssued()) }})</span>
                    </div>
                @endforeach
                <input class="ic-input" name="note" placeholder="Note" maxlength="300" style="margin-top:8px">
                <button class="ic-btn ic-btn--dark" type="submit" style="margin-top:10px"><i class="bi bi-arrow-return-left"></i> Save return</button>
            </form>
        </details>
    @endcan

    {{-- ── Usage reconciliation ─────────────────────────────────── --}}
    @can('reconcile', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.reconcile', $mr) }}" class="ic-card">
            @csrf
            <h2>Record actual usage</h2>
            <p class="ic-note">Enter what was <b>wasted</b> — it is taken off "used" automatically. Anything still left over should be returned to the store first (above).</p>
            @foreach($mr->items->where('line_status', '!=', 'rejected') as $i)
                @php
                    $net = $i->netIssued(); $un = $i->item->unit;
                    $ex = $expected[$i->inventory_item_id] ?? null;
                @endphp
                <div class="us-line" data-recon data-net="{{ $net }}" @if($ex) data-std="{{ $ex['qty'] }}" data-std-label="{{ $ex['source'] === 'standard' ? 'standard' : 'usual' }}" @endif>
                    <div class="us-top">
                        <strong>{{ $i->item->name }}</strong>
                        <span class="us-net">Had <b>{{ $q($net) }} {{ $un }}</b>
                            <small>({{ $q($i->issued_base_quantity) }} given{{ $i->returned_base_quantity > 0 ? ' − ' . $q($i->returned_base_quantity) . ' returned' : '' }})</small></span>
                    </div>
                    <div class="us-grid">
                        <label class="us-f"><span>Used</span>
                            <input class="ic-input" data-c type="number" min="0" step="0.001" max="{{ $net }}" name="lines[{{ $i->id }}][consumed]" value="{{ old("lines.{$i->id}.consumed", $q($net)) }}" inputmode="decimal"></label>
                        <label class="us-f"><span>Wasted</span>
                            <input class="ic-input" data-w type="number" min="0" step="0.001" max="{{ $net }}" name="lines[{{ $i->id }}][wasted]" value="{{ old("lines.{$i->id}.wasted", 0) }}" inputmode="decimal"></label>
                        <div class="us-f"><span>Left over</span><output class="us-left" data-u>0</output></div>
                    </div>
                    <div class="us-bar" aria-hidden="true"><i class="--c" data-bc></i><i class="--w" data-bw></i><i class="--u" data-bu></i></div>
                    <div class="us-msg" data-msg></div>
                    <div class="us-why" data-why-wrap hidden>
                        <span class="us-cap">Why wasted?</span>
                        <div class="us-chips">
                            @foreach(['Burnt', 'Spoiled / expired', 'Spilled', 'Cooked extra, thrown', 'Guests fewer than planned', 'Quality bad'] as $c)
                                <button type="button" class="us-chip" data-why="{{ $c }}">{{ $c }}</button>
                            @endforeach
                        </div>
                        <input class="ic-input" name="lines[{{ $i->id }}][wastage_reason]" data-why-in maxlength="300" placeholder="Or type the reason" value="{{ old("lines.{$i->id}.wastage_reason") }}">
                    </div>
                    <div class="us-why" data-left-wrap hidden>
                        <span class="us-cap">Left over — not used or wasted. Was it returned to the store?</span>
                        <input class="ic-input" name="lines[{{ $i->id }}][variance_reason]" data-left-in maxlength="300" placeholder="Say where it is (return it first if it is still usable)" value="{{ old("lines.{$i->id}.variance_reason") }}">
                    </div>
                    @error("lines.{$i->id}")<div class="us-err">{{ $message }}</div>@enderror
                </div>
            @endforeach
            <button class="ic-btn ic-btn--gold" type="submit" style="margin-top:12px;width:100%"><i class="bi bi-clipboard-check"></i> Save usage</button>
        </form>
        @push('scripts')
        <script>
        (() => {
            const r3 = n => Math.round(n * 1000) / 1000;
            document.querySelectorAll('[data-recon]').forEach(b => {
                const net = parseFloat(b.dataset.net) || 0, std = parseFloat(b.dataset.std) || 0;
                const c = b.querySelector('[data-c]'), w = b.querySelector('[data-w]'), u = b.querySelector('[data-u]');
                const msg = b.querySelector('[data-msg]'), whyWrap = b.querySelector('[data-why-wrap]'), whyIn = b.querySelector('[data-why-in]');
                const leftWrap = b.querySelector('[data-left-wrap]'), leftIn = b.querySelector('[data-left-in]');
                let usedTouched = c.value !== '' && r3(parseFloat(c.value)) !== r3(net);   // old input after an error
                const bar = (el, v) => el.style.width = (net > 0 ? Math.max(0, Math.min(100, v / net * 100)) : 0) + '%';
                function update() {
                    let used = parseFloat(c.value) || 0, waste = parseFloat(w.value) || 0;
                    if (!usedTouched) { used = Math.max(0, r3(net - waste)); c.value = used; }   // wasted comes off "used"
                    const left = r3(net - used - waste);
                    u.textContent = left === 0 ? '0' : left;
                    u.className = 'us-left' + (left < 0 ? ' --bad' : left > 0 ? ' --warn' : '');
                    bar(b.querySelector('[data-bc]'), used); bar(b.querySelector('[data-bw]'), waste); bar(b.querySelector('[data-bu]'), Math.max(0, left));
                    const notes = [];
                    if (left < 0) notes.push(['bad', 'Used + wasted is ' + r3(-left) + ' more than you had. Lower one of them.']);
                    if (std > 0 && used + waste + Math.max(0, left) > std * 1.15) notes.push(['warn', 'About ' + Math.round(((used + waste + Math.max(0, left)) / std - 1) * 100) + '% more than the ' + b.dataset.stdLabel + ' amount (' + r3(std) + '). An admin will review it.']);
                    msg.innerHTML = notes.map(n => '<div class="--' + n[0] + '">' + n[1] + '</div>').join('');
                    whyWrap.hidden = !(waste > 0); whyIn.required = waste > 0;
                    leftWrap.hidden = !(left > 0); leftIn.required = left > 0;
                    c.setCustomValidity(left < 0 ? 'Used + wasted is more than you had' : '');
                }
                c.addEventListener('input', () => { usedTouched = true; update(); });
                w.addEventListener('input', update);
                b.querySelectorAll('[data-why]').forEach(x => x.addEventListener('click', () => {
                    whyIn.value = x.dataset.why;
                    b.querySelectorAll('[data-why]').forEach(y => y.classList.toggle('--on', y === x));
                }));
                update();
            });
        })();
        </script>
        @endpush
    @endcan

    @can('review', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.review', $mr) }}" class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap">
            @csrf
            <strong style="width:100%">Review high wastage / unexplained difference</strong>
            <input class="ic-input" name="remarks" required minlength="3" maxlength="500" placeholder="Your finding (e.g. checked with cook, burnt batch)" style="flex:1;min-width:220px">
            <button class="ic-btn ic-btn--gold" type="submit">Mark reviewed</button>
        </form>
    @endcan
    @if($mr->review_remarks)
        <div class="ic-card ic-note">Reviewed by {{ $mr->reviewer?->name }} {{ $mr->reviewed_at?->format('d M') }}: {{ $mr->review_remarks }}</div>
    @endif

    {{-- ── Other actions ─────────────────────────────────────────── --}}
    <div class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
        @can('update', $mr)
            <a class="ic-btn" href="{{ route('inventory.material-requests.edit', $mr) }}"><i class="bi bi-pencil"></i> Edit</a>
        @endcan
        @can('submit', $mr)
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.submit', $mr) }}">@csrf
                <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-send"></i> Send for approval</button></form>
        @endcan
        @can('revise', $mr)
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.revise', $mr) }}">@csrf
                <button class="ic-btn" type="submit" title="Menu or numbers changed? Create a new revision; this one stays as it is."><i class="bi bi-files"></i> Create revision</button></form>
        @endcan
        @can('cancel', $mr)
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.cancel', $mr) }}" style="display:flex;gap:8px;flex-wrap:wrap;flex:1;min-width:260px">@csrf
                <input class="ic-input" name="reason" placeholder="Reason for cancelling" required minlength="3" maxlength="500" style="flex:1">
                <button class="ic-btn ic-btn--danger" type="submit">Cancel{{ $mr->status === 'approved' ? ' & restore stock' : '' }}</button></form>
        @endcan
    </div>
</div>
@push('scripts')
<script>
(() => {
    // "Approve at standard": lines above the standard / usual amount → that amount (in the requested unit)
    document.querySelector('[data-fit-std]')?.addEventListener('click', e => {
        document.querySelectorAll('.sa-row[data-over][data-std]').forEach(row => {
            const qty = row.querySelector('[data-approve-qty]'), rem = row.querySelector('[data-approve-remark]');
            if (!qty) return;
            qty.value = row.dataset.std;
            if (rem && !rem.value) rem.value = 'Reduced to standard';
        });
        e.currentTarget.innerHTML = '<i class="bi bi-check2"></i> Set — check and tap Approve';
        e.currentTarget.disabled = true;
    });
    // "Approve only what's in store": short lines → available quantity, out-of-stock → 0, with a remark
    document.querySelector('[data-fit-stock]')?.addEventListener('click', e => {
        document.querySelectorAll('.sa-row[data-avail=short], .sa-row[data-avail=out]').forEach(row => {
            const qty = row.querySelector('[data-approve-qty]'), rem = row.querySelector('[data-approve-remark]');
            if (!qty) return;
            qty.value = row.dataset.fit;
            if (rem && !rem.value) rem.value = row.dataset.avail === 'out' ? 'Out of stock' : 'Only ' + row.dataset.fit + ' in store';
            qty.dispatchEvent(new Event('input', {bubbles: true}));
        });
        e.currentTarget.innerHTML = '<i class="bi bi-check2"></i> Set — check and tap Approve';
        e.currentTarget.disabled = true;
        document.querySelector('[data-approve-qty]')?.closest('form')?.scrollIntoView({behavior: 'smooth', block: 'start'});
    });
})();
</script>
@endpush
</x-admin-layout>
