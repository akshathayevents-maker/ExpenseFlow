<x-admin-layout title="Review Bill — {{ $bill->original_filename }}">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Review Extracted Bill</h4>
        <nav aria-label="breadcrumb" class="mt-1">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.inventory.bills.index') }}">Uploaded Bills</a></li>
                <li class="breadcrumb-item active">{{ $bill->original_filename }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        @php
            $statusColor = \App\Models\InventoryBillUpload::statuses()[$bill->status] ?? 'secondary';
            $confidence  = $bill->extracted_json['ocr_confidence'] ?? null;
        @endphp
        <span class="badge bg-{{ $statusColor }} fs-6">
            {{ \App\Models\InventoryBillUpload::statusLabels()[$bill->status] ?? ucfirst($bill->status) }}
        </span>
        @if ($confidence !== null)
            @php
                $confPct   = round($confidence * 100);
                $confColor = $confPct >= 80 ? 'success' : ($confPct >= 60 ? 'warning' : 'danger');
            @endphp
            <span class="badge bg-{{ $confColor }}" title="OCR confidence">
                <i class="bi bi-cpu me-1"></i>{{ $confPct }}% confidence
            </span>
        @endif
        @if (! $bill->isImported())
            <form method="POST" action="{{ route('admin.inventory.bills.rerun-ocr', $bill) }}"
                  id="rerunOcrForm" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" id="btnRerunOcr"
                        title="Re-run OCR extraction" data-loading-text="Running OCR…">
                    <span id="rerunSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    <i class="bi bi-arrow-repeat me-1" id="rerunIcon"></i>Re-run OCR
                </button>
            </form>
        @endif
        @if ($bill->canImport())
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-box-arrow-in-down me-1"></i>Import to Inventory
            </button>
        @endif
        @if ($bill->isImported())
            <a href="{{ route('admin.inventory.items.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-boxes me-1"></i>View Inventory
            </a>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Duplicate warning --}}
@if ($duplicate)
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <strong>Possible duplicate!</strong> Invoice <code>{{ $bill->invoice_number }}</code> from
            <strong>{{ $bill->vendor_name }}</strong> was already uploaded on
            {{ $duplicate->created_at->format('d M Y') }}.
            <a href="{{ route('admin.inventory.bills.show', $duplicate) }}" class="alert-link ms-1">View existing →</a>
        </div>
    </div>
@endif

@if ($bill->extracted_json && ($bill->extracted_json['ocr_message'] ?? null))
    <div class="alert alert-info py-2 small">
        <i class="bi bi-info-circle me-1"></i>{{ $bill->extracted_json['ocr_message'] }}
    </div>
@endif

<div class="row g-3">
    {{-- ── Left: File Preview ──────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-semibold small">
                        <i class="bi bi-file-earmark me-1"></i>{{ $bill->original_filename }}
                    </span>
                    <a href="{{ route('admin.inventory.bills.file', $bill) }}" target="_blank"
                       class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:.75rem">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Full view
                    </a>
                </div>
            </div>
            <div class="card-body p-2 text-center bg-light" style="min-height:300px">
                @if ($bill->isImage())
                    <img src="{{ route('admin.inventory.bills.file', $bill) }}"
                         alt="Bill preview"
                         class="img-fluid rounded shadow-sm"
                         style="max-height:600px;object-fit:contain">
                @else
                    <div class="py-4">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:4rem"></i>
                        <p class="mt-2 text-muted small">PDF document</p>
                        <a href="{{ route('admin.inventory.bills.file', $bill) }}" target="_blank"
                           class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-eye me-1"></i>Open PDF
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-transparent py-2 small text-muted">
                <i class="bi bi-cpu me-1"></i><strong>{{ $bill->ocr_provider ?? 'manual' }}</strong>
                @if ($confidence !== null)
                    &nbsp;·&nbsp;
                    <span class="text-{{ $confColor ?? 'muted' }}">{{ $confPct ?? '—' }}% confidence</span>
                @endif
                &nbsp;·&nbsp; Uploaded {{ $bill->created_at->diffForHumans() }} by {{ optional($bill->uploader)->name }}
            </div>
        </div>
    </div>

    {{-- ── Right: Editable Review Form ─────────────────────────────────────── --}}
    <div class="col-lg-8">
        @if ($bill->isImported())
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                This bill has been imported. All {{ $bill->items->where('imported', true)->count() }} item(s) are now in inventory.
                Reviewed by {{ optional($bill->reviewer)->name ?? '—' }}.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.inventory.bills.update', $bill) }}" id="reviewForm">
            @csrf @method('PUT')

            {{-- Invoice Header --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent py-2">
                    <span class="fw-semibold"><i class="bi bi-receipt me-1"></i>Invoice Details</span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Vendor / Supplier</label>
                            <input type="text" name="vendor_name" class="form-control form-control-sm"
                                   value="{{ old('vendor_name', $bill->vendor_name) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control form-control-sm"
                                   value="{{ old('invoice_number', $bill->invoice_number) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control form-control-sm"
                                   value="{{ old('invoice_date', $bill->invoice_date?->format('Y-m-d')) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">GST Number</label>
                            <input type="text" name="gst_number" class="form-control form-control-sm"
                                   value="{{ old('gst_number', $bill->gst_number) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Notes</label>
                            <input type="text" name="notes" class="form-control form-control-sm"
                                   value="{{ old('notes', $bill->notes) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Subtotal (₹)</label>
                            <input type="number" name="subtotal" class="form-control form-control-sm" step="0.01" min="0"
                                   value="{{ old('subtotal', number_format((float)$bill->subtotal, 2, '.', '')) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Tax Amount (₹)</label>
                            <input type="number" name="tax_amount" class="form-control form-control-sm" step="0.01" min="0"
                                   value="{{ old('tax_amount', number_format((float)$bill->tax_amount, 2, '.', '')) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm fw-semibold">Grand Total (₹)</label>
                            <input type="number" name="total_amount" class="form-control form-control-sm fw-bold" step="0.01" min="0"
                                   value="{{ old('total_amount', number_format((float)$bill->total_amount, 2, '.', '')) }}"
                                   {{ $bill->isImported() ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i>Line Items</span>
                    @unless ($bill->isImported())
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnAddRow">
                            <i class="bi bi-plus-circle me-1"></i>Add Row
                        </button>
                    @endunless
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:160px">Item Name <span class="text-danger">*</span></th>
                                    <th style="min-width:130px">Match to Existing</th>
                                    <th style="width:70px">Qty <span class="text-danger">*</span></th>
                                    <th style="width:70px">Unit</th>
                                    <th style="width:90px">Unit Price</th>
                                    <th style="width:70px">Tax %</th>
                                    <th style="width:90px">Total</th>
                                    <th style="min-width:120px">Category</th>
                                    @unless ($bill->isImported())<th style="width:40px"></th>@endunless
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @foreach ($bill->items as $idx => $row)
                                    <tr class="{{ $row->imported ? 'table-success' : '' }}">
                                        <td>
                                            <input type="text" name="items[{{ $idx }}][item_name]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $row->item_name }}" required
                                                   {{ $row->imported ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx }}][inventory_item_id]"
                                                    class="form-select form-select-sm item-match-select"
                                                    {{ $row->imported ? 'disabled' : '' }}>
                                                <option value="">— Create new item —</option>
                                                @foreach ($inventoryItems as $inv)
                                                    <option value="{{ $inv->id }}"
                                                        {{ ($matchMap[$row->id] ?? null) == $inv->id ? 'selected' : '' }}>
                                                        {{ $inv->name }} {{ $inv->sku ? "({$inv->sku})" : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $idx }}][quantity]"
                                                   class="form-control form-control-sm item-qty"
                                                   step="0.001" min="0.001"
                                                   value="{{ (float)$row->quantity }}" required
                                                   {{ $row->imported ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $idx }}][unit]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $row->unit }}"
                                                   {{ $row->imported ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $idx }}][unit_price]"
                                                   class="form-control form-control-sm item-price"
                                                   step="0.01" min="0"
                                                   value="{{ (float)$row->unit_price }}" required
                                                   {{ $row->imported ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $idx }}][tax_percent]"
                                                   class="form-control form-control-sm item-tax"
                                                   step="0.01" min="0" max="100"
                                                   value="{{ (float)$row->tax_percent }}"
                                                   {{ $row->imported ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $idx }}][total]"
                                                   class="form-control form-control-sm item-total"
                                                   step="0.01" min="0"
                                                   value="{{ (float)$row->total }}" required
                                                   {{ $row->imported ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx }}][category_id]"
                                                    class="form-select form-select-sm"
                                                    {{ $row->imported ? 'disabled' : '' }}>
                                                <option value="">— None —</option>
                                                @foreach ($categories as $catId => $catName)
                                                    <option value="{{ $catId }}"
                                                        {{ $row->category_id == $catId ? 'selected' : '' }}>
                                                        {{ $catName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        @unless ($bill->isImported())
                                        <td class="text-center">
                                            @if (! $row->imported)
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"
                                                        title="Remove">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <i class="bi bi-check-circle-fill text-success" title="Imported"></i>
                                            @endif
                                        </td>
                                        @endunless
                                    </tr>
                                @endforeach

                                @if ($bill->items->isEmpty())
                                    <tr id="noItemsRow">
                                        <td colspan="9" class="text-center text-muted py-4 small">
                                            No items extracted. Click "Add Row" to enter manually.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @unless ($bill->isImported())
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
                        <i class="bi bi-save me-1"></i>Save Review
                    </button>
                    @if ($bill->canImport())
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-box-arrow-in-down me-1"></i>Import to Inventory
                        </button>
                    @endif
                </div>
            @endunless
        </form>
    </div>
</div>

{{-- Debug Panel --}}
@if ($bill->extracted_json)
<div class="mt-3">
    <div class="accordion accordion-flush border rounded" id="debugAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 small text-muted" type="button"
                        data-bs-toggle="collapse" data-bs-target="#debugBody">
                    <i class="bi bi-bug me-2"></i>OCR Debug Info
                    @if ($confidence !== null)
                        &nbsp;— {{ $bill->ocr_provider ?? 'tesseract' }}, {{ $confPct }}% confidence
                    @endif
                </button>
            </h2>
            <div id="debugBody" class="accordion-collapse collapse">
                <div class="accordion-body py-2 px-3">
                    @php $json = $bill->extracted_json; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1 small fw-semibold text-muted">Extracted Invoice Fields</p>
                            <pre class="bg-light rounded p-2 small" style="font-size:.75rem;max-height:250px;overflow:auto">{{ json_encode([
                                'vendor_name'    => $json['vendor_name']    ?? null,
                                'invoice_number' => $json['invoice_number'] ?? null,
                                'invoice_date'   => $json['invoice_date']   ?? null,
                                'gst_number'     => $json['gst_number']     ?? null,
                                'subtotal'       => $json['subtotal']       ?? 0,
                                'tax_amount'     => $json['tax_amount']     ?? 0,
                                'total_amount'   => $json['total_amount']   ?? 0,
                                'ocr_provider'   => $json['ocr_provider']   ?? null,
                                'ocr_confidence' => $json['ocr_confidence'] ?? null,
                                'item_count'     => count($json['items']    ?? []),
                            ], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 small fw-semibold text-muted">OCR Raw Text</p>
                            <pre class="bg-light rounded p-2 small" style="font-size:.72rem;max-height:250px;overflow:auto;white-space:pre-wrap">{{ $json['full_text'] ?? '(not stored)' }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Import Confirmation Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-box-arrow-in-down me-2"></i>Import to Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will add all <strong>{{ $bill->items->where('imported', false)->count() }}</strong> unimported item(s) to inventory.</p>
                <ul class="small text-muted mb-2">
                    <li>Items matched to existing inventory → stock will be increased.</li>
                    <li>Items without a match → new inventory items will be created.</li>
                </ul>
                <p class="mb-0 text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.inventory.bills.import', $bill) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" data-loading-text="Importing…">
                        <i class="bi bi-box-arrow-in-down me-1"></i>Yes, Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Re-run OCR button spinner
    document.getElementById('rerunOcrForm')?.addEventListener('submit', function () {
        const btn    = document.getElementById('btnRerunOcr');
        const icon   = document.getElementById('rerunIcon');
        const spinner = document.getElementById('rerunSpinner');
        if (btn)    btn.disabled = true;
        if (icon)   icon.classList.add('d-none');
        if (spinner) spinner.classList.remove('d-none');
        if (btn)    btn.childNodes[btn.childNodes.length - 1].textContent = ' Running OCR…';
    });

    let rowIndex = {{ $bill->items->count() }};

    const inventoryOptions = `@foreach ($inventoryItems as $inv)
        <option value="{{ $inv->id }}">{{ addslashes($inv->name) }}{{ $inv->sku ? ' (' . addslashes($inv->sku) . ')' : '' }}</option>
    @endforeach`;

    const categoryOptions = `@foreach ($categories as $catId => $catName)
        <option value="{{ $catId }}">{{ addslashes($catName) }}</option>
    @endforeach`;

    function makeRow(idx) {
        return `<tr>
            <td><input type="text" name="items[${idx}][item_name]" class="form-control form-control-sm" required placeholder="Item name"></td>
            <td>
                <select name="items[${idx}][inventory_item_id]" class="form-select form-select-sm item-match-select">
                    <option value="">— Create new item —</option>
                    ${inventoryOptions}
                </select>
            </td>
            <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm item-qty" step="0.001" min="0.001" value="1" required></td>
            <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm" placeholder="pcs"></td>
            <td><input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0" required></td>
            <td><input type="number" name="items[${idx}][tax_percent]" class="form-control form-control-sm item-tax" step="0.01" min="0" max="100" value="0"></td>
            <td><input type="number" name="items[${idx}][total]" class="form-control form-control-sm item-total" step="0.01" min="0" value="0" required></td>
            <td>
                <select name="items[${idx}][category_id]" class="form-select form-select-sm">
                    <option value="">— None —</option>
                    ${categoryOptions}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }

    // Add row
    document.getElementById('btnAddRow')?.addEventListener('click', function () {
        document.getElementById('noItemsRow')?.remove();
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', makeRow(rowIndex++));
        attachRowListeners(document.querySelector('#itemsBody tr:last-child'));
    });

    // Remove row
    document.getElementById('itemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-row');
        if (!btn) return;
        const row = btn.closest('tr');
        row.remove();
        if (!document.querySelector('#itemsBody tr')) {
            document.getElementById('itemsBody').innerHTML =
                '<tr id="noItemsRow"><td colspan="9" class="text-center text-muted py-4 small">No items. Click "Add Row" to enter manually.</td></tr>';
        }
    });

    // Auto-calculate total = qty * price * (1 + tax/100)
    function attachRowListeners(row) {
        if (!row) return;
        ['.item-qty', '.item-price', '.item-tax'].forEach(sel => {
            row.querySelector(sel)?.addEventListener('input', () => recalcRow(row));
        });
    }

    function recalcRow(row) {
        const qty   = parseFloat(row.querySelector('.item-qty')?.value  || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        const tax   = parseFloat(row.querySelector('.item-tax')?.value   || 0);
        const total = qty * price * (1 + tax / 100);
        const totalInput = row.querySelector('.item-total');
        if (totalInput) totalInput.value = total.toFixed(2);
    }

    document.querySelectorAll('#itemsBody tr').forEach(attachRowListeners);
})();
</script>
@endpush
</x-admin-layout>
