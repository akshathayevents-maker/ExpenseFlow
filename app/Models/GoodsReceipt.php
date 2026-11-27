<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    protected $fillable = ['purchase_id', 'received_by', 'received_at', 'notes', 'client_token',
        'bill_number', 'bill_amount', 'bill_path', 'bill_original_name', 'reversed_at', 'reversed_by', 'reversal_reason'];

    protected $casts = ['received_at' => 'datetime', 'bill_amount' => 'float', 'reversed_at' => 'datetime'];

    public function isReversed(): bool { return $this->reversed_at !== null; }
    public function reverser(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(User::class, 'reversed_by'); }

    public function billIsPdf(): bool
    {
        return str_ends_with(strtolower((string) $this->bill_path), '.pdf');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
