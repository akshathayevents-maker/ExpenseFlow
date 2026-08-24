<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_salaries')) {
            Schema::create('employee_salaries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                $table->decimal('monthly_salary', 12, 2);

                // Effective-dated, never overwritten. A salary change closes the
                // previous row (sets effective_to) and inserts a new row — the
                // "current" salary is resolved by query (effective_from <= date
                // AND (effective_to IS NULL OR effective_to >= date)), never a flag.
                $table->date('effective_from');
                $table->date('effective_to')->nullable();

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'effective_from']);
                $table->index(['user_id', 'effective_to']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
