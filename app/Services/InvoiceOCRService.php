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
        // ── Step 1: Run PaddleOCR (Python) ────────────────────────────────
        $ocrResult = $this->paddle->extractText($bill);

        if (! ($ocrResult['success'] ?? false)) {
            $msg = $ocrResult['error'] ?? 'OCR processing failed.';
            Log::warning('InvoiceOCRService: OCR failed', ['bill_id' => $bill->id, 'error' => $msg]);
            return $this->emptyExtraction($msg);
        }

        // ── Step 2: Prefer Python-parsed invoice object, fallback to PHP ──
        if (! empty($ocrResult['invoice'])) {
            $inv = $ocrResult['invoice'];
            $structured = [
                'vendor_name'    => $inv['vendor_name']    ?? null,
                'invoice_number' => $inv['invoice_number'] ?? null,
                'invoice_date'   => $inv['invoice_date']   ?? null,
                'gst_number'     => $inv['gst_number']     ?? null,
                'subtotal'       => (float) ($inv['subtotal']    ?? 0),
                'tax_amount'     => (float) ($inv['tax_amount']  ?? 0),
                'total_amount'   => (float) ($inv['grand_total'] ?? 0),
                'items'          => $this->normaliseItems($inv['items'] ?? []),
            ];
        } else {
            $parsed = $this->parser->parse($ocrResult);
            $structured = [
                'vendor_name'    => $parsed['vendor_name'],
                'invoice_number' => $parsed['invoice_number'],
                'invoice_date'   => $parsed['invoice_date'],
                'gst_number'     => $parsed['gst_number'],
                'subtotal'       => $parsed['subtotal'],
                'tax_amount'     => $parsed['tax_amount'],
                'total_amount'   => $parsed['total_amount'],
                'items'          => $parsed['items'],
            ];
        }

        Log::info('InvoiceOCRService: extraction complete', [
            'bill_id'    => $bill->id,
            'vendor'     => $structured['vendor_name'],
            'total'      => $structured['total_amount'],
            'item_count' => count($structured['items']),
            'confidence' => $ocrResult['confidence'] ?? 0,
        ]);

        return array_merge($structured, [
            'ocr_provider'   => $ocrResult['provider'] ?? 'paddleocr',
            'ocr_confidence' => $ocrResult['confidence'] ?? 0,
            'ocr_message'    => null,
        ]);
    }

    private function normaliseItems(array $items): array
    {
        return array_map(fn($i) => [
            'item_name'   => $i['item_name']   ?? 'Item (please edit)',
            'quantity'    => (float) ($i['quantity']    ?? 1),
            'unit'        => $i['unit']        ?? null,
            'unit_price'  => (float) ($i['unit_price']  ?? 0),
            'tax_percent' => (float) ($i['tax_percent'] ?? 0),
            'total'       => (float) ($i['total']       ?? 0),
        ], $items);
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
