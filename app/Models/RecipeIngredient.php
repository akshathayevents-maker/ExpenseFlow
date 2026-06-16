<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id', 'inventory_item_id',
        'ingredient_name', 'quantity_per_batch', 'quantity_note',
        'unit', 'prep_note', 'is_optional', 'sort_order',
    ];

    protected $casts = [
        'quantity_per_batch' => 'float',
        'is_optional'        => 'boolean',
        'sort_order'         => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * withDefault() returns an empty InventoryItem when inventory_item_id is null,
     * so callers never need null checks on this relationship.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class)->withDefault();
    }

    // ── Scaling logic ──────────────────────────────────────────────────────────

    /**
     * Only numeric quantity_per_batch values are scalable.
     * Free-text quantity_note values ("As Required", "Approx 20 L") are never scaled.
     */
    public function isScalable(): bool
    {
        return $this->quantity_per_batch !== null;
    }

    /**
     * Returns the scaled quantity as a formatted string, or null if not scalable.
     * Trailing zeros are stripped: 24.000 → "24", 17.500 → "17.5"
     */
    public function scaledQuantity(float $scaleFactor): ?string
    {
        if (! $this->isScalable()) {
            return null;
        }

        $scaled = (float) $this->quantity_per_batch * $scaleFactor;

        return $this->formatQuantity($scaled);
    }

    /**
     * Display string for the unscaled quantity (used on recipe view / admin).
     * Returns quantity_note verbatim if quantity_per_batch is null.
     */
    public function displayQuantity(): string
    {
        if ($this->quantity_note && $this->quantity_per_batch === null) {
            return $this->quantity_note;
        }

        if ($this->quantity_per_batch !== null) {
            return $this->formatQuantity((float) $this->quantity_per_batch);
        }

        return '—';
    }

    /**
     * Full display line for calculator output: "24 kg" or "As Required".
     * Used by both Detailed and Compact view modes in the employee calculator.
     */
    public function calculatorDisplay(float $scaleFactor): array
    {
        if ($this->isScalable()) {
            return [
                'quantity' => $this->scaledQuantity($scaleFactor),
                'unit'     => $this->unit ?? '',
                'note'     => null,
                'scalable' => true,
            ];
        }

        return [
            'quantity' => null,
            'unit'     => null,
            'note'     => $this->quantity_note ?? 'As Required',
            'scalable' => false,
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function formatQuantity(float $value): string
    {
        // Format to 3 decimal places then strip trailing zeros and dot
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }
}
