{{--
    One-tap goods receipt on the purchase page.
    Normal case: attach the bill → "Received as ordered".
    Only when something differs: "Something different?" opens qty / rate per line and "Add item".
--}}
@include('inventory._item-picker')
@php
    $dueLines = $purchase->items->map(function ($i) {
        $f = $i->quantity > 0 ? $i->base_quantity / $i->quantity : 1;
        return ['i' => $i, 'due' => round($i->pendingBaseQuantity() / $f, 3)];
    })->filter(fn ($l) => $l['due'] > 0)->values();
    $billNeeded = ! $purchase->bill && $purchase->receipts->whereNotNull('bill_path')->isEmpty();
    $openDiff = $errors->any() && old('mode') === 'changes';
@endphp
@push('styles')
<style>
.qr{border:1px solid var(--ef-border);border-left:3px solid var(--ef-gold);border-radius:14px;background:var(--ef-surface);box-shadow:var(--ef-shadow);padding:14px;margin-bottom:14px}
.qr h2{font-size:1.05rem;font-weight:800;margin:0 0 2px}
.qr-sub{font-size:.85rem;color:var(--ef-muted);margin:0 0 12px}
.qr-file{display:flex;align-items:center;gap:10px;border:1.5px dashed var(--ef-border);border-radius:12px;padding:10px 12px;cursor:pointer;min-height:54px;position:relative}
.qr-file:hover{border-color:var(--ef-gold)}
.qr-file.--ok{border-style:solid;border-color:var(--ef-emerald);background:rgba(46,125,90,.06)}
.qr-file .bi{font-size:1.35rem;color:var(--ef-gold)}
.qr-file.--ok .bi{color:var(--ef-emerald)}
.qr-file input{position:absolute;inset:0;opacity:0;cursor:pointer}
.qr-file span{min-width:0;overflow-wrap:anywhere;font-size:.92rem;font-weight:600}
.qr-acts{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:12px}
.qr-acts .ic-btn--gold{flex:1 1 220px;justify-content:center;min-height:48px;font-size:1rem}
.qr-link{background:none;border:0;padding:8px 4px;color:var(--ef-gold);font-weight:700;cursor:pointer;min-height:44px}
.qr-diff{margin-top:12px;border-top:1px solid var(--ef-border);padding-top:12px}
.qr-diff[hidden]{display:none}
.qr-row{display:grid;grid-template-columns:1fr 92px 92px;gap:8px;align-items:center;padding:6px 0;border-bottom:1px solid var(--ef-border)}
.qr-row:last-of-type{border-bottom:0}
.qr-row.--head{font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--ef-muted);letter-spacing:.03em;padding-top:0}
.qr-row .ic-input{min-height:42px;font-size:16px;padding:6px 8px;width:100%}
.qr-name{min-width:0;font-weight:650;overflow-wrap:anywhere}
.qr-name small{display:block;font-weight:500;color:var(--ef-muted);font-size:.76rem}
.qr-row.--short .qr-name small{color:#7D5218;font-weight:700}
.qr-add{display:grid;grid-template-columns:1fr 92px 92px 36px;gap:8px;align-items:center;padding:6px 0}
@media (max-width:420px){.qr-row{grid-template-columns:1fr 78px 78px}.qr-add{grid-template-columns:1fr 70px 70px 32px}}
.qr-x{border:0;background:none;color:var(--ef-faint);font-size:1.1rem;min-height:40px;cursor:pointer}
.qr-more{display:flex;align-items:center;gap:8px;margin:10px 0 0;font-size:.88rem;font-weight:600;min-height:40px}
.qr-more input{width:20px;height:20px}
.qr-err{color:var(--ef-danger);font-size:.84rem;font-weight:600;margin-top:6px}
</style>
@endpush

<form class="qr" method="POST" enctype="multipart/form-data" data-ic-form action="{{ route('inventory.purchases.receive', $purchase) }}" id="qrForm">
    @csrf
    <input type="hidden" name="client_token" value="{{ old('client_token', (string) \Illuminate\Support\Str::uuid()) }}">
    <h2><i class="bi bi-box-arrow-in-down"></i> Goods arrived?</h2>
    <p class="qr-sub">{{ $billNeeded ? 'Snap the supplier bill, then tap Received.' : 'Tap Received. Attach the bill if you have one.' }} Stock updates immediately.</p>

    <label class="qr-file" data-file-box>
        <i class="bi bi-camera"></i>
        <span data-file-label>{{ $billNeeded ? 'Take photo of bill (required)' : 'Attach bill (optional)' }}</span>
        <input type="file" name="bill_file" accept="image/*,application/pdf" @required($billNeeded) data-file>
    </label>
    @error('bill_file')<div class="qr-err">{{ $message }}</div>@enderror
    @foreach(['lines', 'extra'] as $f)@error($f)<div class="qr-err">{{ $message }}</div>@enderror @endforeach
    @php $unpricedDue = $dueLines->filter(fn ($l) => (float) $l['i']->rate <= 0); @endphp
    @foreach($dueLines as $l)@error('lines.'.$l['i']->id)<div class="qr-err">{{ $message }}</div>@enderror @endforeach
    @if($unpricedDue->isNotEmpty())
        <div class="qr-diff" style="margin-top:12px;padding-top:10px" data-prices>
            <div class="qr-sub" style="margin-bottom:6px"><b>No price was entered when ordering.</b> Type the rate on the bill (per unit, before tax).</div>
            <div class="qr-row --head"><span>Item</span><span>Due</span><span>Rate ₹</span></div>
            @foreach($unpricedDue as $l)
                @php $i = $l['i']; @endphp
                <div class="qr-row">
                    <div class="qr-name">{{ $i->item->name }}<small>per {{ $i->unit }}</small></div>
                    <span>{{ $l['due'] + 0 }} {{ $i->unit }}</span>
                    <input class="ic-input" type="number" step="0.01" min="0.01" inputmode="decimal" name="lines[{{ $i->id }}][rate]" required
                           value="{{ old("lines.{$i->id}.rate") }}" placeholder="₹" aria-label="{{ $i->item->name }} billed rate" style="border-color:var(--ef-gold)">
                </div>
            @endforeach
        </div>
    @endif

    <div class="qr-acts" data-acts-all @if($openDiff) hidden @endif>
        <button type="submit" name="mode" value="all" class="ic-btn ic-btn--gold"><i class="bi bi-check2-all"></i> Received as ordered</button>
        <button type="button" class="qr-link" data-open-diff>Something different?</button>
    </div>

    <div class="qr-diff" data-diff @unless($openDiff) hidden @endunless>
        <div class="qr-sub" style="margin-bottom:6px">Change only what differs. Blank = arrived as ordered.</div>
        <div class="qr-row --head"><span>Item</span><span>Got</span><span>Rate ₹</span></div>
        @foreach($dueLines as $l)
            @php $i = $l['i']; @endphp
            <div class="qr-row" data-line data-due="{{ $l['due'] }}">
                <div class="qr-name">{{ $i->item->name }}<small data-hint>Due {{ $l['due'] + 0 }} {{ $i->unit }}</small></div>
                <input class="ic-input" type="number" step="0.001" min="0" max="{{ $l['due'] }}" inputmode="decimal" name="lines[{{ $i->id }}][received]"
                       value="{{ old("lines.{$i->id}.received") }}" placeholder="{{ $l['due'] + 0 }}" aria-label="{{ $i->item->name }} received ({{ $i->unit }})" data-got>
                @if((float) $i->rate > 0)
                <input class="ic-input" type="number" step="0.01" min="0" inputmode="decimal" name="lines[{{ $i->id }}][rate]"
                       value="{{ old("lines.{$i->id}.rate") }}" placeholder="{{ $i->rate + 0 }}" aria-label="{{ $i->item->name }} rate">
                @else<span class="qr-sub" style="margin:0;font-size:.74rem">rate above</span>@endif
            </div>
        @endforeach

        <div data-extras></div>
        <button type="button" class="qr-link" data-add><i class="bi bi-plus-lg"></i> Add item not on the order</button>

        <label class="qr-more"><input type="checkbox" name="more_coming" value="1" @checked(old('more_coming'))> Rest will come later (keep short items open)</label>

        <div class="qr-acts">
            <button type="submit" name="mode" value="changes" class="ic-btn ic-btn--gold"><i class="bi bi-check2"></i> Save receipt</button>
            <button type="button" class="qr-link" data-close-diff>Cancel</button>
        </div>
        <a class="qr-link" style="display:inline-block;font-size:.82rem;font-weight:600;color:var(--ef-muted)" href="{{ route('inventory.purchases.receive-form', $purchase) }}">Damaged goods? Use detailed receipt</a>
    </div>

    <template id="qrExtraTpl">
        <div class="qr-add" data-extra>
            <div><input class="ic-input" type="text" placeholder="Search item" data-pick aria-label="Item">
                <input type="hidden" name="extra[__I__][inventory_item_id]" data-pick-id></div>
            <input class="ic-input" type="number" step="0.001" min="0.001" inputmode="decimal" name="extra[__I__][quantity]" placeholder="Qty" aria-label="Quantity" data-qty>
            <input class="ic-input" type="number" step="0.01" min="0" inputmode="decimal" name="extra[__I__][rate]" placeholder="Rate" aria-label="Rate">
            <button type="button" class="qr-x" data-rm aria-label="Remove"><i class="bi bi-x-lg"></i></button>
        </div>
    </template>
</form>

@push('scripts')
<script>
(() => {
    const f = document.getElementById('qrForm'); if (!f) return;
    const diff = f.querySelector('[data-diff]'), all = f.querySelector('[data-acts-all]');
    const file = f.querySelector('[data-file]'), box = f.querySelector('[data-file-box]');
    file.addEventListener('change', () => {
        const ok = file.files.length > 0;
        box.classList.toggle('--ok', ok);
        box.querySelector('.bi').className = 'bi ' + (ok ? 'bi-check-circle-fill' : 'bi-camera');
        f.querySelector('[data-file-label]').textContent = ok ? file.files[0].name : 'Take photo of bill';
    });
    const toggle = open => { diff.hidden = !open; all.hidden = open; };
    f.querySelector('[data-open-diff]').onclick = () => toggle(true);
    f.querySelector('[data-close-diff]').onclick = () => toggle(false);
    f.addEventListener('input', e => {
        const row = e.target.closest('[data-line]'); if (!row || !e.target.matches('[data-got]')) return;
        const due = +row.dataset.due, v = e.target.value === '' ? due : +e.target.value;
        row.classList.toggle('--short', v < due);
        row.querySelector('[data-hint]').textContent = v < due ? `Short ${+(due - v).toFixed(3)}` : `Due ${due}`;
    });
    let n = 0;
    f.querySelector('[data-add]').onclick = () => {
        const html = document.getElementById('qrExtraTpl').innerHTML.replaceAll('__I__', n++);
        const box = f.querySelector('[data-extras]'); box.insertAdjacentHTML('beforeend', html);
        const row = box.lastElementChild, inp = row.querySelector('[data-pick]');
        window.IcItemPicker?.attach(inp, { allowCreate: true, onPick(it) {
            row.querySelector('[data-pick-id]').value = it.id;
            row.querySelector('[data-qty]').placeholder = 'Qty ' + (it.unit || '');
            row.querySelector('[data-qty]').required = true;
        }});
        inp.focus();
    };
    f.addEventListener('click', e => { const b = e.target.closest('[data-rm]'); if (b) b.closest('[data-extra]').remove(); });
})();
</script>
@endpush
