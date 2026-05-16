<x-admin-layout title="Edit {{ $item->name }}">
<div class="page-header">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.items.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.items.show', $item) }}">{{ $item->name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
    <h4 class="mb-0 fw-bold">Edit: {{ $item->name }}</h4>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.inventory.items.update', $item) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $item->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SKU</label>
                            <input type="text" name="sku" class="form-control font-monospace"
                                   value="{{ old('sku', $item->sku) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="inventory_category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('inventory_category_id', $item->inventory_category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                @foreach(\App\Models\InventoryItem::$units as $val => $label)
                                    <option value="{{ $val }}" {{ old('unit', $item->unit) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Min Stock <span class="text-danger">*</span></label>
                            <input type="number" name="minimum_stock" class="form-control"
                                   value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0" step="0.001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Max Stock</label>
                            <input type="number" name="maximum_stock" class="form-control"
                                   value="{{ old('maximum_stock', $item->maximum_stock) }}" min="0" step="0.001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Avg Cost (₹)</label>
                            <input type="number" name="average_cost" class="form-control"
                                   value="{{ old('average_cost', $item->average_cost) }}" min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active"   {{ old('status', $item->status) === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $item->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('admin.inventory.items.show', $item) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
