<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\ApproveOvertimeRequest;
use App\Http\Requests\Overtime\RejectOvertimeRequest;
use App\Models\EmployeeOvertime;
use App\Services\OvertimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function __construct(private OvertimeService $service) {}

    public function index(): View
    {
        $records = $this->service->listForManager();

        return view('manager.overtime.index', compact('records'));
    }

    public function show(EmployeeOvertime $overtime): View
    {
        $this->authorize('view', $overtime);

        return view('manager.overtime.show', ['ot' => $overtime]);
    }

    public function approve(ApproveOvertimeRequest $request, EmployeeOvertime $overtime): RedirectResponse
    {
        $this->authorize('approve', $overtime);

        $this->service->approve($overtime, auth()->user(), $request->validated('review_note'));

        return back()->with('success', 'Overtime approved.');
    }

    public function reject(RejectOvertimeRequest $request, EmployeeOvertime $overtime): RedirectResponse
    {
        $this->authorize('reject', $overtime);

        $this->service->reject($overtime, auth()->user(), $request->validated('review_note'));

        return back()->with('success', 'Overtime rejected.');
    }
}
