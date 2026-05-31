<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlan extends Model
{
    use SoftDeletes;

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

    public static function categoryColors(): array
    {
        return [
            'standard' => ['bg' => 'rgba(20,20,18,.04)',        'border' => 'rgba(20,20,18,.08)',      'color' => '#64748b'],
            'premium'  => ['bg' => 'rgba(169,131,56,.09)',      'border' => 'rgba(169,131,56,.22)',    'color' => '#a98338'],
            'custom'   => ['bg' => 'rgba(61,115,88,.08)',       'border' => 'rgba(61,115,88,.2)',      'color' => '#3c8c64'],
        ];
    }
}
