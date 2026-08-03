<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_request_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('event_request_menu_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_veg')->default(true);
            $table->decimal('price_per_person', 10, 2)->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_chef_recommended')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'is_active', 'display_order']);
            $table->index(['is_veg', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_request_menu_items');
    }
};
