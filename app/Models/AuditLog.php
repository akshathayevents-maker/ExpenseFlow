<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'module', 'reference_id',
        'reference_label', 'old_values', 'new_values',
        'ip_address', 'user_agent',
    ];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];

    public $timestamps = true;
    const UPDATED_AT   = null; // audit logs are immutable

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function actionColors(): array
    {
        return [
            'approved'  => 'success',
            'rejected'  => 'danger',
            'deleted'   => 'danger',
            'credited'  => 'success',
            'debited'   => 'warning',
            'adjusted'  => 'secondary',
            'created'   => 'primary',
            'updated'   => 'info',
            'settled'   => 'success',
            'reimbursed'=> 'success',
        ];
    }
}
