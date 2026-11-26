<x-admin-layout :title="'Receive ' . $purchase->reference()">
@include('inventory._styles')

@push('styles')
<style>
/* Receive goods — mobile-first, same language as the bill review page. */
.gr-page{max-width:820px;margin:0 auto}
.gr-note{font-size:.86rem;color:var(--ef-muted);margin:0 0 12px}
.gr-line{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:var(--ef-shadow);padding:12px 14px;margin-bottom:10px;border-left:3px solid var(--gr-tone,var(--ef-border))}
.gr-line[data-state=full]{--gr-tone:var(--ef-emerald)}
.gr-line[data-state=short]{--gr-tone:var(--ef-amber)}
.gr-line[data-state=closed]{--gr-tone:var(--ef-danger)}
.gr-top{display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap}
.gr-name{flex:1 1 160px;min-width:0;font-weight:750;font-size:1rem;overflow-wrap:anywhere}
.gr-sub{font-size:.8rem;color:var(--ef-muted);margin-top:2px}
.gr-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px}
@media (min-width:640px){.gr-grid{grid-template-columns:1.4fr 1fr 1fr}}
.gr-f{display:flex;flex-direction:column;gap:4px;min-width:0}
.gr-cap{font-size:.74rem;font-weight:700;color:var(--ef-muted);text-transform:uppercase;letter-spacing:.03em}
.gr-qty{display:flex;gap:6px}
.gr-qty .ic-input{flex:1;min-width:0}
.gr-f .ic-input{min-height:44px;font-size:16px}
.gr-close{display:flex;align-items:center;gap:8px;margin-top:10px;font-size:.88rem;font-weight:600;cursor:pointer;min-height:40px}
.gr-close input{width:20px;height:20px;accent-color:var(--ef-amber)}
.gr-rem{margin-top:8px}
.gr-diff{font-size:.8rem;margin-top:6px;color:#7D5218;font-weight:600}
.gr-diff[hidden]{display:none}
.gr-ferr{color:var(--ef-danger);font-size:.8rem;font-weight:600;margin-top:6px}
.gr-bill{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:var(--ef-shadow);padding:14px;margin-bottom:10px}
.gr-bill h2{font-size:1rem;font-weight:800;margin:0 0 4px}
.gr-drop{display:flex;align-items:center;gap:10px;border:1.5px dashed var(--ef-border);border-radius:12px;padding:12px;margin:10px 0;cursor:pointer;min-height:56px}
.gr-drop:hover{border-color:var(--ef-gold)}
.gr-drop .bi{font-size:1.4rem;color:var(--ef-gold)}
.gr-drop input{position:absolute;width:1px;height:1px;opacity:0}
.gr-drop span{min-width:0;overflow-wrap:anywhere;font-size:.9rem}
.gr-sum{display:flex;flex-direction:column;gap:2px;min-width:0;flex:1}
.gr-sum strong{font-size:1rem}
.gr-sum small{font-size:.76rem;color:var(--ef-muted)}
.gr-sum .--warn{color:#7D5218;font-weight:700}
</style>
@endpush

@php
    $lines = $purchase->items->map(function ($i) {
        $factor = $i->quantity > 0 ? $i->base_quantity / $i->quantity : 1;
        return ['i' => $i, 'due' => round($i->pendingBaseQuantity() / $factor, 3)];
    })->filter(fn ($l) => $l['due'] > 0);
@endphp

<div class="ic-wrap gr-page">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.purchases.show', $purchase) }}"><i class="bi bi-arrow-left"></i> Back</a>
        <h1>Receive goods · {{ $purchase->supplierLabel() }}</h1>
    </div>
    @if($errors->any())
        <div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <p class="gr-note">Count what physically arrived — only received quantity enters the store.
        If the supplier sent less, enter what came; tick <strong>Rest not coming</strong> if no more will arrive.
        If the bill rate differs, enter it — the approved purchase stays as it was and the difference is recorded.</p>

    <form method="POST" data-ic-form enctype="multipart/form-data" action="{{ route('inventory.purchases.receive', $purchase) }}" id="grForm">
        @csrf
        <input type="hidden" name="client_token" value="{{ old('client_token', $token) }}">

        <div class="gr-bill">
            <h2><i class="bi bi-receipt"></i> Supplier bill</h2>
            <div class="gr-note" style="margin:0">Attach the bill / delivery challan that came with the goods (photo or PDF, up to 10 MB).</div>
            <label class="gr-drop">
                <i class="bi bi-camera"></i>
                <span data-file-label>Take photo or choose file</span>
                <input type="file" name="bill_file" accept="image/jpeg,image/png,application/pdf" capture="environment" data-file>
            </label>
            @error('bill_file')<div class="gr-ferr">{{ $message }}</div>@enderror
            <div class="gr-grid" style="margin-top:0">
                <label class="gr-f"><span class="gr-cap">Bill no.</span>
                    <input class="ic-input" name="bill_number" maxlength="100" value="{{ old('bill_number', $purchase->invoice_number) }}"></label>
                <label class="gr-f"><span class="gr-cap">Bill amount ₹</span>
                    <input class="ic-input" type="number" step="0.01" min="0" name="bill_amount" value="{{ old('bill_amount') }}" inputmode="decimal" data-bill-amt></label>
            </div>
        </div>

        @foreach($lines as $l)
            @php
                $i = $l['i']; $due = $l['due']; $k = "lines.{$i->id}";
                $closeOld = old("$k.close");
            @endphp
            <div class="gr-line" data-line data-due="{{ $due }}" data-rate="{{ $i->rate }}" data-tax="{{ $i->tax_percent }}" data-state="">
                <div class="gr-top">
                    <div class="gr-name">{{ $i->item->name }}
                        <div class="gr-sub">Ordered {{ $i->quantity + 0 }} {{ $i->unit }} @ ₹{{ number_format($i->rate, 2) }}@if($i->tax_percent > 0) + {{ $i->tax_percent + 0 }}%@endif</div>
                    </div>
                    <span class="ic-chip ic-chip--info">Due {{ $due + 0 }} {{ $i->unit }}</span>
                </div>
                <div class="gr-grid">
                    <label class="gr-f" style="grid-column:1/-1;max-width:100%"><span class="gr-cap">Received ({{ $i->unit }})</span>
                        <span class="gr-qty">
                            <input class="ic-input" type="number" step="0.001" min="0" max="{{ $due }}" name="lines[{{ $i->id }}][received]" value="{{ old("$k.received") }}" inputmode="decimal" data-recv>
                            <button type="button" class="ic-btn ic-btn--sm" data-all>All {{ $due + 0 }}</button>
                        </span></label>
                    <label class="gr-f"><span class="gr-cap">Rejected / damaged</span>
                        <input class="ic-input" type="number" step="0.001" min="0" name="lines[{{ $i->id }}][rejected]" value="{{ old("$k.rejected") }}" inputmode="decimal" data-rej></label>
                    <label class="gr-f"><span class="gr-cap">Rate on bill ₹</span>
                        <input class="ic-input" type="number" step="0.01" min="0" name="lines[{{ $i->id }}][rate]" value="{{ old("$k.rate") }}" placeholder="{{ number_format($i->rate, 2, '.', '') }}" inputmode="decimal" data-rate-in></label>
                </div>
                <div class="gr-diff" data-diff hidden></div>
                <label class="gr-close">
                    <input type="hidden" name="lines[{{ $i->id }}][close]" value="0">
                    <input type="checkbox" name="lines[{{ $i->id }}][close]" value="1" @checked($closeOld) data-close>
                    Rest not coming — close this item
                </label>
                <label class="gr-f gr-rem"><span class="gr-cap">Remarks <span data-rem-req hidden>(required)</span></span>
                    <input class="ic-input" name="lines[{{ $i->id }}][remarks]" value="{{ old("$k.remarks") }}" maxlength="300" placeholder="Why short / rejected / rate changed" data-rem></label>
                @error($k)<div class="gr-ferr">{{ $message }}</div>@enderror
            </div>
        @endforeach

        <div class="ic-card">
            <label class="ic-label" for="notes">Notes</label>
            <input class="ic-input" id="notes" name="notes" value="{{ old('notes') }}" maxlength="1000">
        </div>

        <div class="ic-sticky">
            <div class="gr-sum">
                <strong>₹<span data-est>0.00</span></strong>
                <small data-est-note>Value of goods received</small>
            </div>
            <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-box-arrow-in-down"></i> Confirm receipt</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(() => {
    const form = document.getElementById('grForm');
    if (!form) return;
    const num = v => { const n = parseFloat(v); return isFinite(n) ? n : 0; };
    const fmt = n => n.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const file = form.querySelector('[data-file]');
    file?.addEventListener('change', () => {
        form.querySelector('[data-file-label]').textContent = file.files[0] ? file.files[0].name : 'Take photo or choose file';
    });

    function update() {
        let est = 0;
        form.querySelectorAll('[data-line]').forEach(line => {
            const due = num(line.dataset.due), recv = num(line.querySelector('[data-recv]').value);
            const rej = num(line.querySelector('[data-rej]').value), close = line.querySelector('[data-close]').checked;
            const rIn = line.querySelector('[data-rate-in]').value, rate = rIn === '' ? num(line.dataset.rate) : num(rIn);
            est += recv * rate * (1 + num(line.dataset.tax) / 100);
            line.dataset.state = close ? 'closed' : (recv >= due - 0.0005 ? 'full' : (recv > 0 ? 'short' : ''));
            const diff = line.querySelector('[data-diff]'), msgs = [];
            if (recv > 0 && recv < due - 0.0005) msgs.push(close ? `Short by ${+(due - recv).toFixed(3)} — will be closed` : `${+(due - recv).toFixed(3)} still due after this`);
            if (rIn !== '' && Math.abs(num(rIn) - num(line.dataset.rate)) >= 0.005) msgs.push(`Rate ₹${fmt(num(rIn))} vs approved ₹${fmt(num(line.dataset.rate))}`);
            diff.textContent = msgs.join(' · '); diff.hidden = !msgs.length;
            line.querySelector('[data-rem-req]').hidden = !(rej > 0 || close);
            line.querySelector('[data-rem]').required = rej > 0 || close;
        });
        form.querySelector('[data-est]').textContent = fmt(est);
        const bill = form.querySelector('[data-bill-amt]').value, note = form.querySelector('[data-est-note]');
        const gap = bill === '' ? 0 : num(bill) - est;
        note.textContent = Math.abs(gap) >= 1 ? `Bill ₹${fmt(num(bill))} differs by ₹${fmt(Math.abs(gap))}` : 'Value of goods received';
        note.classList.toggle('--warn', Math.abs(gap) >= 1);
    }
    form.addEventListener('click', e => {
        const b = e.target.closest('[data-all]'); if (!b) return;
        const line = b.closest('[data-line]'); line.querySelector('[data-recv]').value = line.dataset.due; update();
    });
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
})();
</script>
@endpush
</x-admin-layout>
