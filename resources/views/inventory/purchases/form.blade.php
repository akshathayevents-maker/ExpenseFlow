<x-admin-layout :title="$purchase ? 'Edit ' . $purchase->reference() : 'New Purchase'">
@include('inventory._styles')
@php
    $h = fn ($k, $d = null) => old($k, $purchase?->{$k} ?? ($header[$k] ?? $d));
    $date = old('purchase_date', $purchase?->purchase_date?->toDateString() ?? ($header['purchase_date'] ?? now()->toDateString()));
@endphp
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ $purchase ? route('inventory.purchases.show', $purchase) : route('inventory.purchases.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
        <h1>{{ $purchase ? 'Edit purchase ' . $purchase->reference() : 'Bulk purchase entry' }}</h1>
    </div>

    @if($errors->any())
        <div class="ic-err"><strong>Please fix:</strong><ul>@foreach(collect($errors->all())->unique()->take(12) as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    @if($bill)
        <div class="ic-banner">
            <i class="bi bi-file-earmark-text"></i>
            <span style="flex:1">From uploaded bill <strong>{{ $bill->original_filename }}</strong>.
                Check every row: suggested items below 85% are not selected for you. Bill total: <strong>₹{{ number_format((float) $bill->total_amount, 2) }}</strong>.</span>
            <a class="ic-btn ic-btn--sm" target="_blank" href="{{ route('admin.inventory.bills.file', $bill) }}"><i class="bi bi-eye"></i> View bill</a>
        </div>
    @endif

    <form method="POST" data-ic-form action="{{ $purchase ? route('inventory.purchases.update', $purchase) : route('inventory.purchases.store') }}">
        @csrf
        @if($purchase) @method('PUT') @endif
        <input type="hidden" name="client_token" value="{{ old('client_token', $token) }}">
        @if($bill)<input type="hidden" name="bill_id" value="{{ $bill->id }}">@endif

        <div class="ic-card">
            <div class="ic-fields">
                <div>
                    <label class="ic-label" for="purchase_date">Purchase date</label>
                    <input class="ic-input" type="date" id="purchase_date" name="purchase_date" value="{{ $date }}" required>
                </div>
                <div>
                    <label class="ic-label" for="vendor_id">Supplier</label>
                    <select class="ic-select" id="vendor_id" name="vendor_id">
                        <option value="">— Other / type below —</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" @selected((string) $h('vendor_id') === (string) $v->id)>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ic-label" for="supplier_name">Shop name (if not in list)</label>
                    <input class="ic-input" id="supplier_name" name="supplier_name" value="{{ $h('supplier_name') }}" maxlength="150">
                </div>
                <div>
                    <label class="ic-label" for="invoice_number">Bill / invoice no.</label>
                    <input class="ic-input" id="invoice_number" name="invoice_number" value="{{ $h('invoice_number') }}" maxlength="100">
                </div>
            </div>
            <details style="margin-top:12px" @if($h('supplier_reference') || $h('payment_mode') || $h('notes')) open @endif>
                <summary class="ic-note" style="cursor:pointer;font-weight:700">More details (payment, reference, notes)</summary>
                <div class="ic-fields" style="margin-top:12px">
                    <div>
                        <label class="ic-label" for="supplier_reference">Supplier reference</label>
                        <input class="ic-input" id="supplier_reference" name="supplier_reference" value="{{ $h('supplier_reference') }}" maxlength="100">
                    </div>
                    <div>
                        <label class="ic-label" for="payment_mode">Payment</label>
                        <select class="ic-select" id="payment_mode" name="payment_mode">
                            <option value="">—</option>
                            @foreach(\App\Models\Purchase::PAYMENT_MODES as $k => $l)
                                <option value="{{ $k }}" @selected($h('payment_mode') === $k)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ic-label" for="amount_paid">Amount paid (₹)</label>
                        <input class="ic-input" type="number" step="0.01" min="0" id="amount_paid" name="amount_paid" value="{{ $h('amount_paid') }}">
                    </div>
                    <div>
                        <label class="ic-label" for="discount_amount">Bill discount (₹)</label>
                        <input class="ic-input" type="number" step="0.01" min="0" id="discount_amount" name="discount_amount" value="{{ $h('discount_amount') }}">
                    </div>
                    <div style="grid-column:1/-1">
                        <label class="ic-label" for="notes">Notes</label>
                        <input class="ic-input" id="notes" name="notes" value="{{ $h('notes') }}" maxlength="1000">
                    </div>
                </div>
            </details>
        </div>

        <div class="ic-card">
            <h2>Items bought</h2>
            @include('inventory._grid', [
                'mode' => 'purchase', 'catalog' => $catalog, 'lines' => $lines,
                'storageKey' => $purchase ? null : 'ic-purchase-draft-' . auth()->id(),
                'vendorSelect' => '#vendor_id',
            ])
        </div>

        <div class="ic-sticky">
            <div class="ic-total">
                <small>Grand total</small>
                ₹<span id="icGrand">0.00</span>
                <span class="ic-note" id="icTotals" style="font-weight:600;font-size:.8rem;display:block"></span>
                @if(! empty($header['bill_total']))
                    <span class="ic-note" id="icBillCheck" style="font-size:.8rem;display:block"></span>
                @endif
            </div>
            @if(! $purchase || $purchase->status === 'draft')
                <button type="submit" name="action" value="draft" class="ic-btn"><i class="bi bi-save"></i> Save draft</button>
                <button type="submit" name="action" value="submit" class="ic-btn ic-btn--gold"><i class="bi bi-send"></i> Submit for approval</button>
            @else
                <button type="submit" name="action" value="draft" class="ic-btn ic-btn--gold"><i class="bi bi-save"></i> Save changes</button>
            @endif
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const billTotal = {{ (float) ($header['bill_total'] ?? 0) }};
    const disc = document.getElementById('discount_amount');
    let last = {subtotal: 0, tax: 0, rows: 0};
    const fmt = n => (Math.round(n * 100) / 100).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    function paint() {
        const d = parseFloat(disc.value) || 0;
        const grand = Math.max(0, last.subtotal + last.tax - d);
        document.getElementById('icGrand').textContent = fmt(grand);
        document.getElementById('icTotals').textContent = last.rows + ' row(s) · items ₹' + fmt(last.subtotal) + (last.tax ? ' · tax ₹' + fmt(last.tax) : '') + (d ? ' · discount ₹' + fmt(d) : '');
        const bc = document.getElementById('icBillCheck');
        if (bc && billTotal) {
            const diff = Math.round((grand - billTotal) * 100) / 100;
            bc.textContent = Math.abs(diff) < 1 ? '✓ Matches bill total' : 'Differs from bill total by ₹' + fmt(diff);
            bc.style.color = Math.abs(diff) < 1 ? 'var(--ef-emerald)' : 'var(--ef-danger)';
        }
    }
    document.addEventListener('ic-grid-total', e => { last = e.detail; paint(); });
    disc.addEventListener('input', paint);
})();
</script>
@endpush
</x-admin-layout>
