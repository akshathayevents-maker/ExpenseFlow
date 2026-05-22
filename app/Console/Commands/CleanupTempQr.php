<?php

namespace App\Console\Commands;

use App\Models\ExpenseRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempQr extends Command
{
    protected $signature   = 'app:cleanup-temp-qr';
    protected $description = 'Delete temp QR files older than 24 hours and clear their DB paths';

    public function handle(): int
    {
        $cutoff  = now()->subHours(48);
        $deleted = 0;
        $cleared = 0;

        // Collect all paths still referenced in the DB so we never delete them
        $activePaths = ExpenseRequest::whereNotNull('qr_file_path')
            ->pluck('qr_file_path')
            ->flip(); // O(1) isset lookup

        // Only purge orphaned files from the legacy temp-qr folder (new uploads
        // go to qr-codes/{id}/ and must never be auto-deleted)
        $files = Storage::disk('public')->files('temp-qr');
        foreach ($files as $file) {
            if (isset($activePaths[$file])) {
                continue; // still referenced — leave it alone
            }
            $lastModified = Storage::disk('public')->lastModified($file);
            if ($lastModified < $cutoff->timestamp) {
                Storage::disk('public')->delete($file);
                $deleted++;
            }
        }

        // Clear stale DB references whose file no longer exists on disk
        ExpenseRequest::whereNotNull('qr_file_path')
            ->each(function (ExpenseRequest $req) use (&$cleared) {
                if (! Storage::disk('public')->exists($req->qr_file_path)) {
                    $req->update(['qr_file_path' => null]);
                    $cleared++;
                }
            });

        $this->info("Deleted {$deleted} orphaned temp QR file(s). Cleared {$cleared} stale DB path(s).");

        return self::SUCCESS;
    }
}
