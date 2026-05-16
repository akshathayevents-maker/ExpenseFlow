<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'inventory_item_id', 'type', 'quantity', 'balance_before',
        'balance_after', 'unit_cost', 'notes', 'created_by',
        'reference_type', 'reference_id',
    ];

    protected $casts = [
        'quantity'       => 'float',
        'balance_before' => 'float',
        'balance_after'  => 'float',
        'unit_cost'      => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isAddition(): bool
    {
        return in_array($this->type, ['purchase', 'adjustment']);
    }

    public function isDeduction(): bool
    {
        return in_array($this->type, ['usage', 'wastage', 'transfer']);
    }

    public static function typeColors(): array
    {
        return [
            'purchase'   => 'success',
            'usage'      => 'primary',
            'adjustment' => 'secondary',
            'wastage'    => 'danger',
            'transfer'   => 'warning',
        ];
    }
}
