<x-admin-layout :title="'Receive ' . $purchase->reference()">
@include('inventory._styles')
<div class="ic-wrap" style="max-width:760px">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.purchases.show', $purchase) }}"><i class="bi bi-arrow-left"></i> Back</a>
        <h1>Receive goods · {{ $purchase->supplierLabel() }}</h1>
    </div>
    @if($errors->any())
        <div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <p class="ic-note">Count what physically arrived. Only received quantity enters the store. Leave a row empty if it has not arrived yet.</p>
    <form method="POST" data-ic-form action="{{ route('inventory.purchases.receive', $purchase) }}">
        @csrf
        <input type="hidden" name="client_token" value="{{ old('client_token', $token) }}">
        @foreach($purchase->items as $i)
            @php
                $factor = $i->quantity > 0 ? $i->base_quantity / $i->quantity : 1;
                $due = round($i->pendingBaseQuantity() / $factor, 3);
            @endphp
            @continue($due <= 0)
            <div class="ic-card">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
                    <strong style="font-size:1.05rem">{{ $i->item->name }}</strong>
                    <span class="ic-chip ic-chip--info">Due {{ $due + 0 }} {{ $i->unit }}</span>
                </div>
                <div class="ic-fields" style="margin-top:10px">
                    <div>
                        <label class="ic-label">Received ({{ $i->unit }})</label>
                        <div style="display:flex;gap:6px">
                            <input class="ic-input" type="number" step="0.001" min="0" max="{{ $due }}" name="lines[{{ $i->id }}][received]" value="{{ old("lines.{$i->id}.received") }}" inputmode="decimal">
                            <button type="button" class="ic-btn ic-btn--sm" onclick="this.previousElementSibling.value='{{ $due + 0 }}'">All</button>
                        </div>
                    </div>
                    <div>
                        <label class="ic-label">Rejected / damaged</label>
                        <input class="ic-input" type="number" step="0.001" min="0" name="lines[{{ $i->id }}][rejected]" value="{{ old("lines.{$i->id}.rejected") }}" inputmode="decimal">
                    </div>
                    <div style="grid-column:1/-1">
                        <label class="ic-label">Remarks</label>
                        <input class="ic-input" name="lines[{{ $i->id }}][remarks]" value="{{ old("lines.{$i->id}.remarks") }}" maxlength="300" placeholder="Needed when something is rejected">
                    </div>
                </div>
            </div>
        @endforeach
        <div class="ic-card">
            <label class="ic-label" for="notes">Notes</label>
            <input class="ic-input" id="notes" name="notes" value="{{ old('notes') }}" maxlength="1000">
        </div>
        <div class="ic-sticky">
            <span class="ic-total" style="font-size:1rem">Received by {{ auth()->user()->name }}</span>
            <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-box-arrow-in-down"></i> Confirm receipt</button>
        </div>
    </form>
</div>
</x-admin-layout>
