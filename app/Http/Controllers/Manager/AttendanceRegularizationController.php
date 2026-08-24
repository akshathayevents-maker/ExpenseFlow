<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRegularization\ApproveAttendanceRegularizationRequest;
use App\Http\Requests\AttendanceRegularization\RejectAttendanceRegularizationRequest;
use App\Models\EmployeeAttendanceRegularization;
use App\Services\EmployeeAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceRegularizationController extends Controller
{
    public function __construct(private EmployeeAttendanceService $service) {}

    public function index(): View
    {
        $records = $this->service->listRegularizationsForManager();

        return view('manager.attendance-regularizations.index', compact('records'));
    }

    public function approve(ApproveAttendanceRegularizationRequest $request, EmployeeAttendanceRegularization $regularization): RedirectResponse
    {
        $this->authorize('approve', $regularization);

        $this->service->approveRegularization($regularization, auth()->user(), $request->validated('review_note'));

        return back()->with('success', 'Attendance regularization approved.');
    }

    public function reject(RejectAttendanceRegularizationRequest $request, EmployeeAttendanceRegularization $regularization): RedirectResponse
    {
        $this->authorize('reject', $regularization);

        $this->service->rejectRegularization($regularization, auth()->user(), $request->validated('review_note'));

        return back()->with('success', 'Attendance regularization rejected.');
    }
}
