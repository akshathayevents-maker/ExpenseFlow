<x-admin-layout title="Add Menu Item">
@push('styles')
<style>
:root {
    --mi-gold: #a0723a; --mi-gold-hi: #b8832a;
    --mi-surface: #fff; --mi-border: #e8e2d8;
    --mi-ink: #1c1712; --mi-muted: #7a6e62;
    --mi-radius: 16px; --mi-r-sm: 10px;
}
.mi-form-wrap { max-width: 560px; margin: 0 auto; }
.mi-form-card {
    background: var(--mi-surface); border: 1.5px solid var(--mi-border);
    border-radius: var(--mi-radius); padding: 28px 28px 24px;
}
.mi-form-title { font-size: 1.2rem; font-weight: 800; color: var(--mi-ink); margin-bottom: 24px; }
.mi-field { margin-bottom: 18px; }
.mi-label {
    display: block; font-size: .78rem; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    color: var(--mi-muted); margin-bottom: 7px;
}
.mi-label .mi-req { color: #dc2626; margin-left: 2px; }
.mi-input, .mi-select {
    width: 100%; padding: 11px 14px;
    border: 1.5px solid var(--mi-border); border-radius: var(--mi-r-sm);
    font-size: 1rem; background: var(--mi-surface); color: var(--mi-ink);
    transition: border-color .12s; box-sizing: border-box;
}
.mi-input:focus, .mi-select:focus { outline: none; border-color: var(--mi-gold); }
.mi-input.is-invalid { border-color: #dc2626; }
.mi-ta-input { font-size: 1.1rem; }
.mi-error { color: #dc2626; font-size: .8rem; margin-top: 4px; }
.mi-hint  { color: var(--mi-muted); font-size: .8rem; margin-top: 4px; }
.mi-cat-preview {
    padding: 10px 14px; background: rgba(160,114,58,.08);
    border-radius: var(--mi-r-sm); margin-top: 8px;
    font-size: .88rem; color: var(--mi-gold); font-weight: 700; display: none;
}
.mi-cat-preview.show { display: block; }
.mi-actions { display: flex; gap: 10px; margin-top: 24px; }
.mi-btn-submit {
    flex: 1; padding: 12px; background: var(--mi-gold); color: #fff;
    border: none; border-radius: var(--mi-r-sm); font-size: 1rem;
    font-weight: 700; cursor: pointer; transition: background .12s;
}
.mi-btn-submit:hover { background: var(--mi-gold-hi); }
.mi-btn-cancel {
    padding: 12px 20px; color: var(--mi-muted); background: transparent;
    border: 1.5px solid var(--mi-border); border-radius: var(--mi-r-sm);
    font-size: 1rem; font-weight: 600; text-decoration: none;
    display: flex; align-items: center; justify-content: center;
    transition: border-color .12s;
}
.mi-btn-cancel:hover { border-color: var(--mi-gold); color: var(--mi-gold); }
</style>
@endpush

<div class="mi-form-wrap">
    <nav style="font-size:.83rem;color:var(--mi-muted);margin-bottom:14px">
        <a href="{{ route('menu.items.index') }}" style="color:var(--mi-muted);text-decoration:none">
            <i class="bi bi-arrow-left me-1"></i>Menu Items
        </a>
    </nav>

    <div class="mi-form-card">
        <div class="mi-form-title">Add Menu Item</div>

        <form method="POST" action="{{ route('menu.items.store') }}">
            @csrf

            {{-- Category --}}
            <div class="mi-field">
                <label for="category_key" class="mi-label">
                    Category <span class="mi-req">*</span>
                </label>
                <select name="category_key" id="category_key"
                        class="mi-select @error('category_key') is-invalid @enderror"
                        onchange="showCatPreview(this)">
                    <option value="">Select category…</option>
                    @foreach($categories as $key => $cat)
                        <option value="{{ $key }}" @selected(old('category_key') === $key)
                                data-ta="{{ $cat['ta'] }}">
                            {{ $cat['en'] }}
                        </option>
                    @endforeach
                </select>
                @error('category_key')
                    <p class="mi-error">{{ $message }}</p>
                @enderror
                <div class="mi-cat-preview" id="catPreview"></div>
            </div>

            {{-- English name --}}
            <div class="mi-field">
                <label for="item_en" class="mi-label">
                    English Name <span class="mi-req">*</span>
                </label>
                <input type="text" name="item_en" id="item_en"
                       class="mi-input @error('item_en') is-invalid @enderror"
                       value="{{ old('item_en') }}"
                       placeholder="e.g. Kesari" autofocus autocomplete="off">
                @error('item_en')
                    <p class="mi-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tamil name with auto-fill UX --}}
            <div class="mi-field">
                <label for="item_ta" class="mi-label">
                    Tamil Name <span class="mi-req">*</span>
                </label>
                <div class="mi-ta-row">
                    <input type="text" name="item_ta" id="item_ta"
                           class="mi-input mi-ta-input @error('item_ta') is-invalid @enderror"
                           value="{{ old('item_ta') }}"
                           placeholder="e.g. கேசரி" lang="ta" inputmode="text" autocomplete="off">
                    <button type="button" id="miRefreshBtn" class="mi-refresh-btn"
                            title="Re-generate Tamil suggestion from current English name">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span class="mi-refresh-label">Re-generate</span>
                    </button>
                </div>
                @error('item_ta')
                    <p class="mi-error">{{ $message }}</p>
                @enderror
                <div class="mi-ta-hint" id="miTaHint">
                    <span class="mi-ta-hint-dot"></span>
                    <span id="miTaHintText"></span>
                </div>
            </div>

            {{-- Advanced --}}
            <details style="margin-bottom:18px">
                <summary style="font-size:.82rem;color:var(--mi-muted);cursor:pointer;user-select:none">
                    Advanced options
                </summary>
                <div class="mi-field mt-2">
                    <label for="sort_order" class="mi-label">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order"
                           class="mi-input" value="{{ old('sort_order', 0) }}"
                           min="0" max="9999" style="max-width:120px">
                    <p class="mi-hint">Lower = appears first within its category.</p>
                </div>
            </details>

            <div class="mi-actions">
                <a href="{{ route('menu.items.index') }}" class="mi-btn-cancel">Cancel</a>
                <button type="submit" class="mi-btn-submit">
                    <i class="bi bi-plus-circle me-1"></i> Add Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCatPreview(sel) {
    const opt     = sel.options[sel.selectedIndex];
    const preview = document.getElementById('catPreview');
    if (sel.value && opt.dataset.ta) {
        preview.textContent = opt.text + ' / ' + opt.dataset.ta;
        preview.classList.add('show');
    } else {
        preview.classList.remove('show');
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('category_key');
    if (sel && sel.value) showCatPreview(sel);
});
</script>

@php
    // Tamil is prefilled only if old() has a value (form re-submission after validation error)
    $taPrefilled  = ! empty(old('item_ta'));
    $translateUrl = route('menu.items.translate');
@endphp
@include('menu.items._tamil_autofill', ['translateUrl' => $translateUrl, 'taPrefilled' => $taPrefilled])

</x-admin-layout>
