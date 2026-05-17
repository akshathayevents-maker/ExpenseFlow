<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function show(Request $request): View
    {
        $user   = auth()->user();
        $wallet = $this->walletService->getOrCreate($user);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with(['expenseRequest', 'creator'])
            ->when($request->get('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->get('from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->get('to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $base = WalletTransaction::where('wallet_id', $wallet->id);

        $stats = [
            'month_credited' => (clone $base)
                ->whereIn('type', ['credit', 'reimbursement'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'month_debited' => (clone $base)
                ->where('type', 'debit')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'pending_requests' => ExpenseRequest::where('requested_by', $user->id)
                ->pending()->count(),
            'last_credit' => (clone $base)
                ->whereIn('type', ['credit', 'reimbursement'])
                ->latest()->first(),
        ];

        return view('employee.wallet.show', compact('wallet', 'transactions', 'stats'));
    }
}
