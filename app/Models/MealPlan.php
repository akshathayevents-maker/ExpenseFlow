<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealPlan extends Model
{
    protected $fillable = ['name', 'category', 'description', 'price_per_person', 'is_active'];

    protected $casts = [
        'price_per_person' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(HallBooking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function categories(): array
    {
        return ['standard' => 'Standard', 'premium' => 'Premium', 'custom' => 'Custom'];
    }
}
