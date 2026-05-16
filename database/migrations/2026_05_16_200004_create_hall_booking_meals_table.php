<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_booking_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_booking_id')->constrained()->cascadeOnDelete();
            $table->string('meal_type'); // breakfast, lunch, dinner
            $table->unsignedInteger('guest_count')->nullable();
            $table->text('special_requirements')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_booking_meals');
    }
};
