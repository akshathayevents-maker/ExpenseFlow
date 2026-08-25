<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkAssignLeavePolicyTemplateRequest;
use App\Http\Requests\Admin\StoreLeavePolicyTemplateRequest;
use App\Http\Requests\Admin\UpdateLeavePolicyTemplateRequest;
use App\Models\LeavePolicyTemplate;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeavePolicyAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Admin-only leave policy template configuration. A template is only ever
// a "stamp" used at assignment time (see LeavePolicyAssignmentService) —
// editing a template's items never touches any already-assigned
// EmployeeLeavePolicy row. Templates are never hard-deleted — only
// deactivated via is_active — mirroring LeaveType's convention exactly.
class LeavePolicyTemplateController extends Controller
{
    public function __construct(private LeavePolicyAssignmentService $assignmentService) {}

    public function index(): View
    {
        $templates = LeavePolicyTemplate::with(['items.leaveType', 'creator'])->orderBy('name')->get();

        $employeesWithoutTemplate = User::whereIn('role', ['employee', 'manager'])
            ->where('is_active', true)
            ->whereNull('leave_policy_template_id')
            ->orderBy('name')
            ->get();

        $allEmployees = User::whereIn('role', ['employee', 'manager'])
            ->where('is_active', true)
            ->with('leavePolicyTemplate')
            ->orderBy('name')
            ->get();

        return view('admin.leave-policy-templates.index', compact('templates', 'employeesWithoutTemplate', 'allEmployees'));
    }

    public function create(): View
    {
        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        return view('admin.leave-policy-templates.create', compact('leaveTypes'));
    }

    public function store(StoreLeavePolicyTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $template = LeavePolicyTemplate::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active'   => (bool) ($data['is_active'] ?? true),
                'created_by'  => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $template->items()->create([
                    'leave_type_id'          => $item['leave_type_id'],
                    'annual_entitlement'     => $item['annual_entitlement'],
                    'allocation_mode'        => $item['allocation_mode'],
                    'monthly_accrual_amount' => $item['monthly_accrual_amount'] ?? 0,
                ]);
            }

            if (! empty($data['is_default'])) {
                $this->assignmentService->setDefault($template);
            }
        });

        return redirect()->route('admin.leave-policy-templates.index')
            ->with('success', 'Leave policy template created.');
    }

    public function edit(LeavePolicyTemplate $leavePolicyTemplate): View
    {
        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $leavePolicyTemplate->load('items');

        return view('admin.leave-policy-templates.edit', [
            'template'   => $leavePolicyTemplate,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function update(UpdateLeavePolicyTemplateRequest $request, LeavePolicyTemplate $leavePolicyTemplate): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $leavePolicyTemplate) {
            $leavePolicyTemplate->update([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active'   => (bool) ($data['is_active'] ?? false),
            ]);

            // Items are fully replaced here — this only ever affects the
            // template's own item rows, never any EmployeeLeavePolicy row
            // already created from a prior version of this template.
            $leavePolicyTemplate->items()->delete();
            foreach ($data['items'] as $item) {
                $leavePolicyTemplate->items()->create([
                    'leave_type_id'          => $item['leave_type_id'],
                    'annual_entitlement'     => $item['annual_entitlement'],
                    'allocation_mode'        => $item['allocation_mode'],
                    'monthly_accrual_amount' => $item['monthly_accrual_amount'] ?? 0,
                ]);
            }
        });

        return redirect()->route('admin.leave-policy-templates.index')
            ->with('success', 'Leave policy template updated.');
    }

    public function setDefault(LeavePolicyTemplate $leavePolicyTemplate): RedirectResponse
    {
        $this->assignmentService->setDefault($leavePolicyTemplate);

        return back()->with('success', "\"{$leavePolicyTemplate->name}\" is now the default template for new employees.");
    }

    public function clearDefault(LeavePolicyTemplate $leavePolicyTemplate): RedirectResponse
    {
        $this->assignmentService->clearDefault($leavePolicyTemplate);

        return back()->with('success', 'Default template cleared. New employees will not be auto-assigned a template unless one is selected explicitly.');
    }

    // Assign this template to a single existing employee.
    public function assign(\App\Http\Requests\Admin\AssignLeavePolicyTemplateRequest $request, User $employee): RedirectResponse
    {
        $this->authorize('manageLeavePolicy', $employee);

        $template = LeavePolicyTemplate::findOrFail($request->validated('leave_policy_template_id'));

        $this->assignmentService->assignTemplate(
            $employee,
            $template,
            auth()->user(),
            Carbon::parse($request->validated('effective_from')),
        );

        return redirect()->route('admin.employees.leave-policies.index', $employee)
            ->with('success', "Assigned \"{$template->name}\" to {$employee->name}.");
    }

    // Bulk-assign a template to multiple existing employees at once.
    public function bulkAssign(BulkAssignLeavePolicyTemplateRequest $request): RedirectResponse
    {
        $template = LeavePolicyTemplate::findOrFail($request->validated('leave_policy_template_id'));
        $effectiveFrom = Carbon::parse($request->validated('effective_from'));
        $actor = auth()->user();

        $assigned = 0;
        $skipped = [];

        foreach (User::whereIn('id', $request->validated('employee_ids'))->get() as $employee) {
            if (! $actor->can('manageLeavePolicy', $employee)) {
                $skipped[] = $employee->name;
                continue;
            }

            try {
                $this->assignmentService->assignTemplate($employee, $template, $actor, $effectiveFrom);
                $assigned++;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $skipped[] = $employee->name;
            }
        }

        $message = "Assigned \"{$template->name}\" to {$assigned} employee(s).";
        if (! empty($skipped)) {
            $message .= ' Skipped: '.implode(', ', $skipped).'.';
        }

        return back()->with('success', $message);
    }
}
