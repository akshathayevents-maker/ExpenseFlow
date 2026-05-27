<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_plan_items')) {
            Schema::create('purchase_plan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_plan_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
                $table->decimal('suggested_quantity', 12, 3);
                $table->decimal('estimated_unit_cost', 12, 2)->nullable();
                $table->enum('priority', ['urgent', 'high', 'normal', 'low'])->default('normal');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_plan_items');
    }
};
