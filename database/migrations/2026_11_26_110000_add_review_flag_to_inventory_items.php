<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Items can be added on the fly from the item picker (purchase / material request / bill forms).
 * Items added by non-admins are usable at once but flagged needs_review so an admin can check
 * name, category and unit (and merge duplicates) later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $t) {
            $t->boolean('needs_review')->default(false)->after('status');
            $t->foreignId('created_by')->nullable()->after('needs_review')->constrained('users')->nullOnDelete();
            $t->index('needs_review');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $t) {
            $t->dropConstrainedForeignId('created_by');
            $t->dropIndex(['needs_review']);
            $t->dropColumn('needs_review');
        });
    }
};
