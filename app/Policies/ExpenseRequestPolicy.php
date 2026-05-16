<?php

namespace App\Policies;

use App\Models\ExpenseRequest;
use App\Models\User;

class ExpenseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ExpenseRequest $request): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->id === $request->requested_by;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function approve(User $user, ExpenseRequest $request): bool
    {
        return ($user->isAdmin() || $user->isManager()) && $request->isPending();
    }

    public function reject(User $user, ExpenseRequest $request): bool
    {
        return ($user->isAdmin() || $user->isManager()) && $request->isPending();
    }

    public function markPaid(User $user, ExpenseRequest $request): bool
    {
        return $user->isAdmin() && $request->isApproved();
    }

    public function markCompleted(User $user, ExpenseRequest $request): bool
    {
        return $user->isAdmin() && $request->isPaid();
    }

    public function delete(User $user, ExpenseRequest $request): bool
    {
        return $user->isAdmin();
    }
}
