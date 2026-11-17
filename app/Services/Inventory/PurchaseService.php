<?php

namespace App\Services\Inventory;

use App\Models\InventoryBillUpload;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Bulk purchase lifecycle: draft -> submitted -> approved -> (partially_)received -> completed.
 * Never moves stock (GoodsReceiptService does).
 */
class PurchaseService
{
    public function __construct(
        private UnitConversionService $units,
        private ItemMatchingService $matcher,
        private AuditLogService $audit,
        private NotificationService $notifications,
    ) {}

    /**
     * Create or update a purchase from the bulk grid.
     * $data: purchase_date, vendor_id, supplier_name, invoice_number, supplier_reference,
     * payment_mode, amount_paid, discount_amount, notes, bill_id, client_token,
     * lines[]: inventory_item_id, description, quantity, unit, rate, tax_percent, remember_alias.
     */
    public function save(array $data, User $user, ?Purchase $purchase = null, bool $submit = false): Purchase
    {
        if (! $purchase && ! empty($data['client_token'])) {
            $existing = Purchase::where('client_token', $data['client_token'])->first();
            if ($existing) {
                return $existing; // same form submitted twice
            }
        }
        if ($purchase && ! $purchase->isEditable()) {
            throw new RuntimeException('This purchase can no longer be edited.');
        }

        $lines = $this->prepareLines($data['lines'] ?? []);
        $this->guardDuplicateInvoice($data, $purchase);

        try {
            return $this->persist($data, $user, $purchase, $submit, $lines);
        } catch (UniqueConstraintViolationException $e) {
            if (! $purchase && ! empty($data['client_token']) && ($existing = Purchase::where('client_token', $data['client_token'])->first())) {
                return $existing; // same form submitted twice at the same moment
            }
            throw $e;
        }
    }

    private function persist(array $data, User $user, ?Purchase $purchase, bool $submit, array $lines): Purchase
    {
        return DB::transaction(function () use ($data, $user, $purchase, $submit, $lines) {
            $isNew = ! $purchase;
            $old = $purchase?->only(['status', 'grand_total', 'invoice_number', 'vendor_id']) ?? [];

            $subtotal = round(array_sum(array_column($lines, 'amount')), 2);
            $tax = round(array_sum(array_map(fn ($l) => $l['amount'] * $l['tax_percent'] / 100, $lines)), 2);
            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $grand = round(max(0, $subtotal + $tax - $discount), 2);
            $paid = round((float) ($data['amount_paid'] ?? 0), 2);

            $attrs = [
                'purchase_date' => $data['purchase_date'],
                'vendor_id' => $data['vendor_id'] ?? null,
                'supplier_name' => $data['supplier_name'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'supplier_reference' => $data['supplier_reference'] ?? null,
                'payment_mode' => $data['payment_mode'] ?? null,
                'amount_paid' => $paid,
                'payment_status' => $paid <= 0 ? 'unpaid' : ($paid + 0.005 >= $grand ? 'paid' : 'partial'),
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => $discount,
                'grand_total' => $grand,
                'notes' => $data['notes'] ?? null,
            ];

            if ($isNew) {
                $purchase = Purchase::create($attrs + [
                    'status' => 'draft',
                    'created_by' => $user->id,
                    'client_token' => $data['client_token'] ?? null,
                ]);
            } else {
                $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);
                if (! $purchase->isEditable()) {
                    throw new RuntimeException('This purchase can no longer be edited.');
                }
                $purchase->update($attrs);
                $purchase->items()->delete();
            }

            foreach ($lines as $i => $line) {
                $purchase->items()->create([
                    'inventory_item_id' => $line['item']->id,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'base_quantity' => $line['base_quantity'],
                    'rate' => $line['rate'],
                    'tax_percent' => $line['tax_percent'],
                    'amount' => $line['amount'],
                    'sort_order' => $i,
                ]);
                if ($line['remember_alias'] && $line['description']) {
                    $this->matcher->remember($line['item'], $line['description'], $purchase->vendor_id, $user, $line['unit']);
                }
            }

            if (! empty($data['bill_id'])) {
                $this->linkBill($purchase, (int) $data['bill_id']);
            }

            if ($submit && $purchase->status === 'draft') {
                $purchase->update(['status' => 'submitted', 'submitted_at' => now()]);
                $this->notifications->sendToAdmins(
                    'purchase_submitted',
                    "Purchase {$purchase->reference()} waiting approval",
                    "{$user->name} submitted ₹".number_format($grand, 2)." from {$purchase->supplierLabel()} (".count($lines).' items).',
                    route('inventory.purchases.show', $purchase)
                );
            }

            $this->audit->log($isNew ? 'created' : 'updated', 'purchase', $purchase->id, $purchase->reference(), $old,
                $purchase->only(['status', 'grand_total', 'invoice_number', 'vendor_id']) + ['lines' => count($lines)]);

            return $purchase->fresh('items');
        });
    }

    public function submit(Purchase $purchase, User $user): Purchase
    {
        return $this->transition($purchase, ['draft'], 'submitted', $user, ['submitted_at' => now()], function ($p) use ($user) {
            if ($p->items()->count() === 0) {
                throw new RuntimeException('Add at least one item before submitting.');
            }
            $this->notifications->sendToAdmins('purchase_submitted', "Purchase {$p->reference()} waiting approval",
                "{$user->name} submitted ₹".number_format((float) $p->grand_total, 2)." from {$p->supplierLabel()}.",
                route('inventory.purchases.show', $p));
        });
    }

    public function approve(Purchase $purchase, User $admin): Purchase
    {
        return $this->transition($purchase, ['submitted'], 'approved', $admin, ['approved_by' => $admin->id, 'approved_at' => now()], function ($p) {
            $this->notifyCreator($p, 'purchase_approved', "Purchase {$p->reference()} approved", 'Goods can now be received into the store.');
        });
    }

    public function reject(Purchase $purchase, User $admin, string $reason): Purchase
    {
        return $this->transition($purchase, ['submitted'], 'rejected', $admin,
            ['rejected_by' => $admin->id, 'rejected_at' => now(), 'rejection_reason' => $reason], function ($p) use ($reason) {
                $this->releaseBill($p);
                $this->notifyCreator($p, 'purchase_rejected', "Purchase {$p->reference()} rejected", $reason);
            });
    }

    public function cancel(Purchase $purchase, User $user): Purchase
    {
        return $this->transition($purchase, ['draft', 'submitted', 'approved'], 'cancelled', $user, [], function ($p) {
            if ($p->receipts()->exists()) {
                throw new RuntimeException('Goods were already received; close the purchase instead.');
            }
            $this->releaseBill($p);
        });
    }

    /** Close a purchase; any not-received quantity is short-closed (no stock effect). */
    public function complete(Purchase $purchase, User $admin): Purchase
    {
        return $this->transition($purchase, ['received', 'partially_received'], 'completed', $admin, ['completed_at' => now()]);
    }

    /** @return array<int, array> lines ready to persist */
    private function prepareLines(array $rows): array
    {
        $errors = [];
        $lines = [];
        $seen = [];
        $max = (int) config('inventory.max_lines', 100);

        $rows = array_values(array_filter($rows, fn ($r) => ! empty($r['inventory_item_id']) || ! empty($r['quantity'])));
        if (count($rows) === 0) {
            throw ValidationException::withMessages(['lines' => 'Add at least one item.']);
        }
        if (count($rows) > $max) {
            throw ValidationException::withMessages(['lines' => "Maximum {$max} rows per purchase."]);
        }

        $items = InventoryItem::with('unitConversions')->whereIn('id', array_column($rows, 'inventory_item_id'))->get()->keyBy('id');

        foreach ($rows as $i => $row) {
            $n = $i + 1;
            $item = $items[$row['inventory_item_id'] ?? 0] ?? null;
            if (! $item) {
                $errors["lines.{$i}.inventory_item_id"] = "Row {$n}: choose an item.";

                continue;
            }
            $qty = round((float) ($row['quantity'] ?? 0), 3);
            $rate = round((float) ($row['rate'] ?? 0), 2);
            if ($qty <= 0) {
                $errors["lines.{$i}.quantity"] = "Row {$n} ({$item->name}): quantity must be more than 0.";

                continue;
            }
            $unit = $this->units->normalize($row['unit'] ?? null) ?? $item->unit;
            try {
                $base = $this->units->toBase($item, $qty, $unit);
            } catch (InvalidArgumentException $e) {
                $errors["lines.{$i}.unit"] = "Row {$n}: ".$e->getMessage();

                continue;
            }
            $key = $item->id.'|'.$unit.'|'.$qty.'|'.$rate;
            if (isset($seen[$key])) {
                $errors["lines.{$i}.inventory_item_id"] = "Row {$n} repeats row {$seen[$key]} ({$item->name}). Remove one or merge the quantities.";

                continue;
            }
            $seen[$key] = $n;
            $lines[] = [
                'item' => $item,
                'description' => isset($row['description']) ? mb_substr(trim((string) $row['description']), 0, 200) ?: null : null,
                'quantity' => $qty,
                'unit' => $unit,
                'base_quantity' => $base,
                'rate' => $rate,
                'tax_percent' => round(min(100, max(0, (float) ($row['tax_percent'] ?? 0))), 2),
                'amount' => round($qty * $rate, 2),
                'remember_alias' => ! empty($row['remember_alias']),
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $lines;
    }

    private function guardDuplicateInvoice(array $data, ?Purchase $current): void
    {
        $invoice = trim((string) ($data['invoice_number'] ?? ''));
        if ($invoice === '') {
            return;
        }
        $dup = Purchase::whereRaw('LOWER(invoice_number) = ?', [mb_strtolower($invoice)])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
            ->when(! empty($data['vendor_id']),
                fn ($q) => $q->where('vendor_id', $data['vendor_id']),
                fn ($q) => $q->whereRaw('LOWER(COALESCE(supplier_name, \'\')) = ?', [mb_strtolower(trim((string) ($data['supplier_name'] ?? '')))]))
            ->first();
        if ($dup) {
            throw ValidationException::withMessages([
                'invoice_number' => "Invoice {$invoice} from this supplier is already entered as {$dup->reference()}.",
            ]);
        }
    }

    private function linkBill(Purchase $purchase, int $billId): void
    {
        $bill = InventoryBillUpload::lockForUpdate()->findOrFail($billId);
        if ($bill->status === 'imported' || ($bill->purchase_id && $bill->purchase_id !== $purchase->id)) {
            throw new RuntimeException('This bill is already linked to another purchase.');
        }
        $bill->update(['purchase_id' => $purchase->id, 'status' => 'converted']);
        $purchase->update(['inventory_bill_upload_id' => $bill->id]);
    }

    private function releaseBill(Purchase $purchase): void
    {
        if ($purchase->inventory_bill_upload_id) {
            InventoryBillUpload::where('id', $purchase->inventory_bill_upload_id)
                ->where('purchase_id', $purchase->id)
                ->update(['purchase_id' => null, 'status' => 'review_pending']);
        }
    }

    private function transition(Purchase $purchase, array $from, string $to, User $user, array $extra = [], ?callable $after = null): Purchase
    {
        return DB::transaction(function () use ($purchase, $from, $to, $extra, $after) {
            $p = Purchase::lockForUpdate()->findOrFail($purchase->id);
            if (! in_array($p->status, $from, true)) {
                throw new RuntimeException("Purchase is {$p->statusLabel()}; cannot change it to ".(Purchase::STATUSES[$to] ?? $to).'.');
            }
            $old = $p->status;
            if ($after) {
                $after($p);
            }
            $p->update(['status' => $to] + $extra);
            $this->audit->log($to, 'purchase', $p->id, $p->reference(), ['status' => $old], ['status' => $to] + array_filter($extra, 'is_string'));

            return $p->fresh();
        });
    }

    private function notifyCreator(Purchase $p, string $type, string $title, string $message): void
    {
        if ($p->creator) {
            $this->notifications->send($p->creator, $type, $title, $message, route('inventory.purchases.show', $p));
        }
    }
}
