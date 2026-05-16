<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function __construct(private ?Request $request = null) {}

    public function log(
        string $action,
        string $module,
        ?int   $referenceId   = null,
        ?string $referenceLabel = null,
        array  $oldValues     = [],
        array  $newValues     = []
    ): AuditLog {
        return AuditLog::create([
            'user_id'         => auth()->id(),
            'action'          => $action,
            'module'          => $module,
            'reference_id'    => $referenceId,
            'reference_label' => $referenceLabel,
            'old_values'      => $oldValues ?: null,
            'new_values'      => $newValues ?: null,
            'ip_address'      => $this->request?->ip(),
            'user_agent'      => $this->request?->userAgent(),
        ]);
    }
}
