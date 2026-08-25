<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetLeavePolicyRequest;
use App\Models\LeavePolicyTemplate;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeavePolicyAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// Admin-only, per-employee leave policy management. A policy is
// effective-dated history — "update" NEVER edits an existing row in place
// and NEVER deactivates a prior row either (unlike EmployeeSalaryService,
// which closes the predecessor via effective_to). EmployeeLeavePolicy has
// no effective_to; effective_from alone determines which row is "current"
// for a given date (see EmployeeLeavePolicy::currentFor()). Deactivating
// the predecessor at write time would create a real gap for any date
// between "now" and a future-dated new row's effective_from — so store()
// only ever INSERTS. `is_active` is a separate, independent enable/disable
// switch that this action never touches.
class LeavePolicyController extends Controller
{
    public function __construct(
        private LeavePolicyAssignmentService $assignmentService,
        private LeaveBalanceService $leaveBalanceService,
    ) {}

    public function index(User $employee): View
    {
        $this->authorize('manageLeavePolicy', $employee);

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $currentPolicies = $leaveTypes->mapWithKeys(
            fn (LeaveType $type) => [$type->id => \App\Models\EmployeeLeavePolicy::currentFor($employee, $type, now())]
        );
        $history = $employee->leavePolicies()->with('leaveType', 'creator')->orderByDesc('effective_from')->get();
        $leavePolicyTemplates = LeavePolicyTemplate::active()->orderBy('name')->get();
        $balances = $this->leaveBalanceService->balancesForAllTypes($employee, now());

        return view('admin.employees.leave-policies.index', compact(
            'employee', 'leaveTypes', 'currentPolicies', 'history', 'leavePolicyTemplates', 'balances',
        ));
    }

    public function store(SetLeavePolicyRequest $request, User $employee): RedirectResponse
    {
        $this->authorize('manageLeavePolicy', $employee);

        $leaveType = LeaveType::findOrFail($request->validated('leave_type_id'));
        $effectiveFrom = Carbon::parse($request->validated('effective_from'));

        // Delegates to the single shared "insert a new effective-dated
        // EmployeeLeavePolicy row" code path (also used by
        // LeavePolicyAssignmentService::assignTemplate()) — same overlap
        // guard as EmployeeSalaryService::setSalary(): only appending after
        // the latest known change for this leave type is allowed, so
        // history stays a clean, non-overlapping timeline.
        $this->assignmentService->applyPolicyChange(
            $employee,
            $leaveType,
            [
                'annual_entitlement'     => $request->validated('annual_entitlement'),
                'allocation_mode'        => $request->validated('allocation_mode'),
                'monthly_accrual_amount' => $request->validated('monthly_accrual_amount') ?? 0,
            ],
            auth()->user(),
            $effectiveFrom,
        );

        return redirect()->route('admin.employees.leave-policies.index', $employee)
            ->with('success', 'Leave policy saved.');
    }
}
