<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\BookingPayment;
use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\HallBookingMeal;
use App\Models\MealPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HallBookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = HallBooking::with(['hall', 'creator'])
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

        return view('hall.bookings.index', compact('bookings', 'halls'));
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
            'total_amount'         => ['required', 'numeric', 'min:0'],
            'advance_amount'       => ['required', 'numeric', 'min:0'],
            'payment_status'       => ['required', 'in:pending,partial,paid'],
            'status'               => ['required', 'in:confirmed,cancelled,completed'],
            'notes'                => ['nullable', 'string', 'max:2000'],
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
            $data['created_by']  = auth()->id();
            $data['has_breakfast'] = $request->boolean('has_breakfast');
            $data['has_lunch']     = $request->boolean('has_lunch');
            $data['has_dinner']    = $request->boolean('has_dinner');

            $booking = HallBooking::create($data);

            // Record advance payment if > 0
            if ((float) $data['advance_amount'] > 0) {
                BookingPayment::create([
                    'hall_booking_id' => $booking->id,
                    'recorded_by'     => auth()->id(),
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
        $booking->load(['hall', 'mealPlan', 'creator', 'meals', 'payments.recorder']);
        return view('hall.bookings.show', compact('booking'));
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
            'total_amount'         => ['required', 'numeric', 'min:0'],
            'advance_amount'       => ['required', 'numeric', 'min:0'],
            'payment_status'       => ['required', 'in:pending,partial,paid'],
            'status'               => ['required', 'in:confirmed,cancelled,completed'],
            'notes'                => ['nullable', 'string', 'max:2000'],
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

        $booking->update($data);

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
        return view('hall.bookings.calendar', compact('halls'));
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

        $colors = ['confirmed' => '#16a34a', 'completed' => '#2563eb', 'cancelled' => '#dc2626'];

        $events = $query->get()->map(fn($b) => [
            'id'    => $b->id,
            'title' => $b->customer_name . ' — ' . $b->hall->name,
            'start' => $b->booking_date->toDateString() . 'T' . $b->start_time,
            'end'   => $b->booking_date->toDateString() . 'T' . $b->end_time,
            'color' => $colors[$b->status] ?? '#64748b',
            'extendedProps' => [
                'customer'     => $b->customer_name,
                'hall'         => $b->hall->name,
                'event_type'   => $b->event_type,
                'people'       => $b->number_of_people,
                'status'       => $b->status,
                'amount'       => $b->total_amount,
                'url'          => route('hall.bookings.show', $b),
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
        $data['recorded_by']     = auth()->id();

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
