<x-admin-layout title="Audit Logs">
<div class="page-header">
    <h4 class="mb-0 fw-bold">Audit Logs</h4>
    <p class="text-muted mb-0 small">All critical actions tracked with before/after values</p>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Module</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">All Modules</option>
                    @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ ($filters['module'] ?? '') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                    <option value="{{ $act }}" {{ ($filters['action'] ?? '') === $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Reference</th>
                        <th>Changes</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $colors = \App\Models\AuditLog::actionColors();
                        $color  = $colors[$log->action] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="text-muted small" style="white-space:nowrap">
                            {{ $log->created_at->format('d M, h:i A') }}
                        </td>
                        <td class="fw-semibold small">{{ $log->user->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border"
                                  style="font-size:.68rem;text-transform:uppercase">
                                {{ $log->module }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.68rem;text-transform:uppercase">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="small">
                            @if($log->reference_label)
                                <span class="text-muted" style="font-size:.75rem">#{{ $log->reference_id }}</span>
                                <div>{{ $log->reference_label }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($log->old_values || $log->new_values)
                            <button class="btn btn-xs btn-outline-secondary py-0 px-1"
                                    style="font-size:.72rem"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#log-{{ $log->id }}">
                                <i class="bi bi-chevron-down"></i> View
                            </button>
                            <div class="collapse mt-1" id="log-{{ $log->id }}">
                                @if($log->old_values)
                                <div class="text-muted" style="font-size:.7rem">Before:</div>
                                <pre class="bg-light rounded p-1 mb-1" style="font-size:.68rem;max-height:100px;overflow:auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                                @if($log->new_values)
                                <div class="text-muted" style="font-size:.7rem">After:</div>
                                <pre class="bg-light rounded p-1 mb-0" style="font-size:.68rem;max-height:100px;overflow:auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            </div>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-2 d-block mb-2"></i>No audit records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $logs->links() }}</div>
    @endif
</div>
</x-admin-layout>
