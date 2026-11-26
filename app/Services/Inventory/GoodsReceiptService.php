<?php

namespace App\Services\Inventory;

use App\Models\GoodsReceipt;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Physical receipt of approved purchases. The only place purchases add stock.
 * Supports several partial receipts per purchase.
 */
class GoodsReceiptService
{
    public const ADDED_ON_RECEIPT = 'Added on receipt';

    public function __construct(
        private InventoryService $inventory,
        private AuditLogService $audit,
    ) {}

    /**
     * $lines is keyed by purchase_item_id; quantities are in the purchase line's unit.
     *  - rate:  rate actually billed (per line unit, before tax). Values the stock; approved figures stay as they are.
     *  - close: the rest of this line is not coming (short supply); it stops counting as due.
     *
     * @param  array<int, array{received?: float|string|null, rejected?: float|string|null, rate?: float|string|null, close?: mixed, remarks?: string|null}>  $lines
     *                                                                                                                                                                $extra: items that arrived but were not on the purchase (stock unit, rate before tax). They are added as
     *                                                                                                                                                                purchase lines marked "Added on receipt" and received in full; approved totals are left as they were.
     * @param  array{bill_number?: ?string, bill_amount?: float|string|null, bill_path?: ?string, bill_original_name?: ?string}  $bill
     * @param  array<int, array{inventory_item_id: int|string, quantity: float|string, rate?: float|string|null}>  $extra
     */
    public function receive(Purchase $purchase, array $lines, User $user, ?string $notes = null, ?string $clientToken = null, array $bill = [], array $extra = []): GoodsReceipt
    {
        if ($clientToken && ($existing = GoodsReceipt::where('client_token', $clientToken)->first())) {
            return $existing;
        }

        return DB::transaction(function () use ($purchase, $lines, $user, $notes, $clientToken, $bill, $extra) {
            $p = Purchase::lockForUpdate()->findOrFail($purchase->id);
            if (! $p->isReceivable()) {
                throw new RuntimeException("Purchase is {$p->statusLabel()}; goods cannot be received.");
            }

            $sort = (int) PurchaseItem::where('purchase_id', $p->id)->max('sort_order');
            foreach ($extra as $x) {
                $qty = round((float) ($x['quantity'] ?? 0), 3);
                $item = InventoryItem::find($x['inventory_item_id'] ?? 0);
                if (! $item || $qty <= 0) {
                    continue;
                }
                $rate = round((float) ($x['rate'] ?? 0), 2);
                $added = PurchaseItem::create([
                    'purchase_id' => $p->id, 'inventory_item_id' => $item->id, 'description' => self::ADDED_ON_RECEIPT,
                    'quantity' => $qty, 'unit' => $item->unit, 'base_quantity' => $qty, 'rate' => $rate,
                    'tax_percent' => 0, 'amount' => round($qty * $rate, 2), 'received_base_quantity' => 0, 'sort_order' => ++$sort,
                ]);
                $lines[$added->id] = ['received' => $qty];
            }

            $items = PurchaseItem::with('item')->where('purchase_id', $p->id)->lockForUpdate()->get()->keyBy('id');
            $errors = [];
            $plan = [];
            foreach ($lines as $itemId => $line) {
                $pi = $items[$itemId] ?? null;
                $received = round((float) ($line['received'] ?? 0), 3);
                $rejected = round((float) ($line['rejected'] ?? 0), 3);
                $close = filter_var($line['close'] ?? false, FILTER_VALIDATE_BOOL);
                $rate = isset($line['rate']) && $line['rate'] !== '' ? round((float) $line['rate'], 2) : null;
                if (! $pi || ($received <= 0 && $rejected <= 0 && ! $close)) {
                    continue;
                }
                if ($received < 0 || $rejected < 0 || ($rate !== null && $rate < 0)) {
                    $errors["lines.{$itemId}"] = 'Quantities and rates cannot be negative.';

                    continue;
                }
                $factor = $pi->quantity > 0 ? $pi->base_quantity / $pi->quantity : 1;
                $receivedBase = round($received * $factor, 3);
                $rejectedBase = round($rejected * $factor, 3);
                if ($receivedBase > $pi->pendingBaseQuantity() + 0.0005) {
                    $pendingEntered = round($pi->pendingBaseQuantity() / $factor, 3);
                    $errors["lines.{$itemId}"] = "{$pi->item->name}: only {$pendingEntered} {$pi->unit} is still due.";

                    continue;
                }
                if ($rejectedBase > 0 && trim((string) ($line['remarks'] ?? '')) === '') {
                    $errors["lines.{$itemId}"] = "{$pi->item->name}: give a reason for rejected quantity.";

                    continue;
                }
                $closedBase = $close ? round($pi->pendingBaseQuantity() - $receivedBase, 3) : 0.0;
                $plan[] = compact('pi', 'received', 'receivedBase', 'rejectedBase', 'closedBase', 'rate') + ['remarks' => $line['remarks'] ?? null];
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            if (! $plan) {
                throw ValidationException::withMessages(['lines' => 'Enter the received quantity for at least one item.']);
            }

            $receipt = GoodsReceipt::create([
                'purchase_id' => $p->id,
                'received_by' => $user->id,
                'received_at' => now(),
                'notes' => $notes,
                'client_token' => $clientToken,
                'bill_number' => $bill['bill_number'] ?? null,
                'bill_amount' => isset($bill['bill_amount']) && $bill['bill_amount'] !== '' ? round((float) $bill['bill_amount'], 2) : null,
                'bill_path' => $bill['bill_path'] ?? null,
                'bill_original_name' => $bill['bill_original_name'] ?? null,
            ]);

            // lock item rows in id order, same as approvals, to avoid deadlocks
            usort($plan, fn ($a, $b) => $a['pi']->inventory_item_id <=> $b['pi']->inventory_item_id);
            foreach ($plan as $row) {
                $pi = $row['pi'];
                $txn = null;
                if ($row['receivedBase'] > 0) {
                    $txn = $this->inventory->post($pi->item, $row['receivedBase'], 'purchase_receipt', $user, [
                        'unit_cost' => $row['rate'] !== null ? $this->billedBaseCost($pi, $row['rate']) : $pi->baseUnitCost(),
                        'reference_type' => 'purchase',
                        'reference_id' => $p->id,
                        'reference_label' => $p->reference().' · '.$p->supplierLabel(),
                        'entered_quantity' => $row['received'],
                        'entered_unit' => $pi->unit,
                        'notes' => $row['remarks'],
                    ]);
                    $pi->increment('received_base_quantity', $row['receivedBase']);
                }
                if ($row['closedBase'] > 0) {
                    $pi->increment('closed_base_quantity', $row['closedBase']);
                }
                $receipt->items()->create([
                    'purchase_item_id' => $pi->id,
                    'received_base_quantity' => $row['receivedBase'],
                    'rejected_base_quantity' => $row['rejectedBase'],
                    'actual_rate' => $row['rate'],
                    'closed_base_quantity' => $row['closedBase'],
                    'remarks' => $row['remarks'],
                    'inventory_transaction_id' => $txn?->id,
                ]);
            }

            $pending = PurchaseItem::where('purchase_id', $p->id)->get()->sum(fn ($i) => $i->pendingBaseQuantity());
            $p->update(['status' => $pending <= 0.0005 ? 'received' : 'partially_received']);

            $this->audit->log('goods_received', 'purchase', $p->id, $p->reference(), [], [
                'receipt_id' => $receipt->id, 'added_items' => count($extra), 'lines' => count($plan), 'status' => $p->status,
                'bill_number' => $receipt->bill_number, 'bill_amount' => $receipt->bill_amount, 'bill_file' => (bool) $receipt->bill_path,
                'short_closed' => collect($plan)->where('closedBase', '>', 0)->map(fn ($r) => $r['pi']->item->name.': '.$r['closedBase'])->values()->all(),
                'rate_changes' => collect($plan)->filter(fn ($r) => $r['rate'] !== null && abs($r['rate'] - $r['pi']->rate) >= 0.005)
                    ->map(fn ($r) => $r['pi']->item->name.': '.$r['pi']->rate.' -> '.$r['rate'])->values()->all(),
            ]);

            return $receipt->load('items');
        });
    }

    /** Cost per stock unit incl. tax when the supplier billed a different rate (per purchase-line unit, before tax). */
    private function billedBaseCost(PurchaseItem $pi, float $rate): float
    {
        $factor = $pi->quantity > 0 ? $pi->base_quantity / $pi->quantity : 1;

        return $factor > 0 ? round($rate * (1 + $pi->tax_percent / 100) / $factor, 4) : 0;
    }
}
