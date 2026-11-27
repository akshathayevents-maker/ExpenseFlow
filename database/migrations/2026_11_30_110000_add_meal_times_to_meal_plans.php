<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Which meals a package is for (subset of breakfast/lunch/dinner). Null = not tagged (offered for any meal). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_plans', function (Blueprint $t) {
            $t->json('meal_times')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('meal_plans', function (Blueprint $t) {
            $t->dropColumn('meal_times');
        });
    }
};
