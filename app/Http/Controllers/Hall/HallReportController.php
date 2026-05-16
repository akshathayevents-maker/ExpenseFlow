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

        $bookings = $query->get();

        $summary = [
            'total_bookings'  => $bookings->count(),
            'total_revenue'   => $bookings->where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_collected' => $bookings->flatMap->payments->sum('amount'),
            'total_balance'   => $bookings->where('status', '!=', 'cancelled')->sum('total_amount') - $bookings->flatMap->payments->sum('amount'),
            'by_hall'         => $bookings->where('status', '!=', 'cancelled')->groupBy('hall_id')->map(fn($g) => [
                'name'    => $g->first()->hall->name,
                'count'   => $g->count(),
                'revenue' => $g->sum('total_amount'),
            ]),
            'by_event'        => $bookings->where('status', '!=', 'cancelled')->groupBy('event_type')->map(fn($g) => [
                'count'   => $g->count(),
                'revenue' => $g->sum('total_amount'),
            ]),
        ];

        return view('hall.reports.index', compact('bookings', 'halls', 'summary'));
    }
}
