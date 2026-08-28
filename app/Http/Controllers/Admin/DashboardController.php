<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\EmployeeOvertime;
use App\Models\ExpenseRequest;
use App\Models\LeaveRequest;
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

        // Pending counts for the "needs attention" roll-up below reuse the
        // exact same status/column conventions each module's own controller
        // already uses (ExpenseRequest::pending() scope; request_status /
        // status = 'pending' columns on Overtime, Advance, Attendance
        // Regularization, Leave — see isPending() on each model). No new
        // "pending" definition is introduced here.
        $overtimePending      = EmployeeOvertime::where('request_status', 'pending')->count();
        $advancePending       = EmployeeAdvance::where('request_status', 'pending')->count();
        $regularizationPending = EmployeeAttendanceRegularization::where('request_status', 'pending')->count();
        $leavePending          = LeaveRequest::where('status', 'pending')->count();

        // Low-wallet threshold (< ₹500) is the same one already used by the
        // pre-existing Wallet Alerts widget below — reused verbatim, not a
        // new figure.
        $lowBalanceCount = Wallet::where('balance', '<', 500)->count();
        $expensePending  = ExpenseRequest::pending()->count();

        $stats = [
            'pending_approvals'    => $expensePending,
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
            'low_balance_count'    => $lowBalanceCount,
            'pending_reimb_amount' => ExpenseRequest::reimbursementPending()->sum('amount'),
            'pending_reimb_count'  => ExpenseRequest::reimbursementPending()->count(),
            'overtime_pending'     => $overtimePending,
            'advance_pending'      => $advancePending,
            'regularization_pending' => $regularizationPending,
            'leave_pending'        => $leavePending,
        ];

        // Attention items: one entry per module with an outstanding count,
        // used to render the "Needs Your Attention" cards. Each links to
        // the existing admin index route for that module (unfiltered where
        // the module's own controller does not support a status filter, so
        // the link never 404s and never invents new query behaviour).
        $attentionItems = collect([
            [
                'key'   => 'expenses',
                'label' => 'Expenses',
                'count' => $expensePending,
                'desc'  => 'expense request(s) awaiting your approval.',
                'icon'  => 'bi-receipt',
                'url'   => route('admin.expense-requests.index', ['status' => 'pending']),
                'cta'   => 'Review Expenses',
            ],
            [
                'key'   => 'overtime',
                'label' => 'Overtime',
                'count' => $overtimePending,
                'desc'  => 'overtime record(s) pending review.',
                'icon'  => 'bi-clock-history',
                'url'   => route('admin.overtime.index'),
                'cta'   => 'Review Overtime',
            ],
            [
                'key'   => 'advances',
                'label' => 'Advances',
                'count' => $advancePending,
                'desc'  => 'salary advance request(s) pending review.',
                'icon'  => 'bi-cash-coin',
                'url'   => route('admin.advances.index'),
                'cta'   => 'Review Advances',
            ],
            [
                'key'   => 'leave',
                'label' => 'Leave',
                'count' => $leavePending,
                'desc'  => 'leave request(s) awaiting decision.',
                'icon'  => 'bi-calendar-check',
                'url'   => route('admin.leave.requests.index', ['status' => 'pending']),
                'cta'   => 'Review Leave',
            ],
            [
                'key'   => 'regularizations',
                'label' => 'Attendance',
                'count' => $regularizationPending,
                'desc'  => 'attendance regularization(s) pending review.',
                'icon'  => 'bi-calendar2-check',
                'url'   => route('admin.attendance-regularizations.index'),
                'cta'   => 'Review Attendance',
            ],
            [
                'key'   => 'wallets',
                'label' => 'Wallet Alerts',
                'count' => $lowBalanceCount,
                'desc'  => 'wallet(s) below the ₹500 balance threshold.',
                'icon'  => 'bi-exclamation-triangle-fill',
                'url'   => route('admin.wallets.index'),
                'cta'   => 'Manage Wallets',
                'critical' => true,
            ],
        ]);

        $needsAttention = $attentionItems->filter(fn ($item) => $item['count'] > 0)->values();
        $needsActionTotal = $attentionItems->sum('count');

        $recentRequests = ExpenseRequest::with(['category', 'requester'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'needsAttention', 'needsActionTotal'));
    }
}
