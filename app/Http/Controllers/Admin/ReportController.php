<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $summary = [
            'total_expenses'         => ExpenseRequest::whereIn('status', ['paid','reimbursed','completed'])->sum('amount'),
            'month_expenses'         => ExpenseRequest::whereIn('status', ['paid','reimbursed','completed'])
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
            'pending_reimbursements' => ExpenseRequest::reimbursementPending()->sum('amount'),
            'total_wallet_balance'   => Wallet::sum('balance'),
            'pending_approvals'      => ExpenseRequest::pending()->count(),
            'active_employees'       => User::whereIn('role', ['employee', 'manager'])->where('is_active', true)->count(),
            'active_vendors'         => Vendor::where('is_active', true)->count(),
        ];

        return view('admin.reports.index', compact('summary'));
    }

    public function employee(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $dateRange = [$from, $to . ' 23:59:59'];

        $data = User::whereIn('role', ['employee', 'manager'])
            ->withSum(['expenseRequests as total_amount' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', ['approved','paid','reimbursement_pending','reimbursed','completed'])
            ], 'amount')
            ->withCount(['expenseRequests as total_count' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
            ])
            ->whereHas('expenseRequests', fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
            )
            ->orderByDesc('total_amount')
            ->get();

        return view('admin.reports.employee', compact('data', 'from', 'to'));
    }

    public function category(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $dateRange = [$from, $to . ' 23:59:59'];

        $data = ExpenseCategory::withSum(['expenseRequests as total_amount' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', ['approved','paid','reimbursement_pending','reimbursed','completed'])
            ], 'amount')
            ->withCount(['expenseRequests as total_count' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
            ])
            ->whereHas('expenseRequests', fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
            )
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = $data->sum('total_amount');

        return view('admin.reports.category', compact('data', 'from', 'to', 'grandTotal'));
    }

    public function vendor(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $dateRange = [$from, $to . ' 23:59:59'];

        $data = Vendor::withSum(['expenseRequests as total_amount' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', ['approved','paid','reimbursement_pending','reimbursed','completed'])
            ], 'amount')
            ->withCount(['expenseRequests as total_count' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
            ])
            ->whereHas('expenseRequests', fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
            )
            ->orderByDesc('total_amount')
            ->get();

        return view('admin.reports.vendor', compact('data', 'from', 'to'));
    }

    public function ledger(Request $request): View
    {
        $filters = $request->only(['employee_id', 'type', 'from', 'to']);

        $transactions = WalletTransaction::with(['wallet.user', 'expenseRequest', 'creator'])
            ->when($filters['employee_id'] ?? null, fn ($q, $v) =>
                $q->whereHas('wallet', fn ($w) => $w->where('user_id', $v))
            )
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $employees = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();

        return view('admin.reports.ledger', compact('transactions', 'filters', 'employees'));
    }

    public function reimbursement(Request $request): View
    {
        $status = $request->get('status', '');
        $from   = $request->get('from', '');
        $to     = $request->get('to', '');

        $data = ExpenseRequest::with(['requester', 'category', 'payment'])
            ->where('settlement_type', 'reimbursement')
            ->when($status, fn ($q, $v) => $q->where('status', $v))
            ->when($from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totals = [
            'pending'    => ExpenseRequest::reimbursementPending()->sum('amount'),
            'reimbursed' => ExpenseRequest::where('status', 'reimbursed')->sum('amount'),
        ];

        return view('admin.reports.reimbursement', compact('data', 'status', 'from', 'to', 'totals'));
    }

    public function daily(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $data = ExpenseRequest::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->whereNotIn('status', ['pending', 'rejected'])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) DESC')
            ->get();

        return view('admin.reports.daily', compact('data', 'from', 'to'));
    }

    public function monthly(Request $request): View
    {
        $year = $request->get('year', now()->year);

        $data = ExpenseRequest::selectRaw("TO_CHAR(created_at, 'Month') as month_name, EXTRACT(MONTH FROM created_at) as month_num, COUNT(*) as count, SUM(amount) as total")
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->groupByRaw("TO_CHAR(created_at, 'Month'), EXTRACT(MONTH FROM created_at)")
            ->orderByRaw('month_num')
            ->get();

        $years = ExpenseRequest::selectRaw('EXTRACT(YEAR FROM created_at) as y')
            ->groupByRaw('EXTRACT(YEAR FROM created_at)')
            ->orderByRaw('y DESC')
            ->pluck('y');

        return view('admin.reports.monthly', compact('data', 'year', 'years'));
    }
}
