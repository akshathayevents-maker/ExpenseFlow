{{--
    Spreadsheet-like item grid shared by Bulk Purchase and Material Request.
    Params: $mode ('purchase'|'request'), $catalog (array of items), $lines (old/prefill rows),
            $storageKey (string|null: localStorage key for unsent rows), $vendorSelect (CSS selector|null)
    Keyboard: Enter moves to the next field; Enter on the last field adds a row.
    Paste: paste rows copied from Excel (Item, Qty, Unit[, Rate]) into any item box.
--}}
@php
    $isPurchase = $mode === 'purchase';
    $gridId = 'icGrid' . ucfirst($mode);
@endphp
<div class="ic-grid" id="{{ $gridId }}" data-mode="{{ $mode }}">
    <div class="ic-row ic-row--head">
        <div>#</div><div>Item</div><div>Qty</div><div>Unit</div>
        @if($isPurchase)<div>Rate (₹)</div><div>Tax %</div><div style="text-align:right">Amount</div>@else<div>In store</div>@endif
        <div></div>
    </div>
    <div class="ic-rows"></div>
</div>
<datalist id="{{ $gridId }}List">
    @foreach($catalog as $c)
        <option value="{{ $c['name'] }}">
        @foreach($c['aliases'] ?? [] as $a)<option value="{{ $a }}" label="→ {{ $c['name'] }}">@endforeach
    @endforeach
</datalist>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
    <button type="button" class="ic-btn ic-btn--sm" data-add-row><i class="bi bi-plus-lg"></i> Add row</button>
    <button type="button" class="ic-btn ic-btn--sm" data-add-rows="5">+5 rows</button>
    <span class="ic-note" style="align-self:center">Tip: copy rows from Excel (Item · Qty · Unit{{ $isPurchase ? ' · Rate' : '' }}) and paste into an item box.</span>
</div>
<template id="{{ $gridId }}Tpl">
    <div class="ic-row">
        <div class="ic-rownum"></div>
        <div class="ic-cell-item">
            <span class="ic-lbl-m">Item</span>
            <input type="text" class="ic-input ic-item-input" list="{{ $gridId }}List" placeholder="Type to search…" autocomplete="off" aria-label="Item">
            <input type="hidden" data-f="inventory_item_id">
            <input type="hidden" data-f="description">
            <div class="ic-hint" data-hint></div>
            @if($isPurchase)
            <label class="ic-hint" data-remember style="display:none;gap:6px;align-items:center;cursor:pointer">
                <input type="checkbox" data-f="remember_alias" value="1"> Remember this invoice name for next time
            </label>
            @endif
        </div>
        <div><span class="ic-lbl-m">Qty</span><input type="number" class="ic-input" data-f="quantity" min="0" step="0.001" inputmode="decimal" aria-label="Quantity"></div>
        <div><span class="ic-lbl-m">Unit</span><select class="ic-select" data-f="unit" aria-label="Unit"></select></div>
        @if($isPurchase)
        <div><span class="ic-lbl-m">Rate ₹</span><input type="number" class="ic-input" data-f="rate" min="0" step="0.01" inputmode="decimal" aria-label="Rate"></div>
        <div><span class="ic-lbl-m">Tax %</span><input type="number" class="ic-input" data-f="tax_percent" min="0" max="100" step="0.01" inputmode="decimal" aria-label="Tax percent"></div>
        <div class="ic-amount"><span class="ic-lbl-m">Amount</span><span data-amount>0.00</span></div>
        @else
        <div class="ic-amount" style="text-align:left;font-weight:600"><span class="ic-lbl-m">In store</span><span data-stock>—</span></div>
        @endif
        <div><button type="button" class="ic-del" data-del aria-label="Remove row"><i class="bi bi-x-circle"></i></button></div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const grid = document.getElementById(@json($gridId));
    if (!grid) return;
    const MODE = @json($mode);
    const CATALOG = @json(array_values($catalog));
    const INITIAL = @json(array_values($lines));
    const STORAGE_KEY = @json($storageKey ?? null);
    const MATCH_URL = @json(route('inventory.items.match'));
    const VENDOR_SEL = @json($vendorSelect ?? null);
    const MAX = {{ (int) config('inventory.max_lines', 100) }};
    const rowsEl = grid.querySelector('.ic-rows');
    const tpl = document.getElementById(@json($gridId . 'Tpl'));
    const form = grid.closest('form');
    const byId = new Map(CATALOG.map(c => [String(c.id), c]));
    const byName = new Map();
    CATALOG.forEach(c => {
        byName.set(c.name.toLowerCase(), c);
        (c.aliases || []).forEach(a => { if (!byName.has(a.toLowerCase())) byName.set(a.toLowerCase(), c); });
    });
    const fmt = n => (Math.round(n * 100) / 100).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function setUnits(row, item, selected) {
        const sel = row.querySelector('[data-f=unit]');
        const units = item ? item.units : @json(array_keys(config('inventory.units')));
        sel.innerHTML = '';
        units.forEach(u => { const o = document.createElement('option'); o.value = u; o.textContent = u; sel.appendChild(o); });
        if (selected && !units.includes(selected)) {
            const o = document.createElement('option'); o.value = selected; o.textContent = selected + ' (no rule)'; sel.appendChild(o);
        }
        sel.value = selected || (item ? item.unit : units[0]);
    }

    function pickItem(row, item, keepUnit) {
        row.querySelector('[data-f=inventory_item_id]').value = item ? item.id : '';
        row.classList.toggle('--bad', !item && row.querySelector('.ic-item-input').value.trim() !== '');
        const unitNow = row.querySelector('[data-f=unit]').value;
        setUnits(row, item, keepUnit ? unitNow : null);
        if (item && MODE === 'purchase') {
            const rate = row.querySelector('[data-f=rate]');
            if (!rate.value && item.rate) rate.value = item.rate;
        }
        const stock = row.querySelector('[data-stock]');
        if (stock) stock.textContent = item ? (item.stock + ' ' + item.unit) : '—';
        hint(row);
        recalc();
    }

    function hint(row, extra) {
        const h = row.querySelector('[data-hint]');
        const item = byId.get(row.querySelector('[data-f=inventory_item_id]').value);
        const desc = row.querySelector('[data-f=description]').value;
        const parts = [];
        let cls = '';
        if (!item && row.querySelector('.ic-item-input').value.trim()) { parts.push('Not in item list — pick a suggestion.'); cls = 'ic-hint--bad'; }
        if (item && MODE === 'purchase' && item.rate) parts.push('Last rate ₹' + item.rate);
        if (item && MODE === 'request') parts.push('Stock unit: ' + item.unit);
        if (desc && (!item || desc.toLowerCase() !== item.name.toLowerCase())) parts.push('Invoice: “' + desc + '”');
        if (extra) { parts.push(extra.text); cls = extra.cls || cls; }
        h.textContent = parts.join(' · ');
        h.className = 'ic-hint ' + cls;
        const rem = row.querySelector('[data-remember]');
        if (rem) rem.style.display = (item && desc && desc.toLowerCase() !== item.name.toLowerCase()) ? 'flex' : 'none';
    }

    function addRow(data, focus) {
        if (rowsEl.children.length >= MAX) return null;
        const row = tpl.content.firstElementChild.cloneNode(true);
        rowsEl.appendChild(row);
        data = data || {};
        const item = data.inventory_item_id ? byId.get(String(data.inventory_item_id)) : null;
        row.querySelector('.ic-item-input').value = item ? item.name : (data.description || '');
        row.querySelector('[data-f=description]').value = data.description || '';
        row.querySelector('[data-f=quantity]').value = data.quantity ?? '';
        if (MODE === 'purchase') {
            row.querySelector('[data-f=rate]').value = data.rate ?? '';
            row.querySelector('[data-f=tax_percent]').value = data.tax_percent ?? '';
            if (data.remember_alias) row.querySelector('[data-f=remember_alias]').checked = true;
        }
        setUnits(row, item, data.unit);
        row.querySelector('[data-f=inventory_item_id]').value = item ? item.id : '';
        const stock = row.querySelector('[data-stock]');
        if (stock && item) stock.textContent = item.stock + ' ' + item.unit;
        if (!item && data.suggested_item_id && byId.get(String(data.suggested_item_id))) {
            const s = byId.get(String(data.suggested_item_id));
            hint(row, {text: 'Maybe “' + s.name + '” (' + data.confidence + '%) — check and select', cls: 'ic-hint--warn'});
            row.classList.add('--bad');
        } else {
            hint(row);
        }
        renumber();
        if (focus) row.querySelector('.ic-item-input').focus();
        return row;
    }

    function renumber() {
        [...rowsEl.children].forEach((r, i) => {
            r.querySelector('.ic-rownum').textContent = i + 1;
            r.querySelectorAll('[data-f]').forEach(el => el.name = 'lines[' + i + '][' + el.dataset.f + ']');
        });
        recalc();
    }

    function recalc() {
        let sub = 0, tax = 0;
        const seen = new Map();
        [...rowsEl.children].forEach((r, i) => {
            const id = r.querySelector('[data-f=inventory_item_id]').value;
            const q = parseFloat(r.querySelector('[data-f=quantity]').value) || 0;
            if (MODE === 'purchase') {
                const rate = parseFloat(r.querySelector('[data-f=rate]').value) || 0;
                const t = parseFloat(r.querySelector('[data-f=tax_percent]').value) || 0;
                const amt = Math.round(q * rate * 100) / 100;
                r.querySelector('[data-amount]').textContent = fmt(amt);
                sub += amt; tax += amt * t / 100;
            }
            r.classList.remove('--dup');
            if (id) {
                if (seen.has(id)) {
                    r.classList.add('--dup');
                    const h = r.querySelector('[data-hint]');
                    h.textContent = 'Same item as row ' + (seen.get(id) + 1) + ' — check it is not entered twice.';
                    h.className = 'ic-hint ic-hint--warn';
                } else seen.set(id, i);
            }
        });
        document.dispatchEvent(new CustomEvent('ic-grid-total', {detail: {mode: MODE, subtotal: sub, tax: tax, rows: rowsEl.children.length}}));
        save();
    }

    function save() {
        if (!STORAGE_KEY) return;
        try {
            const rows = [...rowsEl.children].map(r => {
                const o = {};
                r.querySelectorAll('[data-f]').forEach(el => o[el.dataset.f] = el.type === 'checkbox' ? el.checked : el.value);
                if (!o.inventory_item_id) o.description = r.querySelector('.ic-item-input').value;
                return o;
            }).filter(o => o.inventory_item_id || o.quantity || o.description);
            if (rows.length) localStorage.setItem(STORAGE_KEY, JSON.stringify({at: Date.now(), rows}));
            else localStorage.removeItem(STORAGE_KEY);
        } catch (e) {}
    }

    async function matchTexts(texts) {
        try {
            const vendor = VENDOR_SEL ? document.querySelector(VENDOR_SEL)?.value : null;
            const res = await fetch(MATCH_URL, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({texts, vendor_id: vendor || null})});
            if (!res.ok) return null;
            return (await res.json()).results;
        } catch (e) { return null; }
    }

    async function handlePaste(row, text) {
        const lines = text.replace(/\r/g, '').split('\n').map(l => l.trim()).filter(Boolean).slice(0, MAX);
        const cells = lines.map(l => l.split('\t'));
        const results = await matchTexts(cells.map(c => c[0])) || [];
        let target = row;
        cells.forEach((c, i) => {
            if (!target) target = addRow({});
            if (!target) return;
            const r = results[i] || {};
            const autoItem = r.auto ? byId.get(String(r.auto)) : null;
            const typed = (c[0] || '').trim();
            target.querySelector('.ic-item-input').value = autoItem ? autoItem.name : typed;
            target.querySelector('[data-f=description]').value = autoItem && autoItem.name.toLowerCase() !== typed.toLowerCase() ? typed : '';
            pickItem(target, autoItem, false);
            if (c[1]) target.querySelector('[data-f=quantity]').value = parseFloat(c[1].replace(/,/g, '')) || '';
            if (c[2]) {
                const sel = target.querySelector('[data-f=unit]');
                const u = c[2].trim().toLowerCase();
                if ([...sel.options].some(o => o.value === u)) sel.value = u;
            }
            if (MODE === 'purchase' && c[3]) target.querySelector('[data-f=rate]').value = parseFloat(c[3].replace(/,/g, '')) || '';
            if (!autoItem && r.suggestions && r.suggestions[0]) {
                hint(target, {text: 'Maybe “' + r.suggestions[0].name + '” (' + r.suggestions[0].confidence + '%) — check and select', cls: 'ic-hint--warn'});
            }
            target = target.nextElementSibling;
        });
        recalc();
    }

    rowsEl.addEventListener('change', e => {
        const row = e.target.closest('.ic-row');
        if (e.target.classList.contains('ic-item-input')) {
            const item = byName.get(e.target.value.trim().toLowerCase());
            if (item && item.name !== e.target.value.trim()) {
                // typed/selected an alias: keep it as the description
                row.querySelector('[data-f=description]').value = row.querySelector('[data-f=description]').value || e.target.value.trim();
                e.target.value = item.name;
            }
            pickItem(row, item || null, false);
        } else recalc();
    });
    rowsEl.addEventListener('input', e => { if (e.target.dataset.f) recalc(); });
    rowsEl.addEventListener('click', e => {
        if (e.target.closest('[data-del]')) {
            e.target.closest('.ic-row').remove();
            if (!rowsEl.children.length) addRow({});
            renumber();
        }
    });
    rowsEl.addEventListener('paste', e => {
        if (!e.target.classList.contains('ic-item-input')) return;
        const text = (e.clipboardData || window.clipboardData).getData('text');
        if (!/[\t\n]/.test(text.trim())) return;
        e.preventDefault();
        handlePaste(e.target.closest('.ic-row'), text);
    });
    rowsEl.addEventListener('keydown', e => {
        if (e.key !== 'Enter' || e.target.tagName === 'BUTTON') return;
        e.preventDefault();
        const row = e.target.closest('.ic-row');
        if (e.target.classList.contains('ic-item-input')) e.target.dispatchEvent(new Event('change', {bubbles: true}));
        const fields = [...row.querySelectorAll('.ic-input, .ic-select')];
        const idx = fields.indexOf(e.target);
        if (idx < fields.length - 1) { fields[idx + 1].focus(); return; }
        const next = row.nextElementSibling || addRow({});
        next?.querySelector('.ic-item-input').focus();
    });
    grid.parentElement.querySelector('[data-add-row]')?.addEventListener('click', () => addRow({}, true));
    grid.parentElement.querySelector('[data-add-rows]')?.addEventListener('click', () => { for (let i = 0; i < 5; i++) addRow({}); });

    if (form) {
        form.addEventListener('submit', e => {
            // drop empty rows; block rows typed but not picked from the list
            [...rowsEl.children].forEach(r => {
                const typed = r.querySelector('.ic-item-input').value.trim();
                const id = r.querySelector('[data-f=inventory_item_id]').value;
                const q = r.querySelector('[data-f=quantity]').value;
                if (!typed && !id && !q) r.remove();
            });
            renumber();
            const bad = [...rowsEl.children].find(r => !r.querySelector('[data-f=inventory_item_id]').value);
            if (bad) {
                e.preventDefault();
                bad.classList.add('--bad');
                hint(bad, {text: 'Choose an item from the list for this row.', cls: 'ic-hint--bad'});
                bad.querySelector('.ic-item-input').focus();
                form.querySelectorAll('[type=submit]').forEach(b => b.disabled = false);
                return;
            }
            if (STORAGE_KEY) { try { localStorage.removeItem(STORAGE_KEY); } catch (err) {} }
        });
    }

    // initial rows (+ offer to restore unsent rows from this device)
    INITIAL.forEach(l => addRow(l));
    if (!INITIAL.length) {
        let restored = null;
        if (STORAGE_KEY) { try { restored = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null'); } catch (e) {} }
        if (restored && restored.rows && restored.rows.length) {
            const banner = document.createElement('div');
            banner.className = 'ic-banner';
            banner.innerHTML = '<i class="bi bi-clock-history"></i><span style="flex:1">You have ' + restored.rows.length + ' unsent row(s) from earlier on this device.</span>';
            const yes = document.createElement('button'); yes.type = 'button'; yes.className = 'ic-btn ic-btn--sm ic-btn--gold'; yes.textContent = 'Restore';
            const no = document.createElement('button'); no.type = 'button'; no.className = 'ic-btn ic-btn--sm'; no.textContent = 'Discard';
            banner.append(yes, no);
            grid.parentElement.insertBefore(banner, grid);
            yes.onclick = () => { rowsEl.innerHTML = ''; restored.rows.forEach(r => addRow(r)); banner.remove(); };
            no.onclick = () => { try { localStorage.removeItem(STORAGE_KEY); } catch (e) {} banner.remove(); };
        }
        for (let i = 0; i < 3; i++) addRow({});
    }
    renumber();
    // external hook: quick chips ("recently used") add an item to the first empty row
    window.icGrids = window.icGrids || {};
    window.icGrids[MODE] = {
        addItem(id) {
            const item = byId.get(String(id));
            if (!item) return;
            const existing = [...rowsEl.children].find(r => r.querySelector('[data-f=inventory_item_id]').value === String(id));
            if (existing) { existing.querySelector('[data-f=quantity]').focus(); return; }
            let row = [...rowsEl.children].find(r => !r.querySelector('[data-f=inventory_item_id]').value && !r.querySelector('.ic-item-input').value.trim());
            row = row || addRow({});
            if (!row) return;
            row.querySelector('.ic-item-input').value = item.name;
            pickItem(row, item, false);
            row.querySelector('[data-f=quantity]').focus();
        },
    };
    setTimeout(recalc, 0); // let page scripts registered later receive the first total
})();
</script>
@endpush
