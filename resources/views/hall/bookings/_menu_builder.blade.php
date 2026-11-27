{{--
  Inline menu builder for hall bookings (create + edit).
  The chosen package (meal plan) is the base; this booking's changes are stored as
  hall_booking_menu_items rows (plan / added / replaced / removed + servings, note, charge).
  The package itself is never modified. Posts menu_present=1 and menu[i][...] on submit
  (only when the booking includes food). Exposes window.efMenuExtrasTotal() and fires
  "ef:menu-change" on document so the page totals can include charged extras.
  Expects: $menuBuilder = ['plans' => [...], 'catalogue' => [...], 'saved' => ?array]
--}}
<div class="mb" id="menuBuilder" data-menu-builder>
    <div class="mb-head">
        <div class="mb-title">
            <h3>Menu for this booking</h3>
            <p id="mbBase">Pick a package above — its dishes appear here. Changes apply to this booking only; the package stays the same.</p>
        </div>
        <span class="mb-flag" id="mbFlag" hidden><i class="bi bi-exclamation-triangle-fill"></i> Customized</span>
    </div>

    <ul class="mb-list" id="mbList" aria-live="polite"></ul>
    <p class="mb-empty" id="mbEmpty">No dishes yet. Choose a package, or add dishes below.</p>

    <div class="mb-add">
        <label class="mb-add-in">
            <i class="bi bi-plus-circle"></i>
            <input type="text" id="mbAddInput" list="mbCatalogue" placeholder="+ Add extra item (e.g. Ice Cream)" autocomplete="off" aria-label="Add extra item" maxlength="150">
        </label>
        <button type="button" class="mb-btn --main" id="mbAddBtn">Add</button>
    </div>
    <datalist id="mbCatalogue">
        @foreach($menuBuilder['catalogue'] as $c)<option value="{{ $c['name'] }}">{{ $c['cat'] }}@if($c['price']) · ₹{{ rtrim(rtrim(number_format($c['price'], 2), '0'), '.') }}@endif</option>@endforeach
    </datalist>

    <div class="mb-sum" id="mbSum" hidden>
        <b>Kitchen will see:</b>
        <ul id="mbSumList"></ul>
        <div class="mb-sum-money" id="mbSumMoney"></div>
    </div>
    <div id="mbInputs"></div>
</div>

@push('styles')
<style>
.mb{margin-top:16px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden}
.mb-head{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-bottom:1px solid var(--ef-border);background:var(--ef-bg-subtle)}
.mb-title{flex:1;min-width:0}
.mb-title h3{margin:0;font-size:.95rem;font-weight:800;color:var(--ef-ink)}
.mb-title p{margin:2px 0 0;font-size:.8rem;color:var(--ef-muted)}
.mb-title p b{color:var(--ef-ink)}
.mb-flag{flex:0 0 auto;display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:8px;background:rgba(214,110,40,.12);color:#a3480f;font-size:.72rem;font-weight:800;white-space:nowrap}
.mb-list{list-style:none;margin:0;padding:0}
.mb-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 10px;align-items:center;padding:9px 14px;border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent)}
.mb-row:first-child{border-top:0}
.mb-row[data-c=added]{--tone:var(--ef-emerald);background:rgba(30,122,60,.03)}
.mb-row[data-c=replaced]{--tone:#d89a3d;background:rgba(200,144,42,.05)}
.mb-row[data-c=removed]{--tone:var(--ef-danger);background:rgba(192,57,43,.03)}
.mb-name{min-width:0;font-size:.9rem;font-weight:700;color:var(--ef-ink);overflow-wrap:anywhere}
.mb-name s{color:var(--ef-muted);font-weight:600}
.mb-name .mb-arrow{color:#b4531c;margin:0 4px}
.mb-sub{display:block;font-size:.74rem;font-weight:600;color:var(--ef-muted);margin-top:1px}
.mb-tag{display:inline-block;margin-right:6px;padding:1px 7px;border-radius:6px;font-size:.64rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;vertical-align:1px}
.mb-tag.--added{background:rgba(30,122,60,.12);color:var(--ef-emerald-dk)}
.mb-tag.--replaced{background:rgba(200,144,42,.16);color:#8a5a10}
.mb-tag.--removed{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.mb-acts{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end}
.mb-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;min-height:36px;padding:4px 11px;border-radius:9px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap}
.mb-btn:hover{border-color:var(--ef-gold)}
.mb-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff;min-height:42px;padding:6px 16px}
.mb-btn.--danger{color:var(--ef-danger)}
.mb-btn.--icon{width:36px;padding:0}
.mb-btn[aria-expanded=true]{background:var(--ef-bg-subtle)}
.mb-more{grid-column:1/-1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding-top:4px}
.mb-more[hidden]{display:none}
.mb-more label{display:flex;flex-direction:column;gap:3px;font-size:.7rem;font-weight:700;color:var(--ef-muted);margin:0;min-width:0}
.mb-more input,.mb-more select{min-height:38px;padding:5px 9px;border-radius:9px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink);width:100%;min-width:0}
.mb-more .--wide{grid-column:1/-1}
.mb-hint{grid-column:1/-1;font-size:.74rem;color:var(--ef-muted)}
.mb-hint button{border:0;background:none;padding:0;color:var(--ef-gold);font-weight:800;cursor:pointer;text-decoration:underline}
.mb-rep{grid-column:1/-1;display:flex;gap:6px}
.mb-rep input{flex:1;min-width:0;min-height:38px;padding:5px 10px;border-radius:9px;border:1px solid var(--ef-gold);font-size:16px}
.mb-empty{margin:0;padding:14px;font-size:.84rem;color:var(--ef-faint)}
.mb-add{display:flex;gap:8px;padding:10px 14px;border-top:1px solid var(--ef-border)}
.mb-add-in{position:relative;flex:1;min-width:0;margin:0}
.mb-add-in .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-emerald)}
.mb-add-in input{width:100%;min-height:42px;padding:6px 12px 6px 36px;border-radius:10px;border:1px dashed var(--ef-border-strong);background:var(--ef-surface);font-size:16px;color:var(--ef-ink)}
.mb-add-in input:focus{border-style:solid;border-color:var(--ef-emerald);outline:none}
.mb-sum{padding:10px 14px;border-top:1px solid var(--ef-border);background:rgba(214,110,40,.05);font-size:.82rem;color:var(--ef-ink-2)}
.mb-sum ul{margin:4px 0 0;padding-left:18px}
.mb-sum-money{margin-top:6px;font-weight:700;color:var(--ef-ink)}
@media (max-width:479.98px){
  .mb-row{grid-template-columns:minmax(0,1fr)}
  .mb-acts{justify-content:flex-start}
  .mb-more{grid-template-columns:minmax(0,1fr)}
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const root = document.getElementById('menuBuilder');
    if (!root) return;
    const PLANS = @json($menuBuilder['plans']);
    const CAT = @json($menuBuilder['catalogue']);
    const SAVED = @json($menuBuilder['saved']);
    const catPrice = n => { const c = CAT.find(x => x.name.toLowerCase() === String(n).trim().toLowerCase()); return c && c.price ? c.price : null; };
    const $ = id => document.getElementById(id);
    const list = $('mbList'), form = root.closest('form');
    const planSel = $('meal_plan_id'), people = $('number_of_people'), typeIn = $('booking_type');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const rupee = n => '₹' + (Math.round(n * 100) / 100).toLocaleString('en-IN');
    const blank = o => Object.assign({ name: '', meal_type: null, change_type: 'plan', replaces: null, servings: null, charge_type: null, charge_amount: 0, notes: null, is_removed: false }, o);

    let lines = [];
    let open = {};          // index -> 'more' | 'replace'
    let baseKey = '';

    function mixedOn() { const t = $('mixedFoodToggle'); return !!(t && t.checked); }
    function planIds() {
        if (mixedOn()) return [...document.querySelectorAll('.ef-food-split-plan')].map(s => s.value).filter(Boolean);
        return planSel && planSel.value ? [planSel.value] : [];
    }
    function baseDishes(ids) {
        const out = [], seen = new Set();
        ids.forEach(id => (PLANS[id]?.dishes || []).forEach(d => { const k = d.toLowerCase(); if (!seen.has(k)) { seen.add(k); out.push(d); } }));
        return out;
    }
    function guests() { return Math.max(0, parseInt(people?.value || 0, 10) || 0); }
    function customized(l) { return l.change_type !== 'plan' || l.servings || l.notes; }
    function customizations() { return lines.filter(customized); }

    // Rebuild the base from the package(s), re-applying this booking's changes where the dish still exists.
    function rebase(ids, keep) {
        const dishes = baseDishes(ids);
        const byName = {};
        keep.forEach(l => { const k = (l.change_type === 'replaced' ? l.replaces : l.name || '').toLowerCase(); if (l.change_type !== 'added') byName[k] = l; });
        const next = dishes.map(d => byName[d.toLowerCase()] ? Object.assign({}, byName[d.toLowerCase()]) : blank({ name: d }));
        keep.filter(l => l.change_type === 'added').forEach(l => next.push(Object.assign({}, l)));
        return next;
    }
    function dropped(ids) {
        const names = new Set(baseDishes(ids).map(d => d.toLowerCase()));
        return customizations().filter(l => l.change_type !== 'added' && !names.has(String(l.change_type === 'replaced' ? l.replaces : l.name).toLowerCase()));
    }

    function onPlanChange(revert) {
        const ids = planIds(), key = ids.join(',');
        if (key === baseKey) return;
        const lost = dropped(ids);
        if (customizations().length && baseKey !== '') {
            const msg = 'Change the package for this booking?\n\nYour menu changes will be kept where they still apply'
                + (lost.length ? '. These no longer match a dish in the new package and will be dropped:\n• ' + lost.map(label).join('\n• ') : '.');
            if (!confirm(msg)) { revert && revert(); return; }
        }
        lines = rebase(ids, customizations().filter(l => !lost.includes(l)));
        baseKey = key; open = {};
        render();
    }

    function label(l) {
        if (l.change_type === 'removed') return 'Removed: ' + l.name;
        if (l.change_type === 'replaced') return (l.replaces || '?') + ' → ' + l.name;
        if (l.change_type === 'added') return 'Extra: ' + l.name;
        return l.name + (l.notes ? ' (' + l.notes + ')' : '');
    }
    function lineCharge(l) {
        if (l.change_type === 'removed' || !['per_person', 'fixed'].includes(l.charge_type)) return 0;
        const amt = Number(l.charge_amount || 0);
        return l.charge_type === 'fixed' ? amt : amt * (Number(l.servings) || guests());
    }
    window.efMenuExtrasTotal = () => lines.reduce((s, l) => s + lineCharge(l), 0);
    function changed() { document.dispatchEvent(new CustomEvent('ef:menu-change')); }

    function rowHtml(l, i) {
        const g = guests(), serv = l.servings || g;
        let name;
        if (l.change_type === 'replaced') name = `<span class="mb-tag --replaced">Replaced</span><s>${esc(l.replaces)}</s><span class="mb-arrow">→</span>${esc(l.name)}`;
        else if (l.change_type === 'removed') name = `<span class="mb-tag --removed">Removed</span><s>${esc(l.name)}</s>`;
        else if (l.change_type === 'added') name = `<span class="mb-tag --added">Extra</span>${esc(l.name)}`;
        else name = esc(l.name);
        const bits = [];
        if (l.change_type !== 'removed' && (l.servings || l.change_type !== 'plan') && serv) bits.push(serv + ' servings' + (l.servings ? '' : ' (all guests)'));
        if (l.change_type !== 'removed' && l.change_type !== 'plan') {
            const c = lineCharge(l);
            bits.push(c > 0 ? (l.charge_type === 'fixed' ? rupee(c) + ' agreed' : '₹' + Number(l.charge_amount) + ' × ' + (l.servings || g) + ' = ' + rupee(c)) : 'no extra charge');
        }
        if (l.notes) bits.push('Note: ' + esc(l.notes));
        let acts = '';
        if (l.change_type === 'plan' || l.change_type === 'modified') acts = `<button type="button" class="mb-btn" data-act="replace" data-i="${i}" aria-expanded="${open[i] === 'replace'}">Replace</button><button type="button" class="mb-btn --danger" data-act="remove" data-i="${i}">Remove</button>`;
        else if (l.change_type === 'added') acts = `<button type="button" class="mb-btn --icon --danger" data-act="delete" data-i="${i}" aria-label="Delete ${esc(l.name)}"><i class="bi bi-x-lg"></i></button>`;
        else acts = `<button type="button" class="mb-btn" data-act="undo" data-i="${i}">Undo</button>`;
        if (l.change_type !== 'removed') acts += `<button type="button" class="mb-btn --icon" data-act="more" data-i="${i}" aria-expanded="${open[i] === 'more'}" aria-label="Servings, note${l.change_type !== 'plan' ? ', price' : ''}"><i class="bi bi-sliders"></i></button>`;
        let extra = '';
        if (open[i] === 'replace') {
            extra = `<div class="mb-rep"><input type="text" list="mbCatalogue" data-rep="${i}" placeholder="Replace ${esc(l.name)} with…" maxlength="150" aria-label="Replacement dish"><button type="button" class="mb-btn --main" data-act="dorep" data-i="${i}">OK</button></div>`;
        } else if (open[i] === 'more') {
            const priced = l.change_type !== 'plan' && l.change_type !== 'modified';
            const cp = catPrice(l.name);
            extra = `<div class="mb-more">
                <label>Servings${l.servings ? '' : ' (= guests)'}<input type="number" min="1" inputmode="numeric" data-f="servings" data-i="${i}" value="${l.servings ?? (g || '')}" placeholder="guest count"></label>
                <label class="${priced ? '' : '--wide'}">Kitchen note<input type="text" maxlength="300" data-f="notes" data-i="${i}" value="${esc(l.notes)}" placeholder="e.g. less spicy"></label>
                ${priced ? `<label>Price<select data-f="charge_type" data-i="${i}">
                        <option value="included" ${!['per_person','fixed'].includes(l.charge_type) ? 'selected' : ''}>Included (no charge)</option>
                        <option value="per_person" ${l.charge_type === 'per_person' ? 'selected' : ''}>₹ per serving</option>
                        <option value="fixed" ${l.charge_type === 'fixed' ? 'selected' : ''}>Fixed amount</option></select></label>
                    <label>Amount (₹)<input type="number" min="0" step="0.01" inputmode="decimal" data-f="charge_amount" data-i="${i}" value="${['per_person','fixed'].includes(l.charge_type) ? Number(l.charge_amount || 0) : ''}" ${['per_person','fixed'].includes(l.charge_type) ? '' : 'disabled'}></label>
                    ${cp && l.charge_type !== 'per_person' ? `<div class="mb-hint">Customer menu price: ₹${cp}/person — <button type="button" data-act="useprice" data-i="${i}" data-p="${cp}">use it</button></div>` : ''}` : ''}
            </div>`;
        }
        return `<li class="mb-row" data-c="${l.change_type}" data-line="${i}"><div class="mb-name">${name}${bits.length ? `<span class="mb-sub">${bits.join(' · ')}</span>` : ''}</div><div class="mb-acts">${acts}</div>${extra}</li>`;
    }

    function render() {
        const ids = planIds();
        const names = ids.map(id => PLANS[id]).filter(Boolean);
        $('mbBase').innerHTML = names.length
            ? 'Package: <b>' + names.map(p => esc(p.name) + (p.price ? ' – ₹' + p.price.toLocaleString('en-IN') : '')).join(', ') + '</b> · ' + baseDishes(ids).length + ' dishes. Changes apply to this booking only.'
            : 'No package selected — add the dishes for this booking below, or pick a package above.';
        list.innerHTML = lines.map(rowHtml).join('');
        $('mbEmpty').hidden = lines.length > 0;
        const cz = customizations();
        $('mbFlag').hidden = cz.length === 0;
        $('mbSum').hidden = cz.length === 0;
        $('mbSumList').innerHTML = cz.map(l => '<li>' + esc(label(l)) + (l.servings ? ' – ' + l.servings + ' servings' : (l.change_type === 'added' && guests() ? ' – ' + guests() + ' servings' : '')) + '</li>').join('');
        const extra = window.efMenuExtrasTotal();
        $('mbSumMoney').textContent = extra > 0 ? 'Menu extras charge: ' + rupee(extra) + ' (added to the total)' : 'No extra charge — package price unchanged.';
        changed();
    }

    function add(name) {
        name = String(name || '').trim();
        if (!name) return;
        lines.push(blank({ name, change_type: 'added', charge_type: 'included' }));
        open = { [lines.length - 1]: 'more' };
        render();
    }

    root.addEventListener('click', e => {
        const b = e.target.closest('[data-act]'); if (!b) return;
        const i = +b.dataset.i, l = lines[i], act = b.dataset.act;
        if (act === 'replace') { open = open[i] === 'replace' ? {} : { [i]: 'replace' }; render(); list.querySelector(`[data-rep="${i}"]`)?.focus(); }
        else if (act === 'more') { open = open[i] === 'more' ? {} : { [i]: 'more' }; render(); }
        else if (act === 'remove') { Object.assign(l, { change_type: 'removed', is_removed: true }); open = {}; render(); }
        else if (act === 'delete') { lines.splice(i, 1); open = {}; render(); }
        else if (act === 'undo') {
            if (l.change_type === 'replaced') Object.assign(l, { name: l.replaces, replaces: null, charge_type: null, charge_amount: 0 });
            Object.assign(l, { change_type: 'plan', is_removed: false }); open = {}; render();
        }
        else if (act === 'dorep') doReplace(i);
        else if (act === 'useprice') { Object.assign(l, { charge_type: 'per_person', charge_amount: +b.dataset.p }); render(); }
    });
    function doReplace(i) {
        const inp = list.querySelector(`[data-rep="${i}"]`), v = (inp?.value || '').trim(), l = lines[i];
        if (!v || v.toLowerCase() === l.name.toLowerCase()) { inp?.focus(); return; }
        Object.assign(l, { replaces: l.name, name: v, change_type: 'replaced', is_removed: false, charge_type: 'included', charge_amount: 0 });
        open = {}; render();
    }
    root.addEventListener('keydown', e => {
        if (e.key !== 'Enter') return;
        if (e.target.dataset.rep !== undefined) { e.preventDefault(); doReplace(+e.target.dataset.rep); }
        else if (e.target.id === 'mbAddInput') { e.preventDefault(); add(e.target.value); e.target.value = ''; }
        else if (e.target.dataset.f) { e.preventDefault(); e.target.blur(); }
    });
    root.addEventListener('change', e => {
        const f = e.target.dataset.f; if (!f) return;
        const l = lines[+e.target.dataset.i], v = e.target.value;
        // same as the guest count (or cleared) → keep following the guest count
        if (f === 'servings') { const n = v ? Math.max(1, parseInt(v, 10)) : null; l.servings = n && n !== guests() ? n : null; }
        else if (f === 'notes') l.notes = v.trim() || null;
        else if (f === 'charge_type') { l.charge_type = v; if (v === 'per_person' && !Number(l.charge_amount)) l.charge_amount = catPrice(l.name) || 0; if (v === 'included') l.charge_amount = 0; }
        else if (f === 'charge_amount') l.charge_amount = Math.max(0, Number(v || 0));
        render();
    });
    $('mbAddBtn').addEventListener('click', () => { const inp = $('mbAddInput'); add(inp.value); inp.value = ''; inp.focus(); });

    // package / guest changes
    let prevPlan = planSel ? planSel.value : '';
    function revertPlan() {
        if (!planSel) return;
        planSel.value = prevPlan;
        document.querySelectorAll('.ef-eb-meal-card').forEach(c => c.classList.toggle('selected', (c.dataset.id || '') === String(prevPlan)));
        if (typeof window.recalcTotal === 'function') window.recalcTotal();
        else planSel.dispatchEvent(new Event('input', { bubbles: true }));
    }
    planSel?.addEventListener('change', () => { onPlanChange(revertPlan); prevPlan = planSel.value; });
    document.addEventListener('change', e => { if (e.target.matches('.ef-food-split-plan, #mixedFoodToggle')) onPlanChange(); });
    document.addEventListener('ef:splits-change', () => onPlanChange());
    people?.addEventListener('input', render);

    // submit: post the lines (only when the booking includes food)
    form?.addEventListener('submit', () => {
        const box = $('mbInputs'); box.innerHTML = '';
        if ((typeIn?.value || 'hall_food') === 'hall_only') return;
        const put = (n, v) => { const h = document.createElement('input'); h.type = 'hidden'; h.name = n; h.value = v ?? ''; box.appendChild(h); };
        put('menu_present', '1');
        lines.forEach((l, i) => {
            ['name', 'meal_type', 'change_type', 'replaces', 'servings', 'charge_type', 'charge_amount', 'notes'].forEach(k => {
                if (l[k] !== null && l[k] !== undefined && l[k] !== '') put(`menu[${i}][${k}]`, l[k]);
            });
        });
    });

    // init: saved lines (edit / validation error) or the package dishes
    if (Array.isArray(SAVED)) {
        lines = SAVED.map(l => blank(Object.assign({}, l, {
            change_type: l.is_removed && l.change_type !== 'removed' ? 'removed' : (l.change_type || 'plan'),
            servings: l.servings ? +l.servings : null, charge_amount: Number(l.charge_amount || 0),
        })));
        baseKey = planIds().join(',');
    } else {
        baseKey = planIds().join(',');
        lines = rebase(planIds(), []);
    }
    render();
})();
</script>
@endpush
