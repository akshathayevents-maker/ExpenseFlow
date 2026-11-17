<?php

namespace App\Services\Inventory;

use App\Models\GoodsReceipt;
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
    public function __construct(
        private InventoryService $inventory,
        private AuditLogService $audit,
    ) {}

    /**
     * @param  array<int, array{received?: float|string|null, rejected?: float|string|null, remarks?: string|null}>  $lines
     *                                                                                                                       keyed by purchase_item_id; quantities in the purchase line's unit.
     */
    public function receive(Purchase $purchase, array $lines, User $user, ?string $notes = null, ?string $clientToken = null): GoodsReceipt
    {
        if ($clientToken && ($existing = GoodsReceipt::where('client_token', $clientToken)->first())) {
            return $existing;
        }

        return DB::transaction(function () use ($purchase, $lines, $user, $notes, $clientToken) {
            $p = Purchase::lockForUpdate()->findOrFail($purchase->id);
            if (! $p->isReceivable()) {
                throw new RuntimeException("Purchase is {$p->statusLabel()}; goods cannot be received.");
            }

            $items = PurchaseItem::with('item')->where('purchase_id', $p->id)->lockForUpdate()->get()->keyBy('id');
            $errors = [];
            $plan = [];
            foreach ($lines as $itemId => $line) {
                $pi = $items[$itemId] ?? null;
                $received = round((float) ($line['received'] ?? 0), 3);
                $rejected = round((float) ($line['rejected'] ?? 0), 3);
                if (! $pi || ($received <= 0 && $rejected <= 0)) {
                    continue;
                }
                if ($received < 0 || $rejected < 0) {
                    $errors["lines.{$itemId}"] = 'Quantities cannot be negative.';

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
                $plan[] = compact('pi', 'received', 'receivedBase', 'rejectedBase') + ['remarks' => $line['remarks'] ?? null];
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
            ]);

            // lock item rows in id order, same as approvals, to avoid deadlocks
            usort($plan, fn ($a, $b) => $a['pi']->inventory_item_id <=> $b['pi']->inventory_item_id);
            foreach ($plan as $row) {
                $pi = $row['pi'];
                $txn = null;
                if ($row['receivedBase'] > 0) {
                    $txn = $this->inventory->post($pi->item, $row['receivedBase'], 'purchase_receipt', $user, [
                        'unit_cost' => $pi->baseUnitCost(),
                        'reference_type' => 'purchase',
                        'reference_id' => $p->id,
                        'reference_label' => $p->reference().' · '.$p->supplierLabel(),
                        'entered_quantity' => $row['received'],
                        'entered_unit' => $pi->unit,
                        'notes' => $row['remarks'],
                    ]);
                    $pi->increment('received_base_quantity', $row['receivedBase']);
                }
                $receipt->items()->create([
                    'purchase_item_id' => $pi->id,
                    'received_base_quantity' => $row['receivedBase'],
                    'rejected_base_quantity' => $row['rejectedBase'],
                    'remarks' => $row['remarks'],
                    'inventory_transaction_id' => $txn?->id,
                ]);
            }

            $pending = PurchaseItem::where('purchase_id', $p->id)->get()->sum(fn ($i) => $i->pendingBaseQuantity());
            $p->update(['status' => $pending <= 0.0005 ? 'received' : 'partially_received']);

            $this->audit->log('goods_received', 'purchase', $p->id, $p->reference(), [], [
                'receipt_id' => $receipt->id, 'lines' => count($plan), 'status' => $p->status,
            ]);

            return $receipt->load('items');
        });
    }
}
