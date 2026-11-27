<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Booking-specific menu customisation (additive only):
 *  - replaces:      the package dish this line substitutes (change_type = replaced), e.g. Upma → Semiya
 *  - servings:      explicit serving count for this dish; null = the booking's guest count
 *  - charge_type:   how an extra is billed — included | per_person | fixed (null = included)
 *  - charge_amount: rate per serving (per_person) or flat amount (fixed)
 * The package (meal_plans) itself is never changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hall_booking_menu_items', function (Blueprint $t) {
            $t->string('replaces', 150)->nullable()->after('change_type');
            $t->unsignedInteger('servings')->nullable()->after('replaces');
            $t->string('charge_type', 12)->nullable()->after('servings');
            $t->decimal('charge_amount', 10, 2)->default(0)->after('charge_type');
        });
    }

    public function down(): void
    {
        Schema::table('hall_booking_menu_items', function (Blueprint $t) {
            $t->dropColumn(['replaces', 'servings', 'charge_type', 'charge_amount']);
        });
    }
};
