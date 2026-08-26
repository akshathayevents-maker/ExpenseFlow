<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overtime redesign (Part 3): under the new design, `calculated_amount`
 * (existing column) holds the SYSTEM-computed amount (hourly_rate_snapshot
 * x hours x chosen multiplier), while `approved_amount` is the FINAL amount
 * that actually applies — equal to `calculated_amount` unless the
 * approver supplied a manual override, in which case it's the override
 * value. Keeping both lets the UI show "Calculated Amount" vs "Final
 * Approved Amount" distinctly, and lets Part 5's historical-safety test
 * assert the frozen amount independent of which path produced it.
 *
 * `used_manual_override` records which of the two happened, without the
 * caller having to infer it by comparing the two decimals (which would
 * break if a manual override happened to exactly equal the calculated
 * amount).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_overtime', function (Blueprint $table) {
            $table->decimal('approved_amount', 12, 2)->nullable()->after('calculated_amount');
            $table->boolean('used_manual_override')->default(false)->after('approved_amount');
        });
    }

    public function down(): void
    {
        Schema::table('employee_overtime', function (Blueprint $table) {
            $table->dropColumn(['approved_amount', 'used_manual_override']);
        });
    }
};
