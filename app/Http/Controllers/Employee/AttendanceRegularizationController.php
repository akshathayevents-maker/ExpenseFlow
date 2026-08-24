<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRegularization\StoreAttendanceRegularizationRequest;
use App\Models\EmployeeAttendanceRegularization;
use App\Services\EmployeeAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceRegularizationController extends Controller
{
    public function __construct(private EmployeeAttendanceService $service) {}

    public function create(): View
    {
        $this->authorize('create', EmployeeAttendanceRegularization::class);

        return view('employee.attendance.regularizations.create');
    }

    public function store(StoreAttendanceRegularizationRequest $request): RedirectResponse
    {
        $regularization = $this->service->createRegularization(auth()->user(), $request->validated());

        return redirect()->route('employee.attendance-regularizations.show', $regularization)
            ->with('success', 'Regularization request submitted.');
    }

    public function show(EmployeeAttendanceRegularization $regularization): View
    {
        $this->authorize('view', $regularization);

        return view('employee.attendance.regularizations.show', ['regularization' => $regularization]);
    }

    public function cancel(EmployeeAttendanceRegularization $regularization): RedirectResponse
    {
        $this->authorize('cancel', $regularization);

        $this->service->cancelRegularization($regularization, auth()->user());

        return back()->with('success', 'Regularization request cancelled.');
    }
}
