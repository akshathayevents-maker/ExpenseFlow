<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRequestRevision extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'event_request_id', 'action', 'actor_type', 'actor_name', 'comment', 'snapshot',
    ];

    protected $casts = [
        'snapshot'   => 'array',
        'created_at' => 'datetime',
    ];

    public static function labels(): array
    {
        return [
            'created'            => 'Request created',
            'client_submitted'   => 'Client submitted',
            'admin_modified'     => 'Admin modified',
            'need_changes'       => 'Changes requested',
            'client_resubmitted' => 'Client resubmitted',
            'approved'           => 'Approved',
            'rejected'           => 'Rejected',
        ];
    }

    public function eventRequest(): BelongsTo
    {
        return $this->belongsTo(EventRequest::class);
    }
}
