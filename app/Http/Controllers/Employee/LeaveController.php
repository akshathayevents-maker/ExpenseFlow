<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $service) {}

    public function index(): View
    {
        $requests = auth()->user()->leaveRequests()->with('leaveType')->latest('start_date')->get();

        $summary = [
            'pending'  => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
        ];

        return view('employee.leave.index', ['requests' => $requests, 'summary' => $summary]);
    }

    public function create(): View
    {
        $this->authorize('create', LeaveRequest::class);

        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        return view('employee.leave.create', ['leaveTypes' => $leaveTypes]);
    }

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
