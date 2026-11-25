<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryItemAlias;
use App\Support\ItemText;
use Illuminate\Support\Facades\Cache;

/**
 * The ONE item search used by every item selector (purchase grid, material request grid,
 * invoice review, admin catalogue, duplicate checks). English, Tamil and Tanglish.
 *
 * Ranking (score):
 *   100 exact English name · 96 exact Tamil name · 92 exact alias
 *    80 whole-word match · 75 prefix · 60 partial (contains)
 *    50/45 fuzzy — only when nothing above matched, and never auto-selected.
 *
 * search() never picks for the caller: `exact_id` is set only when exactly ONE item matched
 * at an exact tier (>= 90). Ambiguous words such as "dal" return a list.
 *
 * The term index (names, Tamil names, generic aliases) is cached and versioned; models flush it.
 */
class ItemSearchService
{
    public const EXACT_MIN = 90;

    private const VERSION_KEY = 'inventory:item-search:version';

    private static array $memo = [];

    public static function flush(): void
    {
        $v = (int) Cache::get(self::VERSION_KEY, 0) + 1;
        Cache::forever(self::VERSION_KEY, $v);
        self::$memo = [];
    }

    /**
     * @return array{query:string, results:array<int,array>, exact_id:?int, ambiguous:bool}
     */
    public function search(string $query, int $limit = 12, bool $includeInactive = false): array
    {
        $scores = $this->score($query, $includeInactive);
        $index = $this->index();
        uasort($scores, fn ($a, $b) => $b['score'] <=> $a['score']
            ?: mb_strlen($index['items'][$a['id']]['name']) <=> mb_strlen($index['items'][$b['id']]['name'])
            ?: strcmp($index['items'][$a['id']]['name'], $index['items'][$b['id']]['name']));

        $exact = array_filter($scores, fn ($s) => $s['score'] >= self::EXACT_MIN);
        $results = [];
        foreach (array_slice($scores, 0, $limit, true) as $s) {
            $it = $index['items'][$s['id']];
            $results[] = $it + [
                'score' => $s['score'],
                'match' => $s['label'],
                'matched' => $s['kind'] === 'name' ? null : $s['display'],
            ];
        }

        return [
            'query' => $query,
            'results' => $results,
            'exact_id' => count($exact) === 1 ? (int) array_key_first($exact) : null,
            'ambiguous' => count($exact) > 1 || (! $exact && count($scores) > 1),
        ];
    }

    /** Every matching item id at partial-or-better relevance (admin list filtering). */
    public function matchingIds(string $query, bool $includeInactive = true): array
    {
        return array_keys(array_filter($this->score($query, $includeInactive), fn ($s) => $s['score'] >= 60));
    }

    /** Items whose name / Tamil name / alias equals $name, or differs only by a typo (duplicate warning). */
    public function similarTo(string $name, ?int $exceptId = null): array
    {
        $out = [];
        foreach ($this->search($name, 8, true)['results'] as $r) {
            if ($r['id'] !== $exceptId && ($r['score'] >= self::EXACT_MIN || $r['match'] === 'Similar spelling')) {
                $out[] = $r;
            }
        }

        return $out;
    }

    /** @return array<int, array{id:int, score:int, kind:string, label:string, display:string}> */
    private function score(string $query, bool $includeInactive): array
    {
        $qk = ItemText::phonetic($query);
        if ($qk === '') {
            return [];
        }
        $qc = ItemText::compact($qk);
        $qWords = explode(' ', $qk);
        $index = $this->index();
        $best = [];
        $put = function (array $term, int $score, string $label) use (&$best) {
            $id = $term['id'];
            if (! isset($best[$id]) || $best[$id]['score'] < $score) {
                $best[$id] = ['id' => $id, 'score' => $score, 'kind' => $term['kind'], 'label' => $label, 'display' => $term['display']];
            }
        };
        $exactScore = ['name' => 100, 'ta' => 96, 'alias' => 92];
        $exactLabel = ['name' => 'Name', 'ta' => 'Tamil name', 'alias' => 'Also called'];

        foreach ($index['terms'] as $t) {
            if (! $includeInactive && $index['items'][$t['id']]['status'] !== 'active') {
                continue;
            }
            if ($t['key'] === $qk || $t['compact'] === $qc) {
                $put($t, $exactScore[$t['kind']], $exactLabel[$t['kind']]);

                continue;
            }
            $whole = true;
            $prefix = true;
            foreach ($qWords as $qw) {
                $hitWhole = in_array($qw, $t['words'], true);
                $hitPrefix = $hitWhole;
                if (! $hitPrefix) {
                    foreach ($t['words'] as $w) {
                        if (str_starts_with($w, $qw)) {
                            $hitPrefix = true;
                            break;
                        }
                    }
                }
                $whole = $whole && $hitWhole;
                $prefix = $prefix && $hitPrefix;
            }
            if ($whole) {
                $put($t, 80, $t['kind'] === 'name' ? 'Name' : $exactLabel[$t['kind']]);
            } elseif ($prefix || str_starts_with($t['compact'], $qc)) {
                $put($t, 75, 'Starts with');
            } elseif (mb_strlen($qc) >= 3 && str_contains($t['compact'], $qc)) {
                $put($t, 60, 'Contains');
            }
        }

        if ($best) {
            return $best;
        }

        // Fuzzy fallback (typos, missing vowels: parpu ~ paruppu). Only Latin words of 4+ letters are
        // fuzzed; shorter or Tamil words must still match exactly; at least one word must be fuzzable.
        if (! array_filter($qWords, fn ($w) => ItemText::isLatin($w) && strlen($w) >= 4)) {
            return [];
        }
        foreach ($index['terms'] as $t) {
            if (! $includeInactive && $index['items'][$t['id']]['status'] !== 'active') {
                continue;
            }
            $score = 50;
            foreach ($qWords as $qw) {
                $wordScore = in_array($qw, $t['words'], true) ? 50 : 0;
                if (! $wordScore && ItemText::isLatin($qw) && strlen($qw) >= 4) {
                    $sk = ItemText::skeleton($qw);
                    $allowed = strlen($qw) <= 6 ? 1 : 2;
                    foreach ($t['words'] as $i => $w) {
                        if (strlen($sk) >= 3 && $t['skel'][$i] === $sk) {
                            $wordScore = 50;
                            break;
                        }
                        if (ItemText::isLatin($w) && abs(strlen($w) - strlen($qw)) <= $allowed && levenshtein($qw, $w) <= $allowed) {
                            $wordScore = 45;
                        }
                    }
                }
                $score = min($score, $wordScore);
            }
            if ($score > 0) {
                $put($t, $score, 'Similar spelling');
            }
        }

        return $best;
    }

    /** @return array{items: array<int,array>, terms: array<int,array>} */
    public function index(): array
    {
        $version = (int) Cache::get(self::VERSION_KEY, 0);

        return self::$memo[$version] ??= Cache::remember('inventory:item-search:index:'.$version, now()->addDay(), fn () => $this->build());
    }

    private function build(): array
    {
        $items = [];
        $terms = [];
        $add = function (int $id, string $kind, string $display) use (&$terms) {
            $key = ItemText::phonetic($display);
            if ($key === '') {
                return;
            }
            $words = explode(' ', $key);
            $terms[] = [
                'id' => $id, 'kind' => $kind, 'display' => $display, 'key' => $key,
                'compact' => ItemText::compact($key), 'words' => $words,
                'skel' => array_map([ItemText::class, 'skeleton'], $words),
            ];
        };

        InventoryItem::query()->leftJoin('inventory_categories as c', 'c.id', '=', 'inventory_items.inventory_category_id')
            ->orderBy('inventory_items.id')
            ->get(['inventory_items.id', 'inventory_items.name', 'inventory_items.name_ta', 'inventory_items.unit', 'inventory_items.status', 'c.name as category'])
            ->each(function ($i) use (&$items, $add) {
                $items[$i->id] = ['id' => $i->id, 'name' => $i->name, 'name_ta' => $i->name_ta, 'category' => $i->category, 'unit' => $i->unit, 'status' => $i->status];
                $add($i->id, 'name', $i->name);
                if ($i->name_ta) {
                    $add($i->id, 'ta', $i->name_ta);
                }
            });
        InventoryItemAlias::whereNull('vendor_id')->orderBy('id')->get(['inventory_item_id', 'alias'])
            ->each(function ($a) use ($items, $add) {
                if (isset($items[$a->inventory_item_id])) {
                    $add($a->inventory_item_id, 'alias', $a->alias);
                }
            });

        return ['items' => $items, 'terms' => $terms];
    }
}
