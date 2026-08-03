<x-admin-layout title="New Event Request">
<div class="container-fluid py-4" style="max-width:720px">
    <a href="{{ route('admin.event-requests.index') }}" class="text-decoration-none small text-muted mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Event Requests</a>
    <h1 class="h3 fw-bold mb-1">New Event Request</h1>
    <p class="text-muted mb-4">Fill in whatever you know from the call — the client fills or edits the rest via their link.</p>

    <x-premium.card>
        <form method="POST" action="{{ route('admin.event-requests.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Client Name</label>
                    <input class="form-control" name="client_name" value="{{ old('client_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Mobile Number</label>
                    <input class="form-control" name="client_mobile" maxlength="10" value="{{ old('client_mobile') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Event Name</label>
                    <input class="form-control" name="event_name" value="{{ old('event_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Event Date</label>
                    <input type="date" class="form-control" name="event_date" value="{{ old('event_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Meal Type</label>
                    <select class="form-select" name="meal_type">
                        <option value="">Let client choose</option>
                        @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(old('meal_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Menu Type</label>
                    <select class="form-select" name="menu_type">
                        <option value="">Let client choose</option>
                        @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(old('menu_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Expected Guests</label>
                    <input type="number" min="1" class="form-control" name="guest_count" value="{{ old('guest_count') }}">
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-dark">Create &amp; generate link</button>
            </div>
        </form>
    </x-premium.card>
</div>
</x-admin-layout>
