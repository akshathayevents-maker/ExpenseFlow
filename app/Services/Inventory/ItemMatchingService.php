<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryItemAlias;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Matches free text (invoice lines, pasted rows) to inventory items.
 * Order: exact name -> supplier alias -> confirmed alias -> normalized name ->
 * fuzzy (token containment + similarity). Returns ranked suggestions with a
 * 0-100 confidence; callers must not auto-accept below
 * config('inventory.match_auto_select_confidence').
 */
class ItemMatchingService
{
    private ?Collection $items = null;

    private ?Collection $aliases = null;

    public function __construct(private UnitConversionService $units) {}

    public function normalize(string $text): string
    {
        $t = mb_strtolower($text);
        $unitWords = implode('|', array_map('preg_quote', array_merge(
            array_keys(config('inventory.units')),
            array_keys(config('inventory.unit_aliases'))
        )));
        // drop pack sizes such as "25kg", "1 l", "500 gm", "x 2"
        $t = preg_replace('/\b\d+(?:[.,]\d+)?\s*(?:'.$unitWords.')\b/u', ' ', $t);
        $t = preg_replace('/[^\p{L}\s]+/u', ' ', $t);
        $t = preg_replace('/\s+/u', ' ', $t);

        return trim($t);
    }

    /** @return array<int, array{item_id:int, name:string, unit:string, confidence:int, method:string}> */
    public function suggest(string $text, ?int $vendorId = null, int $limit = 3): array
    {
        $raw = mb_strtolower(trim($text));
        $norm = $this->normalize($text);
        if ($raw === '') {
            return [];
        }
        $scores = [];
        $put = function (int $itemId, int $score, string $method) use (&$scores) {
            if (! isset($scores[$itemId]) || $scores[$itemId]['confidence'] < $score) {
                $scores[$itemId] = ['confidence' => $score, 'method' => $method];
            }
        };

        foreach ($this->items() as $item) {
            $name = mb_strtolower($item->name);
            if ($name === $raw) {
                $put($item->id, 100, 'exact');

                continue;
            }
            $itemNorm = $this->normalize($item->name);
            if ($itemNorm !== '' && $itemNorm === $norm) {
                $put($item->id, 95, 'normalized');

                continue;
            }
            $put($item->id, $this->fuzzy($norm, $itemNorm), 'fuzzy');
        }

        foreach ($this->aliases() as $alias) {
            if ($alias->normalized_alias === $norm && $norm !== '') {
                if ($alias->vendor_id && $alias->vendor_id === $vendorId) {
                    $put($alias->inventory_item_id, 98, 'supplier_alias');
                } elseif (! $alias->vendor_id) {
                    $put($alias->inventory_item_id, $alias->source === 'confirmed' ? 96 : 94, 'alias');
                } else {
                    $put($alias->inventory_item_id, 88, 'alias');   // other supplier's wording
                }
            } elseif ($norm !== '') {
                $put($alias->inventory_item_id, max(0, $this->fuzzy($norm, $alias->normalized_alias) - 2), 'fuzzy');
            }
        }

        $byId = $this->items()->keyBy('id');
        $out = [];
        foreach ($scores as $id => $s) {
            if ($s['confidence'] < 40 || ! $byId->has($id)) {
                continue;
            }
            $item = $byId[$id];
            $out[] = ['item_id' => $id, 'name' => $item->name, 'unit' => $item->unit] + $s;
        }
        usort($out, fn ($a, $b) => $b['confidence'] <=> $a['confidence'] ?: strcmp($a['name'], $b['name']));

        return array_slice($out, 0, $limit);
    }

    public function best(string $text, ?int $vendorId = null): ?array
    {
        return $this->suggest($text, $vendorId, 1)[0] ?? null;
    }

    public function isAutoAcceptable(?array $match): bool
    {
        return $match !== null && $match['confidence'] >= (int) config('inventory.match_auto_select_confidence', 85);
    }

    /** Remember a user-confirmed mapping (idempotent; bumps confirmations). */
    public function remember(InventoryItem $item, string $text, ?int $vendorId, User $user, ?string $unitHint = null): ?InventoryItemAlias
    {
        $norm = $this->normalize($text);
        if ($norm === '' || $norm === $this->normalize($item->name) && $vendorId === null) {
            return null; // the item name itself needs no alias
        }
        $alias = InventoryItemAlias::firstOrNew([
            'normalized_alias' => $norm,
            'vendor_id' => $vendorId,
            'inventory_item_id' => $item->id,
        ]);
        $alias->alias = $alias->alias ?: mb_substr(trim($text), 0, 200);
        $alias->source = 'confirmed';
        $alias->unit_hint = $this->units->normalize($unitHint) ?? $alias->unit_hint;
        $alias->confirmations = ($alias->confirmations ?? 0) + 1;
        $alias->created_by = $alias->created_by ?? $user->id;
        $alias->save();
        $this->aliases = null;

        return $alias;
    }

    private function fuzzy(string $a, string $b): int
    {
        if ($a === '' || $b === '') {
            return 0;
        }
        $ta = array_values(array_filter(explode(' ', $a)));
        $tb = array_values(array_filter(explode(' ', $b)));

        // every word of the item name appears in the text ("sona masoori rice" ⊇ "rice")
        $contained = count(array_diff($tb, $ta)) === 0;
        $containScore = $contained ? (int) round(70 + 25 * mb_strlen($b) / max(mb_strlen($a), 1)) : 0;

        similar_text($a, $b, $pct);
        $common = count(array_intersect($ta, $tb));
        $jaccard = $common / max(count(array_unique(array_merge($ta, $tb))), 1);
        $lev = 1 - levenshtein(mb_substr($a, 0, 255), mb_substr($b, 0, 255)) / max(strlen($a), strlen($b), 1);

        $fuzzy = (int) round(max($pct / 100, $jaccard, $lev) * 90);

        return min(94, max($containScore, $fuzzy));
    }

    private function items(): Collection
    {
        return $this->items ??= InventoryItem::where('status', 'active')->get(['id', 'name', 'unit']);
    }

    private function aliases(): Collection
    {
        return $this->aliases ??= InventoryItemAlias::get(['inventory_item_id', 'normalized_alias', 'vendor_id', 'source']);
    }
}
