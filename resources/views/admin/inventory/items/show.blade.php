<x-admin-layout title="{{ $item->name }}">

@push('styles')
<style>
.ef-inv-show-split { display: grid; gap: 14px; grid-template-columns: 280px 1fr; align-items: start; }
.ef-inv-stock-box {
    background: var(--ef-hero-grad);
    border-radius: var(--ef-radius);
    padding: 24px 20px;
    text-align: center;
    margin-bottom: 14px;
}
.ef-inv-stock-label { color: var(--ef-on-dark-muted); font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 8px; }
.ef-inv-stock-val { font-size: 2.5rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; margin-bottom: 4px; }
.ef-inv-stock-val.--ok   { color: var(--ef-on-dark-gold); }
.ef-inv-stock-val.--low  { color: #f6c86b; }
.ef-inv-stock-val.--oos  { color: #f87171; }
.ef-inv-stock-unit { color: var(--ef-on-dark-muted); font-size: .84rem; }
.ef-inv-stock-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 16px; text-align: left; }
.ef-inv-stock-meta-label { color: var(--ef-on-dark-muted); font-size: .68rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
.ef-inv-stock-meta-val { color: var(--ef-on-dark); font-size: .88rem; font-weight: 680; }
.ef-inv-txn-type { border-radius: 5px; font-size: .68rem; font-weight: 700; letter-spacing: .06em; padding: 2px 8px; text-transform: uppercase; white-space: nowrap; }
.ef-inv-txn-type.--purchase   { background: rgba(15,123,95,.1); color: var(--ef-emerald); }
.ef-inv-txn-type.--usage      { background: rgba(184,137,62,.1); color: var(--ef-gold); }
.ef-inv-txn-type.--wastage    { background: rgba(220,53,69,.08); color: var(--ef-danger); }
.ef-inv-txn-type.--adjustment { background: rgba(100,116,139,.1); color: #64748b; }
.ef-inv-txn-type.--transfer   { background: rgba(13,148,136,.1); color: var(--ef-teal); }
@media (max-width: 991.98px) { .ef-inv-show-split { grid-template-columns: 1fr; } }
</style>
@endpush

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-top:8px">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('admin.inventory.items.index') }}" class="ef-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 style="font-size:1.25rem;font-weight:760;color:var(--ef-ink);margin:0;letter-spacing:-.02em">{{ $item->name }}</h1>
            <p style="color:var(--ef-faint);font-size:.82rem;margin:2px 0 0">
                {{ $item->category->name }} &middot; {{ $item->unit }}
                @if($item->sku) &middot; <span style="font-family:monospace">{{ $item->sku }}</span>@endif
            </p>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        @if($item->isOutOfStock())
            <span style="background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.2);border-radius:6px;color:var(--ef-danger);font-size:.72rem;font-weight:700;letter-spacing:.06em;padding:4px 10px;text-transform:uppercase">OUT OF STOCK</span>
        @elseif($item->isLowStock())
            <span style="background:rgba(216,154,61,.1);border:1px solid rgba(216,154,61,.2);border-radius:6px;color:var(--ef-amber);font-size:.72rem;font-weight:700;letter-spacing:.06em;padding:4px 10px;text-transform:uppercase">LOW STOCK</span>
        @endif
        <a href="{{ route('admin.inventory.items.edit', $item) }}" class="ef-btn">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="ef-inv-show-split">
    {{-- Left sidebar --}}
    <div>
        <div class="ef-inv-stock-box">
            <div class="ef-inv-stock-label">Current Stock</div>
            <div class="ef-inv-stock-val {{ $item->isOutOfStock() ? '--oos' : ($item->isLowStock() ? '--low' : '--ok') }}">
                {{ number_format($item->current_stock, 3) + 0 }}
            </div>
            <div class="ef-inv-stock-unit">{{ $item->unit }}</div>
            <div class="ef-inv-stock-meta">
                <div>
                    <div class="ef-inv-stock-meta-label">Min Stock</div>
                    <div class="ef-inv-stock-meta-val">{{ $item->minimum_stock }} {{ $item->unit }}</div>
                </div>
                @if($item->maximum_stock)
                <div>
                    <div class="ef-inv-stock-meta-label">Max Stock</div>
                    <div class="ef-inv-stock-meta-val">{{ $item->maximum_stock }} {{ $item->unit }}</div>
                </div>
                @endif
                @if($item->average_cost)
                <div>
                    <div class="ef-inv-stock-meta-label">Avg Cost</div>
                    <div class="ef-inv-stock-meta-val">₹{{ number_format($item->average_cost, 2) }}</div>
                </div>
                <div>
                    <div class="ef-inv-stock-meta-label">Est. Value</div>
                    <div class="ef-inv-stock-meta-val">₹{{ number_format($item->estimatedValue(), 2) }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($activeAlerts->isNotEmpty())
        <div style="background:rgba(255,200,0,.06);border:1px solid rgba(255,200,0,.28);border-radius:var(--ef-radius);padding:10px 14px;font-size:.82rem;color:#7d6400;margin-bottom:14px">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>{{ $activeAlerts->count() }} active alert(s)</strong>
            @foreach($activeAlerts as $alert)
                <div style="margin-top:4px">{{ ucwords(str_replace('_', ' ', $alert->alert_type)) }} — {{ $alert->created_at->diffForHumans() }}</div>
            @endforeach
        </div>
        @endif

        {{-- Stock transaction form --}}
        <x-ds.card title="Record Transaction">
            <form method="POST" action="{{ route('admin.inventory.items.transact', $item) }}">
                @csrf
                <div style="margin-bottom:12px">
                    <label class="ef-label">Type <span style="color:var(--ef-danger)">*</span></label>
                    <select name="type" class="ef-select" required>
                        <option value="">Select…</option>
                        <option value="purchase">Purchase (add stock)</option>
                        <option value="usage">Usage (deduct)</option>
                        <option value="wastage">Wastage (deduct)</option>
                        <option value="transfer">Transfer (deduct)</option>
                        <option value="adjustment">Adjustment (set new qty)</option>
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label class="ef-label">Quantity ({{ $item->unit }}) <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" name="quantity" class="ef-input" min="0.001" step="0.001" required>
                </div>
                <div style="margin-bottom:12px">
                    <label class="ef-label">Unit Cost (₹)</label>
                    <input type="number" name="unit_cost" class="ef-input" min="0" step="0.01" placeholder="For purchases">
                </div>
                <div style="margin-bottom:16px">
                    <label class="ef-label">Notes</label>
                    <textarea name="notes" class="ef-textarea" rows="2" style="min-height:60px"></textarea>
                </div>
                <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                    Record Transaction
                </button>
            </form>
        </x-ds.card>
    </div>

    {{-- Right: Stock history --}}
    <x-ds.card :no-pad="true">
        <x-slot:head_right>
            <div style="display:flex;align-items:center;justify-content:space-between;width:100%">
                <x-ds.section-head title="Stock History" />
                <form method="GET" style="display:flex;gap:6px;align-items:center">
                    <select name="type" class="ef-select" style="min-height:34px;padding:5px 10px;font-size:.84rem;min-width:120px">
                        <option value="">All Types</option>
                        @foreach(['purchase','usage','adjustment','wastage','transfer'] as $t)
                            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    <button class="ef-btn" style="height:34px;padding:0 12px;font-size:.84rem">Filter</button>
                </form>
            </div>
        </x-slot:head_right>

        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Notes</th>
                        <th class="r">Before</th>
                        <th class="r">Qty</th>
                        <th class="r">After</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    <tr>
                        <td style="white-space:nowrap">
                            <div style="font-size:.86rem;font-weight:600">{{ $txn->created_at->format('d M Y') }}</div>
                            <div style="color:var(--ef-faint);font-size:.76rem">{{ $txn->created_at->format('h:i A') }}</div>
                        </td>
                        <td><span class="ef-inv-txn-type --{{ $txn->type }}">{{ $txn->type }}</span></td>
                        <td style="color:var(--ef-faint);font-size:.84rem">{{ $txn->notes ?? '—' }}</td>
                        <td class="r" style="color:var(--ef-faint);font-size:.84rem">{{ $txn->balance_before }}</td>
                        <td class="r" style="font-weight:680">
                            @if($txn->isAddition())
                                <span style="color:var(--ef-emerald)">+{{ $txn->quantity }}</span>
                            @else
                                <span style="color:var(--ef-danger)">−{{ $txn->quantity }}</span>
                            @endif
                        </td>
                        <td class="r" style="font-weight:680">{{ $txn->balance_after }}</td>
                        <td style="color:var(--ef-faint);font-size:.84rem">{{ $txn->creator->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:var(--ef-faint)">No transactions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div style="padding:12px 18px;border-top:1px solid var(--ef-border)">
            {{ $transactions->links() }}
        </div>
        @endif
    </x-ds.card>
</div>

</x-admin-layout>
