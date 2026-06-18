<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_drafts', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);
            $table->string('venue', 255)->nullable();
            $table->date('event_date')->nullable();
            $table->unsignedSmallInteger('people_count')->nullable();

            // Full menu content stored as JSON snapshot — no join needed.
            // Structure: { breakfast:[], lunch:[], dinner:[], evening_snacks:[] }
            // Each item: { id, item_en, item_ta, category_key, category_en, category_ta }
            $table->json('content');

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_drafts');
    }
};
