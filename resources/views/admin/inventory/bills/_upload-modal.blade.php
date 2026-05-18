<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--ef-radius);border:1px solid var(--ef-border);box-shadow:0 8px 32px rgba(0,0,0,.18)">
            <div class="modal-header" style="border-bottom:1px solid var(--ef-border);padding:1.1rem 1.4rem">
                <h5 class="modal-title" style="font-size:1rem;font-weight:720;color:var(--ef-ink)">
                    <i class="bi bi-cloud-upload me-2"></i>Upload Bill / Invoice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.bills.store') }}" enctype="multipart/form-data" id="billUploadForm">
                @csrf
                <div class="modal-body" style="padding:1.4rem">
                    {{-- Drop zone --}}
                    <div id="dropZone"
                         style="border:2px dashed var(--ef-border-strong);border-radius:var(--ef-radius);text-align:center;padding:2.5rem 1rem;margin-bottom:1rem;position:relative;cursor:pointer;transition:background .2s">
                        <input type="file" name="bill_file" id="billFileInput"
                               style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer"
                               accept=".jpg,.jpeg,.png,.pdf"
                               capture="environment">
                        <div id="dropZoneContent">
                            <i class="bi bi-file-earmark-image" style="font-size:2.5rem;color:var(--ef-faint);display:block;margin-bottom:.75rem"></i>
                            <div style="font-weight:680;color:var(--ef-ink-2)">Drag &amp; drop or click to select</div>
                            <div style="color:var(--ef-faint);font-size:.82rem;margin-top:.4rem">JPG, PNG, or PDF — max 10 MB</div>
                            <div style="color:var(--ef-faint);font-size:.82rem;margin-top:.25rem">
                                <i class="bi bi-camera me-1"></i>Mobile camera supported
                            </div>
                        </div>
                        <div id="dropZonePreview" class="d-none">
                            <img id="imgPreview" src="" alt="Preview" style="max-height:200px;object-fit:contain;border-radius:6px;margin-bottom:.5rem">
                            <div id="pdfPreviewIcon" class="d-none" style="margin-bottom:.5rem">
                                <i class="bi bi-file-earmark-pdf" style="font-size:3rem;color:var(--ef-danger)"></i>
                            </div>
                            <div style="font-weight:680;color:var(--ef-ink-2)" id="selectedFileName"></div>
                            <div style="color:var(--ef-faint);font-size:.82rem" id="selectedFileSize"></div>
                            <button type="button" class="ef-btn" style="margin-top:.5rem" id="btnClearFile">
                                <i class="bi bi-x"></i> Choose different file
                            </button>
                        </div>
                    </div>

                    @error('bill_file')
                        <div style="background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.2);border-radius:var(--ef-radius);padding:8px 14px;margin-bottom:12px;font-size:.86rem;color:var(--ef-danger)">
                            {{ $message }}
                        </div>
                    @enderror

                    <div style="margin-bottom:1rem">
                        <label class="ef-label">Notes (optional)</label>
                        <input type="text" name="notes" class="ef-input" maxlength="500"
                               placeholder="e.g. Weekly grocery purchase…">
                    </div>

                    <div style="background:rgba(13,148,136,.06);border:1px solid rgba(13,148,136,.2);border-radius:var(--ef-radius);padding:8px 14px;font-size:.82rem;color:var(--ef-teal)">
                        <i class="bi bi-magic me-1"></i>
                        <strong>Smart extraction:</strong> Item details are automatically extracted using OCR.
                        You can review and correct them before importing to inventory.
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border);padding:.9rem 1.4rem;gap:.5rem">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark" id="btnUploadSubmit" disabled
                            data-loading-text="Uploading &amp; extracting…">
                        <i class="bi bi-magic"></i> Upload &amp; Extract
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
        dropZone.style.background = 'rgba(15,123,95,.04)';
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
            dropZone.style.background = 'rgba(59,130,246,.05)';
        });
    });
    ['dragleave', 'dragend', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            if (evt === 'drop') {
                const file = e.dataTransfer.files[0];
                if (file) {
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
