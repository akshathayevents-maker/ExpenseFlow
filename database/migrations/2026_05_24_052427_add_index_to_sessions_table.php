<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // user_id: speeds up session lookup when re-authing via remember token
            if (! $this->indexExists('sessions', 'sessions_user_id_index')) {
                $table->index('user_id');
            }
            // last_activity: critical for the session GC query that deletes expired rows.
            // With SESSION_LIFETIME=43200 (30 days), without this index the GC query
            // does a full table scan on every request (lottery 2/100).
            if (! $this->indexExists('sessions', 'sessions_last_activity_index')) {
                $table->index('last_activity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndexIfExists('sessions_user_id_index');
            $table->dropIndexIfExists('sessions_last_activity_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(DB::select(
            "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
            [$table, $index]
        ))->isNotEmpty();
    }
};
