<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->user_id
            && ($leaveRequest->isPending() || $leaveRequest->isApproved());
    }

    // Admin-or-manager, no reporting hierarchy — same convention already
    // used by EmployeeAdvancePolicy/EmployeeOvertimePolicy/
    // EmployeeAttendanceRegularizationPolicy. Self-approval blocked for the
    // same reason: no manager/employee hierarchy exists to make it safe.
    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $leaveRequest->isPending()
            && $user->id !== $leaveRequest->user_id;
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        return ($user->isAdmin() || $user->isManager())
            && $leaveRequest->isPending()
            && $user->id !== $leaveRequest->user_id;
    }
}
