<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->foreignId('inventory_category_id')->constrained()->restrictOnDelete();
            $table->string('unit'); // kg, gram, litre, ml, packet, piece, box, bundle, cylinder, dozen
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('minimum_stock', 12, 3)->default(0);
            $table->decimal('maximum_stock', 12, 3)->nullable();
            $table->decimal('average_cost', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->index('inventory_category_id');
            $table->index('status');
        });

        DB::statement("ALTER TABLE inventory_items ADD CONSTRAINT inventory_items_unit_check
            CHECK (unit IN ('kg','gram','litre','ml','packet','piece','box','bundle','cylinder','dozen'))");

        DB::statement("ALTER TABLE inventory_items ADD CONSTRAINT inventory_items_status_check
            CHECK (status IN ('active','inactive'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
