<?php

namespace App\Services\Ai;

/**
 * Generates recipe data. The app only talks to this interface so tests can swap in a fake
 * and the provider can change without touching jobs or controllers.
 */
interface RecipeAiClient
{
    /**
     * Propose dish names for a generation batch.
     *
     * @param  array  $config  category, cuisine, food_type, group, yield_people
     * @param  list<string>  $exclude  names that already exist (never propose these)
     * @return list<string>
     */
    public function planDishes(array $config, int $count, array $exclude): array;

    /**
     * Generate one complete recipe for a dish, in the RecipeOutputValidator shape.
     *
     * @return array{recipe: array, request_id: ?string, model: string}
     */
    public function generateRecipe(string $dish, array $config): array;

    public function isConfigured(): bool;

    public function providerName(): string;

    public function modelName(): string;
}
