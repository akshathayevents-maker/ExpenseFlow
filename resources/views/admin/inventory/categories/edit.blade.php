<x-admin-layout title="Edit Category">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.inventory.categories.index') }}" class="ef-back" title="Back to Inventory Categories">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Edit Category</h1>
            <p class="ef-form-page-sub">{{ $category->name }}</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.inventory.categories.update', $category) }}">
            @csrf @method('PUT')

            <div class="ef-form-grid ef-form-grid-1">
                <div>
                    <label class="ef-label" for="name">Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name', $category->name) }}" required>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="ef-textarea">{{ old('description', $category->description) }}</textarea>
                </div>

                <div>
                    <label class="ef-switch">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.inventory.categories.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-floppy"></i> Save Changes
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
