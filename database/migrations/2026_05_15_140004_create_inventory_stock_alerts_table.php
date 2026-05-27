<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_stock_alerts')) {
            Schema::create('inventory_stock_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->string('alert_type'); // low_stock, out_of_stock
                $table->decimal('stock_at_alert', 12, 3);
                $table->boolean('is_resolved')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['inventory_item_id', 'is_resolved']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_alerts');
    }
};
