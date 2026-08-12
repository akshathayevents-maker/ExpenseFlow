<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            // Billing document identifier — distinct from the booking's own
            // id-derived reference (BK-####). Nullable: existing bookings
            // get a sensible default computed on read, not backfilled.
            $table->string('invoice_number', 50)->nullable()->unique()->after('id');
            $table->date('invoice_date')->nullable()->after('invoice_number');

            // Tax rates as of the last time the invoice was generated/saved.
            // Stored (not just computed at render time) so a previously
            // generated invoice always reproduces the same figures even if
            // the default rate changes later.
            $table->decimal('cgst_rate', 5, 2)->default(3.00)->after('total_amount');
            $table->decimal('sgst_rate', 5, 2)->default(3.00)->after('cgst_rate');
        });
    }

    public function down(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoice_date', 'cgst_rate', 'sgst_rate']);
        });
    }
};
