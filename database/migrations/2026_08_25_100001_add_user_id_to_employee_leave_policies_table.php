<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_leave_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_leave_policies', 'user_id')) {
                // A policy now belongs to exactly one employee — this table
                // was previously a global per-leave-type policy with no
                // employee scoping at all. Table is empty in every existing
                // environment (verified before writing this migration), so
                // a NOT NULL column is safe to add directly, no backfill.
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            }
        });

        Schema::table('employee_leave_policies', function (Blueprint $table) {
            $table->dropIndex(['leave_type_id', 'is_active', 'effective_from']);
            $table->index(['user_id', 'leave_type_id', 'is_active', 'effective_from'], 'emp_leave_policy_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('employee_leave_policies', function (Blueprint $table) {
            $table->dropIndex('emp_leave_policy_lookup');
            $table->index(['leave_type_id', 'is_active', 'effective_from']);
        });

        Schema::table('employee_leave_policies', function (Blueprint $table) {
            if (Schema::hasColumn('employee_leave_policies', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
