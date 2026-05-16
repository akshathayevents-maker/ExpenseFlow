<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockAlert extends Model
{
    protected $fillable = [
        'inventory_item_id', 'alert_type', 'stock_at_alert',
        'is_resolved', 'resolved_at', 'resolved_by', 'notes',
    ];

    protected $casts = [
        'is_resolved'    => 'boolean',
        'resolved_at'    => 'datetime',
        'stock_at_alert' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
}
