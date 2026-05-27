<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string'); // string, boolean, integer, json
                $table->string('group')->default('general'); // general, inventory, notifications
                $table->string('label')->nullable();
                $table->timestamps();
            });

            // Seed defaults — use insertOrIgnore so re-runs are safe
            DB::table('settings')->insertOrIgnore([
                ['key' => 'app_name',              'value' => 'ExpenseFlow',    'type' => 'string',  'group' => 'general',      'label' => 'Application Name',          'created_at' => now(), 'updated_at' => now()],
                ['key' => 'currency_symbol',       'value' => '₹',             'type' => 'string',  'group' => 'general',      'label' => 'Currency Symbol',           'created_at' => now(), 'updated_at' => now()],
                ['key' => 'timezone',              'value' => 'Asia/Kolkata',   'type' => 'string',  'group' => 'general',      'label' => 'Timezone',                  'created_at' => now(), 'updated_at' => now()],
                ['key' => 'low_stock_threshold',   'value' => '10',             'type' => 'integer', 'group' => 'inventory',    'label' => 'Default Low Stock Threshold','created_at' => now(), 'updated_at' => now()],
                ['key' => 'max_upload_mb',         'value' => '5',              'type' => 'integer', 'group' => 'general',      'label' => 'Max Upload Size (MB)',      'created_at' => now(), 'updated_at' => now()],
                ['key' => 'notify_low_stock',      'value' => '1',              'type' => 'boolean', 'group' => 'notifications','label' => 'Notify on Low Stock',       'created_at' => now(), 'updated_at' => now()],
                ['key' => 'notify_expense_status', 'value' => '1',              'type' => 'boolean', 'group' => 'notifications','label' => 'Notify on Expense Status',  'created_at' => now(), 'updated_at' => now()],
                ['key' => 'hall_name',             'value' => 'Main Hall',      'type' => 'string',  'group' => 'general',      'label' => 'Hall / Kitchen Name',       'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
