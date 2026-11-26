<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Immutable snapshot of a recipe (details + ingredients + steps) at one revision. */
class RecipeVersion extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['recipe_id', 'revision', 'event', 'snapshot', 'change_summary', 'verified_by', 'verified_at', 'changed_by', 'created_at'];

    protected $casts = ['snapshot' => 'array', 'verified_at' => 'datetime', 'created_at' => 'datetime'];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
