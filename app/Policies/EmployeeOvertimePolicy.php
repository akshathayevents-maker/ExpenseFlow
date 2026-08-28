<?php

namespace App\Policies;

use App\Models\EmployeeOvertime;
use App\Models\Setting;
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

    // Temporary pause on employee self-requesting (business requirement):
    // gated by the `employee_overtime_requests_enabled` Setting, mirroring
    // the no-self-approval flag-style check already used in approve()/
    // reject() below. This deliberately does NOT affect view/cancel/
    // recordForOther — only the ability to CREATE a new request. Flipping
    // the Setting back to true re-enables this check with no code change.
    public function create(User $user): bool
    {
        if (! Setting::get('employee_overtime_requests_enabled', false)) {
            return false;
        }

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

    // Admin-only "delete" for an admin-recorded overtime entry — implemented
    // as a status transition to 'cancelled' (never a hard SQL DELETE), so
    // the audit row is preserved exactly like every other workflow entity in
    // this app (leave/regularization/advance). Only admin_recorded entries
    // may be removed this way, and only while not already cancelled — an
    // already-rejected/cancelled entry has nothing left to remove.
    public function delete(User $user, EmployeeOvertime $ot): bool
    {
        return $user->isAdmin()
            && $ot->origin === 'admin_recorded'
            && ! $ot->isCancelled();
    }
}
