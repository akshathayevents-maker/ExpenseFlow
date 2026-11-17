<?php

namespace App\Services\Inventory;

use App\Models\HallBooking;
use App\Models\InventoryItem;
use App\Models\MaterialRequest;
use App\Models\MealEntry;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Material request lifecycle (central store):
 *   draft -> submitted -> approved (stock deducted NOW) -> issued (handover, no 2nd deduction)
 *         -> reconciled (consumed / wasted / unexplained recorded)
 *   submitted -> rejected ; draft|submitted|approved -> cancelled (approved: deduction reversed)
 * Returns after handover add stock back (material_return).
 * All stock effects go through InventoryService::post / reverse (row locks).
 */
class MaterialRequestService
{
    public function __construct(
        private InventoryService $inventory,
        private UnitConversionService $units,
        private AuditLogService $audit,
        private NotificationService $notifications,
    ) {}

    /**
     * $data: department, source, meal_entry_id, meal_type, hall_booking_id, required_date, required_time,
     * preparation_name, guest_count, purpose, notes, client_token,
     * lines[]: inventory_item_id, quantity, unit
     */
    public function save(array $data, User $user, ?MaterialRequest $request = null, bool $submit = false): MaterialRequest
    {
        if (! $request && ! empty($data['client_token'])) {
            if ($existing = MaterialRequest::where('client_token', $data['client_token'])->first()) {
                return $existing;
            }
        }
        if ($request && $request->status !== 'draft') {
            throw new RuntimeException('Only drafts can be edited.');
        }
        $lines = $this->prepareLines($data['lines'] ?? []);
        $link = $this->resolveLink($data);
        // Standard recipe suggestion (recomputed server-side, stored next to what staff entered).
        $suggested = [];
        $recipeMeta = ['recipe_id' => null, 'recipe_version' => null];
        if (! empty($data['recipe_id']) && ! empty($data['guest_count'])) {
            $s = app(RecipeStandardService::class)->suggest((int) $data['recipe_id'], (int) $data['guest_count']);
            if ($s) {
                $suggested = array_column($s['lines'], 'suggested', 'inventory_item_id');
                $recipeMeta = ['recipe_id' => $s['recipe_id'], 'recipe_version' => $s['version']];
            }
        }

        try {
            return $this->persist($data, $user, $request, $submit, $lines, $link, $suggested, $recipeMeta);
        } catch (UniqueConstraintViolationException $e) {
            if (! $request && ! empty($data['client_token']) && ($existing = MaterialRequest::where('client_token', $data['client_token'])->first())) {
                return $existing; // same form submitted twice at the same moment
            }
            throw $e;
        }
    }

    private function persist(array $data, User $user, ?MaterialRequest $request, bool $submit, array $lines, array $link, array $suggested, array $recipeMeta): MaterialRequest
    {
        return DB::transaction(function () use ($data, $user, $request, $submit, $lines, $link, $suggested, $recipeMeta) {
            $attrs = $link + $recipeMeta + [
                'department' => $data['department'] ?? 'kitchen',
                'required_date' => $data['required_date'],
                'required_time' => $data['required_time'] ?? null,
                'preparation_name' => $data['preparation_name'] ?? null,
                'guest_count' => $data['guest_count'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];
            $isNew = ! $request;
            if ($isNew) {
                $request = MaterialRequest::create($attrs + [
                    'status' => 'draft',
                    'requested_by' => $user->id,
                    'client_token' => $data['client_token'] ?? null,
                    'revision_of_id' => $data['revision_of_id'] ?? null,
                    'revision_no' => $data['revision_no'] ?? 1,
                ]);
            } else {
                $request = MaterialRequest::lockForUpdate()->findOrFail($request->id);
                if ($request->status !== 'draft') {
                    throw new RuntimeException('Only drafts can be edited.');
                }
                $request->update($attrs);
                $request->items()->delete();
            }
            foreach ($lines as $i => $l) {
                $request->items()->create([
                    'inventory_item_id' => $l['item']->id,
                    'requested_quantity' => $l['quantity'],
                    'requested_unit' => $l['unit'],
                    'requested_base_quantity' => $l['base'],
                    'suggested_base_quantity' => $suggested[$l['item']->id] ?? null,
                    'sort_order' => $i,
                ]);
            }
            $this->audit->log($isNew ? 'created' : 'updated', 'material_request', $request->id, $request->reference(), [], ['lines' => count($lines)]);

            if ($submit) {
                $this->markSubmitted($request, $user);
            }

            return $request->fresh('items');
        });
    }

    public function submit(MaterialRequest $request, User $user): MaterialRequest
    {
        return DB::transaction(function () use ($request, $user) {
            $r = $this->lock($request, ['draft']);
            if ($r->items()->count() === 0) {
                throw new RuntimeException('Add at least one material.');
            }
            $this->markSubmitted($r, $user);

            return $r->fresh();
        });
    }

    /**
     * Approve all / partially. $decisions keyed by material_request_item id:
     * ['quantity' => approved qty in the REQUESTED unit (0 = reject line), 'remark' => ?string].
     * Lines not present are approved in full. Stock is deducted immediately, under
     * row locks taken in item-id order; any shortage aborts the whole approval.
     */
    public function approve(MaterialRequest $request, User $admin, array $decisions = [], ?string $remarks = null): MaterialRequest
    {
        return DB::transaction(function () use ($request, $admin, $decisions, $remarks) {
            $r = $this->lock($request, ['submitted']);
            $items = $r->items()->with('item')->get()->sortBy('inventory_item_id');
            $label = $this->label($r);
            $approvedAny = false;
            $shortages = [];

            foreach ($items as $line) {
                $d = $decisions[$line->id] ?? null;
                $qty = $d !== null && array_key_exists('quantity', $d) && $d['quantity'] !== null && $d['quantity'] !== ''
                    ? max(0, (float) $d['quantity'])
                    : $line->requested_quantity;
                $base = $this->units->toBase($line->item, $qty, $line->requested_unit);
                $remark = $d['remark'] ?? null;

                if ($base <= 0) {
                    $line->update(['approved_base_quantity' => 0, 'line_status' => 'rejected', 'admin_remark' => $remark]);

                    continue;
                }
                try {
                    $txn = $this->inventory->post($line->item, -$base, 'material_issue', $admin, [
                        'reference_type' => 'material_request',
                        'reference_id' => $r->id,
                        'reference_label' => $label,
                        'entered_quantity' => $qty,
                        'entered_unit' => $line->requested_unit,
                        'notes' => 'Approved for '.($r->preparation_name ?: $r->forLabel()),
                    ]);
                } catch (RuntimeException $e) {
                    $shortages[] = $e->getMessage();

                    continue;
                }
                $approvedAny = true;
                $line->update([
                    'approved_base_quantity' => $base,
                    'line_status' => abs($base - $line->requested_base_quantity) < 0.0005 ? 'approved' : 'reduced',
                    'admin_remark' => $remark,
                    'issue_transaction_id' => $txn->id,
                    'unit_cost' => $line->item->fresh()->average_cost,
                ]);
            }

            if ($shortages) {
                // roll back every deduction of this approval
                throw ValidationException::withMessages(['approval' => $shortages]);
            }

            $status = $approvedAny ? 'approved' : 'rejected';
            $r->update(['status' => $status, 'approved_by' => $admin->id, 'approved_at' => now(), 'approval_remarks' => $remarks]);
            $this->audit->log($status, 'material_request', $r->id, $r->reference(), ['status' => 'submitted'], [
                'status' => $status,
                'lines' => $items->mapWithKeys(fn ($l) => [$l->item->name => $l->fresh()->approved_base_quantity])->all(),
            ]);

            $this->notifications->send($r->requester, 'material_'.$status, "{$r->reference()} ".($status === 'approved' ? 'approved' : 'rejected'),
                $status === 'approved' ? 'Collect the materials from the store.' : ($remarks ?: 'All items were rejected.'),
                route('inventory.material-requests.show', $r));
            if ($status === 'approved') {
                $this->notifyStorekeepers($r, "Ready for handover: {$r->reference()}", "{$r->requester->name} · {$r->forLabel()}");
            }

            return $r->fresh('items');
        });
    }

    public function reject(MaterialRequest $request, User $admin, string $reason): MaterialRequest
    {
        return DB::transaction(function () use ($request, $admin, $reason) {
            $r = $this->lock($request, ['submitted']);
            $r->items()->update(['line_status' => 'rejected', 'approved_base_quantity' => 0]);
            $r->update(['status' => 'rejected', 'approved_by' => $admin->id, 'approved_at' => now(), 'approval_remarks' => $reason]);
            $this->audit->log('rejected', 'material_request', $r->id, $r->reference(), ['status' => 'submitted'], ['status' => 'rejected', 'reason' => $reason]);
            $this->notifications->send($r->requester, 'material_rejected', "{$r->reference()} rejected", $reason, route('inventory.material-requests.show', $r));

            return $r->fresh();
        });
    }

    /**
     * Physical handover by the storekeeper. $issued keyed by item line id, in the stock unit;
     * missing = full approved quantity. Never deducts again: a short handover reverses the gap.
     */
    public function handover(MaterialRequest $request, User $storekeeper, ?int $collectedById, ?string $collectedByName, array $issued = [], ?string $notes = null): MaterialRequest
    {
        if (! $collectedById && trim((string) $collectedByName) === '') {
            throw ValidationException::withMessages(['collected_by' => 'Record who collected the materials.']);
        }

        return DB::transaction(function () use ($request, $storekeeper, $collectedById, $collectedByName, $issued, $notes) {
            $r = $this->lock($request, ['approved']);
            $items = $r->items()->with(['item', 'issueTransaction'])->where('line_status', '!=', 'rejected')->get()->sortBy('inventory_item_id');
            $errors = [];
            foreach ($items as $line) {
                $qty = array_key_exists($line->id, $issued) && $issued[$line->id] !== null && $issued[$line->id] !== ''
                    ? round((float) $issued[$line->id], 3) : $line->approved_base_quantity;
                if ($qty < 0 || $qty > $line->approved_base_quantity + 0.0005) {
                    $errors["issued.{$line->id}"] = "{$line->item->name}: handover must be between 0 and the approved {$line->approved_base_quantity} {$line->item->unit}.";

                    continue;
                }
                $gap = round($line->approved_base_quantity - $qty, 3);
                if ($gap > 0 && $line->issueTransaction) {
                    $this->inventory->reverse($line->issueTransaction, $storekeeper, $gap, "Short handover {$r->reference()}: {$gap} {$line->item->unit} not handed over");
                }
                $line->update(['issued_base_quantity' => $qty]);
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            $r->update([
                'status' => 'issued',
                'handed_over_by' => $storekeeper->id,
                'collected_by' => $collectedById,
                'collected_by_name' => $collectedById ? null : trim((string) $collectedByName),
                'handed_over_at' => now(),
                'notes' => $notes ? trim(($r->notes ? $r->notes."\n" : '').'Handover: '.$notes) : $r->notes,
            ]);
            $this->audit->log('handed_over', 'material_request', $r->id, $r->reference(), ['status' => 'approved'], [
                'status' => 'issued', 'collected_by' => $collectedById ?? $collectedByName,
            ]);

            return $r->fresh('items');
        });
    }

    /** Cancel. Before approval: nothing to undo. Approved but not handed over: deductions reversed. */
    public function cancel(MaterialRequest $request, User $user, string $reason): MaterialRequest
    {
        return DB::transaction(function () use ($request, $user, $reason) {
            $r = $this->lock($request, ['draft', 'submitted', 'approved']);
            $wasApproved = $r->status === 'approved';
            if ($wasApproved) {
                foreach ($r->items()->with('issueTransaction', 'item')->get()->sortBy('inventory_item_id') as $line) {
                    if ($line->issueTransaction && $line->approved_base_quantity > 0) {
                        $this->inventory->reverse($line->issueTransaction, $user, $line->approved_base_quantity, "Cancelled {$r->reference()} before handover: {$reason}");
                    }
                }
            }
            $old = $r->status;
            $r->update(['status' => 'cancelled', 'cancelled_by' => $user->id, 'cancelled_at' => now(), 'cancel_reason' => $reason]);
            $this->audit->log('cancelled', 'material_request', $r->id, $r->reference(), ['status' => $old], ['status' => 'cancelled', 'reason' => $reason, 'stock_restored' => $wasApproved]);
            if ($wasApproved) {
                $this->notifyStorekeepers($r, "Cancelled: {$r->reference()}", 'Do not hand over — stock has been restored.');
            }

            return $r->fresh();
        });
    }

    /** Materials brought back after handover. $returns keyed by line id, stock unit. */
    public function recordReturn(MaterialRequest $request, User $storekeeper, array $returns, ?string $note = null): MaterialRequest
    {
        return DB::transaction(function () use ($request, $storekeeper, $returns, $note) {
            $r = $this->lock($request, ['issued']);
            $lines = $r->items()->with('item')->get()->keyBy('id');
            $errors = [];
            $done = 0;
            foreach ($returns as $lineId => $qty) {
                $qty = round((float) $qty, 3);
                $line = $lines[$lineId] ?? null;
                if (! $line || $qty <= 0) {
                    continue;
                }
                if ($qty > $line->netIssued() + 0.0005) {
                    $errors["returns.{$lineId}"] = "{$line->item->name}: cannot return more than the {$line->netIssued()} {$line->item->unit} still out.";

                    continue;
                }
                $this->inventory->post($line->item, $qty, 'material_return', $storekeeper, [
                    'reference_type' => 'material_request',
                    'reference_id' => $r->id,
                    'reference_label' => $this->label($r),
                    'unit_cost' => $line->unit_cost,
                    'notes' => $note ?: 'Returned to store',
                ]);
                $line->update(['returned_base_quantity' => round($line->returned_base_quantity + $qty, 3)]);
                $done++;
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            if (! $done) {
                throw ValidationException::withMessages(['returns' => 'Enter a returned quantity.']);
            }
            $this->audit->log('returned', 'material_request', $r->id, $r->reference(), [], ['lines' => $done]);

            return $r->fresh('items');
        });
    }

    /**
     * Record actual usage after handover. $lines keyed by line id:
     * consumed, wasted (stock unit), wastage_reason, variance_reason.
     * issued = returned + consumed + wasted + unexplained.
     */
    public function reconcile(MaterialRequest $request, User $user, array $lines): MaterialRequest
    {
        return DB::transaction(function () use ($request, $user, $lines) {
            $r = $this->lock($request, ['issued']);
            $items = $r->items()->with('item')->where('line_status', '!=', 'rejected')->get();
            $errors = [];
            $needsReview = false;
            $threshold = (float) config('inventory.wastage_review_percent', 10);

            foreach ($items as $line) {
                $in = $lines[$line->id] ?? [];
                $net = $line->netIssued();
                $consumed = round((float) ($in['consumed'] ?? $net), 3);
                $wasted = round((float) ($in['wasted'] ?? 0), 3);
                $unexplained = round($net - $consumed - $wasted, 3);
                $name = $line->item->name;
                if ($consumed < 0 || $wasted < 0) {
                    $errors["lines.{$line->id}"] = "{$name}: quantities cannot be negative.";

                    continue;
                }
                if ($unexplained < -0.0005) {
                    $errors["lines.{$line->id}"] = "{$name}: used + wasted ({$consumed} + {$wasted}) is more than issued minus returned ({$net} {$line->item->unit}).";

                    continue;
                }
                if ($wasted > 0 && trim((string) ($in['wastage_reason'] ?? '')) === '') {
                    $errors["lines.{$line->id}"] = "{$name}: say why material was wasted.";

                    continue;
                }
                if (abs($unexplained) > 0.0005 && trim((string) ($in['variance_reason'] ?? '')) === '') {
                    $errors["lines.{$line->id}"] = "{$name}: {$unexplained} {$line->item->unit} is not accounted for — add a remark.";

                    continue;
                }
                if (($line->issued_base_quantity ?? 0) > 0 && $wasted / $line->issued_base_quantity * 100 > $threshold) {
                    $needsReview = true;
                }
                if (abs($unexplained) > 0.0005) {
                    $needsReview = true;
                }
                $line->update([
                    'consumed_base_quantity' => $consumed,
                    'wasted_base_quantity' => $wasted,
                    'unexplained_base_quantity' => max(0, $unexplained),
                    'wastage_reason' => $in['wastage_reason'] ?? null,
                    'variance_reason' => $in['variance_reason'] ?? null,
                ]);
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            $r->update([
                'status' => 'reconciled',
                'reconciled_by' => $user->id,
                'reconciled_at' => now(),
                'reconciliation_status' => $needsReview ? 'pending_review' : 'accepted',
            ]);
            $this->audit->log('reconciled', 'material_request', $r->id, $r->reference(), [], ['review' => $needsReview]);
            if ($needsReview) {
                $this->notifications->sendToAdmins('material_review', "Usage needs review: {$r->reference()}",
                    'High wastage or unexplained difference recorded for '.$r->forLabel().'.', route('inventory.material-requests.show', $r));
            }

            return $r->fresh('items');
        });
    }

    /** Admin sign-off of exceptional wastage / unexplained variance. */
    public function reviewReconciliation(MaterialRequest $request, User $admin, string $remarks): MaterialRequest
    {
        return DB::transaction(function () use ($request, $admin, $remarks) {
            $r = MaterialRequest::lockForUpdate()->findOrFail($request->id);
            if ($r->reconciliation_status !== 'pending_review') {
                throw new RuntimeException('Nothing waiting for review.');
            }
            $r->update(['reconciliation_status' => 'accepted', 'reviewed_by' => $admin->id, 'reviewed_at' => now(), 'review_remarks' => $remarks]);
            $this->audit->log('usage_reviewed', 'material_request', $r->id, $r->reference(), [], ['remarks' => $remarks]);

            return $r->fresh();
        });
    }

    /** Start a revision (e.g. hall menu changed after approval). The original is never modified. */
    public function revise(MaterialRequest $original, User $user): MaterialRequest
    {
        if (! in_array($original->status, ['submitted', 'approved', 'issued', 'reconciled'], true)) {
            throw new RuntimeException('Only submitted or approved requests can be revised.');
        }
        $root = $original->revision_of_id ? MaterialRequest::find($original->revision_of_id) ?? $original : $original;
        $no = (int) MaterialRequest::where('revision_of_id', $root->id)->max('revision_no');

        return $this->save([
            'department' => $original->department,
            'source' => $original->source,
            'meal_entry_id' => $original->meal_entry_id,
            'meal_type' => $original->meal_type,
            'hall_booking_id' => $original->hall_booking_id,
            'required_date' => $original->required_date->toDateString(),
            'required_time' => $original->required_time,
            'preparation_name' => $original->preparation_name,
            'guest_count' => $original->guest_count,
            'purpose' => 'Revision of '.$original->reference(),
            'revision_of_id' => $root->id,
            'revision_no' => max($no, $root->revision_no) + 1,
            'lines' => $original->items->map(fn ($i) => [
                'inventory_item_id' => $i->inventory_item_id, 'quantity' => $i->requested_quantity, 'unit' => $i->requested_unit,
            ])->all(),
        ], $user);
    }

    // ── internals ───────────────────────────────────────────────────────────

    private function markSubmitted(MaterialRequest $r, User $user): void
    {
        $r->update(['status' => 'submitted', 'submitted_at' => now()]);
        $this->audit->log('submitted', 'material_request', $r->id, $r->reference(), ['status' => 'draft'], ['status' => 'submitted']);
        $this->notifications->sendToAdmins('material_submitted', "Material request {$r->reference()}",
            "{$user->name} · {$r->forLabel()} · needed {$r->required_date->format('d M')}", route('inventory.material-requests.show', $r));
    }

    private function lock(MaterialRequest $request, array $allowed): MaterialRequest
    {
        $r = MaterialRequest::lockForUpdate()->findOrFail($request->id);
        if (! in_array($r->status, $allowed, true)) {
            throw new RuntimeException("Request is {$r->statusLabel()}; this action is not possible.");
        }

        return $r;
    }

    private function label(MaterialRequest $r): string
    {
        return mb_substr($r->reference().' · '.($r->preparation_name ?: $r->forLabel()), 0, 120);
    }

    private function prepareLines(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn ($r) => ! empty($r['inventory_item_id']) || ! empty($r['quantity'])));
        $max = (int) config('inventory.max_lines', 100);
        if (! $rows) {
            throw ValidationException::withMessages(['lines' => 'Add at least one material.']);
        }
        if (count($rows) > $max) {
            throw ValidationException::withMessages(['lines' => "Maximum {$max} materials per request."]);
        }
        $items = InventoryItem::with('unitConversions')->whereIn('id', array_column($rows, 'inventory_item_id'))->get()->keyBy('id');
        $errors = [];
        $out = [];
        $seen = [];
        foreach ($rows as $i => $row) {
            $n = $i + 1;
            $item = $items[$row['inventory_item_id'] ?? 0] ?? null;
            if (! $item) {
                $errors["lines.{$i}.inventory_item_id"] = "Row {$n}: choose a material.";

                continue;
            }
            $qty = round((float) ($row['quantity'] ?? 0), 3);
            if ($qty <= 0) {
                $errors["lines.{$i}.quantity"] = "Row {$n} ({$item->name}): enter the quantity.";

                continue;
            }
            if (isset($seen[$item->id])) {
                $errors["lines.{$i}.inventory_item_id"] = "{$item->name} is entered twice (rows {$seen[$item->id]} and {$n}). Put the total in one row.";

                continue;
            }
            $seen[$item->id] = $n;
            $unit = $this->units->normalize($row['unit'] ?? null) ?? $item->unit;
            try {
                $base = $this->units->toBase($item, $qty, $unit);
            } catch (InvalidArgumentException $e) {
                $errors["lines.{$i}.unit"] = "Row {$n}: ".$e->getMessage();

                continue;
            }
            $out[] = ['item' => $item, 'quantity' => $qty, 'unit' => $unit, 'base' => $base];
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $out;
    }

    private function resolveLink(array $data): array
    {
        $source = $data['source'] ?? 'other';
        if ($source === 'meal') {
            $entry = MealEntry::find($data['meal_entry_id'] ?? 0);
            if (! $entry || empty($data['meal_type'])) {
                throw ValidationException::withMessages(['meal_entry_id' => 'Choose the meal this material is for.']);
            }

            return ['source' => 'meal', 'meal_entry_id' => $entry->id, 'meal_type' => $data['meal_type'], 'hall_booking_id' => null, 'menu_snapshot' => null];
        }
        if ($source === 'hall') {
            $booking = HallBooking::find($data['hall_booking_id'] ?? 0);
            if (! $booking) {
                throw ValidationException::withMessages(['hall_booking_id' => 'Choose the hall order.']);
            }
            if ($booking->status === 'cancelled') {
                throw ValidationException::withMessages(['hall_booking_id' => 'This hall order is cancelled.']);
            }

            return ['source' => 'hall', 'hall_booking_id' => $booking->id, 'meal_entry_id' => null,
                'meal_type' => $data['meal_type'] ?? null, 'menu_snapshot' => app(HallMenuService::class)->snapshot($booking)];
        }

        return ['source' => 'other', 'meal_entry_id' => null, 'meal_type' => null, 'hall_booking_id' => null, 'menu_snapshot' => null];
    }

    private function notifyStorekeepers(MaterialRequest $r, string $title, string $body): void
    {
        User::where('is_active', true)->where(fn ($q) => $q->where('is_storekeeper', true))->get()
            ->each(fn ($u) => $this->notifications->send($u, 'material_handover', $title, $body, route('inventory.material-requests.show', $r)));
    }
}
