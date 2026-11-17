<?php

namespace App\Policies;

use App\Models\MaterialRequest;
use App\Models\User;

class MaterialRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function view(User $user, MaterialRequest $r): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isStorekeeper() || $r->requested_by === $user->id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function update(User $user, MaterialRequest $r): bool
    {
        return $r->status === 'draft' && ($r->requested_by === $user->id || $user->isAdmin());
    }

    public function submit(User $user, MaterialRequest $r): bool
    {
        return $this->update($user, $r);
    }

    public function approve(User $user, MaterialRequest $r): bool
    {
        return $user->isAdmin() && $r->status === 'submitted';
    }

    public function reject(User $user, MaterialRequest $r): bool
    {
        return $this->approve($user, $r);
    }

    public function handover(User $user, MaterialRequest $r): bool
    {
        return $user->isStorekeeper() && $r->status === 'approved';
    }

    public function cancel(User $user, MaterialRequest $r): bool
    {
        if ($user->isAdmin()) {
            return in_array($r->status, ['draft', 'submitted', 'approved'], true);
        }

        return $r->requested_by === $user->id && in_array($r->status, ['draft', 'submitted'], true);
    }

    public function recordReturn(User $user, MaterialRequest $r): bool
    {
        return $user->isStorekeeper() && $r->status === 'issued';
    }

    public function reconcile(User $user, MaterialRequest $r): bool
    {
        return $r->status === 'issued' && ($user->isAdmin() || $user->isManager() || $r->requested_by === $user->id);
    }

    public function review(User $user, MaterialRequest $r): bool
    {
        return $user->isAdmin() && $r->reconciliation_status === 'pending_review';
    }

    public function revise(User $user, MaterialRequest $r): bool
    {
        return in_array($r->status, ['submitted', 'approved', 'issued', 'reconciled'], true)
            && ($user->isAdmin() || $user->isManager() || $r->requested_by === $user->id);
    }
}
