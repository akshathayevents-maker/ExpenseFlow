<?php

namespace App\Services\Recipes;

use App\Models\Recipe;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Imports the hand-curated bilingual starter recipes (database/data/starter_recipes.php + starter_recipes_ta.php +
 * database/data/recipes/*.php) — no AI involved. Safe to re-run:
 *  - new dishes are saved inactive as "Curated starter — not verified";
 *  - only curated (source=starter) recipes are ever updated; manual/AI recipes with the same name are left alone;
 *  - a dish that already exists keeps its id, English text, quantities and verification state; only EMPTY Tamil
 *    fields are filled (name, notes, ingredient names, and step Tamil when the steps still match the curated English);
 *  - verified recipes get no step changes (that would need re-verification) — they are reported instead.
 * Never touches stock or material requests.
 */
class StarterRecipeImporter
{
    public function __construct(private RecipeOutputValidator $validator, private RecipeLibraryService $library, private AuditLogService $audit) {}

    /** @return list<array> curated recipes in RecipeOutputValidator input shape (English + Tamil) */
    public function recipes(): array
    {
        $base = require database_path('data/starter_recipes.php');
        $ta = require database_path('data/starter_recipes_ta.php');
        foreach ($base as &$r) {
            $t = $ta[$r['name']] ?? null;
            if (! $t) {
                continue;
            }
            $r['name_ta'] = $t['name_ta'];
            $r['kitchen_notes'] = $t['notes'] ?? null;
            $r['kitchen_notes_ta'] = $t['notes_ta'] ?? null;
            foreach ($r['steps'] as $n => &$step) {
                if (isset($t['steps_ta'][$n])) {
                    [$step['title_ta'], $step['instruction_ta']] = $t['steps_ta'][$n];
                }
            }
            unset($step);
        }
        unset($r);
        foreach (glob(database_path('data/recipes/*.php')) ?: [] as $file) {
            $base = array_merge($base, require $file);
        }

        return $base;
    }

    /**
     * @return array{created: list<string>, skipped: list<string>, tamil_updated: list<string>, similar: list<string>,
     *               verified_needs_manual_tamil: list<string>, failed: array<string, string>}
     */
    public function import(User $owner): array
    {
        $out = ['created' => [], 'skipped' => [], 'tamil_updated' => [], 'similar' => [], 'verified_needs_manual_tamil' => [], 'failed' => []];
        foreach ($this->recipes() as $raw) {
            $name = $raw['name'] ?? '?';
            try {
                $r = $this->validator->validate($raw, ['food_type' => $raw['food_type'] ?? null]);
                $recipe = $this->library->createDraft($r, $owner, 'starter', null, ['source' => 'curated starter set']);
                $out['created'][] = $name;
                if ($recipe->duplicate_of_id) {
                    $out['similar'][] = $name;
                }
            } catch (DuplicateRecipe $e) {
                $result = isset($r) ? $this->fillMissingTamil($e->existing, $r) : 'none';
                match ($result) {
                    'updated' => $out['tamil_updated'][] = $name,
                    'verified' => $out['verified_needs_manual_tamil'][] = $name,
                    default => $out['skipped'][] = $name,
                };
            } catch (Throwable $e) {
                $out['failed'][$name] = $e->getMessage();
            }
            unset($r);
        }
        if (auth()->check()) {
            $this->audit->log('imported', 'recipe_starter_set', 0, 'Starter recipes', [], array_map(fn ($v) => count($v), $out));
        }

        return $out;
    }

    /** Fill only empty Tamil fields of an existing recipe. Returns updated | verified | none. */
    public function fillMissingTamil(Recipe $existing, array $r): string
    {
        $recipe = Recipe::with(['ingredients', 'sops'])->find($existing->id);
        // manual / AI recipes with the same name are someone else's recipe: never touched
        if ($recipe->source !== 'starter') {
            return 'none';
        }
        $changed = false;
        $stepGap = false;

        DB::transaction(function () use ($recipe, $r, &$changed, &$stepGap) {
            $fill = [];
            foreach (['name_ta', 'kitchen_notes', 'kitchen_notes_ta'] as $f) {
                if (blank($recipe->{$f}) && ! blank($r[$f] ?? null)) {
                    $fill[$f] = $r[$f];
                }
            }
            if ($fill) {
                $recipe->update($fill);
                $changed = true;
            }
            // ingredient Tamil display names (display only — never changes stock mapping)
            $byName = collect($r['ingredients'])->keyBy(fn ($i) => mb_strtolower($i['ingredient_name']));
            foreach ($recipe->ingredients as $ing) {
                if (blank($ing->ingredient_name_ta)) {
                    $ta = $byName[mb_strtolower($ing->ingredient_name)]['ingredient_name_ta'] ?? null
                        ?: RecipeLibraryService::tamilIngredient($ing->ingredient_name);
                    if ($ta) {
                        $ing->update(['ingredient_name_ta' => $ta]);
                        $changed = true;
                    }
                }
            }
            // step Tamil: only when the English steps are still the curated ones (same count and text)
            $same = $recipe->sops->count() === count($r['steps'])
                && $recipe->sops->values()->every(fn ($s, $n) => trim($s->instruction) === trim($r['steps'][$n]['instruction']));
            $missing = $recipe->sops->filter(fn ($s) => blank($s->instruction_ta));
            if ($missing->isNotEmpty()) {
                if (! $same || $recipe->isVerified()) {
                    $stepGap = true;

                    return;
                }
                foreach ($recipe->sops->values() as $n => $sop) {
                    $src = $r['steps'][$n];
                    $sop->update(array_filter([
                        'title_ta' => blank($sop->title_ta) ? ($src['title_ta'] ?? null) : null,
                        'instruction_ta' => blank($sop->instruction_ta) ? ($src['instruction_ta'] ?? null) : null,
                        'note_ta' => blank($sop->note_ta) ? ($src['note_ta'] ?? null) : null,
                    ]));
                }
                $changed = true;
            }
        });

        return $stepGap && $recipe->isVerified() ? 'verified' : ($changed ? 'updated' : 'none');
    }

    /** dishes in the set that are not in the library yet */
    public function missingCount(): int
    {
        return collect($this->recipes())->reject(fn ($r) => $this->library->findDuplicate($r['name'])['exact'])->count();
    }
}
