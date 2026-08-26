<?php

namespace App\Policies;

use App\Models\EmployeeAttendanceRegularization;
use App\Models\User;

class EmployeeAttendanceRegularizationPolicy
{
    public function view(User $user, EmployeeAttendanceRegularization $regularization): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->id === $regularization->user_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    // Mirrors LeaveRequestPolicy::cancel(): cancellation after approval is
    // allowed (the service reverses the derived attendance effect), same as
    // an approved leave request can be cancelled.
    public function cancel(User $user, EmployeeAttendanceRegularization $regularization): bool
    {
        return $user->id === $regularization->user_id
            && ($regularization->isPending() || $regularization->isApproved());
    }

    // Self-approval blocked, same rationale as EmployeeOvertimePolicy — no
    // manager/employee hierarchy, so a manager's own regularization request
    // must not be approvable by themselves.
    public function approve(User $user, EmployeeAttendanceRegularization $regularization): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $regularization->isPending()
            && $user->id !== $regularization->user_id;
    }

    public function reject(User $user, EmployeeAttendanceRegularization $regularization): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $regularization->isPending()
            && $user->id !== $regularization->user_id;
    }
}
