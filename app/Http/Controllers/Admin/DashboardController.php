<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_expenses_month' => ExpenseRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'pending_approvals'    => ExpenseRequest::pending()->count(),
            'approved_today'       => ExpenseRequest::approved()
                ->whereDate('approved_at', today())
                ->count(),
            'total_employees'      => User::where('role', 'employee')->count(),
            'total_managers'       => User::where('role', 'manager')->count(),
            'active_users'         => User::where('is_active', true)->count(),
            'inactive_users'       => User::where('is_active', false)->count(),
            'total_wallet_balance' => Wallet::sum('balance'),
            'low_balance_count'    => Wallet::where('balance', '<', 500)->count(),
            'pending_reimb_amount' => ExpenseRequest::reimbursementPending()->sum('amount'),
            'pending_reimb_count'  => ExpenseRequest::reimbursementPending()->count(),
        ];

        $recentRequests = ExpenseRequest::with(['category', 'requester'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests'));
    }
}
