<?php

namespace App\Services\Inventory;

use App\Models\HallBooking;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Support\ItemText;
use Illuminate\Support\Collection;

/**
 * Everything a material request for a hall order can be pre-filled with:
 * date / time / people from the booking, preparations from its final menu, and materials
 * from the kitchen recipes whose name matches a menu item, scaled to that item's head count.
 * Nothing is saved here — the form shows it and every value stays editable.
 */
class HallOrderPrefillService
{
    /** yield units that mean "servings" (one serving per guest) */
    private const SERVING_UNITS = ['people', 'person', 'persons', 'pax', 'guest', 'guests', 'serving', 'servings', 'portion', 'portions', 'plate', 'plates', 'unit', 'units'];

    public function __construct(
        private UnitConversionService $units,
        private ItemCatalogueService $catalogue,
        private ItemSearchService $search,
        private HallMenuService $menus,
    ) {}

    public function forBooking(HallBooking $b): array
    {
        $b->loadMissing('meals');
        $people = (int) $b->number_of_people;
        $mealPeople = $b->meals->pluck('guest_count', 'meal_type')->filter()->map(fn ($n) => (int) $n);

        // same source as the final-menu editor: saved final menu → event request → meal plan dishes
        $menu = collect($this->menus->draftLines($b))->where('is_removed', false)->values();
        $preparations = $menu->map(fn ($m) => [
            'name' => mb_substr($m['name'], 0, 150),
            'people' => $mealPeople[$m['meal_type']] ?? $people ?: null,
            'meal_type' => $m['meal_type'],
        ])->unique(fn ($p) => mb_strtolower($p['name']))->values();

        $recipes = $this->recipeIndex();
        $lines = [];       // key item|unit => line
        $matched = [];
        $noRecipe = [];
        $unlinked = [];

        foreach ($preparations as $p) {
            $recipe = $this->recipeFor($p['name'], $recipes);
            if (! $recipe) {
                $noRecipe[] = $p['name'];

                continue;
            }
            $count = (int) ($p['people'] ?: $people);
            $perServing = $this->servingsFactor($recipe);
            $used = 0;
            foreach ($recipe->ingredients as $ing) {
                if ($ing->is_optional) {
                    continue;
                }
                $item = $this->itemFor($ing);
                [$qty, $qtyUnit] = $this->quantityFor($ing, $perServing, $count);
                if (! $item || $qty === null) {
                    $unlinked[] = trim($ing->ingredient_name.($item ? ' (no quantity per person)' : '')).' — '.$recipe->name;

                    continue;
                }
                [$q, $unit] = $this->inStockUnit($item, $qty, $qtyUnit);
                $key = $item->id.'|'.$unit;
                $lines[$key] ??= ['inventory_item_id' => $item->id, 'quantity' => 0, 'unit' => $unit, 'from' => []];
                $lines[$key]['quantity'] = round($lines[$key]['quantity'] + $q, 3);
                $lines[$key]['from'][] = $recipe->name;
                $used++;
            }
            $matched[] = ['menu' => $p['name'], 'recipe' => $recipe->name, 'recipe_id' => $recipe->id, 'people' => $count, 'items' => $used];
        }

        return [
            'booking_id' => $b->id,
            'required_date' => $b->booking_date->toDateString(),
            'required_time' => $b->start_time ? substr((string) $b->start_time, 0, 5) : null,
            'people' => $people ?: null,
            'preparations' => $preparations->map(fn ($p) => ['name' => $p['name'], 'people' => $p['people']])->all(),
            'lines' => array_values(array_map(fn ($l) => $l + ['from' => array_values(array_unique($l['from']))], $lines)),
            'recipes' => $matched,
            'no_recipe' => $noRecipe,
            'unlinked' => array_values(array_unique($unlinked)),
            'menu_url' => route('hall.bookings.menu.edit', $b),
            'has_menu' => $menu->isNotEmpty(),
        ];
    }

    /** current recipe versions keyed by a spelling-insensitive name key (trailing version numbers ignored) */
    private function recipeIndex(): Collection
    {
        return Recipe::with('ingredients.inventoryItem')->where('is_active', true)->whereNull('superseded_at')
            ->orderByDesc('version')->get()
            ->keyBy(fn ($r) => $this->key($r->name));
    }

    private function recipeFor(string $menuName, Collection $recipes): ?Recipe
    {
        $k = $this->key($menuName);
        if ($k === '') {
            return null;
        }
        if ($recipes->has($k)) {
            return $recipes[$k];
        }

        // "Rava Upma" ↔ recipe "Upma", "Ghee Pongal (special)" ↔ "Ghee Pongal": whole-word containment, longest recipe name wins
        return $recipes->filter(fn ($r, $rk) => mb_strlen($rk) >= 3 && (
            str_contains(" $k ", " $rk ") || str_contains(" $rk ", " $k ")
        ))->sortByDesc(fn ($r, $rk) => mb_strlen($rk))->first();
    }

    private function key(string $name): string
    {
        $name = preg_replace('/\(.*?\)|\bv(?:er(?:sion)?)?\s*\d+\b|\s+\d+$/iu', ' ', $name);

        return trim(ItemText::phonetic($name));
    }

    private function itemFor(RecipeIngredient $ing): ?InventoryItem
    {
        if ($ing->inventoryItem && $ing->inventoryItem->status === 'active') {
            return $ing->inventoryItem;
        }
        $name = trim((string) $ing->ingredient_name);
        if ($name === '') {
            return null;
        }
        $hit = $this->catalogue->itemNamed($name);
        if (! $hit) {
            $id = $this->search->search($name, 5)['exact_id'] ?? null;
            $hit = $id ? InventoryItem::find($id) : null;
        }
        $hit = $hit ? InventoryItem::find($hit->id) : null;

        return $hit && $hit->status === 'active' ? $hit : null;
    }

    /** per-guest share of one batch, or null when the recipe yield is not counted in servings */
    private function servingsFactor(Recipe $r): ?float
    {
        $unit = strtolower(trim((string) $r->yield_unit));

        return $r->yield_per_batch > 0 && ($unit === '' || in_array($unit, self::SERVING_UNITS, true)) ? 1 / (float) $r->yield_per_batch : null;
    }

    /** @return array{0: ?float, 1: ?string} quantity and the unit it is in (null = stock unit) */
    private function quantityFor(RecipeIngredient $ing, ?float $perServing, int $people): array
    {
        if ($people <= 0) {
            return [null, null];
        }
        if ($ing->quantity_per_guest) {   // recipe standard: stored per guest in the stock unit
            return [(float) $ing->quantity_per_guest * $people, null];
        }
        if ($perServing !== null && $ing->quantity_per_batch > 0) {
            return [(float) $ing->quantity_per_batch * $perServing * $people, $ing->unit];
        }

        return [null, null];
    }

    /** quantity_per_guest is stored in the stock unit; batch quantities are in the ingredient's unit */
    private function inStockUnit(InventoryItem $item, float $qty, ?string $unit): array
    {
        $u = $this->units->normalize($unit);
        if (! $u || $u === $item->unit || ! $this->units->canConvert($item, $u)) {
            return [round($qty, 3), $u && in_array($u, $this->units->unitsFor($item), true) ? $u : $item->unit];
        }

        return [round($this->units->toBase($item, $qty, $u), 3), $item->unit];
    }
}
