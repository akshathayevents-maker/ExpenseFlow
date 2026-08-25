<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_policy_template_items')) {
            Schema::create('leave_policy_template_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_policy_template_id')->constrained()->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();

                // Mirrors EmployeeLeavePolicy's column shapes exactly — a
                // template item is only ever "stamped" into a new
                // EmployeeLeavePolicy row at assignment time, never read
                // live by allocation/balance/LOP logic.
                $table->decimal('annual_entitlement', 5, 1);
                $table->string('allocation_mode'); // yearly|monthly_accrual|quarterly_accrual
                $table->decimal('monthly_accrual_amount', 4, 2)->nullable()->default(0);

                $table->timestamps();

                $table->unique(['leave_policy_template_id', 'leave_type_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_template_items');
    }
};
