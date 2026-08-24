<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable: existing users have no known join date. NULL is treated
            // as "always employed" by payable-day/eligibility calculations, so
            // this is a safe, non-breaking addition for current records.
            if (!Schema::hasColumn('users', 'employment_start_date')) {
                $table->date('employment_start_date')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'employment_end_date')) {
                $table->date('employment_end_date')->nullable()->after('employment_start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(['employment_start_date', 'employment_end_date'], fn($c) => Schema::hasColumn('users', $c));
            if (!empty($cols)) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
