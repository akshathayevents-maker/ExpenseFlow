<?php

namespace App\Models;

use App\Services\MenuItemSync;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventRequestMenuItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'is_veg', 'price_per_person',
        'image_path', 'is_popular', 'is_chef_recommended', 'display_order', 'is_active',
    ];

    protected $casts = [
        'is_veg' => 'boolean',
        'is_popular' => 'boolean',
        'is_chef_recommended' => 'boolean',
        'is_active' => 'boolean',
        'price_per_person' => 'decimal:2',
        'display_order' => 'integer',
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
            'veg' => $query->veg(),
            'non_veg' => $query->nonVeg(),
            default => $query, // 'both' or null — no filter
        };
    }

    public function menuItemMirror(): HasOne
    {
        return $this->hasOne(MenuItem::class, 'event_request_menu_item_id');
    }

    /** Keep the Menu Composer item master in step (names / categories / new and removed dishes). */
    protected static function booted(): void
    {
        static::saved(function (self $er) {
            if (! MenuItemSync::busy()) {
                app(MenuItemSync::class)->fromEventRequest($er);
            }
        });
        static::deleting(function (self $er) {
            // the menu item stays (it may be used in saved menus) but is switched off and unlinked
            MenuItemSync::quietly(fn () => MenuItem::where('event_request_menu_item_id', $er->id)
                ->update(['is_active' => false, 'event_request_menu_item_id' => null]));
        });
    }
}
