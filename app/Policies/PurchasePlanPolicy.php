<?php

namespace App\Policies;

use App\Models\PurchasePlan;
use App\Models\User;

/**
 * Purchase plans: any active staff member can prepare one and send it for approval;
 * admins approve / send back; the creator, storekeepers and admins buy and close an approved plan.
 */
class PurchasePlanPolicy
{
    private function storeSide(User $u): bool
    {
        return $u->isAdmin() || $u->isManager() || $u->isStorekeeper();
    }

    private function mine(User $u, PurchasePlan $p): bool
    {
        return (int) $p->created_by === (int) $u->id;
    }

    public function viewAny(User $u): bool { return (bool) $u->is_active; }

    public function view(User $u, PurchasePlan $p): bool { return $u->is_active && ($this->mine($u, $p) || $this->storeSide($u)); }

    public function create(User $u): bool { return (bool) $u->is_active; }

    public function update(User $u, PurchasePlan $p): bool { return $p->isEditable() && ($this->mine($u, $p) || $u->isAdmin()); }

    public function submit(User $u, PurchasePlan $p): bool { return in_array($p->status, ['draft', 'rejected'], true) && ($this->mine($u, $p) || $u->isAdmin()); }

    public function approve(User $u, PurchasePlan $p): bool { return $u->isAdmin() && in_array($p->status, ['draft', 'submitted'], true); }

    public function reject(User $u, PurchasePlan $p): bool { return $u->isAdmin() && $p->status === 'submitted'; }

    public function buy(User $u, PurchasePlan $p): bool
    {
        return in_array($p->status, ['approved', 'ordered'], true) && ($this->mine($u, $p) || $u->isStorekeeper() || $u->isAdmin());
    }

    public function close(User $u, PurchasePlan $p): bool { return $this->buy($u, $p); }

    public function cancel(User $u, PurchasePlan $p): bool
    {
        return in_array($p->status, ['draft', 'submitted', 'rejected', 'approved'], true) && ($this->mine($u, $p) || $u->isAdmin());
    }
}
