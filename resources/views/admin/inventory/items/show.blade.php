<x-admin-layout title="{{ $item->name }}">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.inventory.items.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">{{ $item->name }}</h4>
            <p class="text-muted mb-0 small">{{ $item->category->name }} · {{ $item->unit }}
                @if($item->sku) · <span class="font-monospace">{{ $item->sku }}</span> @endif
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($item->isOutOfStock())
            <span class="badge bg-danger py-2 px-3">OUT OF STOCK</span>
        @elseif($item->isLowStock())
            <span class="badge bg-warning text-dark py-2 px-3">LOW STOCK</span>
        @endif
        <a href="{{ route('admin.inventory.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Left: Stock info + Transact --}}
    <div class="col-lg-4">
        {{-- Current stock card --}}
        <div class="card border-0 shadow-sm mb-3 {{ $item->isOutOfStock() ? 'border-danger border' : ($item->isLowStock() ? 'border-warning border' : '') }}">
            <div class="card-body text-center py-4">
                <div class="text-muted small mb-1">Current Stock</div>
                <div class="display-5 fw-bold {{ $item->isOutOfStock() ? 'text-danger' : ($item->isLowStock() ? 'text-warning' : 'text-success') }}">
                    {{ number_format($item->current_stock, 3) }}
                </div>
                <div class="text-muted">{{ $item->unit }}</div>
                <div class="row g-2 mt-3 text-start">
                    <div class="col-6">
                        <div class="text-muted small">Min Stock</div>
                        <div class="fw-semibold">{{ $item->minimum_stock }} {{ $item->unit }}</div>
                    </div>
                    @if($item->maximum_stock)
                    <div class="col-6">
                        <div class="text-muted small">Max Stock</div>
                        <div class="fw-semibold">{{ $item->maximum_stock }} {{ $item->unit }}</div>
                    </div>
                    @endif
                    @if($item->average_cost)
                    <div class="col-6">
                        <div class="text-muted small">Avg Cost</div>
                        <div class="fw-semibold">₹{{ number_format($item->average_cost, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Est. Value</div>
                        <div class="fw-semibold text-primary">₹{{ number_format($item->estimatedValue(), 2) }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($activeAlerts->isNotEmpty())
        <div class="alert alert-warning border-0 shadow-sm mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>{{ $activeAlerts->count() }} active alert(s)</strong>
            @foreach($activeAlerts as $alert)
                <div class="small mt-1">{{ ucwords(str_replace('_', ' ', $alert->alert_type)) }} — {{ $alert->created_at->diffForHumans() }}</div>
            @endforeach
        </div>
        @endif

        {{-- Stock transaction form --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-arrow-left-right me-1 text-primary"></i> Record Transaction
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.inventory.items.transact', $item) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="">Select…</option>
                            <option value="purchase">Purchase (add stock)</option>
                            <option value="usage">Usage (deduct)</option>
                            <option value="wastage">Wastage (deduct)</option>
                            <option value="transfer">Transfer (deduct)</option>
                            <option value="adjustment">Adjustment (set new qty)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Quantity ({{ $item->unit }}) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control form-control-sm"
                               min="0.001" step="0.001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Unit Cost (₹)</label>
                        <input type="number" name="unit_cost" class="form-control form-control-sm"
                               min="0" step="0.01" placeholder="For purchases">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Record Transaction</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: Transaction history --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-clock-history me-1 text-primary"></i> Stock History</span>
                <form method="GET" class="d-flex gap-2">
                    <select name="type" class="form-select form-select-sm" style="width:auto">
                        <option value="">All Types</option>
                        @foreach(['purchase','usage','adjustment','wastage','transfer'] as $t)
                            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-secondary">Filter</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Notes</th>
                                <th class="text-end">Before</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">After</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                            @php $colors = \App\Models\InventoryTransaction::typeColors(); $color = $colors[$txn->type] ?? 'secondary'; @endphp
                            <tr>
                                <td class="small text-nowrap">
                                    {{ $txn->created_at->format('d M Y') }}<br>
                                    <span class="text-muted">{{ $txn->created_at->format('h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                          style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px">
                                        {{ $txn->type }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $txn->notes ?? '—' }}</td>
                                <td class="text-end small text-muted">{{ $txn->balance_before }}</td>
                                <td class="text-end fw-semibold">
                                    @if($txn->isAddition())
                                        <span class="text-success">+{{ $txn->quantity }}</span>
                                    @else
                                        <span class="text-danger">−{{ $txn->quantity }}</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ $txn->balance_after }}</td>
                                <td class="small text-muted">{{ $txn->creator->name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No transactions yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
            <div class="card-footer bg-transparent border-top">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</div>
</x-admin-layout>
