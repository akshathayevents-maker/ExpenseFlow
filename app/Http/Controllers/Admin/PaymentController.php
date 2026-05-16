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

        $payments = ExpensePayment::with(['expenseRequest.category', 'expenseRequest.requester', 'payer'])
            ->when($filters['payment_mode'] ?? null, fn ($q, $v) => $q->where('payment_mode', $v))
            ->when($filters['employee_id'] ?? null, fn ($q, $v) =>
                $q->whereHas('expenseRequest', fn ($r) => $r->where('requested_by', $v))
            )
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->whereHas('expenseRequest', fn ($r) =>
                    $r->where('title', 'ilike', "%{$v}%")
                )
            )
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        $employees = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();
        $totalPaid = $payments->sum('amount');

        return view('admin.payments.index', compact('payments', 'filters', 'employees', 'totalPaid'));
    }
}
