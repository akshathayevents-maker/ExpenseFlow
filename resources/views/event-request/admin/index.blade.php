<x-admin-layout title="Event Requests">
@php
    $statusChip = fn ($status) => match($status) {
        'rejected' => 'text-bg-danger',
        'scheduled' => 'text-bg-success',
        'need_changes' => 'text-bg-warning',
        'draft' => 'text-bg-secondary',
        default => 'text-bg-info',
    };
@endphp

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <div class="text-uppercase small fw-bold" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
            <h1 class="h3 mb-0 fw-bold">Event Requests</h1>
        </div>
        <a href="{{ route('admin.event-requests.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg me-1"></i>New Event Request</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <x-premium.card class="mb-4">
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
        </form>
    </x-premium.card>

    <x-premium.card>
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
                    @forelse($requests as $req)
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
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No event requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-premium.card>

    <div class="mt-3">{{ $requests->links() }}</div>
</div>
</x-admin-layout>
