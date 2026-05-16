<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, upi, card, bank_transfer
            $table->string('reference_number')->nullable();
            $table->string('payment_type')->default('advance'); // advance, balance, full
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
