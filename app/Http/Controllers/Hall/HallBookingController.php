<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\BookingPayment;
use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\MealPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HallBookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = HallBooking::with(['hall', 'mealPlan', 'creator'])
            ->orderByDesc('booking_date')
            ->orderByDesc('created_at');

        if ($request->filled('hall_id')) {
            $query->where('hall_id', $request->hall_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('customer_name', 'like', "%{$s}%")->orWhere('customer_mobile', 'like', "%{$s}%"));
        }

        $bookings = $query->paginate(15)->withQueryString();
        $halls    = Hall::active()->orderBy('name')->get();

        $monthOccupied = HallBooking::whereMonth('booking_date', now()->month)
            ->whereYear('booking_date', now()->year)
            ->where('status', '!=', 'cancelled')
            ->distinct('booking_date')
            ->count('booking_date');

        $stats = [
            'today'       => HallBooking::whereDate('booking_date', today())->where('status', '!=', 'cancelled')->count(),
            'upcoming'    => HallBooking::whereDate('booking_date', '>=', today())->where('status', '!=', 'cancelled')->count(),
            'pending_pay' => HallBooking::where('payment_status', 'pending')->where('status', '!=', 'cancelled')->count(),
            'month_occ'   => now()->daysInMonth > 0 ? round($monthOccupied / now()->daysInMonth * 100) : 0,
            'week_guests' => HallBooking::whereBetween('booking_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->where('status', '!=', 'cancelled')
                ->sum('number_of_people'),
        ];

        return view('hall.bookings.index', compact('bookings', 'halls', 'stats'));
    }

    public function create(): View
    {
        $halls     = Hall::active()->orderBy('name')->get();
        $mealPlans = MealPlan::active()->orderBy('name')->get();
        return view('hall.bookings.create', compact('halls', 'mealPlans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hall_id'              => ['required', 'exists:halls,id'],
            'meal_plan_id'         => ['nullable', 'exists:meal_plans,id'],
            'customer_name'        => ['required', 'string', 'max:150'],
            'customer_mobile'      => ['required', 'string', 'max:15'],
            'customer_alt_mobile'  => ['nullable', 'string', 'max:15'],
            'event_type'           => ['required', 'string', 'max:50'],
            'booking_date'         => ['required', 'date'],
            'start_time'           => ['required'],
            'end_time'             => ['required', 'after:start_time'],
            'number_of_people'     => ['required', 'integer', 'min:1'],
            'has_breakfast'        => ['boolean'],
            'has_lunch'            => ['boolean'],
            'has_dinner'           => ['boolean'],
            'hall_cost'            => ['required', 'numeric', 'min:0'],
            'total_amount'         => ['required', 'numeric', 'min:0'],
            'advance_amount'       => ['required', 'numeric', 'min:0'],
            'payment_status'       => ['required', 'in:pending,partial,paid'],
            'status'               => ['required', 'in:confirmed,cancelled,completed'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'services'             => ['nullable', 'array'],
            'services.*.service_name' => ['required_with:services.*', 'string', 'max:150'],
            'services.*.description'  => ['nullable', 'string', 'max:500'],
            'services.*.amount'       => ['required_with:services.*', 'numeric', 'min:0'],
        ]);

        // Double-booking check
        $conflict = HallBooking::where('hall_id', $data['hall_id'])
            ->where('booking_date', $data['booking_date'])
            ->where('status', '!=', 'cancelled')
            ->where(fn($q) => $q
                ->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                ->orWhereBetween('end_time',   [$data['start_time'], $data['end_time']])
                ->orWhere(fn($q2) => $q2->where('start_time', '<=', $data['start_time'])->where('end_time', '>=', $data['end_time']))
            )->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['booking_date' => 'This hall is already booked for an overlapping time slot on that date.']);
        }

        DB::transaction(function () use ($data, $request) {
            $data['created_by']    = Auth::id();
            $data['has_breakfast'] = $request->boolean('has_breakfast');
            $data['has_lunch']     = $request->boolean('has_lunch');
            $data['has_dinner']    = $request->boolean('has_dinner');

            $services = $data['services'] ?? [];
            unset($data['services']);

            $booking = HallBooking::create($data);

            // Save additional services
            foreach ($services as $service) {
                if (!empty(trim($service['service_name'] ?? ''))) {
                    $booking->additionalServices()->create([
                        'service_name' => trim($service['service_name']),
                        'description'  => trim($service['description'] ?? '') ?: null,
                        'amount'       => (float) ($service['amount'] ?? 0),
                    ]);
                }
            }

            // Record advance payment if > 0
            if ((float) $data['advance_amount'] > 0) {
                BookingPayment::create([
                    'hall_booking_id' => $booking->id,
                    'recorded_by'     => Auth::id(),
                    'amount'          => $data['advance_amount'],
                    'payment_method'  => $request->input('payment_method', 'cash'),
                    'payment_type'    => 'advance',
                    'paid_at'         => today(),
                    'notes'           => 'Advance payment at booking',
                ]);
            }
        });

        return redirect()->route('hall.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function show(HallBooking $booking): View
    {
        $booking->load(['hall', 'mealPlan', 'creator', 'meals', 'payments.recorder', 'additionalServices']);
        return view('hall.bookings.show', compact('booking'));
    }

    public function invoice(HallBooking $booking): View
    {
        $booking->load(['hall', 'mealPlan', 'creator', 'meals', 'payments.recorder', 'additionalServices']);
        return view('hall.bookings.invoice', compact('booking'));
    }

    public function downloadPdf(HallBooking $booking): Response
    {
        $booking->load(['hall', 'mealPlan', 'creator', 'meals', 'payments.recorder', 'additionalServices']);
        $pdf = Pdf::loadView('hall.bookings.invoice', compact('booking'))
            ->setPaper('a4', 'portrait')
            ->setOption('margin_top', 12)
            ->setOption('margin_bottom', 12)
            ->setOption('margin_left', 12)
            ->setOption('margin_right', 12)
            ->setOption('defaultFont', 'dejavu sans')   // DejaVu Sans TTF — has U+20B9 rupee glyph
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);
        return $pdf->download("Akshathay-Booking-{$booking->id}.pdf");
    }

    public function edit(HallBooking $booking): View
    {
        $halls     = Hall::active()->orderBy('name')->get();
        $mealPlans = MealPlan::active()->orderBy('name')->get();
        return view('hall.bookings.edit', compact('booking', 'halls', 'mealPlans'));
    }

    public function update(Request $request, HallBooking $booking): RedirectResponse
    {
        $data = $request->validate([
            'hall_id'              => ['required', 'exists:halls,id'],
            'meal_plan_id'         => ['nullable', 'exists:meal_plans,id'],
            'customer_name'        => ['required', 'string', 'max:150'],
            'customer_mobile'      => ['required', 'string', 'max:15'],
            'customer_alt_mobile'  => ['nullable', 'string', 'max:15'],
            'event_type'           => ['required', 'string', 'max:50'],
            'booking_date'         => ['required', 'date'],
            'start_time'           => ['required'],
            'end_time'             => ['required'],
            'number_of_people'     => ['required', 'integer', 'min:1'],
            'has_breakfast'        => ['boolean'],
            'has_lunch'            => ['boolean'],
            'has_dinner'           => ['boolean'],
            'hall_cost'            => ['required', 'numeric', 'min:0'],
            'total_amount'         => ['required', 'numeric', 'min:0'],
            'advance_amount'       => ['required', 'numeric', 'min:0'],
            'payment_status'       => ['required', 'in:pending,partial,paid'],
            'status'               => ['required', 'in:confirmed,cancelled,completed'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'services'             => ['nullable', 'array'],
            'services.*.service_name' => ['required_with:services.*', 'string', 'max:150'],
            'services.*.description'  => ['nullable', 'string', 'max:500'],
            'services.*.amount'       => ['required_with:services.*', 'numeric', 'min:0'],
        ]);

        // Double-booking check (excluding current booking)
        $conflict = HallBooking::where('hall_id', $data['hall_id'])
            ->where('booking_date', $data['booking_date'])
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $booking->id)
            ->where(fn($q) => $q
                ->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                ->orWhereBetween('end_time',   [$data['start_time'], $data['end_time']])
                ->orWhere(fn($q2) => $q2->where('start_time', '<=', $data['start_time'])->where('end_time', '>=', $data['end_time']))
            )->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['booking_date' => 'This hall is already booked for an overlapping time slot on that date.']);
        }

        $data['has_breakfast'] = $request->boolean('has_breakfast');
        $data['has_lunch']     = $request->boolean('has_lunch');
        $data['has_dinner']    = $request->boolean('has_dinner');

        $services = $data['services'] ?? [];
        unset($data['services']);

        DB::transaction(function () use ($booking, $data, $services) {
            $booking->update($data);

            // Replace all additional services
            $booking->additionalServices()->delete();
            foreach ($services as $service) {
                if (!empty(trim($service['service_name'] ?? ''))) {
                    $booking->additionalServices()->create([
                        'service_name' => trim($service['service_name']),
                        'description'  => trim($service['description'] ?? '') ?: null,
                        'amount'       => (float) ($service['amount'] ?? 0),
                    ]);
                }
            }
        });

        return redirect()->route('hall.bookings.show', $booking)->with('success', 'Booking updated.');
    }

    public function destroy(HallBooking $booking): RedirectResponse
    {
        $booking->delete();
        return redirect()->route('hall.bookings.index')->with('success', 'Booking deleted.');
    }

    public function calendar(): View
    {
        $halls = Hall::active()->orderBy('name')->get();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthBookings = HallBooking::with(['hall', 'payments'])
            ->whereBetween('booking_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->get();

        $occupiedDates = $monthBookings->pluck('booking_date')
            ->map(fn ($date) => $date->toDateString())
            ->unique()
            ->count();

        $summary = [
            'total_bookings'   => $monthBookings->count(),
            'upcoming_events'  => $monthBookings->where('booking_date', '>=', today())->count(),
            'revenue'          => $monthBookings->sum('total_amount'),
            'occupancy'        => $monthEnd->day > 0 ? round(($occupiedDates / $monthEnd->day) * 100) : 0,
            'pending_payments' => $monthBookings->where('payment_status', '!=', 'paid')->count(),
            'catering_load'    => $monthBookings->sum('number_of_people'),
            'today_count'      => 0, // filled after todayBookings query below
            'today_revenue'    => 0,
        ];

        // Today's bookings for Share Brief feature + today's ops strip
        $todayBookings = HallBooking::with(['hall', 'mealPlan', 'payments'])
            ->whereDate('booking_date', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        $summary['today_count']   = $todayBookings->count();
        $summary['today_revenue'] = $todayBookings->sum('total_amount');

        $now = now();
        // Prefer: currently active > next upcoming > null
        $nextBooking = $todayBookings->first(
            fn ($b) => \Carbon\Carbon::parse($b->end_time)->isAfter($now)
        );

        return view('hall.bookings.calendar', compact('halls', 'summary', 'todayBookings', 'nextBooking'));
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $query = HallBooking::with('hall')
            ->where('status', '!=', 'cancelled');

        if ($request->filled('start')) {
            $query->whereDate('booking_date', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->whereDate('booking_date', '<=', $request->end);
        }
        if ($request->filled('hall_id')) {
            $query->where('hall_id', $request->hall_id);
        }

        $events = $query->with(['hall', 'mealPlan', 'payments'])->get()->map(fn($b) => [
            'id'    => $b->id,
            'title' => $b->customer_name,
            'start' => $b->booking_date->toDateString() . 'T' . $b->start_time,
            'end'   => $b->booking_date->toDateString() . 'T' . $b->end_time,
            'classNames' => [
                'ef-cal-event',
                'is-' . $b->status,
                'pay-' . $b->payment_status,
            ],
            'extendedProps' => [
                'customer'     => $b->customer_name,
                'hall'         => $b->hall->name,
                'event_type'   => \App\Models\HallBooking::eventTypes()[$b->event_type] ?? str($b->event_type)->headline()->toString(),
                'people'       => $b->number_of_people,
                'status'       => $b->status,
                'status_label' => \App\Models\HallBooking::statuses()[$b->status] ?? str($b->status)->headline()->toString(),
                'payment_status' => $b->payment_status,
                'payment_status_label' => \App\Models\HallBooking::paymentStatuses()[$b->payment_status] ?? str($b->payment_status)->headline()->toString(),
                'amount'       => $b->total_amount,
                'paid'         => $b->total_paid,
                'balance'      => max(0, $b->balance_amount),
                'meal_plan'    => $b->mealPlan?->name,
                'meals'        => collect([
                    'Breakfast' => $b->has_breakfast,
                    'Lunch' => $b->has_lunch,
                    'Dinner' => $b->has_dinner,
                ])->filter()->keys()->values(),
                'start_time'   => \Carbon\Carbon::parse($b->start_time)->format('h:i A'),
                'end_time'     => \Carbon\Carbon::parse($b->end_time)->format('h:i A'),
                'date'         => $b->booking_date->format('d M Y'),
                'url'          => route('hall.bookings.show', $b),
                'payment_url'  => route('hall.bookings.show', $b) . '#record-payment',
                'whatsapp_url' => 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile),
            ],
        ]);

        return response()->json($events);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'hall_id'      => ['required', 'exists:halls,id'],
            'booking_date' => ['required', 'date'],
            'start_time'   => ['required'],
            'end_time'     => ['required'],
            'exclude_id'   => ['nullable', 'integer'],
        ]);

        $query = HallBooking::where('hall_id', $request->hall_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->where(fn($q) => $q
                ->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time',   [$request->start_time, $request->end_time])
                ->orWhere(fn($q2) => $q2->where('start_time', '<=', $request->start_time)->where('end_time', '>=', $request->end_time))
            );

        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $conflicts = $query->with('hall')->get()->map(fn($b) => [
            'customer' => $b->customer_name,
            'start'    => $b->start_time,
            'end'      => $b->end_time,
        ]);

        return response()->json([
            'available' => $conflicts->isEmpty(),
            'conflicts' => $conflicts,
        ]);
    }

    public function addPayment(Request $request, HallBooking $booking): RedirectResponse
    {
        $data = $request->validate([
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_method'   => ['required', 'in:cash,upi,card,bank_transfer'],
            'payment_type'     => ['required', 'in:advance,balance,full'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'paid_at'          => ['required', 'date'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $data['hall_booking_id'] = $booking->id;
        $data['recorded_by']     = Auth::id();

        BookingPayment::create($data);

        // Recalculate payment status
        $totalPaid = $booking->payments()->sum('amount');
        $status    = 'pending';
        if ($totalPaid >= $booking->total_amount) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }
        $booking->update(['payment_status' => $status]);

        return back()->with('success', 'Payment recorded.');
    }

    public function kitchen(): View
    {
        $date = request('date', today()->toDateString());

        $bookings = HallBooking::with(['hall', 'mealPlan'])
            ->whereDate('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        return view('hall.bookings.kitchen', compact('bookings', 'date'));
    }
}
