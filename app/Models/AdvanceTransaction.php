<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceTransaction extends Model
{
    // Immutable, append-only ledger — no update() calls should ever target
    // this model outside of correcting a mistaken `reference` field. Amount/
    // type/balance_after must never be edited after creation.
    protected $fillable = [
        'employee_advance_id', 'user_id', 'transaction_date', 'type',
        'amount', 'reference', 'balance_after', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount'           => 'decimal:2',
            'balance_after'    => 'decimal:2',
        ];
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(EmployeeAdvance::class, 'employee_advance_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
