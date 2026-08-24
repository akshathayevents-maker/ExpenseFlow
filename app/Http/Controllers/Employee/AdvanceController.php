<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advance\StoreAdvanceRequest;
use App\Models\EmployeeAdvance;
use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeAdvanceService;
use App\Services\EmployeeAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdvanceController extends Controller
{
    public function __construct(
        private EmployeeAdvanceService $service,
        private AdvanceEligibilityService $eligibilityService,
        private EmployeeAttendanceService $attendanceService,
    ) {}

    public function index(): View
    {
        $advances = auth()->user()->advances()->latest()->get();

        return view('employee.advances.index', compact('advances'));
    }

    public function create(): View
    {
        $this->authorize('create', EmployeeAdvance::class);

        $eligibility = $this->eligibilityService->evaluate(auth()->user(), $this->attendanceService->today());

        return view('employee.advances.create', compact('eligibility'));
    }

    public function store(StoreAdvanceRequest $request): RedirectResponse
    {
        $advance = $this->service->createRequest(auth()->user(), $request->validated());

        return redirect()->route('employee.advances.show', $advance)
            ->with('success', 'Advance request submitted.');
    }

    public function show(EmployeeAdvance $advance): View
    {
        $this->authorize('view', $advance);

        $advance->load(['transactions' => fn ($q) => $q->latest('transaction_date')->latest('id'), 'approver', 'payer']);

        return view('employee.advances.show', compact('advance'));
    }

    public function cancel(EmployeeAdvance $advance): RedirectResponse
    {
        $this->authorize('cancel', $advance);

        $this->service->cancel($advance, auth()->user());

        return back()->with('success', 'Advance request cancelled.');
    }
}
