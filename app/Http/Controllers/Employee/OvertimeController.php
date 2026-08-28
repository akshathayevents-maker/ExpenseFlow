<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\StoreOvertimeRequest;
use App\Models\EmployeeOvertime;
use App\Services\OvertimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function __construct(private OvertimeService $service) {}

    public function index(): View
    {
        $records = $this->service->listForEmployee(auth()->user());

        return view('employee.overtime.index', compact('records'));
    }

    public function create(): View
    {
        $this->authorize('create', EmployeeOvertime::class);

        return view('employee.overtime.create');
    }

    public function store(StoreOvertimeRequest $request): RedirectResponse
    {
        // Server-side enforcement of the create() gate, mirroring the
        // create() action above — a direct POST to this route (bypassing
        // the hidden UI entry point) must be blocked exactly the same way
        // when employee_overtime_requests_enabled is off, not merely have
        // its button hidden.
        $this->authorize('create', EmployeeOvertime::class);

        $ot = $this->service->createRequest(auth()->user(), $request->validated());

        return redirect()->route('employee.overtime.show', $ot)->with('success', 'Overtime request submitted.');
    }

    public function show(EmployeeOvertime $overtime): View
    {
        $this->authorize('view', $overtime);

        return view('employee.overtime.show', ['ot' => $overtime]);
    }

    public function cancel(EmployeeOvertime $overtime): RedirectResponse
    {
        $this->authorize('cancel', $overtime);

        $this->service->cancel($overtime, auth()->user());

        return back()->with('success', 'Overtime request cancelled.');
    }
}
