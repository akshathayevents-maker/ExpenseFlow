<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveLedger extends Model
{
    protected $table = 'employee_leave_ledger';

    protected $fillable = [
        'user_id', 'leave_type_id', 'entry_date', 'type', 'amount',
        'reference_type', 'reference_id', 'created_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount'     => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
