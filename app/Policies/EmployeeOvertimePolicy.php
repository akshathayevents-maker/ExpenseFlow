<?php

namespace App\Policies;

use App\Models\EmployeeOvertime;
use App\Models\User;

class EmployeeOvertimePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EmployeeOvertime $ot): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->id === $ot->user_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function cancel(User $user, EmployeeOvertime $ot): bool
    {
        return $user->id === $ot->user_id && $ot->isPending();
    }

    // Self-approval is blocked explicitly (unlike ExpenseRequestPolicy, which
    // has no such guard) — with no manager/employee hierarchy in this app, a
    // manager or admin submitting their own OT must not also be able to
    // approve/reject it.
    public function approve(User $user, EmployeeOvertime $ot): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $ot->isPending()
            && $user->id !== $ot->user_id;
    }

    public function reject(User $user, EmployeeOvertime $ot): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $ot->isPending()
            && $user->id !== $ot->user_id;
    }

    // Admin-only: recording a historical OT entry on behalf of another
    // employee (origin=admin_recorded).
    public function recordForOther(User $user): bool
    {
        return $user->isAdmin();
    }
}
