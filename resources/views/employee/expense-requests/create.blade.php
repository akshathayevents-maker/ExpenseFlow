<x-admin-layout title="Submit Expense">
@push('styles')
<style>
/* ── UPI-style expense entry — ef-upi-* ───────────────── */
:root {
    --upi-green:    #1a6645;
    --upi-green-hi: #22845a;
    --upi-red:      #dc2626;
    --upi-amber:    #d97706;
    --upi-text:     #111827;
    --upi-sub:      #6b7280;
    --upi-border:   #e5e7eb;
    --upi-bg:       #f9fafb;
}

/* ── Page wrapper ─────────────────────────────────────── */
.ef-upi-page {
    max-width: 420px;
    margin: 0 auto;
    padding-bottom: 100px;
}

/* ── Top bar ──────────────────────────────────────────── */
.ef-upi-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 0 20px;
}
.ef-upi-back {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: #f3f4f6;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--upi-text);
    font-size: .95rem;
    text-decoration: none;
    transition: background .12s;
    flex-shrink: 0;
}
.ef-upi-back:hover { background: #e5e7eb; color: var(--upi-text); }
.ef-upi-heading {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--upi-text);
    text-align: center;
    flex: 1;
}
.ef-upi-wallet-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    flex-shrink: 0;
}
.chip-ok  { background: #dcfce7; color: #15803d; }
.chip-low { background: #fef3c7; color: #b45309; }
.chip-neg { background: #fee2e2; color: #dc2626; }

/* ── Amount hero ──────────────────────────────────────── */
.ef-upi-amount-hero {
    text-align: center;
    padding: 20px 0 28px;
    position: relative;
}
.ef-upi-amount-lbl {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--upi-sub);
    margin-bottom: 12px;
}
.ef-upi-amount-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.ef-upi-rupee {
    font-size: 2rem;
    font-weight: 700;
    color: var(--upi-sub);
    line-height: 1;
    padding-top: 6px;
    transition: color .2s;
}
.ef-upi-amount-input {
    font-size: 3rem;
    font-weight: 800;
    color: var(--upi-text);
    border: none;
    background: transparent;
    outline: none;
    text-align: center;
    width: 200px;
    min-width: 80px;
    max-width: 280px;
    line-height: 1;
    caret-color: var(--upi-green);
    transition: color .2s;
    /* auto-size via JS */
}
.ef-upi-amount-input::placeholder { color: #d1d5db; }
.ef-upi-amount-input:focus ~ .ef-upi-amount-line,
.ef-upi-amount-row:focus-within .ef-upi-amount-line { background: var(--upi-green); }
.ef-upi-amount-row:focus-within .ef-upi-rupee { color: var(--upi-green); }
.ef-upi-amount-line {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    height: 2px;
    background: var(--upi-border);
    border-radius: 2px;
    transition: background .2s, width .2s;
}
.ef-upi-amount-row:focus-within + .ef-upi-amount-line { background: var(--upi-green); width: 160px; }

/* ── Fields ───────────────────────────────────────────── */
.ef-upi-field {
    background: #fff;
    border: 1.5px solid var(--upi-border);
    border-radius: 16px;
    padding: 16px 18px;
    margin-bottom: 12px;
    transition: border-color .15s, box-shadow .15s;
}
.ef-upi-field:focus-within {
    border-color: var(--upi-green);
    box-shadow: 0 0 0 3px rgba(26,102,69,.08);
}
.ef-upi-field.is-invalid {
    border-color: var(--upi-red);
    box-shadow: 0 0 0 3px rgba(220,38,38,.06);
}
.ef-upi-field-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--upi-sub);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ef-upi-field-input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--upi-text);
    padding: 0;
}
.ef-upi-field-input::placeholder { color: #9ca3af; font-weight: 500; }
.ef-upi-field-error {
    font-size: .74rem;
    color: var(--upi-red);
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Suggestion chips below title */
.ef-upi-suggestions {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    margin-top: 10px;
}
.ef-upi-chip {
    font-size: .73rem;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: all .12s;
    user-select: none;
}
.ef-upi-chip:hover,
.ef-upi-chip.selected {
    background: #dcfce7;
    border-color: #6ee7b7;
    color: #065f46;
}

/* ── Upload zone ──────────────────────────────────────── */
.ef-upi-upload {
    background: #fff;
    border: 2px dashed #d1d5db;
    border-radius: 16px;
    padding: 24px 16px;
    margin-bottom: 12px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.ef-upi-upload:hover,
.ef-upi-upload.drag-over {
    border-color: var(--upi-green);
    background: rgba(26,102,69,.03);
}
.ef-upi-upload.has-file {
    border-style: solid;
    border-color: var(--upi-green);
    background: rgba(26,102,69,.03);
}
.ef-upi-upload.is-invalid { border-color: var(--upi-red); }
.ef-upi-upload-icon {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: #f3f4f6;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: var(--upi-sub);
    margin-bottom: 10px;
    transition: background .2s, color .2s;
}
.ef-upi-upload:hover .ef-upi-upload-icon,
.ef-upi-upload.has-file .ef-upi-upload-icon {
    background: #dcfce7;
    color: var(--upi-green);
}
.ef-upi-upload-title {
    font-size: .92rem;
    font-weight: 700;
    color: var(--upi-text);
    margin-bottom: 3px;
}
.ef-upi-upload-sub {
    font-size: .75rem;
    color: var(--upi-sub);
    margin-bottom: 10px;
}
.ef-upi-camera-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 16px;
    border-radius: 20px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    font-size: .78rem;
    font-weight: 600;
    color: var(--upi-text);
    cursor: pointer;
    transition: all .12s;
}
.ef-upi-camera-pill:hover { background: #dcfce7; border-color: #6ee7b7; color: #065f46; }

/* Upload preview */
.ef-upi-preview { text-align: center; }
.ef-upi-preview-img {
    max-width: 130px; max-height: 130px;
    object-fit: contain;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}
.ef-upi-preview-name {
    font-size: .73rem;
    color: var(--upi-sub);
    margin: 6px 0 0;
}
.ef-upi-change-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 20px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    font-size: .74rem;
    font-weight: 600;
    color: var(--upi-sub);
    cursor: pointer;
    margin-top: 8px;
    transition: all .12s;
}
.ef-upi-change-pill:hover { background: #e5e7eb; color: var(--upi-text); }

/* ── Notes (collapsible feel) ─────────────────────────── */
.ef-upi-notes-toggle {
    display: flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    padding: 4px 0 12px;
    font-size: .8rem;
    font-weight: 600;
    color: var(--upi-sub);
    cursor: pointer;
    width: 100%;
    text-align: left;
    transition: color .12s;
}
.ef-upi-notes-toggle:hover { color: var(--upi-text); }
.ef-upi-notes-toggle i { transition: transform .2s; }
.ef-upi-notes-toggle.open i { transform: rotate(180deg); }
.ef-upi-notes-body {
    display: none;
    margin-bottom: 12px;
}
.ef-upi-notes-body.open { display: block; }
.ef-upi-notes-input {
    width: 100%;
    background: #fff;
    border: 1.5px solid var(--upi-border);
    border-radius: 14px;
    padding: 14px 16px;
    font-size: .95rem;
    color: var(--upi-text);
    outline: none;
    resize: vertical;
    min-height: 80px;
    transition: border-color .15s, box-shadow .15s;
}
.ef-upi-notes-input:focus {
    border-color: var(--upi-green);
    box-shadow: 0 0 0 3px rgba(26,102,69,.08);
}

/* ── Error panel ──────────────────────────────────────── */
.ef-upi-errors {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 16px;
    font-size: .82rem;
    color: #991b1b;
}
.ef-upi-errors ul { margin: 6px 0 0; padding-left: 16px; }
.ef-upi-errors li { margin-bottom: 2px; }

/* ── Sticky submit ────────────────────────────────────── */
.ef-upi-submit-wrap {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(8px);
    padding: 14px 20px calc(14px + env(safe-area-inset-bottom));
    box-shadow: 0 -2px 20px rgba(0,0,0,.08);
    z-index: 1020;
}
.ef-upi-submit-inner { max-width: 420px; margin: 0 auto; }
.ef-upi-submit-btn {
    width: 100%;
    background: var(--upi-green);
    border: none;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    padding: 17px;
    border-radius: 16px;
    letter-spacing: .03em;
    box-shadow: 0 4px 14px rgba(26,102,69,.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background .15s, transform .1s, box-shadow .15s;
}
.ef-upi-submit-btn:hover:not(:disabled) {
    background: var(--upi-green-hi);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(26,102,69,.38);
}
.ef-upi-submit-btn:active:not(:disabled) { transform: translateY(0); }
.ef-upi-submit-btn:disabled { background: #86efac; cursor: not-allowed; box-shadow: none; }
.ef-upi-submit-btn .spinner-border { width: 1rem; height: 1rem; border-width: 2px; }
.ef-upi-spacer { height: 90px; }

/* ── Responsive ───────────────────────────────────────── */
@media (min-width: 768px) {
    .ef-upi-page { padding: 12px 0 100px; }
    .ef-upi-amount-input { font-size: 3.5rem; }
}
</style>
@endpush

<div class="ef-upi-page">

    {{-- Top bar --}}
    <div class="ef-upi-topbar">
        <a href="{{ $backUrl }}" class="ef-upi-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="ef-upi-heading">Submit Expense</span>
        @php
            $chipClass = $walletNegative ? 'chip-neg' : ($walletLow ? 'chip-low' : 'chip-ok');
        @endphp
        <span class="ef-upi-wallet-chip {{ $chipClass }}">
            <i class="bi bi-wallet2"></i>
            ₹{{ number_format($walletBalance, 0) }}
        </span>
    </div>

    {{-- Errors --}}
    @if($errors->any())
    <div class="ef-upi-errors">
        <strong>Please fix:</strong>
        <ul>
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
          id="upiForm"
          novalidate>
    @csrf

    {{-- Amount hero --}}
    <div class="ef-upi-amount-hero">
        <p class="ef-upi-amount-lbl">Enter Amount</p>
        <div class="ef-upi-amount-row">
            <span class="ef-upi-rupee">₹</span>
            <input type="number"
                   id="amount"
                   name="amount"
                   class="ef-upi-amount-input"
                   step="0.01"
                   min="0.01"
                   inputmode="decimal"
                   value="{{ old('amount') }}"
                   placeholder="0"
                   required>
        </div>
        <div class="ef-upi-amount-line"></div>
        @error('amount')
        <div class="ef-upi-field-error mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
        @enderror
    </div>

    {{-- Title --}}
    <div class="ef-upi-field {{ $errors->has('title') ? 'is-invalid' : '' }}">
        <div class="ef-upi-field-label">
            <span>What is this for?</span>
            <span style="font-size:.65rem;color:#9ca3af;font-weight:500;text-transform:none;letter-spacing:0">required</span>
        </div>
        <input type="text"
               id="title"
               name="title"
               class="ef-upi-field-input"
               value="{{ old('title') }}"
               placeholder="Fuel, Vegetables, Supplies…"
               autocomplete="off"
               autofocus
               required>
        {{-- Quick-fill chips --}}
        <div class="ef-upi-suggestions">
            <span class="ef-upi-chip" data-val="Fuel">Fuel</span>
            <span class="ef-upi-chip" data-val="Vegetables">Vegetables</span>
            <span class="ef-upi-chip" data-val="Milk">Milk</span>
            <span class="ef-upi-chip" data-val="Cleaning">Cleaning</span>
            <span class="ef-upi-chip" data-val="Advance">Advance</span>
        </div>
        @error('title')
        <div class="ef-upi-field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
        @enderror
    </div>

    {{-- Upload --}}
    <div id="uploadZone"
         class="ef-upi-upload {{ $errors->has('qr') ? 'is-invalid' : '' }}"
         role="button"
         tabindex="0"
         aria-label="Upload payment QR or receipt">

        <div id="uploadPlaceholder">
            <div class="ef-upi-upload-icon"><i class="bi bi-image"></i></div>
            <p class="ef-upi-upload-title">Upload Bill / QR / Screenshot</p>
            <p class="ef-upi-upload-sub">Tap to pick from gallery</p>
            <button type="button" id="btnCamera" class="ef-upi-camera-pill">
                <i class="bi bi-camera"></i> Take Photo
            </button>
        </div>

        <div id="uploadPreview" class="ef-upi-preview d-none">
            <img id="previewImg" class="ef-upi-preview-img d-none" src="" alt="">
            <div id="previewPdf" class="d-none">
                <i class="bi bi-file-earmark-pdf" style="font-size:3.2rem;color:#dc2626"></i>
            </div>
            <p id="previewName" class="ef-upi-preview-name"></p>
            <button type="button" id="btnChange" class="ef-upi-change-pill">
                <i class="bi bi-arrow-repeat"></i> Change
            </button>
        </div>
    </div>

    <input type="file" id="qrInput"       name="qr" accept="image/*,application/pdf" class="d-none">
    <input type="file" id="qrInputCamera" name="qr" accept="image/*" capture="environment" class="d-none">

    @error('qr')
    <div class="ef-upi-field-error mb-3"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
    @enderror

    {{-- Notes — collapsible --}}
    <button type="button" class="ef-upi-notes-toggle" id="notesToggle">
        <i class="bi bi-chevron-down"></i>
        Add a note (optional)
    </button>
    <div class="ef-upi-notes-body" id="notesBody">
        <textarea name="notes"
                  id="notes"
                  class="ef-upi-notes-input"
                  rows="3"
                  placeholder="Any details for your manager…">{{ old('notes') }}</textarea>
        @error('notes')
        <div class="ef-upi-field-error mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
        @enderror
    </div>

    {{-- Spacer for fixed button --}}
    <div class="ef-upi-spacer"></div>

    {{-- Sticky submit --}}
    <div class="ef-upi-submit-wrap">
        <div class="ef-upi-submit-inner">
            <button type="submit" id="btnSubmit" class="ef-upi-submit-btn">
                <span id="btnNormal"><i class="bi bi-send-fill"></i> Submit Expense</span>
                <span id="btnLoading" class="d-none">
                    <span class="spinner-border" role="status" aria-hidden="true"></span> Submitting…
                </span>
            </button>
        </div>
    </div>

    </form>

</div>{{-- /page --}}

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Quick-fill chips ─────────────────────────────────────────────────────
    const titleInput = document.getElementById('title');
    document.querySelectorAll('.ef-upi-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            titleInput.value = chip.dataset.val;
            document.querySelectorAll('.ef-upi-chip').forEach(c => c.classList.remove('selected'));
            chip.classList.add('selected');
            titleInput.focus();
        });
    });
    titleInput.addEventListener('input', () => {
        document.querySelectorAll('.ef-upi-chip').forEach(c => c.classList.remove('selected'));
    });

    // ── Notes toggle ─────────────────────────────────────────────────────────
    const notesToggle = document.getElementById('notesToggle');
    const notesBody   = document.getElementById('notesBody');
    // If old() notes value exists, open by default
    @if(old('notes'))
    notesBody.classList.add('open');
    notesToggle.classList.add('open');
    @endif
    notesToggle.addEventListener('click', () => {
        const open = notesBody.classList.toggle('open');
        notesToggle.classList.toggle('open', open);
        if (open) document.getElementById('notes').focus();
    });

    // ── Upload logic ─────────────────────────────────────────────────────────
    const zone        = document.getElementById('uploadZone');
    const qrInput     = document.getElementById('qrInput');
    const camInput    = document.getElementById('qrInputCamera');
    const btnCamera   = document.getElementById('btnCamera');
    const placeholder = document.getElementById('uploadPlaceholder');
    const preview     = document.getElementById('uploadPreview');
    const previewImg  = document.getElementById('previewImg');
    const previewPdf  = document.getElementById('previewPdf');
    const previewName = document.getElementById('previewName');
    const btnChange   = document.getElementById('btnChange');

    // ── EXIF orientation reader (pure ArrayBuffer, no deps) ──────────────────
    function readExifOrientation(buf) {
        try {
            const v = new DataView(buf);
            if (v.getUint16(0, false) !== 0xFFD8) return 1;
            let off = 2;
            while (off < Math.min(v.byteLength, 131072)) {
                const marker = v.getUint16(off, false);
                off += 2;
                if (marker === 0xFFE1) {
                    if (v.getUint32(off + 2, false) !== 0x45786966 ||
                        v.getUint16(off + 6, false) !== 0x0000) break;
                    const tiff = off + 8;
                    const le   = v.getUint16(tiff, false) === 0x4949;
                    const ifd  = v.getUint32(tiff + 4, le);
                    const n    = v.getUint16(tiff + ifd, le);
                    for (let i = 0; i < n; i++) {
                        const tag = v.getUint16(tiff + ifd + 2 + i * 12, le);
                        if (tag === 0x0112) {
                            return v.getUint16(tiff + ifd + 2 + i * 12 + 8, le);
                        }
                    }
                    break;
                }
                if ((marker & 0xFF00) !== 0xFF00) break;
                off += v.getUint16(off, false);
            }
        } catch (_) {}
        return 1;
    }

    // ── Normalize image: fix EXIF orientation + compress if >3 MB ───────────
    function normalizeImage(file) {
        return new Promise(function (resolve) {
            if (file.type === 'application/pdf') { resolve(file); return; }
            if (!file.type.startsWith('image/') && file.type !== '') { resolve(file); return; }

            const reader = new FileReader();
            reader.onerror = function () { resolve(file); };
            reader.onload = function (e) {
                const buf         = e.target.result;
                const orientation = readExifOrientation(buf);
                const blob        = new Blob([buf], { type: file.type || 'image/jpeg' });
                const url         = URL.createObjectURL(blob);
                const img         = new Image();

                img.onerror = function () {
                    URL.revokeObjectURL(url);
                    // HEIC on non-Safari: can't decode — resolve original so server gives a clear error
                    resolve(file);
                };
                img.onload = function () {
                    URL.revokeObjectURL(url);

                    const w = img.naturalWidth, h = img.naturalHeight;
                    if (!w || !h) { resolve(file); return; }

                    const swapDims = [5, 6, 7, 8].includes(orientation);
                    const outW = swapDims ? h : w;
                    const outH = swapDims ? w : h;

                    // Scale down only if original > 3 MB or dimensions > 2500 px
                    const needsScale = file.size > 3 * 1024 * 1024 || Math.max(outW, outH) > 2500;
                    const scale = needsScale ? Math.min(1, 2000 / Math.max(outW, outH)) : 1;

                    const canvas = document.createElement('canvas');
                    canvas.width  = Math.round(outW * scale);
                    canvas.height = Math.round(outH * scale);
                    const ctx = canvas.getContext('2d');

                    // Apply EXIF rotation (cases 5/7 treated as 6/8 — mirror ignored, QR still scannable)
                    switch (orientation) {
                        case 3: case 4:
                            ctx.translate(canvas.width, canvas.height);
                            ctx.rotate(Math.PI); break;
                        case 5: case 6:
                            ctx.translate(canvas.width, 0);
                            ctx.rotate(Math.PI / 2); break;
                        case 7: case 8:
                            ctx.translate(0, canvas.height);
                            ctx.rotate(-Math.PI / 2); break;
                    }

                    ctx.drawImage(img, 0, 0, w * scale, h * scale);

                    const quality = (scale < 1 || file.size > 3 * 1024 * 1024) ? 0.82 : 0.92;
                    canvas.toBlob(function (outBlob) {
                        if (!outBlob) { resolve(file); return; }
                        const outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        resolve(new File([outBlob], outName, { type: 'image/jpeg' }));
                    }, 'image/jpeg', quality);
                };
                img.src = url;
            };
            reader.readAsArrayBuffer(file);
        });
    }

    // ── Swap file into input via DataTransfer ────────────────────────────────
    function assignFileToInput(input, file) {
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (_) { /* older browsers: use original file */ }
    }

    // ── Show preview after normalisation ────────────────────────────────────
    function showPreview(file) {
        const sz = file.size < 1048576
            ? (file.size / 1024).toFixed(1) + ' KB'
            : (file.size / 1048576).toFixed(1) + ' MB';
        previewName.textContent = file.name + ' · ' + sz;
        if (file.type === 'application/pdf') {
            previewImg.classList.add('d-none'); previewPdf.classList.remove('d-none');
        } else {
            const r = new FileReader();
            r.onload = ev => {
                previewImg.src = ev.target.result;
                previewImg.classList.remove('d-none');
                previewPdf.classList.add('d-none');
            };
            r.readAsDataURL(file);
        }
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        zone.classList.add('has-file');
        zone.classList.remove('is-invalid');
        zone.classList.remove('is-processing');
    }

    async function handleFile(file, activeInput, passiveInput) {
        if (file.size > 20 * 1024 * 1024) {
            alert('File too large (max 20 MB). Please compress or choose a smaller image.');
            activeInput.value = '';
            return;
        }

        // Mark active input as the QR carrier
        activeInput.setAttribute('name', 'qr');
        if (passiveInput) passiveInput.removeAttribute('name');

        // Show processing indicator
        zone.classList.add('is-processing');
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
        previewName.textContent = 'Processing image…';

        const processed = await normalizeImage(file);
        assignFileToInput(activeInput, processed);
        showPreview(processed);
    }

    zone.addEventListener('click', e => {
        if (btnCamera.contains(e.target) || btnChange.contains(e.target)) return;
        qrInput.click();
    });
    zone.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); qrInput.click(); }
    });
    btnCamera.addEventListener('click', e => { e.stopPropagation(); camInput.click(); });
    btnChange.addEventListener('click', e => { e.stopPropagation(); qrInput.click(); });

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const f = e.dataTransfer.files[0];
        if (f) handleFile(f, qrInput, camInput);
    });

    qrInput.addEventListener('change', () => {
        if (qrInput.files[0]) handleFile(qrInput.files[0], qrInput, camInput);
    });
    camInput.addEventListener('change', () => {
        if (camInput.files[0]) handleFile(camInput.files[0], camInput, qrInput);
    });

    // ── Submit guard ─────────────────────────────────────────────────────────
    let submitted = false;
    document.getElementById('upiForm').addEventListener('submit', function (e) {
        if (submitted) { e.preventDefault(); return; }
        if (zone.classList.contains('is-processing')) { e.preventDefault(); return; }
        const hasQr = (qrInput.getAttribute('name') === 'qr' && qrInput.files.length > 0) ||
                      (camInput.getAttribute('name') === 'qr' && camInput.files.length > 0);
        if (!titleInput.value.trim() || !parseFloat(document.getElementById('amount').value) || !hasQr) return;
        submitted = true;
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        document.getElementById('btnNormal').classList.add('d-none');
        document.getElementById('btnLoading').classList.remove('d-none');
    });
})();
</script>
@endpush
</x-admin-layout>
