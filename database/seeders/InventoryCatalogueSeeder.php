<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Services\Inventory\ItemCatalogueService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent kitchen catalogue (English + Tamil + Tanglish aliases).
 *   php artisan db:seed --class=InventoryCatalogueSeeder
 *
 * Safe to run repeatedly and on live data:
 *  - existing items are matched (spelling-tolerant English name, or Tamil name) and reused;
 *  - for reused items only a missing Tamil name is filled in — never name, unit, category,
 *    stock, cost, status or history;
 *  - new items start at zero stock; aliases that already belong to another item are skipped.
 */
class InventoryCatalogueSeeder extends Seeder
{
    public array $stats = ['categories_created' => 0, 'items_created' => 0, 'items_reused' => 0,
        'tamil_names_filled' => 0, 'aliases_created' => 0, 'aliases_skipped' => 0, 'conflicts' => []];

    public function run(ItemCatalogueService $catalogue): void
    {
        $data = require database_path('seeders/data/kitchen_catalogue.php');
        $units = array_keys(config('inventory.units'));

        DB::transaction(function () use ($data, $units, $catalogue) {
            foreach ($data as $categoryName => $rows) {
                $category = InventoryCategory::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])->first();
                if (! $category) {
                    $category = InventoryCategory::create(['name' => $categoryName, 'is_active' => true]);
                    $this->stats['categories_created']++;
                }
                foreach ($rows as $row) {
                    [$name, $ta, $unit, $en, $tanglish, $taExtra] = array_pad(explode('|', $row), 6, '');
                    if (! in_array($unit, $units, true)) {
                        throw new \RuntimeException("Unknown unit '{$unit}' for {$name}");
                    }
                    $item = $catalogue->itemNamed($name) ?? ($ta ? $catalogue->itemNamed($ta) : null);
                    if ($item) {
                        $this->stats['items_reused']++;
                        if (! $item->name_ta && $ta && ! $catalogue->itemNamed($ta, $item->id)) {
                            $item->name_ta = $ta;
                            $item->save();
                            $this->stats['tamil_names_filled']++;
                        }
                    } else {
                        $item = InventoryItem::create([
                            'name' => $name, 'name_ta' => $ta ?: null, 'unit' => $unit,
                            'inventory_category_id' => $category->id, 'minimum_stock' => 0, 'status' => 'active',
                        ]);
                        $this->stats['items_created']++;
                    }
                    $aliases = [];
                    foreach (array_filter(array_map('trim', explode(',', $en))) as $a) {
                        $aliases[] = [$a, 'en'];
                    }
                    foreach (array_filter(array_map('trim', explode(',', $tanglish))) as $a) {
                        $aliases[] = [$a, 'tanglish'];
                    }
                    foreach (array_filter(array_map('trim', explode(',', $taExtra))) as $a) {
                        $aliases[] = [$a, 'ta'];
                    }
                    foreach ($aliases as [$alias, $lang]) {
                        $r = $catalogue->addAlias($item, $alias, $lang);
                        if ($r['status'] === 'created') {
                            $this->stats['aliases_created']++;
                        } else {
                            $this->stats['aliases_skipped']++;
                            if ($r['status'] === 'conflict') {
                                $this->stats['conflicts'][] = "{$alias} → {$item->name} (already {$r['other']?->name})";
                            }
                        }
                    }
                }
            }
        });

        $s = $this->stats;
        $this->command?->info("Categories created: {$s['categories_created']} · Items created: {$s['items_created']} · reused: {$s['items_reused']}"
            ." · Tamil names filled: {$s['tamil_names_filled']} · Aliases created: {$s['aliases_created']} · skipped: {$s['aliases_skipped']}");
        foreach ($s['conflicts'] as $c) {
            $this->command?->warn('Alias not added: '.$c);
        }
    }
}
