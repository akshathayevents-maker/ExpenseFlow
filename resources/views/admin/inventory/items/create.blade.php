<x-admin-layout title="Add Inventory Item">

<div style="max-width:700px;margin:0 auto">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.inventory.items.index') }}" class="ef-back" title="Back to Inventory">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Add Inventory Item</h1>
            <p class="ef-form-page-sub">Register a new item to track stock levels</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.inventory.items.store') }}">
            @csrf

            <div class="ef-form-grid" style="grid-template-columns:1fr 1fr;gap:16px">
                <div style="grid-column:1/span 1;grid-column-end:span 2">
                    <label class="ef-label" for="name">Item Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name') }}" required>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="sku">SKU</label>
                    <input type="text" id="sku" name="sku"
                           class="ef-input @error('sku') --error @enderror"
                           value="{{ old('sku') }}" placeholder="Optional" style="font-family:monospace">
                    @error('sku') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="status">Status</label>
                    <select id="status" name="status" class="ef-select">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="ef-label" for="inventory_category_id">Category <span style="color:var(--ef-danger)">*</span></label>
                    <select id="inventory_category_id" name="inventory_category_id"
                            class="ef-select @error('inventory_category_id') --error @enderror" required>
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('inventory_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('inventory_category_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="unit">Unit <span style="color:var(--ef-danger)">*</span></label>
                    <select id="unit" name="unit" class="ef-select @error('unit') --error @enderror" required>
                        <option value="">Select unit…</option>
                        @foreach(\App\Models\InventoryItem::$units as $val => $label)
                            <option value="{{ $val }}" {{ old('unit') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('unit') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="minimum_stock">Min Stock <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" id="minimum_stock" name="minimum_stock"
                           class="ef-input @error('minimum_stock') --error @enderror"
                           value="{{ old('minimum_stock', 0) }}" min="0" step="0.001" required>
                    @error('minimum_stock') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="maximum_stock">Max Stock</label>
                    <input type="number" id="maximum_stock" name="maximum_stock"
                           class="ef-input @error('maximum_stock') --error @enderror"
                           value="{{ old('maximum_stock') }}" min="0" step="0.001" placeholder="Optional">
                </div>

                <div>
                    <label class="ef-label" for="average_cost">Avg Cost (₹)</label>
                    <input type="number" id="average_cost" name="average_cost"
                           class="ef-input @error('average_cost') --error @enderror"
                           value="{{ old('average_cost') }}" min="0" step="0.01" placeholder="Optional">
                </div>

                <div style="grid-column:1/span 2">
                    <label class="ef-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="2" class="ef-textarea">{{ old('description') }}</textarea>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.inventory.items.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-plus-lg"></i> Add Item
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
