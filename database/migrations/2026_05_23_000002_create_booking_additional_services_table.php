<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_additional_services')) {
            Schema::create('booking_additional_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hall_booking_id')->constrained()->cascadeOnDelete();
                $table->string('service_name');
                $table->text('description')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_additional_services');
    }
};
