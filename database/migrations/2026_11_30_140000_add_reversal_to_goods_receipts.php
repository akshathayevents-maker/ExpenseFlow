<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A goods receipt can be undone by an admin (stock reversed, purchase reopened); the receipt row is kept as a record. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $t) {
            $t->timestamp('reversed_at')->nullable();
            $t->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('reversal_reason', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $t) {
            $t->dropConstrainedForeignId('reversed_by');
            $t->dropColumn(['reversed_at', 'reversal_reason']);
        });
    }
};
