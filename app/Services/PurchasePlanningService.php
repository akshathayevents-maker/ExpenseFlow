<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PurchaseItem;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchasePlanningService
{
    /** Days of kitchen usage used to size suggestions for items that have no reorder level. */
    public const USAGE_DAYS = 30;

    /**
     * What to buy, and how much:
     *  - items with a reorder level at or below it → top up to maximum (or 2 × minimum)
     *  - items without a reorder level that are empty but were issued to the kitchen in the last USAGE_DAYS → one period's usage
     * minus what is already coming (open purchases) or planned (plans waiting approval / approved, not yet bought).
     * Each item gets: suggested_quantity, deficit, priority, reason, used_recent, on_order, in_plans, covered, tracked.
     */
    public function getSuggestions(): Collection
    {
        $since = now()->subDays(self::USAGE_DAYS);
        $used = InventoryTransaction::where('created_at', '>=', $since)->whereIn('type', ['material_issue', 'material_return'])
            ->selectRaw("inventory_item_id, SUM(CASE WHEN type = 'material_issue' THEN ABS(quantity) ELSE -ABS(quantity) END) AS q")
            ->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id')->map(fn ($q) => max(0, (float) $q));
        $onOrder = PurchaseItem::whereHas('purchase', fn ($q) => $q->whereIn('status', ['submitted', 'approved', 'partially_received']))
            ->selectRaw('inventory_item_id, SUM(base_quantity - received_base_quantity - closed_base_quantity) AS q')
            ->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id')->map(fn ($q) => max(0, (float) $q));
        $inPlans = PurchasePlanItem::whereHas('plan', fn ($q) => $q->whereIn('status', ['submitted', 'approved']))
            ->selectRaw('inventory_item_id, SUM(COALESCE(approved_quantity, suggested_quantity)) AS q')
            ->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id')->map(fn ($q) => (float) $q);
        $usedIds = $used->filter(fn ($q) => $q > 0)->keys()->all();

        return InventoryItem::with('category')->active()
            // below minimum, or empty (with or without a minimum — empty untracked items are listed last, quantity left to the user)
            ->where(fn ($q) => $q->where(fn ($w) => $w->where('minimum_stock', '>', 0)->whereColumn('current_stock', '<=', 'minimum_stock'))
                ->orWhere('current_stock', '<=', 0))
            ->get()
            ->map(function ($item) use ($used, $onOrder, $inPlans) {
                $tracked = (float) $item->minimum_stock > 0;
                $recent = round((float) ($used[$item->id] ?? 0), 3);
                $coming = round((float) ($onOrder[$item->id] ?? 0) + (float) ($inPlans[$item->id] ?? 0), 3);
                $deficit = $tracked ? max(0, (float) $item->minimum_stock - (float) $item->current_stock) : max(0, -(float) $item->current_stock);
                $need = $tracked
                    ? ((float) $item->maximum_stock > 0 ? (float) $item->maximum_stock : (float) $item->minimum_stock * 2) - (float) $item->current_stock
                    : $recent;
                $item->tracked = $tracked;
                $item->used_recent = $recent;
                $item->on_order = round((float) ($onOrder[$item->id] ?? 0), 3);
                $item->in_plans = round((float) ($inPlans[$item->id] ?? 0), 3);
                $item->deficit = round($deficit, 3);
                // empty, no minimum, not used recently: nothing to base a quantity on — the user decides
                $item->untracked_empty = ! $tracked && $recent <= 0;
                $item->suggested_quantity = $item->untracked_empty ? null : round(max(0, $need - $coming), 3);
                $item->covered = ! $item->untracked_empty && $need > 0 && $item->suggested_quantity <= 0
                    || ($item->untracked_empty && $coming > 0);
                $out = $item->isOutOfStock();
                $item->priority = match (true) {
                    $item->covered => 'low',
                    $item->untracked_empty => 'empty',
                    $out && $recent > 0 => 'urgent',
                    $out => 'high',
                    $deficit > (float) $item->minimum_stock * 0.5 => 'high',
                    default => 'normal',
                };
                $item->reason = match (true) {
                    $tracked => ($out ? 'Out of stock' : 'Below minimum').' · min '.$this->fmt($item->minimum_stock).' '.$item->unit,
                    $item->untracked_empty => 'Out of stock · no minimum set',
                    default => 'Out of stock · used '.$this->fmt($recent).' '.$item->unit.' in '.self::USAGE_DAYS.' days',
                };

                return $item;
            })
            ->sortBy([fn ($a, $b) => ['urgent' => 0, 'high' => 1, 'normal' => 2, 'empty' => 3, 'low' => 4][$a->priority] <=> ['urgent' => 0, 'high' => 1, 'normal' => 2, 'empty' => 3, 'low' => 4][$b->priority],
                fn ($a, $b) => strcasecmp($a->name, $b->name)])
            ->values();
    }

    /** Empty items with neither a reorder level nor recent usage — not suggested, but worth configuring. */
    public function unconfiguredEmptyCount(): int
    {
        $usedIds = InventoryTransaction::where('created_at', '>=', now()->subDays(self::USAGE_DAYS))->where('type', 'material_issue')
            ->distinct()->pluck('inventory_item_id')->all();

        return InventoryItem::active()->where('minimum_stock', '<=', 0)->where('current_stock', '<=', 0)
            ->whereNotIn('id', $usedIds ?: [0])->count();
    }

    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.') ?: '0';
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
