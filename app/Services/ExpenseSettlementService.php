<?php

namespace App\Services;

use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseSettlementService
{
    public function __construct(
        private WalletService $walletService,
        private PaymentService $paymentService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService,
    ) {}

    public function settleViaWallet(ExpenseRequest $expenseRequest, User $admin): void
    {
        if ($expenseRequest->isSettled()) {
            throw new \RuntimeException('Request already settled.');
        }

        DB::transaction(function () use ($expenseRequest, $admin) {
            $requester = $expenseRequest->requester;
            $wallet    = $this->walletService->getOrCreate($requester);

            $this->walletService->debit(
                wallet: $wallet,
                amount: (float) $expenseRequest->amount,
                notes: "Expense deduction: {$expenseRequest->title} (#{$expenseRequest->id})",
                createdBy: $admin,
                expenseRequest: $expenseRequest
            );

            $this->paymentService->record($expenseRequest, [
                'payment_mode' => 'wallet',
                'amount'       => $expenseRequest->amount,
                'payment_notes' => 'Auto-deducted from employee wallet',
            ], $admin);

            $expenseRequest->update([
                'status'          => 'paid',
                'settlement_type' => 'wallet_deduction',
            ]);

            $this->auditLogService->log('settled', 'expense_request', $expenseRequest->id, $expenseRequest->title, [], ['method' => 'wallet_deduction']);

            $this->notificationService->send(
                $expenseRequest->requester,
                'expense_settled',
                'Expense Settled via Wallet',
                "₹" . number_format($expenseRequest->amount, 2) . " deducted from wallet for \"{$expenseRequest->title}\".",
                route('employee.expense-requests.show', $expenseRequest),
            );
        });
    }

    public function settleViaDirect(ExpenseRequest $expenseRequest, array $paymentData, User $admin): void
    {
        if ($expenseRequest->isSettled()) {
            throw new \RuntimeException('Request already settled.');
        }

        DB::transaction(function () use ($expenseRequest, $paymentData, $admin) {
            $this->paymentService->record($expenseRequest, $paymentData, $admin);

            $expenseRequest->update([
                'status'          => 'paid',
                'settlement_type' => 'direct_payment',
            ]);

            $this->auditLogService->log('settled', 'expense_request', $expenseRequest->id, $expenseRequest->title, [], ['method' => 'direct_payment']);

            $this->notificationService->send(
                $expenseRequest->requester,
                'expense_settled',
                'Expense Payment Recorded',
                "Payment of ₹" . number_format($expenseRequest->amount, 2) . " recorded for \"{$expenseRequest->title}\".",
                route('employee.expense-requests.show', $expenseRequest),
            );
        });
    }

    public function markReimbursementPending(ExpenseRequest $expenseRequest): void
    {
        if ($expenseRequest->isSettled()) {
            throw new \RuntimeException('Request already settled.');
        }

        $expenseRequest->update([
            'status'          => 'reimbursement_pending',
            'settlement_type' => 'reimbursement',
        ]);

        $this->notificationService->send(
            $expenseRequest->requester,
            'expense_approved',
            'Reimbursement Pending',
            "Your expense \"{$expenseRequest->title}\" is approved and pending reimbursement.",
            route('employee.expense-requests.show', $expenseRequest),
        );
    }

    public function reimburse(ExpenseRequest $expenseRequest, array $paymentData, User $admin): void
    {
        if (! $expenseRequest->isReimbursementPending()) {
            throw new \RuntimeException('Request is not pending reimbursement.');
        }

        DB::transaction(function () use ($expenseRequest, $paymentData, $admin) {
            $this->paymentService->record($expenseRequest, $paymentData, $admin);

            // Credit wallet as a record of reimbursement (optional — employee's record)
            $requester = $expenseRequest->requester;
            if ($requester && ! $requester->isAdmin()) {
                $wallet = $this->walletService->getOrCreate($requester);
                $this->walletService->recordReimbursement(
                    wallet: $wallet,
                    amount: (float) $expenseRequest->amount,
                    notes: "Reimbursement: {$expenseRequest->title} (#{$expenseRequest->id})",
                    createdBy: $admin,
                    expenseRequest: $expenseRequest
                );
            }

            $expenseRequest->update(['status' => 'reimbursed']);

            $this->auditLogService->log('reimbursed', 'expense_request', $expenseRequest->id, $expenseRequest->title, [], ['method' => 'reimbursement']);

            $this->notificationService->send(
                $expenseRequest->requester,
                'expense_settled',
                'Reimbursement Processed',
                "₹" . number_format($expenseRequest->amount, 2) . " reimbursed for \"{$expenseRequest->title}\".",
                route('employee.expense-requests.show', $expenseRequest),
            );
        });
    }

    public function markCompleted(ExpenseRequest $expenseRequest): void
    {
        if (! in_array($expenseRequest->status, ['paid', 'reimbursed'])) {
            throw new \RuntimeException('Only paid or reimbursed requests can be completed.');
        }

        $expenseRequest->update(['status' => 'completed']);
    }
}
