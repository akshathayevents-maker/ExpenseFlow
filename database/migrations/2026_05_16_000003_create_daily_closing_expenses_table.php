<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daily_closing_expenses')) {
            Schema::create('daily_closing_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daily_closing_id')->constrained('daily_closings')->cascadeOnDelete();
                $table->foreignId('original_expense_id')->nullable()->constrained('expense_requests')->nullOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
                $table->string('title');
                $table->decimal('amount', 12, 2);
                $table->string('payment_status')->default('pending');
                $table->text('remarks')->nullable();
                $table->boolean('removed')->default(false);
                $table->timestamps();

                $table->index(['daily_closing_id', 'removed']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closing_expenses');
    }
};
