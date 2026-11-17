<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryStockAlert;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The ONLY writer of inventory_items.current_stock.
 *
 * Every movement is one append-only inventory_transactions row written inside a
 * DB transaction while the item row is locked (SELECT ... FOR UPDATE), so two
 * concurrent requests can never consume the same stock. Quantities here are in
 * the item's stock unit (convert first with UnitConversionService).
 * Negative stock is refused. Ledger rows are never edited: corrections are new
 * rows (type correction / reversal with reversal_of_id).
 */
class InventoryService
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Post one signed movement. $context keys: notes, unit_cost, reference_type,
     * reference_id, reference_label, entered_quantity, entered_unit, reversal_of_id.
     */
    public function post(InventoryItem|int $item, float $signedQuantity, string $type, User $user, array $context = []): InventoryTransaction
    {
        $signedQuantity = round($signedQuantity, 3);
        if ($signedQuantity == 0.0) {
            throw new RuntimeException('Quantity must not be zero.');
        }
        if (! in_array($type, InventoryTransaction::TYPES, true)) {
            throw new RuntimeException("Unknown inventory transaction type: {$type}");
        }

        return DB::transaction(function () use ($item, $signedQuantity, $type, $user, $context) {
            $locked = InventoryItem::lockForUpdate()->findOrFail($item instanceof InventoryItem ? $item->id : $item);
            $before = round((float) $locked->current_stock, 3);
            $after = round($before + $signedQuantity, 3);

            if ($after < 0) {
                throw new RuntimeException("Insufficient stock for {$locked->name}. Available: {$before} {$locked->unit}");
            }

            $unitCost = $context['unit_cost'] ?? null;
            $updates = ['current_stock' => $after];
            if ($signedQuantity > 0 && $unitCost !== null && $unitCost > 0 && in_array($type, ['purchase', 'purchase_receipt'], true)) {
                $updates['average_cost'] = $this->recalcAverageCost($locked, $before, $signedQuantity, (float) $unitCost);
            }
            $locked->update($updates);

            $txn = InventoryTransaction::create([
                'inventory_item_id' => $locked->id,
                'type' => $type,
                'quantity' => abs($signedQuantity),
                'balance_before' => $before,
                'balance_after' => $after,
                'unit_cost' => $unitCost ?? ($signedQuantity < 0 ? $locked->average_cost : null),
                'notes' => $context['notes'] ?? null,
                'created_by' => $user->id,
                'reference_type' => $context['reference_type'] ?? null,
                'reference_id' => $context['reference_id'] ?? null,
                'reference_label' => $context['reference_label'] ?? null,
                'entered_quantity' => $context['entered_quantity'] ?? null,
                'entered_unit' => $context['entered_unit'] ?? null,
                'reversal_of_id' => $context['reversal_of_id'] ?? null,
            ]);

            $fresh = $locked->fresh();
            if ($signedQuantity < 0) {
                $this->checkAndCreateAlerts($fresh, $user);
            } else {
                $this->resolveAlertsIfRestocked($fresh, $user);
            }

            return $txn;
        });
    }

    /** Undo (fully or partly) an earlier movement with an opposite, linked row. */
    public function reverse(InventoryTransaction $original, User $user, ?float $quantity = null, ?string $notes = null, array $context = []): InventoryTransaction
    {
        $qty = round($quantity ?? (float) $original->quantity, 3);
        if ($qty <= 0 || $qty > (float) $original->quantity + 0.0005) {
            throw new RuntimeException('Reversal quantity must be between 0 and the original quantity.');
        }
        $already = (float) InventoryTransaction::where('reversal_of_id', $original->id)->sum('quantity');
        if ($qty + $already > (float) $original->quantity + 0.0005) {
            throw new RuntimeException("Transaction #{$original->id} is already reversed by {$already}.");
        }
        $sign = $original->signedQuantity() < 0 ? 1 : -1;

        return $this->post($original->inventory_item_id, $sign * $qty, 'reversal', $user, array_merge([
            'notes' => $notes ?? "Reversal of transaction #{$original->id}",
            'reference_type' => $original->reference_type,
            'reference_id' => $original->reference_id,
            'reference_label' => $original->reference_label,
            'reversal_of_id' => $original->id,
            'unit_cost' => $original->unit_cost,
        ], $context));
    }

    // ── Legacy API (manual stock form, older callers) ───────────────────────

    public function addStock(
        InventoryItem $item,
        float $quantity,
        ?string $notes,
        User $user,
        ?float $unitCost = null,
        string $type = 'purchase',
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryTransaction {
        return $this->post($item, abs($quantity), $type, $user, [
            'notes' => $notes, 'unit_cost' => $unitCost,
            'reference_type' => $referenceType, 'reference_id' => $referenceId,
        ]);
    }

    public function deductStock(
        InventoryItem $item,
        float $quantity,
        ?string $notes,
        User $user,
        string $type = 'usage',
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryTransaction {
        return $this->post($item, -abs($quantity), $type, $user, [
            'notes' => $notes, 'reference_type' => $referenceType, 'reference_id' => $referenceId,
        ]);
    }

    /** Physical count: set stock to $newQuantity via one signed adjustment row. */
    public function adjustStock(InventoryItem $item, float $newQuantity, ?string $notes, User $user, string $type = 'adjustment'): InventoryTransaction
    {
        if ($newQuantity < 0) {
            throw new RuntimeException('Stock cannot be negative.');
        }

        return DB::transaction(function () use ($item, $newQuantity, $notes, $user, $type) {
            $locked = InventoryItem::lockForUpdate()->findOrFail($item->id);
            $diff = round($newQuantity - (float) $locked->current_stock, 3);
            if ($diff == 0.0) {
                throw new RuntimeException('Counted quantity equals current stock; nothing to adjust.');
            }

            return $this->post($locked, $diff, $type, $user, ['notes' => $notes]);
        });
    }

    public function checkAndCreateAlerts(InventoryItem $item, User $triggeredBy): void
    {
        $existingUnresolved = InventoryStockAlert::where('inventory_item_id', $item->id)
            ->where('is_resolved', false)
            ->exists();

        if ($existingUnresolved) {
            return;
        }

        if ($item->isOutOfStock()) {
            InventoryStockAlert::create([
                'inventory_item_id' => $item->id,
                'alert_type' => 'out_of_stock',
                'stock_at_alert' => $item->current_stock,
            ]);

            $this->notificationService->sendToManagers(
                'out_of_stock',
                "Out of Stock: {$item->name}",
                "{$item->name} is completely out of stock.",
                route('admin.inventory.items.show', $item)
            );
        } elseif ($item->isLowStock()) {
            InventoryStockAlert::create([
                'inventory_item_id' => $item->id,
                'alert_type' => 'low_stock',
                'stock_at_alert' => $item->current_stock,
            ]);

            $this->notificationService->sendToManagers(
                'low_stock',
                "Low Stock: {$item->name}",
                "{$item->name} is running low ({$item->current_stock} {$item->unit} remaining).",
                route('admin.inventory.items.show', $item)
            );
        }
    }

    public function resolveAlertsIfRestocked(InventoryItem $item, ?User $user = null): void
    {
        if ($item->current_stock > $item->minimum_stock) {
            InventoryStockAlert::where('inventory_item_id', $item->id)
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'resolved_by' => $user?->id ?? auth()->id(),
                ]);
        }
    }

    private function recalcAverageCost(InventoryItem $item, float $oldQty, float $addedQty, float $unitCost): float
    {
        $oldCost = ($item->average_cost ?? 0) * $oldQty;
        $newCost = $unitCost * $addedQty;
        $totalQty = $oldQty + $addedQty;

        return $totalQty > 0 ? round(($oldCost + $newCost) / $totalQty, 2) : $unitCost;
    }
}
