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
                if ($receivedBase > 0 && (float) $pi->rate <= 0 && ($rate === null || $rate <= 0)) {
                    $errors["lines.{$itemId}"] = "{$pi->item->name}: no price was entered when ordering — type the billed rate.";

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

            // Lines ordered without a price (₹0 — e.g. bought from a purchase plan with no estimate): the rate billed at the
            // gate becomes the line price and the purchase total is recalculated. Lines that had a price keep their approved
            // figures (the billed rate is only recorded on the receipt and used to value the stock).
            $priced = false;
            foreach ($plan as $row) {
                if ($row['rate'] !== null && $row['rate'] > 0 && (float) $row['pi']->rate <= 0) {
                    $row['pi']->update(['rate' => $row['rate'], 'amount' => round($row['rate'] * (float) $row['pi']->quantity, 2)]);
                    $priced = true;
                }
            }
            if ($priced) {
                $lines = PurchaseItem::where('purchase_id', $p->id)->get();
                $sub = round((float) $lines->sum('amount'), 2);
                $tax = round((float) $lines->sum(fn ($l) => (float) $l->amount * (float) $l->tax_percent / 100), 2);
                $grand = round(max(0, $sub + $tax - (float) $p->discount_amount), 2);
                $paid = (float) $p->amount_paid;
                $p->update(['subtotal' => $sub, 'tax_amount' => $tax, 'grand_total' => $grand,
                    'payment_status' => $paid <= 0 ? 'unpaid' : ($paid + 0.005 >= $grand ? 'paid' : 'partial')]);
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

    /**
     * Undo a goods receipt (admin): reverses its stock movements, takes the quantities off the purchase lines and
     * reopens the purchase so the goods can be received again. Refused when the stock was already used (an item's
     * current stock is below what this receipt added). The receipt stays as a record, marked undone with the reason.
     */
    public function undo(GoodsReceipt $receipt, User $admin, string $reason): Purchase
    {
        return DB::transaction(function () use ($receipt, $admin, $reason) {
            $r = GoodsReceipt::with('items.purchaseItem.item')->lockForUpdate()->findOrFail($receipt->id);
            if ($r->isReversed()) {
                throw new RuntimeException('This receipt was already undone.');
            }
            $p = Purchase::lockForUpdate()->findOrFail($r->purchase_id);
            if (in_array($p->status, ['cancelled', 'rejected'], true)) {
                throw new RuntimeException('This purchase is closed.');
            }
            foreach ($r->items as $ri) {
                $item = \App\Models\InventoryItem::lockForUpdate()->find($ri->purchaseItem->inventory_item_id);
                if ($ri->received_base_quantity > 0 && (float) $item->current_stock + 0.0005 < (float) $ri->received_base_quantity) {
                    throw new RuntimeException("{$item->name}: only {$item->current_stock} {$item->unit} left in stock — part of this receipt was already used, so it can't be undone. Use a stock correction instead.");
                }
            }
            foreach ($r->items as $ri) {
                $pi = $ri->purchaseItem;
                if ($ri->inventory_transaction_id && ($txn = \App\Models\InventoryTransaction::find($ri->inventory_transaction_id))) {
                    $this->inventory->reverse($txn, $admin, null, "Receipt undone on {$p->reference()}: {$reason}");
                }
                $pi->update([
                    'received_base_quantity' => max(0, round((float) $pi->received_base_quantity - (float) $ri->received_base_quantity, 3)),
                    'closed_base_quantity' => max(0, round((float) $pi->closed_base_quantity - (float) $ri->closed_base_quantity, 3)),
                ]);
            }
            $r->update(['reversed_at' => now(), 'reversed_by' => $admin->id, 'reversal_reason' => $reason]);
            $anyReceived = PurchaseItem::where('purchase_id', $p->id)->where('received_base_quantity', '>', 0)->exists();
            $p->update(['status' => $anyReceived ? 'partially_received' : 'approved', 'completed_at' => null]);
            $this->audit->log('receipt_undone', 'purchase', $p->id, $p->reference(), [], ['receipt_id' => $r->id, 'reason' => $reason, 'status' => $p->status]);

            return $p->fresh('items');
        });
    }

    /**
     * Enter prices for lines that were ordered AND received at ₹0 (before rates were required at receipt).
     * Sets the line rate/amount, recalculates the purchase totals, records the rate on the receipt lines, and
     * adds the value of the already-received quantity to the item's average cost (spread over current stock).
     * $rates: [purchase_item_id => rate per line unit, before tax]
     */
    public function priceUnpricedLines(Purchase $purchase, array $rates, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $rates, $user) {
            $p = Purchase::lockForUpdate()->findOrFail($purchase->id);
            $changes = [];
            $errors = [];
            foreach ($p->items()->with('item')->get() as $pi) {
                $r = $rates[$pi->id] ?? null;
                if ($r === null || $r === '' || (float) $pi->rate > 0) {
                    continue;
                }
                $r = round((float) $r, 2);
                if ($r <= 0) {
                    $errors["rates.{$pi->id}"] = "{$pi->item->name}: enter a rate above 0.";

                    continue;
                }
                $pi->update(['rate' => $r, 'amount' => round($r * (float) $pi->quantity, 2)]);
                \App\Models\GoodsReceiptItem::where('purchase_item_id', $pi->id)->whereNull('actual_rate')->update(['actual_rate' => $r]);
                $received = (float) $pi->received_base_quantity;
                $item = \App\Models\InventoryItem::lockForUpdate()->find($pi->inventory_item_id);
                if ($item && $received > 0 && (float) $item->current_stock > 0) {
                    $valueAdded = $received * $this->billedBaseCost($pi, $r);
                    $item->update(['average_cost' => round((float) $item->average_cost + $valueAdded / (float) $item->current_stock, 4)]);
                }
                $changes[] = $pi->item->name.': ₹'.$r;
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            if (! $changes) {
                throw ValidationException::withMessages(['rates' => 'Enter a rate for at least one item.']);
            }
            $lines = PurchaseItem::where('purchase_id', $p->id)->get();
            $sub = round((float) $lines->sum('amount'), 2);
            $tax = round((float) $lines->sum(fn ($l) => (float) $l->amount * (float) $l->tax_percent / 100), 2);
            $grand = round(max(0, $sub + $tax - (float) $p->discount_amount), 2);
            $paid = (float) $p->amount_paid;
            $p->update(['subtotal' => $sub, 'tax_amount' => $tax, 'grand_total' => $grand,
                'payment_status' => $paid <= 0 ? 'unpaid' : ($paid + 0.005 >= $grand ? 'paid' : 'partial')]);
            $this->audit->log('prices_entered', 'purchase', $p->id, $p->reference(), [], ['rates' => $changes, 'grand_total' => $grand]);

            return $p->fresh('items');
        });
    }

    /** Cost per stock unit incl. tax when the supplier billed a different rate (per purchase-line unit, before tax). */
    private function billedBaseCost(PurchaseItem $pi, float $rate): float
    {
        $factor = $pi->quantity > 0 ? $pi->base_quantity / $pi->quantity : 1;

        return $factor > 0 ? round($rate * (1 + $pi->tax_percent / 100) / $factor, 4) : 0;
    }
}
