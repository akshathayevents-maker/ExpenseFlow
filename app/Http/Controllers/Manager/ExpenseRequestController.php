<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\ApproveExpenseRequest;
use App\Http\Requests\Expense\RejectExpenseRequest;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Services\ExpenseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseRequestController extends Controller
{
    public function __construct(private ExpenseRequestService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'category_id', 'priority', 'from', 'to']);

        $requests = ExpenseRequest::with(['category', 'vendor', 'requester', 'approver'])
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('title', 'ilike', "%{$v}%")
            )
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('expense_category_id', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::active()->orderBy('name')->get();

        return view('manager.expense-requests.index', compact('requests', 'filters', 'categories'));
    }

    public function show(ExpenseRequest $expenseRequest): View
    {
        $expenseRequest->load(['category', 'vendor', 'requester', 'approver', 'bills.uploader']);

        return view('manager.expense-requests.show', compact('expenseRequest'));
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
}
