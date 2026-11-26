<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'name', 'name_ta', 'sku', 'inventory_category_id', 'unit',
        'current_stock', 'minimum_stock', 'maximum_stock',
        'average_cost', 'description', 'status', 'needs_review', 'created_by',
    ];

    protected $casts = [
        'current_stock' => 'float',
        'minimum_stock' => 'float',
        'maximum_stock' => 'float',
        'average_cost'  => 'float',
        'needs_review'  => 'boolean',
    ];

    protected static function booted(): void
    {
        // search_key is derived; the item search index is cached and must be refreshed on change.
        static::saving(fn (self $i) => $i->search_key = \App\Support\ItemText::phonetic($i->name));
        static::saved(function (self $i) {
            // stock postings update this row constantly; only catalogue fields affect search
            if ($i->wasRecentlyCreated || $i->wasChanged(['name', 'name_ta', 'status', 'unit', 'inventory_category_id'])) {
                \App\Services\Inventory\ItemSearchService::flush();
            }
        });
        static::deleted(fn () => \App\Services\Inventory\ItemSearchService::flush());
    }

    /** Unit code => code (config/inventory.php is the source). */
    public static function units(): array
    {
        $codes = array_keys(config('inventory.units', []));

        return array_combine($codes, $codes);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->latest();
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(InventoryItemAlias::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(InventoryUnitConversion::class);
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
