<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // Add reimbursement_pending and reimbursed to status enum
            $table->enum('status', [
                'pending', 'approved', 'rejected', 'paid',
                'reimbursement_pending', 'reimbursed', 'completed',
            ])->default('pending')->change();

            $table->enum('settlement_type', ['direct_payment', 'wallet_deduction', 'reimbursement'])
                  ->nullable()
                  ->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropColumn('settlement_type');

            $table->enum('status', ['pending', 'approved', 'rejected', 'paid', 'completed'])
                  ->default('pending')->change();
        });
    }
};
