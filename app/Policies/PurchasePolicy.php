<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function view(User $user, Purchase $p): bool
    {
        return $user->isAdmin() || $user->isStorekeeper() || $p->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function update(User $user, Purchase $p): bool
    {
        if ($p->status === 'draft') {
            return $user->isAdmin() || $p->created_by === $user->id;
        }

        return $p->status === 'submitted' && $user->isAdmin();
    }

    public function submit(User $user, Purchase $p): bool
    {
        return $p->status === 'draft' && ($user->isAdmin() || $p->created_by === $user->id);
    }

    public function approve(User $user, Purchase $p): bool
    {
        return $user->isAdmin() && $p->status === 'submitted';
    }

    public function reject(User $user, Purchase $p): bool
    {
        return $this->approve($user, $p);
    }

    public function receive(User $user, Purchase $p): bool
    {
        return $user->isStorekeeper() && $p->isReceivable();
    }

    public function cancel(User $user, Purchase $p): bool
    {
        if ($user->isAdmin()) {
            return in_array($p->status, ['draft', 'submitted', 'approved'], true);
        }

        return $p->created_by === $user->id && in_array($p->status, ['draft', 'submitted'], true);
    }

    public function complete(User $user, Purchase $p): bool
    {
        return $user->isAdmin() && in_array($p->status, ['received', 'partially_received'], true);
    }
}
