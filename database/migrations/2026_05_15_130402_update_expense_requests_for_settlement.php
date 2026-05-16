<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend status CHECK constraint for PostgreSQL
        DB::statement("ALTER TABLE expense_requests DROP CONSTRAINT IF EXISTS expense_requests_status_check");
        DB::statement("ALTER TABLE expense_requests ADD CONSTRAINT expense_requests_status_check CHECK (status IN ('pending','approved','rejected','paid','reimbursement_pending','reimbursed','completed'))");

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

        DB::statement("ALTER TABLE expense_requests DROP CONSTRAINT IF EXISTS expense_requests_status_check");
        DB::statement("ALTER TABLE expense_requests ADD CONSTRAINT expense_requests_status_check CHECK (status IN ('pending','approved','rejected','paid','completed'))");
    }
};
