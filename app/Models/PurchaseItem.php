<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 'inventory_item_id', 'description', 'quantity', 'unit', 'base_quantity',
        'rate', 'tax_percent', 'amount', 'received_base_quantity', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'float',
        'base_quantity' => 'float',
        'rate' => 'float',
        'tax_percent' => 'float',
        'amount' => 'float',
        'received_base_quantity' => 'float',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function pendingBaseQuantity(): float
    {
        return max(0, round($this->base_quantity - $this->received_base_quantity, 3));
    }

    /** Cost per stock unit incl. tax (used as unit_cost on receipt). */
    public function baseUnitCost(): float
    {
        return $this->base_quantity > 0 ? round($this->amount * (1 + $this->tax_percent / 100) / $this->base_quantity, 4) : 0;
    }
}
