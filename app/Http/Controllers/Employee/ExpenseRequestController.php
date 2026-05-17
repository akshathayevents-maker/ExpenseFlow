<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequestRequest;
use App\Models\ExpenseRequest;
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
        $userId = auth()->id();

        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
        ];

        $requests = ExpenseRequest::with(['category', 'approver'])
            ->where('requested_by', $userId)
            ->when($filters['search'], fn ($q, $v) =>
                $q->where('title', 'ilike', "%{$v}%")
            )
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $base = ExpenseRequest::where('requested_by', $userId);

        $summary = [
            'total'           => (clone $base)->count(),
            'pending'         => (clone $base)->pending()->count(),
            'approved_amount' => (clone $base)->approved()->sum('amount'),
            'paid_amount'     => (clone $base)->whereIn('status', ['paid', 'completed', 'reimbursed'])->sum('amount'),
            'rejected'        => (clone $base)->rejected()->count(),
        ];

        return view('employee.expense-requests.index', compact('requests', 'filters', 'summary'));
    }

    public function create(): View
    {
        $wallet = $this->walletService->getOrCreate(auth()->user());
        return view('employee.expense-requests.create', [
            'walletBalance'  => $wallet->balance,
            'walletLow'      => $wallet->isLow(),
            'walletNegative' => $wallet->isNegative(),
        ]);
    }

    public function store(StoreExpenseRequestRequest $request): RedirectResponse
    {
        $expenseRequest = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->file('qr'),
        );

        return redirect()->route('employee.expense-requests.success', $expenseRequest);
    }

    public function success(ExpenseRequest $expenseRequest): View
    {
        $this->authorize('view', $expenseRequest);

        // Record first WhatsApp prompt time
        if (! $expenseRequest->whatsapp_sent_at) {
            $expenseRequest->update(['whatsapp_sent_at' => now()]);
        }

        return view('employee.expense-requests.success', compact('expenseRequest'));
    }

    public function show(ExpenseRequest $expenseRequest): View
    {
        $this->authorize('view', $expenseRequest);
        $expenseRequest->load(['approver']);

        return view('employee.expense-requests.show', compact('expenseRequest'));
    }
}
