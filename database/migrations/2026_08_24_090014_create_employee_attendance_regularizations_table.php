<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_attendance_regularizations')) {
            Schema::create('employee_attendance_regularizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('attendance_date');

                // Restricted to present|half_day at the application layer
                // (FormRequest + service) — NOT enforced by a DB CHECK, to
                // stay consistent with how employee_attendance.status itself
                // is a plain string column with no DB-level enum. Excludes
                // leave/half_day_leave (leave must go through the LeaveRequest
                // workflow, never this shortcut) and holiday/weekly_off
                // (system-derived, never employee-settable) and absent (no
                // legitimate self-correction use case).
                $table->string('requested_status');

                $table->text('reason');

                // pending|approved|rejected|cancelled — same vocabulary and
                // lifecycle shape as employee_overtime.request_status.
                $table->string('request_status')->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_note')->nullable();

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

                $table->timestamps();

                // Deliberately NOT unique on (user_id, attendance_date) — a
                // rejected/cancelled request must not block a later
                // resubmission for the same date. "Only one ACTIVE/PENDING
                // request per employee/date" is an application-level guard
                // (EmployeeAttendanceService::createRegularization), not a DB
                // constraint, since "active" depends on request_status.
                $table->index(['user_id', 'attendance_date']);
                $table->index('request_status');
                $table->index('attendance_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_regularizations');
    }
};
