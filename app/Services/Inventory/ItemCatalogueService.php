<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryItemAlias;
use App\Support\ItemText;
use Illuminate\Support\Collection;

/**
 * Catalogue rules shared by the admin screens and the catalogue seeder.
 * A generic search alias belongs to exactly one item, and may not equal another
 * item's English or Tamil name (that would hijack searches for the other item).
 */
class ItemCatalogueService
{
    /**
     * @return array{status:'created'|'exists'|'redundant'|'conflict'|'empty', alias:?InventoryItemAlias, other:?InventoryItem}
     */
    public function addAlias(InventoryItem $item, string $alias, ?string $language = null, ?int $userId = null): array
    {
        $alias = trim(preg_replace('/\s+/u', ' ', $alias));
        $norm = ItemText::normalize($alias);
        $key = ItemText::phonetic($alias);
        $out = fn ($status, $a = null, $o = null) => ['status' => $status, 'alias' => $a, 'other' => $o];
        if ($norm === '') {
            return $out('empty');
        }

        $existing = InventoryItemAlias::whereNull('vendor_id')->where('normalized_alias', $norm)->first();
        if ($existing) {
            return $existing->inventory_item_id === $item->id
                ? $out('exists', $existing)
                : $out('conflict', $existing, InventoryItem::find($existing->inventory_item_id));
        }
        // Same spelling key as this item's own names: search already finds it.
        if ($key === ItemText::phonetic($item->name) || ($item->name_ta && $norm === ItemText::normalize($item->name_ta))) {
            return $out('redundant');
        }
        $other = $this->itemNamed($alias, $item->id);
        if ($other) {
            return $out('conflict', null, $other);
        }
        $otherAlias = InventoryItemAlias::whereNull('vendor_id')->where('phonetic_key', $key)
            ->where('inventory_item_id', '!=', $item->id)->first();
        if ($otherAlias) {
            return $out('conflict', $otherAlias, InventoryItem::find($otherAlias->inventory_item_id));
        }

        $language ??= ItemText::hasTamil($alias) ? 'ta' : 'en';
        $created = InventoryItemAlias::create([
            'inventory_item_id' => $item->id,
            'alias' => mb_substr($alias, 0, 200),
            'language' => $language,
            'alias_type' => match ($language) {
                'ta' => 'tamil', 'tanglish' => 'tanglish', default => 'spelling'
            },
            'source' => 'manual',
            'created_by' => $userId,
        ]);

        return $out('created', $created);
    }

    /** Another item whose English name (spelling-tolerant) or Tamil name is $text. */
    public function itemNamed(string $text, ?int $exceptId = null): ?InventoryItem
    {
        $key = ItemText::phonetic($text);
        $norm = ItemText::normalize($text);
        if ($key === '') {
            return null;
        }
        $q = InventoryItem::query()->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId));

        return (clone $q)->where('search_key', $key)->first()
            ?? (ItemText::hasTamil($text) ? $q->whereNotNull('name_ta')->get(['id', 'name', 'name_ta'])
                ->first(fn ($i) => ItemText::normalize($i->name_ta) === $norm) : null);
    }

    /**
     * Groups of items that look like duplicates: same spelling key, same Tamil name,
     * or one item's alias equal to another item's name. Never merged automatically.
     *
     * @return Collection<int, Collection<int, InventoryItem>>
     */
    public function possibleDuplicates(): Collection
    {
        $items = InventoryItem::with('category:id,name')->withCount(['transactions' => fn ($q) => $q->reorder()])->orderBy('name')->get();
        $parent = [];
        $find = function ($x) use (&$parent, &$find) {
            return ($parent[$x] ?? $x) === $x ? $x : $parent[$x] = $find($parent[$x]);
        };
        $union = function ($a, $b) use (&$parent, $find) {
            $parent[$find($a)] = $find($b);
        };
        $byKey = [];
        foreach ($items as $i) {
            $keys = ['k:'.ItemText::compact((string) $i->search_key)];
            if ($i->name_ta) {
                $keys[] = 't:'.ItemText::compact(ItemText::normalize($i->name_ta));
            }
            foreach ($keys as $k) {
                if (isset($byKey[$k])) {
                    $union($i->id, $byKey[$k]);
                } else {
                    $byKey[$k] = $i->id;
                }
            }
        }
        foreach (InventoryItemAlias::whereNull('vendor_id')->get(['inventory_item_id', 'phonetic_key']) as $a) {
            $other = $byKey['k:'.ItemText::compact((string) $a->phonetic_key)] ?? null;
            if ($other && $other !== $a->inventory_item_id) {
                $union($a->inventory_item_id, $other);
            }
        }

        return $items->groupBy(fn ($i) => $find($i->id))->filter(fn ($g) => $g->count() > 1)->values();
    }
}
