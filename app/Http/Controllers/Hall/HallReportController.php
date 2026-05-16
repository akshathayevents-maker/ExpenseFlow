<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallBooking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HallReportController extends Controller
{
    public function index(Request $request): View
    {
        $halls = Hall::orderBy('name')->get();

        $query = HallBooking::with(['hall', 'payments'])
            ->orderBy('booking_date');

        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        if ($request->filled('hall_id')) {
            $query->where('hall_id', $request->hall_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings  = $query->get();
        $active    = $bookings->where('status', '!=', 'cancelled');
        $collected = $bookings->flatMap->payments->sum('amount');
        $revenue   = $active->sum('total_amount');

        $summary = [
            'total_bookings'  => $bookings->count(),
            'active_bookings' => $active->count(),
            'cancelled'       => $bookings->where('status', 'cancelled')->count(),
            'total_revenue'   => $revenue,
            'total_collected' => $collected,
            'total_balance'   => max(0, $revenue - $collected),
            'avg_revenue'     => $active->count() > 0 ? (int) round($revenue / $active->count()) : 0,
            'total_people'    => $active->sum('number_of_people'),
            'collection_rate' => $revenue > 0 ? min(100, (int) round($collected / $revenue * 100)) : 0,
            'by_hall'  => $active->groupBy('hall_id')->map(fn($g) => [
                'name'    => $g->first()->hall->name,
                'count'   => $g->count(),
                'revenue' => $g->sum('total_amount'),
                'people'  => $g->sum('number_of_people'),
            ])->sortByDesc('revenue'),
            'by_event' => $active->groupBy('event_type')->map(fn($g) => [
                'label'   => \App\Models\HallBooking::eventTypes()[$g->first()->event_type]
                             ?? ucwords(str_replace('_', ' ', $g->first()->event_type)),
                'count'   => $g->count(),
                'revenue' => $g->sum('total_amount'),
            ])->sortByDesc('revenue'),
            'pay_paid'    => $active->where('payment_status', 'paid')->count(),
            'pay_partial' => $active->where('payment_status', 'partial')->count(),
            'pay_pending' => $active->where('payment_status', 'pending')->count(),
        ];

        // Last 6 months revenue trend — always global, unaffected by filters
        // Use a plain array so we can safely modify elements inside the closure
        $trendMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $trendMonths[$m->format('Y-m')] = [
                'label'   => $m->format("M 'y"),
                'revenue' => 0,
                'count'   => 0,
            ];
        }

        HallBooking::where('status', '!=', 'cancelled')
            ->where('booking_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->get(['booking_date', 'total_amount'])
            ->groupBy(fn($b) => $b->booking_date->format('Y-m'))
            ->each(function ($g, $key) use (&$trendMonths) {
                if (isset($trendMonths[$key])) {
                    $trendMonths[$key]['revenue'] = (int) $g->sum('total_amount');
                    $trendMonths[$key]['count']   = $g->count();
                }
            });

        // Convert array to collection values for the view
        $monthlyTrend = collect($trendMonths)->values();

        return view('hall.reports.index', compact('bookings', 'halls', 'summary', 'monthlyTrend'));
    }
}
