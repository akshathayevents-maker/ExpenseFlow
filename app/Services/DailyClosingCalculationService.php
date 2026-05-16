<?php

namespace App\Services;

use App\Models\DailyClosing;
use App\Models\DailyClosingAudit;
use App\Models\DailyClosingExpense;
use App\Models\ExpensePayment;
use App\Models\ExpenseRequest;
use Illuminate\Support\Facades\DB;

class DailyClosingCalculationService
{
    /**
     * Compute fresh totals from the snapshot + adjustments.
     * Does NOT mutate the model.
     */
    public function computeTotals(DailyClosing $closing): array
    {
        $expenseTotal = (float) $closing->snapshotExpenses()->active()->sum('amount');
        $expenseCount = $closing->snapshotExpenses()->active()->count();

        // Payments remain live (immutable records in expense_payments)
        $paymentTotal = (float) ExpensePayment::whereDate('paid_at', $closing->date)->sum('amount');

        $totalCredit  = (float) $closing->adjustments()->where('type', 'credit')->sum('amount');
        $totalDebit   = (float) $closing->adjustments()->where('type', 'debit')->sum('amount');

        $opening        = (float) $closing->opening_balance;
        $closingBalance = $opening - $paymentTotal - $totalDebit + $totalCredit;

        return [
            'expense_total'   => $expenseTotal,
            'payment_total'   => $paymentTotal,
            'expense_count'   => $expenseCount,
            'total_credit'    => $totalCredit,
            'total_debit'     => $totalDebit,
            'opening_balance' => $opening,
            'closing_balance' => $closingBalance,
        ];
    }

    /**
     * Compute and persist updated totals to the closing record.
     */
    public function applyTotals(DailyClosing $closing, int $changedBy): void
    {
        $totals = $this->computeTotals($closing);

        $closing->update([
            'expense_total'   => $totals['expense_total'],
            'payment_total'   => $totals['payment_total'],
            'expense_count'   => $totals['expense_count'],
            'total_credit'    => $totals['total_credit'],
            'total_debit'     => $totals['total_debit'],
            'closing_balance' => $totals['closing_balance'],
            'updated_by'      => $changedBy,
        ]);
    }

    /**
     * Preview: old stored values vs freshly computed values.
     */
    public function preview(DailyClosing $closing): array
    {
        $new = $this->computeTotals($closing);

        return [
            'old' => [
                'expense_total'   => (float) $closing->expense_total,
                'payment_total'   => (float) $closing->payment_total,
                'total_credit'    => (float) $closing->total_credit,
                'total_debit'     => (float) $closing->total_debit,
                'opening_balance' => (float) $closing->opening_balance,
                'closing_balance' => (float) $closing->closing_balance,
            ],
            'new' => $new,
        ];
    }

    /**
     * Capture a snapshot of live expense data for the closing date.
     * Idempotent: skips expenses already snapshotted (by original_expense_id).
     */
    public function captureSnapshot(DailyClosing $closing): int
    {
        $existingIds = $closing->snapshotExpenses()
            ->whereNotNull('original_expense_id')
            ->pluck('original_expense_id')
            ->toArray();

        $expenses = ExpenseRequest::with(['requester', 'category'])
            ->whereDate('created_at', $closing->date)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->when($existingIds, fn ($q) => $q->whereNotIn('id', $existingIds))
            ->get();

        foreach ($expenses as $exp) {
            DailyClosingExpense::create([
                'daily_closing_id'    => $closing->id,
                'original_expense_id' => $exp->id,
                'employee_id'         => $exp->requested_by,
                'category_id'         => $exp->expense_category_id,
                'title'               => $exp->title,
                'amount'              => $exp->amount,
                'payment_status'      => $exp->status,
                'remarks'             => null,
            ]);
        }

        $closing->update(['snapshot_captured' => true]);

        return $expenses->count();
    }

    /**
     * Log an audit entry for the closing.
     */
    public function audit(
        DailyClosing $closing,
        string $actionType,
        int $changedBy,
        ?string $fieldName = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?string $remarks = null
    ): void {
        DailyClosingAudit::create([
            'daily_closing_id' => $closing->id,
            'action_type'      => $actionType,
            'field_name'       => $fieldName,
            'old_value'        => $oldValue !== null ? (string) $oldValue : null,
            'new_value'        => $newValue !== null ? (string) $newValue : null,
            'remarks'          => $remarks,
            'changed_by'       => $changedBy,
        ]);
    }
}
