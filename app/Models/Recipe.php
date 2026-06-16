<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'name', 'category', 'description',
        'prep_time_minutes', 'cook_time_minutes',
        'yield_per_batch', 'yield_unit',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'yield_per_batch'    => 'float',
        'is_active'          => 'boolean',
        'prep_time_minutes'  => 'integer',
        'cook_time_minutes'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order')->orderBy('id');
    }

    public function sops(): HasMany
    {
        return $this->hasMany(RecipeSop::class)->orderBy('step_number');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    // ── Computed ───────────────────────────────────────────────────────────────

    public function totalTimeMinutes(): int
    {
        return ($this->prep_time_minutes ?? 0) + ($this->cook_time_minutes ?? 0);
    }

    /**
     * Scale factor for a given guest count.
     * Divide guest count by yield_per_batch to get number of batches needed.
     */
    public function scaleFactor(int $people): float
    {
        if ($this->yield_per_batch <= 0) {
            return 1.0;
        }
        return $people / $this->yield_per_batch;
    }

    public function batchCount(int $people): float
    {
        return $this->scaleFactor($people);
    }

    // ── Static lookups ─────────────────────────────────────────────────────────

    public static function categories(): array
    {
        return [
            'Breakfast', 'Lunch', 'Dinner',
            'Main Course', 'Starter', 'Soup', 'Salad',
            'Side Dish', 'Snacks', 'Beverage',
            'Sweet', 'Dessert', 'Other',
        ];
    }

    public static function categoryColors(): array
    {
        return [
            'Breakfast'   => 'orange',
            'Lunch'       => 'green',
            'Dinner'      => 'blue',
            'Main Course' => 'indigo',
            'Starter'     => 'teal',
            'Soup'        => 'amber',
            'Salad'       => 'lime',
            'Side Dish'   => 'purple',
            'Snacks'      => 'yellow',
            'Beverage'    => 'cyan',
            'Sweet'       => 'pink',
            'Dessert'     => 'rose',
            'Other'       => 'gray',
        ];
    }
}
