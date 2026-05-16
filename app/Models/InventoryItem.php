<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'name', 'sku', 'inventory_category_id', 'unit',
        'current_stock', 'minimum_stock', 'maximum_stock',
        'average_cost', 'description', 'status',
    ];

    protected $casts = [
        'current_stock' => 'float',
        'minimum_stock' => 'float',
        'maximum_stock' => 'float',
        'average_cost'  => 'float',
    ];

    public static array $units = [
        'kg' => 'kg', 'gram' => 'gram', 'litre' => 'litre', 'ml' => 'ml',
        'packet' => 'packet', 'piece' => 'piece', 'box' => 'box',
        'bundle' => 'bundle', 'cylinder' => 'cylinder', 'dozen' => 'dozen',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->latest();
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(InventoryStockAlert::class);
    }

    public function activeAlerts(): HasMany
    {
        return $this->hasMany(InventoryStockAlert::class)->where('is_resolved', false);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock && $this->current_stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function isCritical(): bool
    {
        return $this->isLowStock() || $this->isOutOfStock();
    }

    public function estimatedValue(): float
    {
        return $this->average_cost ? $this->current_stock * $this->average_cost : 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    public function scopeCritical($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }
}
