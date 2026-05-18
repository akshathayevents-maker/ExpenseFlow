<?php

namespace App\Services;

use App\Models\ExpenseRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    private const LOW_BALANCE_THRESHOLD = 500;

    public function __construct(
        private NotificationService $notificationService,
        private AuditLogService $auditLogService,
    ) {}

    public function getOrCreate(User $user): Wallet
    {
        return Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
    }

    public function credit(
        Wallet $wallet,
        float $amount,
        ?string $notes,
        User $createdBy,
        ?ExpenseRequest $expenseRequest = null
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $notes, $createdBy, $expenseRequest) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $before = (float) $wallet->balance;
            $after  = $before + $amount;

            $wallet->update(['balance' => $after]);

            $txn = WalletTransaction::create([
                'wallet_id'          => $wallet->id,
                'expense_request_id' => $expenseRequest?->id,
                'type'               => 'credit',
                'amount'             => $amount,
                'balance_before'     => $before,
                'balance_after'      => $after,
                'notes'              => $notes,
                'created_by'         => $createdBy->id,
            ]);

            $this->auditLogService->log('credited', 'wallet', $wallet->id, $wallet->user->name ?? '', [], ['amount' => $amount, 'balance' => $after]);

            $this->notificationService->send(
                $wallet->user,
                'wallet_credit',
                'Wallet Credited',
                "₹" . number_format($amount, 2) . " credited to your wallet. Balance: ₹" . number_format($after, 2),
                route('employee.wallet.show'),
            );

            return $txn;
        });
    }

    public function debit(
        Wallet $wallet,
        float $amount,
        ?string $notes,
        User $createdBy,
        ?ExpenseRequest $expenseRequest = null
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $notes, $createdBy, $expenseRequest) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            if ($wallet->balance < $amount) {
                throw new \RuntimeException("Insufficient wallet balance. Available: ₹{$wallet->balance}");
            }

            $before = (float) $wallet->balance;
            $after  = $before - $amount;

            $wallet->update(['balance' => $after]);

            $txn = WalletTransaction::create([
                'wallet_id'          => $wallet->id,
                'expense_request_id' => $expenseRequest?->id,
                'type'               => 'debit',
                'amount'             => $amount,
                'balance_before'     => $before,
                'balance_after'      => $after,
                'notes'              => $notes,
                'created_by'         => $createdBy->id,
            ]);

            $this->auditLogService->log('debited', 'wallet', $wallet->id, $wallet->user->name ?? '', [], ['amount' => $amount, 'balance' => $after]);

            $this->notificationService->send(
                $wallet->user,
                'wallet_debit',
                'Wallet Debited',
                "₹" . number_format($amount, 2) . " debited from your wallet. Balance: ₹" . number_format($after, 2),
                route('employee.wallet.show'),
            );

            if ($after < self::LOW_BALANCE_THRESHOLD) {
                $this->notificationService->send(
                    $wallet->user,
                    'wallet_low',
                    'Low Wallet Balance',
                    "Your wallet balance is low: ₹" . number_format($after, 2),
                    route('employee.wallet.show'),
                );
            }

            return $txn;
        });
    }

    public function adjust(
        Wallet $wallet,
        float $newBalance,
        ?string $notes,
        User $createdBy
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $newBalance, $notes, $createdBy) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $before = (float) $wallet->balance;

            $wallet->update(['balance' => $newBalance]);

            $txn = WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'adjustment',
                'amount'         => abs($newBalance - $before),
                'balance_before' => $before,
                'balance_after'  => $newBalance,
                'notes'          => $notes,
                'created_by'     => $createdBy->id,
            ]);

            $this->auditLogService->log('adjusted', 'wallet', $wallet->id, $wallet->user->name ?? '', ['balance' => $before], ['balance' => $newBalance]);

            return $txn;
        });
    }

    public function recordReimbursement(
        Wallet $wallet,
        float $amount,
        ?string $notes,
        User $createdBy,
        ExpenseRequest $expenseRequest
    ): WalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $notes, $createdBy, $expenseRequest) {
            $wallet = Wallet::lockForUpdate()->find($wallet->id);

            $before = (float) $wallet->balance;
            $after  = $before + $amount;

            $wallet->update(['balance' => $after]);

            return WalletTransaction::create([
                'wallet_id'          => $wallet->id,
                'expense_request_id' => $expenseRequest->id,
                'type'               => 'reimbursement',
                'amount'             => $amount,
                'balance_before'     => $before,
                'balance_after'      => $after,
                'notes'              => $notes,
                'created_by'         => $createdBy->id,
            ]);
        });
    }
}
