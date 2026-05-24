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
        // Accepts both 'approved' (normal workflow) and 'pending_payment'
        // (QR-payment workflow — created directly in pending_payment state).
        return ($user->isAdmin() || $user->isManager())
            && ($request->isApproved() || $request->isPendingPayment());
    }

    /**
     * Admin/manager can reject an expense from the public payment page
     * as long as it is not already settled or previously rejected.
     */
    public function rejectFromPayPage(User $user, ExpenseRequest $request): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && ! $request->isSettled()
            && ! $request->isRejected();
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
