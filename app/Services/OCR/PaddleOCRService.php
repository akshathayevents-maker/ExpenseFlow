<?php

namespace App\Services\OCR;

use App\Models\InventoryBillUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaddleOCRService
{
    private string $scriptPath;
    private string $pythonBin;
    private int    $timeout;

    public function __construct()
    {
        $this->scriptPath = config('ocr.script_path', storage_path('app/ocr/invoice_ocr.py'));
        $this->pythonBin  = config('ocr.python_bin', 'python3');
        $this->timeout    = config('ocr.timeout', 120);
    }

    /**
     * Run PaddleOCR on the bill file. Returns raw OCR result array.
     *
     * Shape on success:
     *   ['success' => true, 'raw_lines' => [...], 'full_text' => '...', 'confidence' => 0.9, 'line_count' => N]
     *
     * Shape on failure:
     *   ['success' => false, 'error' => '...', 'raw_lines' => [], 'full_text' => '']
     */
    public function extractText(InventoryBillUpload $bill): array
    {
        if (! file_exists($this->scriptPath)) {
            return $this->fail(
                'OCR script missing. Run: cp storage/app/ocr/invoice_ocr.py to the right location, ' .
                'then: pip install paddleocr paddlepaddle'
            );
        }

        $filePath = Storage::disk('private')->path($bill->stored_path);

        if (! file_exists($filePath)) {
            return $this->fail("Bill file not found on disk: {$filePath}");
        }

        $lang = config('ocr.language', 'en');
        $cmd  = implode(' ', [
            escapeshellcmd($this->pythonBin),
            escapeshellarg($this->scriptPath),
            escapeshellarg($filePath),
            escapeshellarg($lang),
        ]);

        [$stdout, $exitCode] = $this->runProcess($cmd);

        if ($exitCode !== 0 && empty($stdout)) {
            return $this->fail('OCR process failed with no output. Is PaddleOCR installed? Run: pip install paddleocr paddlepaddle');
        }

        // PaddlePaddle prints debug noise before the JSON; find the last valid JSON object
        $json = $this->extractJsonFromOutput($stdout);

        if ($json === null) {
            Log::warning('PaddleOCR: no valid JSON in output', [
                'bill_id' => $bill->id,
                'output'  => substr($stdout, -500),
            ]);
            return $this->fail('OCR returned no parseable data. Raw output: ' . substr($stdout, -200));
        }

        if (! ($json['success'] ?? false)) {
            $err = $json['error'] ?? 'Unknown OCR error';
            Log::warning('PaddleOCR: script reported failure', ['bill_id' => $bill->id, 'error' => $err]);
            return $this->fail($err);
        }

        Log::info('PaddleOCR: extracted', [
            'bill_id'    => $bill->id,
            'lines'      => $json['line_count'] ?? 0,
            'confidence' => $json['confidence']  ?? 0,
        ]);

        return $json;
    }

    // ── Internals ──────────────────────────────────────────────────────────────

    private function runProcess(string $cmd): array
    {
        $descriptors = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $proc = proc_open($cmd, $descriptors, $pipes);

        if (! is_resource($proc)) {
            return ['', 1];
        }

        // Read with timeout via stream_select
        $stdout = '';
        $stderr = '';
        $start  = time();

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (true) {
            $read = [$pipes[1], $pipes[2]];
            $w    = $e = [];

            if (@stream_select($read, $w, $e, 1) === false) break;

            foreach ($read as $stream) {
                $chunk = fread($stream, 8192);
                if ($chunk !== false) {
                    if ($stream === $pipes[1]) $stdout .= $chunk;
                    else                       $stderr .= $chunk;
                }
            }

            if (feof($pipes[1]) && feof($pipes[2])) break;
            if ((time() - $start) >= $this->timeout) {
                proc_terminate($proc);
                Log::error('PaddleOCR: process timed out', ['timeout' => $this->timeout]);
                break;
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        if ($stderr) {
            Log::debug('PaddleOCR stderr', ['output' => substr($stderr, -500)]);
        }

        return [$stdout, $exitCode];
    }

    private function extractJsonFromOutput(string $output): ?array
    {
        // Walk lines in reverse — the JSON is always the last line in the script
        $lines = array_reverse(explode("\n", $output));
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
                $decoded = json_decode($line, true);
                if (is_array($decoded)) return $decoded;
            }
        }
        return null;
    }

    private function fail(string $message): array
    {
        return [
            'success'    => false,
            'error'      => $message,
            'raw_lines'  => [],
            'full_text'  => '',
            'confidence' => 0.0,
            'line_count' => 0,
        ];
    }
}
