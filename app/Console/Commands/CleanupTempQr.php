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
        $cutoff  = now()->subHours(24);
        $deleted = 0;
        $cleared = 0;

        // Delete files from disk that are older than 24 h
        $files = Storage::disk('public')->files('temp-qr');
        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            if ($lastModified < $cutoff->timestamp) {
                Storage::disk('public')->delete($file);
                $deleted++;
            }
        }

        // Clear qr_file_path on DB rows whose QR no longer exists
        ExpenseRequest::whereNotNull('qr_file_path')
            ->each(function (ExpenseRequest $req) use (&$cleared) {
                if (! Storage::disk('public')->exists($req->qr_file_path)) {
                    $req->update(['qr_file_path' => null]);
                    $cleared++;
                }
            });

        $this->info("Deleted {$deleted} QR file(s). Cleared {$cleared} stale DB path(s).");

        return self::SUCCESS;
    }
}
