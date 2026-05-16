<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryStockAlert;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(private NotificationService $notificationService) {}

    public function addStock(
        InventoryItem $item,
        float         $quantity,
        ?string       $notes,
        User          $user,
        ?float        $unitCost      = null,
        string        $type          = 'purchase',
        ?string       $referenceType = null,
        ?int          $referenceId   = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($item, $quantity, $notes, $user, $unitCost, $type, $referenceType, $referenceId) {
            $item    = InventoryItem::lockForUpdate()->find($item->id);
            $before  = (float) $item->current_stock;
            $after   = $before + $quantity;

            $item->update([
                'current_stock' => $after,
                'average_cost'  => $unitCost ? $this->recalcAverageCost($item, $before, $quantity, $unitCost) : $item->average_cost,
            ]);

            $txn = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type'              => $type,
                'quantity'          => $quantity,
                'balance_before'    => $before,
                'balance_after'     => $after,
                'unit_cost'         => $unitCost,
                'notes'             => $notes,
                'created_by'        => $user->id,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
            ]);

            $this->resolveAlertsIfRestocked($item->fresh());

            return $txn;
        });
    }

    public function deductStock(
        InventoryItem $item,
        float         $quantity,
        ?string       $notes,
        User          $user,
        string        $type          = 'usage',
        ?string       $referenceType = null,
        ?int          $referenceId   = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($item, $quantity, $notes, $user, $type, $referenceType, $referenceId) {
            $item   = InventoryItem::lockForUpdate()->find($item->id);
            $before = (float) $item->current_stock;

            if ($before < $quantity) {
                throw new \RuntimeException("Insufficient stock. Available: {$before} {$item->unit}");
            }

            $after = $before - $quantity;
            $item->update(['current_stock' => $after]);

            $txn = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type'              => $type,
                'quantity'          => $quantity,
                'balance_before'    => $before,
                'balance_after'     => $after,
                'notes'             => $notes,
                'created_by'        => $user->id,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
            ]);

            $this->checkAndCreateAlerts($item->fresh(), $user);

            return $txn;
        });
    }

    public function adjustStock(
        InventoryItem $item,
        float         $newQuantity,
        ?string       $notes,
        User          $user
    ): InventoryTransaction {
        return DB::transaction(function () use ($item, $newQuantity, $notes, $user) {
            $item   = InventoryItem::lockForUpdate()->find($item->id);
            $before = (float) $item->current_stock;

            $item->update(['current_stock' => $newQuantity]);

            $txn = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type'              => 'adjustment',
                'quantity'          => abs($newQuantity - $before),
                'balance_before'    => $before,
                'balance_after'     => $newQuantity,
                'notes'             => $notes,
                'created_by'        => $user->id,
            ]);

            $fresh = $item->fresh();
            if ($newQuantity <= $fresh->minimum_stock) {
                $this->checkAndCreateAlerts($fresh, $user);
            } else {
                $this->resolveAlertsIfRestocked($fresh);
            }

            return $txn;
        });
    }

    public function checkAndCreateAlerts(InventoryItem $item, User $triggeredBy): void
    {
        $existingUnresolved = InventoryStockAlert::where('inventory_item_id', $item->id)
            ->where('is_resolved', false)
            ->exists();

        if ($existingUnresolved) return;

        if ($item->isOutOfStock()) {
            InventoryStockAlert::create([
                'inventory_item_id' => $item->id,
                'alert_type'        => 'out_of_stock',
                'stock_at_alert'    => $item->current_stock,
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
                'alert_type'        => 'low_stock',
                'stock_at_alert'    => $item->current_stock,
            ]);

            $this->notificationService->sendToManagers(
                'low_stock',
                "Low Stock: {$item->name}",
                "{$item->name} is running low ({$item->current_stock} {$item->unit} remaining).",
                route('admin.inventory.items.show', $item)
            );
        }
    }

    public function resolveAlertsIfRestocked(InventoryItem $item): void
    {
        if ($item->current_stock > $item->minimum_stock) {
            InventoryStockAlert::where('inventory_item_id', $item->id)
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);
        }
    }

    private function recalcAverageCost(InventoryItem $item, float $oldQty, float $addedQty, float $unitCost): float
    {
        $oldCost   = ($item->average_cost ?? 0) * $oldQty;
        $newCost   = $unitCost * $addedQty;
        $totalQty  = $oldQty + $addedQty;

        return $totalQty > 0 ? ($oldCost + $newCost) / $totalQty : $unitCost;
    }
}
