<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_advances')) {
            Schema::create('employee_advances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                // employee_request | admin_recorded (pre-existing advance entered by an admin)
                $table->string('origin');

                $table->decimal('requested_amount', 12, 2)->nullable(); // null when admin_recorded
                // Full AdvanceEligibilityService breakdown, snapshotted at request time
                // (or at admin-entry time) — powers the "why this amount" UI and audit trail.
                $table->json('eligibility_breakdown')->nullable();

                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();

                // pending|approved|rejected|cancelled
                $table->string('request_status')->default('pending');
                // unpaid|paid
                $table->string('payment_status')->default('unpaid');
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();

                // = approved_amount once paid, or the admin-entered original
                // principal for an admin_recorded advance.
                $table->decimal('original_amount', 12, 2)->default(0);

                // CACHED/DERIVED ONLY. Always reconciled against
                // SUM(advance_transactions.amount) inside the same DB
                // transaction that writes a new transaction row. No
                // controller/Blade/form may set this directly — it is
                // intentionally excluded from $fillable on the model.
                $table->decimal('outstanding_amount', 12, 2)->default(0);

                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'request_status']);
                $table->index(['user_id', 'payment_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};
