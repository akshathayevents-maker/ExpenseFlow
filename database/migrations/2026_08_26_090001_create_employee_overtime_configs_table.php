<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overtime redesign (Part 1): per-employee configurable OT multipliers,
 * chosen by Admin/Manager AT APPROVAL time — replacing the old design where
 * a single company-wide date-category multiplier was applied automatically
 * at request-creation time.
 *
 * One row per employee (nullable/absent = implicit default). Deliberately
 * NOT backfilled for every existing employee — an absent row means "use the
 * implicit default" (allowed_multipliers=[1.5], default_multiplier=1.5),
 * which is simpler and carries zero data-migration risk. This "row-or-
 * implicit-default" logic lives in ONE place:
 * EmployeeOvertimeConfig::allowedMultipliersFor()/defaultMultiplierFor().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_overtime_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // JSON array of decimals the admin has made selectable for this
            // employee's OT approvals, e.g. [1.0, 1.5, 2.0].
            $table->json('allowed_multipliers');

            // Must be a member of allowed_multipliers — enforced in
            // SetLeavePolicyRequest-style validation at the app layer, not a
            // DB constraint (JSON membership isn't practically enforceable
            // in a portable CHECK constraint across sqlite/pgsql).
            $table->decimal('default_multiplier', 3, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_overtime_configs');
    }
};
