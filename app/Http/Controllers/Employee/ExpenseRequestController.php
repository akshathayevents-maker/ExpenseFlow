<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequestRequest;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\Vendor;
use App\Services\ExpenseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseRequestController extends Controller
{
    public function __construct(private ExpenseRequestService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'priority']);

        $requests = ExpenseRequest::with(['category', 'vendor', 'approver'])
            ->where('requested_by', auth()->id())
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('title', 'ilike', "%{$v}%")
            )
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('employee.expense-requests.index', compact('requests', 'filters'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $vendors    = Vendor::active()->orderBy('name')->get();

        return view('employee.expense-requests.create', compact('categories', 'vendors'));
    }

    public function store(StoreExpenseRequestRequest $request): RedirectResponse
    {
        $expenseRequest = $this->service->create(
            $request->validated(),
            $request->file('bills', []),
            auth()->user()
        );

        return redirect()->route('employee.expense-requests.show', $expenseRequest)
            ->with('success', 'Expense request submitted successfully.');
    }

    public function show(ExpenseRequest $expenseRequest): View
    {
        $this->authorize('view', $expenseRequest);
        $expenseRequest->load(['category', 'vendor', 'approver', 'bills']);

        return view('employee.expense-requests.show', compact('expenseRequest'));
    }
}
