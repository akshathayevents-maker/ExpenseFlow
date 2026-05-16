<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePlanItem extends Model
{
    protected $fillable = [
        'purchase_plan_id', 'inventory_item_id',
        'suggested_quantity', 'estimated_unit_cost', 'priority', 'notes',
    ];

    protected $casts = [
        'suggested_quantity'  => 'float',
        'estimated_unit_cost' => 'float',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PurchasePlan::class, 'purchase_plan_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function estimatedTotal(): float
    {
        return ($this->estimated_unit_cost ?? 0) * $this->suggested_quantity;
    }

    public static function priorityColors(): array
    {
        return ['urgent' => 'danger', 'high' => 'warning', 'normal' => 'primary', 'low' => 'secondary'];
    }
}
