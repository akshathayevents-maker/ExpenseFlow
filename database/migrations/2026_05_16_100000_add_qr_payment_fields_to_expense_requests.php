<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make expense_category_id nullable (employee QR flow doesn't need it)
        DB::statement('ALTER TABLE expense_requests ALTER COLUMN expense_category_id DROP NOT NULL');

        // Make priority nullable (admin/manager can set it later)
        DB::statement('ALTER TABLE expense_requests ALTER COLUMN priority DROP NOT NULL');

        // Extend status enum to include pending_payment
        DB::statement("ALTER TABLE expense_requests DROP CONSTRAINT IF EXISTS expense_requests_status_check");
        DB::statement("ALTER TABLE expense_requests ADD CONSTRAINT expense_requests_status_check CHECK (status IN (
            'pending','pending_payment','approved','rejected','paid',
            'reimbursement_pending','reimbursed','completed'
        ))");

        Schema::table('expense_requests', function (Blueprint $table) {
            $table->string('qr_file_path')->nullable()->after('notes');
            $table->timestamp('whatsapp_sent_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropColumn(['qr_file_path', 'whatsapp_sent_at']);
        });

        DB::statement("ALTER TABLE expense_requests DROP CONSTRAINT IF EXISTS expense_requests_status_check");
        DB::statement("ALTER TABLE expense_requests ADD CONSTRAINT expense_requests_status_check CHECK (status IN (
            'pending','approved','rejected','paid','reimbursement_pending','reimbursed','completed'
        ))");

        DB::statement("ALTER TABLE expense_requests ALTER COLUMN expense_category_id SET NOT NULL");
        DB::statement("ALTER TABLE expense_requests ALTER COLUMN priority SET NOT NULL");
    }
};
