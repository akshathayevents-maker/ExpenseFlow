<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeSop extends Model
{
    protected $fillable = [
        'recipe_id', 'step_number', 'title', 'instruction', 'duration_minutes',
    ];

    protected $casts = [
        'step_number'      => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
