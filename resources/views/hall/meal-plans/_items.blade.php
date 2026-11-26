{{--
    Dishes in a meal plan: add / edit / remove / reorder. Typing suggests names from the menu catalogue,
    recipes and other plans. Pasting several lines adds one dish per line.
    $items: current dish names.
--}}
@php $items = array_values(old('items', $items ?? [])); @endphp
@push('styles')
<style>
.mpi-list{display:flex;flex-direction:column;gap:8px}
.mpi-row{display:grid;grid-template-columns:28px minmax(0,1fr) auto;gap:8px;align-items:center}
.mpi-num{font-size:.78rem;font-weight:700;color:var(--ef-faint);text-align:right;font-variant-numeric:tabular-nums}
.mpi-row .ef-mpf-input{min-height:46px;font-size:16px}
.mpi-acts{display:flex;gap:2px}
.mpi-btn{width:40px;height:44px;border:0;background:none;border-radius:10px;color:var(--ef-faint);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:1rem}
.mpi-btn:hover{background:rgba(20,20,18,.05);color:var(--ef-ink)}
.mpi-btn--del:hover{color:var(--ef-danger)}
.mpi-btn:disabled{opacity:.3;cursor:default;background:none}
@media (max-width:520px){.mpi-row{grid-template-columns:22px minmax(0,1fr) auto}.mpi-btn{width:34px}.mpi-up{display:none!important}}
.mpi-foot{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:10px}
.mpi-add{display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:8px 14px;border:1.5px dashed var(--ef-border-strong);border-radius:11px;background:none;color:var(--ef-ink);font-weight:680;font-size:.86rem;cursor:pointer}
.mpi-add:hover{border-color:var(--ef-gold);color:var(--ef-gold)}
.mpi-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted)}
.mpi-empty{font-size:.84rem;color:var(--ef-muted);padding:4px 0}
</style>
@endpush

<div class="ef-mpf-field">
    <label class="ef-mpf-label" id="mpiLabel">Dishes in this plan</label>
    <div class="mpi-list" id="mpiList" aria-labelledby="mpiLabel">
        @foreach($items as $n => $name)
            <div class="mpi-row" data-dish>
                <span class="mpi-num" data-n>{{ $n + 1 }}</span>
                <input class="ef-mpf-input" name="items[]" value="{{ $name }}" maxlength="150" list="mpiSuggest" autocomplete="off" aria-label="Dish {{ $n + 1 }}">
                <span class="mpi-acts">
                    <button type="button" class="mpi-btn mpi-up" data-up title="Move up" aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="mpi-btn mpi-btn--del" data-del title="Remove" aria-label="Remove dish"><i class="bi bi-trash3"></i></button>
                </span>
            </div>
        @endforeach
    </div>
    <div class="mpi-empty" id="mpiEmpty" @if($items) hidden @endif>No dishes yet — add the first one.</div>
    <div class="mpi-foot">
        <button type="button" class="mpi-add" id="mpiAdd"><i class="bi bi-plus-lg"></i> Add dish</button>
        <span class="mpi-count" id="mpiCount"></span>
    </div>
    @error('items')<p class="ef-mpf-error">{{ $message }}</p>@enderror
    @foreach($errors->get('items.*') as $msgs)<p class="ef-mpf-error">{{ $msgs[0] }}</p>@endforeach
    <p class="ef-mpf-hint">Press Enter for the next dish. Paste a list (one dish per line) to add many at once. Used for the hall order's final menu and material requests.</p>
    <datalist id="mpiSuggest">
        @foreach($dishSuggestions as $d)<option value="{{ $d }}">@endforeach
    </datalist>
    <template id="mpiTpl">
        <div class="mpi-row" data-dish>
            <span class="mpi-num" data-n></span>
            <input class="ef-mpf-input" name="items[]" maxlength="150" list="mpiSuggest" autocomplete="off" aria-label="Dish">
            <span class="mpi-acts">
                <button type="button" class="mpi-btn mpi-up" data-up title="Move up" aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
                <button type="button" class="mpi-btn mpi-btn--del" data-del title="Remove" aria-label="Remove dish"><i class="bi bi-trash3"></i></button>
            </span>
        </div>
    </template>
</div>

@push('scripts')
<script>
(() => {
    const list = document.getElementById('mpiList'); if (!list) return;
    const MAX = {{ \App\Models\MealPlan::MAX_ITEMS }};
    const rows = () => [...list.querySelectorAll('[data-dish]')];
    function sync() {
        const r = rows();
        r.forEach((row, i) => {
            row.querySelector('[data-n]').textContent = i + 1;
            row.querySelector('input').setAttribute('aria-label', 'Dish ' + (i + 1));
            row.querySelector('[data-up]').disabled = i === 0;
        });
        const filled = r.filter(x => x.querySelector('input').value.trim()).length;
        document.getElementById('mpiCount').textContent = filled ? filled + (filled === 1 ? ' dish' : ' dishes') : '';
        document.getElementById('mpiEmpty').hidden = r.length > 0;
        document.getElementById('mpiAdd').disabled = r.length >= MAX;
    }
    function add(value = '', after = null, focus = true) {
        if (rows().length >= MAX) return null;
        const row = document.getElementById('mpiTpl').content.firstElementChild.cloneNode(true);
        row.querySelector('input').value = value;
        after ? after.after(row) : list.appendChild(row);
        sync();
        if (focus) row.querySelector('input').focus();
        return row;
    }
    document.getElementById('mpiAdd').addEventListener('click', () => {
        const empty = rows().find(r => !r.querySelector('input').value.trim());
        empty ? empty.querySelector('input').focus() : add();
    });
    list.addEventListener('click', e => {
        const row = e.target.closest('[data-dish]'); if (!row) return;
        if (e.target.closest('[data-del]')) {
            const next = row.nextElementSibling || row.previousElementSibling;
            row.remove(); sync(); next?.querySelector('input').focus();
        } else if (e.target.closest('[data-up]') && row.previousElementSibling) {
            row.previousElementSibling.before(row); sync(); row.querySelector('[data-up]').focus();
        }
    });
    list.addEventListener('keydown', e => {
        if (e.key !== 'Enter' || !e.target.matches('input')) return;
        e.preventDefault();   // Enter = next dish, never submits the form
        const row = e.target.closest('[data-dish]');
        const next = row.nextElementSibling;
        if (next && !next.querySelector('input').value.trim()) next.querySelector('input').focus();
        else if (e.target.value.trim()) add('', row);
    });
    list.addEventListener('paste', e => {
        const text = (e.clipboardData || window.clipboardData)?.getData('text') || '';
        const parts = text.split(/[\r\n]+/).map(s => s.replace(/^[\s\-\*•\d.)]+/, '').trim()).filter(Boolean);
        if (parts.length < 2 || !e.target.matches('input')) return;
        e.preventDefault();
        let row = e.target.closest('[data-dish]');
        const cur = e.target.value.trim();
        e.target.value = cur ? cur : parts.shift();
        parts.forEach(p => { row = add(p, row, false) || row; });
        sync();
    });
    list.addEventListener('input', sync);
    if (!rows().length) for (let i = 0; i < 3; i++) add('', null, false);
    sync();
})();
</script>
@endpush
