<x-admin-layout title="Add Inventory Item">
<div class="page-header">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.items.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Add Item</li>
    </ol></nav>
    <h4 class="mb-0 fw-bold">Add Inventory Item</h4>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.inventory.items.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SKU</label>
                            <input type="text" name="sku" class="form-control font-monospace @error('sku') is-invalid @enderror"
                                   value="{{ old('sku') }}" placeholder="Optional">
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="inventory_category_id" class="form-select @error('inventory_category_id') is-invalid @enderror" required>
                                <option value="">Select category…</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('inventory_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('inventory_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                <option value="">Select unit…</option>
                                @foreach(\App\Models\InventoryItem::$units as $val => $label)
                                    <option value="{{ $val }}" {{ old('unit') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Min Stock <span class="text-danger">*</span></label>
                            <input type="number" name="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror"
                                   value="{{ old('minimum_stock', 0) }}" min="0" step="0.001" required>
                            @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Max Stock</label>
                            <input type="number" name="maximum_stock" class="form-control @error('maximum_stock') is-invalid @enderror"
                                   value="{{ old('maximum_stock') }}" min="0" step="0.001" placeholder="Optional">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Avg Cost (₹)</label>
                            <input type="number" name="average_cost" class="form-control @error('average_cost') is-invalid @enderror"
                                   value="{{ old('average_cost') }}" min="0" step="0.01" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Add Item</button>
                        <a href="{{ route('admin.inventory.items.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
