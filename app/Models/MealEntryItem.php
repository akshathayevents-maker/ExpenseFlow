<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealEntryItem extends Model
{
    protected $fillable = [
        'meal_entry_id', 'meal_type', 'planned_count', 'actual_count',
        'sort_order', 'planned_updated_by', 'actual_updated_by',
    ];

    protected $casts = [
        'planned_count' => 'integer',
        'actual_count'  => 'integer',
    ];

    public static function mealTypes(): array
    {
        return [
            'breakfast' => ['label' => 'Breakfast', 'icon' => '☀️', 'sort' => 1],
            'lunch'     => ['label' => 'Lunch',     'icon' => '🍱', 'sort' => 2],
            'dinner'    => ['label' => 'Dinner',    'icon' => '🌙', 'sort' => 3],
        ];
    }

    public function entry(): BelongsTo { return $this->belongsTo(MealEntry::class, 'meal_entry_id'); }
    public function plannedUpdater(): BelongsTo { return $this->belongsTo(User::class, 'planned_updated_by'); }
    public function actualUpdater(): BelongsTo  { return $this->belongsTo(User::class, 'actual_updated_by'); }

    public function variance(): ?int
    {
        if ($this->planned_count === null || $this->actual_count === null) return null;
        return $this->actual_count - $this->planned_count;
    }

    public function varianceClass(): string
    {
        $v = $this->variance();
        if ($v === null) return 'neutral';
        if ($v > 0)  return 'over';
        if ($v < 0)  return 'under';
        return 'equal';
    }

    public function mealLabel(): string { return static::mealTypes()[$this->meal_type]['label'] ?? ucfirst($this->meal_type); }
    public function mealIcon(): string  { return static::mealTypes()[$this->meal_type]['icon'] ?? '🍽️'; }

    // Aliases for backward-compatible view compatibility
    public function difference(): ?int { return $this->variance(); }

    public function diffClass(): string
    {
        $v = $this->variance();
        if ($v === null) return 'dmr-neutral';
        if ($v > 0)  return 'dmr-over';
        if ($v < 0)  return 'dmr-under';
        return 'dmr-neutral';
    }
}
