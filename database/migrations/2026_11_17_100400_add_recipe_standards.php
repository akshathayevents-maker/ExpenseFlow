<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gradual recipe standards: versioned recipes with per-guest quantities
 * (stock unit of the linked inventory item). Old versions are kept; material
 * requests remember which version suggested their quantities.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $t) {
            if (! Schema::hasColumn('recipes', 'version')) {
                $t->unsignedInteger('version')->default(1);
                $t->foreignId('parent_recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
                $t->timestamp('superseded_at')->nullable();
            }
        });
        Schema::table('recipe_ingredients', function (Blueprint $t) {
            if (! Schema::hasColumn('recipe_ingredients', 'quantity_per_guest')) {
                $t->decimal('quantity_per_guest', 12, 5)->nullable();
            }
        });
        Schema::table('material_requests', function (Blueprint $t) {
            if (! Schema::hasColumn('material_requests', 'recipe_id')) {
                $t->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
                $t->unsignedInteger('recipe_version')->nullable();
            }
        });
        Schema::table('material_request_items', function (Blueprint $t) {
            if (! Schema::hasColumn('material_request_items', 'suggested_base_quantity')) {
                $t->decimal('suggested_base_quantity', 12, 3)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_request_items', fn (Blueprint $t) => Schema::hasColumn('material_request_items', 'suggested_base_quantity') ? $t->dropColumn('suggested_base_quantity') : null);
        Schema::table('material_requests', function (Blueprint $t) {
            if (Schema::hasColumn('material_requests', 'recipe_id')) {
                $t->dropConstrainedForeignId('recipe_id');
                $t->dropColumn('recipe_version');
            }
        });
        Schema::table('recipe_ingredients', fn (Blueprint $t) => Schema::hasColumn('recipe_ingredients', 'quantity_per_guest') ? $t->dropColumn('quantity_per_guest') : null);
        Schema::table('recipes', function (Blueprint $t) {
            if (Schema::hasColumn('recipes', 'version')) {
                $t->dropConstrainedForeignId('parent_recipe_id');
                $t->dropColumn(['version', 'superseded_at']);
            }
        });
    }
};
