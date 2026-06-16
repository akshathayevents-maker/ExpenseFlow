<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();

            // Optional link to inventory — never blocks recipe creation
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();

            // ingredient_name is always the display source; inventory_item_id is supplemental
            $table->string('ingredient_name', 200);

            // null quantity_per_batch means "as required" — display quantity_note verbatim
            $table->decimal('quantity_per_batch', 12, 3)->nullable();
            $table->string('quantity_note', 200)->nullable();   // "As Required", "Approx 20 L", "To Taste"
            $table->string('unit', 50)->nullable();             // free text: "kg", "L", "nos", "pinch"

            $table->string('prep_note', 200)->nullable();       // "finely chopped", "sifted"
            $table->boolean('is_optional')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('recipe_id');
            $table->index('inventory_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
