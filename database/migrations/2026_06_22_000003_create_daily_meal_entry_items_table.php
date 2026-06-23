<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_meal_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_meal_entry_id')->constrained('daily_meal_entries')->cascadeOnDelete();
            $table->string('meal_type', 50);
            $table->unsignedInteger('planned_count')->default(0);
            $table->unsignedInteger('actual_count')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_meal_entry_items');
    }
};
