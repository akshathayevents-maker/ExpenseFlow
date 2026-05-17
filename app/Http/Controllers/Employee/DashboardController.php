<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Services\WalletService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(): View
    {
        $user   = auth()->user();
        $userId = $user->id;
        $wallet = $this->walletService->getOrCreate($user);

        $stats = [
            'my_requests'            => ExpenseRequest::where('requested_by', $userId)->count(),
            'pending_requests'       => ExpenseRequest::where('requested_by', $userId)->pending()->count(),
            'approved_requests'      => ExpenseRequest::where('requested_by', $userId)->approved()->count(),
            'approved_amount'        => ExpenseRequest::where('requested_by', $userId)->approved()->sum('amount'),
            'rejected_requests'      => ExpenseRequest::where('requested_by', $userId)->rejected()->count(),
            'monthly_expense'        => ExpenseRequest::where('requested_by', $userId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'reimbursement_pending'  => ExpenseRequest::where('requested_by', $userId)
                ->reimbursementPending()->sum('amount'),
            'wallet_balance'         => $wallet->balance,
            'wallet_low'             => $wallet->isLow(),
            'wallet_negative'        => $wallet->isNegative(),
        ];

        $recentRequests = ExpenseRequest::with(['category'])
            ->where('requested_by', $userId)
            ->latest()
            ->limit(5)
            ->get();

        return view('employee.dashboard', compact('stats', 'recentRequests'));
    }
}
