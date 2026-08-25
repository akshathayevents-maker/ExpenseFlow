<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'leave_policy_template_id')) {
                // Pure display/bookkeeping pointer to "which template this
                // employee is currently on" — never read by
                // LeaveAllocationService/LeaveBalanceService/LeaveService,
                // which only ever read EmployeeLeavePolicy rows.
                $table->foreignId('leave_policy_template_id')->nullable()
                    ->after('employment_end_date')
                    ->constrained('leave_policy_templates')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'leave_policy_template_id')) {
                $table->dropConstrainedForeignId('leave_policy_template_id');
            }
        });
    }
};
