<?php

use App\Support\ItemText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multilingual item catalogue (additive, reversible):
 *  - inventory_items.name_ta    Tamil name
 *  - inventory_items.search_key phonetic key of the English name (duplicate detection, search)
 *  - inventory_item_aliases.language / alias_type / phonetic_key
 *  - a generic alias (no supplier) can belong to ONE item only (partial unique index)
 * Existing rows are back-filled; no stock, ledger or history column is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $t) {
            $t->string('name_ta', 200)->nullable()->after('name');
            $t->string('search_key', 200)->nullable()->after('name_ta');
            $t->index('search_key');
        });

        Schema::table('inventory_item_aliases', function (Blueprint $t) {
            $t->string('language', 12)->nullable()->after('normalized_alias');   // en | ta | tanglish
            $t->string('alias_type', 20)->default('spelling')->after('language'); // spelling | tamil | tanglish | invoice
            $t->string('phonetic_key', 200)->nullable()->after('alias_type');
            $t->index('phonetic_key');
        });

        foreach (DB::table('inventory_items')->select('id', 'name')->orderBy('id')->cursor() as $row) {
            DB::table('inventory_items')->where('id', $row->id)->update(['search_key' => ItemText::phonetic($row->name)]);
        }
        foreach (DB::table('inventory_item_aliases')->select('id', 'alias', 'vendor_id')->orderBy('id')->cursor() as $row) {
            DB::table('inventory_item_aliases')->where('id', $row->id)->update([
                // the old normaliser dropped Tamil vowel signs; recompute those keys
                ...(ItemText::hasTamil($row->alias) ? ['normalized_alias' => ItemText::normalize($row->alias)] : []),
                'phonetic_key' => ItemText::phonetic($row->alias),
                'language' => ItemText::hasTamil($row->alias) ? 'ta' : 'en',
                'alias_type' => $row->vendor_id ? 'invoice' : 'spelling',
            ]);
        }

        // Refuse (loudly) instead of silently dropping data if old generic aliases collide.
        $dupes = DB::table('inventory_item_aliases')->whereNull('vendor_id')
            ->select('normalized_alias')->groupBy('normalized_alias')->havingRaw('COUNT(*) > 1')->pluck('normalized_alias');
        if ($dupes->isNotEmpty()) {
            throw new RuntimeException('Generic aliases point to more than one item: '.$dupes->take(10)->implode(', ')
                .'. Resolve them in Item Catalogue before migrating.');
        }
        DB::statement('CREATE UNIQUE INDEX inv_alias_generic_unique ON inventory_item_aliases (normalized_alias) WHERE vendor_id IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS inv_alias_generic_unique');
        Schema::table('inventory_item_aliases', function (Blueprint $t) {
            $t->dropIndex(['phonetic_key']);
            $t->dropColumn(['language', 'alias_type', 'phonetic_key']);
        });
        Schema::table('inventory_items', function (Blueprint $t) {
            $t->dropIndex(['search_key']);
            $t->dropColumn(['name_ta', 'search_key']);
        });
    }
};
