<x-admin-layout title="New Event Request">
<div class="container-fluid py-4" style="max-width:720px">
    <a href="{{ route('admin.event-requests.index') }}" class="text-decoration-none small text-muted mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Event Requests</a>
    <h1 class="h3 fw-bold mb-1">New Event Request</h1>
    <p class="text-muted mb-4">Fill in whatever you know from the call — the client fills or edits the rest via their link.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Couldn't create the request:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-premium.card>
        <form method="POST" action="{{ route('admin.event-requests.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Client Name</label>
                    <input class="form-control @error('client_name') is-invalid @enderror" name="client_name" value="{{ old('client_name') }}">
                    @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Mobile Number</label>
                    <input class="form-control @error('client_mobile') is-invalid @enderror" name="client_mobile" maxlength="10" pattern="[0-9]{10}" title="Enter exactly 10 digits" inputmode="numeric" placeholder="10-digit number" value="{{ old('client_mobile') }}">
                    @error('client_mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">Must be exactly 10 digits, no spaces or +91.</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Event Name</label>
                    <input class="form-control @error('event_name') is-invalid @enderror" name="event_name" value="{{ old('event_name') }}">
                    @error('event_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Event Date</label>
                    <input type="date" class="form-control @error('event_date') is-invalid @enderror" name="event_date" min="{{ today()->toDateString() }}" value="{{ old('event_date') }}">
                    @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Meal Type</label>
                    <select class="form-select @error('meal_type') is-invalid @enderror" name="meal_type">
                        <option value="">Let client choose</option>
                        @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(old('meal_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('meal_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Menu Type</label>
                    <select class="form-select @error('menu_type') is-invalid @enderror" name="menu_type">
                        <option value="">Let client choose</option>
                        @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                            <option value="{{ $value }}" @selected(old('menu_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('menu_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Expected Guests</label>
                    <input type="number" min="1" class="form-control @error('guest_count') is-invalid @enderror" name="guest_count" value="{{ old('guest_count') }}">
                    @error('guest_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-dark">Create &amp; generate link</button>
            </div>
        </form>
    </x-premium.card>
</div>
</x-admin-layout>
