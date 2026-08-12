<x-admin-layout title="Event Requests">
<x-event-request.admin-responsive-styles />
@php
    $statusChip = fn ($status) => match($status) {
        'rejected' => 'text-bg-danger',
        'scheduled' => 'text-bg-success',
        'approved' => 'text-bg-success',
        'need_changes' => 'text-bg-warning',
        'draft' => 'text-bg-secondary',
        default => 'text-bg-info',
    };
    $activeFilters = request()->hasAny(['status','client','meal_type','date']);
    $quickFilters = [
        ''              => 'All',
        'submitted'     => 'Submitted',
        'under_review'  => 'Under Review',
        'need_changes'  => 'Need Changes',
        'scheduled'     => 'Scheduled',
    ];
@endphp

<div class="container-fluid py-4">
    <div class="erm-header">
        <div class="erm-header-text">
            <div class="text-uppercase small fw-bold" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
            <h1 class="h3 mb-0 fw-bold">Event Requests</h1>
        </div>
        <div class="erm-header-actions">
            <a href="{{ route('admin.event-requests.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg me-1"></i>New Event Request</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Mobile: compact search + quick status chips + "Filters" trigger --}}
    <div class="d-md-none mb-3">
        <form method="GET" class="mb-2">
            @foreach(request()->except(['client', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Search requests..." onchange="this.form.submit()">
        </form>
        <div class="erm-chips mb-2">
            @foreach($quickFilters as $value => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null]) }}"
                   class="erm-chip {{ (request('status') ?: '') === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2" style="min-height:44px" data-bs-toggle="modal" data-bs-target="#filtersModal">
            <i class="bi bi-sliders"></i> Filters {{ $activeFilters ? '(' . collect(request()->only(['status','client','meal_type','date']))->filter()->count() . ')' : '' }}
        </button>
    </div>

    {{-- Desktop: full inline filter toolbar --}}
    <x-premium.card class="mb-4 d-none d-md-block">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All ({{ $counts->sum() }})</option>
                    @foreach(\App\Models\EventRequest::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }} ({{ $counts[$value] ?? 0 }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Client</label>
                <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Search client name">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Meal Type</label>
                <select name="meal_type" class="form-select">
                    <option value="">Any</option>
                    @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                        <option value="{{ $value }}" @selected(request('meal_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-dark">Filter</button>
            </div>
            @if($activeFilters)
                <div class="col-12">
                    <a href="{{ route('admin.event-requests.index') }}" class="small text-muted">Clear filters</a>
                </div>
            @endif
        </form>
    </x-premium.card>

    <x-premium.card>
        @if($requests->isEmpty())
            <div class="erm-empty">
                <div class="glyph"><i class="bi bi-inbox"></i></div>
                <div class="title">No event requests found.</div>
                <div class="body">{{ $activeFilters ? 'No requests match these filters.' : 'New requests will appear here once created.' }}</div>
                @if($activeFilters)
                    <a href="{{ route('admin.event-requests.index') }}" class="btn btn-outline-dark btn-sm">Clear Filters</a>
                @endif
            </div>
        @else
            {{-- Desktop table (>=768px) --}}
            <div class="erm-desktop-table">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-uppercase text-muted">
                                <th>Reference</th>
                                <th>Client</th>
                                <th>Event Date</th>
                                <th>Meal</th>
                                <th>Menu</th>
                                <th>Guests</th>
                                <th>Est. Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td class="fw-bold">{{ $req->referenceNumber() }}</td>
                                    <td>{{ $req->client_name ?: '—' }}</td>
                                    <td>{{ $req->event_date?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $req->mealTypeLabel() }}</td>
                                    <td>{{ $req->menuTypeLabel() }}</td>
                                    <td>{{ $req->guest_count ? number_format($req->guest_count) : '—' }}</td>
                                    <td>₹{{ number_format($req->estimated_total, 0) }}</td>
                                    <td><span class="badge {{ $statusChip($req->status) }}">{{ $req->statusLabel() }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.event-requests.show', $req) }}" class="btn btn-sm btn-outline-dark">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards (<768px) --}}
            <div class="erm-mobile-cards">
                @foreach($requests as $req)
                    <div class="erm-card">
                        <div class="erm-card-top">
                            <div>
                                <div class="erm-card-title">{{ $req->client_name ?: 'Untitled request' }}</div>
                                <div class="erm-card-subtitle">{{ $req->referenceNumber() }} &middot; {{ $req->event_name ?: $req->mealTypeLabel() }}</div>
                            </div>
                            <span class="badge {{ $statusChip($req->status) }} flex-shrink-0">{{ $req->statusLabel() }}</span>
                        </div>
                        <div class="erm-card-grid">
                            <div class="erm-card-field">
                                <div class="k"><i class="bi bi-calendar-event"></i> Date</div>
                                <div class="v">{{ $req->event_date?->format('d M Y') ?? '—' }}</div>
                            </div>
                            <div class="erm-card-field">
                                <div class="k"><i class="bi bi-people"></i> Guests</div>
                                <div class="v">{{ $req->guest_count ? number_format($req->guest_count) : '—' }}</div>
                            </div>
                            <div class="erm-card-field">
                                <div class="k">Menu</div>
                                <div class="v">{{ $req->menuTypeLabel() }}</div>
                            </div>
                            <div class="erm-card-field">
                                <div class="k">Est. Total</div>
                                <div class="v">₹{{ number_format($req->estimated_total, 0) }}</div>
                            </div>
                        </div>
                        <div class="erm-card-footer">
                            <span class="text-muted small">{{ $req->created_at->diffForHumans() }}</span>
                            <a href="{{ route('admin.event-requests.show', $req) }}" class="btn btn-dark btn-sm">View Request</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-premium.card>

    <div class="mt-3">{{ $requests->links() }}</div>
</div>

{{-- Mobile filters modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="GET" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All ({{ $counts->sum() }})</option>
                        @foreach(\App\Models\EventRequest::statuses() as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }} ({{ $counts[$value] ?? 0 }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Client</label>
                    <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Search client name">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Meal Type</label>
                    <select name="meal_type" class="form-select">
                        <option value="">Any</option>
                        @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(request('meal_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small fw-bold">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.event-requests.index') }}" class="btn btn-outline-dark">Reset</a>
                <button type="submit" class="btn btn-dark">Apply</button>
            </div>
        </form>
    </div>
</div>
</x-admin-layout>
