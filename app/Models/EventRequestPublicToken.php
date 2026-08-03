<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRequestPublicToken extends Model
{
    protected $fillable = [
        'event_request_id', 'token', 'is_active', 'expires_at', 'revoked_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }

    public function isUsable(): bool
    {
        if (! $this->is_active || $this->revoked_at) {
            return false;
        }

        return ! $this->expires_at || $this->expires_at->isFuture();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('revoked_at');
    }
}
