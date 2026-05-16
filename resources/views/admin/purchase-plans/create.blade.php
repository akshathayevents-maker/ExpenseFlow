<x-admin-layout title="Create Purchase Plan">
<div class="page-header">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
        <li class="breadcrumb-item"><a href="{{ route('admin.purchase-plans.index') }}">Purchase Plans</a></li>
        <li class="breadcrumb-item active">New Plan</li>
    </ol></nav>
    <h4 class="mb-0 fw-bold">Create Purchase Plan</h4>
</div>

<form method="POST" action="{{ route('admin.purchase-plans.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            {{-- Plan details --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">Plan Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Plan Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', 'Purchase Plan — ' . now()->format('d M Y')) }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Planned Date <span class="text-danger">*</span></label>
                            <input type="date" name="planned_date" class="form-control @error('planned_date') is-invalid @enderror"
                                   value="{{ old('planned_date', now()->addDay()->toDateString()) }}" required>
                            @error('planned_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Suggested items --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-lightbulb me-1 text-warning"></i>
                    Suggested Items
                    <span class="badge bg-secondary ms-1">{{ $suggestions->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($suggestions->isEmpty())
                        <div class="text-center py-4 text-muted small p-3">
                            All stock is adequate. You can still create a plan manually.
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" class="form-check-input" checked></th>
                                    <th>Item</th>
                                    <th>Priority</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost (₹)</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suggestions as $i => $item)
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="{{ $item->id }}">
                                        <input type="checkbox" name="items[{{ $i }}][selected]" value="1"
                                               class="form-check-input item-checkbox" checked>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small">{{ $item->name }}</div>
                                        <div class="text-muted" style="font-size:.72rem">
                                            Current: {{ $item->current_stock }} / Min: {{ $item->minimum_stock }} {{ $item->unit }}
                                        </div>
                                    </td>
                                    <td>
                                        <select name="items[{{ $i }}][priority]" class="form-select form-select-sm" style="width:100px">
                                            @foreach(['urgent','high','normal','low'] as $p)
                                                <option value="{{ $p }}" {{ $item->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm" style="width:130px">
                                            <input type="number" name="items[{{ $i }}][quantity]"
                                                   class="form-control form-control-sm"
                                                   value="{{ $item->suggested_quantity }}"
                                                   min="0.001" step="0.001" required>
                                            <span class="input-group-text">{{ $item->unit }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $i }}][unit_cost]"
                                               class="form-control form-control-sm" style="width:100px"
                                               value="{{ $item->average_cost }}" min="0" step="0.01">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $i }}][notes]"
                                               class="form-control form-control-sm" placeholder="Optional">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Summary</h6>
                    <div class="text-muted small mb-2">Selected items will be added to the plan as draft for review and approval.</div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-1"></i> Create Plan
                    </button>
                    <a href="{{ route('admin.purchase-plans.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
</x-admin-layout>
