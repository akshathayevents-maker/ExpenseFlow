<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_types', 'is_paid')) {
                // Every leave type has always been implicitly "paid" — this
                // makes it explicit rather than assumed, default true
                // preserves existing behavior for existing rows.
                $table->boolean('is_paid')->default(true)->after('allow_half_day');
            }
            if (!Schema::hasColumn('leave_types', 'allow_carry_forward')) {
                $table->boolean('allow_carry_forward')->default(false)->after('is_paid');
            }
            if (!Schema::hasColumn('leave_types', 'max_carry_forward')) {
                $table->decimal('max_carry_forward', 5, 1)->nullable()->after('allow_carry_forward');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            foreach (['is_paid', 'allow_carry_forward', 'max_carry_forward'] as $col) {
                if (Schema::hasColumn('leave_types', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
