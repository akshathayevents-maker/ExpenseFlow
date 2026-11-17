<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Final (customised) menu of a hall order. Plan lines are kept with
 * change_type=plan; client changes are added / modified / removed (removed lines
 * stay with is_removed=true so the original plan remains visible).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hall_booking_menu_items')) {
            Schema::create('hall_booking_menu_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('hall_booking_id')->constrained()->cascadeOnDelete();
                $t->string('name', 150);
                $t->string('meal_type', 20)->nullable();
                $t->string('change_type', 12)->default('plan');   // plan | added | modified | removed
                $t->boolean('is_removed')->default(false);
                $t->string('notes', 300)->nullable();             // e.g. "extra paneer"
                $t->unsignedInteger('sort_order')->default(0);
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index('hall_booking_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_booking_menu_items');
    }
};
