<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'paid_leave_days')) {
                // days_requested = paid_leave_days + lop_days, always. Not a
                // generated DB column (portability across the app's
                // Postgres/SQLite drivers) — LeaveService always computes
                // and persists all three together, in one INSERT.
                $table->decimal('paid_leave_days', 5, 1)->default(0)->after('days_requested');
            }
            if (!Schema::hasColumn('leave_requests', 'lop_days')) {
                $table->decimal('lop_days', 5, 1)->default(0)->after('paid_leave_days');
            }
            if (!Schema::hasColumn('leave_requests', 'lop_confirmed')) {
                // True only when the employee explicitly acknowledged the
                // LOP split on a second submit — set once, at creation,
                // never toggled afterward. Existing rows (all pre-LOP,
                // 100% paid) default false, which is correct: lop_days=0
                // means no confirmation was ever needed for them.
                $table->boolean('lop_confirmed')->default(false)->after('lop_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            foreach (['paid_leave_days', 'lop_days', 'lop_confirmed'] as $col) {
                if (Schema::hasColumn('leave_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
