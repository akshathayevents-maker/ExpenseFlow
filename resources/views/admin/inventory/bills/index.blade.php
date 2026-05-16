<x-admin-layout title="Uploaded Bills">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Uploaded Bills</h4>
        <p class="text-muted mb-0 small">Invoice / bill upload history and import status</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inventory.items.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-boxes me-1"></i>Inventory Items
        </a>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-1"></i>Upload Bill
        </button>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        {{ session('warning') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <input type="text" name="vendor" class="form-control form-control-sm"
                    placeholder="Vendor name…" value="{{ request('vendor') }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\InventoryBillUpload::statusLabels() as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.inventory.bills.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Vendor</th>
                        <th>Invoice No.</th>
                        <th>Invoice Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Status</th>
                        <th>Uploaded By</th>
                        <th>Uploaded At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bills as $bill)
                        @php
                            $statusColor = \App\Models\InventoryBillUpload::statuses()[$bill->status] ?? 'secondary';
                            $statusLabel = \App\Models\InventoryBillUpload::statusLabels()[$bill->status] ?? ucfirst($bill->status);
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $bill->vendor_name ?: '—' }}</td>
                            <td class="font-monospace small">{{ $bill->invoice_number ?: '—' }}</td>
                            <td class="small">{{ $bill->invoice_date?->format('d M Y') ?? '—' }}</td>
                            <td class="text-end">{{ $bill->total_amount > 0 ? '₹' . number_format($bill->total_amount, 2) : '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $bill->items->count() }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="small text-muted">{{ optional($bill->uploader)->name ?? '—' }}</td>
                            <td class="small text-muted" style="white-space:nowrap">{{ $bill->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-end" style="white-space:nowrap">
                                <a href="{{ route('admin.inventory.bills.show', $bill) }}"
                                   class="btn btn-sm btn-outline-primary" title="Review">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if ($bill->canDelete())
                                    <form method="POST"
                                          action="{{ route('admin.inventory.bills.destroy', $bill) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this bill upload?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>
                                No bills uploaded yet.
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        <i class="bi bi-cloud-upload me-1"></i>Upload First Bill
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($bills->hasPages())
        <div class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between">
            <div class="text-muted small">{{ $bills->total() }} bill(s)</div>
            {{ $bills->links() }}
        </div>
    @endif
</div>

{{-- Upload Modal --}}
@include('admin.inventory.bills._upload-modal')

</x-admin-layout>
