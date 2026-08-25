<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// Manager side of leave approvals — no leave type / policy configuration
// here (admin-only, per the business rule). Otherwise mirrors
// Admin\LeaveController's request-review actions exactly, same as
// Manager\AdvanceController mirrors Admin\AdvanceController.
class LeaveController extends Controller
{
    public function __construct(private LeaveService $service) {}

    public function index(Request $request): View
    {
        $status = $request->get('status', '');

        $requests = LeaveRequest::with(['user', 'leaveType'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('manager.leave.requests.index', compact('requests', 'status'));
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['user', 'leaveType', 'reviewer']);

        return view('admin.leave.requests.show', ['leaveRequest' => $leaveRequest, 'routePrefix' => 'manager']);
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
}
