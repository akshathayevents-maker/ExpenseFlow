<x-admin-layout title="Daily Closings">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Daily Closings</h4>
        <p class="text-muted mb-0 small">End-of-day operational summaries</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(!$todayClosed)
            <a href="{{ route('admin.daily-closings.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar-check me-1"></i> Close Today
            </a>
        @else
            <span class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3">
                <i class="bi bi-check-circle me-1"></i> Today Closed
            </span>
        @endif
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#pastDateModal">
            <i class="bi bi-calendar-plus me-1"></i> Close Past Date
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-auto">
                <label class="form-label form-label-sm mb-1 text-muted">From</label>
                <input type="date" name="from" class="form-control form-control-sm"
                       value="{{ request('from') }}" max="{{ today()->toDateString() }}">
            </div>
            <div class="col-sm-auto">
                <label class="form-label form-label-sm mb-1 text-muted">To</label>
                <input type="date" name="to" class="form-control form-control-sm"
                       value="{{ request('to') }}" max="{{ today()->toDateString() }}">
            </div>
            <div class="col-sm-auto">
                <label class="form-label form-label-sm mb-1 text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['draft' => 'Draft', 'verified' => 'Verified', 'closed' => 'Closed'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <label class="form-label form-label-sm mb-1 text-muted">Created By</label>
                <select name="created_by" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($adminUsers as $u)
                        <option value="{{ $u->id }}" {{ request('created_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.daily-closings.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Date</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-center">Count</th>
                        <th class="text-end">Payments</th>
                        <th class="text-center">Status</th>
                        <th>Created By</th>
                        <th>Last Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($closings as $closing)
                    @php
                        $colors = \App\Models\DailyClosing::statusColors();
                        $color  = $colors[$closing->status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="fw-semibold">
                            {{ $closing->date->format('d M Y') }}
                            @if($closing->date->isToday())
                                <span class="badge bg-info-subtle text-info ms-1" style="font-size:.62rem">Today</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($closing->expense_total, 2) }}</td>
                        <td class="text-center text-muted small">{{ $closing->expense_count }}</td>
                        <td class="text-end text-muted small">₹{{ number_format($closing->payment_total, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.7rem;text-transform:uppercase">
                                {{ $closing->status }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $closing->creator->name }}</td>
                        <td class="text-muted small">
                            @if($closing->updater)
                                {{ $closing->updated_at->format('d M, h:i A') }}<br>
                                <span class="text-muted" style="font-size:.7rem">by {{ $closing->updater->name }}</span>
                            @else
                                {{ $closing->created_at->format('d M, h:i A') }}
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.daily-closings.show', $closing) }}"
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($closing->canEdit())
                                    <a href="{{ route('admin.daily-closings.edit', $closing) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.daily-closings.recalculate', $closing) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Recalculate"
                                                data-loading-text="">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $closing->id }}"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Per-row delete modal --}}
                    @if($closing->canDelete())
                    <div class="modal fade" id="deleteModal{{ $closing->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h6 class="modal-title"><i class="bi bi-trash text-danger me-2"></i>Delete Closing</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body small">
                                    <div class="alert alert-danger py-2 mb-2 small">Cannot be undone.</div>
                                    Delete closing for <strong>{{ $closing->date->format('d M Y') }}</strong>?
                                </div>
                                <div class="modal-footer border-0 py-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST" action="{{ route('admin.daily-closings.destroy', $closing) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Deleting…">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>No daily closings recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($closings->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $closings->links() }}</div>
    @endif
</div>

{{-- Past Date Modal --}}
<div class="modal fade" id="pastDateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-calendar-plus text-primary me-2"></i>Close Past Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select a past date to create or view its daily closing.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Date <span class="text-danger">*</span></label>
                    <input type="date" id="pastDateInput" class="form-control"
                           max="{{ today()->subDay()->toDateString() }}"
                           required>
                    <div class="form-text">Future dates are not allowed.</div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="pastDateProceed">
                    <i class="bi bi-arrow-right me-1"></i> Proceed
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('pastDateProceed').addEventListener('click', function () {
    const input = document.getElementById('pastDateInput');
    if (!input.value) {
        input.classList.add('is-invalid');
        return;
    }
    input.classList.remove('is-invalid');
    window.location.href = '{{ route('admin.daily-closings.create') }}?date=' + encodeURIComponent(input.value);
});

document.getElementById('pastDateInput').addEventListener('input', function () {
    this.classList.remove('is-invalid');
});
</script>
@endpush
</x-admin-layout>
