<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_overtime')) {
            Schema::create('employee_overtime', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                // The calendar date the OT was worked. A shift crossing midnight
                // is attributed entirely to this start date — never split into
                // two records (locked decision #6).
                $table->date('ot_date');
                $table->decimal('hours', 4, 2);

                // weekday|weekend|holiday — derived from the existing `holidays`
                // table + settings.weekly_off_days and SNAPSHOTTED here at
                // submission time. A holiday added/removed later must never
                // retroactively reclassify historical OT (locked decision #10).
                $table->string('category');

                // ── Server-computed financial snapshot ──────────────────────
                // The employee submits ONLY ot_date/hours/reason. These three
                // columns are populated by the server (never trusted from the
                // client) using the salary effective ON ot_date (via
                // User::currentSalaryAsOf(), NOT today's salary) and the
                // company-level standard-hours/multiplier settings. Nullable
                // because a value is written as a preview at submission time
                // but is only ever authoritative/frozen once request_status
                // becomes 'approved' — after that point these three columns
                // must never be recalculated, even if salary or settings
                // change afterward (locked decision #11).
                $table->decimal('hourly_rate_snapshot', 10, 2)->nullable();
                $table->decimal('rate_multiplier', 3, 2)->nullable();
                $table->decimal('calculated_amount', 12, 2)->nullable();

                $table->text('reason');

                // pending|approved|rejected|cancelled — single axis. No
                // separate payroll_status yet: there is no payroll engine to
                // track a second lifecycle against (unlike employee_advances'
                // payment_status, which tracks a real, immediate v1 fact).
                $table->string('request_status')->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_note')->nullable();

                // Set once a future payroll process has consumed this approved
                // OT row. NULL = not yet paid/picked up. This is the minimal
                // marker needed to support the future query "approved OT for a
                // period that has not already been paid" (locked decision #17)
                // without inventing a full payroll_status/period schema now.
                $table->timestamp('paid_at')->nullable();

                // employee_request | admin_recorded (mirrors employee_advances.origin)
                $table->string('origin');
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

                $table->timestamps();

                // Deliberately NOT unique — an employee may legitimately log
                // multiple separate OT periods on the same date (locked
                // decision #3). Exact-duplicate prevention is an application-
                // layer concern, not a DB constraint.
                $table->index(['user_id', 'ot_date']);
                $table->index('ot_date');
                $table->index('request_status');
                // Serves the future payroll pickup query directly.
                $table->index(['request_status', 'paid_at']);
            });
        }

        // Company-level OT configuration — reuses the existing `settings`
        // table (key/value), same pattern as weekly_off_days in the holidays
        // migration. No new configuration table.
        DB::table('settings')->insertOrIgnore([
            'key'        => 'standard_working_hours_per_day',
            'value'      => '8.00',
            'type'       => 'string', // Setting::get() has no 'decimal' type; read as string, cast to float at the call site
            'group'      => 'employee_self_service',
            'label'      => 'Standard Working Hours Per Day',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->insertOrIgnore([
            'key'        => 'ot_multipliers',
            // JSON_PRESERVE_ZERO_FRACTION — without it, json_encode(2.0)
            // serializes as "2" (whole-number floats lose their fractional
            // marker), so json_decode() later hands back int 2, not float
            // 2.0. Harmless for arithmetic (PHP coerces either way) but
            // semantically these are rate multipliers, not counts.
            'value'      => json_encode(['weekday' => 1.5, 'weekend' => 2.0, 'holiday' => 2.0], JSON_PRESERVE_ZERO_FRACTION),
            'type'       => 'json',
            'group'      => 'employee_self_service',
            'label'      => 'Overtime Rate Multipliers',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Deliberately NOT deleting the 'standard_working_hours_per_day' /
        // 'ot_multipliers' settings rows here. up() uses insertOrIgnore, so
        // if either key already existed before this migration ran (e.g. an
        // admin configured it manually, or a prior partial run inserted it),
        // an unconditional delete in down() would destroy data this
        // migration never created — there's no reliable way to tell "did we
        // insert this row" after the fact without a second tracking column.
        // The settings rows are harmless if left behind; the actual schema
        // change (the table) is still fully reversed below.
        Schema::dropIfExists('employee_overtime');
    }
};
