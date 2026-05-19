<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            // Make nullable — employee QR flow doesn't require a category
            $table->unsignedBigInteger('expense_category_id')->nullable()->change();

            // Make nullable — admin/manager can set priority later
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable()->change();

            // Extend status enum to include pending_payment
            $table->enum('status', [
                'pending', 'pending_payment', 'approved', 'rejected',
                'paid', 'reimbursement_pending', 'reimbursed', 'completed',
            ])->default('pending')->change();

            $table->string('qr_file_path')->nullable()->after('notes');
            $table->timestamp('whatsapp_sent_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropColumn(['qr_file_path', 'whatsapp_sent_at']);

            // Revert status (remove pending_payment)
            $table->enum('status', [
                'pending', 'approved', 'rejected', 'paid',
                'reimbursement_pending', 'reimbursed', 'completed',
            ])->default('pending')->change();

            // Restore NOT NULL
            $table->unsignedBigInteger('expense_category_id')->nullable(false)->change();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->nullable(false)->change();
        });
    }
};
