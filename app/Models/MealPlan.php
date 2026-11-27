<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlan extends Model
{
    use SoftDeletes;

    public const MEAL_TIMES = ['breakfast' => 'Breakfast', 'lunch' => 'Lunch', 'dinner' => 'Dinner'];

    protected $fillable = ['name', 'category', 'meal_times', 'description', 'items', 'price_per_person', 'is_active'];

    protected $casts = [
        'price_per_person' => 'decimal:2',
        'is_active'        => 'boolean',
        'items'            => 'array',
        'meal_times'       => 'array',
    ];

    /** "Breakfast + Lunch", or null when the package isn't tagged with meals. */
    public function mealTimesLabel(): ?string
    {
        $t = array_values(array_intersect(array_keys(self::MEAL_TIMES), $this->meal_times ?? []));

        return $t ? implode(' + ', array_map(fn ($k) => self::MEAL_TIMES[$k], $t)) : null;
    }

    public const MAX_ITEMS = 100;

    /** Dishes in the plan: the item list, or (plans saved before the list existed) the lines of the description. */
    public function dishList(): array
    {
        if (! empty($this->items)) {
            return array_values($this->items);
        }
        $out = [];
        foreach (preg_split('/[\r\n,;•]+/u', (string) $this->description) as $name) {
            $name = trim(preg_replace('/^[\-\*\d\.\)\s]+/u', '', $name));
            if ($name !== '' && mb_strlen($name) <= 150) {
                $out[] = $name;
            }
        }

        return $out;
    }

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
