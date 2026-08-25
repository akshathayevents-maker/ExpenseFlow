<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetEmployeeSalaryRequest;
use App\Models\User;
use App\Services\EmployeeSalaryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeSalaryController extends Controller
{
    public function __construct(private EmployeeSalaryService $service) {}

    // Global "Employee Salaries" list — the actual discoverable entry point
    // for Compensation / Payroll. role.admin middleware (applied to the
    // whole admin route group) is the only guard needed here, same as
    // EmployeeController::index; manageSalary is checked per-employee below,
    // on the per-employee index()/store() actions.
    public function listAll(\Illuminate\Http\Request $request): View
    {
        $search       = $request->get('search', '');
        $role         = $request->get('role', '');
        $salaryStatus = $request->get('salary_status', ''); // '', 'set', 'not_set'

        $now   = now();
        $today = $now->toDateString();

        // The "currently effective" condition mirrors EmployeeSalary's own
        // isCurrentAsOf()/User::currentSalaryAsOf() rule exactly (effective_from
        // <= today AND (effective_to is null OR effective_to >= today)) — this
        // is not a new business rule, just that same existing rule expressed
        // as a query condition so it can be used for filtering/counting.
        $isCurrent = function ($q) use ($today) {
            $q->whereDate('effective_from', '<=', $today)
              ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today));
        };

        $workforce = User::whereIn('role', ['employee', 'manager']);

        $employees = (clone $workforce)
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
            ))
            ->when($role, fn ($q, $v) => $q->where('role', $v))
            ->when($salaryStatus === 'set',     fn ($q) => $q->whereHas('salaries', $isCurrent))
            ->when($salaryStatus === 'not_set', fn ($q) => $q->whereDoesntHave('salaries', $isCurrent))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $currentSalaries = $employees->getCollection()
            ->mapWithKeys(fn (User $employee) => [$employee->id => $employee->currentSalaryAsOf($now)]);

        // Payroll summary — computed once across the WHOLE workforce (not
        // just the current page/filter), from the same "current" condition
        // above. At most one row per user can satisfy it, since
        // EmployeeSalaryService never allows overlapping effective periods
        // — so summing/counting this row set needs no per-user grouping.
        $workforceIds = (clone $workforce)->pluck('id');
        $currentSalaryRows = \App\Models\EmployeeSalary::whereIn('user_id', $workforceIds)
            ->where($isCurrent)
            ->get(['user_id', 'monthly_salary']);

        $totalEmployees     = $workforceIds->count();
        $configuredCount    = $currentSalaryRows->count();
        $notConfiguredCount = $totalEmployees - $configuredCount;
        $totalMonthlyPayroll = (float) $currentSalaryRows->sum('monthly_salary');

        return view('admin.salaries.index', compact(
            'employees', 'currentSalaries', 'search', 'role', 'salaryStatus',
            'totalEmployees', 'configuredCount', 'notConfiguredCount', 'totalMonthlyPayroll',
        ));
    }

    public function index(User $employee): View
    {
        $this->authorize('manageSalary', $employee);

        $currentSalary = $employee->currentSalaryAsOf(now());
        $history = $employee->salaries()->orderByDesc('effective_from')->with('creator')->get();

        return view('admin.employees.salaries.index', compact('employee', 'currentSalary', 'history'));
    }

    public function store(SetEmployeeSalaryRequest $request, User $employee): RedirectResponse
    {
        $this->authorize('manageSalary', $employee);

        $this->service->setSalary(
            $employee,
            (float) $request->validated('monthly_salary'),
            Carbon::parse($request->validated('effective_from')),
            auth()->user(),
        );

        return redirect()->route('admin.employees.salaries.index', $employee)
            ->with('success', 'Salary updated successfully.');
    }
}
