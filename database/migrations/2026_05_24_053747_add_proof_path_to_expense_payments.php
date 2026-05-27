<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add proof_file_path to expense_payments.
     *
     * Staff upload a payment screenshot / UTR slip from the public payment page.
     * Stored on the private (default) disk — served only via a signed controller URL.
     */
    public function up(): void
    {
        Schema::table('expense_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_payments', 'proof_file_path')) {
                $table->string('proof_file_path', 500)->nullable()->after('payment_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expense_payments', function (Blueprint $table) {
            if (Schema::hasColumn('expense_payments', 'proof_file_path')) {
                $table->dropColumn('proof_file_path');
            }
        });
    }
};
