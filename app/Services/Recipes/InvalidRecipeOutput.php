<?php

namespace App\Services\Recipes;

use RuntimeException;

/** AI output failed validation; retrying the same dish may produce a usable recipe. */
class InvalidRecipeOutput extends RuntimeException {}
