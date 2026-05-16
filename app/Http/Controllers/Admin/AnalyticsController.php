<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->get('from', now()->startOfYear()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $dateRange = [$from, $to . ' 23:59:59'];
        $settledStatuses = ['paid', 'reimbursed', 'completed'];

        $topCategories = ExpenseCategory::withSum(['expenseRequests as total' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', $settledStatuses)
            ], 'amount')
            ->whereHas('expenseRequests', fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', $settledStatuses)
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topEmployees = User::whereIn('role', ['employee', 'manager'])
            ->withSum(['expenseRequests as total' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', $settledStatuses)
            ], 'amount')
            ->whereHas('expenseRequests', fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', $settledStatuses)
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topVendors = Vendor::withSum(['expenseRequests as total' => fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', $settledStatuses)
            ], 'amount')
            ->whereHas('expenseRequests', fn ($q) =>
                $q->whereBetween('expense_requests.created_at', $dateRange)
                  ->whereIn('expense_requests.status', $settledStatuses)
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $monthlyTrend = ExpenseRequest::selectRaw("TO_CHAR(created_at, 'Mon YYYY') as month, EXTRACT(YEAR FROM created_at)*100+EXTRACT(MONTH FROM created_at) as sort_key, SUM(amount) as total, COUNT(*) as count")
            ->whereBetween('created_at', $dateRange)
            ->whereIn('status', $settledStatuses)
            ->groupByRaw("TO_CHAR(created_at, 'Mon YYYY'), EXTRACT(YEAR FROM created_at)*100+EXTRACT(MONTH FROM created_at)")
            ->orderBy('sort_key')
            ->get();

        $grandTotal = ExpenseRequest::whereBetween('created_at', $dateRange)
            ->whereIn('status', $settledStatuses)
            ->sum('amount');

        return view('admin.analytics.index', compact(
            'from', 'to', 'topCategories', 'topEmployees',
            'topVendors', 'monthlyTrend', 'grandTotal'
        ));
    }

    public function inventory(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $dateRange = [$from, $to . ' 23:59:59'];

        $topUsed = InventoryItem::with('category')
            ->withSum(['transactions as used_qty' => fn ($q) =>
                $q->where('type', 'usage')
                  ->whereBetween('inventory_transactions.created_at', $dateRange)
            ], 'quantity')
            ->whereHas('transactions', fn ($q) =>
                $q->where('type', 'usage')
                  ->whereBetween('inventory_transactions.created_at', $dateRange)
                  ->where('quantity', '>', 0)
            )
            ->orderByDesc('used_qty')
            ->limit(10)
            ->get();

        $topWasted = InventoryItem::with('category')
            ->withSum(['transactions as wasted_qty' => fn ($q) =>
                $q->where('type', 'wastage')
                  ->whereBetween('inventory_transactions.created_at', $dateRange)
            ], 'quantity')
            ->whereHas('transactions', fn ($q) =>
                $q->where('type', 'wastage')
                  ->whereBetween('inventory_transactions.created_at', $dateRange)
                  ->where('quantity', '>', 0)
            )
            ->orderByDesc('wasted_qty')
            ->limit(10)
            ->get();

        $totalInventoryValue = InventoryItem::active()
            ->selectRaw('SUM(current_stock * COALESCE(average_cost, 0)) as total_value')
            ->value('total_value') ?? 0;

        $totalWastageCost = InventoryTransaction::where('inventory_transactions.type', 'wastage')
            ->whereBetween('inventory_transactions.created_at', $dateRange)
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->selectRaw('SUM(inventory_transactions.quantity * COALESCE(inventory_items.average_cost, 0)) as total')
            ->value('total') ?? 0;

        return view('admin.analytics.inventory', compact(
            'from', 'to', 'topUsed', 'topWasted', 'totalInventoryValue', 'totalWastageCost'
        ));
    }
}
