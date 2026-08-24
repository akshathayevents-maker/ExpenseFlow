<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->date('holiday_date')->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('holiday_date');
            });
        }

        // Weekly-off configuration lives in the existing `settings` table
        // (reused, not duplicated — see app/Models/Setting.php) rather than a
        // new company_settings table. Default: Sunday only. Extensible later
        // to per-employee offs without a schema change (value is JSON).
        DB::table('settings')->insertOrIgnore([
            'key'        => 'weekly_off_days',
            'value'      => '[0]',
            'type'       => 'json',
            'group'      => 'employee_self_service',
            'label'      => 'Weekly Off Days (0=Sunday .. 6=Saturday)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'weekly_off_days')->delete();
        Schema::dropIfExists('holidays');
    }
};
