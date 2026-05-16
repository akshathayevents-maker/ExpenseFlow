<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyClosingRequest;
use App\Models\DailyClosing;
use App\Models\DailyClosingAdjustment;
use App\Models\DailyClosingExpense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\DailyClosingCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyClosingController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private DailyClosingCalculationService $calc,
    ) {}

    // ─── Index ───────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $filtered = fn () => DailyClosing::query()
            ->when($request->get('from'),       fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($request->get('to'),         fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->when($request->get('status'),     fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('created_by'), fn ($q, $v) => $q->where('created_by', $v));

        $closings = $filtered()
            ->with(['creator', 'verifier', 'updater'])
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $expenseTotal  = (float) $filtered()->sum('expense_total');
        $paymentTotal  = (float) $filtered()->sum('payment_total');
        $summary = [
            'expense_total'  => $expenseTotal,
            'payment_total'  => $paymentTotal,
            'variance'       => $expenseTotal - $paymentTotal,
            'total_count'    => $filtered()->count(),
            'draft_count'    => $filtered()->where('status', 'draft')->count(),
            'verified_count' => $filtered()->where('status', 'verified')->count(),
            'closed_count'   => $filtered()->where('status', 'closed')->count(),
        ];

        $todayClosed = DailyClosing::whereDate('date', today())->exists();
        $adminUsers  = User::whereIn('role', ['admin'])->orderBy('name')->get();

        return view('admin.daily-closings.index', compact('closings', 'todayClosed', 'adminUsers', 'summary'));
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function create(Request $request): View|RedirectResponse
    {
        $requestedDate = $request->get('date');

        if ($requestedDate) {
            if ($requestedDate > today()->toDateString()) {
                return redirect()->route('admin.daily-closings.index')
                    ->with('error', 'Cannot create a closing for a future date.');
            }

            $existing = DailyClosing::whereDate('date', $requestedDate)->first();
            if ($existing) {
                return redirect()->route('admin.daily-closings.show', $existing)
                    ->with('error', "A closing for {$existing->date->format('d M Y')} already exists. You can edit it from here.");
            }

            $date = \Carbon\Carbon::parse($requestedDate);
        } else {
            $date = today();
        }

        $figures        = DailyClosing::computeForDate($date);
        $recentExpenses = ExpenseRequest::with(['requester', 'category'])
            ->whereDate('created_at', $date)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->latest()
            ->get();

        return view('admin.daily-closings.create', [
            'date'            => $date,
            'recentExpenses'  => $recentExpenses,
            'expenseTotal'    => $figures['expense_total'],
            'paymentTotal'    => $figures['payment_total'],
            'expenseCount'    => $figures['expense_count'],
            'stockAdditions'  => $figures['stock_additions'],
            'stockDeductions' => $figures['stock_deductions'],
        ]);
    }

    // ─── Store ───────────────────────────────────────────────────────────────

    public function store(DailyClosingRequest $request): RedirectResponse
    {
        $date = $request->validated('date');

        $closing = DB::transaction(function () use ($date, $request) {
            if (DailyClosing::whereDate('date', $date)->lockForUpdate()->exists()) {
                throw new \RuntimeException("A closing for {$date} already exists.");
            }

            $figures        = DailyClosing::computeForDate($date);
            $openingBalance = DailyClosing::openingBalanceFor($date);

            $closing = DailyClosing::create(array_merge($figures, [
                'date'            => $date,
                'notes'           => $request->validated('notes'),
                'created_by'      => auth()->id(),
                'opening_balance' => $openingBalance,
                'closing_balance' => $openingBalance - (float) $figures['payment_total'],
            ]));

            // Capture expense snapshot
            $this->calc->captureSnapshot($closing);

            // Recalculate with snapshot to get consistent totals
            $this->calc->applyTotals($closing, auth()->id());

            $this->calc->audit($closing, 'created', auth()->id(), remarks: "Date: {$date}");
            $this->calc->audit($closing, 'snapshot_captured', auth()->id(),
                remarks: "{$closing->expense_count} expenses captured");

            return $closing->fresh();
        });

        $this->audit->log('created', 'daily_closing', $closing->id, $closing->date->toDateString());

        return redirect()->route('admin.daily-closings.show', $closing)
            ->with('success', 'Daily closing recorded for ' . $closing->date->format('d M Y') . '.');
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function show(DailyClosing $dailyClosing): View
    {
        $dailyClosing->load(['creator', 'verifier', 'updater']);

        $expenses = ExpenseRequest::with(['requester', 'category'])
            ->whereDate('created_at', $dailyClosing->date)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->get();

        $payments = ExpensePayment::with(['expenseRequest.requester', 'payer'])
            ->whereDate('paid_at', $dailyClosing->date)
            ->get();

        $liveFigures = DailyClosing::computeForDate($dailyClosing->date);
        $hasDrift    = (
            abs($liveFigures['expense_total'] - (float) $dailyClosing->expense_total) > 0.005 ||
            abs($liveFigures['payment_total'] - (float) $dailyClosing->payment_total) > 0.005 ||
            abs($liveFigures['expense_count'] - $dailyClosing->expense_count) > 0
        );

        return view('admin.daily-closings.show', compact(
            'dailyClosing', 'expenses', 'payments', 'liveFigures', 'hasDrift'
        ));
    }

    // ─── Edit ────────────────────────────────────────────────────────────────

    public function edit(DailyClosing $dailyClosing): View
    {
        $dailyClosing->load([
            'creator', 'verifier', 'updater',
            'snapshotExpenses.employee', 'snapshotExpenses.category',
            'adjustments.creator',
        ]);

        $audits     = $dailyClosing->audits()->with('user')->paginate(20);
        $categories = ExpenseCategory::active()->orderBy('name')->pluck('name', 'id');
        $employees  = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->pluck('name', 'id');
        $totals     = $this->calc->computeTotals($dailyClosing);

        return view('admin.daily-closings.edit', compact(
            'dailyClosing', 'audits', 'categories', 'employees', 'totals'
        ));
    }

    // ─── Update (notes + opening balance only) ────────────────────────────────

    public function update(DailyClosingRequest $request, DailyClosing $dailyClosing): RedirectResponse
    {
        $oldNotes   = $dailyClosing->notes;
        $oldOpening = (float) $dailyClosing->opening_balance;
        $newNotes   = $request->validated('notes');
        $newOpening = (float) ($request->input('opening_balance', $oldOpening));

        DB::transaction(function () use ($dailyClosing, $newNotes, $newOpening, $oldNotes, $oldOpening) {
            $dailyClosing->update([
                'notes'           => $newNotes,
                'opening_balance' => $newOpening,
                'updated_by'      => auth()->id(),
            ]);

            if ($oldOpening !== $newOpening) {
                $this->calc->audit($dailyClosing, 'opening_balance_changed', auth()->id(),
                    'opening_balance', $oldOpening, $newOpening);
            }
            if ($oldNotes !== $newNotes) {
                $this->calc->audit($dailyClosing, 'notes_updated', auth()->id(),
                    'notes', $oldNotes, $newNotes);
            }

            $this->calc->applyTotals($dailyClosing, auth()->id());
        });

        return redirect()->route('admin.daily-closings.edit', $dailyClosing)
            ->with('success', 'Closing updated.');
    }

    // ─── Verify ──────────────────────────────────────────────────────────────

    public function verify(DailyClosing $dailyClosing): RedirectResponse
    {
        $dailyClosing->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $this->calc->audit($dailyClosing, 'recalculated', auth()->id(), remarks: 'Verified');
        $this->audit->log('verified', 'daily_closing', $dailyClosing->id, $dailyClosing->date->toDateString());

        return back()->with('success', 'Daily closing verified.');
    }

    // ─── Recalculate ─────────────────────────────────────────────────────────

    public function recalculate(DailyClosing $dailyClosing): RedirectResponse
    {
        DB::transaction(function () use ($dailyClosing) {
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'recalculated', auth()->id());
        });

        $this->audit->log('recalculated', 'daily_closing', $dailyClosing->id, $dailyClosing->date->toDateString());

        return back()->with('success', 'Figures recalculated from snapshot + adjustments.');
    }

    // ─── Finalize ────────────────────────────────────────────────────────────

    public function finalize(DailyClosing $dailyClosing): RedirectResponse
    {
        if ($dailyClosing->isFinalized()) {
            return back()->with('error', 'Closing is already finalized.');
        }

        DB::transaction(function () use ($dailyClosing) {
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $dailyClosing->update([
                'status'       => 'closed',
                'finalized_at' => now(),
                'updated_by'   => auth()->id(),
            ]);
            $this->calc->audit($dailyClosing, 'finalized', auth()->id());
        });

        $this->audit->log('finalized', 'daily_closing', $dailyClosing->id, $dailyClosing->date->toDateString());

        return redirect()->route('admin.daily-closings.show', $dailyClosing)
            ->with('success', 'Daily closing finalized and locked.');
    }

    // ─── Preview (AJAX) ──────────────────────────────────────────────────────

    public function preview(DailyClosing $dailyClosing): JsonResponse
    {
        return response()->json($this->calc->preview($dailyClosing));
    }

    // ─── Snapshot capture (AJAX) ─────────────────────────────────────────────

    public function captureSnapshot(DailyClosing $dailyClosing): JsonResponse
    {
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        $count = DB::transaction(function () use ($dailyClosing) {
            $n = $this->calc->captureSnapshot($dailyClosing);
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'snapshot_captured', auth()->id(),
                remarks: "{$n} new expenses captured from live data");
            return $n;
        });

        $dailyClosing->refresh();
        $totals = $this->calc->computeTotals($dailyClosing);

        return response()->json([
            'message' => "{$count} expense(s) synced from live data.",
            'totals'  => $totals,
        ]);
    }

    // ─── Expense: store (AJAX) ───────────────────────────────────────────────

    public function storeExpense(Request $request, DailyClosing $dailyClosing): JsonResponse
    {
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01|max:9999999',
            'employee_id'    => 'nullable|exists:users,id',
            'category_id'    => 'nullable|exists:expense_categories,id',
            'payment_status' => 'required|in:pending,approved,paid,reimbursement_pending,reimbursed,completed',
            'remarks'        => 'nullable|string|max:1000',
        ]);

        $expense = DB::transaction(function () use ($data, $dailyClosing) {
            $exp = DailyClosingExpense::create(array_merge($data, [
                'daily_closing_id' => $dailyClosing->id,
            ]));
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'expense_added', auth()->id(),
                remarks: "{$data['title']} — ₹{$data['amount']}");
            return $exp->load(['employee', 'category']);
        });

        $closing = $dailyClosing;

        return response()->json([
            'message' => 'Expense added.',
            'row_html' => view('admin.daily-closings.partials.expense-row', compact('expense', 'closing'))->render(),
            'totals'   => $this->calc->computeTotals($dailyClosing),
        ]);
    }

    // ─── Expense: update (AJAX) ──────────────────────────────────────────────

    public function updateExpense(Request $request, DailyClosing $dailyClosing, DailyClosingExpense $expense): JsonResponse
    {
        abort_if($expense->daily_closing_id !== $dailyClosing->id, 404);
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01|max:9999999',
            'employee_id'    => 'nullable|exists:users,id',
            'category_id'    => 'nullable|exists:expense_categories,id',
            'payment_status' => 'required|in:pending,approved,paid,reimbursement_pending,reimbursed,completed',
            'remarks'        => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $expense, $dailyClosing) {
            $old = "amount={$expense->amount}, status={$expense->payment_status}";
            $new = "amount={$data['amount']}, status={$data['payment_status']}";
            $expense->update($data);
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'expense_edited', auth()->id(),
                'expense_id:' . $expense->id, $old, $new, $expense->title);
        });

        $expense->refresh()->load(['employee', 'category']);

        return response()->json([
            'message'  => 'Expense updated.',
            'row_html' => view('admin.daily-closings.partials.expense-row', ['expense' => $expense, 'closing' => $dailyClosing])->render(),
            'totals'   => $this->calc->computeTotals($dailyClosing),
        ]);
    }

    // ─── Expense: remove (AJAX) ──────────────────────────────────────────────

    public function removeExpense(DailyClosing $dailyClosing, DailyClosingExpense $expense): JsonResponse
    {
        abort_if($expense->daily_closing_id !== $dailyClosing->id, 404);
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        DB::transaction(function () use ($expense, $dailyClosing) {
            $expense->update(['removed' => true]);
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'expense_removed', auth()->id(),
                remarks: "{$expense->title} — ₹{$expense->amount}");
        });

        $expense->refresh()->load(['employee', 'category']);

        return response()->json([
            'message'  => 'Expense removed from closing.',
            'row_html' => view('admin.daily-closings.partials.expense-row', ['expense' => $expense, 'closing' => $dailyClosing])->render(),
            'totals'   => $this->calc->computeTotals($dailyClosing),
        ]);
    }

    // ─── Expense: restore (AJAX) ─────────────────────────────────────────────

    public function restoreExpense(DailyClosing $dailyClosing, DailyClosingExpense $expense): JsonResponse
    {
        abort_if($expense->daily_closing_id !== $dailyClosing->id, 404);
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        DB::transaction(function () use ($expense, $dailyClosing) {
            $expense->update(['removed' => false]);
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'expense_restored', auth()->id(),
                remarks: "{$expense->title} — ₹{$expense->amount}");
        });

        $expense->refresh()->load(['employee', 'category']);

        return response()->json([
            'message'  => 'Expense restored.',
            'row_html' => view('admin.daily-closings.partials.expense-row', ['expense' => $expense, 'closing' => $dailyClosing])->render(),
            'totals'   => $this->calc->computeTotals($dailyClosing),
        ]);
    }

    // ─── Adjustment: store (AJAX) ─────────────────────────────────────────────

    public function storeAdjustment(Request $request, DailyClosing $dailyClosing): JsonResponse
    {
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        $data = $request->validate([
            'type'   => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01|max:9999999',
            'reason' => 'required|string|min:3|max:255',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $adjustment = DB::transaction(function () use ($data, $dailyClosing) {
            $adj = DailyClosingAdjustment::create(array_merge($data, [
                'daily_closing_id' => $dailyClosing->id,
                'created_by'       => auth()->id(),
            ]));
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'adjustment_added', auth()->id(),
                'type', null, "{$data['type']} ₹{$data['amount']}", $data['reason']);
            return $adj->load('creator');
        });

        return response()->json([
            'message'    => 'Adjustment recorded.',
            'adjustment' => [
                'id'         => $adjustment->id,
                'type'       => $adjustment->type,
                'amount'     => (float) $adjustment->amount,
                'reason'     => $adjustment->reason,
                'notes'      => $adjustment->notes,
                'created_by' => $adjustment->creator->name,
                'created_at' => $adjustment->created_at->format('d M Y, h:i A'),
            ],
            'totals' => $this->calc->computeTotals($dailyClosing),
        ]);
    }

    // ─── Adjustment: destroy (AJAX) ───────────────────────────────────────────

    public function destroyAdjustment(DailyClosing $dailyClosing, DailyClosingAdjustment $adjustment): JsonResponse
    {
        abort_if($adjustment->daily_closing_id !== $dailyClosing->id, 404);
        if (! $dailyClosing->canEdit()) {
            return response()->json(['message' => 'Cannot modify a finalized closing.'], 422);
        }

        DB::transaction(function () use ($adjustment, $dailyClosing) {
            $label = "{$adjustment->type} ₹{$adjustment->amount} — {$adjustment->reason}";
            $adjustment->delete();
            $this->calc->applyTotals($dailyClosing, auth()->id());
            $this->calc->audit($dailyClosing, 'adjustment_deleted', auth()->id(), remarks: $label);
        });

        return response()->json([
            'message' => 'Adjustment removed.',
            'totals'  => $this->calc->computeTotals($dailyClosing),
        ]);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(DailyClosing $dailyClosing): RedirectResponse
    {
        if (! $dailyClosing->canDelete()) {
            return back()->with('error', 'Only non-finalized draft closings can be deleted.');
        }

        $label = $dailyClosing->date->format('d M Y');
        $id    = $dailyClosing->id;
        $dailyClosing->delete();

        $this->audit->log('deleted', 'daily_closing', $id, $label);

        return redirect()->route('admin.daily-closings.index')
            ->with('success', "Daily closing for {$label} deleted.");
    }
}
