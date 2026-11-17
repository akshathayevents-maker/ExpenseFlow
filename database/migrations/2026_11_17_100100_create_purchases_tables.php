<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bulk purchases: Draft -> Submitted -> Approved -> (Partially) Received -> Completed.
 * Stock only moves on goods receipt (goods_receipt_items -> inventory_transactions).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $t) {
                $t->id();
                $t->date('purchase_date');
                $t->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $t->string('supplier_name', 150)->nullable();   // free text when no vendor row
                $t->string('invoice_number', 100)->nullable();
                $t->string('supplier_reference', 100)->nullable();
                $t->string('payment_mode', 20)->nullable();     // cash|upi|bank_transfer|credit
                $t->string('payment_status', 20)->default('unpaid'); // unpaid|partial|paid
                $t->decimal('amount_paid', 12, 2)->default(0);
                $t->decimal('subtotal', 12, 2)->default(0);
                $t->decimal('tax_amount', 12, 2)->default(0);
                $t->decimal('discount_amount', 12, 2)->default(0);
                $t->decimal('grand_total', 12, 2)->default(0);
                $t->string('status', 20)->default('draft');
                $t->text('notes')->nullable();
                $t->foreignId('purchase_plan_id')->nullable()->constrained('purchase_plans')->nullOnDelete();
                $t->foreignId('inventory_bill_upload_id')->nullable()->constrained('inventory_bill_uploads')->nullOnDelete();
                $t->uuid('client_token')->nullable()->unique();  // double-submit guard
                $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $t->timestamp('submitted_at')->nullable();
                $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('approved_at')->nullable();
                $t->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('rejected_at')->nullable();
                $t->string('rejection_reason', 500)->nullable();
                $t->timestamp('completed_at')->nullable();
                $t->timestamps();
                $t->index(['status', 'purchase_date']);
                $t->index('purchase_date');
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $t->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
                $t->string('description', 200)->nullable();   // as written on the invoice
                $t->decimal('quantity', 12, 3);
                $t->string('unit', 20);
                $t->decimal('base_quantity', 12, 3);          // in inventory_items.unit
                $t->decimal('rate', 12, 2);
                $t->decimal('tax_percent', 5, 2)->default(0);
                $t->decimal('amount', 12, 2);
                $t->decimal('received_base_quantity', 12, 3)->default(0);
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
                $t->index('inventory_item_id');
            });
        }

        if (! Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('purchase_id')->constrained()->restrictOnDelete();
                $t->foreignId('received_by')->constrained('users')->restrictOnDelete();
                $t->timestamp('received_at');
                $t->text('notes')->nullable();
                $t->uuid('client_token')->nullable()->unique();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
                $t->foreignId('purchase_item_id')->constrained()->restrictOnDelete();
                $t->decimal('received_base_quantity', 12, 3);
                $t->decimal('rejected_base_quantity', 12, 3)->default(0);
                $t->string('remarks', 300)->nullable();
                $t->foreignId('inventory_transaction_id')->nullable()->constrained('inventory_transactions')->nullOnDelete();
                $t->timestamps();
            });
        }

        Schema::table('inventory_bill_items', function (Blueprint $t) {
            if (! Schema::hasColumn('inventory_bill_items', 'match_confidence')) {
                $t->unsignedTinyInteger('match_confidence')->nullable();
                $t->string('match_method', 20)->nullable();
                $t->string('original_item_name', 255)->nullable();
            }
        });
        Schema::table('inventory_bill_uploads', function (Blueprint $t) {
            if (! Schema::hasColumn('inventory_bill_uploads', 'purchase_id')) {
                $t->unsignedBigInteger('purchase_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_bill_uploads', function (Blueprint $t) {
            if (Schema::hasColumn('inventory_bill_uploads', 'purchase_id')) {
                $t->dropIndex(['purchase_id']);
                $t->dropColumn('purchase_id');
            }
        });
        Schema::table('inventory_bill_items', function (Blueprint $t) {
            foreach (['match_confidence', 'match_method', 'original_item_name'] as $c) {
                if (Schema::hasColumn('inventory_bill_items', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
