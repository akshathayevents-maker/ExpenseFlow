<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('meal_plans')) {
            Schema::create('meal_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->default('standard'); // standard, premium, custom
                $table->text('description')->nullable();
                $table->decimal('price_per_person', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
