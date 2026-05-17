<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['payment_mode', 'employee_id', 'from', 'to', 'search']);

        $applyFilters = function ($query) use ($filters) {
            return $query
                ->when($filters['payment_mode'] ?? null, fn ($q, $v) => $q->where('payment_mode', $v))
                ->when($filters['employee_id'] ?? null, fn ($q, $v) =>
                    $q->whereHas('expenseRequest', fn ($r) => $r->where('requested_by', $v))
                )
                ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '>=', $v))
                ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '<=', $v))
                ->when($filters['search'] ?? null, fn ($q, $v) =>
                    $q->whereHas('expenseRequest', fn ($r) => $r->where('title', 'ilike', "%{$v}%"))
                );
        };

        $payments = $applyFilters(
            ExpensePayment::with(['expenseRequest.category', 'expenseRequest.requester', 'payer'])
        )->latest('paid_at')->paginate(20)->withQueryString();

        // Aggregate stats across full filtered result set
        $agg = $applyFilters(ExpensePayment::query())
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(AVG(amount), 0) as avg_amount,
                COALESCE(SUM(CASE WHEN payment_mode = 'cash'          THEN amount ELSE 0 END), 0) as cash_total,
                COALESCE(SUM(CASE WHEN payment_mode = 'upi'           THEN amount ELSE 0 END), 0) as upi_total,
                COALESCE(SUM(CASE WHEN payment_mode = 'bank_transfer' THEN amount ELSE 0 END), 0) as bank_total
            ")
            ->first();

        $stats = [
            'total_count'   => (int)   ($agg->total_count   ?? 0),
            'total_amount'  => (float) ($agg->total_amount  ?? 0),
            'avg_amount'    => (float) ($agg->avg_amount    ?? 0),
            'cash_total'    => (float) ($agg->cash_total    ?? 0),
            'upi_total'     => (float) ($agg->upi_total     ?? 0),
            'bank_total'    => (float) ($agg->bank_total    ?? 0),
            'monthly_total' => (float) ExpensePayment::whereMonth('paid_at', now()->month)
                                    ->whereYear('paid_at', now()->year)
                                    ->sum('amount'),
        ];

        $employees = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();

        return view('admin.payments.index', compact('payments', 'filters', 'employees', 'stats'));
    }
}
