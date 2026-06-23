<x-admin-layout title="Daily Entry">
@push('styles')
<style>
:root{
    --me-gold:#a0723a;--me-gold-hi:#b8832a;--me-gold-dim:rgba(160,114,58,.1);
    --me-ink:#1c1712;--me-muted:#7a6e62;--me-faint:#b0a89a;
    --me-border:#e8e2d8;--me-surface:#fff;--me-page:#f7f5f2;
    --me-radius:16px;--me-r-sm:10px;
    --me-green:#16a34a;--me-orange:#d97706;--me-blue:#2563eb;
    --me-over:rgba(22,163,74,.1);--me-under:rgba(217,119,6,.1);--me-eq:rgba(37,99,235,.08);
}
.me-wrap{max-width:620px;margin:0 auto;padding-bottom:110px}
.me-page-hdr{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.me-page-title{font-size:1.25rem;font-weight:900;color:var(--me-ink);margin:0;flex:1}
.me-card{background:var(--me-surface);border:1.5px solid var(--me-border);border-radius:var(--me-radius);overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04);margin-bottom:14px}
.me-card-hdr{padding:13px 18px;background:#faf8f5;border-bottom:1px solid var(--me-border);font-size:.88rem;font-weight:700;color:var(--me-ink);display:flex;align-items:center;gap:8px}
.me-card-body{padding:18px}
.me-field{margin-bottom:12px}
.me-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--me-faint);margin-bottom:5px}
.me-select,.me-input{width:100%;padding:11px 14px;border:1.5px solid var(--me-border);border-radius:10px;font-size:.92rem;color:var(--me-ink);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box;min-height:46px}
.me-select:focus,.me-input:focus{border-color:var(--me-gold);box-shadow:0 0 0 3px var(--me-gold-dim)}
.me-textarea{resize:vertical;min-height:72px}

/* State banner */
.me-state{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:.84rem;font-weight:600;margin-bottom:14px}
.me-state--idle{background:#faf8f5;border:1.5px dashed var(--me-border);color:var(--me-muted)}
.me-state--loading{background:rgba(160,114,58,.07);border:1.5px solid rgba(160,114,58,.2);color:var(--me-gold)}
.me-state--found{background:rgba(22,163,74,.06);border:1.5px solid rgba(22,163,74,.2);color:var(--me-green)}
.me-state--new{background:rgba(37,99,235,.05);border:1.5px solid rgba(37,99,235,.15);color:var(--me-blue)}

/* Meal cards */
.me-meal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
@media(max-width:400px){.me-meal-grid{grid-template-columns:1fr}}
.me-meal-card{border:1.5px solid var(--me-border);border-radius:14px;padding:16px;background:var(--me-surface);transition:border-color .14s,box-shadow .14s}
.me-meal-card:focus-within{border-color:var(--me-gold);box-shadow:0 0 0 3px var(--me-gold-dim)}
.me-meal-title{font-size:.82rem;font-weight:800;color:var(--me-ink);margin-bottom:12px;display:flex;align-items:center;gap:6px}
.me-count-lbl{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--me-faint);text-align:center;margin-bottom:5px}
.me-num{font-size:1.5rem;font-weight:900;text-align:center;padding:10px 6px;border-radius:10px;border:2px solid var(--me-border);width:100%;background:#fff;color:var(--me-ink);outline:none;transition:border-color .15s;box-sizing:border-box;min-height:54px;-moz-appearance:textfield}
.me-num::-webkit-inner-spin-button,.me-num::-webkit-outer-spin-button{-webkit-appearance:none}
.me-num:focus{border-color:var(--me-gold);box-shadow:0 0 0 3px var(--me-gold-dim)}
.me-variance{text-align:center;font-size:.78rem;font-weight:700;padding:4px 10px;border-radius:8px;margin-top:8px;min-height:22px}
.me-var-over{background:var(--me-over);color:var(--me-green)}
.me-var-under{background:var(--me-under);color:var(--me-orange)}
.me-var-eq{background:var(--me-eq);color:var(--me-blue)}
.me-var-none{background:rgba(0,0,0,.04);color:var(--me-faint)}

/* Audit footer */
.me-audit{display:flex;flex-wrap:wrap;gap:8px;padding:12px 16px;background:#faf8f5;border-radius:10px;border:1px solid var(--me-border);margin-top:4px}
.me-audit-item{font-size:.7rem;color:var(--me-muted);display:flex;align-items:center;gap:4px}
.me-audit-item strong{color:var(--me-ink)}

/* Copy btn */
.me-copy-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:9px;font-size:.8rem;font-weight:700;border:1.5px solid var(--me-border);background:#fff;color:var(--me-muted);cursor:pointer;transition:all .14s;min-height:40px}
.me-copy-btn:hover{border-color:var(--me-gold);color:var(--me-gold)}
.me-copy-btn:disabled{opacity:.5;cursor:not-allowed}

/* Sticky save */
.me-sticky{position:fixed;bottom:0;left:0;right:0;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);border-top:1.5px solid var(--me-border);padding:14px 20px;z-index:100;box-shadow:0 -3px 16px rgba(0,0,0,.08)}
.me-save-btn{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;padding:16px;border-radius:13px;font-size:1rem;font-weight:900;border:none;cursor:pointer;background:var(--me-gold);color:#fff;box-shadow:0 3px 12px rgba(160,114,58,.3);min-height:54px;transition:background .13s}
.me-save-btn:hover:not(:disabled){background:var(--me-gold-hi)}
.me-save-btn:disabled{opacity:.55;cursor:not-allowed}
</style>
@endpush

<div class="me-wrap">
    <div class="me-page-hdr">
        <h1 class="me-page-title">
            <i class="bi bi-journal-check me-2" style="color:var(--me-gold)"></i>Daily Entry
        </h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:10px;font-size:.85rem" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('meal-register.entries.save') }}" id="me-form">
        @csrf

        {{-- Client + Date --}}
        <div class="me-card">
            <div class="me-card-hdr"><i class="bi bi-building"></i> Client & Date</div>
            <div class="me-card-body">
                <div class="me-field">
                    <label class="me-label" for="me-client">Client *</label>
                    @php $preClient = old('meal_client_id', request('client_id')); @endphp
                    <select id="me-client" name="meal_client_id" class="me-select" required>
                        <option value="">— Select client —</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ $preClient == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @if(in_array(auth()->user()->role, ['admin','manager']))
                    <button type="button" id="openQuickAdd"
                            style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:7px 14px;border-radius:8px;font-size:.78rem;font-weight:700;border:1.5px dashed rgba(160,114,58,.4);background:rgba(160,114,58,.06);color:#a0723a;cursor:pointer">
                        <i class="bi bi-plus-circle"></i> Quick Add Client
                    </button>
                    @endif
                </div>
                <div class="me-field mb-0">
                    <label class="me-label" for="me-date">Date *</label>
                    @php $preDate = old('entry_date', request('entry_date', $today)); @endphp
                    <input id="me-date" name="entry_date" type="date" class="me-input"
                           value="{{ $preDate }}" required>
                </div>
            </div>
        </div>

        {{-- State banner --}}
        <div id="me-state" class="me-state me-state--idle">
            <i class="bi bi-info-circle"></i>
            <span id="me-state-text">Select a client and date to load or create an entry.</span>
        </div>

        {{-- Hidden entry id for tracking --}}
        <input type="hidden" name="_entry_id" id="me-entry-id" value="">

        {{-- Meal Cards --}}
        <div class="me-card" id="me-meals-card">
            <div class="me-card-hdr"><i class="bi bi-egg-fried"></i> Meal Counts</div>
            <div class="me-card-body" style="padding:14px">
                <div class="me-meal-grid">
                    @foreach($mealTypes as $key => $type)
                    @php $idx = array_search($key, array_keys($mealTypes)); @endphp
                    <div class="me-meal-card" data-meal="{{ $key }}">
                        <div class="me-meal-title">{{ $type['icon'] }} {{ $type['label'] }}</div>
                        <input type="hidden" name="items[{{ $idx }}][meal_type]" value="{{ $key }}">
                        <div style="margin-bottom:8px">
                            <div class="me-count-lbl">Planned</div>
                            <input type="number" name="items[{{ $idx }}][planned_count]"
                                   class="me-num planned-inp" data-meal="{{ $key }}"
                                   min="0" inputmode="numeric" placeholder="—">
                        </div>
                        <div>
                            <div class="me-count-lbl">Actual</div>
                            <input type="number" name="items[{{ $idx }}][actual_count]"
                                   class="me-num actual-inp" data-meal="{{ $key }}"
                                   min="0" inputmode="numeric" placeholder="—">
                        </div>
                        <div class="me-variance me-var-none" id="var-{{ $key }}">—</div>
                    </div>
                    @endforeach
                </div>

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <button type="button" class="me-copy-btn" id="me-copy-btn">
                        <i class="bi bi-arrow-counterclockwise"></i> Copy Yesterday's Planned
                    </button>
                    <div id="me-copy-msg" style="font-size:.75rem;color:var(--me-muted);display:none"></div>
                </div>
            </div>
        </div>

        {{-- Remarks --}}
        <div class="me-card">
            <div class="me-card-hdr"><i class="bi bi-chat-left-text"></i> Remarks</div>
            <div class="me-card-body">
                <textarea name="remarks" id="me-remarks" class="me-input me-textarea"
                          placeholder="Optional notes…" maxlength="500">{{ old('remarks') }}</textarea>
            </div>
        </div>

        {{-- Audit footer (shows when entry loaded) --}}
        <div id="me-audit" class="me-audit" style="display:none">
            <div class="me-audit-item" id="audit-created" style="display:none">
                <i class="bi bi-person-circle"></i> Created by <strong id="audit-created-name"></strong>
            </div>
            <div class="me-audit-item" id="audit-planned" style="display:none">
                <i class="bi bi-pencil"></i> Planned by <strong id="audit-planned-name"></strong>
            </div>
            <div class="me-audit-item" id="audit-actual" style="display:none">
                <i class="bi bi-check2"></i> Actual by <strong id="audit-actual-name"></strong>
            </div>
            <div class="me-audit-item" id="audit-updated" style="display:none">
                <i class="bi bi-clock"></i> <strong id="audit-updated-at"></strong>
            </div>
        </div>

        {{-- Sticky Save --}}
        <div class="me-sticky">
            <button type="submit" class="me-save-btn" id="me-save-btn">
                <i class="bi bi-check-lg"></i> Save Entry
            </button>
        </div>
    </form>
</div>

{{-- Quick Add Modal (admin/manager) --}}
@if(in_array(auth()->user()->role, ['admin','manager']))
<div id="quickAddModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1060;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:18px;padding:24px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative">
        <button type="button" id="closeQA" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.1rem;color:#b0a89a;cursor:pointer"><i class="bi bi-x-lg"></i></button>
        <div style="font-size:1rem;font-weight:800;color:#1c1712;margin-bottom:4px"><i class="bi bi-building me-2" style="color:#a0723a"></i>Quick Add Client</div>
        <div style="font-size:.78rem;color:#7a6e62;margin-bottom:18px">Add new client and select immediately.</div>
        <div id="qaErr" style="display:none;background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);border-radius:8px;padding:8px 12px;font-size:.78rem;color:#dc2626;margin-bottom:14px"></div>
        <div style="margin-bottom:12px">
            <label style="display:block;font-size:.7rem;font-weight:700;color:#7a6e62;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Company Name *</label>
            <input id="qa_name" type="text" style="width:100%;padding:11px 14px;border:1.5px solid #e8e2d8;border-radius:10px;font-size:.9rem;outline:none" onfocus="this.style.borderColor='#a0723a'" onblur="this.style.borderColor='#e8e2d8'">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
            <div>
                <label style="display:block;font-size:.7rem;font-weight:700;color:#7a6e62;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Contact Person</label>
                <input id="qa_contact" type="text" style="width:100%;padding:11px 14px;border:1.5px solid #e8e2d8;border-radius:10px;font-size:.9rem;outline:none" onfocus="this.style.borderColor='#a0723a'" onblur="this.style.borderColor='#e8e2d8'">
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:700;color:#7a6e62;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Mobile</label>
                <input id="qa_mobile" type="tel" inputmode="numeric" style="width:100%;padding:11px 14px;border:1.5px solid #e8e2d8;border-radius:10px;font-size:.9rem;outline:none" onfocus="this.style.borderColor='#a0723a'" onblur="this.style.borderColor='#e8e2d8'">
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button type="button" id="saveQA" style="flex:1;padding:12px;background:#a0723a;color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:700;cursor:pointer"><i class="bi bi-check-lg me-1"></i> Save & Select</button>
            <button type="button" id="closeQA2" style="padding:12px 18px;background:#f7f5f2;color:#7a6e62;border:1.5px solid #e8e2d8;border-radius:10px;font-size:.9rem;font-weight:700;cursor:pointer">Cancel</button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
(function() {
    var LOAD_URL      = '{{ route('meal-register.entries.load') }}';
    var PREV_URL      = '{{ route('meal-register.entries.previous-day') }}';
    var CSRF          = '{{ csrf_token() }}';
    var clientSel     = document.getElementById('me-client');
    var dateInput     = document.getElementById('me-date');
    var stateBanner   = document.getElementById('me-state');
    var stateText     = document.getElementById('me-state-text');
    var auditDiv      = document.getElementById('me-audit');
    var entryIdInput  = document.getElementById('me-entry-id');
    var loadTimer;

    // ── Variance ─────────────────────────────────────────────────────────────
    function updateVariance(mealKey) {
        var card  = document.querySelector('.me-meal-card[data-meal="' + mealKey + '"]');
        var badge = document.getElementById('var-' + mealKey);
        if (!card || !badge) return;
        var p = parseInt(card.querySelector('.planned-inp').value);
        var a = parseInt(card.querySelector('.actual-inp').value);
        if (isNaN(p) || isNaN(a)) { badge.textContent = '—'; badge.className = 'me-variance me-var-none'; return; }
        var d = a - p;
        if (d > 0)      { badge.textContent = '+' + d + ' Over Served';  badge.className = 'me-variance me-var-over'; }
        else if (d < 0) { badge.textContent = d + ' Short';              badge.className = 'me-variance me-var-under'; }
        else            { badge.textContent = 'On Target';               badge.className = 'me-variance me-var-eq'; }
    }

    document.querySelectorAll('.planned-inp, .actual-inp').forEach(function(inp) {
        inp.addEventListener('input', function() { updateVariance(inp.dataset.meal); });
    });

    // ── State display ─────────────────────────────────────────────────────────
    function setState(type, icon, text) {
        stateBanner.className = 'me-state me-state--' + type;
        stateText.innerHTML = '<i class="bi bi-' + icon + ' me-1"></i>' + text;
    }

    // ── Audit footer ──────────────────────────────────────────────────────────
    function showAudit(d) {
        var any = false;
        function setItem(id, name) {
            var el = document.getElementById(id);
            if (name) { el.style.display = 'flex'; el.querySelector('strong').textContent = name; any = true; }
            else el.style.display = 'none';
        }
        setItem('audit-created', d.created_by_name);
        setItem('audit-planned', d.planned_by_name);
        setItem('audit-actual',  d.actual_by_name);
        var upEl = document.getElementById('audit-updated');
        var upName = document.getElementById('audit-updated-at');
        if (d.updated_at) { upEl.style.display = 'flex'; upName.textContent = d.updated_at; any = true; }
        else upEl.style.display = 'none';
        auditDiv.style.display = any ? 'flex' : 'none';
    }

    function clearForm() {
        document.querySelectorAll('.planned-inp, .actual-inp').forEach(function(i) { i.value = ''; });
        document.querySelectorAll('[id^="var-"]').forEach(function(el) { el.textContent = '—'; el.className = 'me-variance me-var-none'; });
        document.getElementById('me-remarks').value = '';
        entryIdInput.value = '';
        auditDiv.style.display = 'none';
    }

    function populateEntry(entry) {
        var items = entry.items;
        Object.keys(items).forEach(function(key) {
            var v   = items[key];
            var pi  = document.querySelector('.planned-inp[data-meal="' + key + '"]');
            var ai  = document.querySelector('.actual-inp[data-meal="' + key + '"]');
            if (pi) pi.value = v.planned !== null && v.planned !== undefined ? v.planned : '';
            if (ai) ai.value = v.actual  !== null && v.actual  !== undefined ? v.actual  : '';
            updateVariance(key);
        });
        if (entry.remarks) document.getElementById('me-remarks').value = entry.remarks;
        entryIdInput.value = entry.id;
        showAudit(entry);
    }

    // ── Load ──────────────────────────────────────────────────────────────────
    function loadEntry() {
        var clientId  = clientSel.value;
        var entryDate = dateInput.value;
        clearForm();
        if (!clientId || !entryDate) {
            setState('idle', 'info-circle', 'Select a client and date to load or create an entry.');
            return;
        }
        setState('loading', 'hourglass-split', 'Loading…');
        fetch(LOAD_URL + '?client_id=' + encodeURIComponent(clientId) + '&entry_date=' + encodeURIComponent(entryDate), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.entry) {
                populateEntry(data.entry);
                setState('found', 'check-circle', 'Entry loaded — update and save below.');
            } else {
                setState('new', 'plus-circle', 'No entry yet for this date — fill in counts to create one.');
            }
        })
        .catch(function() {
            setState('idle', 'exclamation-triangle', 'Could not load entry. Check connection and try again.');
        });
    }

    clientSel.addEventListener('change', loadEntry);
    dateInput.addEventListener('change', function() {
        clearTimeout(loadTimer);
        loadTimer = setTimeout(loadEntry, 200);
    });

    // Auto-load if pre-selected
    if (clientSel.value && dateInput.value) loadEntry();
    else setState('idle', 'info-circle', 'Select a client and date to load or create an entry.');

    // ── Copy Yesterday ────────────────────────────────────────────────────────
    document.getElementById('me-copy-btn').addEventListener('click', function() {
        var clientId  = clientSel.value;
        var entryDate = dateInput.value;
        var msg       = document.getElementById('me-copy-msg');
        if (!clientId || !entryDate) { msg.textContent = 'Select client and date first.'; msg.style.display = 'block'; return; }
        this.disabled = true; msg.textContent = 'Fetching…'; msg.style.display = 'block';
        var btn = this;
        fetch(PREV_URL + '?client_id=' + encodeURIComponent(clientId) + '&entry_date=' + encodeURIComponent(entryDate), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.entry) { msg.textContent = 'No entry found for yesterday.'; return; }
            var copied = 0;
            data.entry.items.forEach(function(item) {
                var pi = document.querySelector('.planned-inp[data-meal="' + item.meal_type + '"]');
                if (pi) { pi.value = item.planned_count !== null ? item.planned_count : ''; updateVariance(item.meal_type); copied++; }
            });
            msg.textContent = copied > 0 ? 'Copied planned counts from ' + data.entry.date + '.' : 'Nothing to copy.';
        })
        .catch(function() { msg.textContent = 'Error fetching.'; })
        .finally(function() { btn.disabled = false; });
    });

@if(in_array(auth()->user()->role, ['admin','manager']))
    // ── Quick Add Modal ───────────────────────────────────────────────────────
    var modal   = document.getElementById('quickAddModal');
    function openModal()  { modal.style.display = 'flex'; document.getElementById('qa_name').focus(); }
    function closeModal() { modal.style.display = 'none'; }
    document.getElementById('openQuickAdd').addEventListener('click', openModal);
    document.getElementById('closeQA').addEventListener('click', closeModal);
    document.getElementById('closeQA2').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.getElementById('saveQA').addEventListener('click', function() {
        var name    = document.getElementById('qa_name').value.trim();
        var contact = document.getElementById('qa_contact').value.trim();
        var mobile  = document.getElementById('qa_mobile').value.trim();
        var errBox  = document.getElementById('qaErr');
        errBox.style.display = 'none';
        if (!name) { errBox.textContent = 'Company name is required.'; errBox.style.display = 'block'; return; }
        var btn = this; btn.disabled = true; btn.textContent = 'Saving…';
        fetch('{{ route('meal-register.clients.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ name: name, contact_person: contact, mobile: mobile }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.errors) {
                errBox.textContent = Object.values(data.errors).flat().join(' ');
                errBox.style.display = 'block';
            } else {
                clientSel.appendChild(new Option(data.name, data.id, true, true));
                clientSel.value = data.id;
                closeModal();
                ['qa_name','qa_contact','qa_mobile'].forEach(function(id) { document.getElementById(id).value = ''; });
                loadEntry();
            }
        })
        .catch(function() { errBox.textContent = 'Server error.'; errBox.style.display = 'block'; })
        .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save & Select'; });
    });
@endif
})();
</script>
@endpush
</x-admin-layout>
