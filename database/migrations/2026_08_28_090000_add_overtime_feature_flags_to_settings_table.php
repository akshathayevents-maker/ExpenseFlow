<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Overtime redesign (Part 4 — "pause employee requesting" + admin-granted
 * allowances): two new runtime-configurable flags, following the EXACT
 * existing `settings` key/value/type pattern already used for
 * `ot_multipliers` / `standard_working_hours_per_day` — no new config
 * table, no `.env` constant.
 *
 * `employee_overtime_requests_enabled` (boolean): gates ONLY the employee
 * self-request path (EmployeeOvertimePolicy::create() / the
 * employee.overtime.create+store routes). Defaults to `false` per the
 * current business requirement — employees temporarily cannot submit new
 * OT requests. Flipping this back to `true` (via Setting::set(), e.g. the
 * existing admin Settings page or a one-line artisan tinker/seeder call)
 * re-enables the entire existing request/approval/calculation flow with
 * ZERO code changes, since nothing else in that flow was touched.
 *
 * Admin/Manager approval of already-pending requests, and the admin
 * "record historical OT for another employee" flow (origin=admin_recorded,
 * already existed before this migration), are NOT gated by this flag.
 *
 * `overtime_allowance_mode` (string enum 'multiple'|'single'): governs
 * whether an admin may record more than one admin_recorded OT entry per
 * employee per calendar month (pay period). Defaults to 'multiple' — the
 * pre-existing, unrestricted behavior — so this migration changes nothing
 * about today's behavior until an admin explicitly switches it to
 * 'single' from the Settings page.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'        => 'employee_overtime_requests_enabled',
            'value'      => '0',
            'type'       => 'boolean',
            'group'      => 'employee_self_service',
            'label'      => 'Employee Overtime Requests Enabled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->insertOrIgnore([
            'key'        => 'overtime_allowance_mode',
            // 'multiple' (default, pre-existing behavior) | 'single'
            'value'      => 'multiple',
            'type'       => 'string',
            'group'      => 'employee_self_service',
            'label'      => 'Admin Overtime Allowance Mode (single/multiple per pay period)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Deliberately NOT deleting these rows on rollback — mirrors the
        // existing employee_overtime migration's down() rationale: harmless
        // if left behind, and insertOrIgnore in up() means we can't safely
        // tell whether we created them or an admin had already configured
        // them.
    }
};
