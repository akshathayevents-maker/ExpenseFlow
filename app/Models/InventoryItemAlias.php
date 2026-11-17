<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItemAlias extends Model
{
    protected $fillable = [
        'inventory_item_id', 'alias', 'normalized_alias', 'vendor_id',
        'unit_hint', 'source', 'confirmations', 'created_by',
    ];

    protected $casts = ['confirmations' => 'integer'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
