{{--
    Reusable item search box (English / Tamil / Tanglish) backed by inventory.items.search,
    i.e. App\Services\Inventory\ItemSearchService. Include once per page, then:
        IcItemPicker.attach(inputEl, { onPick(item), onUnresolved(text, response), allowCreate, onCreate(item) })
    - allowCreate: shows "+ Add “…” as new item" (popup: name, Tamil name, category, unit → inventory.items.quick).
      onCreate(item) runs before onPick so a page can add the new item to its own catalogue.
    - never auto-selects while typing; Enter / blur picks only a single exact match
    - keyboard: ↑ ↓ Enter Esc · touch: 44px options
--}}
@once
{{-- Emitted inline (not pushed) so it is defined before any page script in the 'scripts' stack. --}}
<style>
.ic-pick-wrap{position:relative}
.ic-pick-list{position:absolute;left:0;right:0;top:calc(100% + 4px);z-index:1060;background:var(--ef-card,#fff);border:1px solid var(--ef-line,#e5e0d6);border-radius:12px;box-shadow:0 12px 32px rgba(17,17,17,.14);max-height:min(52vh,360px);overflow-y:auto;padding:4px;display:none;min-width:240px}
.ic-pick-list.--open{display:block}
.ic-pick-opt{display:block;width:100%;text-align:left;background:none;border:0;border-radius:9px;padding:9px 10px;min-height:44px;cursor:pointer;color:var(--ef-ink,#111)}
.ic-pick-opt:hover,.ic-pick-opt.--active{background:rgba(184,137,62,.12)}
.ic-pick-name{font-weight:650;font-size:.92rem;line-height:1.25}
.ic-pick-ta{display:block;font-weight:500;font-size:.88rem;color:var(--ef-muted,#6b6459);margin-top:1px}
.ic-pick-wrap>.ic-pick-clear{position:absolute;right:4px;top:4px;z-index:2;width:38px;height:38px;border:0;background:none;color:var(--ef-faint,#8d867a);border-radius:8px;display:none;align-items:center;justify-content:center;font-size:1.1rem;cursor:pointer}
.ic-pick-wrap.--has>.ic-pick-clear{display:flex}
.ic-pick-wrap.--has>input{padding-right:40px}
.ic-pick-meta{display:flex;flex-wrap:wrap;gap:4px 10px;margin-top:3px;font-size:.74rem;color:var(--ef-muted,#6b6459)}
.ic-pick-meta b{font-weight:650;color:var(--ef-gold,#b8893e)}
.ic-pick-empty{padding:12px;font-size:.84rem;color:var(--ef-muted,#6b6459)}
.ic-pick-add{display:flex;align-items:center;gap:8px;width:100%;text-align:left;border:0;border-top:1px solid var(--ef-line,#e5e0d6);background:none;padding:10px;min-height:46px;margin-top:2px;color:var(--ef-gold,#b8893e);font-weight:750;font-size:.9rem;cursor:pointer;border-radius:0 0 9px 9px}
.ic-pick-add:hover,.ic-pick-add.--active{background:rgba(184,137,62,.12)}
.ic-qa{border:0;border-radius:16px;padding:0;width:min(440px,calc(100vw - 24px));max-height:calc(100dvh - 24px);box-shadow:0 24px 60px rgba(0,0,0,.28);color:var(--ef-ink,#111);background:var(--ef-card,#fff)}
.ic-qa::backdrop{background:rgba(17,17,17,.45)}
.ic-qa form{display:flex;flex-direction:column;gap:12px;padding:18px}
.ic-qa h3{margin:0;font-size:1.1rem;font-weight:800}
.ic-qa p{margin:-6px 0 0;font-size:.84rem;color:var(--ef-muted,#6b6459)}
.ic-qa label{display:flex;flex-direction:column;gap:4px;font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:var(--ef-muted,#6b6459)}
.ic-qa input,.ic-qa select{min-height:46px;font-size:16px;text-transform:none;letter-spacing:0;font-weight:500}
.ic-qa-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.ic-qa-acts{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap}
.ic-qa-acts .ic-btn{min-height:46px}
.ic-qa-sim{border:1px solid rgba(216,154,61,.5);background:rgba(216,154,61,.1);border-radius:12px;padding:10px 12px;font-size:.88rem}
.ic-qa-sim[hidden],.ic-qa-err[hidden]{display:none}
.ic-qa-sim strong{display:block;margin-bottom:6px;color:#7D5218}
.ic-qa-sim button{display:block;width:100%;text-align:left;border:1px solid var(--ef-line,#e5e0d6);background:var(--ef-card,#fff);border-radius:10px;padding:8px 10px;margin-bottom:6px;min-height:44px;cursor:pointer;font-weight:650}
.ic-qa-sim button small{display:block;font-weight:500;color:var(--ef-muted,#6b6459)}
.ic-qa-err{color:var(--ef-danger,#c0392b);font-size:.86rem;font-weight:600}
.ic-pick-head{padding:6px 10px 2px;font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-faint,#8d867a)}
</style>
<script>
window.IcItemPicker = window.IcItemPicker || (function () {
    const URL_ = @json(route('inventory.items.search'));
    const QA = {
        url: @json(route('inventory.items.quick')),
        admin: @json((bool) auth()->user()?->isAdmin()),
        cats: @json(\App\Models\InventoryCategory::active()->orderBy('name')->get(['id', 'name'])),
        units: @json(collect(config('inventory.units'))->map(fn ($u, $k) => ['k' => $k, 'l' => $u['label']])->values()),
    };
    const cache = new Map();
    let seq = 0;
    async function query(q) {
        const k = q.trim().toLowerCase();
        if (!k) return {results: [], exact_id: null, ambiguous: false};
        if (cache.has(k)) return cache.get(k);
        try {
            const r = await fetch(URL_ + '?q=' + encodeURIComponent(q.trim()), {headers: {'Accept': 'application/json'}});
            if (!r.ok) return null;
            const data = await r.json();
            cache.set(k, data);
            return data;
        } catch (e) { return null; }
    }
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

    function attach(input, opts) {
        if (!input || input.dataset.picker) return;
        input.dataset.picker = '1';
        opts = opts || {};
        const host = input.parentElement;
        host.classList.add('ic-pick-wrap');
        const list = document.createElement('div');
        list.className = 'ic-pick-list';
        list.id = 'icpick' + Math.random().toString(36).slice(2, 9);
        list.setAttribute('role', 'listbox');
        input.after(list);
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-controls', list.id);
        input.setAttribute('aria-expanded', 'false');
        input.autocomplete = 'off';
        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'ic-pick-clear';
        clear.setAttribute('aria-label', 'Clear item search');
        clear.innerHTML = '<i class="bi bi-x-lg"></i>';
        list.after(clear);
        const syncClear = () => {
            host.classList.toggle('--has', !!input.value);
            // centre on the input itself (a label may sit above it inside the same wrapper)
            clear.style.top = (input.offsetTop + (input.offsetHeight - 38) / 2) + 'px';
            clear.style.right = (host.clientWidth - input.offsetLeft - input.offsetWidth + 4) + 'px';
        };
        clear.addEventListener('mousedown', e => e.preventDefault());
        clear.addEventListener('click', () => {
            input.value = '';
            input.dispatchEvent(new Event('input', {bubbles: true}));
            input.dispatchEvent(new Event('change', {bubbles: true}));
            input.focus();
        });
        syncClear();
        let last = null, active = -1, timer = null, picked = null;

        function fit() {
            // keep the list above any sticky action bar and inside the viewport
            const bar = document.querySelector('.ic-sticky');
            const limit = bar ? Math.min(innerHeight, bar.getBoundingClientRect().top) : innerHeight;
            const room = limit - input.getBoundingClientRect().bottom - 12;
            list.style.maxHeight = Math.max(140, Math.min(360, room)) + 'px';
        }
        function open(v) { if (v) fit(); list.classList.toggle('--open', v); input.setAttribute('aria-expanded', v ? 'true' : 'false'); if (!v) active = -1; }
        function render(data) {
            last = data;
            if (!data) { list.innerHTML = '<div class="ic-pick-empty">Search is unavailable right now — try again.</div>'; open(true); return; }
            const addBtn = opts.allowCreate && !data.exact_id
                ? '<button type="button" class="ic-pick-add" data-add><i class="bi bi-plus-circle"></i> Add “' + esc(input.value.trim()) + '” as new item</button>' : '';
            if (!data.results.length) {
                list.innerHTML = '<div class="ic-pick-empty">No item matches “' + esc(input.value.trim()) + '”.' + (opts.allowCreate ? '' : ' Check the spelling, try the Tamil name, or ask an admin to add it.') + '</div>' + addBtn;
                open(true); return;
            }
            const head = data.results.length > 1 && !data.exact_id ? '<div class="ic-pick-head">' + data.results.length + ' matching items — choose one</div>' : '';
            list.innerHTML = head + data.results.map((r, i) =>
                '<button type="button" role="option" class="ic-pick-opt" data-i="' + i + '" id="' + list.id + '-' + i + '">' +
                '<div class="ic-pick-name">' + esc(r.name) + (r.name_ta ? '<span class="ic-pick-ta" lang="ta">' + esc(r.name_ta) + '</span>' : '') + '</div>' +
                '<div class="ic-pick-meta"><span>' + (r.category ? esc(r.category) + ' · ' : '') + esc(r.unit) + '</span>' +
                (r.matched ? '<span>Matched: <b>' + esc(r.matched) + '</b></span>' : '') +
                (r.status !== 'active' ? '<span>Inactive</span>' : '') + '</div></button>').join('') + addBtn;
            open(true);
            highlight(data.exact_id ? 0 : -1);
        }
        function highlight(i) {
            const opts_ = [...list.querySelectorAll('.ic-pick-opt')];
            active = Math.max(-1, Math.min(i, opts_.length - 1));
            opts_.forEach((o, j) => o.classList.toggle('--active', j === active));
            if (active >= 0) { opts_[active].scrollIntoView({block: 'nearest'}); input.setAttribute('aria-activedescendant', opts_[active].id); }
            else input.removeAttribute('aria-activedescendant');
        }
        function choose(i) {
            const r = last?.results?.[i];
            if (!r) return;
            picked = r;
            input.value = r.name;
            syncClear();
            open(false);
            opts.onPick && opts.onPick(r, input.dataset.typed || '');
        }
        async function lookup() {
            const my = ++seq;
            const data = await query(input.value);
            if (my !== seq || document.activeElement !== input) return;
            render(data);
        }
        input.addEventListener('input', () => {
            syncClear();
            picked = null;
            input.dataset.typed = input.value.trim();
            clearTimeout(timer);
            if (!input.value.trim()) { open(false); opts.onClear && opts.onClear(); return; }
            timer = setTimeout(lookup, 140);
        });
        input.addEventListener('focus', () => {
            syncClear();
            // phones: lift the field towards the top so results have room above the keyboard / action bar
            if (innerWidth < 768) {
                if (input.getBoundingClientRect().top > innerHeight * 0.35) {
                    input.style.scrollMarginTop = '84px';   // clear the fixed app header
                    input.scrollIntoView({block: 'start', behavior: 'smooth'});
                }
            }
            if (input.value.trim() && !picked && last) open(true);
        });
        input.addEventListener('keydown', e => {
            const isOpen = list.classList.contains('--open');
            if (e.key === 'ArrowDown') { e.preventDefault(); if (!isOpen) lookup(); else highlight(active + 1); }
            else if (e.key === 'ArrowUp' && isOpen) { e.preventDefault(); highlight(active - 1); }
            else if (e.key === 'Escape' && isOpen) { e.preventDefault(); e.stopPropagation(); open(false); }
            else if (e.key === 'Enter' && isOpen) {
                if (active >= 0) { e.preventDefault(); e.stopPropagation(); choose(active); }
                else if (last && !last.exact_id) { e.preventDefault(); e.stopPropagation(); } // ambiguous: make the user choose
            }
        });
        list.addEventListener('mousedown', e => e.preventDefault()); // keep focus so blur does not close first
        list.addEventListener('click', e => {
            const b = e.target.closest('.ic-pick-opt'); if (b) { choose(+b.dataset.i); return; }
            if (e.target.closest('[data-add]')) { open(false); quickAdd(input.value.trim(), created); }
        });
        function created(r) {
            picked = r; input.value = r.name; input.dataset.typed = r.name; syncClear(); open(false);
            cache.clear();
            opts.onCreate && opts.onCreate(r);
            opts.onPick && opts.onPick(r, '');
        }
        input.addEventListener('blur', () => setTimeout(() => open(false), 120));

        return {
            /** Resolve typed text without user choice: only a single exact match is accepted. */
            async resolve() {
                const text = input.value.trim();
                if (!text) return null;
                if (picked && picked.name === text) return picked;
                const data = await query(text);
                const hit = data && data.exact_id ? data.results.find(r => r.id === data.exact_id) : null;
                if (hit) { picked = hit; input.dataset.typed = text; input.value = hit.name; syncClear(); opts.onPick && opts.onPick(hit, text); return hit; }
                opts.onUnresolved && opts.onUnresolved(text, data);
                return null;
            },
            setPicked(r) { picked = r; syncClear(); },
        };
    }
    let dlg = null;
    function quickAdd(text, done) {
        if (!dlg) {
            dlg = document.createElement('dialog');
            dlg.className = 'ic-qa';
            dlg.innerHTML =
                '<form method="dialog" novalidate><h3>Add new item</h3>' +
                '<p>' + (QA.admin ? 'It goes straight into the item catalogue.' : 'You can use it right away. An admin will check it later.') + '</p>' +
                '<label>Item name<input class="ic-input" name="name" required maxlength="255" autocomplete="off"></label>' +
                '<label>Tamil name <span style="text-transform:none;font-weight:500">(optional)</span><input class="ic-input" name="name_ta" maxlength="200" lang="ta" autocomplete="off"></label>' +
                '<div class="ic-qa-row"><label>Category<select class="ic-select" name="inventory_category_id" required><option value="">Choose…</option>' +
                QA.cats.map(c => '<option value="' + c.id + '">' + esc(c.name) + '</option>').join('') + '</select></label>' +
                '<label>Unit<select class="ic-select" name="unit" required><option value="">Choose…</option>' +
                QA.units.map(u => '<option value="' + u.k + '">' + esc(u.l) + '</option>').join('') + '</select></label></div>' +
                '<div class="ic-qa-sim" data-sim hidden></div><div class="ic-qa-err" data-err hidden></div>' +
                '<div class="ic-qa-acts"><button type="button" class="ic-btn" data-cancel>Cancel</button>' +
                '<button type="submit" class="ic-btn ic-btn--gold" data-save data-no-loading><i class="bi bi-plus-lg"></i> Add item</button></div></form>';
            document.body.appendChild(dlg);
            dlg.querySelector('[data-cancel]').onclick = () => dlg.close();
        }
        const f = dlg.querySelector('form'), sim = dlg.querySelector('[data-sim]'), err = dlg.querySelector('[data-err]'), save = dlg.querySelector('[data-save]');
        f.reset(); f.elements.name.value = text; sim.hidden = err.hidden = true; save.disabled = false;
        let confirm = false;
        const fail = m => { err.textContent = m; err.hidden = false; };
        f.onsubmit = async e => {
            e.preventDefault(); err.hidden = true;
            const body = Object.fromEntries(new FormData(f));
            if (!body.name.trim() || !body.inventory_category_id || !body.unit) { fail('Fill in name, category and unit.'); return; }
            if (confirm) body.confirm = 1;
            save.disabled = true;
            try {
                const r = await fetch(QA.url, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''}, body: JSON.stringify(body)});
                const d = await r.json().catch(() => ({}));
                if (r.status === 409 && d.similar) {
                    sim.innerHTML = '<strong>Is it one of these? Tap to use it.</strong>' + d.similar.map((x, i) =>
                        '<button type="button" data-i="' + i + '">' + esc(x.name) + (x.name_ta ? ' · <span lang="ta">' + esc(x.name_ta) + '</span>' : '') +
                        '<small>' + esc((x.category ? x.category + ' · ' : '') + x.unit) + '</small></button>').join('') +
                        '<div style="font-size:.82rem;margin-top:2px">None of these? Tap <b>Add anyway</b>.</div>';
                    sim.hidden = false;
                    sim.querySelectorAll('button').forEach(b => b.onclick = () => { dlg.close(); done(d.similar[+b.dataset.i]); });
                    confirm = true; save.innerHTML = '<i class="bi bi-plus-lg"></i> Add anyway';
                } else if (r.ok && d.item) {
                    dlg.close(); done(d.item);
                } else {
                    fail(d.message || (d.errors ? Object.values(d.errors)[0][0] : 'Could not add the item — try again.'));
                }
            } catch (_) { fail('No connection — try again.'); }
            save.disabled = false;
        };
        save.innerHTML = '<i class="bi bi-plus-lg"></i> Add item';
        dlg.showModal();
        setTimeout(() => f.elements.name_ta.focus(), 50);
    }

    return {attach, query};
})();
</script>
@endonce
