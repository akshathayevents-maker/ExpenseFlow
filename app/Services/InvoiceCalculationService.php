<?php

namespace App\Services;

use App\Models\HallBooking;

/**
 * Single source of truth for invoice figures (subtotal, CGST/SGST, grand
 * total, balance due). Both the invoice Blade view and the PDF render the
 * same result from here, so they can never drift apart.
 *
 * IMPORTANT: `HallBooking::total_amount` keeps its existing meaning
 * throughout the rest of the app (Hall + Meals + Services — the taxable
 * subtotal). GST is an invoice-level concern only; it is never written back
 * into total_amount, advance_amount, or balance_amount, so the dashboard,
 * reports, calendar, and payment-status logic are all unaffected by it.
 */
class InvoiceCalculationService
{
    public const DEFAULT_CGST_RATE = 3.00;
    public const DEFAULT_SGST_RATE = 3.00;

    /**
     * @return array{
     *     subtotal: float,
     *     line_items: array<int, array{label: string, description: ?string, amount: float}>,
     *     cgst_rate: float, cgst_amount: float,
     *     sgst_rate: float, sgst_amount: float,
     *     tax_total: float,
     *     grand_total: float,
     *     amount_received: float,
     *     balance_due: float,
     * }
     */
    public function calculate(HallBooking $booking, ?float $cgstRateOverride = null, ?float $sgstRateOverride = null): array
    {
        $cgstRate = $cgstRateOverride ?? (float) ($booking->cgst_rate ?? self::DEFAULT_CGST_RATE);
        $sgstRate = $sgstRateOverride ?? (float) ($booking->sgst_rate ?? self::DEFAULT_SGST_RATE);

        $lineItems = $this->lineItems($booking);
        $subtotal = round((float) $booking->total_amount, 2);

        $cgstAmount = round($subtotal * $cgstRate / 100, 2);
        $sgstAmount = round($subtotal * $sgstRate / 100, 2);
        $taxTotal = round($cgstAmount + $sgstAmount, 2);
        $grandTotal = round($subtotal + $taxTotal, 2);

        $amountReceived = round((float) $booking->total_paid, 2);
        $balanceDue = max(0, round($grandTotal - $amountReceived, 2));

        return [
            'subtotal'        => $subtotal,
            'line_items'      => $lineItems,
            'cgst_rate'       => $cgstRate,
            'cgst_amount'     => $cgstAmount,
            'sgst_rate'       => $sgstRate,
            'sgst_amount'     => $sgstAmount,
            'tax_total'       => $taxTotal,
            'grand_total'     => $grandTotal,
            'amount_received' => $amountReceived,
            'balance_due'     => $balanceDue,
        ];
    }

    /**
     * Individual billable components (hall / catering / add-on services).
     * Kept as separate rows rather than one opaque "Booking Value" line —
     * and structured as a plain list so a future per-plan (e.g. veg/non-veg
     * mixed catering) breakdown can add more rows here without touching the
     * total logic above.
     *
     * @return array<int, array{label: string, description: ?string, amount: float}>
     */
    public function lineItems(HallBooking $booking): array
    {
        $items = [];

        if ((float) $booking->hall_cost > 0) {
            $items[] = [
                'label'       => 'Hall / Venue Charges',
                'description' => $booking->location_label,
                'amount'      => round((float) $booking->hall_cost, 2),
            ];
        }

        if ($booking->uses_mixed_food) {
            // Structured split rows — never collapsed into one opaque food
            // line. Each row's amount was already computed and stored
            // server-side when the booking was saved (see
            // HallBookingController::saveFoodSplits()), so this is purely
            // a read/format step, not a second calculation.
            foreach ($booking->foodSplits as $split) {
                $items[] = [
                    'label'       => $split->meal_plan_name . ' Food Plan',
                    'description' => number_format($split->guest_count) . ' guests × '
                        . '₹' . number_format((float) $split->price_per_guest, 2) . '/guest',
                    'amount'      => round((float) $split->amount, 2),
                ];
            }
        } elseif ($booking->mealPlan && (float) $booking->mealPlan->price_per_person > 0) {
            $items[] = [
                'label'       => 'Food / Catering — ' . $booking->mealPlan->name,
                'description' => number_format($booking->number_of_people) . ' guests × '
                    . '₹' . number_format((float) $booking->mealPlan->price_per_person, 2) . '/person',
                'amount'      => round((float) $booking->meal_cost, 2),
            ];
        }

        foreach ($booking->additionalServices as $service) {
            $items[] = [
                'label'       => $service->service_name,
                'description' => $service->description,
                'amount'      => round((float) $service->amount, 2),
            ];
        }

        // Nothing itemizable matched the subtotal (e.g. legacy bookings
        // saved before line-item tracking existed) — fall back to a single
        // row so the total is never shown without an explanation.
        if (empty($items) && (float) $booking->total_amount > 0) {
            $items[] = [
                'label'       => 'Booking Charges',
                'description' => null,
                'amount'      => round((float) $booking->total_amount, 2),
            ];
        }

        return $items;
    }
}
