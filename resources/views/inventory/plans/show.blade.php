<x-admin-layout :title="$plan->reference().' · '.$plan->title">
@include('inventory.plans._style')
@php
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0';
    $lines = collect($progress['lines']);
    $st = $plan->status;
    $order = ['draft' => 0, 'submitted' => 1, 'rejected' => 1, 'approved' => 2, 'ordered' => 3, 'completed' => 4, 'cancelled' => 4];
    $steps = [
        ['Prepared', $plan->created_at?->format('d M')],
        [$st === 'rejected' ? 'Sent back' : 'Approval', $st === 'rejected' ? $plan->rejected_at?->format('d M') : $plan->approved_at?->format('d M')],
        ['Buying', $plan->purchases->count() ? $plan->purchases->count().' '.Str::plural('purchase', $plan->purchases->count()) : null],
        ['Receiving', $progress['awaiting'] ? $progress['awaiting'].' awaiting' : null],
        [$st === 'cancelled' ? 'Cancelled' : 'Closed', $plan->completed_at?->format('d M')],
    ];
    $now = match ($st) { 'draft' => 0, 'submitted', 'rejected' => 1, 'approved' => 2, 'ordered' => $progress['awaiting'] || $progress['open_purchases'] ? 3 : 4, default => 5 };
    $stateLabel = ['as_planned' => 'As planned', 'short' => 'Short', 'over' => 'More than planned', 'awaiting' => 'Awaiting goods', 'not_bought' => 'Not bought', 'dropped' => 'Not approved'];
    $approving = auth()->user()->can('approve', $plan) && $st === 'submitted';
    $canBuy = auth()->user()->can('buy', $plan);
    $toBuy = $lines->filter(fn ($l) => $l['to_order'] > 0);
    $showProgress = in_array($st, ['ordered', 'completed'], true) || $plan->purchases->isNotEmpty();
@endphp
@push('styles')
<style>
.pv-tbl{width:100%;border-collapse:collapse;font-size:.86rem}
.pv-tbl th{padding:8px 12px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted);text-align:left;white-space:nowrap}
.pv-tbl td{padding:10px 12px;border-top:1px solid var(--ef-border);vertical-align:middle}
.pv-tbl .r{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.pv-tbl td b{font-weight:750}
.pv-tbl small{display:block;font-size:.72rem;color:var(--ef-muted)}
.pv-tbl input{width:110px;min-height:40px;padding:4px 8px;border-radius:8px;border:1px solid var(--ef-border);font-size:16px;text-align:right;font-weight:700}
.pv-diff{font-size:.72rem;font-weight:800}
.pv-diff.--up{color:var(--ef-danger)}
.pv-diff.--down{color:var(--ef-emerald-dk)}
@media (max-width:759.98px){
  .pv-tbl thead{display:none}
  .pv-tbl,.pv-tbl tbody,.pv-tbl tr{display:block;width:100%}
  .pv-tbl tr{padding:10px 14px;border-top:1px solid var(--ef-border)}
  .pv-tbl td{display:flex;justify-content:space-between;gap:10px;padding:3px 0;border:0;text-align:right}
  .pv-tbl td::before{content:attr(data-l);font-size:.72rem;font-weight:700;color:var(--ef-muted);text-align:left}
  .pv-tbl td:first-child{display:block;text-align:left}
  .pv-tbl td:first-child::before{display:none}
}
.pv-purch{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 14px;border-top:1px solid var(--ef-border);text-decoration:none;color:var(--ef-ink)}
.pv-purch:first-child{border-top:0}
.pv-purch:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.pv-purch small{display:block;font-size:.74rem;color:var(--ef-muted)}
.pv-sum{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden}
.pv-sum div{padding:10px 14px;background:linear-gradient(var(--tint,transparent),var(--tint,transparent)),var(--ef-surface)}
.pv-sum small{display:block;font-size:.72rem;font-weight:700;color:var(--ef-muted)}
.pv-sum strong{font-size:1.2rem;font-weight:800;color:var(--acc,var(--ef-ink))}
@media (max-width:639.98px){.pv-sum{grid-template-columns:repeat(2,minmax(0,1fr))}}
.pv-check{display:flex;gap:8px;align-items:flex-start;font-size:.86rem;font-weight:600;color:var(--ef-ink);margin:8px 0}
.pv-check input{width:18px;height:18px;margin-top:2px;accent-color:var(--ef-emerald)}
</style>
@endpush

<div class="pl">
    <header class="pl-head">
        <div style="flex:1 1 300px;min-width:0">
            <a class="pl-back" href="{{ route('inventory.plans.index') }}"><i class="bi bi-arrow-left"></i> Purchase plans</a>
            <h1>{{ $plan->title }} <span class="pl-chip" data-s="{{ $st }}">{{ $plan->statusLabel() }}</span></h1>
            <p>{{ $plan->reference() }} · by {{ $plan->creator?->name }} · needed by {{ $plan->planned_date?->format('d M Y') }}@if($plan->approver) · approved by {{ $plan->approver->name }}@endif</p>
        </div>
        <div class="pl-acts">
            @can('update', $plan)<a href="{{ route('inventory.plans.edit', $plan) }}" class="pl-btn"><i class="bi bi-pencil"></i> Edit</a>@endcan
            @can('submit', $plan)
                <form method="POST" action="{{ route('inventory.plans.submit', $plan) }}">@csrf<button class="pl-btn --main"><i class="bi bi-send"></i> Send for approval</button></form>
            @endcan
            @can('cancel', $plan)
                <form method="POST" action="{{ route('inventory.plans.cancel', $plan) }}" onsubmit="return confirm('Cancel this plan?')">@csrf<button class="pl-btn --bad"><i class="bi bi-x-circle"></i> Cancel</button></form>
            @endcan
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="pl-err">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="pl-err"><b>Please fix:</b><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @if($st === 'rejected')<div class="pl-err"><b>Sent back by {{ $plan->rejecter?->name }}:</b> {{ $plan->rejection_reason }} — edit the plan and send it again.</div>@endif
    @if($plan->approval_note)<div class="alert alert-info mb-0"><b>Approval note:</b> {{ $plan->approval_note }}</div>@endif

    <nav class="pl-steps" aria-label="Progress">
        @foreach($steps as $i => [$label, $sub])
            <div class="pl-step {{ $i < $now ? '--done' : ($i === $now ? '--now' : '') }} {{ ($st === 'rejected' && $i === 1) || ($st === 'cancelled' && $i === 4) ? '--stop' : '' }}">
                <b>{{ $i < $now ? '✓' : $i + 1 }}</b><span>{{ $label }}@if($sub)<small>{{ $sub }}</small>@endif</span>
            </div>
        @endforeach
    </nav>

    @if($plan->notes)<p class="pl-note"><i class="bi bi-sticky"></i> {{ $plan->notes }}</p>@endif

    {{-- Items: plan vs approval vs what was bought and received --}}
    <form method="POST" action="{{ route('inventory.plans.approve', $plan) }}" id="pvApprove">
        @csrf
        <section class="pl-panel" style="--acc:var(--ef-emerald)">
            <div class="pl-ph" style="--tint:rgba(30,122,60,.05)"><h2><i class="bi bi-basket"></i> Items</h2>
                <span class="--r">{{ $lines->count() }} items · estimate ₹{{ number_format($plan->items->sum(fn ($i) => (float) $i->estimated_unit_cost * $i->allowedQuantity()), 0) }}</span></div>
            <div style="overflow-x:auto">
            <table class="pv-tbl">
                <thead><tr><th>Item</th><th class="r">Requested</th><th class="r">{{ $approving ? 'Approve qty' : 'Approved' }}</th>
                    @if($showProgress)<th class="r">Bought</th><th class="r">Received</th><th>Status</th>@else<th class="r">Est. rate</th><th>Priority</th>@endif</tr></thead>
                <tbody>
                @foreach($lines as $l)
                    <tr data-line="{{ $l['id'] }}">
                        <td><b>{{ $l['name'] }}</b>@if($l['name_ta'])<small>{{ $l['name_ta'] }}</small>@endif @if($l['notes'])<small>{{ $l['notes'] }}</small>@endif</td>
                        <td data-l="Requested" class="r">{{ $q($l['requested']) }} {{ $l['unit'] }}</td>
                        <td data-l="{{ $approving ? 'Approve qty' : 'Approved' }}" class="r">
                            @if($approving)<input type="number" step="0.001" min="0" name="approved[{{ $l['id'] }}]" value="{{ $q($l['requested']) }}" aria-label="Approve quantity for {{ $l['name'] }}">
                            @elseif(in_array($st, ['draft', 'submitted', 'rejected', 'cancelled'], true) && ! $plan->approved_at)—
                            @else<b>{{ $q($l['allowed']) }}</b> {{ $l['unit'] }}@if(abs($l['allowed'] - $l['requested']) > 0.0005)<small>changed by approver</small>@endif @endif
                        </td>
                        @if($showProgress)
                            <td data-l="Bought" class="r">{{ $q($l['ordered']) }}@if($l['avg_rate'])<small>₹{{ number_format($l['avg_rate'], 2) }} @if($l['price_diff'])<span class="pv-diff {{ $l['price_diff'] > 0 ? '--up' : '--down' }}">{{ $l['price_diff'] > 0 ? '+' : '' }}{{ $l['price_diff'] }}% vs est.</span>@endif</small>@endif</td>
                            <td data-l="Received" class="r"><b>{{ $q($l['received']) }}</b>@if($l['rejected'] > 0)<small>{{ $q($l['rejected']) }} rejected</small>@endif @if($l['closed'] > 0)<small>{{ $q($l['closed']) }} closed short</small>@endif</td>
                            <td data-l="Status"><span class="pl-chip" data-s="{{ $l['state'] }}">{{ $stateLabel[$l['state']] }}</span>@if($l['to_order'] > 0 && in_array($st, ['approved', 'ordered'], true))<small>{{ $q($l['to_order']) }} left to buy</small>@endif</td>
                        @else
                            <td data-l="Est. rate" class="r">{{ $l['estimate'] ? '₹'.number_format($l['estimate'], 2) : '—' }}</td>
                            <td data-l="Priority">{{ ucfirst($l['priority']) }}</td>
                        @endif
                    </tr>
                @endforeach
                @foreach($progress['extras'] as $x)
                    <tr><td><b>{{ $x['name'] }}</b><small>not on the plan — added when receiving</small></td><td data-l="Requested" class="r">—</td><td data-l="Approved" class="r">—</td>
                        @if($showProgress)<td data-l="Bought" class="r">—</td><td data-l="Received" class="r"><b>{{ $q($x['received']) }}</b> {{ $x['unit'] }}</td><td data-l="Status"><span class="pl-chip" data-s="over">Extra</span></td>@else<td></td><td></td>@endif</tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @if($approving)
                <div class="pl-pb" style="border-top:1px solid var(--ef-border)">
                    <label class="pl-field">Note to the creator <span style="font-weight:500">(optional)</span><input name="approval_note" maxlength="500" placeholder="e.g. buy onions from the market, not the supermarket"></label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
                        <button class="pl-btn --ok"><i class="bi bi-check2-circle"></i> Approve plan</button>
                        <button type="button" class="pl-btn --bad" data-bs-toggle="collapse" data-bs-target="#pvReject"><i class="bi bi-arrow-return-left"></i> Send back</button>
                    </div>
                    <p class="pl-note" style="margin-top:6px">Set a quantity to 0 to leave that item out. Changes are shown to the creator.</p>
                </div>
            @endif
        </section>
    </form>
    @if($approving)
        <form method="POST" action="{{ route('inventory.plans.reject', $plan) }}" class="collapse" id="pvReject">
            @csrf
            <section class="pl-panel" style="--acc:var(--ef-danger)"><div class="pl-pb">
                <label class="pl-field">Why is it sent back?<textarea name="reason" required minlength="3" maxlength="500" placeholder="e.g. Rice already in stock — remove it"></textarea></label>
                <button class="pl-btn --bad" style="margin-top:8px"><i class="bi bi-arrow-return-left"></i> Send back to {{ $plan->creator?->name }}</button>
            </div></section>
        </form>
    @elseif(auth()->user()->isAdmin() && $st === 'draft')
        <form method="POST" action="{{ route('inventory.plans.approve', $plan) }}">@csrf
            <button class="pl-btn --ok"><i class="bi bi-check2-circle"></i> Approve as is</button>
        </form>
    @endif

    @if($showProgress)
        <div class="pv-sum">
            <div style="--acc:#2c5784"><small>Purchases</small><strong>{{ $plan->purchases->whereNotIn('status', ['cancelled', 'rejected'])->count() }}</strong></div>
            <div style="--acc:#9a6413"><small>Spent</small><strong>₹{{ number_format($progress['spent'], 0) }}</strong></div>
            <div style="--acc:{{ $progress['awaiting'] ? '#2c5784' : 'var(--ef-faint)' }}"><small>Items awaiting goods</small><strong>{{ $progress['awaiting'] }}</strong></div>
            <div style="--acc:{{ $progress['differences'] ? '#a3480f' : 'var(--ef-emerald)' }};--tint:{{ $progress['differences'] ? 'rgba(214,110,40,.06)' : 'rgba(30,122,60,.05)' }}"><small>Differences from plan</small><strong>{{ $progress['differences'] ?: 'None' }}</strong></div>
        </div>
    @endif

    {{-- Buy from the plan --}}
    @if($canBuy && $toBuy->isNotEmpty())
        <form method="POST" action="{{ route('inventory.plans.buy', $plan) }}">
            @csrf
            <input type="hidden" name="client_token" value="{{ Str::uuid() }}">
            <section class="pl-panel" style="--acc:#2c5784">
                <div class="pl-ph" style="--tint:rgba(59,110,165,.05)"><h2><i class="bi bi-cart-check"></i> Buy from this plan</h2><span class="--r">one purchase per supplier / shop</span></div>
                <div class="pl-pb pl-grid">
                    <label class="pl-field">Supplier<select name="vendor_id"><option value="">— type shop name instead —</option>@foreach($vendors as $v)<option value="{{ $v->id }}" @selected(old('vendor_id') == $v->id)>{{ $v->name }}</option>@endforeach</select></label>
                    <label class="pl-field">or shop name<input name="supplier_name" maxlength="150" value="{{ old('supplier_name') }}" placeholder="e.g. Ukkadam market"></label>
                    <label class="pl-field">Purchase date<input type="date" name="purchase_date" required value="{{ old('purchase_date', now()->toDateString()) }}"></label>
                </div>
                <div style="overflow-x:auto">
                <table class="pv-tbl">
                    <thead><tr><th>Item</th><th class="r">Left to buy</th><th class="r">Buy now</th><th class="r">Rate ₹ / unit</th></tr></thead>
                    <tbody>
                    @foreach($toBuy as $l)
                        <tr><td><b>{{ $l['name'] }}</b><small>{{ $l['unit'] }}</small></td>
                            <td data-l="Left to buy" class="r">{{ $q($l['to_order']) }} {{ $l['unit'] }}</td>
                            <td data-l="Buy now" class="r"><input type="number" step="0.001" min="0" max="{{ $l['to_order'] }}" name="lines[{{ $l['id'] }}][quantity]" value="{{ old('lines.'.$l['id'].'.quantity', $q($l['to_order'])) }}" aria-label="Buy now {{ $l['name'] }}"></td>
                            <td data-l="Rate" class="r"><input type="number" step="0.01" min="0" name="lines[{{ $l['id'] }}][rate]" value="{{ old('lines.'.$l['id'].'.rate', $l['estimate']) }}" aria-label="Rate {{ $l['name'] }}"></td></tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
                <div class="pl-pb" style="border-top:1px solid var(--ef-border)">
                    <p class="pl-note" style="margin-bottom:8px">Don't know the price yet? Leave the rate empty — the storekeeper enters the billed rate when receiving and the purchase total updates then. Clear a quantity (or set 0) to buy that item later or from another shop. The purchase is already approved through this plan; stock rises only when the goods are received.</p>
                    <button class="pl-btn --main"><i class="bi bi-cart-check"></i> Create purchase</button>
                </div>
            </section>
        </form>
    @endif

    {{-- Purchases made from the plan --}}
    @if($plan->purchases->isNotEmpty())
        <section class="pl-panel" style="--acc:#9a6413">
            <div class="pl-ph" style="--tint:rgba(200,144,42,.06)"><h2><i class="bi bi-receipt"></i> Purchases from this plan</h2></div>
            @foreach($plan->purchases as $pu)
                <a class="pv-purch" href="{{ route('inventory.purchases.show', $pu) }}">
                    <span><b>{{ $pu->reference() }}</b> · {{ $pu->vendor?->name ?? $pu->supplier_name }}<small>{{ $pu->purchase_date?->format('d M Y') }} · {{ $pu->items_count }} items · ₹{{ number_format((float) $pu->grand_total, 0) }}</small></span>
                    <span class="pl-chip" data-s="{{ in_array($pu->status, ['received', 'completed'], true) ? 'as_planned' : ($pu->status === 'cancelled' || $pu->status === 'rejected' ? 'dropped' : 'awaiting') }}">{{ $pu->statusLabel() }}</span>
                </a>
            @endforeach
            @if($progress['open_purchases'])<p class="pl-note" style="padding:8px 14px;border-top:1px solid var(--ef-border)">When goods arrive, open the purchase and use <b>Receive goods</b>: record what came, anything rejected, a different rate, or extra items.</p>@endif
        </section>
    @endif

    {{-- Close the plan --}}
    @if(auth()->user()->can('close', $plan) && $plan->purchases->isNotEmpty())
        <section class="pl-panel" style="--acc:{{ $progress['all_as_planned'] ? 'var(--ef-emerald)' : '#a3480f' }}">
            <div class="pl-ph"><h2><i class="bi bi-flag"></i> Close the plan</h2></div>
            <div class="pl-pb">
                @if($progress['open_purchases'])
                    <p class="pl-note">{{ $progress['open_purchases'] }} {{ Str::plural('purchase', $progress['open_purchases']) }} still open. Receive the goods (or close / cancel the purchase) before closing the plan.</p>
                @elseif($progress['all_as_planned'])
                    <form method="POST" action="{{ route('inventory.plans.close', $plan) }}">@csrf
                        <p class="pl-note" style="margin-bottom:8px"><i class="bi bi-check-circle-fill" style="color:var(--ef-emerald)"></i> Everything was received as planned.</p>
                        <button class="pl-btn --ok"><i class="bi bi-check2-all"></i> Close plan</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('inventory.plans.close', $plan) }}">@csrf
                        <p class="pl-note" style="margin-bottom:6px">What arrived differs from the plan:</p>
                        <ul class="pl-note" style="margin:0 0 8px;padding-left:18px">
                            @foreach($lines->whereIn('state', ['short', 'over', 'not_bought']) as $l)
                                <li><b style="color:var(--ef-ink)">{{ $l['name'] }}</b> — approved {{ $q($l['allowed']) }}, received {{ $q($l['received']) }} {{ $l['unit'] }} ({{ strtolower($stateLabel[$l['state']]) }})</li>
                            @endforeach
                            @foreach($progress['extras'] as $x)<li><b style="color:var(--ef-ink)">{{ $x['name'] }}</b> — {{ $q($x['received']) }} {{ $x['unit'] }} received but not planned</li>@endforeach
                        </ul>
                        <label class="pl-field">Explain the difference<textarea name="closing_note" required maxlength="1000" placeholder="e.g. Shop had only 20 kg onions; tomatoes rejected (spoiled)">{{ old('closing_note') }}</textarea></label>
                        @if($lines->contains(fn ($l) => $l['allowed'] > 0 && $l['received'] + 0.0005 < $l['allowed']))
                            <label class="pv-check"><input type="checkbox" name="replan" value="1" checked> Put the shortfall into a new draft plan (to buy it later)</label>
                        @endif
                        <button class="pl-btn --main"><i class="bi bi-flag"></i> Close plan with differences</button>
                    </form>
                @endif
            </div>
        </section>
    @endif

    @if($st === 'completed')
        <section class="pl-panel" style="--acc:#5f6b5a"><div class="pl-pb">
            <p class="pl-note"><b style="color:var(--ef-ink)">Closed</b> {{ $plan->completed_at?->format('d M Y') }} by {{ $plan->closer?->name }}@if($plan->closing_note) — {{ $plan->closing_note }}@endif</p>
            @if($plan->shortfallPlan)<p class="pl-note" style="margin-top:6px">Shortfall moved to <a href="{{ route('inventory.plans.show', $plan->shortfallPlan) }}">{{ $plan->shortfallPlan->reference() }} · {{ $plan->shortfallPlan->title }}</a></p>@endif
        </div></section>
    @endif
</div>
</x-admin-layout>
