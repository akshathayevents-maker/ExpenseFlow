<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
                $table->enum('type', ['purchase', 'usage', 'adjustment', 'wastage', 'transfer']);
                $table->decimal('quantity', 12, 3);
                $table->decimal('balance_before', 12, 3);
                $table->decimal('balance_after', 12, 3);
                $table->decimal('unit_cost', 12, 2)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();

                $table->index('inventory_item_id');
                $table->index('type');
                $table->index(['reference_type', 'reference_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
