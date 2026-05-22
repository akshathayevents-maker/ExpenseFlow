<x-admin-layout title="Review Bill — {{ $bill->original_filename }}">

@push('styles')
<style>
.ef-bill-review-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 14px; align-items: start; }
.ef-bill-preview-card { background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: var(--ef-radius); overflow: hidden; }
.ef-bill-preview-head { border-bottom: 1px solid var(--ef-border); padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; }
.ef-bill-preview-body { padding: 12px; background: var(--ef-bg-subtle); min-height: 300px; display: flex; align-items: center; justify-content: center; }
.ef-bill-preview-foot { border-top: 1px solid var(--ef-border); padding: 8px 14px; font-size: .76rem; color: var(--ef-faint); }
.ef-bill-inline-input  { background: var(--ef-bg-subtle); border: 1px solid var(--ef-border); border-radius: 6px; color: var(--ef-ink-2); font-size: .82rem; min-height: 32px; padding: 4px 8px; transition: border-color .15s; width: 100%; }
.ef-bill-inline-input:focus { border-color: var(--ef-gold); box-shadow: 0 0 0 3px rgba(184,137,62,.12); outline: none; }
.ef-bill-inline-input[readonly] { background: var(--ef-bg-subtle); color: var(--ef-faint); cursor: not-allowed; }
@media (max-width: 991.98px) { .ef-bill-review-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@php
$statusColor = \App\Models\InventoryBillUpload::statuses()[$bill->status] ?? 'secondary';
$statusLabel = \App\Models\InventoryBillUpload::statusLabels()[$bill->status] ?? ucfirst($bill->status);
$confidence  = $bill->extracted_json['ocr_confidence'] ?? null;
$statusDsColors = [
    'success'   => 'background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);color:var(--ef-emerald)',
    'warning'   => 'background:rgba(216,154,61,.1);border:1px solid rgba(216,154,61,.2);color:var(--ef-amber)',
    'danger'    => 'background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);color:var(--ef-danger)',
    'secondary' => 'background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);color:#64748b',
    'primary'   => 'background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);color:#3b82f6',
    'info'      => 'background:rgba(13,148,136,.08);border:1px solid rgba(13,148,136,.15);color:var(--ef-teal)',
];
$badgeStyle = $statusDsColors[$statusColor] ?? $statusDsColors['secondary'];
if ($confidence !== null) {
    $confPct   = round($confidence * 100);
    $confColor = $confPct >= 80 ? 'var(--ef-emerald)' : ($confPct >= 60 ? 'var(--ef-amber)' : 'var(--ef-danger)');
    $confBg    = $confPct >= 80 ? 'rgba(15,123,95,.1)' : ($confPct >= 60 ? 'rgba(216,154,61,.1)' : 'rgba(220,53,69,.08)');
}
@endphp

{{-- Page header --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-top:8px">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('admin.inventory.bills.index') }}" class="ef-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 style="font-size:1.25rem;font-weight:760;color:var(--ef-ink);margin:0;letter-spacing:-.02em">Review Extracted Bill</h1>
            <p style="color:var(--ef-faint);font-size:.82rem;margin:2px 0 0">{{ $bill->original_filename }}</p>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <span style="{{ $badgeStyle }};border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;text-transform:uppercase">
            {{ $statusLabel }}
        </span>
        @if($confidence !== null)
            <span style="background:{{ $confBg }};border-radius:6px;color:{{ $confColor }};font-size:.78rem;font-weight:700;padding:4px 12px" title="OCR confidence">
                <i class="bi bi-cpu me-1"></i>{{ $confPct }}% confidence
            </span>
        @endif
        @if(! $bill->isImported())
            <form method="POST" action="{{ route('admin.inventory.bills.rerun-ocr', $bill) }}" id="rerunOcrForm" style="display:inline">
                @csrf
                <button type="submit" class="ef-btn" id="btnRerunOcr" title="Re-run OCR extraction">
                    <span id="rerunSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    <i class="bi bi-arrow-repeat" id="rerunIcon"></i> Re-run OCR
                </button>
            </form>
        @endif
        @if($bill->canImport())
            <button type="button" class="ef-btn ef-btn-dark" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-box-arrow-in-down"></i> Import to Inventory
            </button>
        @endif
        @if($bill->isImported())
            <a href="{{ route('admin.inventory.items.index') }}" class="ef-btn">
                <i class="bi bi-boxes"></i> View Inventory
            </a>
        @endif
    </div>
</div>

{{-- Flash handled by global toast in admin-layout --}}

{{-- Duplicate warning --}}
@if($duplicate)
<div style="background:rgba(216,154,61,.08);border:1px solid rgba(216,154,61,.25);border-radius:var(--ef-radius);padding:10px 16px;margin-bottom:14px;font-size:.86rem;color:var(--ef-amber);display:flex;align-items:flex-start;gap:10px">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0" style="margin-top:.1rem"></i>
    <div>
        <strong>Possible duplicate!</strong> Invoice <code>{{ $bill->invoice_number }}</code> from
        <strong>{{ $bill->vendor_name }}</strong> was already uploaded on
        {{ $duplicate->created_at->format('d M Y') }}.
        <a href="{{ route('admin.inventory.bills.show', $duplicate) }}" style="color:var(--ef-amber);font-weight:600;margin-left:4px">View existing →</a>
    </div>
</div>
@endif

@if($bill->extracted_json && ($bill->extracted_json['ocr_message'] ?? null))
<div style="background:rgba(13,148,136,.06);border:1px solid rgba(13,148,136,.2);border-radius:var(--ef-radius);padding:8px 14px;margin-bottom:14px;font-size:.82rem;color:var(--ef-teal)">
    <i class="bi bi-info-circle me-1"></i>{{ $bill->extracted_json['ocr_message'] }}
</div>
@endif

<div class="ef-bill-review-grid">
    {{-- Left: File Preview --}}
    <div class="ef-bill-preview-card">
        <div class="ef-bill-preview-head">
            <span style="font-weight:600;font-size:.84rem;color:var(--ef-ink-2)">
                <i class="bi bi-file-earmark me-1"></i>{{ $bill->original_filename }}
            </span>
            <a href="{{ route('admin.inventory.bills.file', $bill) }}" target="_blank" class="ef-btn" style="font-size:.76rem;padding:2px 8px">
                <i class="bi bi-box-arrow-up-right"></i> Full view
            </a>
        </div>
        <div class="ef-bill-preview-body">
            @if($bill->isImage())
                <img src="{{ route('admin.inventory.bills.file', $bill) }}"
                     alt="Bill preview"
                     style="max-height:600px;max-width:100%;object-fit:contain;border-radius:6px">
            @else
                <div style="text-align:center;padding:2rem">
                    <i class="bi bi-file-earmark-pdf" style="font-size:4rem;color:var(--ef-danger)"></i>
                    <p style="margin:8px 0 16px;color:var(--ef-faint);font-size:.86rem">PDF document</p>
                    <a href="{{ route('admin.inventory.bills.file', $bill) }}" target="_blank" class="ef-btn" style="color:var(--ef-danger)">
                        <i class="bi bi-eye"></i> Open PDF
                    </a>
                </div>
            @endif
        </div>
        <div class="ef-bill-preview-foot">
            <i class="bi bi-cpu me-1"></i><strong>{{ $bill->ocr_provider ?? 'manual' }}</strong>
            @if($confidence !== null)
                &nbsp;·&nbsp;
                <span style="color:{{ $confColor }}">{{ $confPct ?? '—' }}% confidence</span>
            @endif
            &nbsp;·&nbsp; Uploaded {{ $bill->created_at->diffForHumans() }} by {{ optional($bill->uploader)->name }}
        </div>
    </div>

    {{-- Right: Editable Review Form --}}
    <div>
        @if($bill->isImported())
        <div style="background:rgba(15,123,95,.08);border:1px solid rgba(15,123,95,.2);border-radius:var(--ef-radius);padding:10px 16px;margin-bottom:14px;font-size:.86rem;color:var(--ef-emerald);display:flex;align-items:center;gap:10px">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            This bill has been imported. All {{ $bill->items->where('imported', true)->count() }} item(s) are now in inventory.
            Reviewed by {{ optional($bill->reviewer)->name ?? '—' }}.
        </div>
        @endif

        <form method="POST" action="{{ route('admin.inventory.bills.update', $bill) }}" id="reviewForm">
            @csrf @method('PUT')

            {{-- Invoice Details --}}
            <x-ds.card title="Invoice Details" style="margin-bottom:14px">
                <div class="ef-form-grid ef-form-grid-2">
                    <div>
                        <label class="ef-label">Vendor / Supplier</label>
                        <input type="text" name="vendor_name" class="ef-input"
                               value="{{ old('vendor_name', $bill->vendor_name) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="ef-input"
                               value="{{ old('invoice_number', $bill->invoice_number) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label">Invoice Date</label>
                        <input type="date" name="invoice_date" class="ef-input"
                               value="{{ old('invoice_date', $bill->invoice_date?->format('Y-m-d')) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label">GST Number</label>
                        <input type="text" name="gst_number" class="ef-input"
                               value="{{ old('gst_number', $bill->gst_number) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label">Notes</label>
                        <input type="text" name="notes" class="ef-input"
                               value="{{ old('notes', $bill->notes) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label">Subtotal (₹)</label>
                        <input type="number" name="subtotal" class="ef-input" step="0.01" min="0"
                               value="{{ old('subtotal', number_format((float)$bill->subtotal, 2, '.', '')) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label">Tax Amount (₹)</label>
                        <input type="number" name="tax_amount" class="ef-input" step="0.01" min="0"
                               value="{{ old('tax_amount', number_format((float)$bill->tax_amount, 2, '.', '')) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                    <div>
                        <label class="ef-label" style="font-weight:700">Grand Total (₹)</label>
                        <input type="number" name="total_amount" class="ef-input" step="0.01" min="0"
                               style="font-weight:700"
                               value="{{ old('total_amount', number_format((float)$bill->total_amount, 2, '.', '')) }}"
                               {{ $bill->isImported() ? 'readonly' : '' }}>
                    </div>
                </div>
            </x-ds.card>

            {{-- Line Items --}}
            <x-ds.card :no-pad="true" style="margin-bottom:14px">
                <x-slot:head_right>
                    <div style="display:flex;align-items:center;justify-content:space-between;width:100%">
                        <x-ds.section-head title="Line Items" />
                        @unless($bill->isImported())
                        <button type="button" class="ef-btn" id="btnAddRow">
                            <i class="bi bi-plus-circle"></i> Add Row
                        </button>
                        @endunless
                    </div>
                </x-slot:head_right>

                <div style="overflow-x:auto">
                    <table class="ef-an-trend-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width:160px">Item Name <span style="color:var(--ef-danger)">*</span></th>
                                <th style="min-width:130px">Match to Existing</th>
                                <th style="width:80px">Qty <span style="color:var(--ef-danger)">*</span></th>
                                <th style="width:70px">Unit</th>
                                <th style="width:100px">Unit Price</th>
                                <th style="width:70px">Tax %</th>
                                <th style="width:100px">Total</th>
                                <th style="min-width:120px">Category</th>
                                @unless($bill->isImported())<th style="width:40px"></th>@endunless
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @foreach($bill->items as $idx => $row)
                            <tr style="{{ $row->imported ? 'background:rgba(15,123,95,.05)' : '' }}">
                                <td>
                                    <input type="text" name="items[{{ $idx }}][item_name]"
                                           class="ef-bill-inline-input"
                                           value="{{ $row->item_name }}" required
                                           {{ $row->imported ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <select name="items[{{ $idx }}][inventory_item_id]"
                                            class="ef-bill-inline-input item-match-select"
                                            {{ $row->imported ? 'disabled' : '' }}>
                                        <option value="">— Create new item —</option>
                                        @foreach($inventoryItems as $inv)
                                            <option value="{{ $inv->id }}"
                                                {{ ($matchMap[$row->id] ?? null) == $inv->id ? 'selected' : '' }}>
                                                {{ $inv->name }} {{ $inv->sku ? "({$inv->sku})" : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][quantity]"
                                           class="ef-bill-inline-input item-qty"
                                           step="0.001" min="0.001"
                                           value="{{ (float)$row->quantity }}" required
                                           {{ $row->imported ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $idx }}][unit]"
                                           class="ef-bill-inline-input"
                                           value="{{ $row->unit }}"
                                           {{ $row->imported ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][unit_price]"
                                           class="ef-bill-inline-input item-price"
                                           step="0.01" min="0"
                                           value="{{ (float)$row->unit_price }}" required
                                           {{ $row->imported ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][tax_percent]"
                                           class="ef-bill-inline-input item-tax"
                                           step="0.01" min="0" max="100"
                                           value="{{ (float)$row->tax_percent }}"
                                           {{ $row->imported ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][total]"
                                           class="ef-bill-inline-input item-total"
                                           step="0.01" min="0"
                                           value="{{ (float)$row->total }}" required
                                           {{ $row->imported ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <select name="items[{{ $idx }}][category_id]"
                                            class="ef-bill-inline-input"
                                            {{ $row->imported ? 'disabled' : '' }}>
                                        <option value="">— None —</option>
                                        @foreach($categories as $catId => $catName)
                                            <option value="{{ $catId }}"
                                                {{ $row->category_id == $catId ? 'selected' : '' }}>
                                                {{ $catName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                @unless($bill->isImported())
                                <td style="text-align:center">
                                    @if(! $row->imported)
                                        <button type="button" class="ef-btn ef-btn-icon btn-remove-row"
                                                style="color:var(--ef-danger)" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <i class="bi bi-check-circle-fill" style="color:var(--ef-emerald)" title="Imported"></i>
                                    @endif
                                </td>
                                @endunless
                            </tr>
                            @endforeach

                            @if($bill->items->isEmpty())
                            <tr id="noItemsRow">
                                <td colspan="9" style="text-align:center;color:var(--ef-faint);padding:32px;font-size:.86rem">
                                    No items extracted. Click "Add Row" to enter manually.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </x-ds.card>

            @unless($bill->isImported())
            <div style="display:flex;gap:8px">
                <button type="submit" class="ef-btn ef-btn-dark" data-loading-text="Saving…">
                    <i class="bi bi-save"></i> Save Review
                </button>
                @if($bill->canImport())
                    <button type="button" class="ef-btn" style="color:var(--ef-emerald)" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-box-arrow-in-down"></i> Import to Inventory
                    </button>
                @endif
            </div>
            @endunless
        </form>
    </div>
</div>

{{-- OCR Debug Panel (keep Bootstrap accordion — uses BS collapse JS) --}}
@if($bill->extracted_json)
<div style="margin-top:14px">
    <div class="accordion accordion-flush border rounded" id="debugAccordion" style="border-color:var(--ef-border)!important;border-radius:var(--ef-radius)!important">
        <div class="accordion-item" style="border:none">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#debugBody"
                        style="font-size:.82rem;padding:8px 12px;background:var(--ef-bg-subtle);color:var(--ef-muted);border-radius:var(--ef-radius)">
                    <i class="bi bi-bug me-2"></i>OCR Debug Info
                    @if($confidence !== null)
                        &nbsp;— {{ $bill->ocr_provider ?? 'tesseract' }}, {{ $confPct }}% confidence
                    @endif
                </button>
            </h2>
            <div id="debugBody" class="accordion-collapse collapse">
                <div class="accordion-body py-2 px-3">
                    @php $json = $bill->extracted_json; @endphp
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div>
                            <p style="font-size:.82rem;font-weight:600;color:var(--ef-faint);margin-bottom:6px">Extracted Invoice Fields</p>
                            <pre style="background:var(--ef-bg-subtle);border-radius:6px;padding:10px;font-size:.72rem;max-height:250px;overflow:auto">{{ json_encode([
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
                        <div>
                            <p style="font-size:.82rem;font-weight:600;color:var(--ef-faint);margin-bottom:6px">OCR Raw Text</p>
                            <pre style="background:var(--ef-bg-subtle);border-radius:6px;padding:10px;font-size:.72rem;max-height:250px;overflow:auto;white-space:pre-wrap">{{ $json['full_text'] ?? '(not stored)' }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Import Confirmation Modal (keep Bootstrap modal — uses BS modal JS) --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--ef-radius);border:1px solid var(--ef-border)">
            <div class="modal-header" style="background:var(--ef-emerald);border-radius:var(--ef-radius) var(--ef-radius) 0 0;padding:1rem 1.4rem">
                <h5 class="modal-title" style="color:#fff;font-size:1rem;font-weight:700">
                    <i class="bi bi-box-arrow-in-down me-2"></i>Import to Inventory
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.2rem 1.4rem">
                <p style="color:var(--ef-ink-2);font-size:.9rem">This will add all <strong>{{ $bill->items->where('imported', false)->count() }}</strong> unimported item(s) to inventory.</p>
                <ul style="color:var(--ef-faint);font-size:.84rem;margin-bottom:12px;padding-left:1.2rem">
                    <li>Items matched to existing inventory → stock will be increased.</li>
                    <li>Items without a match → new inventory items will be created.</li>
                </ul>
                <p style="color:var(--ef-faint);font-size:.82rem;margin:0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ef-border);padding:.9rem 1.4rem;gap:.5rem">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.inventory.bills.import', $bill) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="ef-btn ef-btn-dark" data-loading-text="Importing…">
                        <i class="bi bi-box-arrow-in-down"></i> Yes, Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    document.getElementById('rerunOcrForm')?.addEventListener('submit', function () {
        const btn     = document.getElementById('btnRerunOcr');
        const icon    = document.getElementById('rerunIcon');
        const spinner = document.getElementById('rerunSpinner');
        if (btn)     btn.disabled = true;
        if (icon)    icon.classList.add('d-none');
        if (spinner) spinner.classList.remove('d-none');
    });

    let rowIndex = {{ $bill->items->count() }};

    const inventoryOptions = `@foreach($inventoryItems as $inv)
        <option value="{{ $inv->id }}">{{ addslashes($inv->name) }}{{ $inv->sku ? ' (' . addslashes($inv->sku) . ')' : '' }}</option>
    @endforeach`;

    const categoryOptions = `@foreach($categories as $catId => $catName)
        <option value="{{ $catId }}">{{ addslashes($catName) }}</option>
    @endforeach`;

    function makeRow(idx) {
        return `<tr>
            <td><input type="text" name="items[${idx}][item_name]" class="ef-bill-inline-input" required placeholder="Item name"></td>
            <td>
                <select name="items[${idx}][inventory_item_id]" class="ef-bill-inline-input item-match-select">
                    <option value="">— Create new item —</option>
                    ${inventoryOptions}
                </select>
            </td>
            <td><input type="number" name="items[${idx}][quantity]" class="ef-bill-inline-input item-qty" step="0.001" min="0.001" value="1" required></td>
            <td><input type="text" name="items[${idx}][unit]" class="ef-bill-inline-input" placeholder="pcs"></td>
            <td><input type="number" name="items[${idx}][unit_price]" class="ef-bill-inline-input item-price" step="0.01" min="0" value="0" required></td>
            <td><input type="number" name="items[${idx}][tax_percent]" class="ef-bill-inline-input item-tax" step="0.01" min="0" max="100" value="0"></td>
            <td><input type="number" name="items[${idx}][total]" class="ef-bill-inline-input item-total" step="0.01" min="0" value="0" required></td>
            <td>
                <select name="items[${idx}][category_id]" class="ef-bill-inline-input">
                    <option value="">— None —</option>
                    ${categoryOptions}
                </select>
            </td>
            <td style="text-align:center">
                <button type="button" class="ef-btn ef-btn-icon btn-remove-row" style="color:var(--ef-danger)"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }

    document.getElementById('btnAddRow')?.addEventListener('click', function () {
        document.getElementById('noItemsRow')?.remove();
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', makeRow(rowIndex++));
        attachRowListeners(document.querySelector('#itemsBody tr:last-child'));
    });

    document.getElementById('itemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-row');
        if (!btn) return;
        btn.closest('tr').remove();
        if (!document.querySelector('#itemsBody tr')) {
            document.getElementById('itemsBody').innerHTML =
                '<tr id="noItemsRow"><td colspan="9" style="text-align:center;color:var(--ef-faint);padding:32px;font-size:.86rem">No items. Click "Add Row" to enter manually.</td></tr>';
        }
    });

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
