<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('hall_bookings', 'hall_cost')) {
                $table->decimal('hall_cost', 12, 2)->default(0)->after('advance_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('hall_bookings', 'hall_cost')) {
                $table->dropColumn('hall_cost');
            }
        });
    }
};
