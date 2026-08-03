<?php

namespace App\Services\EventRequest;

use App\Models\EventRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Event Request lifecycle: creation, client submission, admin
 * edits, need-changes, approval, and rejection. Every state change writes
 * an immutable revision row — nothing is ever overwritten in place, so the
 * full "client submitted -> admin modified -> resubmitted -> approved"
 * history is always reconstructable.
 */
class EventRequestService
{
    public function __construct(
        private readonly EventRequestPricingService $pricing,
        private readonly EventRequestTokenService $tokens,
        private readonly EventRequestCalendarIntegrationService $calendar,
    ) {
    }

    /**
     * Admin creates the shell request (usually right after a phone call)
     * and a public link is generated immediately.
     */
    public function createDraft(array $data, User $admin): EventRequest
    {
        return DB::transaction(function () use ($data, $admin) {
            $eventRequest = EventRequest::create([
                ...$data,
                'created_by' => $admin->id,
                'status'     => 'draft',
            ]);

            $this->tokens->issue($eventRequest);

            $this->recordRevision($eventRequest, 'created', 'admin', $admin->name);

            return $eventRequest;
        });
    }

    /**
     * Client fills Step 1 + Step 2 and submits (first time, or after
     * addressing a "need changes" request).
     *
     * @param  int[]  $menuItemIds
     */
    public function submitFromClient(EventRequest $eventRequest, array $details, array $menuItemIds): EventRequest
    {
        return DB::transaction(function () use ($eventRequest, $details, $menuItemIds) {
            $priced = $this->pricing->priceSelection($menuItemIds);
            $wasNeedChanges = $eventRequest->status === 'need_changes';

            $eventRequest->fill($details);
            $eventRequest->per_person_cost = $priced['per_person_cost'];
            $eventRequest->estimated_total = $this->pricing->estimatedTotal(
                $priced['per_person_cost'],
                (int) ($details['guest_count'] ?? $eventRequest->guest_count ?? 0)
            );
            $eventRequest->status = $wasNeedChanges ? 'resubmitted' : 'submitted';
            $eventRequest->submitted_at = now();
            $eventRequest->save();

            $eventRequest->items()->delete();
            foreach ($priced['items'] as $item) {
                $eventRequest->items()->create($item);
            }

            $this->recordRevision(
                $eventRequest,
                $wasNeedChanges ? 'client_resubmitted' : 'client_submitted',
                'client',
                $eventRequest->client_name
            );

            return $eventRequest->fresh(['items']);
        });
    }

    /**
     * Admin edits any field (guest count, date, menu, notes) during review.
     *
     * @param  int[]|null  $menuItemIds  Null = leave menu selection untouched.
     */
    public function adminUpdate(EventRequest $eventRequest, array $details, ?array $menuItemIds, User $admin): EventRequest
    {
        return DB::transaction(function () use ($eventRequest, $details, $menuItemIds, $admin) {
            $eventRequest->fill($details);

            if ($menuItemIds !== null) {
                $priced = $this->pricing->priceSelection($menuItemIds);
                $eventRequest->per_person_cost = $priced['per_person_cost'];
                $eventRequest->items()->delete();
                foreach ($priced['items'] as $item) {
                    $eventRequest->items()->create($item);
                }
            }

            $eventRequest->estimated_total = $this->pricing->estimatedTotal(
                (float) $eventRequest->per_person_cost,
                (int) ($eventRequest->guest_count ?? 0)
            );

            if ($eventRequest->status === 'submitted') {
                $eventRequest->status = 'under_review';
            }

            $eventRequest->save();

            $this->recordRevision($eventRequest, 'admin_modified', 'admin', $admin->name);

            return $eventRequest->fresh(['items']);
        });
    }

    public function requestChanges(EventRequest $eventRequest, string $comment, User $admin): EventRequest
    {
        $eventRequest->status = 'need_changes';
        $eventRequest->admin_comment = $comment;
        $eventRequest->save();

        $this->recordRevision($eventRequest, 'need_changes', 'admin', $admin->name, $comment);

        return $eventRequest;
    }

    public function reject(EventRequest $eventRequest, string $comment, User $admin): EventRequest
    {
        $eventRequest->status = 'rejected';
        $eventRequest->admin_comment = $comment;
        $eventRequest->rejected_at = now();
        $eventRequest->save();

        $this->tokens->deactivate($eventRequest);

        $this->recordRevision($eventRequest, 'rejected', 'admin', $admin->name, $comment);

        return $eventRequest;
    }

    public function approve(EventRequest $eventRequest, User $admin): EventRequest
    {
        return DB::transaction(function () use ($eventRequest, $admin) {
            $booking = $this->calendar->createBookingForApprovedRequest($eventRequest, $admin);

            $eventRequest->status = 'scheduled';
            $eventRequest->hall_booking_id = $booking->id;
            $eventRequest->approved_at = now();
            $eventRequest->save();

            $this->recordRevision($eventRequest, 'approved', 'admin', $admin->name);

            return $eventRequest;
        });
    }

    private function recordRevision(EventRequest $eventRequest, string $action, string $actorType, ?string $actorName, ?string $comment = null): void
    {
        $eventRequest->revisions()->create([
            'action'     => $action,
            'actor_type' => $actorType,
            'actor_name' => $actorName,
            'comment'    => $comment,
            'snapshot'   => $eventRequest->fresh(['items'])->toArray(),
        ]);
    }
}
