<x-admin-layout title="Event Requests">
<x-event-request.admin-responsive-styles />
@php
    $statusBadgeClass = fn ($status) => match($status) {
        'submitted'    => 'is-neutral',
        'under_review' => 'is-amber',
        'resubmitted'  => 'is-amber',
        'need_changes' => 'is-orange',
        'scheduled'    => 'is-green',
        'approved'     => 'is-green',
        'rejected'     => 'is-red',
        default        => 'is-gray',
    };
    $activeFilters = request()->hasAny(['status','client','meal_type','menu_type','date']);
    $activeFilterCount = collect(request()->only(['status','client','meal_type','menu_type','date']))->filter()->count();
    $quickFilters = [
        ''              => 'All',
        'submitted'     => 'Submitted',
        'under_review'  => 'Under Review',
        'need_changes'  => 'Need Changes',
        'scheduled'     => 'Scheduled',
        'rejected'      => 'Rejected',
    ];
    $searchPlaceholder = 'Search name, phone, request # or event';
@endphp

<div class="container-fluid py-4">
    <div class="erm-req-header mb-3">
        <div class="text-uppercase small fw-bold d-none d-md-block" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
        <div class="erm-req-header-row">
            <h1 class="h3 mb-0 fw-bold">Event Requests</h1>
            <a href="{{ route('admin.event-requests.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-lg me-1"></i><span class="d-md-none">New</span><span class="d-none d-md-inline">New Event Request</span>
            </a>
        </div>
        <div class="erm-req-subtitle d-md-none">Manage incoming event enquiries</div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Mobile: compact search + filter icon, scrollable status chips --}}
    <div class="d-md-none mb-3">
        <form method="GET" class="erm-req-search-row mb-2">
            @foreach(request()->except(['client', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="erm-req-search-wrap">
                <i class="bi bi-search erm-req-search-icon"></i>
                <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="{{ $searchPlaceholder }}" aria-label="Search event requests" onchange="this.form.submit()">
            </div>
            <button type="button" class="btn btn-outline-dark erm-req-filter-btn" data-bs-toggle="modal" data-bs-target="#filtersModal" aria-label="Filters{{ $activeFilterCount ? ', '.$activeFilterCount.' active' : '' }}">
                <i class="bi bi-sliders"></i>
                @if($activeFilterCount)<span class="erm-req-filter-dot" aria-hidden="true"></span>@endif
            </button>
        </form>
        <div class="erm-req-chip-scroll" role="group" aria-label="Filter by status">
            @foreach($quickFilters as $value => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null]) }}"
                   class="erm-chip {{ (request('status') ?: '') === $value ? 'active' : '' }}"
                   @if((request('status') ?: '') === $value) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Desktop: full inline filter toolbar --}}
    <x-premium.card class="mb-3 d-none d-md-block">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All ({{ $counts->sum() }})</option>
                    @foreach(\App\Models\EventRequest::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }} ({{ $counts[$value] ?? 0 }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small fw-bold">Search</label>
                <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="{{ $searchPlaceholder }}">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-bold">Meal Type</label>
                <select name="meal_type" class="form-select">
                    <option value="">Any</option>
                    @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                        <option value="{{ $value }}" @selected(request('meal_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-bold">Menu</label>
                <select name="menu_type" class="form-select">
                    <option value="">Any</option>
                    @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                        <option value="{{ $value }}" @selected(request('menu_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-bold">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-lg-1 col-md-4 d-grid">
                <button class="btn btn-outline-dark">Filter</button>
            </div>
            @if($activeFilters)
                <div class="col-12">
                    <a href="{{ route('admin.event-requests.index') }}" class="small text-muted">Clear filters</a>
                </div>
            @endif
        </form>
    </x-premium.card>

    <div class="erm-req-count">
        {{ $requests->total() }} {{ Str::plural('Event Request', $requests->total()) }}
    </div>

    <x-premium.card>
        @if($requests->isEmpty())
            <div class="erm-empty">
                <div class="glyph"><i class="bi bi-inbox"></i></div>
                @if($activeFilters)
                    <div class="title">No event requests found</div>
                    <div class="body">Try changing your filters or search.</div>
                    <a href="{{ route('admin.event-requests.index') }}" class="btn btn-outline-dark btn-sm">Clear Filters</a>
                @else
                    <div class="title">No event requests yet</div>
                    <div class="body">Create your first request to get started.</div>
                    <a href="{{ route('admin.event-requests.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg me-1"></i>New Event Request</a>
                @endif
            </div>
        @else
            {{-- Desktop table (>=768px) --}}
            <div class="erm-desktop-table">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-uppercase text-muted">
                                <th>Customer</th>
                                <th>Request</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Guests</th>
                                <th>Menu</th>
                                <th>Estimated Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td class="erm-req-table-client">
                                        <div class="name">{{ $req->client_name ?: 'Untitled request' }}</div>
                                        @if($req->client_mobile)
                                            <div class="sub">{{ $req->client_mobile }}</div>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $req->referenceNumber() }}</td>
                                    <td>{{ $req->event_name ?: $req->mealTypeLabel() }}</td>
                                    <td>{{ $req->event_date?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $req->guest_count ? number_format($req->guest_count) : '—' }}</td>
                                    <td>{{ $req->menuTypeLabel() }}</td>
                                    <td class="fw-bold">₹{{ number_format($req->estimated_total, 0) }}</td>
                                    <td><span class="erm-req-badge {{ $statusBadgeClass($req->status) }}">{{ $req->statusLabel() }}</span></td>
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
                    <div class="erm-card erm-req-card">
                        <a href="{{ route('admin.event-requests.show', $req) }}" class="erm-req-card-hit" aria-label="View request {{ $req->referenceNumber() }} for {{ $req->client_name ?: 'untitled request' }}, status {{ $req->statusLabel() }}"></a>
                        <div class="erm-req-card-top">
                            <div class="erm-req-card-name">{{ $req->client_name ?: 'Untitled request' }}</div>
                            <span class="erm-req-badge {{ $statusBadgeClass($req->status) }} flex-shrink-0">{{ $req->statusLabel() }}</span>
                        </div>
                        <div class="erm-req-card-sub">{{ $req->referenceNumber() }} &middot; {{ $req->event_name ?: $req->mealTypeLabel() }}</div>
                        <div class="erm-req-card-meta">
                            <span class="item"><i class="bi bi-calendar-event"></i>{{ $req->event_date?->format('d M Y') ?? '—' }}</span>
                            <span class="item"><i class="bi bi-people"></i>{{ $req->guest_count ? number_format($req->guest_count) : '—' }}</span>
                            <span class="item"><i class="bi bi-egg-fried"></i>{{ $req->menuTypeLabel() }}</span>
                        </div>
                        <div class="erm-req-card-money">
                            <span class="label">Estimated Total</span>
                            <span class="value">₹{{ number_format($req->estimated_total, 0) }}</span>
                        </div>
                        <div class="erm-req-card-footer">
                            <span class="time">{{ $req->created_at->diffForHumans() }}</span>
                            <span class="erm-req-card-link" aria-hidden="true">View Request <i class="bi bi-arrow-right"></i></span>
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
                    <label class="form-label small fw-bold">Meal Type</label>
                    <select name="meal_type" class="form-select">
                        <option value="">Any</option>
                        @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(request('meal_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Menu</label>
                    <select name="menu_type" class="form-select">
                        <option value="">Any</option>
                        @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(request('menu_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small fw-bold">Event Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                @foreach(request()->except(['status','meal_type','menu_type','date','page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </div>
            <div class="modal-footer">
                <a href="{{ request()->fullUrlWithQuery(['status' => null, 'meal_type' => null, 'menu_type' => null, 'date' => null, 'page' => null]) }}" class="btn btn-outline-dark">Reset</a>
                <button type="submit" class="btn btn-dark">Apply</button>
            </div>
        </form>
    </div>
</div>
</x-admin-layout>
