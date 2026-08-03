<?php

namespace App\Services\EventRequest;

use App\Models\EventRequest;
use App\Models\HallBooking;
use App\Models\User;

/**
 * Bridges an approved Event Request into the existing Hall Booking / Calendar
 * module WITHOUT modifying it. An approved request simply produces a normal
 * `food_only` HallBooking row — the existing calendar, list, and show views
 * pick it up automatically because they just query HallBooking.
 *
 * Time windows are inferred from meal_type since the Event Request Portal
 * never asks the client for exact start/end times (that's a hall-booking
 * concept, not a catering-request one).
 */
class EventRequestCalendarIntegrationService
{
    private const MEAL_TIME_WINDOWS = [
        'breakfast' => ['07:00', '09:00'],
        'lunch'     => ['12:00', '15:00'],
        'dinner'    => ['19:00', '22:00'],
        'reception' => ['18:00', '21:00'],
        'high_tea'  => ['16:00', '18:00'],
    ];

    public function createBookingForApprovedRequest(EventRequest $eventRequest, User $approvedBy): HallBooking
    {
        [$start, $end] = self::MEAL_TIME_WINDOWS[$eventRequest->meal_type] ?? ['10:00', '13:00'];

        $mealFlags = [
            'has_breakfast' => $eventRequest->meal_type === 'breakfast',
            'has_lunch'     => in_array($eventRequest->meal_type, ['lunch', 'reception'], true),
            'has_dinner'    => in_array($eventRequest->meal_type, ['dinner', 'reception'], true),
        ];

        return HallBooking::create([
            'created_by'       => $approvedBy->id,
            'booking_type'     => 'food_only',
            'service_location' => $eventRequest->event_name ?: 'Client Event Request',
            'customer_name'    => (string) $eventRequest->client_name,
            'customer_mobile'  => (string) $eventRequest->client_mobile,
            'event_type'       => 'other',
            'booking_date'     => $eventRequest->event_date,
            'start_time'       => $start,
            'end_time'         => $end,
            'number_of_people' => $eventRequest->guest_count ?? 0,
            ...$mealFlags,
            'hall_cost'        => 0,
            'total_amount'     => $eventRequest->estimated_total,
            'advance_amount'   => 0,
            'payment_status'   => 'pending',
            'status'           => 'confirmed',
            'notes'            => sprintf(
                "Created from Event Request Portal (%s).\n\n%s",
                $eventRequest->referenceNumber(),
                (string) $eventRequest->special_instructions
            ),
        ]);
    }
}
