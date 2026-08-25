<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Salary management is admin-only, and only for real, active workforce
    // accounts — never an admin account, never a deactivated user. Route
    // model binding accepts any user id, so this must be enforced here
    // (server-side), not just by hiding the UI.
    public function manageSalary(User $admin, User $employee): bool
    {
        return $admin->isAdmin()
            && in_array($employee->role, ['employee', 'manager'], true)
            && (bool) $employee->is_active;
    }

    // Leave policy assignment is admin-only, mirroring manageSalary exactly
    // — only for real, active workforce accounts, never an admin account.
    public function manageLeavePolicy(User $admin, User $employee): bool
    {
        return $admin->isAdmin()
            && in_array($employee->role, ['employee', 'manager'], true)
            && (bool) $employee->is_active;
    }
}
