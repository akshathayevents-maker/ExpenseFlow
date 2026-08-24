<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\AdminRecordOvertimeRequest;
use App\Http\Requests\Overtime\ApproveOvertimeRequest;
use App\Http\Requests\Overtime\RejectOvertimeRequest;
use App\Models\EmployeeOvertime;
use App\Models\User;
use App\Services\OvertimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function __construct(private OvertimeService $service) {}

    public function index(): View
    {
        $records = $this->service->listForManager();

        return view('admin.overtime.index', compact('records'));
    }

    public function create(): View
    {
        $this->authorize('recordForOther', EmployeeOvertime::class);

        $employees = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.overtime.create', compact('employees'));
    }

    public function store(AdminRecordOvertimeRequest $request): RedirectResponse
    {
        $this->authorize('recordForOther', EmployeeOvertime::class);

        $employee = User::findOrFail($request->validated('user_id'));

        $ot = $this->service->recordHistorical(auth()->user(), $employee, $request->validated());

        return redirect()->route('admin.overtime.show', $ot)->with('success', 'Historical overtime recorded.');
    }

    public function show(EmployeeOvertime $overtime): View
    {
        $this->authorize('view', $overtime);

        return view('admin.overtime.show', ['ot' => $overtime]);
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
