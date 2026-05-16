<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosing extends Model
{
    protected $fillable = [
        'date', 'status', 'expense_total', 'payment_total',
        'stock_additions', 'stock_deductions', 'expense_count',
        'notes', 'created_by', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'date'        => 'date',
        'verified_at' => 'datetime',
    ];

    public function creator(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isVerified(): bool { return $this->status === 'verified'; }
    public function isClosed(): bool   { return $this->status === 'closed'; }

    public static function statusColors(): array
    {
        return ['draft' => 'secondary', 'verified' => 'success', 'closed' => 'primary'];
    }
}
