<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_clients', function (Blueprint $table) {
            $table->string('email')->nullable()->after('mobile');
            $table->string('gst_number', 20)->nullable()->after('address');
            $table->text('remarks')->nullable()->after('gst_number');
        });
    }

    public function down(): void
    {
        Schema::table('meal_clients', function (Blueprint $table) {
            $table->dropColumn(['email', 'gst_number', 'remarks']);
        });
    }
};
