<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Postgres-only syntax (production driver). The test suite runs on
        // SQLite in-memory, which has no ALTER COLUMN ... SET DEFAULT — the
        // column default doesn't affect test correctness there since tests
        // always insert values explicitly, so it's safely skipped.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE hall_bookings ALTER COLUMN cgst_rate SET DEFAULT 2.50');
            DB::statement('ALTER TABLE hall_bookings ALTER COLUMN sgst_rate SET DEFAULT 2.50');
        }

        // Bring forward bookings still at the old 3% default with no invoice
        // issued yet. Bookings that already have an invoice_number are left
        // untouched — their tax rate is part of a finalized document.
        DB::table('hall_bookings')
            ->whereNull('invoice_number')
            ->where('cgst_rate', 3.00)
            ->where('sgst_rate', 3.00)
            ->update(['cgst_rate' => 2.50, 'sgst_rate' => 2.50]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE hall_bookings ALTER COLUMN cgst_rate SET DEFAULT 3.00');
            DB::statement('ALTER TABLE hall_bookings ALTER COLUMN sgst_rate SET DEFAULT 3.00');
        }

        DB::table('hall_bookings')
            ->whereNull('invoice_number')
            ->where('cgst_rate', 2.50)
            ->where('sgst_rate', 2.50)
            ->update(['cgst_rate' => 3.00, 'sgst_rate' => 3.00]);
    }
};
