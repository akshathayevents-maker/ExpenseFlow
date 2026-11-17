<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\HallBooking;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Inventory\LossControlReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Management loss-control dashboard and reports (admin + manager). */
class LossControlController extends Controller
{
    public function __construct(private LossControlReportService $reports) {}

    public function index(Request $request): View
    {
        $this->guard($request);
        $f = $this->filters($request);

        return view('inventory.reports.index', [
            'f' => $f, 'signals' => $this->reports->signals($f), 'totals' => $this->reports->totals($f),
            'reports' => LossControlReportService::REPORTS,
        ] + $this->lookups());
    }

    public function show(Request $request, string $report): View|StreamedResponse
    {
        $this->guard($request);
        $f = $this->filters($request);
        $data = $this->reports->run($report, $f);

        if ($request->input('format') === 'csv') {
            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                fputcsv($out, array_values($data['columns']));
                foreach ($data['rows'] as $row) {
                    fputcsv($out, array_map(function ($k) use ($row) {
                        $v = is_scalar($row[$k] ?? null) ? (string) $row[$k] : '';

                        // neutralise spreadsheet formulas in free-text cells (CSV injection)
                        return preg_match('/^[=+\-@\t\r]/', $v) && ! is_numeric($v) ? "'".$v : $v;
                    }, array_keys($data['columns'])));
                }
                fclose($out);
            }, $report.'-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
        }

        return view('inventory.reports.show', ['report' => $report, 'data' => $data, 'f' => $f, 'reports' => LossControlReportService::REPORTS] + $this->lookups());
    }

    private function guard(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isManager(), 403);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'item_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'meal_type' => ['nullable', 'string', 'max:20'],
            'hall_booking_id' => ['nullable', 'integer'],
            'vendor_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
        ]);
    }

    private function lookups(): array
    {
        return [
            'items' => InventoryItem::orderBy('name')->pluck('name', 'id'),
            'users' => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'vendors' => Vendor::orderBy('name')->pluck('name', 'id'),
            'categories' => InventoryCategory::orderBy('name')->pluck('name', 'id'),
            'bookings' => HallBooking::where('booking_date', '>=', now()->subMonths(3))->orderByDesc('booking_date')->limit(200)->get(['id', 'customer_name', 'booking_date']),
        ];
    }
}
