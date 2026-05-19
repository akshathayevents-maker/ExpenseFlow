<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL cannot use CHECK inline in ALTER COLUMN TYPE.
        // Must drop old constraint, retype, then add new constraint separately.
        DB::statement("
            ALTER TABLE expense_requests
                DROP CONSTRAINT IF EXISTS expense_requests_status_check,
                ALTER COLUMN status TYPE VARCHAR(255),
                ALTER COLUMN status SET DEFAULT 'pending',
                ALTER COLUMN status SET NOT NULL
        ");
        DB::statement("
            ALTER TABLE expense_requests
                ADD CONSTRAINT expense_requests_status_check
                CHECK (status IN (
                    'pending','approved','rejected','paid',
                    'reimbursement_pending','reimbursed','completed'
                ))
        ");

        Schema::table('expense_requests', function (Blueprint $table) {
            $table->enum('settlement_type', ['direct_payment', 'wallet_deduction', 'reimbursement'])
                  ->nullable()
                  ->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropColumn('settlement_type');
        });

        DB::statement("
            ALTER TABLE expense_requests
                DROP CONSTRAINT IF EXISTS expense_requests_status_check,
                ALTER COLUMN status TYPE VARCHAR(255),
                ALTER COLUMN status SET DEFAULT 'pending',
                ALTER COLUMN status SET NOT NULL
        ");
        DB::statement("
            ALTER TABLE expense_requests
                ADD CONSTRAINT expense_requests_status_check
                CHECK (status IN ('pending','approved','rejected','paid','completed'))
        ");
    }
};
