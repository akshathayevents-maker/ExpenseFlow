<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Waiting approval',
        'approved' => 'Approved – awaiting goods',
        'partially_received' => 'Partly received',
        'received' => 'Goods received',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_MODES = ['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank transfer', 'credit' => 'Credit (pay later)'];

    protected $fillable = [
        'purchase_date', 'vendor_id', 'supplier_name', 'invoice_number', 'supplier_reference',
        'payment_mode', 'payment_status', 'amount_paid',
        'subtotal', 'tax_amount', 'discount_amount', 'grand_total',
        'status', 'notes', 'purchase_plan_id', 'inventory_bill_upload_id', 'client_token',
        'created_by', 'submitted_at', 'approved_by', 'approved_at',
        'rejected_by', 'rejected_at', 'rejection_reason', 'completed_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'amount_paid' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class)->latest('received_at');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(InventoryBillUpload::class, 'inventory_bill_upload_id');
    }

    public function reference(): string
    {
        return 'PO-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function supplierLabel(): string
    {
        return $this->vendor?->name ?? $this->supplier_name ?? '—';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'submitted'], true);
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, ['approved', 'partially_received'], true);
    }

    public static function statusTone(string $status): string
    {
        return match ($status) {
            'draft' => 'neutral', 'submitted' => 'warn', 'approved', 'partially_received' => 'info',
            'received', 'completed' => 'ok', 'rejected', 'cancelled' => 'bad', default => 'neutral',
        };
    }
}
