<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Goods receipt records what really arrived against an approved purchase:
 *  - the supplier's delivery bill (file on the private disk, number, amount),
 *  - the rate actually billed per line (stock is valued at it; approved figures stay untouched),
 *  - quantity the storekeeper closed as "not coming" (short supply), which stops it showing as due.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $t) {
            $t->string('bill_number', 100)->nullable()->after('notes');
            $t->decimal('bill_amount', 12, 2)->nullable()->after('bill_number');
            $t->string('bill_path', 255)->nullable()->after('bill_amount');
            $t->string('bill_original_name', 255)->nullable()->after('bill_path');
        });
        Schema::table('goods_receipt_items', function (Blueprint $t) {
            $t->decimal('actual_rate', 12, 2)->nullable()->after('rejected_base_quantity');
            $t->decimal('closed_base_quantity', 12, 3)->default(0)->after('actual_rate');
        });
        Schema::table('purchase_items', function (Blueprint $t) {
            $t->decimal('closed_base_quantity', 12, 3)->default(0)->after('received_base_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', fn (Blueprint $t) => $t->dropColumn('closed_base_quantity'));
        Schema::table('goods_receipt_items', fn (Blueprint $t) => $t->dropColumn(['actual_rate', 'closed_base_quantity']));
        Schema::table('goods_receipts', fn (Blueprint $t) => $t->dropColumn(['bill_number', 'bill_amount', 'bill_path', 'bill_original_name']));
    }
};
