<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMealEntryItem extends Model
{
    protected $fillable = [
        'daily_meal_entry_id', 'meal_type', 'planned_count', 'actual_count', 'sort_order',
    ];

    protected $casts = [
        'planned_count' => 'integer',
        'actual_count'  => 'integer',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(DailyMealEntry::class, 'daily_meal_entry_id');
    }

    public function difference(): ?int
    {
        if ($this->actual_count === null) return null;
        return $this->actual_count - $this->planned_count;
    }

    public function diffClass(): string
    {
        $d = $this->difference();
        if ($d === null) return 'dmr-neutral';
        if ($d > 0) return 'dmr-over';
        if ($d < 0) return 'dmr-under';
        return 'dmr-neutral';
    }

    public static function mealTypes(): array
    {
        return [
            'breakfast' => ['label' => 'Breakfast', 'sort' => 1, 'icon' => '☀️'],
            'lunch'     => ['label' => 'Lunch',     'sort' => 2, 'icon' => '🍱'],
            'dinner'    => ['label' => 'Dinner',    'sort' => 3, 'icon' => '🌙'],
        ];
    }

    public function mealLabel(): string
    {
        return static::mealTypes()[$this->meal_type]['label'] ?? ucfirst($this->meal_type);
    }

    public function mealIcon(): string
    {
        return static::mealTypes()[$this->meal_type]['icon'] ?? '🍽️';
    }
}
