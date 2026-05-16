<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'expense_request_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after'  => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCredit(): bool
    {
        return in_array($this->type, ['credit', 'reimbursement']);
    }

    public function isDebit(): bool
    {
        return in_array($this->type, ['debit']);
    }

    public static function typeColors(): array
    {
        return [
            'credit'        => 'success',
            'debit'         => 'danger',
            'adjustment'    => 'info',
            'reimbursement' => 'primary',
        ];
    }
}
