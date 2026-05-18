<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\ApproveExpenseRequest;
use App\Http\Requests\Expense\RejectExpenseRequest;
use App\Http\Requests\Expense\StoreExpenseRequestRequest;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Services\ExpenseRequestService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseRequestController extends Controller
{
    public function __construct(
        private ExpenseRequestService $service,
        private WalletService $walletService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'category_id', 'priority', 'from', 'to']);

        $requests = ExpenseRequest::with(['category', 'requester'])
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('title', 'ilike', "%{$v}%")
            )
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('expense_category_id', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::active()->orderBy('name')->get();

        $summary = [
            'pending'              => ExpenseRequest::pending()->count(),
            'pending_amount'       => ExpenseRequest::pending()->sum('amount'),
            'high_priority'        => ExpenseRequest::pending()->whereIn('priority', ['high', 'urgent'])->count(),
            'approved_today'       => ExpenseRequest::approved()->whereDate('updated_at', today())->count(),
            'approved_today_amount'=> ExpenseRequest::approved()->whereDate('updated_at', today())->sum('amount'),
            'rejected'             => ExpenseRequest::rejected()->count(),
            'paid_today'           => ExpenseRequest::whereIn('status', ['paid', 'completed', 'reimbursed'])
                                        ->whereDate('updated_at', today())->sum('amount'),
        ];

        return view('manager.expense-requests.index', compact('requests', 'filters', 'categories', 'summary'));
    }

    public function show(ExpenseRequest $expenseRequest): View
    {
        $expenseRequest->load(['category', 'vendor', 'requester', 'approver', 'bills.uploader']);

        return view('manager.expense-requests.show', compact('expenseRequest'));
    }

    public function create(): View
    {
        $wallet = $this->walletService->getOrCreate(auth()->user());
        return view('employee.expense-requests.create', [
            'walletBalance'  => $wallet->balance,
            'walletLow'      => $wallet->isLow(),
            'walletNegative' => $wallet->isNegative(),
            'formAction'     => route('manager.expense-requests.store'),
            'backUrl'        => route('manager.dashboard'),
        ]);
    }

    public function store(StoreExpenseRequestRequest $request): RedirectResponse
    {
        $expenseRequest = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->file('qr'),
        );

        return redirect()->route('manager.expense-requests.success', $expenseRequest);
    }

    public function success(ExpenseRequest $expenseRequest): View
    {
        $this->authorize('view', $expenseRequest);

        if (! $expenseRequest->whatsapp_sent_at) {
            $expenseRequest->update(['whatsapp_sent_at' => now()]);
        }

        return view('employee.expense-requests.success', [
            'expenseRequest' => $expenseRequest,
            'dashboardUrl'   => route('manager.dashboard'),
            'createUrl'      => route('manager.expense-requests.create'),
            'showUrl'        => route('manager.expense-requests.show', $expenseRequest),
        ]);
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
