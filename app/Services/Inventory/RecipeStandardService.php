<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\MaterialRequest;
use App\Models\Recipe;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Gradual standardisation:
 *  1. history()  — what reconciled requests actually used per person, per preparation.
 *  2. saveStandard() — admin turns that into a recipe VERSION (per-guest qty per item).
 *     Changing a standard always creates a new version; old versions stay untouched.
 *  3. suggest()  — proposes quantities for N people. Staff can change every number;
 *     requests remember the recipe version and the suggested quantity.
 */
class RecipeStandardService
{
    public function __construct(private AuditLogService $audit) {}

    /** Preparations with recorded usage (reconciled requests with a guest count). */
    public function preparations(): Collection
    {
        return MaterialRequest::where('status', 'reconciled')->whereNotNull('preparation_name')->where('guest_count', '>', 0)
            ->get(['id', 'preparation_name', 'guest_count'])
            ->groupBy(fn ($r) => mb_strtolower(trim($r->preparation_name)))
            ->map(fn ($g) => ['name' => trim($g->first()->preparation_name), 'times' => $g->count(),
                'avg_guests' => (int) round($g->avg('guest_count')), 'standard' => $this->currentFor(trim($g->first()->preparation_name))])
            ->sortByDesc('times')->values();
    }

    /**
     * Per-person usage statistics for one preparation, by item (stock unit).
     * "Used" = consumed + wasted + unexplained (everything that did not come back).
     */
    public function history(string $preparation): array
    {
        $reqs = MaterialRequest::with('items.item')->where('status', 'reconciled')->where('guest_count', '>', 0)
            ->whereRaw('LOWER(TRIM(preparation_name)) = ?', [mb_strtolower(trim($preparation))])
            ->orderBy('required_date')->get();
        $perItem = [];
        foreach ($reqs as $r) {
            foreach ($r->items as $l) {
                if ($l->line_status === 'rejected') {
                    continue;
                }
                $out = (float) $l->consumed_base_quantity + (float) $l->wasted_base_quantity + (float) $l->unexplained_base_quantity;
                $perItem[$l->inventory_item_id]['item'] = $l->item;
                $perItem[$l->inventory_item_id]['per_guest'][] = $out / $r->guest_count;
                $perItem[$l->inventory_item_id]['consumed_per_guest'][] = (float) $l->consumed_base_quantity / $r->guest_count;
            }
        }
        $rows = [];
        foreach ($perItem as $id => $d) {
            $v = collect($d['per_guest']);
            $c = collect($d['consumed_per_guest']);
            $rows[$id] = [
                'item_id' => $id, 'item' => $d['item']->name, 'unit' => $d['item']->unit, 'times' => $v->count(),
                'avg' => round($v->avg(), 5), 'median' => round($v->median(), 5), 'min' => round($v->min(), 5), 'max' => round($v->max(), 5),
                'consumed_median' => round($c->median(), 5),
            ];
        }
        uasort($rows, fn ($a, $b) => strcmp($a['item'], $b['item']));

        return ['preparation' => $preparation, 'requests' => $reqs->count(), 'rows' => $rows,
            'guests' => $reqs->pluck('guest_count')->all(), 'current' => $this->currentFor($preparation)];
    }

    /**
     * Expected quantity per item (stock unit) for a material request, from the recipe standard of each preparation
     * × its head count, or — when no standard exists — the median per-person usage of that preparation (3+ past uses).
     * Used to warn at approval and at usage recording. Nothing is enforced silently.
     *
     * @return array<int, array{qty: float, source: string, label: string}>
     */
    public function expectedFor(MaterialRequest $mr): array
    {
        $preps = collect($mr->preparationList())->filter(fn ($p) => trim((string) ($p['name'] ?? '')) !== '' && (int) ($p['people'] ?? 0) > 0);
        $out = [];
        foreach ($preps as $p) {
            $people = (int) $p['people'];
            $recipe = $this->currentFor($p['name']);
            if (! $recipe && $preps->count() === 1 && $mr->recipe_id) {
                $recipe = Recipe::with('ingredients')->find($mr->recipe_id);
            }
            if ($recipe) {
                foreach ($recipe->ingredients as $ing) {
                    if ($ing->inventory_item_id && $ing->quantity_per_guest > 0) {
                        $this->addExpected($out, $ing->inventory_item_id, $ing->quantity_per_guest * $people, 'standard', "{$recipe->name} standard × {$people}");
                    }
                }

                continue;
            }
            foreach ($this->history($p['name'])['rows'] as $row) {
                if ($row['times'] >= 3 && $row['median'] > 0) {
                    $this->addExpected($out, $row['item_id'], $row['median'] * $people, 'history', "usual for {$p['name']} × {$people}");
                }
            }
        }

        return $out;
    }

    private function addExpected(array &$out, int $itemId, float $qty, string $source, string $label): void
    {
        $out[$itemId] ??= ['qty' => 0.0, 'source' => $source, 'label' => $label];
        $out[$itemId]['qty'] = round($out[$itemId]['qty'] + $qty, 3);
        if ($out[$itemId]['label'] !== $label) {
            $out[$itemId]['label'] = 'several dishes';
        }
    }

    public function currentFor(string $name): ?Recipe
    {
        return Recipe::with('ingredients')->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
            ->whereNull('superseded_at')->whereHas('ingredients', fn ($q) => $q->whereNotNull('quantity_per_guest'))
            ->orderByDesc('version')->first();
    }

    /**
     * Save per-guest standards as a new recipe version.
     * $lines: [inventory_item_id => quantity_per_guest (stock unit)]; empty/0 = not part of the standard.
     */
    public function saveStandard(string $preparation, array $lines, User $admin, ?string $notes = null): Recipe
    {
        $lines = array_filter(array_map(fn ($q) => round((float) $q, 5), $lines), fn ($q) => $q > 0);
        if (! $lines) {
            throw ValidationException::withMessages(['lines' => 'Enter at least one per-person quantity.']);
        }
        $items = InventoryItem::whereIn('id', array_keys($lines))->get()->keyBy('id');

        return DB::transaction(function () use ($preparation, $lines, $admin, $notes, $items) {
            $prev = Recipe::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($preparation))])
                ->whereNull('superseded_at')->orderByDesc('version')->lockForUpdate()->first();
            $root = $prev ? ($prev->parent_recipe_id ?: $prev->id) : null;
            $recipe = Recipe::create([
                'name' => $prev?->name ?? trim($preparation),
                'category' => $prev?->category,
                'description' => $notes ?? $prev?->description,
                'yield_per_batch' => 1,
                'yield_unit' => 'person',
                'is_active' => true,
                'created_by' => $admin->id,
                'version' => ($prev?->version ?? 0) + 1,
                'parent_recipe_id' => $root,
            ]);
            $i = 0;
            foreach ($lines as $itemId => $q) {
                $item = $items[$itemId] ?? null;
                if (! $item) {
                    continue;
                }
                $recipe->ingredients()->create([
                    'inventory_item_id' => $item->id, 'ingredient_name' => $item->name,
                    'quantity_per_batch' => $q, 'unit' => $item->unit, 'quantity_per_guest' => $q, 'sort_order' => $i++,
                ]);
            }
            if ($prev) {
                $prev->update(['superseded_at' => now(), 'is_active' => false]);
            }
            $this->audit->log('standard_saved', 'recipe', $recipe->id, "{$recipe->name} v{$recipe->version}",
                $prev ? ['version' => $prev->version] : [], ['version' => $recipe->version, 'per_guest' => $lines]);

            return $recipe->load('ingredients');
        });
    }

    /** Suggested request lines for $guests people from a standard recipe (null when none). */
    public function suggest(int $recipeId, int $guests): ?array
    {
        $recipe = Recipe::with('ingredients.inventoryItem')->find($recipeId);
        if (! $recipe || $guests <= 0) {
            return null;
        }
        $lines = [];
        foreach ($recipe->ingredients as $ing) {
            if (! $ing->inventory_item_id || ! $ing->quantity_per_guest) {
                continue;
            }
            $lines[] = [
                'inventory_item_id' => $ing->inventory_item_id,
                'quantity' => round((float) $ing->quantity_per_guest * $guests, 3),
                'unit' => $ing->inventoryItem?->unit,
                'suggested' => round((float) $ing->quantity_per_guest * $guests, 3),
            ];
        }

        return $lines ? ['recipe' => $recipe->name, 'recipe_id' => $recipe->id, 'version' => $recipe->version, 'guests' => $guests, 'lines' => $lines] : null;
    }
}
