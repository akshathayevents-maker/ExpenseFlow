<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\SavePurchaseRequest;
use App\Models\GoodsReceipt;
use App\Models\InventoryBillUpload;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Vendor;
use App\Services\Inventory\GoodsReceiptService;
use App\Services\Inventory\ItemMatchingService;
use App\Services\Inventory\PurchaseService;
use App\Services\Inventory\UnitConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseService $purchases,
        private GoodsReceiptService $receipts,
        private ItemMatchingService $matcher,
        private UnitConversionService $units,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Purchase::class);
        $user = $request->user();
        $status = $request->input('status');

        $purchases = Purchase::with(['vendor', 'creator'])->withCount('items')
            ->when(! $user->isAdmin() && ! $user->isStorekeeper(), fn ($q) => $q->where('created_by', $user->id))
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('purchase_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('purchase_date', '<=', $request->to))
            ->latest('purchase_date')->latest('id')
            ->paginate(25)->withQueryString();

        $counts = Purchase::selectRaw('status, COUNT(*) as c')
            ->when(! $user->isAdmin() && ! $user->isStorekeeper(), fn ($q) => $q->where('created_by', $user->id))
            ->groupBy('status')->pluck('c', 'status');

        return view('inventory.purchases.index', compact('purchases', 'counts', 'status'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Purchase::class);
        $bill = null;
        $prefill = [];
        if ($request->filled('bill')) {
            abort_unless($request->user()->isAdmin(), 403);
            $bill = InventoryBillUpload::with('items')->findOrFail($request->integer('bill'));
            if ($bill->purchase_id || $bill->status === 'imported') {
                return redirect()->route('admin.inventory.bills.show', $bill)->with('error', 'This bill is already linked to a purchase.');
            }
            $prefill = $this->prefillFromBill($bill);
        }

        return view('inventory.purchases.form', $this->formData(null, $bill, $prefill));
    }

    public function store(SavePurchaseRequest $request): RedirectResponse
    {
        try {
            $purchase = $this->purchases->save($request->validated(), $request->user(), null, $request->input('action') === 'submit');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.purchases.show', $purchase)
            ->with('success', $purchase->status === 'submitted' ? 'Purchase sent for approval.' : 'Draft saved.');
    }

    public function show(Purchase $purchase): View
    {
        $this->authorize('view', $purchase);
        $purchase->load(['items.item', 'vendor', 'creator', 'approver', 'rejecter', 'bill', 'receipts.receiver', 'receipts.items.purchaseItem.item']);

        return view('inventory.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $this->authorize('update', $purchase);
        $purchase->load('items.item');

        return view('inventory.purchases.form', $this->formData($purchase, $purchase->bill));
    }

    public function update(SavePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        try {
            $purchase = $this->purchases->save($request->validated(), $request->user(), $purchase, $request->input('action') === 'submit');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.purchases.show', $purchase)->with('success', 'Purchase updated.');
    }

    public function submit(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('submit', $purchase);

        return $this->run(fn () => $this->purchases->submit($purchase, $request->user()), $purchase, 'Sent for approval.');
    }

    public function approve(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('approve', $purchase);

        return $this->run(fn () => $this->purchases->approve($purchase, $request->user()), $purchase, 'Purchase approved.');
    }

    public function reject(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('reject', $purchase);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        return $this->run(fn () => $this->purchases->reject($purchase, $request->user(), $data['reason']), $purchase, 'Purchase rejected.');
    }

    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('cancel', $purchase);

        return $this->run(fn () => $this->purchases->cancel($purchase, $request->user()), $purchase, 'Purchase cancelled.');
    }

    public function complete(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('complete', $purchase);

        return $this->run(fn () => $this->purchases->complete($purchase, $request->user()), $purchase, 'Purchase closed.');
    }

    public function receiveForm(Purchase $purchase): View
    {
        $this->authorize('receive', $purchase);
        $purchase->load('items.item', 'vendor');

        return view('inventory.purchases.receive', ['purchase' => $purchase, 'token' => (string) Str::uuid()]);
    }

    public function receive(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('receive', $purchase);
        $data = $request->validate([
            // mode: all = everything still due arrived as ordered; changes = only edited figures differ.
            // Without mode the detailed receive form posts lines as before.
            'mode' => ['nullable', 'in:all,changes'],
            'more_coming' => ['nullable', 'boolean'],
            'extra' => ['nullable', 'array', 'max:30'],
            'extra.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'extra.*.quantity' => ['nullable', 'numeric', 'min:0.001', 'max:9999999', 'required_with:extra.*.inventory_item_id'],
            'extra.*.rate' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines' => ['required_without:mode', 'array'],
            'lines.*.received' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.rejected' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.close' => ['nullable', 'boolean'],
            'lines.*.remarks' => ['nullable', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'client_token' => ['nullable', 'uuid'],
            'bill_file' => [$request->filled('mode') && ! $purchase->bill && ! $purchase->receipts()->whereNotNull('bill_path')->exists() ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'bill_number' => ['nullable', 'string', 'max:100'],
            'bill_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        if (! empty($data['mode'])) {
            $data['lines'] = $this->quickLines($purchase, $data);
        }
        $extra = array_values(array_filter($data['extra'] ?? [], fn ($x) => ! empty($x['inventory_item_id'])));

        $bill = ['bill_number' => $data['bill_number'] ?? null, 'bill_amount' => $data['bill_amount'] ?? null];
        if ($request->hasFile('bill_file')) {
            $file = $request->file('bill_file');
            $bill['bill_path'] = $file->store('goods-receipt-bills', 'private');
            $bill['bill_original_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }

        try {
            $receipt = $this->receipts->receive($purchase, $data['lines'], $request->user(), $data['notes'] ?? null, $data['client_token'] ?? null, $bill, $extra);
        } catch (\Throwable $e) {
            if (! empty($bill['bill_path'])) {
                Storage::disk('private')->delete($bill['bill_path']);
            }
            if (! $e instanceof RuntimeException) {
                throw $e;
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
        // replayed submit (same client_token) returns the earlier receipt — drop the duplicate upload
        if (! empty($bill['bill_path']) && $receipt->bill_path !== $bill['bill_path']) {
            Storage::disk('private')->delete($bill['bill_path']);
        }

        return redirect()->route('inventory.purchases.show', $purchase)->with('success', 'Goods received and stock updated.');
    }

    public function receiptBill(Purchase $purchase, GoodsReceipt $receipt): Response
    {
        $this->authorize('view', $purchase);
        abort_unless($receipt->purchase_id === $purchase->id && $receipt->bill_path, 404);
        abort_unless(Storage::disk('private')->exists($receipt->bill_path), 404, 'File not found.');

        $mime = $receipt->billIsPdf() ? 'application/pdf'
            : (str_ends_with(strtolower($receipt->bill_path), '.png') ? 'image/png' : 'image/jpeg');
        $name = str_replace(['"', '\\', "\r", "\n"], '', $receipt->bill_original_name ?: basename($receipt->bill_path));

        return response(Storage::disk('private')->get($receipt->bill_path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ── helpers ────────────────────────────────────────────────────────────

    /**
     * One-tap receipt from the purchase page. "all": every due line arrives in full.
     * "changes": a line left blank arrived as ordered; a smaller quantity closes the rest
     * unless "more_coming" is ticked; a changed rate is recorded as the billed rate.
     */
    private function quickLines(Purchase $purchase, array $data): array
    {
        $posted = $data['lines'] ?? [];
        $lines = [];
        foreach ($purchase->items()->get() as $pi) {
            $factor = $pi->quantity > 0 ? $pi->base_quantity / $pi->quantity : 1;
            $due = round($pi->pendingBaseQuantity() / $factor, 3);
            if ($due <= 0) {
                continue;
            }
            $row = $data['mode'] === 'changes' ? ($posted[$pi->id] ?? []) : [];
            $got = isset($row['received']) && $row['received'] !== '' ? min((float) $row['received'], $due) : $due;
            $rate = isset($row['rate']) && $row['rate'] !== '' && abs((float) $row['rate'] - $pi->rate) >= 0.005 ? $row['rate'] : null;
            $lines[$pi->id] = [
                'received' => $got,
                'rate' => $rate,
                'close' => $got < $due - 0.0005 && empty($data['more_coming']),
                'remarks' => $got < $due - 0.0005 ? 'Short supply' : null,
            ];
        }

        return $lines;
    }

    private function run(callable $fn, Purchase $purchase, string $ok): RedirectResponse
    {
        try {
            $fn();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.purchases.show', $purchase)->with('success', $ok);
    }

    private function formData(?Purchase $purchase, ?InventoryBillUpload $bill, array $prefill = []): array
    {
        $lines = old('lines');
        if (! $lines && $purchase) {
            $lines = $purchase->items->map(fn ($i) => [
                'inventory_item_id' => $i->inventory_item_id, 'description' => $i->description,
                'quantity' => $i->quantity, 'unit' => $i->unit, 'rate' => $i->rate, 'tax_percent' => $i->tax_percent,
            ])->all();
        }
        $lines = $lines ?: ($prefill['lines'] ?? []);

        return [
            'purchase' => $purchase,
            'bill' => $bill,
            'header' => $prefill['header'] ?? [],
            'lines' => array_values($lines),
            'vendors' => Vendor::active()->orderBy('name')->get(['id', 'name']),
            'catalog' => $this->catalog(),
            'unitCodes' => array_keys(config('inventory.units')),
            'token' => (string) Str::uuid(),
        ];
    }

    /** Items for the searchable grid (id, name, unit, allowed units, last rate). */
    private function catalog(): array
    {
        $lastRates = PurchaseItem::query()
            ->selectRaw('inventory_item_id, MAX(id) as id')->groupBy('inventory_item_id')->pluck('id', 'inventory_item_id');
        $rates = PurchaseItem::whereIn('id', $lastRates->values())->get(['inventory_item_id', 'rate', 'unit'])->keyBy('inventory_item_id');

        return InventoryItem::with('unitConversions')->where('status', 'active')->orderBy('name')->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'name_ta' => $i->name_ta,
                'unit' => $i->unit,
                'units' => $this->units->unitsFor($i),
                'stock' => round((float) $i->current_stock, 3),
                'rate' => $rates[$i->id]->rate ?? null,
            ])->all();
    }

    private function prefillFromBill(InventoryBillUpload $bill): array
    {
        $vendor = $bill->vendor_name ? Vendor::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($bill->vendor_name))])->first() : null;
        $lines = [];
        foreach ($bill->items as $row) {
            $match = $row->inventory_item_id
                ? ['item_id' => $row->inventory_item_id, 'confidence' => 100, 'method' => 'manual']
                : $this->matcher->best($row->item_name, $vendor?->id);
            $auto = $row->inventory_item_id || $this->matcher->isAutoAcceptable($match);
            $lines[] = [
                'inventory_item_id' => $auto ? $match['item_id'] : null,
                'suggested_item_id' => $match['item_id'] ?? null,
                'confidence' => $match['confidence'] ?? 0,
                'description' => $row->item_name,
                'quantity' => (float) $row->quantity,
                'unit' => $this->units->normalize($row->unit),
                'rate' => (float) $row->unit_price,
                'tax_percent' => (float) $row->tax_percent,
                'remember_alias' => false,
            ];
        }

        return [
            'header' => [
                'purchase_date' => optional($bill->invoice_date)->toDateString() ?? now()->toDateString(),
                'vendor_id' => $vendor?->id,
                'supplier_name' => $vendor ? null : $bill->vendor_name,
                'invoice_number' => $bill->invoice_number,
                'bill_total' => (float) $bill->total_amount,
            ],
            'lines' => $lines,
        ];
    }
}
