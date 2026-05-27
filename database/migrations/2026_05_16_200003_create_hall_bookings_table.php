<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hall_bookings')) {
            Schema::create('hall_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
                $table->foreignId('meal_plan_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by')->constrained('users');

                // Customer info
                $table->string('customer_name');
                $table->string('customer_mobile', 15);
                $table->string('customer_alt_mobile', 15)->nullable();

                // Event info
                $table->string('event_type');
                $table->date('booking_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('number_of_people');

                // Meals
                $table->boolean('has_breakfast')->default(false);
                $table->boolean('has_lunch')->default(false);
                $table->boolean('has_dinner')->default(false);

                // Financials
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->decimal('advance_amount', 12, 2)->default(0);
                $table->string('payment_status')->default('pending'); // pending, partial, paid
                $table->string('status')->default('confirmed');       // confirmed, cancelled, completed

                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_bookings');
    }
};
