<x-admin-layout title="Add Vendor">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.vendors.index') }}" class="ef-back" title="Back to Vendors">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Add Vendor</h1>
            <p class="ef-form-page-sub">Register a new vendor or supplier</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.vendors.store') }}">
            @csrf

            <div class="ef-form-grid ef-form-grid-2">
                <div>
                    <label class="ef-label" for="name">Vendor Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name') }}" autofocus>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           class="ef-input @error('phone') --error @enderror"
                           value="{{ old('phone') }}">
                    @error('phone') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="address">Address</label>
                    <textarea id="address" name="address" rows="2"
                              class="ef-textarea @error('address') --error @enderror">{{ old('address') }}</textarea>
                    @error('address') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="ef-textarea @error('notes') --error @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-switch">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', '1') ? 'checked' : '' }}>
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.vendors.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-plus-lg"></i> Create Vendor
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
