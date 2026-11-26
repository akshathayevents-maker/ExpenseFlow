<x-admin-layout :title="$mr ? 'Edit ' . $mr->reference() : 'Request Materials'">
@include('inventory._styles')
@php
    $v = fn ($k, $d = null) => old($k, $prefill[$k] ?? $d);
    $source = $v('source', 'other');
    // Several preparations, each with its own head count (older requests: the single name / people pair).
    $preps = old('preparations') ?? ($prefill['preparations'] ?? null);
    if (! $preps) {
        $preps = [['name' => $v('preparation_name'), 'people' => $v('guest_count')]];
    }
    $preps = array_values(array_map(fn ($r) => ['name' => $r['name'] ?? '', 'people' => $r['people'] ?? ''], (array) $preps));
@endphp
@push('styles')
<style>
.mrh-note{display:flex;flex-direction:column;gap:6px;margin:0 0 12px;padding:10px 12px;border:1px solid rgba(184,137,62,.35);background:rgba(184,137,62,.08);border-radius:12px;font-size:.86rem;line-height:1.4;color:var(--ef-ink)}
.mrh-note[hidden]{display:none}
.mrh-note .bi-magic{color:var(--ef-gold)}
.mrh-note .--warn{color:#7D5218}
.mrh-note a{font-weight:700}
.mrp-list{display:grid;gap:8px;margin-bottom:12px}
.mrp-head,.mrp-row{display:grid;grid-template-columns:minmax(0,1fr) 116px 44px;gap:8px;align-items:start}
.mrp-head{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;color:var(--ef-muted)}
.mrp-row .ic-input{font-size:16px}
.mrp-num{position:relative}
.mrp-num .ic-input{padding-right:34px;text-align:right}
.mrp-num i{position:absolute;right:11px;top:14px;color:var(--ef-faint);pointer-events:none}
.mrp-del{width:44px;height:46px;border-radius:10px;border:1.5px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-faint);font-size:1.05rem;cursor:pointer}
.mrp-del:hover{border-color:var(--ef-danger);color:var(--ef-danger)}
.mrp-err{grid-column:1 / -1;color:var(--ef-danger);font-size:.78rem;font-weight:600;margin-top:-2px}
.mrp-foot{display:flex;flex-wrap:wrap;align-items:center;gap:8px 14px}
.mrp-sum{font-size:.84rem;color:var(--ef-muted)}
@media (max-width:359.98px){.mrp-head,.mrp-row{grid-template-columns:minmax(0,1fr) 96px 44px}}
</style>
@endpush
<div class="ic-wrap" style="max-width:960px">
    <div class="ic-head">
        <a class="ic-back" href="{{ $mr ? route('inventory.material-requests.show', $mr) : route('inventory.home') }}"><i class="bi bi-arrow-left"></i> Back</a>
        <h1>{{ $mr ? 'Edit ' . $mr->reference() : 'Request materials from store' }}</h1>
    </div>

    @if($errors->any())
        <div class="ic-err"><strong>Please fix:</strong><ul>@foreach(collect($errors->all())->unique()->take(12) as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" data-ic-form action="{{ $mr ? route('inventory.material-requests.update', $mr) : route('inventory.material-requests.store') }}">
        @csrf
        @if($mr) @method('PUT') @endif
        <input type="hidden" name="client_token" value="{{ old('client_token', $token) }}">
        @if(! empty($prefill['recipe_id']))<input type="hidden" name="recipe_id" value="{{ old('recipe_id', $prefill['recipe_id']) }}">@endif

        {{-- Step 1: what is it for --}}
        <div class="ic-card">
            <h2>1 · What is it for?</h2>
            <div class="ic-tabs" role="radiogroup">
                @foreach(['meal' => ['bi-egg-fried', 'Daily meal'], 'hall' => ['bi-stars', 'Hall order'], 'other' => ['bi-box', 'Other']] as $k => [$icon, $label])
                    <label class="ic-tab {{ $source === $k ? '--on' : '' }}" style="cursor:pointer;min-height:44px;display:flex;align-items:center;gap:6px">
                        <input type="radio" name="source" value="{{ $k }}" @checked($source === $k) style="display:none" data-source> <i class="bi {{ $icon }}"></i> {{ $label }}
                    </label>
                @endforeach
            </div>
            <div class="ic-fields">
                <div data-for="meal">
                    <label class="ic-label" for="meal_entry_id">Meal register entry</label>
                    <select class="ic-select" id="meal_entry_id" name="meal_entry_id">
                        <option value="">Choose…</option>
                        @foreach($mealEntries as $e)
                            <option value="{{ $e->id }}" data-date="{{ $e->entry_date->toDateString() }}" @selected((string) $v('meal_entry_id') === (string) $e->id)>{{ $e->entry_date->format('D d M') }} · {{ $e->client?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-for="meal">
                    <label class="ic-label" for="meal_type">Meal</label>
                    <select class="ic-select" id="meal_type" name="meal_type">
                        @foreach($mealTypes as $k => $m)
                            <option value="{{ $k }}" @selected($v('meal_type') === $k)>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-for="hall">
                    <label class="ic-label" for="hall_booking_id">Hall order</label>
                    <select class="ic-select" id="hall_booking_id" name="hall_booking_id">
                        <option value="">Choose…</option>
                        @foreach($bookings as $b)
                            <option value="{{ $b->id }}" data-date="{{ $b->booking_date->toDateString() }}" data-guests="{{ $b->number_of_people }}" @selected((string) $v('hall_booking_id') === (string) $b->id)>{{ $b->booking_date->format('D d M') }} · {{ $b->customer_name }} · {{ $b->number_of_people }} guests</option>
                        @endforeach
                    </select>
                </div>
                <div data-for="other">
                    <label class="ic-label" for="purpose">Purpose</label>
                    <input class="ic-input" id="purpose" name="purpose" value="{{ $v('purpose') }}" maxlength="200" placeholder="e.g. Staff tea, cleaning">
                </div>
            </div>
        </div>

        {{-- Step 2: details --}}
        <div class="ic-card">
            <h2>2 · Details</h2>
            <div class="mrp-list" id="mrpList" aria-label="Preparations">
                <div class="mrp-head" aria-hidden="true"><span>Preparation</span><span>People</span><span></span></div>
                @foreach($preps as $i => $p)
                    <div class="mrp-row" data-prep>
                        <input class="ic-input" name="preparations[{{ $i }}][name]" value="{{ $p['name'] }}" maxlength="150"
                               placeholder="{{ $i === 0 ? 'e.g. Upma' : 'e.g. Coffee' }}" aria-label="Preparation {{ $i + 1 }}">
                        <div class="mrp-num">
                            <input class="ic-input" type="number" min="1" step="1" inputmode="numeric" data-prep-people
                                   name="preparations[{{ $i }}][people]" value="{{ $p['people'] }}" placeholder="0" aria-label="Number of people for preparation {{ $i + 1 }}">
                            <i class="bi bi-people" aria-hidden="true"></i>
                        </div>
                        <button type="button" class="mrp-del" data-prep-del aria-label="Remove preparation {{ $i + 1 }}" title="Remove"><i class="bi bi-x-lg"></i></button>
                        @foreach(['name', 'people'] as $f)
                            @error("preparations.$i.$f")<div class="mrp-err">{{ $message }}</div>@enderror
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="mrp-foot" style="margin-bottom:14px">
                <button type="button" class="ic-btn ic-btn--sm" id="mrpAdd"><i class="bi bi-plus-lg"></i> Add preparation</button>
                <span class="mrp-sum" id="mrpSum" aria-live="polite"></span>
            </div>
            <template id="mrpTpl">
                <div class="mrp-row" data-prep>
                    <input class="ic-input" name="preparations[__I__][name]" maxlength="150" placeholder="e.g. Coffee" aria-label="Preparation">
                    <div class="mrp-num">
                        <input class="ic-input" type="number" min="1" step="1" inputmode="numeric" data-prep-people name="preparations[__I__][people]" placeholder="0" aria-label="Number of people">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>
                    <button type="button" class="mrp-del" data-prep-del aria-label="Remove preparation" title="Remove"><i class="bi bi-x-lg"></i></button>
                </div>
            </template>
            <div class="ic-fields">
                <div>
                    <label class="ic-label" for="required_date">Needed on</label>
                    <input class="ic-input" type="date" id="required_date" name="required_date" value="{{ $v('required_date') }}" required>
                </div>
                <div>
                    <label class="ic-label" for="required_time">Time</label>
                    <input class="ic-input" type="time" id="required_time" name="required_time" value="{{ $v('required_time') ? substr($v('required_time'), 0, 5) : '' }}">
                </div>
                <div>
                    <label class="ic-label" for="department">Section</label>
                    <select class="ic-select" id="department" name="department">
                        @foreach(\App\Models\MaterialRequest::DEPARTMENTS as $k => $l)
                            <option value="{{ $k }}" @selected($v('department', 'kitchen') === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($recipes->isNotEmpty() && ! $mr)
                <div class="ic-note" style="margin-top:10px">
                    Have a standard recipe? <select class="ic-select" id="recipeSuggest" style="display:inline-block;width:auto;min-height:38px">
                        <option value="">Suggest quantities from…</option>
                        @foreach($recipes as $rc)<option value="{{ $rc->id }}" @selected(($prefill['recipe_id'] ?? null) == $rc->id)>{{ $rc->name }} (v{{ $rc->version }}){{ $rc->isVerified() ? ' ✓ Verified' : '' }}</option>@endforeach
                    </select>
                    <span>(you can still change every quantity)</span>
                </div>
            @endif
            @if($suggestion)
                <div class="ic-banner" style="margin-top:10px"><i class="bi bi-lightbulb"></i>
                    <span>Quantities suggested from <strong>{{ $suggestion['recipe'] }}</strong> v{{ $suggestion['version'] }} for {{ $suggestion['guests'] }} people. Check and change as needed.</span></div>
            @endif
        </div>

        {{-- Step 3: materials --}}
        <div class="ic-card">
            <h2>3 · Materials</h2>
            <div class="mrh-note" id="hallNote" hidden aria-live="polite"></div>
            @if(! empty($quickItems['recent']) || ! empty($quickItems['frequent']))
                <div style="margin-bottom:10px">
                    <span class="ic-label">Tap to add</span>
                    <div class="ic-tabs" style="flex-wrap:wrap">
                        @foreach(collect($quickItems['frequent'])->merge($quickItems['recent'])->unique('id')->take(12) as $q)
                            <button type="button" class="ic-tab" data-quick="{{ $q['id'] }}"><i class="bi bi-plus"></i> {{ $q['name'] }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
            @include('inventory._grid', [
                'mode' => 'request', 'catalog' => $catalog, 'lines' => $lines,
                'storageKey' => $mr ? null : 'ic-request-draft-' . auth()->id(), 'vendorSelect' => null,
            ])
            <div style="margin-top:12px">
                <label class="ic-label" for="notes">Notes for the approver</label>
                <input class="ic-input" id="notes" name="notes" value="{{ $v('notes') }}" maxlength="1000">
            </div>
        </div>

        <div class="ic-sticky">
            <span class="ic-total" style="font-size:1rem"><small>Materials</small><span id="icRows">0</span> item(s)</span>
            <button type="submit" name="action" value="draft" class="ic-btn"><i class="bi bi-save"></i> Save draft</button>
            <button type="submit" name="action" value="submit" class="ic-btn ic-btn--gold"><i class="bi bi-send"></i> Send for approval</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const radios = document.querySelectorAll('[data-source]');
    function show() {
        const v = document.querySelector('[data-source]:checked')?.value || 'other';
        document.querySelectorAll('[data-for]').forEach(el => el.style.display = el.dataset.for === v ? '' : 'none');
        radios.forEach(r => r.closest('label').classList.toggle('--on', r.checked));
    }
    radios.forEach(r => r.addEventListener('change', show));
    show();
    const date = document.getElementById('required_date');

    /* ── Preparations: each with its own number of people ── */
    const list = document.getElementById('mrpList');
    let nextPrep = {{ count($preps) }};
    const rows = () => [...list.querySelectorAll('[data-prep]')];
    const maxPeople = () => Math.max(0, ...rows().map(r => parseInt(r.querySelector('[data-prep-people]').value, 10) || 0));
    function summarise() {
        const filled = rows().filter(r => r.querySelector('input').value.trim() || r.querySelector('[data-prep-people]').value);
        const m = maxPeople();
        document.getElementById('mrpSum').textContent = filled.length > 1
            ? filled.length + ' preparations' + (m ? ' · up to ' + m + ' people' : '') : '';
    }
    document.getElementById('mrpAdd').addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', document.getElementById('mrpTpl').innerHTML.replaceAll('__I__', String(nextPrep++)));
        list.lastElementChild.querySelector('input').focus();
        summarise();
    });
    list.addEventListener('click', e => {
        const b = e.target.closest('[data-prep-del]');
        if (!b) return;
        const row = b.closest('[data-prep]');
        if (rows().length > 1) row.remove();
        else row.querySelectorAll('input').forEach(i => i.value = '');   // keep one (empty) row
        summarise();
    });
    list.addEventListener('input', summarise);
    summarise();

    /* ── Hall order: pre-fill time, preparations (final menu) and materials (matching recipes) ── */
    const PREFILL_URL = @json(route('inventory.material-requests.hall-prefill', ['booking' => '__B__']));
    const note = document.getElementById('hallNote');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const prepsEmpty = () => rows().every(r => !r.querySelector('input').value.trim());
    function setPreps(preps) {
        list.querySelectorAll('[data-prep]').forEach(r => r.remove());
        preps.forEach(p => {
            list.insertAdjacentHTML('beforeend', document.getElementById('mrpTpl').innerHTML.replaceAll('__I__', String(nextPrep++)));
            const r = list.lastElementChild;
            r.querySelector('input').value = p.name;
            r.querySelector('[data-prep-people]').value = p.people || '';
        });
        summarise();
    }
    function showNote(d, applied) {
        const parts = [];
        if (d.recipes.length) parts.push('<i class="bi bi-magic"></i> <b>' + (applied ? 'Filled from recipes' : 'Recipe quantities ready') + ':</b> ' +
            d.recipes.map(r => esc(r.menu) + (r.menu !== r.recipe ? ' → ' + esc(r.recipe) : '') + ' × ' + r.people).join(', ') + '. Check and edit as needed.');
        if (d.no_recipe.length) parts.push('<span class="--warn">No recipe yet: ' + esc(d.no_recipe.join(', ')) + ' — add their materials below.</span>');
        if (d.unlinked.length) parts.push('<span class="--warn">Pick by hand (not matched to a stock item): ' + esc(d.unlinked.join('; ')) + '</span>');
        if (!d.has_menu) parts.push('<span class="--warn">This order has no final menu yet. <a href="' + esc(d.menu_url) + '" target="_blank" rel="noopener">Add the menu</a> to get preparations and recipe quantities filled in.</span>');
        note.innerHTML = parts.map(p => '<div>' + p + '</div>').join('');
        note.hidden = !parts.length;
    }
    async function hallPrefill(id) {
        note.hidden = true;
        if (!id) return;
        let d;
        try {
            const r = await fetch(PREFILL_URL.replace('__B__', encodeURIComponent(id)), {headers: {Accept: 'application/json'}});
            if (!r.ok) return;
            d = await r.json();
        } catch (_) { return; }
        if (String(document.getElementById('hall_booking_id').value) !== String(d.booking_id)) return;   // changed meanwhile
        if (d.required_date) date.value = d.required_date;
        const time = document.getElementById('required_time');
        if (d.required_time && !time.value) time.value = d.required_time;
        if (d.preparations.length && prepsEmpty()) setPreps(d.preparations);
        else if (d.people) rows().forEach(r => { const p = r.querySelector('[data-prep-people]'); if (!p.value) p.value = d.people; });
        summarise();
        const grid = window.icGrids?.request;
        const lines = d.lines.map(l => ({...l, note: 'From recipe: ' + l.from.join(', ')}));
        if (lines.length && grid) {
            if (!grid.filledCount()) { grid.setLines(lines); showNote(d, true); return; }
            showNote(d, false);
            const b = document.createElement('div');
            b.innerHTML = '<button type="button" class="ic-btn ic-btn--sm ic-btn--gold"><i class="bi bi-arrow-repeat"></i> Replace materials with recipe quantities</button>';
            b.querySelector('button').onclick = () => { grid.setLines(lines); showNote(d, true); };
            note.appendChild(b);
            return;
        }
        showNote(d, false);
    }
    document.getElementById('hall_booking_id')?.addEventListener('change', e => hallPrefill(e.target.value));

    ['meal_entry_id', 'hall_booking_id'].forEach(id => document.getElementById(id)?.addEventListener('change', e => {
        const o = e.target.selectedOptions[0];
        if (o?.dataset.date) date.value = o.dataset.date;
        if (o?.dataset.guests) rows().forEach(r => { const p = r.querySelector('[data-prep-people]'); if (!p.value) p.value = o.dataset.guests; });
        summarise();
    }));
    document.querySelectorAll('[data-quick]').forEach(b => b.addEventListener('click', () => window.icGrids?.request?.addItem(b.dataset.quick)));
    document.addEventListener('ic-grid-total', e => { if (e.detail.mode === 'request') document.getElementById('icRows').textContent = e.detail.rows; });
    document.getElementById('recipeSuggest')?.addEventListener('change', e => {
        if (!e.target.value) return;
        const u = new URL(window.location.href);
        u.searchParams.set('recipe_id', e.target.value);
        if (maxPeople()) u.searchParams.set('guest_count', maxPeople());
        window.location = u.toString();
    });
})();
</script>
@endpush
</x-admin-layout>
