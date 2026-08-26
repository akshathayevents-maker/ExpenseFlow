<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendance_regularizations', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendance_regularizations', 'half_day_period')) {
                // first_half|second_half — same vocabulary as
                // leave_requests.half_day_period / employee_attendance.
                // Only meaningful (and required at the FormRequest layer)
                // when requested_status is 'half_day'.
                $table->string('half_day_period')->nullable()->after('requested_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendance_regularizations', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendance_regularizations', 'half_day_period')) {
                $table->dropColumn('half_day_period');
            }
        });
    }
};
