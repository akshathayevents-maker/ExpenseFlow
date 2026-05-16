<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload Bill / Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.bills.store') }}" enctype="multipart/form-data" id="billUploadForm">
                @csrf
                <div class="modal-body">
                    {{-- Drop zone --}}
                    <div id="dropZone"
                         class="border-2 border-dashed rounded-3 text-center p-5 mb-3 position-relative"
                         style="border: 2px dashed #dee2e6; cursor: pointer; transition: background .2s;">
                        <input type="file" name="bill_file" id="billFileInput"
                               class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                               style="cursor:pointer"
                               accept=".jpg,.jpeg,.png,.pdf"
                               capture="environment">
                        <div id="dropZoneContent">
                            <i class="bi bi-file-earmark-image fs-1 text-muted d-block mb-2"></i>
                            <div class="fw-semibold">Drag &amp; drop or click to select</div>
                            <div class="text-muted small mt-1">JPG, PNG, or PDF — max 10 MB</div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-camera me-1"></i>Mobile camera supported
                            </div>
                        </div>
                        <div id="dropZonePreview" class="d-none">
                            <img id="imgPreview" src="" alt="Preview" class="img-fluid rounded shadow-sm mb-2" style="max-height:200px">
                            <div id="pdfPreviewIcon" class="d-none mb-2">
                                <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                            </div>
                            <div class="fw-semibold" id="selectedFileName"></div>
                            <div class="text-muted small" id="selectedFileSize"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnClearFile">
                                <i class="bi bi-x me-1"></i>Choose different file
                            </button>
                        </div>
                    </div>

                    @error('bill_file')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control" maxlength="500"
                               placeholder="e.g. Weekly grocery purchase…">
                    </div>

                    <div class="alert alert-info py-2 mb-0 small">
                        <i class="bi bi-magic me-1"></i>
                        <strong>Smart extraction:</strong> Item details are automatically extracted using OCR.
                        You can review and correct them before importing to inventory.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnUploadSubmit" disabled
                            data-loading-text="Uploading &amp; extracting…">
                        <i class="bi bi-magic me-1"></i>Upload &amp; Extract
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const input       = document.getElementById('billFileInput');
    const dropZone    = document.getElementById('dropZone');
    const content     = document.getElementById('dropZoneContent');
    const preview     = document.getElementById('dropZonePreview');
    const imgPrev     = document.getElementById('imgPreview');
    const pdfIcon     = document.getElementById('pdfPreviewIcon');
    const fileName    = document.getElementById('selectedFileName');
    const fileSize    = document.getElementById('selectedFileSize');
    const clearBtn    = document.getElementById('btnClearFile');
    const submitBtn   = document.getElementById('btnUploadSubmit');

    function fmtBytes(bytes) {
        return bytes < 1024 * 1024
            ? (bytes / 1024).toFixed(1) + ' KB'
            : (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function handleFile(file) {
        if (!file) return;
        const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!allowed.includes(file.type)) {
            alert('Unsupported file type. Please upload a JPG, PNG, or PDF.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('File too large. Maximum size is 10 MB.');
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = fmtBytes(file.size);

        if (file.type === 'application/pdf') {
            imgPrev.classList.add('d-none');
            pdfIcon.classList.remove('d-none');
        } else {
            pdfIcon.classList.add('d-none');
            imgPrev.classList.remove('d-none');
            const reader = new FileReader();
            reader.onload = e => { imgPrev.src = e.target.result; };
            reader.readAsDataURL(file);
        }

        content.classList.add('d-none');
        preview.classList.remove('d-none');
        dropZone.style.background = '#f0fff4';
        submitBtn.disabled = false;
    }

    input.addEventListener('change', () => handleFile(input.files[0]));

    clearBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        input.value = '';
        imgPrev.src = '';
        content.classList.remove('d-none');
        preview.classList.add('d-none');
        dropZone.style.background = '';
        submitBtn.disabled = true;
    });

    ['dragover', 'dragenter'].forEach(evt => {
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            dropZone.style.background = '#e8f4fd';
        });
    });
    ['dragleave', 'dragend', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            if (evt === 'drop') {
                const file = e.dataTransfer.files[0];
                if (file) {
                    // Assign to input via DataTransfer
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    handleFile(file);
                } else {
                    dropZone.style.background = '';
                }
            } else {
                if (!input.files.length) dropZone.style.background = '';
            }
        });
    });
})();
</script>
@endpush
