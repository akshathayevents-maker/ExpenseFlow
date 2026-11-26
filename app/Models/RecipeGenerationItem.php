<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeGenerationItem extends Model
{
    protected $fillable = ['recipe_generation_batch_id', 'dish_name', 'dish_key', 'status', 'attempts', 'recipe_id', 'error', 'request_id'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(RecipeGenerationBatch::class, 'recipe_generation_batch_id');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
