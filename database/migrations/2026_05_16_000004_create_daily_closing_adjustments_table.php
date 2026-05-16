<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_closing_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_closing_id')->constrained('daily_closings')->cascadeOnDelete();
            $table->string('type'); // credit, debit
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['daily_closing_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closing_adjustments');
    }
};
