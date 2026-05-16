<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyClosing extends Model
{
    protected $fillable = [
        'date', 'status',
        'expense_total', 'payment_total', 'stock_additions', 'stock_deductions', 'expense_count',
        'opening_balance', 'total_credit', 'total_debit', 'closing_balance',
        'notes', 'created_by', 'updated_by', 'verified_by', 'verified_at',
        'finalized_at', 'snapshot_captured',
    ];

    protected $casts = [
        'date'             => 'date',
        'verified_at'      => 'datetime',
        'finalized_at'     => 'datetime',
        'snapshot_captured'=> 'boolean',
        'opening_balance'  => 'decimal:2',
        'closing_balance'  => 'decimal:2',
        'total_credit'     => 'decimal:2',
        'total_debit'      => 'decimal:2',
    ];

    // Relationships
    public function creator(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function updater(): BelongsTo  { return $this->belongsTo(User::class, 'updated_by'); }

    public function snapshotExpenses(): HasMany
    {
        return $this->hasMany(DailyClosingExpense::class, 'daily_closing_id')->orderBy('id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(DailyClosingAdjustment::class, 'daily_closing_id')->latest();
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DailyClosingAudit::class, 'daily_closing_id')->orderByDesc('created_at');
    }

    // Status helpers
    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isVerified(): bool  { return $this->status === 'verified'; }
    public function isClosed(): bool    { return $this->status === 'closed'; }
    public function isFinalized(): bool { return ! is_null($this->finalized_at); }

    public function canEdit(): bool     { return $this->isDraft(); }
    public function canDelete(): bool   { return $this->isDraft() && ! $this->isFinalized(); }
    public function canFinalize(): bool { return $this->isVerified() && ! $this->isFinalized(); }

    public static function statusColors(): array
    {
        return ['draft' => 'secondary', 'verified' => 'success', 'closed' => 'primary'];
    }

    /**
     * Compute live figures from the database for a given date.
     * Returns snake_case keys matching DB column names.
     */
    public static function computeForDate(\Illuminate\Support\Carbon|string $date): array
    {
        $d = is_string($date) ? $date : $date->toDateString();

        return [
            'expense_total'    => ExpenseRequest::whereDate('created_at', $d)
                                    ->whereNotIn('status', ['pending', 'rejected'])
                                    ->sum('amount'),
            'payment_total'    => ExpensePayment::whereDate('paid_at', $d)->sum('amount'),
            'expense_count'    => ExpenseRequest::whereDate('created_at', $d)
                                    ->whereNotIn('status', ['pending', 'rejected'])
                                    ->count(),
            'stock_additions'  => InventoryTransaction::whereDate('created_at', $d)
                                    ->whereIn('type', ['purchase'])->sum('quantity'),
            'stock_deductions' => InventoryTransaction::whereDate('created_at', $d)
                                    ->whereIn('type', ['usage', 'wastage'])->sum('quantity'),
        ];
    }

    /**
     * Get the previous closing's balance as opening balance for this date.
     */
    public static function openingBalanceFor(string $date): float
    {
        return (float) (
            static::where('date', '<', $date)
                ->orderByDesc('date')
                ->value('closing_balance') ?? 0
        );
    }
}
