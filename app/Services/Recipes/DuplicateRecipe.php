<?php

namespace App\Services\Recipes;

use App\Models\Recipe;
use RuntimeException;

/** The dish already exists in the library (same name, spelling-insensitive); nothing was saved. */
class DuplicateRecipe extends RuntimeException
{
    public function __construct(public readonly Recipe $existing)
    {
        parent::__construct("Already in the library as “{$existing->name}”.");
    }
}
