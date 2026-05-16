<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchasePlanningService
{
    public function getSuggestions(): Collection
    {
        return InventoryItem::with('category')
            ->active()
            ->critical()
            ->orderByRaw('current_stock / NULLIF(minimum_stock, 0) ASC')
            ->get()
            ->map(function ($item) {
                $deficit   = max(0, $item->minimum_stock - $item->current_stock);
                $suggested = $item->maximum_stock
                    ? ($item->maximum_stock - $item->current_stock)
                    : $deficit * 2;

                $item->suggested_quantity = round($suggested, 3);
                $item->deficit            = round($deficit, 3);
                $item->priority           = $item->isOutOfStock() ? 'urgent' : ($deficit > $item->minimum_stock * 0.5 ? 'high' : 'normal');
                return $item;
            });
    }

    public function createFromSuggestions(array $validated, User $user): PurchasePlan
    {
        return DB::transaction(function () use ($validated, $user) {
            $plan = PurchasePlan::create([
                'title'        => $validated['title'],
                'planned_date' => $validated['planned_date'],
                'notes'        => $validated['notes'] ?? null,
                'created_by'   => $user->id,
            ]);

            foreach ($validated['items'] as $itemData) {
                if (empty($itemData['selected'])) continue;

                PurchasePlanItem::create([
                    'purchase_plan_id'   => $plan->id,
                    'inventory_item_id'  => $itemData['inventory_item_id'],
                    'suggested_quantity' => $itemData['quantity'],
                    'estimated_unit_cost'=> $itemData['unit_cost'] ?? null,
                    'priority'           => $itemData['priority'] ?? 'normal',
                    'notes'              => $itemData['notes'] ?? null,
                ]);
            }

            return $plan;
        });
    }

    public function approve(PurchasePlan $plan, User $approver): void
    {
        $plan->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }
}
