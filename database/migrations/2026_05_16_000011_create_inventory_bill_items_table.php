<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_upload_id')
                  ->constrained('inventory_bill_uploads')
                  ->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('item_name');
            $table->string('sku')->nullable();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->string('raw_extracted_text')->nullable();
            $table->boolean('imported')->default(false);
            $table->timestamps();

            $table->index('bill_upload_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_bill_items');
    }
};
