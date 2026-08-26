<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ── Half-day overlay segments ────────────────────────────────────────────
 *
 * employee_attendance holds exactly one row per (user_id, attendance_date)
 * — a hard unique constraint that cannot represent two INDEPENDENTLY
 * sourced half-day facts for the same date (e.g. AM self-marked present +
 * PM approved half-day leave). Until now, LeaveService::writeOneAttendanceRow()
 * and (implicitly) regularization approval simply skipped writing anything
 * for the "second" half in that situation — the day's own financial effect
 * (ledger entry / LOP) was still correct, but PayableDaysCalculator, which
 * only ever reads the single employee_attendance row per date, undercounted
 * the day because it never saw the second half at all.
 *
 * This table is the SMALLEST correct fix for that gap: it holds only the
 * "second half" fact for a date that already has a complementary-half
 * employee_attendance row — never a full parallel representation of every
 * attendance date. The overwhelming majority of dates (unsplit, or where
 * only one half's fact ever exists) never get a row here at all.
 *
 * Invariant enforced at the application layer (the one code path that ever
 * writes here — see LeaveService::writeOneAttendanceRow() and
 * EmployeeAttendanceService::approveRegularization()): a row is only ever
 * created for a date that ALREADY has an employee_attendance row on the
 * OPPOSITE, non-conflicting half (per AttendanceConflictChecker). Two
 * segment rows for the same (user_id, attendance_date) would mean three
 * independent halves of one day, which is nonsensical, so `period` is part
 * of the unique key purely as a defensive backstop, not because more than
 * one segment row per date is ever an expected state.
 *
 * NO BACKFILL IS NEEDED: the punted skip-behavior this table replaces was
 * added in the immediately preceding session and, per that session's own
 * account, has zero real usage yet (the code path always just `return`ed
 * without writing anything). There is therefore no historical data of this
 * shape to migrate — every existing employee_attendance row remains exactly
 * as-is and continues to be read as it always has.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_attendance_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');

            // first_half|second_half only — a segment row is by definition
            // half a day; a full-day fact never needs this table.
            $table->string('period');

            // present|leave|lop|absent — the same vocabulary as
            // employee_attendance's non-compound statuses. Compound
            // half_day_* statuses don't apply here: `period` already encodes
            // "this row is half a day," so status stays simple.
            $table->string('status');

            // self|admin|leave_approval|regularization — mirrors
            // employee_attendance.source semantics for this independently
            // sourced half.
            $table->string('source');

            $table->foreignId('leave_request_id')->nullable()->constrained('leave_requests')->nullOnDelete();
            $table->foreignId('regularization_id')->nullable()->constrained('employee_attendance_regularizations')->nullOnDelete();

            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at')->nullable();

            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable();
            $table->text('correction_reason')->nullable();
            $table->string('previous_status')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'attendance_date', 'period']);
            $table->index('attendance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_segments');
    }
};
