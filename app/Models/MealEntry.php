<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealEntry extends Model
{
    protected $fillable = [
        'meal_client_id', 'entry_date', 'remarks',
        'created_by', 'updated_by',
        'planned_updated_by', 'actual_updated_by',
    ];

    protected $casts = ['entry_date' => 'date'];

    public function client(): BelongsTo { return $this->belongsTo(MealClient::class, 'meal_client_id'); }
    public function items(): HasMany { return $this->hasMany(MealEntryItem::class)->orderBy('sort_order'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function plannedUpdater(): BelongsTo { return $this->belongsTo(User::class, 'planned_updated_by'); }
    public function actualUpdater(): BelongsTo { return $this->belongsTo(User::class, 'actual_updated_by'); }

    public function totalPlanned(): int { return $this->items->sum(fn($i) => $i->planned_count ?? 0); }
    public function totalActual(): int  { return $this->items->sum(fn($i) => $i->actual_count ?? 0); }
}
