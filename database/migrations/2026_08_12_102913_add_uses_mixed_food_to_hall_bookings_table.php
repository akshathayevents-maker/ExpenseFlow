<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            // When true, food cost/pricing comes from hall_booking_food_splits
            // instead of the single meal_plan_id + number_of_people pair.
            // Default false — existing bookings are untouched normal bookings.
            $table->boolean('uses_mixed_food')->default(false)->after('meal_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            $table->dropColumn('uses_mixed_food');
        });
    }
};
