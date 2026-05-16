<x-admin-layout title="Edit Meal Plan">

<div class="page-header d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('hall.meal-plans.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-bold">Edit Meal Plan</h5>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-3 mb-3">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="max-width:560px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('hall.meal-plans.update', $mealPlan) }}">
        @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold small">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                       value="{{ old('name', $mealPlan->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                <select name="category" class="form-select rounded-3" required>
                    @foreach(\App\Models\MealPlan::categories() as $v => $l)
                        <option value="{{ $v }}" {{ old('category', $mealPlan->category) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Price per Person (₹) <span class="text-danger">*</span></label>
                <input type="number" name="price_per_person" step="0.01" min="0" class="form-control rounded-3"
                       value="{{ old('price_per_person', $mealPlan->price_per_person) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Description</label>
                <textarea name="description" rows="3" class="form-control rounded-3">{{ old('description', $mealPlan->description) }}</textarea>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $mealPlan->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="is_active">Active</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3 px-4">
                    <i class="bi bi-check-circle me-1"></i>Update Plan
                </button>
                <a href="{{ route('hall.meal-plans.index') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
            </div>
        </form>
    </div>
</div>

</x-admin-layout>
