<?php

namespace App\Models;

use App\Services\MenuItemSync;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_request_menu_item_id',
        'category_key',
        'category_en',
        'category_ta',
        'item_en',
        'item_ta',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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

    public function eventRequestItem(): BelongsTo
    {
        return $this->belongsTo(EventRequestMenuItem::class, 'event_request_menu_item_id');
    }

    /** Keep the Event Request menu in step (names / categories / new dishes). */
    protected static function booted(): void
    {
        static::saved(function (self $m) {
            if (! MenuItemSync::busy() && ($m->wasRecentlyCreated || $m->wasChanged(['item_en', 'category_key']) || ! $m->event_request_menu_item_id)) {
                app(MenuItemSync::class)->fromMenu($m);
            }
        });
        static::deleting(function (self $m) {
            // the event-request dish may be on past customer requests: switch it off instead of deleting it
            if ($m->event_request_menu_item_id && ! MenuItemSync::busy()) {
                MenuItemSync::quietly(fn () => EventRequestMenuItem::whereKey($m->event_request_menu_item_id)->update(['is_active' => false]));
            }
        });
    }
}
