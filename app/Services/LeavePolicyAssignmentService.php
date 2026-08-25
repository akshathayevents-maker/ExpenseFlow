<?php

namespace App\Services;

use App\Models\EmployeeLeavePolicy;
use App\Models\LeavePolicyTemplate;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Single code path for "insert a new effective-dated EmployeeLeavePolicy
// row" — used by both the existing per-employee override screen
// (Admin\LeavePolicyController) and template assignment (this service),
// so there is exactly one insert-only, non-overlapping-history mechanism
// in the app, never two. EmployeeLeavePolicy::currentFor()'s effective-
// dating semantics (effective_from alone selects "current"; is_active is
// an independent, never-auto-flipped switch) are left completely untouched
// here — this service only ever produces rows in that same shape.
class LeavePolicyAssignmentService
{
    /**
     * Inserts one new effective-dated EmployeeLeavePolicy row for
     * $employee + $leaveType. Never edits or deactivates any prior row.
     * Rejects if a row already exists on/after $effectiveFrom for this
     * leave type (same overlap guard LeavePolicyController::store() has
     * always used, extracted here so both callers share it).
     */
    public function applyPolicyChange(
        User $employee,
        LeaveType $leaveType,
        array $attrs,
        User $actor,
        Carbon $effectiveFrom,
    ): EmployeeLeavePolicy {
        $conflicting = $employee->leavePolicies()
            ->where('leave_type_id', $leaveType->id)
            ->whereDate('effective_from', '>=', $effectiveFrom->toDateString())
            ->exists();

        if ($conflicting) {
            throw ValidationException::withMessages([
                'effective_from' => "A leave policy change already exists on or after this date for {$leaveType->name}.",
            ]);
        }

        $policy = new EmployeeLeavePolicy();
        $policy->fill([
            'user_id'                => $employee->id,
            'leave_type_id'          => $leaveType->id,
            'annual_entitlement'     => $attrs['annual_entitlement'],
            'allocation_mode'        => $attrs['allocation_mode'],
            'monthly_accrual_amount' => $attrs['monthly_accrual_amount'] ?? 0,
            'effective_from'         => $effectiveFrom->toDateString(),
        ]);
        $policy->forceFill([
            'is_active'  => true,
            'created_by' => $actor->id,
        ]);
        $policy->save();

        return $policy;
    }

    /**
     * Assigns every item of $template to $employee as of $effectiveFrom,
     * inside one transaction — all rows are created or none are (the
     * atomicity requirement, e.g. for employee-creation wiring, holds
     * because the caller wraps user-creation + this call together).
     * Sets users.leave_policy_template_id to $template->id.
     *
     * A per-item conflict (an existing row on/after $effectiveFrom for
     * that leave type) aborts the whole assignment via the transaction —
     * this never happens for a brand-new employee (no prior rows), but
     * matters for the bulk "assign to existing employees" path.
     *
     * ── Authoritative template (leave-type removal) ──────────────────────
     * The new template is authoritative for the employee's active leave
     * types from $effectiveFrom onward: any leave type the employee is
     * currently active on (as of the day before $effectiveFrom) that is
     * NOT one of the new template's items gets an explicit closing row —
     * a new EmployeeLeavePolicy with allocation_mode =
     * EmployeeLeavePolicy::ALLOCATION_MODE_REMOVED, effective $effectiveFrom.
     * This never touches, deactivates, or deletes any prior row; it only
     * ever inserts, exactly like every other write in this service.
     * currentFor() for any date before $effectiveFrom is unaffected. For a
     * brand-new employee (no prior policies at all — the employee-creation
     * path), this set is always empty, so nothing extra is inserted.
     *
     * @return EmployeeLeavePolicy[] the newly created rows (template items
     *         plus any closing rows for removed leave types).
     */
    public function assignTemplate(User $employee, LeavePolicyTemplate $template, User $actor, Carbon $effectiveFrom): array
    {
        return DB::transaction(function () use ($employee, $template, $actor, $effectiveFrom) {
            $items = $template->items()->with('leaveType')->get();
            $newLeaveTypeIds = $items->pluck('leave_type_id');

            $created = [];
            foreach ($items as $item) {
                $created[] = $this->applyPolicyChange(
                    $employee,
                    $item->leaveType,
                    [
                        'annual_entitlement'     => $item->annual_entitlement,
                        'allocation_mode'        => $item->allocation_mode,
                        'monthly_accrual_amount' => $item->monthly_accrual_amount,
                    ],
                    $actor,
                    $effectiveFrom,
                );
            }

            $dayBefore = $effectiveFrom->copy()->subDay();
            $currentlyActiveTypes = LeaveType::active()->get()->filter(function (LeaveType $type) use ($employee, $dayBefore) {
                $current = EmployeeLeavePolicy::currentFor($employee, $type, $dayBefore);

                return $current && ! $current->isRemoved();
            });

            foreach ($currentlyActiveTypes as $leaveType) {
                if ($newLeaveTypeIds->contains($leaveType->id)) {
                    continue;
                }

                $created[] = $this->applyPolicyChange(
                    $employee,
                    $leaveType,
                    [
                        'annual_entitlement'     => 0,
                        'allocation_mode'        => EmployeeLeavePolicy::ALLOCATION_MODE_REMOVED,
                        'monthly_accrual_amount' => 0,
                    ],
                    $actor,
                    $effectiveFrom,
                );
            }

            $employee->forceFill(['leave_policy_template_id' => $template->id])->save();

            return $created;
        });
    }

    /**
     * Marks $template as the sole default, clearing any previous default
     * inside a transaction. "No default" (all templates false) remains a
     * valid state — this method is only ever called to explicitly set one.
     */
    public function setDefault(LeavePolicyTemplate $template): void
    {
        DB::transaction(function () use ($template) {
            LeavePolicyTemplate::where('is_default', true)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);

            $template->forceFill(['is_default' => true])->save();
        });
    }

    /**
     * Clears the default flag on $template, leaving "no default" as the
     * resulting state.
     */
    public function clearDefault(LeavePolicyTemplate $template): void
    {
        $template->forceFill(['is_default' => false])->save();
    }
}
