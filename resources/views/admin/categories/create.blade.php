<x-admin-layout title="Add Category">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.categories.index') }}" class="ef-back" title="Back to Categories">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Add Category</h1>
            <p class="ef-form-page-sub">Create a new expense category</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf

            <div class="ef-form-grid ef-form-grid-1">
                <div>
                    <label class="ef-label" for="name">Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name') }}" autofocus>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="ef-textarea @error('description') --error @enderror">{{ old('description') }}</textarea>
                    @error('description') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-switch">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', '1') ? 'checked' : '' }}>
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.categories.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-plus-lg"></i> Create
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
