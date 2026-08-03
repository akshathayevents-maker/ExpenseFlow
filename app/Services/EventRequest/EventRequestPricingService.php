<?php

namespace App\Services\EventRequest;

use App\Models\EventRequestMenuItem;
use Illuminate\Support\Collection;

/**
 * Turns a raw list of selected menu item IDs into priced, immutable
 * snapshots (so later price/menu edits never change what a client already
 * agreed to) plus the resulting per-person and estimated total figures.
 */
class EventRequestPricingService
{
    /**
     * @param  int[]  $menuItemIds
     * @return array{items: Collection<int, array>, per_person_cost: float}
     */
    public function priceSelection(array $menuItemIds): array
    {
        $menuItems = EventRequestMenuItem::with('category')
            ->whereIn('id', $menuItemIds)
            ->get();

        $items = $menuItems->map(fn (EventRequestMenuItem $item) => [
            'menu_item_id'              => $item->id,
            'name_snapshot'             => $item->name,
            'category_name_snapshot'   => $item->category?->name,
            'is_veg_snapshot'           => $item->is_veg,
            'price_per_person_snapshot' => (float) $item->price_per_person,
        ]);

        return [
            'items'           => $items,
            'per_person_cost' => (float) $items->sum('price_per_person_snapshot'),
        ];
    }

    public function estimatedTotal(float $perPersonCost, int $guestCount): float
    {
        return round($perPersonCost * max(0, $guestCount), 2);
    }
}
