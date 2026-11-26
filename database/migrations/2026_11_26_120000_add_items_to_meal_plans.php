<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dishes in a meal plan as an ordered list (JSON array of names). Before this the dishes lived as lines
 * inside `description`; MealPlan::dishList() still falls back to those lines for plans not yet re-saved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_plans', function (Blueprint $t) {
            $t->json('items')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('meal_plans', fn (Blueprint $t) => $t->dropColumn('items'));
    }
};
