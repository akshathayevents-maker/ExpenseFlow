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
        $weekEnd = $today->copy()->addDays(6);
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $todayList = HallBooking::with(['hall', 'mealPlan', 'payments'])
            ->whereDate('booking_date', $today)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        $weekBookings = HallBooking::with(['hall', 'payments'])
            ->whereBetween('booking_date', [$today->toDateString(), $weekEnd->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->get();

        $tomorrowBookings = $weekBookings
            ->filter(fn (HallBooking $booking) => $booking->booking_date->isSameDay($today->copy()->addDay()));

        $monthBookings = HallBooking::with('payments')
            ->whereBetween('booking_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->get();

        $pendingPaymentsQuery = HallBooking::where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled');

        $pendingPayments = (clone $pendingPaymentsQuery)->count();
        $pendingBalance = (clone $pendingPaymentsQuery)
            ->with('payments')
            ->get()
            ->sum(fn (HallBooking $booking) => max(0, $booking->balance_amount));

        $pendingPaymentBookings = (clone $pendingPaymentsQuery)->with(['hall', 'payments'])
            ->orderBy('booking_date')
            ->limit(6)
            ->get();

        $todayBookings = $todayList->count();
        $foodOnlyToday = $todayList->filter(fn (HallBooking $b) => $b->isFoodOnly())->count();
        $upcomingBookings = $weekBookings->where('booking_date', '>', $today)->count();
        $monthRevenue = $monthBookings->sum('total_amount');
        $cateringLoad = $weekBookings->sum('number_of_people');

        $halls = Hall::active()
            ->with(['bookings' => fn ($query) => $query
                ->with('payments')
                ->whereDate('booking_date', '>=', $today)
                ->where('status', 'confirmed')
                ->orderBy('booking_date')
                ->orderBy('start_time')
            ])
            ->orderBy('name')
            ->get();

        $occupiedHallDays = $weekBookings
            ->map(fn (HallBooking $booking) => $booking->hall_id . ':' . $booking->booking_date->toDateString())
            ->unique()
            ->count();
        $totalHallDays = max(1, $halls->count() * 7);
        $occupancyRate = round(($occupiedHallDays / $totalHallDays) * 100);

        $recentBookings = HallBooking::with(['hall', 'creator', 'payments'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $busyDays = $weekBookings
            ->groupBy(fn (HallBooking $booking) => $booking->booking_date->toDateString())
            ->map(fn ($bookings, $date) => [
                'date' => $date,
                'label' => $bookings->first()->booking_date->format('D, d M'),
                'count' => $bookings->count(),
                'guests' => $bookings->sum('number_of_people'),
                'revenue' => $bookings->sum('total_amount'),
            ])
            ->sortByDesc('count')
            ->take(4)
            ->values();

        $hallStatuses = $halls->map(function (Hall $hall) use ($today) {
            $todayBookings = $hall->bookings->filter(fn (HallBooking $booking) => $booking->booking_date->isSameDay($today));
            $nextBooking = $hall->bookings->first();

            return [
                'hall' => $hall,
                'today_count' => $todayBookings->count(),
                'upcoming_count' => $hall->bookings->count(),
                'next_booking' => $nextBooking,
                'state' => $todayBookings->isNotEmpty() ? 'Busy today' : ($nextBooking ? 'Upcoming event' : 'Available'),
            ];
        });

        $mealLoadFor = fn ($bookings) => [
            'breakfast' => $bookings->where('has_breakfast', true)->sum('number_of_people'),
            'lunch' => $bookings->where('has_lunch', true)->sum('number_of_people'),
            'dinner' => $bookings->where('has_dinner', true)->sum('number_of_people'),
            'total' => $bookings->sum('number_of_people'),
        ];

        $kitchenLoad = [
            'today' => $mealLoadFor($todayList),
            'tomorrow' => $mealLoadFor($tomorrowBookings),
        ];

        $operationMoments = $todayList
            ->flatMap(function (HallBooking $booking) {
                $moments = collect();
                $start = \Carbon\Carbon::parse($booking->start_time);
                $eventLabel = \App\Models\HallBooking::eventTypes()[$booking->event_type] ?? str($booking->event_type)->headline()->toString();
                $meals = collect([
                    'Breakfast' => $booking->has_breakfast,
                    'Lunch' => $booking->has_lunch,
                    'Dinner' => $booking->has_dinner,
                ])->filter()->keys()->join(', ');

                if ($meals) {
                    $moments->push([
                        'time' => $start->copy()->subHours(2)->format('H:i'),
                        'label' => 'Kitchen prep',
                        'title' => $meals . ' for ' . number_format($booking->number_of_people) . ' guests',
                        'meta' => $booking->location_label . ' · ' . $booking->customer_name,
                        'tone' => 'gold',
                        'url' => route('hall.bookings.kitchen', ['date' => $booking->booking_date->toDateString()]),
                    ]);
                }

                $moments->push([
                    'time' => $start->format('H:i'),
                    'label' => $eventLabel,
                    'title' => $booking->customer_name,
                    'meta' => $booking->location_label . ' · ' . number_format($booking->number_of_people) . ' guests',
                    'tone' => 'emerald',
                    'url' => route('hall.bookings.show', $booking),
                ]);

                if ($booking->payment_status !== 'paid') {
                    $moments->push([
                        'time' => \Carbon\Carbon::parse($booking->end_time)->format('H:i'),
                        'label' => 'Payment follow-up',
                        'title' => 'Collect balance ₹' . number_format(max(0, $booking->balance_amount), 0),
                        'meta' => $booking->customer_name . ' · ' . ucfirst($booking->payment_status),
                        'tone' => 'danger',
                        'url' => route('hall.bookings.show', $booking) . '#record-payment',
                    ]);
                }

                return $moments;
            })
            ->sortBy('time')
            ->values();

        $occupancyTimeline = collect(range(0, 6))->map(function ($offset) use ($today, $weekBookings, $halls) {
            $date = $today->copy()->addDays($offset);
            $bookings = $weekBookings->filter(fn (HallBooking $booking) => $booking->booking_date->isSameDay($date));
            $occupiedHalls = $bookings->filter(fn (HallBooking $b) => $b->requiresHall())->pluck('hall_id')->unique()->count();
            $capacity = max(1, $halls->count());
            $percent = round(($occupiedHalls / $capacity) * 100);

            return [
                'date' => $date,
                'label' => $date->format('D'),
                'day' => $date->format('d'),
                'bookings' => $bookings->count(),
                'guests' => $bookings->sum('number_of_people'),
                'percent' => $percent,
                'load' => $percent >= 75 ? 'high' : ($percent >= 35 ? 'medium' : 'low'),
            ];
        });

        $attentionItems = collect();
        if ($pendingPayments > 0) {
            $attentionItems->push([
                'title' => 'Balance payments pending',
                'body' => '₹' . number_format($pendingBalance, 0) . ' awaiting collection across ' . $pendingPayments . ' bookings.',
                'tone' => 'danger',
                'url' => route('hall.bookings.index', ['payment_status' => 'pending']),
            ]);
        }

        $busiestDay = $occupancyTimeline->sortByDesc('percent')->first();
        if (($busiestDay['percent'] ?? 0) >= 75) {
            $attentionItems->push([
                'title' => $busiestDay['date']->format('l') . ' nearing full occupancy',
                'body' => $busiestDay['bookings'] . ' bookings and ' . number_format($busiestDay['guests']) . ' guests currently planned.',
                'tone' => 'gold',
                'url' => route('hall.bookings.calendar'),
            ]);
        }

        if ($kitchenLoad['tomorrow']['total'] >= 250) {
            $attentionItems->push([
                'title' => 'Kitchen load is heavy tomorrow',
                'body' => number_format($kitchenLoad['tomorrow']['total']) . ' guest covers across breakfast, lunch, and dinner.',
                'tone' => 'gold',
                'url' => route('hall.bookings.kitchen', ['date' => $today->copy()->addDay()->toDateString()]),
            ]);
        }

        if ($attentionItems->isEmpty()) {
            $attentionItems->push([
                'title' => 'Operations are calm',
                'body' => 'No urgent payment, occupancy, or kitchen risks detected right now.',
                'tone' => 'emerald',
                'url' => route('hall.bookings.calendar'),
            ]);
        }

        $nextEvents = $weekBookings
            ->sortBy([['booking_date', 'asc'], ['start_time', 'asc']])
            ->take(5)
            ->values();

        $operations = [
            'today_bookings' => $todayBookings,
            'food_only_today' => $foodOnlyToday,
            'upcoming_bookings' => $upcomingBookings,
            'pending_payments' => $pendingPayments,
            'month_revenue' => $monthRevenue,
            'occupancy_rate' => $occupancyRate,
            'catering_load' => $cateringLoad,
            'pending_balance' => $pendingBalance,
        ];

        return view('hall.dashboard', compact(
            'operations', 'recentBookings', 'todayList', 'halls',
            'pendingPaymentBookings', 'busyDays', 'hallStatuses',
            'operationMoments', 'kitchenLoad', 'occupancyTimeline',
            'attentionItems', 'nextEvents'
        ));
    }
}
