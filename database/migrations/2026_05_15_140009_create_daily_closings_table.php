<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daily_closings')) {
            Schema::create('daily_closings', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->string('status')->default('draft'); // draft, verified, closed
                $table->decimal('expense_total', 12, 2)->default(0);
                $table->decimal('payment_total', 12, 2)->default(0);
                $table->decimal('stock_additions', 12, 3)->default(0);
                $table->decimal('stock_deductions', 12, 3)->default(0);
                $table->integer('expense_count')->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->index(['date', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
