<?php

namespace App\Services\Inventory;

use App\Models\MaterialRequest;
use Illuminate\Support\Collection;

/**
 * Aggregates material request lines into a per-item reconciliation:
 * requested, approved, issued, returned, consumed, wasted, unexplained, cost.
 * Cancelled / rejected / draft requests are ignored. Quantities in stock units.
 */
class ConsumptionService
{
    /** @param Collection<int, MaterialRequest> $requests */
    public function summarize(Collection $requests): array
    {
        $rows = [];
        $live = $requests->filter(fn ($r) => ! in_array($r->status, ['draft', 'cancelled', 'rejected'], true));
        foreach ($live as $r) {
            $r->loadMissing('items.item');
            foreach ($r->items as $l) {
                $id = $l->inventory_item_id;
                $rows[$id] ??= [
                    'item' => $l->item->name, 'unit' => $l->item->unit,
                    'requested' => 0, 'approved' => 0, 'issued' => 0, 'returned' => 0,
                    'consumed' => 0, 'wasted' => 0, 'unexplained' => 0, 'cost' => 0, 'open' => 0,
                ];
                $row = &$rows[$id];
                $row['requested'] += $l->requested_base_quantity;
                $row['approved'] += (float) $l->approved_base_quantity;
                $row['issued'] += (float) $l->issued_base_quantity;
                $row['returned'] += $l->returned_base_quantity;
                $row['consumed'] += (float) $l->consumed_base_quantity;
                $row['wasted'] += (float) $l->wasted_base_quantity;
                $row['unexplained'] += (float) $l->unexplained_base_quantity;
                $net = $r->status === 'approved' ? (float) $l->approved_base_quantity : $l->netIssued();
                $row['cost'] += $net * (float) $l->unit_cost;
                if ($r->status === 'issued') {
                    $row['open'] += $l->netIssued();   // handed over, usage not yet recorded
                }
                unset($row);
            }
        }
        foreach ($rows as &$row) {
            foreach ($row as $k => $v) {
                if (is_float($v) || is_int($v)) {
                    $row[$k] = round($v, $k === 'cost' ? 2 : 3);
                }
            }
        }
        unset($row);
        uasort($rows, fn ($a, $b) => strcmp($a['item'], $b['item']));

        return [
            'rows' => $rows,
            'cost' => round(array_sum(array_column($rows, 'cost')), 2),
            'wasted' => round(array_sum(array_map(fn ($r) => $r['wasted'], $rows)), 3),
            'unexplained' => round(array_sum(array_map(fn ($r) => $r['unexplained'], $rows)), 3),
            'requests' => $requests->count(),
        ];
    }
}
