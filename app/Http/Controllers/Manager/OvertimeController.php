<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\ApproveOvertimeRequest;
use App\Http\Requests\Overtime\RejectOvertimeRequest;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
use App\Services\OvertimeCalculationService;
use App\Services\OvertimeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
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

        return view('manager.overtime.index', compact('records'));
    }

    public function show(EmployeeOvertime $overtime): View
    {
        $this->authorize('view', $overtime);

        return view('manager.overtime.show', $this->approvalViewData($overtime));
    }

    // Mirrors Admin\OvertimeController::approvalViewData() exactly — see
    // that method's comment for rationale.
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
