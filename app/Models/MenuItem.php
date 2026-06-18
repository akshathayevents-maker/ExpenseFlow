<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_key',
        'category_en',
        'category_ta',
        'item_en',
        'item_ta',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('category_key')->orderBy('sort_order')->orderBy('item_en');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Return all active items as a lightweight array for JS hydration.
     * Indexed by category sort order from config.
     */
    public static function forComposer(): array
    {
        $categoryOrder = array_keys(config('menu_categories.items', []));

        $items = static::active()
            ->orderBy('sort_order')
            ->orderBy('item_en')
            ->get(['id', 'category_key', 'category_en', 'category_ta', 'item_en', 'item_ta'])
            ->toArray();

        // Sort by config category order, then by sort_order/item_en
        usort($items, function ($a, $b) use ($categoryOrder) {
            $ai = array_search($a['category_key'], $categoryOrder);
            $bi = array_search($b['category_key'], $categoryOrder);
            $ai = $ai === false ? 999 : $ai;
            $bi = $bi === false ? 999 : $bi;
            return $ai <=> $bi;
        });

        return $items;
    }

    /**
     * Return the category sort order keys from config.
     */
    public static function categoryKeys(): array
    {
        return array_keys(config('menu_categories.items', []));
    }
}
