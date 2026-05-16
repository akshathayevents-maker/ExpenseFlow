<?php

namespace App\Services\OCR;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Parses raw PaddleOCR output into structured invoice data using
 * spatial grouping (bbox Y-coordinates) + regex/heuristic extraction.
 *
 * Designed for Tamil+English mixed grocery/store bills.
 */
class InvoiceParserService
{
    // ── Keyword patterns ───────────────────────────────────────────────────────

    private const SKIP_ITEM = '/\b(?:total|grand\s*total|sub\s*total|subtotal|net\s*(?:total|amount)|amount\s*due|balance|sgst|cgst|igst|gst|vat|tax|discount|freight|shipping|round(?:ed)?\s*off|advance|paid|due|thank|signature|authorized|gstin|pan|registration|cin|bill\s*no|invoice\s*no|date|from|to|buyer|seller|ship|deliver|s\.?\s*no|sr\.?\s*no|sl\.?\s*no|description|particulars|qty|quantity|rate|price|per\s*unit|hsn|sac|mrp|unit|amount|value|rs\.?)\b/iu';

    private const TOTAL_LINE = '/\b(?:grand\s*total|total\s*amount|net\s*(?:total|amount)|amount\s*due|total)\s*[:\-]?\s*₹?\s*([\d,]+(?:\.\d{1,2})?)/iu';
    private const TAX_LINE   = '/\b(?:(?:cgst|sgst|igst|gst|tax|vat)(?:\s*@\s*[\d.]+%?)?)\s*[:\-]?\s*₹?\s*([\d,]+(?:\.\d{1,2})?)/iu';
    private const SUB_LINE   = '/\b(?:subtotal|sub\s*total|taxable)\s*[:\-]?\s*₹?\s*([\d,]+(?:\.\d{1,2})?)/iu';

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Parse PaddleOCR result into structured invoice data.
     *
     * @param  array  $ocrResult  ['raw_lines' => [...], 'full_text' => '...', ...]
     */
    public function parse(array $ocrResult): array
    {
        $rawLines = $ocrResult['raw_lines'] ?? [];
        $fullText = $ocrResult['full_text'] ?? '';

        if (empty($rawLines)) {
            return $this->emptyResult();
        }

        // Reconstruct rows by spatial proximity on Y axis
        $rows = $this->groupByRow($rawLines);

        return [
            'vendor_name'    => $this->findVendor($rows),
            'invoice_number' => $this->findInvoiceNumber($fullText),
            'invoice_date'   => $this->findDate($fullText),
            'gst_number'     => $this->findGst($fullText),
            ...$this->findTotals($rows, $fullText),
            'items'          => $this->extractItems($rows),
        ];
    }

    // ── Spatial grouping ───────────────────────────────────────────────────────

    /**
     * Group OCR lines into logical rows using Y-coordinate proximity.
     * Each row is an array of lines sorted left-to-right (ascending X).
     *
     * bbox format per line: [x1,y1, x2,y2, x3,y3, x4,y4] (4 corners, 8 values)
     * midY = average of y1 (top-left) and y4 (bottom-left)
     */
    private function groupByRow(array $lines, int $tolerance = 12): array
    {
        if (empty($lines)) return [];

        usort($lines, fn($a, $b) => $this->midY($a['bbox']) <=> $this->midY($b['bbox']));

        $rows    = [];
        $current = [$lines[0]];
        $lastY   = $this->midY($lines[0]['bbox']);

        for ($i = 1; $i < count($lines); $i++) {
            $y = $this->midY($lines[$i]['bbox']);
            if (abs($y - $lastY) <= $tolerance) {
                $current[] = $lines[$i];
            } else {
                $rows[]  = $this->sortByX($current);
                $current = [$lines[$i]];
            }
            $lastY = $y;
        }
        $rows[] = $this->sortByX($current);

        return $rows;
    }

    private function midY(array $bbox): int
    {
        // bbox = [x1,y1, x2,y2, x3,y3, x4,y4]
        // top-left y = bbox[1], bottom-left y = bbox[7]
        return (int) (($bbox[1] + $bbox[7]) / 2);
    }

    private function leftX(array $bbox): int
    {
        return $bbox[0]; // top-left x
    }

    private function sortByX(array $row): array
    {
        usort($row, fn($a, $b) => $this->leftX($a['bbox']) <=> $this->leftX($b['bbox']));
        return $row;
    }

    private function rowText(array $row): string
    {
        return implode(' ', array_map(fn($l) => $l['text'], $row));
    }

    // ── Vendor name ────────────────────────────────────────────────────────────

    private function findVendor(array $rows): ?string
    {
        // Vendor/shop name is usually in the first 1–4 rows.
        // It looks like a business name: mostly letters, possibly caps, no date/invoice patterns.
        foreach (array_slice($rows, 0, 6) as $row) {
            $text = trim($this->rowText($row));
            if (strlen($text) < 3) continue;
            if (preg_match('/^\d/', $text)) continue;                          // starts with digit
            if (preg_match('/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/', $text)) continue; // date
            if (preg_match('/\b(?:bill|invoice|credit|debit|memo|receipt|cash)\b/i', $text)) continue;
            if (preg_match('/\b(?:gstin|pan|cin)\b/i', $text)) continue;

            // Accept if it has at least 2 consecutive letters (handles Tamil too)
            if (preg_match('/\p{L}{2}/u', $text)) {
                return $text;
            }
        }
        return null;
    }

    // ── Date ───────────────────────────────────────────────────────────────────

    private function findDate(string $text): ?string
    {
        // dd/mm/yyyy  dd-mm-yyyy  dd.mm.yyyy
        if (preg_match('/\b(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})\b/', $text, $m)) {
            try {
                return Carbon::createFromFormat('d/m/Y', "{$m[1]}/{$m[2]}/{$m[3]}")->format('Y-m-d');
            } catch (\Throwable) {}
        }

        // yyyy-mm-dd
        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $text, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        // dd Month yyyy
        if (preg_match('/\b(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s+(\d{4})\b/i', $text, $m)) {
            try {
                return Carbon::parse("{$m[1]} {$m[2]} {$m[3]}")->format('Y-m-d');
            } catch (\Throwable) {}
        }

        return null;
    }

    // ── Invoice number ─────────────────────────────────────────────────────────

    private function findInvoiceNumber(string $text): ?string
    {
        $patterns = [
            '/(?:Bill|Invoice|Inv)[\s.:#-]*(?:No|Number|#)[\s.:#-]*([A-Z0-9\-\/]{1,20})/i',
            '/(?:No|#)[\s.:]*([A-Z0-9]{2,15})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $val = trim($m[1]);
                // Reject: pure long-digit strings (phone numbers, dates)
                if (! preg_match('/^\d{9,}$/', $val)) {
                    return $val;
                }
            }
        }
        return null;
    }

    // ── GST ────────────────────────────────────────────────────────────────────

    private function findGst(string $text): ?string
    {
        // Indian GSTIN: 15 chars
        if (preg_match('/\b([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z])\b/', $text, $m)) {
            return $m[1];
        }
        return null;
    }

    // ── Totals ─────────────────────────────────────────────────────────────────

    private function findTotals(array $rows, string $fullText): array
    {
        $grand   = 0.0;
        $tax     = 0.0;
        $sub     = 0.0;

        foreach ($rows as $row) {
            $text = $this->rowText($row);

            if ($grand === 0.0 && preg_match(self::TOTAL_LINE, $text, $m)) {
                $grand = $this->toFloat($m[1]);
            }
            if ($tax === 0.0 && preg_match(self::TAX_LINE, $text, $m)) {
                $tax = $this->toFloat($m[1]);
            }
            if ($sub === 0.0 && preg_match(self::SUB_LINE, $text, $m)) {
                $sub = $this->toFloat($m[1]);
            }
        }

        // Fallback: scan all standalone numbers and use the largest plausible amount
        if ($grand === 0.0) {
            preg_match_all('/\b(\d{2,8}(?:\.\d{2})?)\b/', $fullText, $all);
            $candidates = array_map('floatval', $all[1] ?? []);
            rsort($candidates);
            foreach ($candidates as $c) {
                if ($c >= 1 && $c < 10_000_000) {
                    $grand = $c;
                    break;
                }
            }
        }

        if ($sub === 0.0) {
            $sub = max(0.0, $grand - $tax);
        }

        return ['total_amount' => $grand, 'tax_amount' => $tax, 'subtotal' => $sub];
    }

    // ── Item extraction ────────────────────────────────────────────────────────

    private function extractItems(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $text = trim($this->rowText($row));

            // Must have at least one letter (any script) and at least one digit
            if (! preg_match('/\p{L}/u', $text)) continue;
            if (! preg_match('/\d/', $text)) continue;

            // Skip known header/footer keyword lines (short lines only — long item names can contain keywords)
            if (strlen($text) < 60 && preg_match(self::SKIP_ITEM, $text)) continue;

            $item = $this->parseItemRow($row, $text);
            if ($item) {
                $items[] = $item;
            }
        }

        // If nothing found, single placeholder from the total
        if (empty($items)) {
            $items[] = [
                'item_name'   => 'Item (please edit)',
                'quantity'    => 1.0,
                'unit'        => null,
                'unit_price'  => 0.0,
                'tax_percent' => 0.0,
                'total'       => 0.0,
            ];
        }

        return $items;
    }

    /**
     * Parse a single row into an item array.
     * Strategy: collect all numbers from the row; the non-numeric prefix is the item name.
     * Assign numbers: [qty?] [unit_price?] [total]
     */
    private function parseItemRow(array $row, string $text): ?array
    {
        // Extract all numeric tokens (integers or decimals)
        preg_match_all('/\b(\d+(?:\.\d{1,3})?)\b/', $text, $numMatches);
        $nums = array_map('floatval', $numMatches[1]);
        $nums = array_values(array_filter($nums, fn($n) => $n > 0));

        if (empty($nums)) return null;

        // Item name: strip trailing numbers + unit tokens
        $name = preg_replace('/\s+[\d.,]+(\s*(kg|g|gm|gram|litre?|ltr|ml|pcs?|nos?|box|pkt|dozen|doz))?(\s+[\d.,]+)*\s*$/iu', '', $text);
        $name = trim(preg_replace('/\s{2,}/', ' ', $name));

        if (mb_strlen($name) < 2) return null;
        if (preg_match('/^\d+$/', $name)) return null; // pure serial number

        // Detect unit from text
        $unit = null;
        if (preg_match('/\b(\d+(?:\.\d+)?)\s*(kg|g|gm|gram|litres?|ltr|ml|pcs?|nos?|box|pkt|dozen|doz)\b/iu', $text, $um)) {
            $unit = strtolower($um[2]);
        }

        // Assign numbers to qty / unit_price / total heuristically
        [$qty, $unitPrice, $total] = match (count($nums)) {
            0       => [1.0, 0.0, 0.0],
            1       => [1.0, 0.0, $nums[0]],
            2       => [1.0, $nums[0], $nums[1]],
            default => [$nums[0], $nums[count($nums) - 2], $nums[count($nums) - 1]],
        };

        // Sanity: total should be close to qty * unitPrice (if both non-zero)
        if ($qty > 0 && $unitPrice > 0) {
            $expected = $qty * $unitPrice;
            // If total is wildly off, it's probably qty*rate with extra column — keep as-is
        }

        return [
            'item_name'   => $name,
            'quantity'    => $qty,
            'unit'        => $unit,
            'unit_price'  => $unitPrice,
            'tax_percent' => 0.0,
            'total'       => $total,
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function toFloat(string $raw): float
    {
        return (float) preg_replace('/[^\d.]/', '', str_replace([',', ' '], '', $raw));
    }

    private function emptyResult(): array
    {
        return [
            'vendor_name'    => null,
            'invoice_number' => null,
            'invoice_date'   => null,
            'gst_number'     => null,
            'total_amount'   => 0.0,
            'tax_amount'     => 0.0,
            'subtotal'       => 0.0,
            'items'          => [],
        ];
    }
}
