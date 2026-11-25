<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItemAlias extends Model
{
    protected $fillable = [
        'inventory_item_id', 'alias', 'normalized_alias', 'language', 'alias_type', 'phonetic_key', 'vendor_id',
        'unit_hint', 'source', 'confirmations', 'created_by',
    ];

    protected $casts = ['confirmations' => 'integer'];

    public const LANGUAGES = ['en' => 'English', 'ta' => 'Tamil', 'tanglish' => 'Tamil in English letters'];

    protected static function booted(): void
    {
        static::saving(function (self $a) {
            $a->normalized_alias = \App\Support\ItemText::normalize($a->alias);
            $a->phonetic_key = \App\Support\ItemText::phonetic($a->alias);
            $a->language ??= \App\Support\ItemText::hasTamil($a->alias) ? 'ta' : 'en';
        });
        static::saved(fn () => \App\Services\Inventory\ItemSearchService::flush());
        static::deleted(fn () => \App\Services\Inventory\ItemSearchService::flush());
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
