<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosingAudit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'daily_closing_id', 'action_type', 'field_name',
        'old_value', 'new_value', 'remarks', 'changed_by',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function closing(): BelongsTo { return $this->belongsTo(DailyClosing::class, 'daily_closing_id'); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class, 'changed_by'); }

    public static function actionLabels(): array
    {
        return [
            'created'                  => 'Created',
            'expense_added'            => 'Expense Added',
            'expense_edited'           => 'Expense Edited',
            'expense_removed'          => 'Expense Removed',
            'expense_restored'         => 'Expense Restored',
            'adjustment_added'         => 'Adjustment Added',
            'adjustment_deleted'       => 'Adjustment Deleted',
            'opening_balance_changed'  => 'Opening Balance Changed',
            'notes_updated'            => 'Notes Updated',
            'recalculated'             => 'Recalculated',
            'finalized'                => 'Finalized',
            'snapshot_captured'        => 'Snapshot Captured',
        ];
    }

    public static function actionColors(): array
    {
        return [
            'created'                  => 'secondary',
            'expense_added'            => 'success',
            'expense_edited'           => 'info',
            'expense_removed'          => 'danger',
            'expense_restored'         => 'warning',
            'adjustment_added'         => 'primary',
            'adjustment_deleted'       => 'danger',
            'opening_balance_changed'  => 'warning',
            'notes_updated'            => 'secondary',
            'recalculated'             => 'info',
            'finalized'                => 'success',
            'snapshot_captured'        => 'secondary',
        ];
    }
}
