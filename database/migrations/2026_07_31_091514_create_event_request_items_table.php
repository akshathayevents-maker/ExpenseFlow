<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_request_id')->constrained('event_requests')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained('event_request_menu_items')->nullOnDelete();

            // Snapshots — menu items can change price/name/be retired later;
            // the request must always reflect what the client actually selected.
            $table->string('name_snapshot');
            $table->string('category_name_snapshot')->nullable();
            $table->boolean('is_veg_snapshot')->default(true);
            $table->decimal('price_per_person_snapshot', 10, 2)->default(0);

            $table->timestamps();

            $table->index('event_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_request_items');
    }
};
