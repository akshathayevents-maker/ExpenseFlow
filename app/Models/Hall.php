<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    protected $fillable = ['name', 'description', 'capacity', 'location', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bookings(): HasMany
    {
        return $this->hasMany(HallBooking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
