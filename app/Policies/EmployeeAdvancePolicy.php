<?php

namespace App\Policies;

use App\Models\EmployeeAdvance;
use App\Models\User;

class EmployeeAdvancePolicy
{
    public function view(User $user, EmployeeAdvance $advance): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->id === $advance->user_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function cancel(User $user, EmployeeAdvance $advance): bool
    {
        return $user->id === $advance->user_id && $advance->isPending();
    }

    // Self-approval blocked — same rationale as every other approval flow
    // this session (Overtime, Attendance Regularization): no manager/
    // employee hierarchy exists, so a manager's own advance must not be
    // approvable by themselves.
    public function approve(User $user, EmployeeAdvance $advance): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $advance->isPending()
            && $user->id !== $advance->user_id;
    }

    public function reject(User $user, EmployeeAdvance $advance): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $advance->isPending()
            && $user->id !== $advance->user_id;
    }

    public function disburse(User $user, EmployeeAdvance $advance): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $advance->isApproved()
            && $advance->isUnpaid()
            && $user->id !== $advance->user_id;
    }

    public function recordRepayment(User $user, EmployeeAdvance $advance): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $advance->isPaid()
            && (float) $advance->outstanding_amount > 0.0;
    }
}
