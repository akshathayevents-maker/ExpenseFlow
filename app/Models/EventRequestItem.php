<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRequestItem extends Model
{
    protected $fillable = [
        'event_request_id', 'menu_item_id', 'name_snapshot',
        'category_name_snapshot', 'is_veg_snapshot', 'price_per_person_snapshot',
    ];

    protected $casts = [
        'is_veg_snapshot'           => 'boolean',
        'price_per_person_snapshot' => 'decimal:2',
    ];

    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(EventRequestMenuItem::class, 'menu_item_id');
    }
}
