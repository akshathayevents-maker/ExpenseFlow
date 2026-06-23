<?php

namespace App\Http\Controllers\MealRegister;

use App\Http\Controllers\Controller;
use App\Models\MealEntry;
use App\Models\MealEntryItem;
use App\Models\MealClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MealRegisterReportController extends Controller
{
    public function index(Request $request): View
    {
        $clients   = MealClient::orderBy('name')->get();
        $mealTypes = MealEntryItem::mealTypes();

        // ── Date range ────────────────────────────────────────────────────────
        $preset = $request->input('preset', '');
        if ($preset === 'today') {
            $from = $to = now()->toDateString();
        } elseif ($preset === 'week') {
            $from = now()->startOfWeek()->toDateString();
            $to   = now()->endOfWeek()->toDateString();
        } elseif ($preset === 'month') {
            $from = now()->startOfMonth()->toDateString();
            $to   = now()->endOfMonth()->toDateString();
        } else {
            $from = $request->input('from', now()->startOfMonth()->toDateString());
            $to   = $request->input('to', now()->toDateString());
        }

        // ── Query ─────────────────────────────────────────────────────────────
        $query = MealEntry::with(['client', 'items'])
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('meal_client_id')
            ->orderBy('entry_date');

        if ($request->filled('client_id')) {
            $query->where('meal_client_id', $request->client_id);
        }

        // Meal type filter: filter entries that contain at least one item of this type
        $filterMealType = $request->input('meal_type', '');
        if ($filterMealType && array_key_exists($filterMealType, $mealTypes)) {
            $query->whereHas('items', fn($q) => $q->where('meal_type', $filterMealType));
        }

        $entries = $query->get();

        // ── Grand totals ──────────────────────────────────────────────────────
        $grandPlanned = 0;
        $grandActual  = 0;

        // ── Per meal-type totals ──────────────────────────────────────────────
        $mealTypeTotals = [];
        foreach ($mealTypes as $key => $meta) {
            $mealTypeTotals[$key] = ['planned' => 0, 'actual' => 0, 'label' => $meta['label'], 'icon' => $meta['icon']];
        }

        // ── Client summary ────────────────────────────────────────────────────
        $summary = [];
        foreach ($entries as $entry) {
            $cid  = $entry->meal_client_id;
            $name = $entry->client->name ?? 'Unknown';
            if (!isset($summary[$cid])) {
                $summary[$cid] = ['name' => $name, 'days' => 0, 'planned' => 0, 'actual' => 0, 'types' => []];
            }
            $summary[$cid]['days']++;

            foreach ($entry->items as $item) {
                $t = $item->meal_type;
                if (!isset($summary[$cid]['types'][$t])) {
                    $summary[$cid]['types'][$t] = ['planned' => 0, 'actual' => 0];
                }
                $p = $item->planned_count ?? 0;
                $a = $item->actual_count  ?? 0;

                $summary[$cid]['types'][$t]['planned'] += $p;
                $summary[$cid]['types'][$t]['actual']  += $a;
                $summary[$cid]['planned'] += $p;
                $summary[$cid]['actual']  += $a;
                $grandPlanned += $p;
                $grandActual  += $a;

                if (array_key_exists($t, $mealTypeTotals)) {
                    $mealTypeTotals[$t]['planned'] += $p;
                    $mealTypeTotals[$t]['actual']  += $a;
                }
            }
        }

        // Compute per-client variance and sort by absolute variance descending
        foreach ($summary as $cid => &$row) {
            $row['variance'] = $row['actual'] - $row['planned'];
        }
        unset($row);

        $clientPerf = collect($summary)
            ->sortByDesc(fn($r) => abs($r['variance']))
            ->values()
            ->all();

        // Top positive / negative variance clients
        $topPositive = collect($summary)->sortByDesc('variance')->first();
        $topNegative = collect($summary)->sortBy('variance')->first();
        if ($topNegative && $topNegative['variance'] >= 0) $topNegative = null;
        if ($topPositive && $topPositive['variance'] <= 0) $topPositive = null;

        // ── Today's snapshot (always shows regardless of date filter) ─────────
        $todayEntries = MealEntry::with('items')->whereDate('entry_date', today())->get();
        $todayPlanned = 0; $todayActual = 0;
        foreach ($todayEntries as $e) {
            foreach ($e->items as $i) {
                $todayPlanned += ($i->planned_count ?? 0);
                $todayActual  += ($i->actual_count  ?? 0);
            }
        }

        $grandVariance    = $grandActual - $grandPlanned;
        $clientsServed    = count($summary);

        return view('meal-register.reports.index', compact(
            'clients', 'mealTypes', 'from', 'to', 'preset',
            'summary', 'entries', 'clientPerf',
            'mealTypeTotals', 'grandPlanned', 'grandActual', 'grandVariance',
            'clientsServed', 'topPositive', 'topNegative',
            'todayPlanned', 'todayActual',
            'filterMealType'
        ));
    }

    public function export(Request $request): Response
    {
        $mealTypes = MealEntryItem::mealTypes();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $query = MealEntry::with(['client', 'items'])
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('meal_client_id')
            ->orderBy('entry_date');

        if ($request->filled('client_id')) {
            $query->where('meal_client_id', $request->client_id);
        }

        $entries = $query->get();

        $summary = [];
        foreach ($entries as $entry) {
            $cid  = $entry->meal_client_id;
            $name = $entry->client->name ?? 'Unknown';
            if (!isset($summary[$cid])) {
                $summary[$cid] = ['name' => $name, 'days' => 0, 'types' => []];
            }
            $summary[$cid]['days']++;
            foreach ($entry->items as $item) {
                $t = $item->meal_type;
                if (!isset($summary[$cid]['types'][$t])) {
                    $summary[$cid]['types'][$t] = ['planned' => 0, 'actual' => 0];
                }
                $summary[$cid]['types'][$t]['planned'] += ($item->planned_count ?? 0);
                $summary[$cid]['types'][$t]['actual']  += ($item->actual_count ?? 0);
            }
        }

        $rows = [];

        $summaryHeader = ['Client', 'Days'];
        foreach ($mealTypes as $meta) {
            $summaryHeader[] = $meta['label'] . ' Plan';
            $summaryHeader[] = $meta['label'] . ' Actual';
        }
        $summaryHeader[] = 'Total Plan';
        $summaryHeader[] = 'Total Actual';
        $summaryHeader[] = 'Variance';

        $rows[] = ['SUMMARY: ' . $from . ' to ' . $to];
        $rows[] = $summaryHeader;

        foreach ($summary as $row) {
            $line   = [$row['name'], $row['days']];
            $totalP = 0; $totalA = 0;
            foreach ($mealTypes as $key => $meta) {
                $p = $row['types'][$key]['planned'] ?? 0;
                $a = $row['types'][$key]['actual']  ?? 0;
                $line[] = $p; $line[] = $a;
                $totalP += $p; $totalA += $a;
            }
            $line[] = $totalP; $line[] = $totalA; $line[] = $totalA - $totalP;
            $rows[] = $line;
        }

        $rows[] = [];
        $rows[] = ['DETAIL (one row per day per client)'];

        $detailHeader = ['Date', 'Client'];
        foreach ($mealTypes as $meta) {
            $detailHeader[] = $meta['label'] . ' Plan';
            $detailHeader[] = $meta['label'] . ' Actual';
        }
        $detailHeader[] = 'Total Plan';
        $detailHeader[] = 'Total Actual';
        $detailHeader[] = 'Variance';
        $detailHeader[] = 'Remarks';
        $rows[] = $detailHeader;

        foreach ($entries as $entry) {
            $line   = [$entry->entry_date->format('d/m/Y'), $entry->client->name ?? ''];
            $totalP = 0; $totalA = 0;
            foreach ($mealTypes as $key => $meta) {
                $item = $entry->items->firstWhere('meal_type', $key);
                $p = $item?->planned_count ?? 0;
                $a = $item?->actual_count  ?? 0;
                $line[] = $p; $line[] = $a;
                $totalP += $p; $totalA += $a;
            }
            $line[] = $totalP; $line[] = $totalA; $line[] = $totalA - $totalP;
            $line[] = $entry->remarks ?? '';
            $rows[] = $line;
        }

        $filename = 'meal-report-' . $from . '-to-' . $to . '.csv';
        $csv = '';
        foreach ($rows as $row) {
            $escaped = array_map(function ($cell) {
                $cell = (string) $cell;
                if (str_contains($cell, ',') || str_contains($cell, '"') || str_contains($cell, "\n")) {
                    $cell = '"' . str_replace('"', '""', $cell) . '"';
                }
                return $cell;
            }, $row);
            $csv .= implode(',', $escaped) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
