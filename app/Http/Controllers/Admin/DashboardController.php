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
        $approvedTotal = ExpenseRequest::whereIn('status', [
            'approved', 'pending_payment', 'paid', 'reimbursement_pending', 'reimbursed', 'completed',
        ])->count();

        $rejectedTotal = ExpenseRequest::rejected()->count();

        $stats = [
            'pending_approvals'    => ExpenseRequest::pending()->count(),
            'approved_today'       => ExpenseRequest::approved()->whereDate('approved_at', today())->count(),
            'approved_today_amount'=> ExpenseRequest::approved()->whereDate('approved_at', today())->sum('amount'),
            'rejected'             => $rejectedTotal,
            'approved_total'       => $approvedTotal,
            'paid_total'           => ExpenseRequest::whereIn('status', ['paid', 'reimbursed', 'completed'])->count(),
            'total_processed'      => $approvedTotal + $rejectedTotal,
            'total_submitted'      => ExpenseRequest::count(),
            'total_expenses_month' => ExpenseRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
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
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests'));
    }
}
