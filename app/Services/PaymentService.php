<?php

namespace App\Services;

use App\Models\ExpensePayment;
use App\Models\ExpenseRequest;
use App\Models\User;

class PaymentService
{
    public function record(
        ExpenseRequest $expenseRequest,
        array $data,
        User $paidBy,
        string $mode = 'cash'
    ): ExpensePayment {
        return ExpensePayment::create([
            'expense_request_id'    => $expenseRequest->id,
            'payment_mode'          => $data['payment_mode'] ?? $mode,
            'amount'                => $data['amount'] ?? $expenseRequest->amount,
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'payment_notes'         => $data['payment_notes'] ?? null,
            'paid_by'               => $paidBy->id,
            'paid_at'               => $data['paid_at'] ?? now(),
        ]);
    }
}
