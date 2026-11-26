<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Is anything processing the queue? A running `queue:work` touches a heartbeat on every loop
 * (registered in AppServiceProvider), so the UI can say "worker not running" instead of pretending
 * a batch is generating.
 */
class QueueHealth
{
    public const HEARTBEAT_KEY = 'queue:worker-heartbeat';

    public const ALIVE_SECONDS = 120;

    public static function beat(): void
    {
        Cache::put(self::HEARTBEAT_KEY, now()->getTimestamp(), now()->addMinutes(10));
    }

    /** @return array{connection: string, sync: bool, worker_seen: ?Carbon, worker_alive: bool, pending: int, oldest_pending_minutes: ?int, failed: int, ready: bool} */
    public function status(): array
    {
        $conn = (string) config('queue.default');
        $sync = $conn === 'sync';
        $beat = Cache::get(self::HEARTBEAT_KEY);
        $seen = $beat ? Carbon::createFromTimestamp($beat) : null;
        $alive = $sync || ($seen && $seen->gt(now()->subSeconds(self::ALIVE_SECONDS)));
        $pending = 0;
        $oldest = null;
        if ($conn === 'database' && Schema::hasTable('jobs')) {
            $pending = DB::table('jobs')->count();
            $min = DB::table('jobs')->min('available_at');
            $oldest = $min ? (int) floor((now()->getTimestamp() - (int) $min) / 60) : null;
        }
        $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        return [
            'connection' => $conn, 'sync' => $sync, 'worker_seen' => $seen, 'worker_alive' => (bool) $alive,
            'pending' => $pending, 'oldest_pending_minutes' => $oldest, 'failed' => $failed, 'ready' => (bool) $alive,
        ];
    }

    public static function workerCommand(): string
    {
        return 'php artisan queue:work --timeout=600 --tries=3';
    }
}
