<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRequestMenuItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'is_veg', 'price_per_person',
        'image_path', 'is_popular', 'is_chef_recommended', 'display_order', 'is_active',
    ];

    protected $casts = [
        'is_veg'               => 'boolean',
        'is_popular'           => 'boolean',
        'is_chef_recommended'  => 'boolean',
        'is_active'            => 'boolean',
        'price_per_person'     => 'decimal:2',
        'display_order'        => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventRequestMenuCategory::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVeg(Builder $query): Builder
    {
        return $query->where('is_veg', true);
    }

    public function scopeNonVeg(Builder $query): Builder
    {
        return $query->where('is_veg', false);
    }

    /**
     * Filter items by the request's menu_type (veg|non_veg|both).
     */
    public function scopeForMenuType(Builder $query, ?string $menuType): Builder
    {
        return match ($menuType) {
            'veg'     => $query->veg(),
            'non_veg' => $query->nonVeg(),
            default   => $query, // 'both' or null — no filter
        };
    }
}
