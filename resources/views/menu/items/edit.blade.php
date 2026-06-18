<x-admin-layout title="Edit Menu Item">
@push('styles')
<style>
:root {
    --mi-gold: #a0723a; --mi-gold-hi: #b8832a;
    --mi-surface: #fff; --mi-border: #e8e2d8;
    --mi-ink: #1c1712; --mi-muted: #7a6e62;
    --mi-radius: 16px; --mi-r-sm: 10px;
}
.mi-form-wrap { max-width: 560px; margin: 0 auto; }
.mi-form-card { background: var(--mi-surface); border: 1.5px solid var(--mi-border); border-radius: var(--mi-radius); padding: 28px 28px 24px; }
.mi-form-title { font-size: 1.2rem; font-weight: 800; color: var(--mi-ink); margin-bottom: 24px; }
.mi-field { margin-bottom: 18px; }
.mi-label { display: block; font-size: .78rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--mi-muted); margin-bottom: 7px; }
.mi-input, .mi-select { width: 100%; padding: 11px 14px; border: 1.5px solid var(--mi-border); border-radius: var(--mi-r-sm); font-size: 1rem; background: var(--mi-surface); color: var(--mi-ink); transition: border-color .12s; box-sizing: border-box; }
.mi-input:focus, .mi-select:focus { outline: none; border-color: var(--mi-gold); }
.mi-ta-input { font-size: 1.1rem; }
.mi-error { color: #dc2626; font-size: .8rem; margin-top: 4px; }
.mi-hint  { color: var(--mi-muted); font-size: .8rem; margin-top: 4px; }
.mi-cat-preview { padding: 10px 14px; background: rgba(160,114,58,.08); border-radius: var(--mi-r-sm); margin-top: 8px; font-size: .88rem; color: var(--mi-gold); font-weight: 700; display: none; }
.mi-cat-preview.show { display: block; }
.mi-actions { display: flex; gap: 10px; margin-top: 24px; flex-wrap: wrap; }
.mi-btn-submit { flex: 1; min-width: 140px; padding: 12px; background: var(--mi-gold); color: #fff; border: none; border-radius: var(--mi-r-sm); font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .12s; }
.mi-btn-submit:hover { background: var(--mi-gold-hi); }
.mi-btn-cancel { padding: 12px 20px; color: var(--mi-muted); background: transparent; border: 1.5px solid var(--mi-border); border-radius: var(--mi-r-sm); font-size: 1rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; }
.mi-btn-del { padding: 12px 16px; color: #dc2626; background: transparent; border: 1.5px solid #fee2e2; border-radius: var(--mi-r-sm); font-size: 1rem; font-weight: 600; cursor: pointer; }
.mi-btn-del:hover { background: #fee2e2; }
</style>
@endpush

<div class="mi-form-wrap">
    <nav style="font-size:.83rem;color:var(--mi-muted);margin-bottom:14px">
        <a href="{{ route('menu.items.index') }}" style="color:var(--mi-muted);text-decoration:none">
            <i class="bi bi-arrow-left me-1"></i>Menu Items
        </a>
    </nav>

    <div class="mi-form-card">
        <div class="mi-form-title">Edit — {{ $item->item_en }}</div>

        <form method="POST" action="{{ route('menu.items.update', $item) }}">
            @csrf @method('PUT')

            <div class="mi-field">
                <label for="category_key" class="mi-label">Category</label>
                <select name="category_key" id="category_key"
                        class="mi-select @error('category_key') is-invalid @enderror"
                        onchange="showCatPreview(this)">
                    @foreach($categories as $key => $cat)
                        <option value="{{ $key }}"
                                @selected(old('category_key', $item->category_key) === $key)
                                data-ta="{{ $cat['ta'] }}">
                            {{ $cat['en'] }}
                        </option>
                    @endforeach
                </select>
                @error('category_key') <p class="mi-error">{{ $message }}</p> @enderror
                <div class="mi-cat-preview" id="catPreview"></div>
            </div>

            <div class="mi-field">
                <label for="item_en" class="mi-label">English Name</label>
                <input type="text" name="item_en" id="item_en"
                       class="mi-input @error('item_en') is-invalid @enderror"
                       value="{{ old('item_en', $item->item_en) }}"
                       autofocus autocomplete="off">
                @error('item_en') <p class="mi-error">{{ $message }}</p> @enderror
            </div>

            {{-- Tamil name — prefilled from DB; auto-sync disabled unless Refresh clicked --}}
            <div class="mi-field">
                <label for="item_ta" class="mi-label">Tamil Name</label>
                <div class="mi-ta-row">
                    <input type="text" name="item_ta" id="item_ta"
                           class="mi-input mi-ta-input @error('item_ta') is-invalid @enderror"
                           value="{{ old('item_ta', $item->item_ta) }}"
                           lang="ta" inputmode="text" autocomplete="off">
                    <button type="button" id="miRefreshBtn" class="mi-refresh-btn"
                            title="Re-generate Tamil suggestion from current English name">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span class="mi-refresh-label">Re-generate</span>
                    </button>
                </div>
                @error('item_ta') <p class="mi-error">{{ $message }}</p> @enderror
                <div class="mi-ta-hint" id="miTaHint">
                    <span class="mi-ta-hint-dot"></span>
                    <span id="miTaHintText"></span>
                </div>
            </div>

            <details style="margin-bottom:18px">
                <summary style="font-size:.82rem;color:var(--mi-muted);cursor:pointer;user-select:none">Advanced options</summary>
                <div class="mi-field mt-2">
                    <label for="sort_order" class="mi-label">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order"
                           class="mi-input" value="{{ old('sort_order', $item->sort_order) }}"
                           min="0" max="9999" style="max-width:120px">
                </div>
            </details>

            <div class="mi-actions">
                <a href="{{ route('menu.items.index') }}" class="mi-btn-cancel">Cancel</a>
                <button type="submit" class="mi-btn-submit">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>
        </form>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--mi-border)">
            <form method="POST" action="{{ route('menu.items.destroy', $item) }}"
                  onsubmit="return confirm('Delete {{ addslashes($item->item_en) }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="mi-btn-del">
                    <i class="bi bi-trash3 me-1"></i> Delete this item
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showCatPreview(sel) {
    const opt     = sel.options[sel.selectedIndex];
    const preview = document.getElementById('catPreview');
    if (sel.value && opt.dataset.ta) {
        preview.textContent = opt.text + ' / ' + opt.dataset.ta;
        preview.classList.add('show');
    } else { preview.classList.remove('show'); }
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('category_key');
    if (sel && sel.value) showCatPreview(sel);
});
</script>

@php
    // On edit: Tamil is always considered prefilled (has DB value) unless a validation error occurred
    // and old() is empty (user cleared the field). Either way, auto-sync is off — Refresh is explicit.
    $taPrefilled  = true;   // existing records always treated as manually set
    $translateUrl = route('menu.items.translate');
@endphp
@include('menu.items._tamil_autofill', ['translateUrl' => $translateUrl, 'taPrefilled' => $taPrefilled])

</x-admin-layout>
