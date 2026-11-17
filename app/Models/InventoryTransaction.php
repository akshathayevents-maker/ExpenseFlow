<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    /** Current types + legacy ones (purchase, usage, transfer) kept readable. */
    public const TYPES = [
        'purchase_receipt', 'material_issue', 'material_return',
        'adjustment', 'correction', 'wastage', 'reversal',
        'purchase', 'usage', 'transfer',
    ];

    protected $fillable = [
        'inventory_item_id', 'type', 'quantity', 'balance_before',
        'balance_after', 'unit_cost', 'notes', 'created_by',
        'reference_type', 'reference_id', 'reference_label',
        'entered_quantity', 'entered_unit', 'reversal_of_id',
    ];

    protected $casts = [
        'quantity' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'unit_cost' => 'float',
        'entered_quantity' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    /** Positive = stock in, negative = stock out (derived from the balances). */
    public function signedQuantity(): float
    {
        return round($this->balance_after - $this->balance_before, 3);
    }

    public function isAddition(): bool
    {
        return $this->signedQuantity() > 0;
    }

    public function isDeduction(): bool
    {
        return $this->signedQuantity() < 0;
    }

    public static function typeLabels(): array
    {
        return [
            'purchase_receipt' => 'Purchase receipt',
            'material_issue' => 'Material issue',
            'material_return' => 'Material return',
            'adjustment' => 'Stock adjustment',
            'correction' => 'Stock correction',
            'wastage' => 'Store wastage',
            'reversal' => 'Reversal',
            'purchase' => 'Purchase (legacy)',
            'usage' => 'Usage (legacy)',
            'transfer' => 'Transfer',
        ];
    }

    public function typeLabel(): string
    {
        return static::typeLabels()[$this->type] ?? ucfirst($this->type);
    }

    public static function typeColors(): array
    {
        return [
            'purchase' => 'success',
            'purchase_receipt' => 'success',
            'usage' => 'primary',
            'material_issue' => 'primary',
            'material_return' => 'info',
            'adjustment' => 'secondary',
            'correction' => 'secondary',
            'wastage' => 'danger',
            'reversal' => 'warning',
            'transfer' => 'warning',
        ];
    }
}
