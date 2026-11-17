<?php

namespace App\Services\Inventory;

use App\Models\InventoryTransaction;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Management reports for material control. Grouping is done in PHP so the same
 * code runs on PostgreSQL (production) and SQLite (tests).
 * Findings are "worth checking" signals with their supporting rows — never
 * accusations.
 *
 * Every report returns ['title', 'columns' => [key => label], 'rows' => [...], 'note' => ?string].
 */
class LossControlReportService
{
    public const REPORTS = [
        'daily-purchases' => 'Daily purchases',
        'monthly-purchases' => 'Monthly purchases',
        'supplier-purchases' => 'Supplier-wise purchases',
        'price-history' => 'Item purchase price history',
        'movements' => 'Inventory movements',
        'material-issues' => 'Material issues',
        'meal-consumption' => 'Meal-wise consumption',
        'hall-consumption' => 'Hall order consumption & profitability',
        'expected-vs-actual' => 'Requested vs actually used',
        'wastage' => 'Wastage',
        'adjustments' => 'Stock adjustments & corrections',
        'employee-requests' => 'Employee-wise requests',
        'reconciliation' => 'Stock reconciliation (usage not recorded)',
        'cost-per-guest' => 'Food cost per guest',
    ];

    /** $f: from, to (Y-m-d), item_id, user_id, meal_type, hall_booking_id, vendor_id, category_id */
    public function run(string $key, array $f): array
    {
        $method = lcfirst(str_replace('-', '', ucwords($key, '-')));
        abort_unless(isset(self::REPORTS[$key]) && method_exists($this, $method), 404);

        return ['title' => self::REPORTS[$key], 'note' => null] + $this->{$method}($this->range($f), $f);
    }

    // ── Purchases ───────────────────────────────────────────────────────────

    private function purchaseLines(array $r, array $f): Collection
    {
        return PurchaseItem::with(['purchase.vendor', 'item.category'])
            ->whereHas('purchase', fn ($q) => $q->whereNotIn('status', ['draft', 'rejected', 'cancelled'])
                ->whereBetween('purchase_date', [$r[0]->toDateString(), $r[1]->toDateString()])
                ->when($f['vendor_id'] ?? null, fn ($w, $v) => $w->where('vendor_id', $v))
                ->when($f['user_id'] ?? null, fn ($w, $v) => $w->where('created_by', $v)))
            ->when($f['item_id'] ?? null, fn ($q, $v) => $q->where('inventory_item_id', $v))
            ->when($f['category_id'] ?? null, fn ($q, $v) => $q->whereHas('item', fn ($w) => $w->where('inventory_category_id', $v)))
            ->get();
    }

    private function purchaseGroup(Collection $lines, callable $key, string $label): array
    {
        $rows = $lines->groupBy($key)->map(fn ($g, $k) => [
            $label => $k,
            'bills' => $g->pluck('purchase_id')->unique()->count(),
            'lines' => $g->count(),
            'amount' => round($g->sum(fn ($l) => $l->amount * (1 + $l->tax_percent / 100)), 2),
            'received' => round($g->sum(fn ($l) => $l->base_quantity > 0 ? $l->amount * (1 + $l->tax_percent / 100) * min(1, $l->received_base_quantity / $l->base_quantity) : 0), 2),
        ])->sortKeys()->values()->all();

        return ['columns' => [$label => ucfirst($label), 'bills' => 'Purchases', 'lines' => 'Lines', 'amount' => 'Amount ₹', 'received' => 'Received value ₹'], 'rows' => $rows];
    }

    private function dailyPurchases(array $r, array $f): array
    {
        return $this->purchaseGroup($this->purchaseLines($r, $f), fn ($l) => $l->purchase->purchase_date->toDateString(), 'date');
    }

    private function monthlyPurchases(array $r, array $f): array
    {
        return $this->purchaseGroup($this->purchaseLines($r, $f), fn ($l) => $l->purchase->purchase_date->format('Y-m'), 'month');
    }

    private function supplierPurchases(array $r, array $f): array
    {
        return $this->purchaseGroup($this->purchaseLines($r, $f), fn ($l) => $l->purchase->supplierLabel(), 'supplier');
    }

    private function priceHistory(array $r, array $f): array
    {
        $lines = $this->purchaseLines($r, $f)->filter(fn ($l) => $l->base_quantity > 0);
        $avg = $lines->groupBy('inventory_item_id')->map(fn ($g) => $g->avg(fn ($l) => $l->amount / $l->base_quantity));
        $rows = $lines->sortBy(fn ($l) => $l->item->name.$l->purchase->purchase_date->toDateString())->map(function ($l) use ($avg) {
            $rate = round($l->amount / $l->base_quantity, 2);
            $a = (float) $avg[$l->inventory_item_id];
            $dev = $a > 0 ? round(($rate - $a) / $a * 100, 1) : 0;

            return [
                'date' => $l->purchase->purchase_date->toDateString(), 'item' => $l->item->name,
                'supplier' => $l->purchase->supplierLabel(), 'qty' => $l->quantity.' '.$l->unit,
                'rate' => $rate.' / '.$l->item->unit, 'avg' => round($a, 2), 'deviation' => $dev.'%',
                '_flag' => abs($dev) >= 20, '_link' => route('inventory.purchases.show', $l->purchase_id),
            ];
        })->values()->all();

        return ['columns' => ['date' => 'Date', 'item' => 'Item', 'supplier' => 'Supplier', 'qty' => 'Qty', 'rate' => 'Rate ₹ (stock unit)', 'avg' => 'Period avg ₹', 'deviation' => 'vs avg'],
            'rows' => $rows, 'note' => 'Highlighted rows are 20% or more away from the period average for that item.'];
    }

    // ── Stock ledger ────────────────────────────────────────────────────────

    private function ledger(array $r, array $f, ?array $types = null): Collection
    {
        return InventoryTransaction::with(['item.category', 'creator'])
            ->whereBetween('created_at', [$r[0]->copy()->utc(), $r[1]->copy()->utc()])
            ->when($types, fn ($q) => $q->whereIn('type', $types))
            ->when($f['item_id'] ?? null, fn ($q, $v) => $q->where('inventory_item_id', $v))
            ->when($f['user_id'] ?? null, fn ($q, $v) => $q->where('created_by', $v))
            ->when($f['category_id'] ?? null, fn ($q, $v) => $q->whereHas('item', fn ($w) => $w->where('inventory_category_id', $v)))
            ->orderBy('created_at')->orderBy('id')->limit(5000)->get();
    }

    private function ledgerRows(Collection $txns): array
    {
        return $txns->map(fn ($t) => [
            'when' => $t->created_at->timezone('Asia/Kolkata')->format('Y-m-d H:i'), 'item' => $t->item?->name, 'type' => $t->typeLabel(),
            'qty' => ($t->signedQuantity() > 0 ? '+' : '').$t->signedQuantity().' '.$t->item?->unit,
            'balance' => $t->balance_after, 'reference' => $t->reference_label ?? '—', 'by' => $t->creator?->name, 'notes' => $t->notes,
            '_link' => $this->txnLink($t),
        ])->all();
    }

    private function movements(array $r, array $f): array
    {
        return ['columns' => ['when' => 'When', 'item' => 'Item', 'type' => 'Type', 'qty' => 'Qty', 'balance' => 'Balance', 'reference' => 'Reference', 'by' => 'By', 'notes' => 'Notes'],
            'rows' => $this->ledgerRows($this->ledger($r, $f))];
    }

    private function adjustments(array $r, array $f): array
    {
        return ['columns' => ['when' => 'When', 'item' => 'Item', 'type' => 'Type', 'qty' => 'Qty', 'by' => 'By', 'notes' => 'Reason'],
            'rows' => $this->ledgerRows($this->ledger($r, $f, ['adjustment', 'correction', 'wastage', 'usage']))];
    }

    // ── Material requests ───────────────────────────────────────────────────

    private function requestLines(array $r, array $f, array $statuses = ['approved', 'issued', 'reconciled']): Collection
    {
        return MaterialRequestItem::with(['item', 'request.requester', 'request.mealEntry.client', 'request.hallBooking'])
            ->whereHas('request', fn ($q) => $q->whereIn('status', $statuses)
                ->whereBetween('required_date', [$r[0]->toDateString(), $r[1]->toDateString()])
                ->when($f['user_id'] ?? null, fn ($w, $v) => $w->where('requested_by', $v))
                ->when($f['meal_type'] ?? null, fn ($w, $v) => $w->where('meal_type', $v))
                ->when($f['hall_booking_id'] ?? null, fn ($w, $v) => $w->where('hall_booking_id', $v)))
            ->when($f['item_id'] ?? null, fn ($q, $v) => $q->where('inventory_item_id', $v))
            ->when($f['category_id'] ?? null, fn ($q, $v) => $q->whereHas('item', fn ($w) => $w->where('inventory_category_id', $v)))
            ->where('line_status', '!=', 'rejected')
            ->get();
    }

    private function materialIssues(array $r, array $f): array
    {
        $rows = $this->requestLines($r, $f)->sortBy(fn ($l) => $l->request->required_date->toDateString().$l->id)->map(fn ($l) => [
            'date' => $l->request->required_date->toDateString(), 'request' => $l->request->reference(), 'for' => $l->request->forLabel(),
            'item' => $l->item->name, 'approved' => $l->approved_base_quantity, 'issued' => $l->issued_base_quantity ?? '—',
            'unit' => $l->item->unit, 'requested_by' => $l->request->requester?->name, 'collected' => $l->request->collector?->name ?? $l->request->collected_by_name ?? '—',
            '_flag' => $l->request->source === 'other', '_link' => route('inventory.material-requests.show', $l->material_request_id),
        ])->values()->all();

        return ['columns' => ['date' => 'Needed', 'request' => 'Request', 'for' => 'For', 'item' => 'Item', 'approved' => 'Approved', 'issued' => 'Handed over', 'unit' => 'Unit', 'requested_by' => 'Requested by', 'collected' => 'Collected by'],
            'rows' => $rows, 'note' => 'Highlighted: issues not linked to a meal or hall order.'];
    }

    private function consumptionBy(Collection $lines, callable $key, callable $meta): array
    {
        return $lines->groupBy($key)->map(function ($g) use ($meta) {
            $req = $g->first()->request;
            $cost = $g->sum(fn ($l) => ($l->request->status === 'approved' ? $l->approved_base_quantity : $l->netIssued()) * (float) $l->unit_cost);
            $guests = $g->pluck('request')->unique('id')->sum('guest_count');

            return $meta($req, $g) + [
                'guests' => $guests ?: '—', 'cost' => round($cost, 2),
                'per_guest' => $guests ? round($cost / $guests, 2) : '—',
                'wasted_value' => round($g->sum(fn ($l) => (float) $l->wasted_base_quantity * (float) $l->unit_cost), 2),
                'unexplained_value' => round($g->sum(fn ($l) => (float) $l->unexplained_base_quantity * (float) $l->unit_cost), 2),
            ];
        })->values()->all();
    }

    private function mealConsumption(array $r, array $f): array
    {
        $lines = $this->requestLines($r, $f)->filter(fn ($l) => $l->request->source === 'meal');
        $rows = $this->consumptionBy($lines, fn ($l) => $l->request->meal_entry_id.'|'.$l->request->meal_type, fn ($req) => [
            'date' => $req->mealEntry?->entry_date?->toDateString(), 'client' => $req->mealEntry?->client?->name,
            'meal' => ucfirst((string) $req->meal_type), '_link' => $req->mealEntry ? route('meal-register.entries.show', $req->mealEntry) : null,
        ]);
        $avg = collect($rows)->filter(fn ($x) => is_numeric($x['per_guest']))->groupBy('meal')->map(fn ($g) => $g->avg('per_guest'));
        foreach ($rows as &$row) {
            $a = $avg[$row['meal']] ?? null;
            $row['_flag'] = is_numeric($row['per_guest']) && $a && $row['per_guest'] > 1.3 * $a;
        }
        unset($row);

        return ['columns' => ['date' => 'Date', 'client' => 'Client', 'meal' => 'Meal', 'guests' => 'People', 'cost' => 'Material ₹', 'per_guest' => '₹ / person', 'wasted_value' => 'Wasted ₹', 'unexplained_value' => 'Unexplained ₹'],
            'rows' => collect($rows)->sortBy('date')->values()->all(), 'note' => 'Highlighted: cost per person 30% above the average for that meal.'];
    }

    private function hallConsumption(array $r, array $f): array
    {
        $lines = $this->requestLines($r, $f)->filter(fn ($l) => $l->request->source === 'hall' && $l->request->hallBooking);
        $rows = $this->consumptionBy($lines, fn ($l) => $l->request->hall_booking_id, function ($req) {
            $b = $req->hallBooking->loadMissing('mealPlan', 'foodSplits');

            return ['date' => $b->booking_date->toDateString(), 'order' => $b->booking_reference.' · '.$b->customer_name,
                'revenue' => round($b->meal_cost, 2), '_link' => route('hall.bookings.show', $b)];
        });
        foreach ($rows as &$row) {
            $row['margin'] = $row['revenue'] > 0 ? round($row['revenue'] - $row['cost'], 2) : '—';
            $row['food_cost_pct'] = $row['revenue'] > 0 ? round($row['cost'] / $row['revenue'] * 100, 1).'%' : '—';
            $row['_flag'] = $row['revenue'] > 0 && $row['cost'] / $row['revenue'] > 0.45;
        }
        unset($row);

        return ['columns' => ['date' => 'Date', 'order' => 'Hall order', 'guests' => 'Guests', 'cost' => 'Material ₹', 'per_guest' => '₹ / guest', 'revenue' => 'Food revenue ₹', 'margin' => 'Margin ₹', 'food_cost_pct' => 'Food cost %', 'wasted_value' => 'Wasted ₹', 'unexplained_value' => 'Unexplained ₹'],
            'rows' => collect($rows)->sortBy('date')->values()->all(),
            'note' => 'Material cost only (store average cost). Labour, fuel and overheads are not included. Highlighted: food cost above 45% of food revenue.'];
    }

    private function expectedVsActual(array $r, array $f): array
    {
        $rows = $this->requestLines($r, $f, ['reconciled'])->map(function ($l) {
            $exp = $l->requested_base_quantity;
            $act = (float) $l->consumed_base_quantity + (float) $l->wasted_base_quantity + (float) $l->unexplained_base_quantity;
            $var = $exp > 0 ? round(($act - $exp) / $exp * 100, 1) : 0;

            return ['date' => $l->request->required_date->toDateString(), 'for' => $l->request->preparation_name ?: $l->request->forLabel(),
                'guests' => $l->request->guest_count ?: '—', 'item' => $l->item->name, 'expected' => $exp, 'issued' => $l->issued_base_quantity,
                'used' => $l->consumed_base_quantity, 'wasted' => $l->wasted_base_quantity, 'unexplained' => $l->unexplained_base_quantity,
                'variance' => $var.'%', '_flag' => $var > 15, '_link' => route('inventory.material-requests.show', $l->material_request_id)];
        })->sortBy('date')->values()->all();

        return ['columns' => ['date' => 'Date', 'for' => 'Preparation', 'guests' => 'People', 'item' => 'Item', 'expected' => 'Requested', 'issued' => 'Issued', 'used' => 'Used', 'wasted' => 'Wasted', 'unexplained' => 'Unexplained', 'variance' => 'Out vs requested'],
            'rows' => $rows, 'note' => 'Highlighted: material that left the store (used + wasted + unexplained) exceeds the request by more than 15%.'];
    }

    private function wastage(array $r, array $f): array
    {
        $kitchen = $this->requestLines($r, $f, ['reconciled'])->filter(fn ($l) => $l->wasted_base_quantity > 0)->map(fn ($l) => [
            'date' => $l->request->required_date->toDateString(), 'where' => 'Kitchen · '.($l->request->preparation_name ?: $l->request->forLabel()),
            'item' => $l->item->name, 'qty' => $l->wasted_base_quantity.' '.$l->item->unit,
            'value' => round($l->wasted_base_quantity * (float) $l->unit_cost, 2), 'reason' => $l->wastage_reason,
            'by' => $l->request->requester?->name, '_link' => route('inventory.material-requests.show', $l->material_request_id),
            '_flag' => $l->issued_base_quantity > 0 && $l->wasted_base_quantity / $l->issued_base_quantity * 100 > (float) config('inventory.wastage_review_percent', 10),
        ]);
        $store = $this->ledger($r, $f, ['wastage'])->map(fn ($t) => [
            'date' => $t->created_at->toDateString(), 'where' => 'Store', 'item' => $t->item?->name, 'qty' => $t->quantity.' '.$t->item?->unit,
            'value' => round($t->quantity * (float) ($t->unit_cost ?? $t->item?->average_cost), 2), 'reason' => $t->notes, 'by' => $t->creator?->name,
            '_link' => $t->item ? route('admin.inventory.items.show', $t->item) : null, '_flag' => false,
        ]);

        return ['columns' => ['date' => 'Date', 'where' => 'Where', 'item' => 'Item', 'qty' => 'Qty', 'value' => 'Value ₹', 'reason' => 'Reason', 'by' => 'Recorded by'],
            'rows' => $kitchen->concat($store)->sortBy('date')->values()->all()];
    }

    private function employeeRequests(array $r, array $f): array
    {
        $lines = $this->requestLines($r, $f);
        $rows = $lines->groupBy(fn ($l) => $l->request->requested_by)->map(function ($g) {
            $reqs = $g->pluck('request')->unique('id');
            $value = $g->sum(fn ($l) => (float) $l->approved_base_quantity * (float) $l->unit_cost);
            $guests = $reqs->sum('guest_count');

            return ['employee' => $reqs->first()->requester?->name, 'requests' => $reqs->count(),
                'unlinked' => $reqs->where('source', 'other')->count(), 'value' => round($value, 2),
                'per_guest' => $guests ? round($value / $guests, 2) : '—',
                'reduced' => $g->where('line_status', 'reduced')->count(),
                'wasted_value' => round($g->sum(fn ($l) => (float) $l->wasted_base_quantity * (float) $l->unit_cost), 2),
                'unexplained_value' => round($g->sum(fn ($l) => (float) $l->unexplained_base_quantity * (float) $l->unit_cost), 2)];
        })->values();
        $median = $rows->pluck('per_guest')->filter(fn ($v) => is_numeric($v))->median();
        $rows = $rows->map(fn ($x) => $x + ['_flag' => is_numeric($x['per_guest']) && $median && $x['per_guest'] > 1.3 * $median])->all();

        return ['columns' => ['employee' => 'Employee', 'requests' => 'Requests', 'unlinked' => 'Not linked to meal/hall', 'value' => 'Approved value ₹', 'per_guest' => '₹ / person', 'reduced' => 'Lines cut by approver', 'wasted_value' => 'Wasted ₹', 'unexplained_value' => 'Unexplained ₹'],
            'rows' => $rows, 'note' => 'Highlighted: approved value per person 30% above the median across employees.'];
    }

    private function reconciliation(array $r, array $f): array
    {
        $today = now('Asia/Kolkata')->toDateString();
        $rows = MaterialRequest::with(['requester', 'items.item', 'mealEntry.client', 'hallBooking'])
            ->whereIn('status', ['issued', 'reconciled'])
            ->whereBetween('required_date', [$r[0]->toDateString(), $r[1]->toDateString()])
            ->when($f['user_id'] ?? null, fn ($q, $v) => $q->where('requested_by', $v))
            ->get()
            ->filter(fn ($m) => $m->status === 'issued' || $m->reconciliation_status === 'pending_review' || $m->items->sum('unexplained_base_quantity') > 0)
            ->map(fn ($m) => [
                'date' => $m->required_date->toDateString(), 'request' => $m->reference(), 'for' => $m->forLabel(),
                'by' => $m->requester?->name,
                'state' => $m->status === 'issued' ? 'Usage not recorded' : ($m->reconciliation_status === 'pending_review' ? 'Waiting admin review' : 'Accepted with difference'),
                'out_value' => round($m->items->sum(fn ($l) => $l->netIssued() * (float) $l->unit_cost), 2),
                'unexplained_value' => round($m->items->sum(fn ($l) => (float) $l->unexplained_base_quantity * (float) $l->unit_cost), 2),
                '_flag' => $m->status === 'issued' && $m->required_date->toDateString() < $today,
                '_link' => route('inventory.material-requests.show', $m),
            ])->sortBy('date')->values()->all();

        return ['columns' => ['date' => 'Needed', 'request' => 'Request', 'for' => 'For', 'by' => 'Requested by', 'state' => 'State', 'out_value' => 'Material out ₹', 'unexplained_value' => 'Unexplained ₹'],
            'rows' => $rows, 'note' => 'Highlighted: handed over, the date has passed and usage is still not recorded.'];
    }

    private function costPerGuest(array $r, array $f): array
    {
        $lines = $this->requestLines($r, $f)->filter(fn ($l) => in_array($l->request->source, ['meal', 'hall'], true));
        $rows = $lines->groupBy(fn ($l) => $l->request->required_date->toDateString())->map(function ($g, $date) {
            $reqs = $g->pluck('request')->unique('id');
            $cost = $g->sum(fn ($l) => ($l->request->status === 'approved' ? $l->approved_base_quantity : $l->netIssued()) * (float) $l->unit_cost);
            $guests = $reqs->unique(fn ($q) => $q->source.($q->meal_entry_id ?? '').($q->meal_type ?? '').($q->hall_booking_id ?? ''))->sum('guest_count');

            return ['date' => $date, 'meals' => $reqs->where('source', 'meal')->count(), 'events' => $reqs->where('source', 'hall')->count(),
                'guests' => $guests, 'cost' => round($cost, 2), 'per_guest' => $guests ? round($cost / $guests, 2) : '—'];
        })->sortKeys()->values()->all();

        return ['columns' => ['date' => 'Date', 'meals' => 'Meal requests', 'events' => 'Hall requests', 'guests' => 'People', 'cost' => 'Material ₹', 'per_guest' => '₹ / person'], 'rows' => $rows];
    }

    // ── Dashboard signals ───────────────────────────────────────────────────

    /** "Worth checking" signals with counts and a report to open. */
    public function signals(array $f): array
    {
        $r = $this->range($f);
        $count = fn (string $key) => collect($this->run($key, $f)['rows'])->where('_flag', true)->count();
        $adjItems = $this->ledger($r, $f, ['adjustment', 'correction'])->groupBy('inventory_item_id')->filter(fn ($g) => $g->count() >= 3);
        $repeatWaste = $this->requestLines($r, $f, ['reconciled'])->filter(fn ($l) => $l->wasted_base_quantity > 0)
            ->groupBy('inventory_item_id')->filter(fn ($g) => $g->count() >= 3);
        $out = $this->ledger($r, $f, ['usage', 'material_issue']);
        $otherIds = MaterialRequest::whereIn('id', $out->where('reference_type', 'material_request')->pluck('reference_id')->unique())
            ->where('source', 'other')->pluck('id')->flip();
        $unlinkedOut = $out->filter(fn ($t) => $t->type === 'usage' || isset($otherIds[$t->reference_id]));

        return [
            ['label' => 'Employees requesting much more per person', 'count' => $count('employee-requests'), 'report' => 'employee-requests'],
            ['label' => 'Meals costing far more per person than usual', 'count' => $count('meal-consumption'), 'report' => 'meal-consumption'],
            ['label' => 'Purchases at unusual prices', 'count' => $count('price-history'), 'report' => 'price-history'],
            ['label' => 'Stock issued without a meal or hall order', 'count' => $unlinkedOut->count(), 'report' => 'material-issues'],
            ['label' => 'Items adjusted 3+ times', 'count' => $adjItems->count(), 'report' => 'adjustments'],
            ['label' => 'Items wasted repeatedly (3+ times)', 'count' => $repeatWaste->count(), 'report' => 'wastage'],
            ['label' => 'Usage not recorded after the date', 'count' => $count('reconciliation'), 'report' => 'reconciliation'],
            ['label' => 'More used than requested (>15%)', 'count' => $count('expected-vs-actual'), 'report' => 'expected-vs-actual'],
        ];
    }

    public function totals(array $f): array
    {
        $r = $this->range($f);
        $lines = $this->requestLines($r, $f);

        return [
            'purchased' => round($this->purchaseLines($r, $f)->sum(fn ($l) => $l->amount * (1 + $l->tax_percent / 100)), 2),
            'issued' => round($lines->sum(fn ($l) => ($l->request->status === 'approved' ? $l->approved_base_quantity : $l->netIssued()) * (float) $l->unit_cost), 2),
            'wasted' => round($lines->sum(fn ($l) => (float) $l->wasted_base_quantity * (float) $l->unit_cost), 2),
            'unexplained' => round($lines->sum(fn ($l) => (float) $l->unexplained_base_quantity * (float) $l->unit_cost), 2),
        ];
    }

    private function range(array $f): array
    {
        $tz = 'Asia/Kolkata';
        $to = ! empty($f['to']) ? Carbon::parse($f['to'], $tz)->endOfDay() : now($tz)->endOfDay();
        $from = ! empty($f['from']) ? Carbon::parse($f['from'], $tz)->startOfDay() : $to->copy()->subDays(29)->startOfDay();

        return [$from, $to];   // business dates (IST); ledger() converts to UTC for timestamps
    }

    private function txnLink(InventoryTransaction $t): ?string
    {
        return match ($t->reference_type) {
            'material_request' => route('inventory.material-requests.show', $t->reference_id),
            'purchase' => route('inventory.purchases.show', $t->reference_id),
            default => $t->item ? route('admin.inventory.items.show', $t->item) : null,
        };
    }
}
