<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->unsignedInteger('prep_time_minutes')->nullable();
            $table->unsignedInteger('cook_time_minutes')->nullable();
            $table->decimal('yield_per_batch', 8, 2);   // how many people one batch serves
            $table->string('yield_unit', 50)->default('portions');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
