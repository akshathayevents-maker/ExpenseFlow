<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(
        private LeaveService $service,
        private LeaveBalanceService $balanceService,
    ) {}

    public function index(): View
    {
        $requests = auth()->user()->leaveRequests()->with('leaveType')->latest('start_date')->get();

        $summary = [
            'pending'  => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
        ];

        $balances = $this->balanceService->balancesForAllTypes(auth()->user());

        return view('employee.leave.index', ['requests' => $requests, 'summary' => $summary, 'balances' => $balances]);
    }

    public function create(): View
    {
        $this->authorize('create', LeaveRequest::class);

        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        // Available balance per leave type, shown on the form BEFORE
        // submission so the employee can see what they have left — never
        // computed here beyond what LeaveBalanceService already returns.
        $balances = $this->balanceService->balancesForAllTypes(auth()->user());

        return view('employee.leave.create', ['leaveTypes' => $leaveTypes, 'balances' => $balances]);
    }

    // The first submit attempt never silently retries with lop_confirmed=1
    // — createRequest() throws a 'lop_confirmation' ValidationException
    // whenever the request would exceed the paid balance and the caller
    // hasn't already confirmed. That exception's message IS the exact
    // split text the employee must see; letting it flow through Laravel's
    // normal validation-exception handling re-renders the create form with
    // the submitted input preserved (old()) and that message available via
    // $errors->first('lop_confirmation') — the view then shows an explicit
    // "Apply remaining N days as LOP" confirmation control that resubmits
    // the same form with lop_confirmed=1.
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $leaveRequest = $this->service->createRequest(auth()->user(), $request->validated());

        return redirect()->route('employee.leave.show', $leaveRequest)
            ->with('success', 'Leave request submitted.');
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load('leaveType');

        return view('employee.leave.show', ['leaveRequest' => $leaveRequest]);
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $this->service->cancel($leaveRequest, auth()->user());

        return back()->with('success', 'Leave request cancelled.');
    }
}
