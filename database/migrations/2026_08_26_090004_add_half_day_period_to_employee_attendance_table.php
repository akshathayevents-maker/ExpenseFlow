<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendance', 'half_day_period')) {
                // first_half|second_half — same vocabulary as
                // leave_requests.half_day_period. Only meaningful when
                // status is one of half_day/half_day_leave/half_day_lop;
                // null for full-day statuses and for legacy half-day rows
                // written before this column existed (treated conservatively
                // as "unspecified" by conflict checks).
                $table->string('half_day_period')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendance', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendance', 'half_day_period')) {
                $table->dropColumn('half_day_period');
            }
        });
    }
};
