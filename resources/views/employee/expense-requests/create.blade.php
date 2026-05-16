<x-admin-layout title="Send Payment Request">

<style>
/* ── Mobile-first overrides for this page only ─────────────────── */
.pay-card {
    max-width: 480px;
    margin: 0 auto;
    border-radius: 20px;
    border: none;
    box-shadow: 0 4px 24px rgba(0,0,0,.10);
}

/* Larger inputs for thumb use */
.pay-card .form-control,
.pay-card .form-select {
    font-size: 1rem;
    padding: .75rem 1rem;
    border-radius: 12px;
    border-color: #e2e8f0;
}
.pay-card .form-control:focus {
    border-color: #25D366;
    box-shadow: 0 0 0 3px rgba(37,211,102,.15);
}
.pay-card .form-label {
    font-weight: 600;
    font-size: .85rem;
    color: #475569;
    margin-bottom: .4rem;
    letter-spacing: .02em;
    text-transform: uppercase;
}

/* Amount field */
.amount-wrap .input-group-text {
    font-size: 1.25rem;
    font-weight: 700;
    background: #f8fafc;
    border-color: #e2e8f0;
    border-radius: 12px 0 0 12px;
    color: #1e293b;
    padding: .75rem 1rem;
}
.amount-wrap input {
    font-size: 1.5rem !important;
    font-weight: 700;
    color: #1e293b;
    border-radius: 0 12px 12px 0 !important;
}

/* QR upload zone */
.qr-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    background: #f8fafc;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: 1.5rem;
}
.qr-zone:hover,
.qr-zone.drag-over {
    border-color: #25D366;
    background: #f0fdf4;
}
.qr-zone.has-file {
    border-style: solid;
    border-color: #25D366;
    background: #f0fdf4;
}
.qr-preview {
    width: 120px;
    height: 120px;
    object-fit: contain;
    border-radius: 10px;
}

/* CTA button */
.btn-send {
    background: #25D366;
    border: none;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 700;
    border-radius: 14px;
    padding: .9rem 1.5rem;
    width: 100%;
    letter-spacing: .03em;
    transition: background .2s, transform .1s;
    position: relative;
}
.btn-send:hover:not(:disabled) { background: #1ebe5d; transform: translateY(-1px); }
.btn-send:active:not(:disabled) { transform: translateY(0); }
.btn-send:disabled { background: #86efac; cursor: not-allowed; }
.btn-send .spinner-border { width: 1.1rem; height: 1.1rem; border-width: 2px; }

/* Sticky footer on mobile */
@media (max-width: 575px) {
    .sticky-cta {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: #fff;
        padding: 1rem 1.25rem calc(1rem + env(safe-area-inset-bottom));
        box-shadow: 0 -4px 20px rgba(0,0,0,.10);
        z-index: 1020;
    }
    .form-bottom-spacer { height: 100px; }
}
@media (min-width: 576px) {
    .sticky-cta { padding-top: 1.5rem; }
}

/* Page header */
.pay-header {
    text-align: center;
    padding: 1.5rem 0 1rem;
}
.pay-header .wa-icon {
    width: 52px; height: 52px;
    background: #25D366;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin-bottom: .75rem;
    color: #fff;
    box-shadow: 0 4px 12px rgba(37,211,102,.35);
}
.pay-header h4 { font-weight: 800; color: #1e293b; margin-bottom: .2rem; }
.pay-header p  { color: #64748b; font-size: .9rem; }
</style>

{{-- Page header --}}
<div class="pay-header">
    <div class="wa-icon"><i class="bi bi-whatsapp"></i></div>
    <h4>Send Payment Request</h4>
    <p>Fill in the details and attach your payment QR</p>
</div>

@if ($errors->any())
    <div class="pay-card card mx-auto mb-3 px-4 py-3">
        <div class="alert alert-danger mb-0 py-2 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST"
      action="{{ route('employee.expense-requests.store') }}"
      enctype="multipart/form-data"
      id="payForm"
      novalidate>
    @csrf

    <div class="pay-card card mb-0">
        <div class="card-body p-4 pb-3">
            <div class="d-flex flex-column gap-4">

                {{-- Title ------------------------------------------------- --}}
                <div>
                    <label class="form-label" for="title">
                        What is this payment for?
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="e.g. Grocery supplies, Fuel, Cleaning material…"
                           autocomplete="off"
                           autofocus
                           required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Amount ------------------------------------------------- --}}
                <div>
                    <label class="form-label" for="amount">Amount</label>
                    <div class="input-group amount-wrap">
                        <span class="input-group-text">₹</span>
                        <input type="number"
                               id="amount"
                               name="amount"
                               step="0.01"
                               min="0.01"
                               inputmode="decimal"
                               class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount') }}"
                               placeholder="0.00"
                               required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- QR Upload ---------------------------------------------- --}}
                <div>
                    <label class="form-label">Payment QR Code <span class="text-danger">*</span></label>

                    <div id="qrZone" class="qr-zone @error('qr') border-danger @enderror"
                         role="button"
                         aria-label="Upload QR code image"
                         tabindex="0">
                        <div id="qrPlaceholder">
                            <i class="bi bi-qr-code fs-1 text-muted d-block text-center mb-1"></i>
                            <div class="text-center text-muted small">
                                <span class="fw-semibold text-dark">Tap to upload QR</span><br>
                                or drag &amp; drop here<br>
                                <span class="d-none d-sm-inline">JPG · PNG · PDF — max 10 MB</span>
                            </div>
                            {{-- Camera capture on mobile --}}
                            <div class="text-center mt-2">
                                <button type="button" id="btnCamera"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-camera me-1"></i>Take Photo
                                </button>
                            </div>
                        </div>

                        {{-- Preview after file chosen --}}
                        <div id="qrPreviewWrap" class="d-none text-center">
                            <img id="qrPreviewImg" class="qr-preview d-none" src="" alt="QR preview">
                            <div id="qrPreviewPdf" class="d-none">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size:3rem"></i>
                            </div>
                            <p id="qrFileName" class="small text-muted mt-2 mb-0"></p>
                            <button type="button" id="btnChangeQr"
                                    class="btn btn-sm btn-outline-secondary mt-2 rounded-pill px-3">
                                <i class="bi bi-arrow-repeat me-1"></i>Change
                            </button>
                        </div>
                    </div>

                    {{-- Hidden file inputs: one for gallery/files, one for camera --}}
                    <input type="file" id="qrInput"       name="qr" accept="image/*,application/pdf" class="d-none">
                    <input type="file" id="qrInputCamera" name="qr" accept="image/*" capture="environment" class="d-none">

                    @error('qr')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Notes -------------------------------------------------- --}}
                <div>
                    <label class="form-label" for="notes">
                        Note <span class="text-muted fw-normal" style="text-transform:none">(optional)</span>
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="2"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Any extra details for the manager…">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>{{-- /gap-4 --}}

            {{-- Bottom spacer so sticky button doesn't cover content on mobile --}}
            <div class="form-bottom-spacer"></div>
        </div>
    </div>

    {{-- Sticky CTA --------------------------------------------------------- --}}
    <div class="sticky-cta">
        <button type="submit" id="btnSubmit" class="btn-send">
            <span id="btnNormal">
                <i class="bi bi-whatsapp me-2"></i>Send Payment Request
            </span>
            <span id="btnLoading" class="d-none">
                <span class="spinner-border me-2" role="status" aria-hidden="true"></span>
                Sending…
            </span>
        </button>
    </div>

</form>

@push('scripts')
<script>
(function () {
    'use strict';

    // ── QR upload logic ──────────────────────────────────────────────────────
    const zone         = document.getElementById('qrZone');
    const qrInput      = document.getElementById('qrInput');
    const cameraInput  = document.getElementById('qrInputCamera');
    const btnCamera    = document.getElementById('btnCamera');
    const placeholder  = document.getElementById('qrPlaceholder');
    const previewWrap  = document.getElementById('qrPreviewWrap');
    const previewImg   = document.getElementById('qrPreviewImg');
    const previewPdf   = document.getElementById('qrPreviewPdf');
    const fileName     = document.getElementById('qrFileName');
    const btnChange    = document.getElementById('btnChangeQr');
    const form         = document.getElementById('payForm');
    const btnSubmit    = document.getElementById('btnSubmit');
    const btnNormal    = document.getElementById('btnNormal');
    const btnLoading   = document.getElementById('btnLoading');

    // Tap zone → open file picker
    zone.addEventListener('click', function (e) {
        if (e.target === btnCamera || btnCamera.contains(e.target)) return;
        if (e.target === btnChange || btnChange.contains(e.target)) return;
        qrInput.click();
    });

    // Keyboard accessible
    zone.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); qrInput.click(); }
    });

    // Camera button → trigger camera capture
    btnCamera.addEventListener('click', e => { e.stopPropagation(); cameraInput.click(); });

    // Change button → reopen picker
    btnChange.addEventListener('click', e => { e.stopPropagation(); qrInput.click(); });

    // Drag & drop
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const dt = e.dataTransfer;
        if (dt.files && dt.files.length) handleFile(dt.files[0], qrInput);
    });

    qrInput.addEventListener('change', () => {
        if (qrInput.files[0]) {
            handleFile(qrInput.files[0], qrInput);
            // Disable camera input so only one is submitted
            cameraInput.removeAttribute('name');
        }
    });

    cameraInput.addEventListener('change', () => {
        if (cameraInput.files[0]) {
            handleFile(cameraInput.files[0], cameraInput);
            // Make camera input the submitted file
            qrInput.removeAttribute('name');
            cameraInput.setAttribute('name', 'qr');
        }
    });

    function handleFile(file, sourceInput) {
        const maxBytes = 10 * 1024 * 1024;
        if (file.size > maxBytes) {
            alert('File is too large. Maximum size is 10 MB.');
            sourceInput.value = '';
            return;
        }

        const isPdf = file.type === 'application/pdf';
        fileName.textContent = file.name + ' (' + humanSize(file.size) + ')';

        if (isPdf) {
            previewImg.classList.add('d-none');
            previewPdf.classList.remove('d-none');
        } else {
            const reader = new FileReader();
            reader.onload = ev => {
                previewImg.src = ev.target.result;
                previewImg.classList.remove('d-none');
                previewPdf.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }

        placeholder.classList.add('d-none');
        previewWrap.classList.remove('d-none');
        zone.classList.add('has-file');
    }

    function humanSize(bytes) {
        return bytes < 1048576
            ? (bytes / 1024).toFixed(1) + ' KB'
            : (bytes / 1048576).toFixed(1) + ' MB';
    }

    // ── Duplicate-submission prevention ──────────────────────────────────────
    let submitted = false;

    form.addEventListener('submit', function (e) {
        if (submitted) { e.preventDefault(); return; }

        // Basic client-side validation
        const title  = document.getElementById('title').value.trim();
        const amount = parseFloat(document.getElementById('amount').value);
        const hasQr  = qrInput.files.length > 0 || cameraInput.files.length > 0;

        if (!title || !amount || amount <= 0 || !hasQr) return; // let HTML5 validation handle it

        submitted = true;
        btnSubmit.disabled = true;
        btnNormal.classList.add('d-none');
        btnLoading.classList.remove('d-none');
    });
})();
</script>
@endpush

</x-admin-layout>
