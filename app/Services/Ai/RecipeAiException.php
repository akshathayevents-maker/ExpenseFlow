<?php

namespace App\Services\Ai;

use RuntimeException;

/** $retryable = worth trying again later (rate limit, overload, network); false = fix the input/config. */
class RecipeAiException extends RuntimeException
{
    public function __construct(string $message, public readonly bool $retryable = false, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
