<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-employee configurable OT multipliers (Overtime redesign Part 1).
 *
 * At most one row per user. Absence of a row is a VALID, intentional state
 * meaning "use the implicit default" — do not backfill a row for every
 * employee. allowedMultipliersFor()/defaultMultiplierFor() are the SINGLE
 * place this row-or-implicit-default fallback logic lives; no controller or
 * view should re-derive it.
 */
class EmployeeOvertimeConfig extends Model
{
    protected $table = 'employee_overtime_configs';

    protected $fillable = [
        'user_id', 'allowed_multipliers', 'default_multiplier',
    ];

    protected function casts(): array
    {
        return [
            'allowed_multipliers' => 'array',
            'default_multiplier'  => 'decimal:2',
        ];
    }

    // Server-side constant list of selectable multiplier options shown in
    // the admin UI. A future 4th option is a one-line change here, never a
    // migration — the DB column is just a JSON array, unconstrained.
    public const MULTIPLIER_OPTIONS = [1.0, 1.5, 2.0];

    public const IMPLICIT_DEFAULT_ALLOWED = [1.5];
    public const IMPLICIT_DEFAULT_MULTIPLIER = 1.5;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The multipliers this employee's admin/manager may choose from at
     * approval time. Falls back to the implicit default when no config row
     * exists for the employee.
     *
     * @return float[]
     */
    public static function allowedMultipliersFor(User $user): array
    {
        $config = $user->relationLoaded('overtimeConfig')
            ? $user->overtimeConfig
            : static::where('user_id', $user->id)->first();

        if ($config === null) {
            return self::IMPLICIT_DEFAULT_ALLOWED;
        }

        return array_map('floatval', $config->allowed_multipliers);
    }

    /**
     * The multiplier pre-selected in the approval UI for this employee.
     * Falls back to the implicit default when no config row exists.
     */
    public static function defaultMultiplierFor(User $user): float
    {
        $config = $user->relationLoaded('overtimeConfig')
            ? $user->overtimeConfig
            : static::where('user_id', $user->id)->first();

        if ($config === null) {
            return self::IMPLICIT_DEFAULT_MULTIPLIER;
        }

        return (float) $config->default_multiplier;
    }
}
