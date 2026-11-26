<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'name', 'name_ta', 'category', 'description', 'kitchen_notes', 'kitchen_notes_ta',
        'prep_time_minutes', 'cook_time_minutes',
        'yield_per_batch', 'yield_unit',
        'is_active', 'created_by',
        'version', 'parent_recipe_id', 'superseded_at',
        'verification_status', 'verified_by', 'verified_at', 'verification_notes', 'reviewed_by', 'reviewed_at',
        'source', 'cuisine', 'food_type', 'revision', 'generation_item_id', 'duplicate_of_id', 'ai_meta',
    ];

    public const VERIFICATION = [
        'unverified' => 'Not verified', 'ai_generated' => 'AI generated', 'needs_review' => 'Needs review',
        'reviewed' => 'Reviewed', 'verified' => 'Verified', 'archived' => 'Archived',
    ];

    public const CUISINES = ['Tamil Nadu', 'South Indian', 'Kerala', 'Andhra', 'Karnataka', 'Chettinad', 'North Indian', 'Indian', 'Indo-Chinese', 'Continental'];

    public const FOOD_TYPES = ['veg' => 'Vegetarian', 'non_veg' => 'Non-vegetarian', 'egg' => 'Egg', 'vegan' => 'Vegan'];

    protected $casts = [
        'yield_per_batch' => 'float',
        'is_active' => 'boolean',
        'prep_time_minutes' => 'integer',
        'version' => 'integer',
        'superseded_at' => 'datetime',
        'cook_time_minutes' => 'integer',
        'revision' => 'integer',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'ai_meta' => 'array',
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

    public function versions(): HasMany
    {
        return $this->hasMany(RecipeVersion::class)->orderByDesc('revision')->orderByDesc('id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    public function generationItem(): BelongsTo
    {
        return $this->belongsTo(RecipeGenerationItem::class, 'generation_item_id');
    }

    /** Only an explicit admin verification counts — never the AI. */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified' && $this->verified_by !== null && $this->verified_at !== null;
    }

    public function isAiGenerated(): bool
    {
        return $this->source === 'ai';
    }

    public function verificationLabel(): string
    {
        return self::VERIFICATION[$this->verification_status] ?? 'Not verified';
    }

    /** Everything that defines how the dish is made: used for snapshots and to detect material changes. */
    public function materialSnapshot(): array
    {
        $this->loadMissing(['ingredients', 'sops']);

        return [
            'name' => $this->name, 'name_ta' => $this->name_ta, 'category' => $this->category,
            'kitchen_notes' => $this->kitchen_notes, 'kitchen_notes_ta' => $this->kitchen_notes_ta, 'cuisine' => $this->cuisine, 'food_type' => $this->food_type,
            'description' => $this->description, 'yield_per_batch' => (float) $this->yield_per_batch, 'yield_unit' => $this->yield_unit,
            'prep_time_minutes' => $this->prep_time_minutes, 'cook_time_minutes' => $this->cook_time_minutes,
            'ingredients' => $this->ingredients->map(fn ($i) => [
                'ingredient_name' => $i->ingredient_name, 'ingredient_name_ta' => $i->ingredient_name_ta, 'inventory_item_id' => $i->inventory_item_id,
                'quantity_per_batch' => $i->quantity_per_batch !== null ? (float) $i->quantity_per_batch : null,
                'quantity_note' => $i->quantity_note, 'unit' => $i->unit, 'prep_note' => $i->prep_note,
                'is_optional' => (bool) $i->is_optional, 'is_approximate' => (bool) $i->is_approximate,
            ])->values()->all(),
            'steps' => $this->sops->map(fn ($s) => [
                'title' => $s->title, 'title_ta' => $s->title_ta, 'instruction' => $s->instruction, 'instruction_ta' => $s->instruction_ta,
                'note' => $s->note, 'note_ta' => $s->note_ta, 'duration_minutes' => $s->duration_minutes,
            ])->values()->all(),
        ];
    }

    /** The parts whose change invalidates verification (not the name/description/category). */
    public static function materialPart(array $snap): array
    {
        return [
            'yield' => [$snap['yield_per_batch'] ?? null, mb_strtolower(trim((string) ($snap['yield_unit'] ?? '')))],
            'ingredients' => array_map(fn ($i) => [
                mb_strtolower(trim((string) $i['ingredient_name'])), $i['inventory_item_id'] ?? null,
                isset($i['quantity_per_batch']) ? round((float) $i['quantity_per_batch'], 3) : null,
                mb_strtolower(trim((string) ($i['unit'] ?? ''))), trim((string) ($i['quantity_note'] ?? '')), (bool) ($i['is_optional'] ?? false),
            ], $snap['ingredients'] ?? []),
            // both languages count: a changed Tamil instruction changes what the kitchen does
            'steps' => array_map(fn ($s) => [trim((string) $s['title']), trim((string) $s['instruction']), trim((string) ($s['instruction_ta'] ?? '')), $s['duration_minutes'] ?? null], $snap['steps'] ?? []),
        ];
    }

    /** Steps without Tamil / without English instructions (1-based step numbers) — shown before verification. */
    public function languageGaps(): array
    {
        $this->loadMissing('sops');

        return [
            'name_ta' => trim((string) $this->name_ta) === '',
            'steps_without_tamil' => $this->sops->filter(fn ($s) => trim((string) $s->instruction_ta) === '')->pluck('step_number')->values()->all(),
            'steps_without_english' => $this->sops->filter(fn ($s) => trim((string) $s->instruction) === '')->pluck('step_number')->values()->all(),
        ];
    }

    /**
     * Ingredient quantities for $people (base quantities × people ÷ yield), plus per-serving amounts and practical
     * batch advice. Cooking times and spice/water ratios are NOT scaled — see batchPlan().
     */
    public function scaled(int $people): array
    {
        $this->loadMissing('ingredients.inventoryItem');
        $f = $this->scaleFactor($people);

        return $this->ingredients->map(fn ($i) => [
            'ingredient' => $i,
            'quantity' => $i->quantity_per_batch !== null ? round((float) $i->quantity_per_batch * $f, 3) : null,
            'per_serving' => $i->quantity_per_batch !== null && $this->yield_per_batch > 0 ? round((float) $i->quantity_per_batch / (float) $this->yield_per_batch, 4) : null,
        ])->all();
    }

    /** How to split a large order into vessel-sized batches of the tested base yield. */
    public function batchPlan(int $people): array
    {
        $base = max(1, (int) round((float) $this->yield_per_batch));
        $batches = (int) ceil($people / $base);

        return ['batches' => $batches, 'per_batch' => (int) ceil($people / max(1, $batches)), 'base' => $base,
            'note' => $batches > 1
                ? "Cook in {$batches} batches of about ".(int) ceil($people / $batches).' — use the base recipe times per batch; do not cook the whole quantity in one vessel.'
                : null,
            'note_ta' => $batches > 1
                ? "{$batches} தடவையாக, ஒவ்வொன்றும் சுமார் ".(int) ceil($people / $batches).' பேருக்கு சமைக்கவும் — ஒவ்வொரு தடவைக்கும் அடிப்படை நேரத்தையே பின்பற்றவும்; முழு அளவையும் ஒரே பாத்திரத்தில் சமைக்க வேண்டாம்.'
                : null];
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
            'Breakfast' => 'orange',
            'Lunch' => 'green',
            'Dinner' => 'blue',
            'Main Course' => 'indigo',
            'Starter' => 'teal',
            'Soup' => 'amber',
            'Salad' => 'lime',
            'Side Dish' => 'purple',
            'Snacks' => 'yellow',
            'Beverage' => 'cyan',
            'Sweet' => 'pink',
            'Dessert' => 'rose',
            'Other' => 'gray',
        ];
    }
}
