<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            // booking_type is the authoritative discriminator — no boolean flags.
            // Values: hall_only | hall_food | food_only
            $table->string('booking_type')->default('hall_food')->after('hall_id');

            // Service location: required for food_only (client address, venue, etc.)
            // Nullable for hall types. Kept separate from notes for querying/display.
            $table->string('service_location', 255)->nullable()->after('booking_type');

            // Make hall_id nullable — food_only bookings have no hall.
            // Existing rows already have hall_id populated; this is safe.
            $table->foreignId('hall_id')->nullable()->change();
        });

        // Backfill: all existing records are hall+food bookings (the only type that existed).
        // We do NOT attempt to classify based on has_breakfast/lunch/dinner flags —
        // those flags indicate meal selection, not booking intent.
        DB::statement("UPDATE hall_bookings SET booking_type = 'hall_food' WHERE booking_type = 'hall_food' OR booking_type IS NULL OR booking_type = ''");
    }

    public function down(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_type', 'service_location']);
            $table->foreignId('hall_id')->nullable(false)->change();
        });
    }
};
