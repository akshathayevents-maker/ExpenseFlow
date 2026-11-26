<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tamil content for recipes, kept beside the English (never replacing it):
 * recipe name, kitchen notes (tips / food safety / scaling), ingredient display name, and each step's title,
 * instruction and step note. Steps stay one row per step, so English and Tamil stay in the same order.
 * Ingredient Tamil names are display text only; stock mapping still uses inventory_item_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $t) {
            $t->string('name_ta', 200)->nullable()->after('name');
            $t->text('kitchen_notes')->nullable()->after('description');
            $t->text('kitchen_notes_ta')->nullable()->after('kitchen_notes');
        });
        Schema::table('recipe_ingredients', function (Blueprint $t) {
            $t->string('ingredient_name_ta', 200)->nullable()->after('ingredient_name');
        });
        Schema::table('recipe_sops', function (Blueprint $t) {
            $t->string('title_ta', 200)->nullable()->after('title');
            $t->text('instruction_ta')->nullable()->after('instruction');
            $t->string('note', 500)->nullable()->after('instruction_ta');
            $t->string('note_ta', 500)->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('recipe_sops', fn (Blueprint $t) => $t->dropColumn(['title_ta', 'instruction_ta', 'note', 'note_ta']));
        Schema::table('recipe_ingredients', fn (Blueprint $t) => $t->dropColumn('ingredient_name_ta'));
        Schema::table('recipes', fn (Blueprint $t) => $t->dropColumn(['name_ta', 'kitchen_notes', 'kitchen_notes_ta']));
    }
};
