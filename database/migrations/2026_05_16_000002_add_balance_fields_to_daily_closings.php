<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_closings', 'opening_balance')) {
                $table->decimal('opening_balance', 12, 2)->default(0)->after('expense_count');
            }
            if (!Schema::hasColumn('daily_closings', 'total_credit')) {
                $table->decimal('total_credit', 12, 2)->default(0)->after('opening_balance');
            }
            if (!Schema::hasColumn('daily_closings', 'total_debit')) {
                $table->decimal('total_debit', 12, 2)->default(0)->after('total_credit');
            }
            if (!Schema::hasColumn('daily_closings', 'closing_balance')) {
                $table->decimal('closing_balance', 12, 2)->default(0)->after('total_debit');
            }
            if (!Schema::hasColumn('daily_closings', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('daily_closings', 'snapshot_captured')) {
                $table->boolean('snapshot_captured')->default(false)->after('finalized_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $cols = array_filter(
                ['opening_balance', 'total_credit', 'total_debit', 'closing_balance', 'finalized_at', 'snapshot_captured'],
                fn($c) => Schema::hasColumn('daily_closings', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
