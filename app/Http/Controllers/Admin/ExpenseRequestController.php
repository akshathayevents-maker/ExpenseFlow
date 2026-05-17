<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordPaymentRequest;
use App\Http\Requests\Admin\ReimburseRequest;
use App\Http\Requests\Expense\ApproveExpenseRequest;
use App\Http\Requests\Expense\RejectExpenseRequest;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Services\ExpenseRequestService;
use App\Services\ExpenseSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseRequestController extends Controller
{
    public function __construct(
        private ExpenseRequestService    $service,
        private ExpenseSettlementService $settlementService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'category_id', 'employee_id', 'priority', 'from', 'to']);

        $applyFilters = function ($query) use ($filters) {
            return $query
                ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('title', 'ilike', "%{$v}%"))
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
                ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('expense_category_id', $v))
                ->when($filters['employee_id'] ?? null, fn ($q, $v) => $q->where('requested_by', $v))
                ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
                ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
        };

        $requests = $applyFilters(
            ExpenseRequest::with(['category', 'vendor', 'requester', 'approver'])
                ->withCount(['bills'])
        )->latest()->paginate(20)->withQueryString();

        $agg = $applyFilters(ExpenseRequest::query())
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END), 0) as pending_count,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) as approved_count,
                COALESCE(SUM(CASE WHEN status IN ('paid','reimbursed','completed','pending_payment','reimbursement_pending') THEN 1 ELSE 0 END), 0) as settled_count,
                COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) as rejected_count
            ")
            ->first();

        $stats = [
            'total_count'    => (int)   ($agg->total_count    ?? 0),
            'total_amount'   => (float) ($agg->total_amount   ?? 0),
            'pending_count'  => (int)   ($agg->pending_count  ?? 0),
            'approved_count' => (int)   ($agg->approved_count ?? 0),
            'settled_count'  => (int)   ($agg->settled_count  ?? 0),
            'rejected_count' => (int)   ($agg->rejected_count ?? 0),
            'monthly_total'  => (float) ExpenseRequest::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->whereNotIn('status', ['rejected'])
                                    ->sum('amount'),
        ];

        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $employees  = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();

        return view('admin.expense-requests.index', compact('requests', 'filters', 'categories', 'employees', 'stats'));
    }

    public function show(ExpenseRequest $expenseRequest): View
    {
        $expenseRequest->load(['category', 'vendor', 'requester', 'approver', 'bills.uploader', 'payment']);

        return view('admin.expense-requests.show', compact('expenseRequest'));
    }

    public function approve(ApproveExpenseRequest $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('approve', $expenseRequest);
        $this->service->approve($expenseRequest, auth()->user());

        return back()->with('success', 'Expense request approved.');
    }

    public function reject(RejectExpenseRequest $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('reject', $expenseRequest);
        $this->service->reject($expenseRequest, auth()->user(), $request->validated('rejection_reason'));

        return back()->with('success', 'Expense request rejected.');
    }

    public function settleViaWallet(ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('markPaid', $expenseRequest);

        try {
            $this->settlementService->settleViaWallet($expenseRequest, auth()->user());
            return back()->with('success', "₹{$expenseRequest->amount} deducted from {$expenseRequest->requester->name}'s wallet. Marked as paid.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function settleViaDirect(RecordPaymentRequest $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('markPaid', $expenseRequest);

        try {
            $this->settlementService->settleViaDirect($expenseRequest, $request->validated(), auth()->user());
            return back()->with('success', 'Direct payment recorded. Marked as paid.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markReimbursementPending(ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('markPaid', $expenseRequest);

        try {
            $this->settlementService->markReimbursementPending($expenseRequest);
            return back()->with('success', 'Marked as reimbursement pending.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reimburse(ReimburseRequest $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('markPaid', $expenseRequest);

        try {
            $this->settlementService->reimburse($expenseRequest, $request->validated(), auth()->user());
            return back()->with('success', 'Reimbursement recorded. Employee notified.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markCompleted(ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('markCompleted', $expenseRequest);

        try {
            $this->settlementService->markCompleted($expenseRequest);
            return back()->with('success', 'Marked as completed.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorize('delete', $expenseRequest);
        $expenseRequest->delete();

        return redirect()->route('admin.expense-requests.index')
            ->with('success', 'Expense request deleted.');
    }
}
