<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryUnitConversion extends Model
{
    protected $fillable = ['inventory_item_id', 'unit', 'factor', 'notes', 'created_by'];

    protected $casts = ['factor' => 'float'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
