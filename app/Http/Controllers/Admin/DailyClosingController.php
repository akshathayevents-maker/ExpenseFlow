<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyClosingRequest;
use App\Models\DailyClosing;
use App\Models\ExpensePayment;
use App\Models\ExpenseRequest;
use App\Models\InventoryTransaction;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DailyClosingController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        $closings = DailyClosing::with(['creator', 'verifier'])
            ->latest('date')
            ->paginate(20);

        $todayClosed = DailyClosing::whereDate('date', today())->exists();

        return view('admin.daily-closings.index', compact('closings', 'todayClosed'));
    }

    public function create(): View
    {
        $date = today();

        $expenseTotal  = ExpenseRequest::whereDate('created_at', $date)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->sum('amount');

        $paymentTotal  = ExpensePayment::whereDate('paid_at', $date)->sum('amount');

        $expenseCount  = ExpenseRequest::whereDate('created_at', $date)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->count();

        $stockAdditions = InventoryTransaction::whereDate('created_at', $date)
            ->whereIn('type', ['purchase'])
            ->sum('quantity');

        $stockDeductions = InventoryTransaction::whereDate('created_at', $date)
            ->whereIn('type', ['usage', 'wastage'])
            ->sum('quantity');

        $recentExpenses = ExpenseRequest::with(['requester', 'category'])
            ->whereDate('created_at', $date)
            ->latest()
            ->get();

        return view('admin.daily-closings.create', compact(
            'date', 'expenseTotal', 'paymentTotal', 'expenseCount',
            'stockAdditions', 'stockDeductions', 'recentExpenses'
        ));
    }

    public function store(DailyClosingRequest $request): RedirectResponse
    {
        $date = $request->validated('date');

        $closing = DailyClosing::create([
            'date'             => $date,
            'notes'            => $request->validated('notes'),
            'expense_total'    => ExpenseRequest::whereDate('created_at', $date)->whereNotIn('status', ['pending', 'rejected'])->sum('amount'),
            'payment_total'    => ExpensePayment::whereDate('paid_at', $date)->sum('amount'),
            'expense_count'    => ExpenseRequest::whereDate('created_at', $date)->whereNotIn('status', ['pending', 'rejected'])->count(),
            'stock_additions'  => InventoryTransaction::whereDate('created_at', $date)->whereIn('type', ['purchase'])->sum('quantity'),
            'stock_deductions' => InventoryTransaction::whereDate('created_at', $date)->whereIn('type', ['usage', 'wastage'])->sum('quantity'),
            'created_by'       => auth()->id(),
        ]);

        $this->audit->log('created', 'daily_closing', $closing->id, $closing->date->toDateString());

        return redirect()->route('admin.daily-closings.show', $closing)->with('success', 'Daily closing recorded.');
    }

    public function show(DailyClosing $dailyClosing): View
    {
        $dailyClosing->load(['creator', 'verifier']);

        $expenses = ExpenseRequest::with(['requester', 'category'])
            ->whereDate('created_at', $dailyClosing->date)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->get();

        $payments = ExpensePayment::with(['expenseRequest.requester', 'payer'])
            ->whereDate('paid_at', $dailyClosing->date)
            ->get();

        return view('admin.daily-closings.show', compact('dailyClosing', 'expenses', 'payments'));
    }

    public function verify(DailyClosing $dailyClosing): RedirectResponse
    {
        $dailyClosing->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $this->audit->log('verified', 'daily_closing', $dailyClosing->id, $dailyClosing->date->toDateString());

        return back()->with('success', 'Daily closing verified.');
    }
}
