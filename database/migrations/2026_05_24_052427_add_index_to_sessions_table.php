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
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        Schema::table('sessions', function (Blueprint $table) {
            if (! $this->indexExists('sessions', 'sessions_user_id_index')) {
                $table->index('user_id');
            }
            if (! $this->indexExists('sessions', 'sessions_last_activity_index')) {
                $table->index('last_activity');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndexIfExists('sessions_user_id_index');
            $table->dropIndexIfExists('sessions_last_activity_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() !== 'pgsql') {
            return false;
        }
        return collect(DB::select(
            "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
            [$table, $index]
        ))->isNotEmpty();
    }
};
