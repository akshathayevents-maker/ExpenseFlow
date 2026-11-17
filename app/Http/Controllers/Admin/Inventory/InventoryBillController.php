<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryBillItem;
use App\Models\InventoryBillUpload;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Services\AuditLogService;
use App\Services\Inventory\ItemMatchingService;
use App\Services\InvoiceOCRService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InventoryBillController extends Controller
{
    public function __construct(
        private InvoiceOCRService $ocr,
        private InventoryService  $inventory,
        private AuditLogService   $audit,
    ) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = InventoryBillUpload::with('uploader')->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($vendor = $request->input('vendor')) {
            $query->where('vendor_name', 'like', "%{$vendor}%");
        }

        $bills = $query->paginate(20)->withQueryString();

        return view('admin.inventory.bills.index', compact('bills'));
    }

    // ─── Store (upload + run OCR) ─────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bill_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file     = $request->file('bill_file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $fileType = ($ext === 'pdf') ? 'pdf' : 'image';
        $path     = $file->store('inventory-bills', 'private');
        $hash     = hash_file('sha256', $file->getRealPath());

        // Hash-based duplicate detection
        $hashDupe = InventoryBillUpload::where('file_hash', $hash)->first();
        if ($hashDupe) {
            Storage::disk('private')->delete($path);
            return redirect()->route('admin.inventory.bills.show', $hashDupe)
                ->with('warning', 'This exact file was already uploaded. Showing existing record.');
        }

        $bill = DB::transaction(function () use ($file, $path, $fileType, $hash, $request) {
            $b = InventoryBillUpload::create([
                'original_filename' => $file->getClientOriginalName(),
                'stored_path'       => $path,
                'file_type'         => $fileType,
                'file_hash'         => $hash,
                'status'            => 'uploaded',
                'uploaded_by'       => auth()->id(),
                'notes'             => $request->input('notes'),
            ]);

            $this->audit->log('created', 'inventory_bill', $b->id, $b->original_filename);
            return $b;
        });

        // Run OCR synchronously
        $bill->update(['status' => 'processing']);
        $extracted = $this->ocr->extract($bill);

        DB::transaction(function () use ($bill, $extracted) {
            $bill->update([
                'vendor_name'    => $extracted['vendor_name'],
                'invoice_number' => $extracted['invoice_number'],
                'invoice_date'   => $extracted['invoice_date'],
                'gst_number'     => $extracted['gst_number'],
                'subtotal'       => $extracted['subtotal'],
                'tax_amount'     => $extracted['tax_amount'],
                'total_amount'   => $extracted['total_amount'],
                'extracted_json' => $extracted,
                'ocr_provider'   => $extracted['ocr_provider'],
                'status'         => 'review_pending',
            ]);

            foreach ($extracted['items'] as $row) {
                if (empty($row['item_name'])) continue;
                InventoryBillItem::create([
                    'bill_upload_id' => $bill->id,
                    'item_name'      => $row['item_name'],
                    'original_item_name' => $row['item_name'],
                    'quantity'       => $row['quantity']    ?? 0,
                    'unit'           => $row['unit']        ?? null,
                    'unit_price'     => $row['unit_price']  ?? 0,
                    'tax_percent'    => $row['tax_percent'] ?? 0,
                    'total'          => $row['total']       ?? 0,
                ]);
            }
        });

        $msg = $extracted['ocr_message']
            ? 'Bill uploaded. ' . $extracted['ocr_message']
            : 'Bill uploaded and data extracted. Please review below.';

        return redirect()->route('admin.inventory.bills.show', $bill)
            ->with('success', $msg);
    }

    // ─── Show (review page) ───────────────────────────────────────────────────

    public function show(InventoryBillUpload $bill): View
    {
        $bill->load(['items.inventoryItem', 'items.category', 'uploader', 'reviewer']);

        $categories    = InventoryCategory::active()->orderBy('name')->pluck('name', 'id');
        $inventoryItems = InventoryItem::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'inventory_category_id']);

        // Dictionary match (exact / alias / supplier alias / fuzzy) with confidence.
        $matcher = app(ItemMatchingService::class);
        $matchMap = [];
        $matchInfo = [];
        foreach ($bill->items as $item) {
            if ($item->inventory_item_id) {
                $matchMap[$item->id] = $item->inventory_item_id;
                continue;
            }
            $best = $matcher->best($item->item_name);
            $matchInfo[$item->id] = $best;
            $matchMap[$item->id] = $matcher->isAutoAcceptable($best) ? $best['item_id'] : null;
        }

        $duplicate = $bill->duplicateCheck();

        return view('admin.inventory.bills.review', compact(
            'bill', 'categories', 'inventoryItems', 'matchMap', 'matchInfo', 'duplicate'
        ));
    }

    // ─── Update (save review edits) ───────────────────────────────────────────

    public function update(Request $request, InventoryBillUpload $bill): RedirectResponse
    {
        if ($bill->isImported() || $bill->purchase_id) {
            return back()->with('error', 'This bill is already linked to stock or a purchase.');
        }

        $request->validate([
            'vendor_name'    => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date'   => 'nullable|date',
            'gst_number'     => 'nullable|string|max:20',
            'subtotal'       => 'nullable|numeric|min:0',
            'tax_amount'     => 'nullable|numeric|min:0',
            'total_amount'   => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
            'items'          => 'nullable|array',
            'items.*.id'     => 'nullable|integer',
            'items.*.item_name'       => 'required|string|max:255',
            'items.*.quantity'        => 'required|numeric|min:0.001',
            'items.*.unit'            => 'nullable|string|max:50',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.tax_percent'     => 'nullable|numeric|min:0|max:100',
            'items.*.total'           => 'required|numeric|min:0',
            'items.*.category_id'     => 'nullable|exists:inventory_categories,id',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
        ]);

        DB::transaction(function () use ($request, $bill) {
            $bill->update([
                'vendor_name'    => $request->input('vendor_name'),
                'invoice_number' => $request->input('invoice_number'),
                'invoice_date'   => $request->input('invoice_date'),
                'gst_number'     => $request->input('gst_number'),
                'subtotal'       => $request->input('subtotal', 0),
                'tax_amount'     => $request->input('tax_amount', 0),
                'total_amount'   => $request->input('total_amount', 0),
                'notes'          => $request->input('notes'),
                'reviewed_by'    => auth()->id(),
                'status'         => 'review_pending',
            ]);

            // Sync items: delete existing non-imported, recreate from form
            $bill->items()->where('imported', false)->delete();

            foreach ($request->input('items', []) as $row) {
                InventoryBillItem::create([
                    'bill_upload_id'    => $bill->id,
                    'inventory_item_id' => $row['inventory_item_id'] ?? null,
                    'category_id'       => $row['category_id']       ?? null,
                    'item_name'         => $row['item_name'],
                    'quantity'          => $row['quantity'],
                    'unit'              => $row['unit']         ?? null,
                    'unit_price'        => $row['unit_price'],
                    'tax_percent'       => $row['tax_percent']  ?? 0,
                    'total'             => $row['total'],
                ]);
            }
        });

        return back()->with('success', 'Review saved.');
    }

    // ─── Import to inventory ──────────────────────────────────────────────────

    public function import(InventoryBillUpload $bill): RedirectResponse
    {
        // Invoice uploads never add stock directly any more: the reviewed bill
        // becomes a purchase (approval) and stock moves only on goods receipt.
        if ($bill->isImported() || $bill->purchase_id) {
            return back()->with('error', 'This bill is already linked to stock or a purchase.');
        }

        return redirect()->route('inventory.purchases.create', ['bill' => $bill->id]);
    }

    // ─── Re-run OCR ───────────────────────────────────────────────────────────

    public function rerunOcr(InventoryBillUpload $bill): RedirectResponse
    {
        if ($bill->isImported() || $bill->purchase_id) {
            return back()->with('error', 'Cannot re-run OCR on a bill that is linked to stock or a purchase.');
        }

        $bill->update(['status' => 'processing']);
        $extracted = $this->ocr->extract($bill);

        DB::transaction(function () use ($bill, $extracted) {
            $bill->update([
                'vendor_name'    => $extracted['vendor_name'],
                'invoice_number' => $extracted['invoice_number'],
                'invoice_date'   => $extracted['invoice_date'],
                'gst_number'     => $extracted['gst_number'],
                'subtotal'       => $extracted['subtotal'],
                'tax_amount'     => $extracted['tax_amount'],
                'total_amount'   => $extracted['total_amount'],
                'extracted_json' => $extracted,
                'ocr_provider'   => $extracted['ocr_provider'],
                'status'         => 'review_pending',
            ]);

            $bill->items()->where('imported', false)->delete();

            foreach ($extracted['items'] as $row) {
                if (empty($row['item_name'])) continue;
                InventoryBillItem::create([
                    'bill_upload_id' => $bill->id,
                    'item_name'      => $row['item_name'],
                    'original_item_name' => $row['item_name'],
                    'quantity'       => $row['quantity']    ?? 0,
                    'unit'           => $row['unit']        ?? null,
                    'unit_price'     => $row['unit_price']  ?? 0,
                    'tax_percent'    => $row['tax_percent'] ?? 0,
                    'total'          => $row['total']       ?? 0,
                ]);
            }
        });

        $msg = $extracted['ocr_message']
            ? 'OCR re-run complete. ' . $extracted['ocr_message']
            : 'OCR re-run complete. Please review the updated data below.';

        return redirect()->route('admin.inventory.bills.show', $bill)
            ->with('success', $msg);
    }

    // ─── Serve file ───────────────────────────────────────────────────────────

    public function file(InventoryBillUpload $bill): Response
    {
        if (! Storage::disk('private')->exists($bill->stored_path)) {
            abort(404, 'File not found.');
        }

        $mime = $bill->isPdf() ? 'application/pdf' : 'image/jpeg';
        $content = Storage::disk('private')->get($bill->stored_path);

        return response($content, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $bill->original_filename . '"',
        ]);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(InventoryBillUpload $bill): RedirectResponse
    {
        if (! $bill->canDelete() || $bill->purchase_id) {
            return back()->with('error', 'Imported bills cannot be deleted.');
        }

        Storage::disk('private')->delete($bill->stored_path);
        $bill->delete();

        $this->audit->log('deleted', 'inventory_bill', $bill->id, $bill->original_filename);

        return redirect()->route('admin.inventory.bills.index')
            ->with('success', 'Bill deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function normaliseUnit(?string $unit): string
    {
        if (! $unit) return 'piece';
        $map = [
            'pcs'    => 'piece',
            'pc'     => 'piece',
            'nos'    => 'piece',
            'no'     => 'piece',
            'kg'     => 'kg',
            'kgs'    => 'kg',
            'gram'   => 'gram',
            'gm'     => 'gram',
            'g'      => 'gram',
            'litre'  => 'litre',
            'ltr'    => 'litre',
            'l'      => 'litre',
            'ml'     => 'ml',
            'packet' => 'packet',
            'pkt'    => 'packet',
            'box'    => 'box',
            'bundle' => 'bundle',
            'dozen'  => 'dozen',
            'doz'    => 'dozen',
        ];
        return $map[strtolower(trim($unit))] ?? 'piece';
    }
}
