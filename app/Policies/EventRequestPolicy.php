<?php

namespace App\Policies;

use App\Models\EventRequest;
use App\Models\User;

class EventRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, EventRequest $eventRequest): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, EventRequest $eventRequest): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function decide(User $user, EventRequest $eventRequest): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && in_array($eventRequest->status, ['submitted', 'under_review', 'resubmitted'], true);
    }

    public function manageMenu(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
