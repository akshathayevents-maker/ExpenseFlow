<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kitchen inventory control — Phase 2.
 *
 * - inventory_transactions.type and inventory_items.unit: enum -> string so new
 *   ledger types (purchase_receipt, material_issue, material_return, correction,
 *   reversal) and configurable units (bag, bottle, ...) can be stored.
 *   Existing values are not touched.
 * - Ledger traceability columns (reversal link, entered quantity/unit, label).
 * - users.is_storekeeper capability flag (role enum is unchanged).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_transactions DROP CONSTRAINT IF EXISTS inventory_transactions_type_check');
            DB::statement('ALTER TABLE inventory_transactions ALTER COLUMN type TYPE VARCHAR(30)');
            DB::statement('ALTER TABLE inventory_items DROP CONSTRAINT IF EXISTS inventory_items_unit_check');
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN unit TYPE VARCHAR(20)');
        } else {
            Schema::table('inventory_transactions', fn (Blueprint $t) => $t->string('type', 30)->change());
            Schema::table('inventory_items', fn (Blueprint $t) => $t->string('unit', 20)->change());
        }

        Schema::table('inventory_transactions', function (Blueprint $t) {
            if (! Schema::hasColumn('inventory_transactions', 'reversal_of_id')) {
                $t->foreignId('reversal_of_id')->nullable()->constrained('inventory_transactions')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventory_transactions', 'entered_quantity')) {
                $t->decimal('entered_quantity', 12, 3)->nullable();
                $t->string('entered_unit', 20)->nullable();
            }
            if (! Schema::hasColumn('inventory_transactions', 'reference_label')) {
                $t->string('reference_label', 120)->nullable();
            }
            if (! Schema::hasIndex('inventory_transactions', 'inventory_transactions_created_at_index')) {
                $t->index('created_at');
            }
        });

        Schema::table('users', function (Blueprint $t) {
            if (! Schema::hasColumn('users', 'is_storekeeper')) {
                $t->boolean('is_storekeeper')->default(false);
            }
        });

        if (! Schema::hasTable('inventory_unit_conversions')) {
            Schema::create('inventory_unit_conversions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $t->string('unit', 20);                 // e.g. bag
                $t->decimal('factor', 14, 6);           // 1 unit = factor x item base unit
                $t->string('notes', 200)->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->unique(['inventory_item_id', 'unit']);
            });
        }

        if (! Schema::hasTable('inventory_item_aliases')) {
            Schema::create('inventory_item_aliases', function (Blueprint $t) {
                $t->id();
                $t->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $t->string('alias', 200);
                $t->string('normalized_alias', 200);
                $t->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $t->string('unit_hint', 20)->nullable();
                $t->string('source', 20)->default('manual'); // manual | confirmed | invoice
                $t->unsignedInteger('confirmations')->default(0);
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index('normalized_alias');
                $t->unique(['normalized_alias', 'vendor_id', 'inventory_item_id'], 'inv_alias_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_aliases');
        Schema::dropIfExists('inventory_unit_conversions');
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'is_storekeeper')) {
                $t->dropColumn('is_storekeeper');
            }
        });
        Schema::table('inventory_transactions', function (Blueprint $t) {
            if (Schema::hasIndex('inventory_transactions', 'inventory_transactions_created_at_index')) {
                $t->dropIndex('inventory_transactions_created_at_index');
            }
            if (Schema::hasColumn('inventory_transactions', 'reversal_of_id')) {
                $t->dropConstrainedForeignId('reversal_of_id');
            }
            foreach (['entered_quantity', 'entered_unit', 'reference_label'] as $c) {
                if (Schema::hasColumn('inventory_transactions', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
        // type/unit stay VARCHAR: re-adding the old CHECK would fail once new values exist.
    }
};
