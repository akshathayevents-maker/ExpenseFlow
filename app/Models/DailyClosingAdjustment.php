<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosingAdjustment extends Model
{
    protected $fillable = [
        'daily_closing_id', 'type', 'amount', 'reason', 'notes', 'created_by',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function closing(): BelongsTo { return $this->belongsTo(DailyClosing::class, 'daily_closing_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function isCredit(): bool { return $this->type === 'credit'; }
    public function isDebit(): bool  { return $this->type === 'debit'; }
}
