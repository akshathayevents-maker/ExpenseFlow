<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosingExpense extends Model
{
    protected $fillable = [
        'daily_closing_id', 'original_expense_id', 'employee_id', 'category_id',
        'title', 'amount', 'payment_status', 'remarks', 'removed',
    ];

    protected $casts = ['removed' => 'boolean', 'amount' => 'decimal:2'];

    public function closing(): BelongsTo    { return $this->belongsTo(DailyClosing::class, 'daily_closing_id'); }
    public function employee(): BelongsTo  { return $this->belongsTo(User::class, 'employee_id'); }
    public function category(): BelongsTo  { return $this->belongsTo(ExpenseCategory::class, 'category_id'); }
    public function original(): BelongsTo  { return $this->belongsTo(ExpenseRequest::class, 'original_expense_id'); }

    public function scopeActive($query)   { return $query->where('removed', false); }
    public function scopeRemoved($query)  { return $query->where('removed', true); }

    public static function statusColors(): array
    {
        return [
            'pending'               => 'warning',
            'approved'              => 'info',
            'paid'                  => 'success',
            'reimbursement_pending' => 'primary',
            'reimbursed'            => 'success',
            'completed'             => 'secondary',
            'rejected'              => 'danger',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending'               => 'Pending',
            'approved'              => 'Approved',
            'paid'                  => 'Paid',
            'reimbursement_pending' => 'Reimb. Pending',
            'reimbursed'            => 'Reimbursed',
            'completed'             => 'Completed',
            'rejected'              => 'Rejected',
        ];
    }
}
