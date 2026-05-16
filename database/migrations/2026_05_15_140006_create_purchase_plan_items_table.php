<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->decimal('suggested_quantity', 12, 3);
            $table->decimal('estimated_unit_cost', 12, 2)->nullable();
            $table->string('priority')->default('normal'); // urgent, high, normal, low
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE purchase_plan_items ADD CONSTRAINT purchase_plan_items_priority_check
            CHECK (priority IN ('urgent','high','normal','low'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_plan_items');
    }
};
