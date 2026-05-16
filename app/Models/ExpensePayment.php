<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePayment extends Model
{
    protected $fillable = [
        'expense_request_id',
        'payment_mode',
        'amount',
        'transaction_reference',
        'payment_notes',
        'paid_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public static function modeColors(): array
    {
        return [
            'cash'          => 'success',
            'upi'           => 'primary',
            'bank_transfer' => 'info',
            'wallet'        => 'warning',
        ];
    }

    public static function modeLabels(): array
    {
        return [
            'cash'          => 'Cash',
            'upi'           => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'wallet'        => 'Wallet',
        ];
    }
}
