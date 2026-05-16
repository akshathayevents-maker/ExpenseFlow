<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->decimal('opening_balance', 12, 2)->default(0)->after('expense_count');
            $table->decimal('total_credit', 12, 2)->default(0)->after('opening_balance');
            $table->decimal('total_debit', 12, 2)->default(0)->after('total_credit');
            $table->decimal('closing_balance', 12, 2)->default(0)->after('total_debit');
            $table->timestamp('finalized_at')->nullable()->after('verified_at');
            $table->boolean('snapshot_captured')->default(false)->after('finalized_at');
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropColumn([
                'opening_balance', 'total_credit', 'total_debit',
                'closing_balance', 'finalized_at', 'snapshot_captured',
            ]);
        });
    }
};
