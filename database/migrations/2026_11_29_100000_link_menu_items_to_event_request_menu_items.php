<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu Composer items (menu_items) mirror the Event Request menu (event_request_menu_items): one row each, linked here.
 * Names and categories stay in step both ways (App\Services\MenuItemSync); Tamil names live on menu_items,
 * price lives on event_request_menu_items; each page keeps its own active flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $t) {
            $t->foreignId('event_request_menu_item_id')->nullable()->after('id')
                ->unique()->constrained('event_request_menu_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', fn (Blueprint $t) => $t->dropConstrainedForeignId('event_request_menu_item_id'));
    }
};
