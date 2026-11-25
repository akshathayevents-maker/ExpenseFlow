<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A material request can cover several preparations, each for its own number of people
 * (e.g. Upma for 80, Coffee for 120). preparation_name / guest_count stay as the summary
 * (names joined, largest head count) so existing labels, reports and recipe suggestions keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->json('preparations')->nullable()->after('guest_count');
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('preparations');
        });
    }
};
