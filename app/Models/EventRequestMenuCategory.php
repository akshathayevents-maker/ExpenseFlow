<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventRequestMenuCategory extends Model
{
    protected $fillable = [
        'name', 'description', 'display_order', 'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active'     => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(EventRequestMenuItem::class, 'category_id');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true)->orderBy('display_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
