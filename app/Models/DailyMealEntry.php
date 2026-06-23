<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMealEntry extends Model
{
    protected $fillable = [
        'meal_client_id', 'meal_date', 'menu_draft_id', 'remarks', 'created_by', 'updated_by',
    ];

    protected $casts = ['meal_date' => 'date'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(MealClient::class, 'meal_client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyMealEntryItem::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function menuDraft(): BelongsTo
    {
        return $this->belongsTo(MenuDraft::class);
    }

    public function totalPlanned(): int
    {
        return $this->items->sum('planned_count');
    }

    public function totalActual(): int
    {
        return $this->items->sum(fn($i) => $i->actual_count ?? 0);
    }
}
