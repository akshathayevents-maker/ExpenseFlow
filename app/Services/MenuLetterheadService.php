<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MenuLetterheadService
{
    /**
     * Return the path to a cached JPEG of page 1 of the letterhead PDF.
     *
     * JPEG is embedded by dompdf using raw file_get_contents — no GD extension
     * required. The cache file lives inside storage/app/ which is within
     * dompdf's chroot (base_path()).
     *
     * Returns null on any failure so the caller falls back to a white background.
     */
    public function jpegPath(): ?string
    {
        $pdfPath = config('menu.pdf_letterhead');

        if (! $pdfPath || ! file_exists($pdfPath)) {
            Log::warning('[MenuLetterhead] Source PDF not found', ['path' => $pdfPath]);
            return null;
        }

        // Must be inside base_path() — dompdf chroot restriction
        $cachePath = storage_path('app/menu_letterhead_cache.jpg');

        // Skip regeneration if cache is newer than source
        if (file_exists($cachePath) && filemtime($cachePath) >= filemtime($pdfPath)) {
            return $cachePath;
        }

        $gs = $this->ghostScriptBin();
        if (! $gs) {
            Log::warning('[MenuLetterhead] GhostScript binary not found');
            return null;
        }

        $dpi = (int) config('menu.pdf_letterhead_dpi', 150);
        $cmd = sprintf(
            '%s -dBATCH -dNOPAUSE -dFirstPage=1 -dLastPage=1'
            . ' -sDEVICE=jpeg -r%d -dJPEGQ=92'
            . ' -sOutputFile=%s %s 2>&1',
            escapeshellcmd($gs),
            $dpi,
            escapeshellarg($cachePath),
            escapeshellarg($pdfPath)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || ! file_exists($cachePath)) {
            Log::error('[MenuLetterhead] GhostScript conversion failed', [
                'exit_code' => $exitCode,
                'output'    => implode("\n", $output),
            ]);
            return null;
        }

        Log::info('[MenuLetterhead] JPEG cached', [
            'path'  => $cachePath,
            'bytes' => filesize($cachePath),
        ]);

        return $cachePath;
    }

    private function ghostScriptBin(): ?string
    {
        foreach (['gs', '/usr/bin/gs', '/usr/local/bin/gs'] as $bin) {
            $which = trim((string) shell_exec('which ' . escapeshellarg($bin) . ' 2>/dev/null'));
            if ($which) {
                return $which;
            }
        }
        return null;
    }
}
