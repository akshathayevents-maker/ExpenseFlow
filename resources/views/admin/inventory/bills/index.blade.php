<x-admin-layout title="Uploaded Bills">

<x-ds.hero eyebrow="Inventory" title="Uploaded Bills"
    :meta="[['icon' => 'bi-file-earmark-text', 'text' => 'Invoice & bill upload history and import status']]">
    <x-slot:actions>
        <a href="{{ route('admin.inventory.items.index') }}" class="ef-btn">
            <i class="bi bi-boxes"></i> Inventory Items
        </a>
        <button type="button" class="ef-btn ef-btn-dark" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload"></i> Upload Bill
        </button>
    </x-slot:actions>
</x-ds.hero>

{{-- Flash handled by global toast in admin-layout --}}
@endif

{{-- Filters --}}
<x-ds.card style="margin-bottom:14px">
    <form method="GET" class="ef-an-filter">
        <div class="ef-an-filter-field">
            <input type="text" name="vendor" class="ef-input" placeholder="Vendor name…" value="{{ request('vendor') }}">
        </div>
        <div class="ef-an-filter-field">
            <select name="status" class="ef-select">
                <option value="">All Statuses</option>
                @foreach(\App\Models\InventoryBillUpload::statusLabels() as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="ef-an-filter-actions">
            <button type="submit" class="ef-btn ef-btn-dark">Filter</button>
            <a href="{{ route('admin.inventory.bills.index') }}" class="ef-btn">Reset</a>
        </div>
    </form>
</x-ds.card>

@php
$statusDsColors = [
    'success'   => 'background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);color:var(--ef-emerald)',
    'warning'   => 'background:rgba(216,154,61,.1);border:1px solid rgba(216,154,61,.2);color:var(--ef-amber)',
    'danger'    => 'background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);color:var(--ef-danger)',
    'secondary' => 'background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);color:#64748b',
    'primary'   => 'background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);color:#3b82f6',
    'info'      => 'background:rgba(13,148,136,.08);border:1px solid rgba(13,148,136,.15);color:var(--ef-teal)',
];
@endphp

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>Invoice No.</th>
                    <th>Invoice Date</th>
                    <th class="r">Total</th>
                    <th style="text-align:center">Items</th>
                    <th style="text-align:center">Status</th>
                    <th>Uploaded By</th>
                    <th>Uploaded At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                @php
                    $statusColor = \App\Models\InventoryBillUpload::statuses()[$bill->status] ?? 'secondary';
                    $statusLabel = \App\Models\InventoryBillUpload::statusLabels()[$bill->status] ?? ucfirst($bill->status);
                    $badgeStyle  = $statusDsColors[$statusColor] ?? $statusDsColors['secondary'];
                @endphp
                <tr>
                    <td style="font-weight:600">{{ $bill->vendor_name ?: '—' }}</td>
                    <td style="font-family:monospace;font-size:.82rem">{{ $bill->invoice_number ?: '—' }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $bill->invoice_date?->format('d M Y') ?? '—' }}</td>
                    <td class="r fw">{{ $bill->total_amount > 0 ? '₹' . number_format($bill->total_amount, 2) : '—' }}</td>
                    <td style="text-align:center">
                        <span style="background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);border-radius:5px;color:#64748b;font-size:.72rem;font-weight:700;padding:2px 8px">{{ $bill->items->count() }}</span>
                    </td>
                    <td style="text-align:center">
                        <span style="{{ $badgeStyle }};border-radius:5px;font-size:.72rem;font-weight:700;padding:2px 8px;text-transform:uppercase">{{ $statusLabel }}</span>
                    </td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ optional($bill->uploader)->name ?? '—' }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem;white-space:nowrap">{{ $bill->created_at->format('d M Y, h:i A') }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        <div style="display:flex;gap:6px;justify-content:flex-end">
                            <a href="{{ route('admin.inventory.bills.show', $bill) }}" class="ef-btn ef-btn-icon" title="Review">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($bill->canDelete())
                            <form method="POST" action="{{ route('admin.inventory.bills.destroy', $bill) }}" style="display:inline"
                                  onsubmit="return confirm('Delete this bill upload?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="ef-btn ef-btn-icon" style="color:var(--ef-danger)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--ef-faint)">
                        <i class="bi bi-file-earmark-text" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                        No bills uploaded yet.
                        <div style="margin-top:12px">
                            <button type="button" class="ef-btn ef-btn-dark" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-cloud-upload"></i> Upload First Bill
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())
    <div style="padding:12px 18px;border-top:1px solid var(--ef-border);display:flex;align-items:center;justify-content:space-between">
        <div style="color:var(--ef-faint);font-size:.82rem">{{ $bills->total() }} bill(s)</div>
        {{ $bills->links() }}
    </div>
    @endif
</x-ds.card>

@include('admin.inventory.bills._upload-modal')

</x-admin-layout>
