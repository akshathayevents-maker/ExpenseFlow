<x-admin-layout title="New Expense Request">
    <div class="page-header d-flex align-items-center gap-2">
        <a href="{{ route('employee.expense-requests.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">New Expense Request</h4>
            <p class="text-muted mb-0 small">Submit a new expense for approval</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('employee.expense-requests.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="row g-3">
                            {{-- Title --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" placeholder="e.g. Fuel for delivery van" autofocus>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Category --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="expense_category_id">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select id="expense_category_id" name="expense_category_id"
                                        class="form-select @error('expense_category_id') is-invalid @enderror">
                                    <option value="">Select category…</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Amount --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="amount">Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" placeholder="0.00">
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Priority --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="priority">Priority <span class="text-danger">*</span></label>
                                <select id="priority" name="priority"
                                        class="form-select @error('priority') is-invalid @enderror">
                                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('priority', 'medium') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Vendor (optional) --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="vendor_id">Vendor <span class="text-muted fw-normal">(optional)</span></label>
                                <select id="vendor_id" name="vendor_id"
                                        class="form-select @error('vendor_id') is-invalid @enderror">
                                    <option value="">No vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="notes">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea id="notes" name="notes" rows="3"
                                          class="form-control @error('notes') is-invalid @enderror"
                                          placeholder="Add any details, descriptions, or context…">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Bill upload --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Upload Bills <span class="text-muted fw-normal">(optional — max 5 files, 5MB each)</span></label>
                                <div id="dropzone" class="border-2 border-dashed rounded p-4 text-center bg-light" style="border-style:dashed!important;cursor:pointer">
                                    <i class="bi bi-cloud-upload fs-2 text-muted"></i>
                                    <p class="mb-1 text-muted">Drag & drop or click to upload</p>
                                    <small class="text-muted">JPG, PNG, PDF — max 5MB each</small>
                                    <input type="file" id="bills" name="bills[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                                           class="d-none @error('bills.*') is-invalid @enderror">
                                </div>
                                @error('bills.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('bills') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                <div id="file-preview" class="row g-2 mt-2"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Sticky footer on mobile --}}
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('employee.expense-requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" data-loading-text="Submitting…">
                                <i class="bi bi-send me-1"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

@push('scripts')
<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('bills');
    const preview  = document.getElementById('file-preview');

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('border-primary');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-primary'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('border-primary');
        fileInput.files = e.dataTransfer.files;
        renderPreviews(fileInput.files);
    });

    fileInput.addEventListener('change', () => renderPreviews(fileInput.files));

    function renderPreviews(files) {
        preview.innerHTML = '';
        if (!files.length) return;

        Array.from(files).forEach(file => {
            const col = document.createElement('div');
            col.className = 'col-6 col-sm-4 col-md-3';

            const isPdf = file.type === 'application/pdf';
            const size  = file.size < 1048576
                ? (file.size / 1024).toFixed(1) + ' KB'
                : (file.size / 1048576).toFixed(1) + ' MB';

            if (isPdf) {
                col.innerHTML = `
                    <div class="border rounded p-2 text-center bg-light" style="aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                        <small class="mt-1 text-muted text-truncate w-100">${file.name}</small>
                        <span class="badge bg-secondary mt-1" style="font-size:.65rem">${size}</span>
                    </div>`;
            } else {
                const reader = new FileReader();
                reader.onload = e => {
                    col.innerHTML = `
                        <div class="border rounded overflow-hidden position-relative" style="aspect-ratio:1">
                            <img src="${e.target.result}" class="w-100 h-100" style="object-fit:cover">
                            <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-50 text-white px-2 py-1" style="font-size:.65rem">${size}</div>
                        </div>`;
                };
                reader.readAsDataURL(file);
            }

            preview.appendChild(col);
        });
    }
</script>
@endpush
