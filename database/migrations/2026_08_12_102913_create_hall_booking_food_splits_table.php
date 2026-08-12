<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_booking_food_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_booking_id')->constrained('hall_bookings')->cascadeOnDelete();
            $table->foreignId('meal_plan_id')->nullable()->constrained('meal_plans')->nullOnDelete();

            // Snapshot the plan name/price at save time — a meal plan being
            // renamed or repriced later must never change what an already
            // saved booking/invoice shows.
            $table->string('meal_plan_name');
            $table->unsignedInteger('guest_count');
            $table->decimal('price_per_guest', 10, 2);
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['hall_booking_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_booking_food_splits');
    }
};
