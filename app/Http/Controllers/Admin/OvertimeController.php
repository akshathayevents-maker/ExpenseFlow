<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\AdminRecordOvertimeRequest;
use App\Http\Requests\Overtime\ApproveOvertimeRequest;
use App\Http\Requests\Overtime\RejectOvertimeRequest;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
use App\Models\Setting;
use App\Models\User;
use App\Services\OvertimeCalculationService;
use App\Services\OvertimeService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function __construct(
        private OvertimeService $service,
        private OvertimeCalculationService $calculationService,
    ) {}

    public function index(): View
    {
        $records = $this->service->listForManager();

        return view('admin.overtime.index', compact('records'));
    }

    public function create(Request $request): View
    {
        $this->authorize('recordForOther', EmployeeOvertime::class);

        $employees = User::where('is_active', true)->orderBy('name')->get();

        // Optional employee+period pre-filter (GET query params) to show the
        // existing admin-recorded allowances for that employee/pay-period
        // below the form, with a running total — reuses the same shared
        // ot-list partial as every other Overtime index page rather than a
        // new table implementation. "Pay period" = calendar month, matching
        // MonthlyPayableService's convention and the mode-check in
        // OvertimeService::recordHistorical().
        $selectedUserId = $request->integer('user_id') ?: null;

        // OT date, when already chosen (GET reselect on employee/date
        // change, or old() on a validation-failure redisplay), drives BOTH
        // the read-only "Pay Period" display and the "existing allowances"
        // list below — there is no separate pay-period concept and no
        // second employee selector anywhere on this page. Month derivation
        // matches MonthlyPayableService's existing calendar-month
        // convention verbatim.
        $otDateInput = $request->input('ot_date', old('ot_date'));
        $otDate = $otDateInput ? Carbon::parse($otDateInput) : null;
        $month = $otDate ? $otDate->copy()->startOfMonth() : Carbon::now()->startOfMonth();

        $employee = $selectedUserId ? $employees->firstWhere('id', $selectedUserId) : null;

        $hourlyRate = null;
        if ($employee && $otDate) {
            try {
                $hourlyRate = $this->calculationService->hourlyRateFor($employee, $otDate);
            } catch (DomainException $e) {
                $hourlyRate = null;
            }
        }

        $allowedMultipliers = $employee ? EmployeeOvertimeConfig::allowedMultipliersFor($employee) : EmployeeOvertimeConfig::IMPLICIT_DEFAULT_ALLOWED;
        $defaultMultiplier = $employee ? EmployeeOvertimeConfig::defaultMultiplierFor($employee) : EmployeeOvertimeConfig::IMPLICIT_DEFAULT_MULTIPLIER;

        $existingAllowances = collect();
        if ($selectedUserId) {
            $existingAllowances = EmployeeOvertime::where('user_id', $selectedUserId)
                ->where('origin', 'admin_recorded')
                ->whereYear('ot_date', $month->year)
                ->whereMonth('ot_date', $month->month)
                ->with(['user', 'reviewer'])
                ->latest('ot_date')
                ->get();
        }

        $runningTotal = $existingAllowances
            ->where('request_status', 'approved')
            ->sum(fn (EmployeeOvertime $ot) => (float) ($ot->approved_amount ?? 0));
        $allowanceMode = Setting::get('overtime_allowance_mode', 'multiple');

        return view('admin.overtime.create', compact(
            'employees', 'selectedUserId', 'otDate', 'month', 'existingAllowances', 'runningTotal', 'allowanceMode',
            'hourlyRate', 'allowedMultipliers', 'defaultMultiplier',
        ));
    }

    public function store(AdminRecordOvertimeRequest $request): RedirectResponse
    {
        $this->authorize('recordForOther', EmployeeOvertime::class);

        $employee = User::findOrFail($request->validated('user_id'));

        // Combined record+approve: creates the entry AND approves it in one
        // transaction (see OvertimeService::recordAndApprove()) — the admin
        // "Record Overtime" screen no longer leaves a pending record behind
        // requiring a separate approval step.
        $ot = $this->service->recordAndApprove(
            auth()->user(),
            $employee,
            $request->validated(),
            (float) $request->validated('multiplier'),
            $request->validated('manual_amount') !== null ? (float) $request->validated('manual_amount') : null,
            $request->validated('review_note'),
        );

        return redirect()->route('admin.overtime.show', $ot)->with('success', 'Overtime recorded and approved.');
    }

    // Admin-only "delete": a status transition to 'cancelled', never a hard
    // SQL DELETE — see EmployeeOvertimePolicy::delete() and
    // OvertimeService::cancel() (reused verbatim; it merely sets
    // request_status, which is exactly what "removing" this record means
    // here — MonthlyPayableService's approved_amount sum already filters on
    // request_status='approved', so a cancelled record is automatically
    // excluded from payroll with no special-case code).
    public function destroy(EmployeeOvertime $overtime): RedirectResponse
    {
        $this->authorize('delete', $overtime);

        $this->service->cancel($overtime, auth()->user());

        return back()->with('success', 'Overtime entry deleted.');
    }

    public function show(EmployeeOvertime $overtime): View
    {
        $this->authorize('view', $overtime);

        return view('admin.overtime.show', $this->approvalViewData($overtime));
    }

    // Shared by the show() actions on both Admin and Manager
    // OvertimeControllers so the approval UI (salary/hour, allowed
    // multipliers, default multiplier) is computed identically in both
    // places. Only meaningful when the record is still pending — salary
    // may no longer resolve for a long-approved historical record, so this
    // is best-effort and never blocks rendering the page.
    private function approvalViewData(EmployeeOvertime $overtime): array
    {
        $salaryPerHour = null;

        if ($overtime->isPending()) {
            try {
                $salaryPerHour = $this->calculationService->hourlyRateFor($overtime->user, $overtime->ot_date);
            } catch (DomainException $e) {
                $salaryPerHour = null;
            }
        }

        return [
            'ot'                 => $overtime,
            'salaryPerHour'      => $salaryPerHour,
            'allowedMultipliers' => EmployeeOvertimeConfig::allowedMultipliersFor($overtime->user),
            'defaultMultiplier'  => EmployeeOvertimeConfig::defaultMultiplierFor($overtime->user),
        ];
    }

    public function approve(ApproveOvertimeRequest $request, EmployeeOvertime $overtime): RedirectResponse
    {
        $this->authorize('approve', $overtime);

        $this->service->approve(
            $overtime,
            auth()->user(),
            (float) $request->validated('multiplier'),
            $request->validated('manual_amount') !== null ? (float) $request->validated('manual_amount') : null,
            $request->validated('review_note'),
        );

        return back()->with('success', 'Overtime approved.');
    }

    public function reject(RejectOvertimeRequest $request, EmployeeOvertime $overtime): RedirectResponse
    {
        $this->authorize('reject', $overtime);

        $this->service->reject($overtime, auth()->user(), $request->validated('review_note'));

        return back()->with('success', 'Overtime rejected.');
    }
}
