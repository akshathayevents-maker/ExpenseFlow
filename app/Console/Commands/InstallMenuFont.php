<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallMenuFont extends Command
{
    protected $signature   = 'menu:install-font {--force : Re-download even if font already exists}';
    protected $description = 'Download and install the Noto Sans Tamil font for PDF generation';

    // Google Fonts static CDN — Noto Sans Tamil Regular + Bold
    private const FONTS = [
        'NotoSansTamil-Regular.ttf' => 'https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoSansTamil/NotoSansTamil-Regular.ttf',
        'NotoSansTamil-Bold.ttf'    => 'https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoSansTamil/NotoSansTamil-Bold.ttf',
    ];

    public function handle(): int
    {
        $dir = storage_path('fonts');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach (self::FONTS as $filename => $url) {
            $dest = $dir . '/' . $filename;

            if (file_exists($dest) && ! $this->option('force')) {
                $this->line("  <comment>Already installed:</comment> {$filename}");
                continue;
            }

            $this->line("  Downloading <info>{$filename}</info>…");

            $context = stream_context_create([
                'http' => [
                    'timeout'       => 30,
                    'user_agent'    => 'Mozilla/5.0 (Laravel Menu Composer)',
                    'ignore_errors' => false,
                ],
                'ssl'  => ['verify_peer' => true],
            ]);

            $data = @file_get_contents($url, false, $context);

            if ($data === false || strlen($data) < 1000) {
                $this->error("  Failed to download {$filename}. Check your internet connection.");
                $this->line("  Manual download: {$url}");
                $this->line("  Save to: {$dest}");
                return self::FAILURE;
            }

            file_put_contents($dest, $data);
            $this->info("  ✓ Installed: {$filename} (" . number_format(strlen($data) / 1024, 1) . ' KB)');
        }

        $this->newLine();
        $this->info('Tamil font ready. PDF generation will now render Tamil text correctly.');
        $this->line('Font location: ' . $dir);

        return self::SUCCESS;
    }
}
