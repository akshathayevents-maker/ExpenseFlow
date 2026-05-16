<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table    = 'app_notifications';
    protected $fillable = ['user_id', 'type', 'title', 'body', 'link', 'data', 'read_at'];
    protected $casts    = ['data' => 'array', 'read_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function typeIcons(): array
    {
        return [
            'expense_approved'  => ['icon' => 'bi-check-circle-fill', 'color' => 'success'],
            'expense_rejected'  => ['icon' => 'bi-x-circle-fill',     'color' => 'danger'],
            'expense_submitted' => ['icon' => 'bi-file-earmark-plus',  'color' => 'primary'],
            'low_stock'         => ['icon' => 'bi-exclamation-triangle-fill', 'color' => 'warning'],
            'out_of_stock'      => ['icon' => 'bi-bag-x-fill',         'color' => 'danger'],
            'reimbursed'        => ['icon' => 'bi-cash-coin',          'color' => 'success'],
            'wallet_low'        => ['icon' => 'bi-wallet2',            'color' => 'warning'],
            'payment_recorded'  => ['icon' => 'bi-credit-card-fill',   'color' => 'info'],
            'pending_reminder'  => ['icon' => 'bi-clock-fill',         'color' => 'warning'],
            'daily_summary'     => ['icon' => 'bi-bar-chart-fill',     'color' => 'secondary'],
        ];
    }
}
