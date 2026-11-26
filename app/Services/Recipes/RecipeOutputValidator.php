<?php

namespace App\Services\Recipes;

use App\Models\Recipe;
use App\Services\Inventory\UnitConversionService;
use App\Support\ItemText;

/**
 * Checks and normalises AI recipe output before anything is saved.
 * Rejects (throws InvalidRecipeOutput) output that is incomplete or implausible; normalises units and text.
 */
class RecipeOutputValidator
{
    private const MIN_INGREDIENTS = 3;

    private const MIN_STEPS = 3;

    private const VAGUE = ['cook until done', 'cook till done', 'as usual'];

    public function __construct(private UnitConversionService $units) {}

    /** @return array normalised recipe */
    public function validate(array $r, array $config = []): array
    {
        $errors = [];
        $name = trim((string) ($r['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 200) {
            $errors[] = 'missing or too long name';
        }
        $category = in_array($r['category'] ?? null, Recipe::categories(), true) ? $r['category'] : ($config['category'] ?? 'Other');
        $foodType = array_key_exists($r['food_type'] ?? '', Recipe::FOOD_TYPES) ? $r['food_type'] : ($config['food_type'] ?? null);
        if (! empty($config['food_type']) && $foodType !== $config['food_type']) {
            $errors[] = "food type is {$foodType}, expected {$config['food_type']}";
        }
        $yield = (float) ($r['yield_per_batch'] ?? 0);
        if ($yield <= 0 || $yield > 5000) {
            $errors[] = 'implausible batch yield';
        }

        $ingredients = [];
        $numeric = 0;
        foreach ((array) ($r['ingredients'] ?? []) as $i => $ing) {
            $iname = trim(preg_replace('/\s+/u', ' ', (string) ($ing['name'] ?? '')));
            if ($iname === '') {
                continue;
            }
            $qty = isset($ing['quantity']) && is_numeric($ing['quantity']) ? (float) $ing['quantity'] : null;
            if ($qty !== null && ($qty <= 0 || $qty > 100000)) {
                $errors[] = "{$iname}: implausible quantity {$qty}";
                $qty = null;
            }
            $unit = $this->unit($ing['unit'] ?? null);
            if ($qty !== null) {
                $numeric++;
                if ($unit === null) {
                    $errors[] = "{$iname}: quantity without a unit";
                }
                if ($yield > 0 && $this->perPersonGrams($qty, $unit, $yield) > 1500) {
                    $errors[] = "{$iname}: {$qty} {$unit} for {$yield} people is implausible";
                }
            }
            $display = trim((string) ($ing['display_quantity'] ?? '')) ?: null;
            $approx = (bool) ($ing['approximate'] ?? false);
            if ($qty === null && $display === null) {
                $display = 'As required';
                $approx = true;
            }
            $ingredients[] = [
                'ingredient_name' => mb_substr($iname, 0, 200),
                'ingredient_name_ta' => $this->tamil($ing['name_ta'] ?? null, 200),
                'quantity_per_batch' => $qty,
                'unit' => $unit,
                'prep_note' => mb_substr(trim((string) ($ing['prep_note'] ?? '')), 0, 200) ?: null,
                'quantity_note' => $display ? mb_substr($display, 0, 200) : null,
                'is_approximate' => $approx,
                'is_optional' => (bool) ($ing['optional'] ?? false),
            ];
        }
        if (count($ingredients) < self::MIN_INGREDIENTS) {
            $errors[] = 'too few ingredients';
        } elseif ($numeric < max(2, (int) ceil(count($ingredients) * 0.6))) {
            $errors[] = 'too many ingredients without a numeric quantity';
        }

        // vegetarian / vegan recipes must not contain meat, fish or egg (vegan: no dairy either)
        $names = mb_strtolower(implode(' | ', array_column($ingredients, 'ingredient_name')));
        if (in_array($foodType, ['veg', 'vegan'], true) && preg_match('/\b(chicken|mutton|lamb|goat|beef|pork|fish|prawn|shrimp|crab|egg|eggs)\b/u', $names, $m)) {
            $errors[] = "vegetarian recipe contains {$m[1]}";
        }
        if ($foodType === 'vegan' && preg_match('/\b(milk|curd|ghee|butter|paneer|cream|yogurt)\b/u', $names, $m)) {
            $errors[] = "vegan recipe contains {$m[1]}";
        }

        $missingTamil = [];
        $steps = [];
        foreach ((array) ($r['steps'] ?? []) as $s) {
            $title = trim((string) ($s['title'] ?? ''));
            $text = trim((string) ($s['instruction'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            if (mb_strlen($text) < 25 || in_array(mb_strtolower(rtrim($text, '. ')), self::VAGUE, true)) {
                $errors[] = 'a step is too vague: "'.mb_substr($text, 0, 40).'"';
            }
            $dur = isset($s['duration_minutes']) && is_numeric($s['duration_minutes']) ? (int) $s['duration_minutes'] : null;
            $ta = $this->tamil($s['instruction_ta'] ?? null);
            if ($ta === null) {
                $missingTamil[] = count($steps) + 1;
            }
            $steps[] = [
                'title' => mb_substr($title ?: 'Step', 0, 200), 'title_ta' => $this->tamil($s['title_ta'] ?? null, 200),
                'instruction' => $text, 'instruction_ta' => $ta,
                'note' => mb_substr(trim((string) ($s['note'] ?? '')), 0, 500) ?: null, 'note_ta' => $this->tamil($s['note_ta'] ?? null, 500),
                'duration_minutes' => $dur !== null && $dur >= 0 && $dur <= 1440 ? $dur : null,
            ];
        }
        if (count($steps) < self::MIN_STEPS) {
            $errors[] = 'too few preparation steps';
        }

        if ($errors) {
            throw new InvalidRecipeOutput('AI recipe rejected: '.implode('; ', array_slice($errors, 0, 6)));
        }

        $minutes = fn ($v) => is_numeric($v) && $v >= 0 && $v <= 1440 ? (int) $v : null;

        return [
            'name' => $name,
            'category' => $category,
            'cuisine' => mb_substr(trim((string) ($r['cuisine'] ?? ($config['cuisine'] ?? ''))), 0, 60) ?: null,
            'food_type' => $foodType,
            'description' => trim((string) ($r['description'] ?? '')) ?: null,
            'yield_per_batch' => $yield,
            'yield_unit' => mb_substr(trim((string) ($r['yield_unit'] ?? 'people')), 0, 50) ?: 'people',
            'prep_time_minutes' => $minutes($r['prep_time_minutes'] ?? null),
            'cook_time_minutes' => $minutes($r['cook_time_minutes'] ?? null),
            'ingredients' => $ingredients,
            'steps' => $steps,
            'name_ta' => $this->tamil($r['name_ta'] ?? null, 200),
            'kitchen_notes' => trim((string) ($r['kitchen_notes'] ?? '')) ?: null,
            'kitchen_notes_ta' => $this->tamil($r['kitchen_notes_ta'] ?? null),
            // incomplete Tamil is not a rejection — the recipe is flagged for review instead
            'missing_tamil_steps' => $missingTamil,
        ];
    }

    /** Tamil text only if it actually contains Tamil script (an English copy does not count as a translation). */
    private function tamil(mixed $v, int $max = 5000): ?string
    {
        $v = trim((string) $v);

        return $v !== '' && ItemText::hasTamil($v) ? mb_substr($v, 0, $max) : null;
    }

    private function unit(?string $u): ?string
    {
        $u = trim((string) $u);
        if ($u === '') {
            return null;
        }
        $n = $this->units->normalize($u);
        $map = ['grams' => 'gram', 'g' => 'gram', 'gm' => 'gram', 'gms' => 'gram', 'kgs' => 'kg', 'kilogram' => 'kg', 'l' => 'litre',
            'liter' => 'litre', 'liters' => 'litre', 'litres' => 'litre', 'ltr' => 'litre', 'pieces' => 'piece', 'pcs' => 'piece', 'nos' => 'piece', 'bunch' => 'bundle'];

        return mb_substr($map[$n] ?? $n, 0, 50);
    }

    /** rough mass per person, to catch absurd numbers (litres ≈ kg) */
    private function perPersonGrams(float $qty, ?string $unit, float $people): float
    {
        $g = match ($unit) {
            'kg', 'litre' => $qty * 1000, 'gram', 'ml' => $qty, default => 0
        };

        return $g / max(1, $people);
    }
}
