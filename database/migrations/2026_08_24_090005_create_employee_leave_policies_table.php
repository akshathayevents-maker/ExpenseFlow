<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_leave_policies')) {
            Schema::create('employee_leave_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();

                $table->decimal('annual_entitlement', 5, 1);

                // yearly            = full annual_entitlement granted once at leave-year start
                // monthly_accrual   = monthly_accrual_amount added after each completed month
                $table->string('allocation_mode'); // yearly|monthly_accrual
                $table->decimal('monthly_accrual_amount', 4, 2)->nullable();

                // Effective-dated like salary — a policy change creates a new
                // row rather than editing the old one in place.
                $table->date('effective_from');
                $table->boolean('is_active')->default(true);

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();

                $table->index(['leave_type_id', 'is_active', 'effective_from']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_policies');
    }
};
