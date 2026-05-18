<x-admin-layout title="Edit {{ $item->name }}">

<div style="max-width:700px;margin:0 auto">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.inventory.items.show', $item) }}" class="ef-back" title="Back to Item">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Edit Item</h1>
            <p class="ef-form-page-sub">{{ $item->name }}</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.inventory.items.update', $item) }}">
            @csrf @method('PUT')

            <div class="ef-form-grid" style="grid-template-columns:1fr 1fr;gap:16px">
                <div style="grid-column:1/span 2">
                    <label class="ef-label" for="name">Item Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name', $item->name) }}" required>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="sku">SKU</label>
                    <input type="text" id="sku" name="sku"
                           class="ef-input"
                           value="{{ old('sku', $item->sku) }}" style="font-family:monospace">
                </div>

                <div>
                    <label class="ef-label" for="status">Status</label>
                    <select id="status" name="status" class="ef-select" required>
                        <option value="active"   {{ old('status', $item->status) === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $item->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="ef-label" for="inventory_category_id">Category <span style="color:var(--ef-danger)">*</span></label>
                    <select id="inventory_category_id" name="inventory_category_id"
                            class="ef-select @error('inventory_category_id') --error @enderror" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('inventory_category_id', $item->inventory_category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('inventory_category_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="unit">Unit <span style="color:var(--ef-danger)">*</span></label>
                    <select id="unit" name="unit" class="ef-select @error('unit') --error @enderror" required>
                        @foreach(\App\Models\InventoryItem::$units as $val => $label)
                            <option value="{{ $val }}" {{ old('unit', $item->unit) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('unit') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="minimum_stock">Min Stock <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" id="minimum_stock" name="minimum_stock"
                           class="ef-input @error('minimum_stock') --error @enderror"
                           value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0" step="0.001" required>
                    @error('minimum_stock') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="maximum_stock">Max Stock</label>
                    <input type="number" id="maximum_stock" name="maximum_stock"
                           class="ef-input"
                           value="{{ old('maximum_stock', $item->maximum_stock) }}" min="0" step="0.001">
                </div>

                <div>
                    <label class="ef-label" for="average_cost">Avg Cost (₹)</label>
                    <input type="number" id="average_cost" name="average_cost"
                           class="ef-input"
                           value="{{ old('average_cost', $item->average_cost) }}" min="0" step="0.01">
                </div>

                <div style="grid-column:1/span 2">
                    <label class="ef-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="2" class="ef-textarea">{{ old('description', $item->description) }}</textarea>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.inventory.items.show', $item) }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-floppy"></i> Save Changes
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
