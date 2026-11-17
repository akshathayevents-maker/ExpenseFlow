<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Material requests from the central store.
 * Approval deducts stock immediately (material_issue); handover is recorded
 * separately and never deducts again; cancel-before-handover reverses.
 * Line quantities are stored in the item's stock unit (*_base_quantity).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('material_requests')) {
            Schema::create('material_requests', function (Blueprint $t) {
                $t->id();
                $t->string('department', 30)->default('kitchen');
                $t->string('source', 10)->default('other');           // meal | hall | other
                $t->foreignId('meal_entry_id')->nullable()->constrained('meal_entries')->nullOnDelete();
                $t->string('meal_type', 20)->nullable();
                $t->foreignId('hall_booking_id')->nullable()->constrained('hall_bookings')->nullOnDelete();
                $t->date('required_date');
                $t->time('required_time')->nullable();
                $t->string('preparation_name', 150)->nullable();
                $t->unsignedInteger('guest_count')->nullable();
                $t->string('purpose', 200)->nullable();
                $t->text('notes')->nullable();
                $t->json('menu_snapshot')->nullable();                 // final hall menu at request time
                $t->string('status', 20)->default('draft');
                $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
                $t->timestamp('submitted_at')->nullable();
                $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('approved_at')->nullable();
                $t->string('approval_remarks', 500)->nullable();
                $t->foreignId('handed_over_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
                $t->string('collected_by_name', 120)->nullable();
                $t->timestamp('handed_over_at')->nullable();
                $t->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('cancelled_at')->nullable();
                $t->string('cancel_reason', 500)->nullable();
                $t->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('reconciled_at')->nullable();
                $t->string('reconciliation_status', 20)->nullable();  // pending_review | accepted
                $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('reviewed_at')->nullable();
                $t->string('review_remarks', 500)->nullable();
                $t->foreignId('revision_of_id')->nullable()->constrained('material_requests')->nullOnDelete();
                $t->unsignedSmallInteger('revision_no')->default(1);
                $t->uuid('client_token')->nullable()->unique();
                $t->timestamps();
                $t->index(['status', 'required_date']);
                $t->index('required_date');
                $t->index('requested_by');
                $t->index(['meal_entry_id', 'meal_type']);
                $t->index('hall_booking_id');
            });
        }

        if (! Schema::hasTable('material_request_items')) {
            Schema::create('material_request_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('material_request_id')->constrained()->cascadeOnDelete();
                $t->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
                $t->decimal('requested_quantity', 12, 3);
                $t->string('requested_unit', 20);
                $t->decimal('requested_base_quantity', 12, 3);
                $t->decimal('approved_base_quantity', 12, 3)->nullable();
                $t->string('line_status', 20)->default('pending');   // pending|approved|reduced|rejected
                $t->string('admin_remark', 300)->nullable();
                $t->foreignId('issue_transaction_id')->nullable()->constrained('inventory_transactions')->nullOnDelete();
                $t->decimal('issued_base_quantity', 12, 3)->nullable();
                $t->decimal('returned_base_quantity', 12, 3)->default(0);
                $t->decimal('consumed_base_quantity', 12, 3)->nullable();
                $t->decimal('wasted_base_quantity', 12, 3)->nullable();
                $t->decimal('unexplained_base_quantity', 12, 3)->nullable();
                $t->string('wastage_reason', 300)->nullable();
                $t->string('variance_reason', 300)->nullable();
                $t->decimal('unit_cost', 12, 4)->nullable();           // stock average cost at approval
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index('inventory_item_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_request_items');
        Schema::dropIfExists('material_requests');
    }
};
