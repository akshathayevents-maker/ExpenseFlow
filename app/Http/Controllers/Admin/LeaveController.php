<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManualLeaveAdjustmentRequest;
use App\Http\Requests\Admin\RejectLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveAllocationService;
use App\Services\LeaveBalanceService;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(
        private LeaveService $service,
        private LeaveBalanceService $balanceService,
        private LeaveAllocationService $allocationService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->get('status', '');

        $requests = LeaveRequest::with(['user', 'leaveType'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.leave.requests.index', compact('requests', 'status'));
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['user', 'leaveType', 'reviewer']);

        return view('admin.leave.requests.show', ['leaveRequest' => $leaveRequest, 'routePrefix' => 'admin']);
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        try {
            $this->service->approve($leaveRequest, auth()->user());
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(RejectLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        try {
            $this->service->reject($leaveRequest, auth()->user(), $request->validated('review_note'));
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Leave request rejected.');
    }

    // Balances for one employee, across every active leave type — always
    // derived live via LeaveBalanceService, never a stored/cached figure.
    public function balances(User $employee): View
    {
        $balances = $this->balanceService->balancesForAllTypes($employee);
        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        return view('admin.leave.balances.show', compact('employee', 'balances', 'leaveTypes'));
    }

    public function storeAdjustment(ManualLeaveAdjustmentRequest $request, User $employee): RedirectResponse
    {
        $leaveType = LeaveType::findOrFail($request->validated('leave_type_id'));

        try {
            $this->allocationService->manualAdjustment(
                $employee,
                $leaveType,
                (float) $request->validated('amount'),
                $request->validated('reason'),
                auth()->user(),
            );
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Leave balance adjustment recorded.');
    }
}
