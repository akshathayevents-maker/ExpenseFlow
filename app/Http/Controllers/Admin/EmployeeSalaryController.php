<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetEmployeeSalaryRequest;
use App\Http\Requests\Admin\SetOvertimeConfigRequest;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
use App\Models\User;
use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeSalaryService;
use App\Services\MonthlyPayableService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeSalaryController extends Controller
{
    public function __construct(
        private EmployeeSalaryService $service,
        private MonthlyPayableService $monthlyPayableService,
        private AdvanceEligibilityService $advanceEligibilityService,
    ) {}

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

        // Overtime Configuration section — per-employee configurable OT
        // multipliers (Overtime redesign Part 1). No row means "implicit
        // default"; the helpers on EmployeeOvertimeConfig are the SINGLE
        // place that fallback logic lives.
        $overtimeConfig            = $employee->overtimeConfig;
        $overtimeMultiplierOptions = EmployeeOvertimeConfig::MULTIPLIER_OPTIONS;
        $allowedMultipliers        = EmployeeOvertimeConfig::allowedMultipliersFor($employee);
        $defaultMultiplier         = EmployeeOvertimeConfig::defaultMultiplierFor($employee);

        return view('admin.employees.salaries.index', compact(
            'employee', 'currentSalary', 'history',
            'overtimeConfig', 'overtimeMultiplierOptions', 'allowedMultipliers', 'defaultMultiplier',
        ));
    }

    // Overtime Configuration save — separate endpoint from the salary
    // form above (distinct concern, distinct request class), but rendered
    // as a section on the same Compensation page.
    public function storeOvertimeConfig(SetOvertimeConfigRequest $request, User $employee): RedirectResponse
    {
        $this->authorize('manageSalary', $employee);

        EmployeeOvertimeConfig::updateOrCreate(
            ['user_id' => $employee->id],
            [
                'allowed_multipliers' => array_values(array_map('floatval', $request->validated('allowed_multipliers'))),
                'default_multiplier'  => (float) $request->validated('default_multiplier'),
            ],
        );

        return redirect()->route('admin.employees.salaries.index', $employee)
            ->with('success', 'Overtime configuration updated successfully.');
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

    // ── Monthly Payable (Payroll) ────────────────────────────────────────
    // Reuses MonthlyPayableService::calculate() exactly as-is for every
    // component (salary/LOP-adjusted payable_salary, approved OT via
    // approved_amount, advance_deduction_amount) — no calculation is
    // duplicated here, this controller only iterates the workforce and
    // renders what the service already returns.
    public function payrollIndex(Request $request): View
    {
        $month = $this->resolvePayrollMonth($request->get('month'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd   = $month->copy()->endOfMonth();

        $search = $request->get('search', '');

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
            ))
            ->orderBy('name')
            ->get();

        $rows = [];
        $totalNetPayable = 0.0;
        $employeesWithSalary = 0;

        foreach ($employees as $employee) {
            try {
                $breakdown = $this->monthlyPayableService->calculate($employee, $monthStart, $monthEnd);
            } catch (DomainException $e) {
                $rows[] = [
                    'employee' => $employee,
                    'available' => false,
                    'reason' => $e->getMessage(),
                ];
                continue;
            }

            $employeesWithSalary++;
            $totalNetPayable += $breakdown['net_payable'];

            $rows[] = [
                'employee' => $employee,
                'available' => true,
                'breakdown' => $breakdown,
            ];
        }

        // ── Daily Advance Eligibility (as-of date) ──────────────────────────
        // Reuses AdvanceEligibilityService::evaluate() exactly as-is per
        // employee — evaluate() genuinely accepts an arbitrary Carbon $asOf
        // (verified: it scopes MonthlyPayableService::calculate() to
        // $asOf->startOfMonth() .. min($asOf, endOfMonth)), so the selected
        // date is not cosmetic — no calculation is duplicated here.
        $eligibilityAsOf = $this->resolveEligibilityDate($request->get('eligibility_date'));
        $eligSearch = $request->get('elig_search', '');
        $eligStatus = $request->get('elig_status', ''); // '', 'eligible', 'unavailable'

        $eligEmployees = User::whereIn('role', ['employee', 'manager'])
            ->when($eligSearch, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$eligSearch}%")
                ->orWhere('email', 'ilike', "%{$eligSearch}%")
            ))
            ->orderBy('name')
            ->get();

        // KPIs are aggregated purely from the already-evaluated per-employee
        // results below — no new formula, no second call per employee, no
        // recomputation of anything AdvanceEligibilityService returns.
        $eligibleCount = 0;
        $totalEligibleAmount = 0.0;
        $withOutstandingCount = 0;
        $unavailableCount = 0;

        $eligibilityRows = [];
        foreach ($eligEmployees as $employee) {
            $eligibility = $this->advanceEligibilityService->evaluate($employee, $eligibilityAsOf->copy());

            $isEligible = $eligibility['salary_configured'] && $eligibility['unavailable_reason'] === null;

            if ($isEligible) {
                $eligibleCount++;
                $totalEligibleAmount += $eligibility['eligible_advance_amount'];
                if ($eligibility['outstanding_amount'] > 0) {
                    $withOutstandingCount++;
                }
            } else {
                $unavailableCount++;
            }

            if ($eligStatus === 'eligible' && !$isEligible) {
                continue;
            }
            if ($eligStatus === 'unavailable' && $isEligible) {
                continue;
            }

            $eligibilityRows[] = [
                'employee' => $employee,
                'eligibility' => $eligibility,
            ];
        }

        return view('admin.payroll.index', compact(
            'rows', 'month', 'search', 'totalNetPayable', 'employeesWithSalary',
            'eligibilityRows', 'eligibilityAsOf', 'eligSearch', 'eligStatus',
            'eligibleCount', 'totalEligibleAmount', 'withOutstandingCount', 'unavailableCount',
        ));
    }

    public function payrollShow(Request $request, User $employee): View
    {
        $month = $this->resolvePayrollMonth($request->get('month'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd   = $month->copy()->endOfMonth();

        $breakdown = null;
        $unavailableReason = null;

        try {
            $breakdown = $this->monthlyPayableService->calculate($employee, $monthStart, $monthEnd);
        } catch (DomainException $e) {
            $unavailableReason = $e->getMessage();
        }

        // Per-record OT detail for the month — same filter MonthlyPayableService
        // uses internally, just without collapsing to a sum, so the admin can
        // see exactly which records contributed approved_amount.
        $overtimeRecords = EmployeeOvertime::where('user_id', $employee->id)
            ->whereDate('ot_date', '>=', $monthStart->toDateString())
            ->whereDate('ot_date', '<=', $monthEnd->toDateString())
            ->orderBy('ot_date')
            ->get();

        return view('admin.payroll.show', compact(
            'employee', 'month', 'breakdown', 'unavailableReason', 'overtimeRecords',
        ));
    }

    private function resolvePayrollMonth(?string $month): Carbon
    {
        if ($month) {
            try {
                return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            } catch (\Exception $e) {
                // fall through to current month
            }
        }

        return now()->startOfMonth();
    }

    private function resolveEligibilityDate(?string $date): Carbon
    {
        if ($date) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
            } catch (\Exception $e) {
                // fall through to today
            }
        }

        return now()->startOfDay();
    }
}
