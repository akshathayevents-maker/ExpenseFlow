<?php

namespace App\Services;

use App\Models\InventoryBillUpload;
use App\Services\OCR\InvoiceParserService;
use App\Services\OCR\PaddleOCRService;
use Illuminate\Support\Facades\Log;

/**
 * Facade for the full OCR pipeline:
 *   File on disk → PaddleOCRService (Python) → InvoiceParserService (PHP) → structured array
 *
 * Works fully offline — no external API required.
 *
 * To enable OCR install PaddleOCR:
 *   pip install paddleocr paddlepaddle pillow
 *   pip install pdf2image && sudo apt install poppler-utils  # for PDF support
 *
 * Configure in .env:
 *   OCR_PYTHON_BIN=python3     # or full path, e.g. /home/user/.venv/bin/python3
 *   OCR_LANGUAGE=en            # en | ta | ch | etc.
 *   OCR_TIMEOUT=120
 */
class InvoiceOCRService
{
    public function __construct(
        private PaddleOCRService   $paddle,
        private InvoiceParserService $parser,
    ) {}

    /**
     * Extract invoice data from an uploaded bill file.
     *
     * Returns array compatible with InventoryBillController::store() expectations:
     * {
     *   vendor_name, invoice_number, invoice_date, gst_number,
     *   subtotal, tax_amount, total_amount,
     *   items: [{item_name, quantity, unit, unit_price, tax_percent, total}],
     *   ocr_provider,
     *   ocr_message   // null on success, human-readable string on fallback
     * }
     */
    public function extract(InventoryBillUpload $bill): array
    {
        // ── Step 1: Run PaddleOCR ──────────────────────────────────────────
        $ocrResult = $this->paddle->extractText($bill);

        if (! ($ocrResult['success'] ?? false)) {
            $msg = $ocrResult['error'] ?? 'OCR processing failed.';
            Log::warning('InvoiceOCRService: OCR failed', ['bill_id' => $bill->id, 'error' => $msg]);
            return $this->emptyExtraction($msg);
        }

        // ── Step 2: Parse raw OCR lines into structured data ───────────────
        $parsed = $this->parser->parse($ocrResult);

        Log::info('InvoiceOCRService: extraction complete', [
            'bill_id'    => $bill->id,
            'vendor'     => $parsed['vendor_name'],
            'total'      => $parsed['total_amount'],
            'item_count' => count($parsed['items']),
            'confidence' => $ocrResult['confidence'] ?? 0,
        ]);

        return [
            'vendor_name'    => $parsed['vendor_name'],
            'invoice_number' => $parsed['invoice_number'],
            'invoice_date'   => $parsed['invoice_date'],
            'gst_number'     => $parsed['gst_number'],
            'subtotal'       => $parsed['subtotal'],
            'tax_amount'     => $parsed['tax_amount'],
            'total_amount'   => $parsed['total_amount'],
            'items'          => $parsed['items'],
            'ocr_provider'   => 'paddleocr',
            'ocr_message'    => null,
        ];
    }

    // ── Private ────────────────────────────────────────────────────────────────

    private function emptyExtraction(string $message): array
    {
        return [
            'vendor_name'    => null,
            'invoice_number' => null,
            'invoice_date'   => null,
            'gst_number'     => null,
            'subtotal'       => 0,
            'tax_amount'     => 0,
            'total_amount'   => 0,
            'items'          => [],
            'ocr_provider'   => 'paddleocr',
            'ocr_message'    => $message,
        ];
    }
}
