<?php

namespace App\Services\Recipes;

use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\RecipeGenerationItem;
use App\Models\RecipeIngredient;
use App\Models\RecipeSop;
use App\Models\RecipeVersion;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Inventory\ItemMatchingService;
use App\Support\ItemText;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Recipe review lifecycle: AI generated → needs review → reviewed → verified (→ archived).
 * Verification only by an explicit admin action; material edits to a verified recipe keep the old version
 * in recipe_versions and send the recipe back to review. Never touches stock.
 */
class RecipeLibraryService
{
    public function __construct(private ItemMatchingService $matcher, private AuditLogService $audit) {}

    // ── names & duplicates ──────────────────────────────────────────────────

    /** Spelling-insensitive key: "Rava Upma (special)" ≈ "rava uppuma"; trailing version numbers ignored. */
    public static function nameKey(string $name): string
    {
        // a single trailing digit is a version ("Idly 1"); a dish number ("Chicken 65") is part of the name
        $name = preg_replace('/\(.*?\)|\bv(?:er(?:sion)?)?\s*\d+\b|\s+\d$/iu', ' ', $name);
        preg_match_all('/\b\d{2,}\b/u', $name, $nums);

        return trim(ItemText::phonetic($name).' '.implode(' ', $nums[0]));
    }

    /**
     * @return array{exact: ?Recipe, similar: ?Recipe} exact = same dish (skip), similar = probable duplicate (flag)
     */
    public function findDuplicate(string $name, ?int $exceptId = null): array
    {
        $key = self::nameKey($name);
        if ($key === '') {
            return ['exact' => null, 'similar' => null];
        }
        $candidates = Recipe::where('verification_status', '!=', 'archived')->whereNull('superseded_at')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->get(['id', 'name']);
        $exact = null;
        $similar = null;
        $best = 0;
        foreach ($candidates as $r) {
            $k = self::nameKey($r->name);
            if ($k === '') {
                continue;
            }
            if ($k === $key) {
                $exact = $r;
                break;
            }
            // "Upma" vs "Rava Upma" / "Vegetable Upma": whole-word containment — but a lone base word
            // ("Chicken", "Paneer") is not a dish of its own, so "Chicken Fry" is not a duplicate of "Chicken 65"
            $score = 0;
            $shorter = mb_strlen($k) <= mb_strlen($key) ? $k : $key;
            $generic = ! str_contains($shorter, ' ') && in_array($shorter, self::baseWords(), true);
            if (! $generic && mb_strlen($k) >= 3 && (str_contains(" {$key} ", " {$k} ") || str_contains(" {$k} ", " {$key} "))) {
                $score = 80;
            } else {
                similar_text($key, $k, $pct);
                $score = $pct >= 88 ? (int) $pct : 0;
            }
            if ($score > $best) {
                [$best, $similar] = [$score, $r];
            }
        }

        return ['exact' => $exact, 'similar' => $exact ? null : $similar];
    }

    /** single words that name an ingredient, not a dish (phonetic keys) */
    private static function baseWords(): array
    {
        static $w = null;

        return $w ??= array_map(fn ($x) => trim(ItemText::phonetic($x)), ['chicken', 'mutton', 'fish', 'egg', 'paneer', 'gobi', 'mushroom',
            'potato', 'aloo', 'vegetable', 'veg', 'rice', 'dal', 'prawn', 'baby corn', 'corn', 'sambar', 'masala', 'curry', 'fry', 'gravy']);
    }

    /** @return list<string> existing recipe names (for the AI planner's exclusion list) */
    public function existingNames(): array
    {
        return Recipe::where('verification_status', '!=', 'archived')->whereNull('superseded_at')->orderBy('name')->pluck('name')->all();
    }

    // ── create from AI ──────────────────────────────────────────────────────

    /** Saves a validated AI recipe as an inactive, unverified recipe with ingredients mapped to stock items where confident. */
    public function createFromAi(RecipeGenerationItem $item, array $r, array $meta, User $owner): Recipe
    {
        return $this->createDraft($r, $owner, 'ai', $item, $meta);
    }

    /**
     * Saves a validated recipe draft (source ai|starter) — inactive and unverified until an admin verifies it.
     *
     * @throws DuplicateRecipe when the same dish already exists
     */
    public function createDraft(array $r, User $owner, string $source, ?RecipeGenerationItem $item = null, ?array $meta = null): Recipe
    {
        return DB::transaction(function () use ($item, $r, $meta, $owner, $source) {
            $dup = $this->findDuplicate($r['name']);
            if ($dup['exact']) {
                throw new DuplicateRecipe($dup['exact']);
            }
            $unmapped = 0;
            $recipe = Recipe::create([
                'name' => $r['name'], 'name_ta' => $r['name_ta'] ?? null, 'category' => $r['category'],
                'kitchen_notes' => $r['kitchen_notes'] ?? null, 'kitchen_notes_ta' => $r['kitchen_notes_ta'] ?? null, 'cuisine' => $r['cuisine'], 'food_type' => $r['food_type'],
                'description' => $r['description'], 'yield_per_batch' => $r['yield_per_batch'], 'yield_unit' => $r['yield_unit'],
                'prep_time_minutes' => $r['prep_time_minutes'], 'cook_time_minutes' => $r['cook_time_minutes'],
                'is_active' => false,                       // hidden from kitchen staff until verified
                'created_by' => $owner->id, 'source' => $source, 'revision' => 1,
                'verification_status' => $source === 'ai' ? 'ai_generated' : 'needs_review',
                'generation_item_id' => $item?->id,
                'duplicate_of_id' => $dup['similar']?->id,
                'ai_meta' => $meta,
            ]);
            foreach ($r['ingredients'] as $n => $ing) {
                $match = $this->matcher->best($ing['ingredient_name']);
                $ok = $this->matcher->isAutoAcceptable($match);
                $needs = ! $ok && ! $this->isWater($ing['ingredient_name']);
                $unmapped += $needs ? 1 : 0;
                $ing['ingredient_name_ta'] = ($ing['ingredient_name_ta'] ?? null) ?: self::tamilIngredient($ing['ingredient_name'])
                    ?: ($ok ? InventoryItem::whereKey($match['item_id'])->value('name_ta') : null);
                RecipeIngredient::create($ing + [
                    'recipe_id' => $recipe->id, 'sort_order' => $n,
                    'inventory_item_id' => $ok ? $match['item_id'] : null,
                    'match_confidence' => $match['confidence'] ?? null,
                    'needs_mapping' => $needs,
                ]);
            }
            foreach ($r['steps'] as $n => $s) {
                RecipeSop::create($s + ['recipe_id' => $recipe->id, 'step_number' => $n + 1]);
            }
            if ($dup['similar'] || $unmapped > 0 || ! empty($r['missing_tamil_steps']) || empty($r['name_ta'])) {
                $recipe->update(['verification_status' => 'needs_review']);
            }

            return $recipe->fresh(['ingredients', 'sops']);
        });
    }

    /** Tamil display name for a common ingredient (database/data/ingredient_tamil.php), or null. */
    public static function tamilIngredient(string $english): ?string
    {
        static $dict = null;
        $dict ??= require database_path('data/ingredient_tamil.php');

        return $dict[mb_strtolower(trim($english))] ?? null;
    }

    private function isWater(string $name): bool
    {
        return in_array(mb_strtolower(trim($name)), ['water', 'hot water', 'cold water', 'warm water'], true);
    }

    // ── review workflow ─────────────────────────────────────────────────────

    public function markReviewed(Recipe $recipe, User $user): Recipe
    {
        if (in_array($recipe->verification_status, ['verified', 'archived'], true)) {
            throw new RuntimeException('Only recipes waiting for review can be marked reviewed.');
        }
        $recipe->update(['verification_status' => 'reviewed', 'reviewed_by' => $user->id, 'reviewed_at' => now()]);
        $this->audit->log('reviewed', 'recipe', $recipe->id, $recipe->name);

        return $recipe;
    }

    /** Explicit admin verification: snapshot this revision and make the recipe available to the kitchen. */
    public function verify(Recipe $recipe, User $admin, ?string $notes = null, bool $allowMissingTamil = false): Recipe
    {
        if ($recipe->verification_status === 'archived') {
            throw new RuntimeException('Restore the recipe before verifying it.');
        }
        $recipe->loadMissing(['ingredients', 'sops']);
        if ($recipe->ingredients->isEmpty() || $recipe->sops->isEmpty()) {
            throw new RuntimeException('A recipe needs ingredients and preparation steps before it can be verified.');
        }
        $gaps = $recipe->languageGaps();
        if ($gaps['steps_without_english']) {
            throw new RuntimeException('Steps '.implode(', ', $gaps['steps_without_english']).' have no English instruction.');
        }
        if (($gaps['steps_without_tamil'] || $gaps['name_ta']) && ! $allowMissingTamil) {
            throw new RuntimeException('Tamil is missing'.($gaps['name_ta'] ? ' for the recipe name' : '')
                .($gaps['steps_without_tamil'] ? ($gaps['name_ta'] ? ' and' : '').' for steps '.implode(', ', $gaps['steps_without_tamil']) : '')
                .'. Add it, or tick "verify with English only".');
        }

        return DB::transaction(function () use ($recipe, $admin, $notes, $gaps) {
            $recipe->update([
                'verification_status' => 'verified', 'verified_by' => $admin->id, 'verified_at' => now(),
                'verification_notes' => $notes, 'is_active' => true,
                'reviewed_by' => $recipe->reviewed_by ?? $admin->id, 'reviewed_at' => $recipe->reviewed_at ?? now(),
            ]);
            RecipeVersion::create([
                'recipe_id' => $recipe->id, 'revision' => $recipe->revision, 'event' => 'verified',
                'snapshot' => $recipe->fresh(['ingredients', 'sops'])->materialSnapshot(),
                'change_summary' => trim(($notes ?? '').(($gaps['steps_without_tamil'] || $gaps['name_ta']) ? ' [verified without complete Tamil]' : '')) ?: null, 'verified_by' => $admin->id, 'verified_at' => now(),
                'changed_by' => $admin->id, 'created_at' => now(),
            ]);
            $this->audit->log('verified', 'recipe', $recipe->id, $recipe->name, [], ['revision' => $recipe->revision]);

            return $recipe;
        });
    }

    public function archive(Recipe $recipe, User $user): Recipe
    {
        $recipe->update(['verification_status' => 'archived', 'is_active' => false]);
        $this->audit->log('archived', 'recipe', $recipe->id, $recipe->name);

        return $recipe;
    }

    public function restore(Recipe $recipe, User $user): Recipe
    {
        $recipe->update(['verification_status' => 'needs_review']);
        $this->audit->log('restored', 'recipe', $recipe->id, $recipe->name);

        return $recipe;
    }

    /**
     * Call after a recipe was edited. If the way the dish is made changed (yield, ingredients, quantities, units,
     * mapping, steps), a verified recipe loses its verification: the previous verified version is kept as a
     * snapshot, the revision number goes up and the recipe goes back to "Needs review".
     */
    public function afterEdit(Recipe $recipe, array $before, User $user): bool
    {
        $after = $recipe->fresh(['ingredients', 'sops'])->materialSnapshot();
        if (Recipe::materialPart($before) === Recipe::materialPart($after)) {
            return false;
        }
        $wasVerified = $recipe->verification_status === 'verified';
        DB::transaction(function () use ($recipe, $before, $user, $wasVerified) {
            RecipeVersion::create([
                'recipe_id' => $recipe->id, 'revision' => $recipe->revision, 'event' => 'superseded', 'snapshot' => $before,
                'change_summary' => $wasVerified ? 'Verified version replaced by an edit' : 'Edited before verification',
                'verified_by' => $wasVerified ? $recipe->verified_by : null, 'verified_at' => $wasVerified ? $recipe->verified_at : null,
                'changed_by' => $user->id, 'created_at' => now(),
            ]);
            $recipe->update([
                'revision' => $recipe->revision + 1,
                'verification_status' => in_array($recipe->verification_status, ['unverified', 'archived'], true) ? $recipe->verification_status : 'needs_review',
                'verified_by' => null, 'verified_at' => null,
            ]);
            if ($wasVerified) {
                $this->audit->log('verification_reset', 'recipe', $recipe->id, $recipe->name, [], ['revision' => $recipe->revision]);
            }
        });

        return $wasVerified;
    }

    /** Admin maps an AI ingredient to a stock item (remembered as an alias for future matching). */
    public function mapIngredient(RecipeIngredient $ing, ?InventoryItem $item, User $user): void
    {
        $before = $ing->recipe->materialSnapshot();
        $ing->update(['inventory_item_id' => $item?->id, 'needs_mapping' => $item === null, 'match_confidence' => $item ? 100 : null]);
        if ($item && mb_strtolower(trim($ing->ingredient_name)) !== mb_strtolower($item->name)) {
            $this->matcher->remember($item, $ing->ingredient_name, null, $user);
        }
        $this->afterEdit($ing->recipe, $before, $user);
    }
}
