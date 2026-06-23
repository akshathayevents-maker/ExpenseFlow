<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealClient extends Model
{
    protected $fillable = [
        'name', 'contact_person', 'mobile', 'email', 'address',
        'gst_number', 'remarks', 'active', 'created_by',
    ];

    protected $casts = ['active' => 'boolean'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DailyMealEntry::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }
}
