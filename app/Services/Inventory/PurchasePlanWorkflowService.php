<?php

namespace App\Services\Inventory;

use App\Models\GoodsReceiptItem;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanItem;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Purchase plans prepared by staff:
 *   draft → submitted → approved | rejected (sent back, editable) → ordered → completed / cancelled.
 * Buying happens through normal Purchases created from the plan (PurchaseService); goods come in through the
 * normal receipt flow (GoodsReceiptService — stock only rises there). This service only plans, links and compares:
 * per line approved vs ordered vs received, and how a plan is closed when what arrived differs from the plan.
 */
class PurchasePlanWorkflowService
{
    public function __construct(
        private PurchaseService $purchases,
        private AuditLogService $audit,
        private NotificationService $notifications,
    ) {}

    /**
     * Create / update a plan. $data: title, planned_date, notes, lines[]: inventory_item_id, quantity (stock unit),
     * estimated_unit_cost?, priority?, notes?
     */
    public function save(array $data, User $user, ?PurchasePlan $plan = null, bool $submit = false): PurchasePlan
    {
        if ($plan && ! $plan->isEditable()) {
            throw new RuntimeException('This plan can no longer be changed.');
        }
        $lines = $this->cleanLines($data['lines'] ?? []);

        return DB::transaction(function () use ($data, $user, $plan, $submit, $lines) {
            $attrs = ['title' => trim($data['title']), 'planned_date' => $data['planned_date'], 'notes' => $data['notes'] ?? null];
            if ($plan) {
                $plan->update($attrs + ['status' => 'draft']);   // a sent-back plan returns to draft when edited
                $plan->items()->delete();
            } else {
                $plan = PurchasePlan::create($attrs + ['status' => 'draft', 'created_by' => $user->id]);
            }
            foreach ($lines as $l) {
                $plan->items()->create($l);
            }
            $this->audit->log($plan->wasRecentlyCreated ? 'created' : 'updated', 'purchase_plan', $plan->id, $plan->title, [], ['lines' => count($lines)]);

            return $submit ? $this->submit($plan->fresh('items'), $user) : $plan->fresh('items');
        });
    }

    public function submit(PurchasePlan $plan, User $user): PurchasePlan
    {
        if (! in_array($plan->status, ['draft', 'rejected'], true)) {
            throw new RuntimeException('Only a draft plan can be sent for approval.');
        }
        if ($plan->items()->count() === 0) {
            throw new RuntimeException('Add at least one item before sending for approval.');
        }
        $plan->update(['status' => 'submitted', 'submitted_at' => now(), 'rejected_by' => null, 'rejected_at' => null, 'rejection_reason' => null]);
        $this->notifications->sendToAdmins('purchase_plan_submitted', "Purchase plan {$plan->reference()} waiting approval",
            "{$user->name} sent “{$plan->title}” (".$plan->items()->count().' items) for approval.', route('inventory.plans.show', $plan));
        $this->audit->log('submitted', 'purchase_plan', $plan->id, $plan->title);

        return $plan->fresh('items');
    }

    /** $approved: [plan_item_id => quantity]; 0 drops a line from what may be bought. */
    public function approve(PurchasePlan $plan, User $admin, array $approved = [], ?string $note = null): PurchasePlan
    {
        if (! in_array($plan->status, ['draft', 'submitted'], true)) {
            throw new RuntimeException('Only a draft or submitted plan can be approved.');
        }

        return DB::transaction(function () use ($plan, $admin, $approved, $note) {
            $changed = [];
            foreach ($plan->items as $it) {
                if (array_key_exists($it->id, $approved) && $approved[$it->id] !== null && $approved[$it->id] !== '') {
                    $q = round(max(0, (float) $approved[$it->id]), 3);
                    $it->update(['approved_quantity' => $q]);
                    if (abs($q - $it->suggested_quantity) > 0.0005) {
                        $changed[] = $it->inventoryItem?->name.' '.$this->fmt($it->suggested_quantity).'→'.$this->fmt($q);
                    }
                }
            }
            if ($plan->items->every(fn ($i) => $i->fresh()->allowedQuantity() <= 0)) {
                throw ValidationException::withMessages(['approved' => 'Approve at least one item, or send the plan back instead.']);
            }
            $plan->update(['status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now(), 'approval_note' => $note]);
            $this->tell($plan, 'purchase_plan_approved', "Purchase plan {$plan->reference()} approved",
                ($changed ? 'Quantities changed: '.implode(', ', $changed).'. ' : '').'You can now buy it.');
            $this->audit->log('approved', 'purchase_plan', $plan->id, $plan->title, [], ['changed' => $changed, 'note' => $note]);

            return $plan->fresh('items');
        });
    }

    public function reject(PurchasePlan $plan, User $admin, string $reason): PurchasePlan
    {
        if ($plan->status !== 'submitted') {
            throw new RuntimeException('Only a submitted plan can be sent back.');
        }
        $plan->update(['status' => 'rejected', 'rejected_by' => $admin->id, 'rejected_at' => now(), 'rejection_reason' => $reason]);
        $this->tell($plan, 'purchase_plan_rejected', "Purchase plan {$plan->reference()} sent back", $reason);
        $this->audit->log('rejected', 'purchase_plan', $plan->id, $plan->title, [], ['reason' => $reason]);

        return $plan->fresh();
    }

    public function cancel(PurchasePlan $plan, User $user): PurchasePlan
    {
        if (! in_array($plan->status, ['draft', 'submitted', 'rejected', 'approved'], true)) {
            throw new RuntimeException('A plan with purchases cannot be cancelled — close it instead.');
        }
        $plan->update(['status' => 'cancelled']);
        $this->audit->log('cancelled', 'purchase_plan', $plan->id, $plan->title);

        return $plan->fresh();
    }

    /**
     * Turn (part of) an approved plan into a purchase from one supplier. The plan was already approved, so the
     * purchase is approved straight away (recorded against the plan's approver) and goes to "awaiting goods".
     * $data: purchase_date, vendor_id?, supplier_name?, notes?, lines[plan_item_id]: quantity, rate.
     */
    public function createPurchase(PurchasePlan $plan, User $user, array $data): Purchase
    {
        if (! in_array($plan->status, ['approved', 'ordered'], true)) {
            throw new RuntimeException('Only an approved plan can be bought.');
        }
        $progress = collect($this->progress($plan)['lines'])->keyBy('id');
        $lines = [];
        $errors = [];
        foreach ($data['lines'] ?? [] as $pid => $row) {
            $q = (float) ($row['quantity'] ?? 0);
            if ($q <= 0) {
                continue;
            }
            $p = $progress[$pid] ?? null;
            if (! $p) {
                $errors["lines.$pid.quantity"] = 'This item is not on the plan.';

                continue;
            }
            if ($q > $p['to_order'] + 0.0005) {
                $errors["lines.$pid.quantity"] = "{$p['name']}: only {$this->fmt($p['to_order'])} {$p['unit']} left to buy on this plan.";
            }
            $lines[] = ['inventory_item_id' => $p['item_id'], 'quantity' => $q, 'unit' => $p['unit'],
                'rate' => round(max(0, (float) ($row['rate'] ?? $p['estimate'] ?? 0)), 2), 'tax_percent' => 0, 'description' => null];
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        if (! $lines) {
            throw ValidationException::withMessages(['lines' => 'Enter a quantity for at least one item.']);
        }

        return DB::transaction(function () use ($plan, $user, $data, $lines) {
            $purchase = $this->purchases->save([
                'purchase_date' => $data['purchase_date'], 'vendor_id' => $data['vendor_id'] ?? null,
                'supplier_name' => $data['supplier_name'] ?? null, 'notes' => trim('From purchase plan '.$plan->reference().'. '.($data['notes'] ?? '')),
                'lines' => $lines, 'client_token' => $data['client_token'] ?? null,
            ], $user, null, true);
            $purchase->update(['purchase_plan_id' => $plan->id]);
            if ($purchase->status === 'submitted') {
                $approver = $plan->approver && $plan->approver->isAdmin() ? $plan->approver : User::where('role', 'admin')->where('is_active', true)->orderBy('id')->first();
                if ($approver) {
                    $this->purchases->approve($purchase, $approver);
                }
            }
            if ($plan->status === 'approved') {
                $plan->update(['status' => 'ordered']);
            }
            $this->audit->log('purchase_created', 'purchase_plan', $plan->id, $plan->title, [], ['purchase' => $purchase->reference()]);

            return $purchase->fresh();
        });
    }

    /**
     * Plan vs reality, per line (stock unit):
     * allowed (approved) · ordered (on live purchases) · received · rejected at the gate · closed short · to_order · state.
     */
    public function progress(PurchasePlan $plan): array
    {
        $plan->loadMissing('items.inventoryItem');
        $purchaseIds = Purchase::where('purchase_plan_id', $plan->id)->whereNotIn('status', ['cancelled', 'rejected'])->pluck('id');
        $pis = PurchaseItem::whereIn('purchase_id', $purchaseIds)->get(['id', 'purchase_id', 'inventory_item_id', 'base_quantity', 'received_base_quantity', 'closed_base_quantity', 'rate', 'quantity']);
        $rejected = GoodsReceiptItem::whereIn('purchase_item_id', $pis->pluck('id'))->whereHas('receipt', fn ($q) => $q->whereNull('reversed_at'))->selectRaw('purchase_item_id, SUM(rejected_base_quantity) as r')
            ->groupBy('purchase_item_id')->pluck('r', 'purchase_item_id');
        $byItem = $pis->toBase()->groupBy('inventory_item_id');
        $planned = $plan->items->pluck('inventory_item_id')->all();

        $lines = [];
        foreach ($plan->items as $it) {
            $g = $byItem->get($it->inventory_item_id, collect());
            $ordered = (float) $g->sum('base_quantity');
            $received = (float) $g->sum('received_base_quantity');
            $closed = (float) $g->sum('closed_base_quantity');
            $rej = (float) $g->sum(fn ($p) => (float) ($rejected[$p->id] ?? 0));
            $allowed = $it->allowedQuantity();
            $avgRate = $ordered > 0 ? $g->sum(fn ($p) => $p->rate * $p->quantity) / max(0.0001, $g->sum('quantity')) : null;
            $open = max(0, $ordered - $received - $closed);   // still expected on live purchases
            $state = match (true) {
                $allowed <= 0 => 'dropped',
                $ordered <= 0 => 'not_bought',
                $open > 0.0005 => 'awaiting',
                abs($received - $allowed) <= 0.0005 => 'as_planned',
                $received < $allowed => 'short',
                default => 'over',
            };
            $lines[] = [
                'id' => $it->id, 'item_id' => $it->inventory_item_id, 'name' => $it->inventoryItem?->name ?? '—',
                'name_ta' => $it->inventoryItem?->name_ta, 'unit' => $it->inventoryItem?->unit ?? '',
                'requested' => (float) $it->suggested_quantity, 'allowed' => $allowed, 'ordered' => $ordered,
                'received' => $received, 'rejected' => $rej, 'closed' => $closed, 'open' => $open,
                'to_order' => max(0, round($allowed - $ordered, 3)), 'estimate' => $it->estimated_unit_cost,
                'avg_rate' => $avgRate !== null ? round($avgRate, 2) : null, 'priority' => $it->priority, 'notes' => $it->notes,
                'state' => $state,
                'price_diff' => $avgRate !== null && $it->estimated_unit_cost ? round(($avgRate - $it->estimated_unit_cost) / $it->estimated_unit_cost * 100) : null,
            ];
        }
        // goods bought against this plan that were never on it (e.g. added at the receiving gate)
        $extras = $byItem->except($planned)->map(fn ($g, $itemId) => ['item_id' => $itemId,
            'name' => InventoryItem::find($itemId)?->name ?? '—', 'received' => (float) $g->sum('received_base_quantity'),
            'unit' => InventoryItem::find($itemId)?->unit ?? ''])->values()->all();

        $c = collect($lines);
        $active = $c->where('state', '!=', 'dropped');

        return [
            'lines' => $lines,
            'extras' => $extras,
            'awaiting' => $active->where('state', 'awaiting')->count(),
            'not_bought' => $active->where('state', 'not_bought')->count(),
            'differences' => $active->whereIn('state', ['short', 'over', 'not_bought'])->count() + count($extras),
            'all_as_planned' => $active->isNotEmpty() && $active->every(fn ($l) => $l['state'] === 'as_planned') && ! $extras,
            'open_purchases' => Purchase::where('purchase_plan_id', $plan->id)->whereIn('status', ['draft', 'submitted', 'approved', 'partially_received'])->count(),
            'spent' => (float) Purchase::where('purchase_plan_id', $plan->id)->whereNotIn('status', ['cancelled', 'rejected'])->sum('grand_total'),
        ];
    }

    /**
     * Close a plan once nothing is still on the way. If what arrived matches the plan it closes as is;
     * otherwise a note is required, and the shortfall can be carried into a new draft plan.
     */
    public function close(PurchasePlan $plan, User $user, ?string $note = null, bool $replanShortfall = false): PurchasePlan
    {
        if (! in_array($plan->status, ['ordered', 'approved'], true)) {
            throw new RuntimeException('Only a plan that is being bought can be closed.');
        }
        $p = $this->progress($plan);
        if ($p['open_purchases'] > 0) {
            throw new RuntimeException('Some purchases from this plan are still open — receive the goods (or close / cancel those purchases) first.');
        }
        if (! $p['all_as_planned'] && trim((string) $note) === '') {
            throw ValidationException::withMessages(['closing_note' => 'What arrived differs from the plan — add a short note explaining it.']);
        }

        return DB::transaction(function () use ($plan, $user, $note, $replanShortfall, $p) {
            $short = collect($p['lines'])->filter(fn ($l) => $l['allowed'] > 0 && $l['received'] + 0.0005 < $l['allowed']);
            $newPlan = null;
            if ($replanShortfall && $short->isNotEmpty()) {
                $newPlan = PurchasePlan::create(['title' => 'Shortfall – '.$plan->title, 'planned_date' => now()->toDateString(),
                    'status' => 'draft', 'created_by' => $user->id, 'notes' => 'Not received on '.$plan->reference().'.']);
                foreach ($short as $l) {
                    $newPlan->items()->create(['inventory_item_id' => $l['item_id'], 'suggested_quantity' => round($l['allowed'] - $l['received'], 3),
                        'estimated_unit_cost' => $l['avg_rate'] ?? $l['estimate'], 'priority' => $l['priority'] ?: 'normal', 'notes' => 'Shortfall from '.$plan->reference()]);
                }
            }
            $plan->update(['status' => 'completed', 'completed_at' => now(), 'closed_by' => $user->id,
                'closing_note' => trim((string) $note) ?: ($p['all_as_planned'] ? 'Received as planned.' : null), 'shortfall_plan_id' => $newPlan?->id]);
            $this->audit->log('completed', 'purchase_plan', $plan->id, $plan->title, [], [
                'as_planned' => $p['all_as_planned'], 'differences' => $p['differences'], 'shortfall_plan' => $newPlan?->id]);

            return $plan->fresh();
        });
    }

    private function cleanLines(array $rows): array
    {
        $out = [];
        $errors = [];
        foreach (array_values($rows) as $i => $r) {
            if (empty($r['inventory_item_id']) && empty($r['quantity'])) {
                continue;
            }
            $id = (int) ($r['inventory_item_id'] ?? 0);
            $q = (float) ($r['quantity'] ?? 0);
            if (! $id || ! InventoryItem::whereKey($id)->exists()) {
                $errors["lines.$i.inventory_item_id"] = 'Pick an item from the list.';
            } elseif ($q <= 0) {
                $errors["lines.$i.quantity"] = 'Enter a quantity.';
            } elseif (isset($out[$id])) {
                $errors["lines.$i.inventory_item_id"] = 'This item is already on the plan.';
            }
            $out[$id] = ['inventory_item_id' => $id, 'suggested_quantity' => round($q, 3),
                'estimated_unit_cost' => isset($r['estimated_unit_cost']) && $r['estimated_unit_cost'] !== '' ? round((float) $r['estimated_unit_cost'], 2) : null,
                'priority' => in_array($r['priority'] ?? 'normal', ['urgent', 'high', 'normal', 'low'], true) ? ($r['priority'] ?? 'normal') : 'normal',
                'notes' => isset($r['notes']) && trim((string) $r['notes']) !== '' ? mb_substr(trim($r['notes']), 0, 255) : null];
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        if (! $out) {
            throw ValidationException::withMessages(['lines' => 'Add at least one item.']);
        }

        return array_values($out);
    }

    private function tell(PurchasePlan $plan, string $type, string $title, string $body): void
    {
        if ($plan->creator) {
            $this->notifications->send($plan->creator, $type, $title, $body, route('inventory.plans.show', $plan));
        }
    }

    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.') ?: '0';
    }
}
