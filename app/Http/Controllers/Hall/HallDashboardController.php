<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallBooking;
use Illuminate\View\View;

class HallDashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $todayBookings    = HallBooking::whereDate('booking_date', $today)->where('status', '!=', 'cancelled')->count();
        $upcomingBookings = HallBooking::whereDate('booking_date', '>', $today)->where('status', 'confirmed')->count();
        $pendingPayments  = HallBooking::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->count();
        $totalRevenue     = HallBooking::where('status', '!=', 'cancelled')->sum('total_amount');
        $monthRevenue     = HallBooking::where('status', '!=', 'cancelled')
            ->whereYear('booking_date', $today->year)
            ->whereMonth('booking_date', $today->month)
            ->sum('total_amount');

        $recentBookings = HallBooking::with(['hall', 'creator'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $todayList = HallBooking::with('hall')
            ->whereDate('booking_date', $today)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        $halls = Hall::active()->withCount(['bookings as upcoming_count' => fn($q) => $q->whereDate('booking_date', '>=', $today)->where('status', 'confirmed')])->get();

        return view('hall.dashboard', compact(
            'todayBookings', 'upcomingBookings', 'pendingPayments',
            'totalRevenue', 'monthRevenue', 'recentBookings', 'todayList', 'halls'
        ));
    }
}
