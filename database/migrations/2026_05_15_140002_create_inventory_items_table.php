<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku')->nullable()->unique();
                $table->foreignId('inventory_category_id')->constrained()->restrictOnDelete();
                $table->enum('unit', ['kg', 'gram', 'litre', 'ml', 'packet', 'piece', 'box', 'bundle', 'cylinder', 'dozen']);
                $table->decimal('current_stock', 12, 3)->default(0);
                $table->decimal('minimum_stock', 12, 3)->default(0);
                $table->decimal('maximum_stock', 12, 3)->nullable();
                $table->decimal('average_cost', 12, 2)->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->index('inventory_category_id');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
