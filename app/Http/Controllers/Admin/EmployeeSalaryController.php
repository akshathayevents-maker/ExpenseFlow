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
        $search = $request->get('search', '');

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
            ))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $now = now();
        $currentSalaries = $employees->getCollection()
            ->mapWithKeys(fn (User $employee) => [$employee->id => $employee->currentSalaryAsOf($now)]);

        return view('admin.salaries.index', compact('employees', 'currentSalaries', 'search'));
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
