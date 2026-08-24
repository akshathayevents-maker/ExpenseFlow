<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_attendance')) {
            Schema::create('employee_attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('attendance_date');

                // present|half_day|leave|half_day_leave|absent
                // Deliberately NO 'unmarked' value — absence of a row for a
                // date IS "unmarked". Holiday/weekly_off are computed at read
                // time (see AttendanceCalendarService), never stored here.
                $table->string('status');

                $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('marked_at')->nullable();

                // self (employee marked today) | admin (manual entry/correction)
                // | system (end-of-day absentee job) | leave_approval (written by
                // leave approval — see leave_request_id below)
                $table->string('source');

                $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('corrected_at')->nullable();
                $table->text('correction_reason')->nullable();
                // Snapshot of the status immediately before a correction, so the
                // audit trail shows what changed without joining audit_logs.
                $table->string('previous_status')->nullable();

                $table->timestamps();

                $table->unique(['user_id', 'attendance_date']);
                $table->index('attendance_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendance');
    }
};
