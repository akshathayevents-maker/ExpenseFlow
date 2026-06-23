<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meal_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_entry_id')->constrained('meal_entries')->cascadeOnDelete();
            $table->string('meal_type', 30); // breakfast|lunch|evening_snacks|dinner
            $table->unsignedInteger('planned_count')->nullable();
            $table->unsignedInteger('actual_count')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->foreignId('planned_updated_by')->nullable()->constrained('users');
            $table->foreignId('actual_updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['meal_entry_id', 'meal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_entry_items');
    }
};
