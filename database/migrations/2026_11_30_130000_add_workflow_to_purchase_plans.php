<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purchase-plan workflow for employees (additive):
 *   draft → submitted → approved | rejected → ordered (purchases created from the plan) → completed / cancelled
 * - approved_quantity: what the approver allowed per line (null = as requested)
 * - closing_note / shortfall_plan_id: how differences between plan and what arrived were settled
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_plans', function (Blueprint $t) {
            $t->timestamp('submitted_at')->nullable()->after('status');
            $t->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $t->timestamp('rejected_at')->nullable()->after('rejected_by');
            $t->string('rejection_reason', 500)->nullable()->after('rejected_at');
            $t->string('approval_note', 500)->nullable()->after('rejection_reason');
            $t->foreignId('closed_by')->nullable()->after('approval_note')->constrained('users')->nullOnDelete();
            $t->timestamp('completed_at')->nullable()->after('closed_by');
            $t->string('closing_note', 1000)->nullable()->after('completed_at');
            $t->foreignId('shortfall_plan_id')->nullable()->after('closing_note')->constrained('purchase_plans')->nullOnDelete();
            $t->index('created_by');
        });
        Schema::table('purchase_plan_items', function (Blueprint $t) {
            $t->decimal('approved_quantity', 12, 3)->nullable()->after('suggested_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_plan_items', fn (Blueprint $t) => $t->dropColumn('approved_quantity'));
        Schema::table('purchase_plans', function (Blueprint $t) {
            $t->dropIndex(['created_by']);
            $t->dropConstrainedForeignId('shortfall_plan_id');
            $t->dropConstrainedForeignId('closed_by');
            $t->dropConstrainedForeignId('rejected_by');
            $t->dropColumn(['submitted_at', 'rejected_at', 'rejection_reason', 'approval_note', 'completed_at', 'closing_note']);
        });
    }
};
