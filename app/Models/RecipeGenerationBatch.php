<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipeGenerationBatch extends Model
{
    public const STATUSES = [
        'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed',
        'partially_completed' => 'Partially completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'created_by', 'status', 'label', 'config', 'requested_count', 'generated_count', 'failed_count',
        'duplicate_count', 'provider', 'model', 'error', 'started_at', 'completed_at',
    ];

    protected $casts = ['config' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function items(): HasMany
    {
        return $this->hasMany(RecipeGenerationItem::class)->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'partially_completed', 'failed', 'cancelled'], true);
    }

    public function progressPercent(): int
    {
        $done = $this->generated_count + $this->failed_count + $this->duplicate_count;

        return $this->requested_count > 0 ? (int) min(100, round($done / $this->requested_count * 100)) : 0;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'completed' => 'ok', 'partially_completed' => 'warn', 'failed', 'cancelled' => 'bad', default => 'info',
        };
    }
}
