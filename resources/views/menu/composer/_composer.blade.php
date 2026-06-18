{{--
  Shared composer partial — create.blade.php and edit.blade.php both include this.
  Props: $draft (null = new, MenuDraft = editing)

  Section data format:
    [{key, label_en, label_ta, people_count, items:[{id,item_en,item_ta,category_key,...}]}]
--}}

{{-- ── Data hydration ────────────────────────────────────────────────────── --}}
<script>
window.MC = {
    allItems:     @json($menuItems),
    catKeys:      @json($categoryKeys),
    categories:   @json($categories),
    mealSections: @json($mealSections),
    draftId:      @json($draft?->id ?? null),
    initial:      @json($initialContent),
    meta:         @json($initialMeta),
    routes: {
        storeDraft:    '{{ route('menu.drafts.store') }}',
        updateDraft:   '{{ url('menu/drafts') }}/',
        generatePdf:   '{{ route('menu.pdf.generate') }}',
        storeTemplate: '{{ route('menu.templates.store') }}',
    },
    csrf: '{{ csrf_token() }}',
};
</script>

{{-- ════════════════════════════════════════════════════════════════════
     TWO-PANEL LAYOUT  (library 30% | composer 70%)
     Mobile: library hidden → slide-over drawer via FAB
     ════════════════════════════════════════════════════════════════════ --}}
<div class="mc-layout">

    {{-- ── LEFT: Menu Library ───────────────────────────────────────────── --}}
    <aside class="mc-library" aria-label="Menu Library">
        <div class="mc-lib-hdr">
            <span class="mc-lib-title"><i class="bi bi-grid-3x3-gap me-1"></i>Menu Library</span>
            <span class="mc-lib-count-badge" id="mcLibTotal">0</span>
        </div>
        <div class="mc-lib-search-wrap">
            <i class="bi bi-search mc-lib-search-ico"></i>
            <input type="text" id="mcLibSearch" class="mc-lib-search"
                   placeholder="Search items…" autocomplete="off"
                   oninput="onLibSearch(this.value)"
                   onkeydown="if(event.key==='Escape'){this.value='';onLibSearch('')}">
            <button type="button" id="mcLibSearchClear" class="mc-lib-search-clear"
                    onclick="document.getElementById('mcLibSearch').value='';onLibSearch('')">×</button>
        </div>
        <div id="mcLibHint" class="mc-lib-hint">
            Click a section to select it,<br>then click items to add.
        </div>
        <div class="mc-lib-body" id="mcLibBody"></div>
    </aside>

    {{-- ── RIGHT: Composer ──────────────────────────────────────────────── --}}
    <div class="mc-shell">

        {{-- Back + status --}}
        <div class="d-flex align-items-center gap-3 mb-2 pt-1">
            <a href="{{ route('menu.composer.index') }}" class="mc-back">
                <i class="bi bi-arrow-left"></i> Saved Menus
            </a>
            <div class="mc-save-status" id="mcSaveStatus">
                <span class="dot"></span>
                <span id="mcSaveText">All changes saved</span>
            </div>
        </div>

        {{-- ── Meta card ────────────────────────────────────────────────── --}}
        <div class="mc-meta-card">
            <div class="mc-meta-row full" style="margin-bottom:12px">
                <div>
                    <label class="mc-field-lbl" for="mcTitle">Menu Title</label>
                    <input type="text" id="mcTitle" class="mc-input --title"
                           placeholder="e.g. Wedding Reception Menu"
                           value="{{ $draft?->title ?? '' }}" autocomplete="off">
                </div>
            </div>
            <div class="mc-meta-row">
                <div>
                    <label class="mc-field-lbl" for="mcVenue">Venue <span class="mc-opt">(optional)</span></label>
                    <input type="text" id="mcVenue" class="mc-input"
                           placeholder="e.g. Akshathay Hall, Chennai"
                           value="{{ $draft?->venue ?? '' }}" autocomplete="off">
                </div>
                <div>
                    <label class="mc-field-lbl" for="mcDate">Event Date <span class="mc-opt">(optional)</span></label>
                    <input type="date" id="mcDate" class="mc-input"
                           value="{{ $draft?->event_date?->format('Y-m-d') ?? '' }}">
                </div>
            </div>
        </div>

        {{-- ── Dynamic sections ─────────────────────────────────────────── --}}
        <div id="mcSectionList"></div>

        {{-- ── Add Section ──────────────────────────────────────────────── --}}
        <div class="mc-add-section-wrap" id="mcAddSectionWrap">
            <button type="button" class="mc-add-section-btn" onclick="openAddSection()">
                <i class="bi bi-plus-circle-fill"></i> Add Section
            </button>
        </div>

    </div>{{-- /mc-shell --}}
</div>{{-- /mc-layout --}}

{{-- ── Mobile Library FAB ────────────────────────────────────────────────── --}}
<button type="button" class="mc-lib-fab" id="mcLibFab"
        onclick="openLibDrawer()" title="Menu Library" aria-label="Open menu library">
    <i class="bi bi-grid-3x3-gap"></i>
</button>

{{-- ── Mobile Library Drawer ─────────────────────────────────────────────── --}}
<div class="mc-lib-overlay" id="mcLibOverlay" onclick="closeLibDrawer()"></div>
<div class="mc-lib-drawer" id="mcLibDrawer">
    <div class="mc-lib-drawer-hdr">
        <span class="mc-lib-title"><i class="bi bi-grid-3x3-gap me-1"></i>Menu Library</span>
        <span class="mc-lib-count-badge" id="mcLibTotalMob">0</span>
        <span style="flex:1"></span>
        <button type="button" class="mc-lib-drawer-close" onclick="closeLibDrawer()">×</button>
    </div>
    <div class="mc-lib-search-wrap" style="flex-shrink:0">
        <i class="bi bi-search mc-lib-search-ico"></i>
        <input type="text" id="mcLibSearchMob" class="mc-lib-search"
               placeholder="Search items…" autocomplete="off"
               oninput="onLibSearch(this.value,'mob')"
               onkeydown="if(event.key==='Escape'){this.value='';onLibSearch('','mob')}">
        <button type="button" class="mc-lib-search-clear" id="mcLibSearchClearMob"
                onclick="document.getElementById('mcLibSearchMob').value='';onLibSearch('','mob')">×</button>
    </div>
    <div id="mcLibHintMob" class="mc-lib-hint">
        Click a section to select it, then tap items to add.
    </div>
    <div class="mc-lib-drawer-body">
        <div class="mc-lib-body" id="mcLibBodyMob"></div>
    </div>
</div>

{{-- ── Bottom action bar ────────────────────────────────────────────────── --}}
<div class="mc-action-bar">
    <button type="button" class="mc-bar-save" onclick="saveDraft(true)">
        <i class="bi bi-floppy me-1"></i> Save
    </button>
    <button type="button" class="mc-bar-tpl" onclick="saveAsTemplate()" title="Save as reusable template">
        <i class="bi bi-collection me-1"></i> Template
    </button>
    <button type="button" class="mc-bar-copy" onclick="copyWhatsApp()">
        <i class="bi bi-whatsapp me-1"></i> Copy
    </button>
    <div class="mc-bar-pdf-group">
        {{-- Each language has a split button: main = Letterhead PDF, dropdown = Standard PDF --}}
        @foreach([['en','EN','English'],['ta','தமிழ்','Tamil'],['bi','EN+தமிழ்','Bilingual']] as [$lc,$lbl,$lname])
        <div class="btn-group mc-bar-pdf-split" role="group">
            <button type="button" class="mc-bar-pdf mc-bar-pdf--{{ $lc }}"
                    onclick="generatePdf('{{ $lc }}', true)"
                    title="{{ $lname }} PDF with Letterhead">
                <i class="bi bi-file-earmark-pdf me-1"></i> {{ $lbl }}
            </button>
            <button type="button" class="mc-bar-pdf mc-bar-pdf--{{ $lc }} mc-bar-pdf-caret dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    title="{{ $lname }} PDF options">
                <span class="visually-hidden">Options</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end mc-pdf-dropdown">
                <li><h6 class="dropdown-header">{{ $lname }} PDF</h6></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="generatePdf('{{ $lc }}', true); return false;">
                        <i class="bi bi-file-earmark-richtext me-1 text-warning"></i> With Letterhead
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="generatePdf('{{ $lc }}', false); return false;">
                        <i class="bi bi-file-earmark me-1"></i> Standard (no letterhead)
                    </a>
                </li>
            </ul>
        </div>
        @endforeach
    </div>
</div>

{{-- PDF download anchor (programmatic click, no hidden form needed) --}}
<a id="mcPdfAnchor" style="display:none"></a>

{{-- Toast --}}
<div class="mc-toast" id="mcToast" role="status" aria-live="polite"></div>

{{-- ── Add Section modal ────────────────────────────────────────────────── --}}
<div id="mcAddModal" class="mc-modal-overlay" style="display:none" onclick="if(event.target===this)closeAddSection()">
    <div class="mc-modal-box" role="dialog" aria-modal="true" aria-label="Add Section">
        <div class="mc-modal-hdr">
            <span class="mc-modal-title">Add Section</span>
            <button type="button" class="mc-modal-close" onclick="closeAddSection()">×</button>
        </div>
        <div class="mc-modal-body">
            <div class="mc-section-picker" id="mcSectionPicker"></div>
            <div id="mcCustomFields" style="display:none;margin-top:16px">
                <label class="mc-field-lbl" style="margin-bottom:4px">Section Name (English)</label>
                <input type="text" id="customLabelEn" class="mc-input" placeholder="e.g. VIP Counter" maxlength="100" style="margin-bottom:10px">
                <label class="mc-field-lbl" style="margin-bottom:4px">Section Name (Tamil)</label>
                <input type="text" id="customLabelTa" class="mc-input" placeholder="e.g. விஐபி கவுண்டர்" maxlength="100" lang="ta">
                <button type="button" class="mc-modal-confirm-btn" style="margin-top:14px" onclick="confirmAddCustom()">
                    <i class="bi bi-plus-lg me-1"></i> Add Section
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Rename Section modal ─────────────────────────────────────────────── --}}
<div id="mcRenameModal" class="mc-modal-overlay" style="display:none" onclick="if(event.target===this)closeRename()">
    <div class="mc-modal-box" role="dialog" aria-modal="true" aria-label="Rename Section">
        <div class="mc-modal-hdr">
            <span class="mc-modal-title">Rename Section</span>
            <button type="button" class="mc-modal-close" onclick="closeRename()">×</button>
        </div>
        <div class="mc-modal-body">
            <label class="mc-field-lbl" style="margin-bottom:4px">English Name</label>
            <input type="text" id="renameLabelEn" class="mc-input" maxlength="100" style="margin-bottom:10px">
            <label class="mc-field-lbl" style="margin-bottom:4px">Tamil Name</label>
            <input type="text" id="renameLabelTa" class="mc-input" maxlength="100" lang="ta">
            <button type="button" class="mc-modal-confirm-btn" style="margin-top:14px" onclick="confirmRename()">
                Save Name
            </button>
        </div>
    </div>
</div>

{{-- ── Save as Template modal ───────────────────────────────────────────── --}}
<div id="mcTplModal" class="mc-modal-overlay" style="display:none" onclick="if(event.target===this)closeTplModal()">
    <div class="mc-modal-box" role="dialog" aria-modal="true" aria-label="Save as Template">
        <div class="mc-modal-hdr">
            <span class="mc-modal-title">Save as Template</span>
            <button type="button" class="mc-modal-close" onclick="closeTplModal()">×</button>
        </div>
        <div class="mc-modal-body">
            <p style="font-size:.83rem;color:#7a6e62;margin-bottom:14px">
                Saves current menu structure. People counts, venue, and date are not stored in templates.
            </p>
            <label class="mc-field-lbl" style="margin-bottom:4px">Template Name</label>
            <input type="text" id="tplModalName" class="mc-input" placeholder="e.g. Standard Wedding Lunch" maxlength="255">
            <button type="button" class="mc-modal-confirm-btn" style="margin-top:14px" onclick="confirmSaveTemplate()">
                Save Template
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     COMPOSER JAVASCRIPT
     ══════════════════════════════════════════════════════════════════════ --}}
<script>
(function () {
'use strict';

// ── Constants ─────────────────────────────────────────────────────────────

const allItems     = MC.allItems;
const catKeys      = MC.catKeys;
const mealSections = MC.mealSections;

// ── State ─────────────────────────────────────────────────────────────────

// Section: { _cid, key, label_en, label_ta, people_count, items[] }
// _cid: client-only, stripped before save
let sections = (MC.initial || []).map((s, i) => ({ ...s, _cid: 'sec_' + i }));
let _cid = sections.length;

let meta = {
    title:      MC.meta.title      || '',
    venue:      MC.meta.venue      || '',
    event_date: MC.meta.event_date || '',
};

let draftId      = MC.draftId;
let saveTimer    = null;
let searchTimers = {};
let closeTimers  = {};
let focusedIdx   = {};
let searchResults = {};

// Active section (set by clicking a section or adding/searching within it)
let activeCid = null;

// Library state
let libSearch    = '';
let libExpanded  = new Set(catKeys); // all groups expanded by default

// ── Drag state ────────────────────────────────────────────────────────────

// dragType: 'section' | 'item' | null
let dragType     = null;
let dragCid      = null;  // section being dragged (section DnD)
let dragOver     = null;  // section hovered over (section DnD)
let itemDragData = null;  // { fromCid, itemIdx } (item DnD)
let itemDragOverCid = null;  // section cid drop target (item cross-section)
let itemDragOverIdx = null;  // item index drop target (item within-section)

// ── Init ──────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('mcTitle').addEventListener('input',  e => { meta.title      = e.target.value; scheduleSave(); });
    document.getElementById('mcVenue').addEventListener('input',  e => { meta.venue      = e.target.value; scheduleSave(); });
    document.getElementById('mcDate').addEventListener('change',  e => { meta.event_date = e.target.value; scheduleSave(); });

    renderLibrary();
    renderAllSections();
    renderSectionPicker();

    // Close move-to menus on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.mc-moveto-menu.open').forEach(m => m.classList.remove('open'));
    });
});

// ── Library ───────────────────────────────────────────────────────────────

function renderLibrary(filter) {
    const q      = (filter !== undefined ? filter : libSearch).toLowerCase().trim();
    const bodies = [
        { bodyEl: document.getElementById('mcLibBody'),    totalEl: document.getElementById('mcLibTotal'), hintEl: document.getElementById('mcLibHint') },
        { bodyEl: document.getElementById('mcLibBodyMob'), totalEl: document.getElementById('mcLibTotalMob'), hintEl: document.getElementById('mcLibHintMob') },
    ];

    let totalCount = 0;

    // Build category groups
    const groups = catKeys.map(cKey => {
        const catMeta  = MC.categories[cKey];
        const catItems = allItems.filter(i => i.category_key === cKey && (
            !q || i.item_en.toLowerCase().includes(q) || (i.item_ta && i.item_ta.includes(q)) ||
            (catMeta?.en || '').toLowerCase().includes(q)
        ));
        return { cKey, catMeta, items: catItems };
    }).filter(g => g.items.length > 0);

    totalCount = groups.reduce((s, g) => s + g.items.length, 0);

    const html = groups.length === 0
        ? `<div class="mc-lib-empty"><i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>No items match "${escHtml(q)}"</div>`
        : groups.map(({ cKey, catMeta, items }) => {
            const isOpen = q ? true : libExpanded.has(cKey);
            const itemsHtml = items.map(item => `
                <div class="mc-lib-item" onclick="addToActive(${JSON.stringify({
                    id: item.id, item_en: item.item_en, item_ta: item.item_ta,
                    category_key: item.category_key, category_en: item.category_en, category_ta: item.category_ta
                }).replace(/"/g,'&quot;')})" title="Click to add to active section">
                    <div class="mc-lib-item-names">
                        <div class="mc-lib-item-en">${highlightMatch(item.item_en, q)}</div>
                        ${item.item_ta ? `<div class="mc-lib-item-ta">${escHtml(item.item_ta)}</div>` : ''}
                    </div>
                    <div class="mc-lib-item-add"><i class="bi bi-plus"></i></div>
                </div>`).join('');
            return `
                <div class="mc-lib-group ${isOpen ? '--open' : ''}" data-cat="${escAttr(cKey)}">
                    <button type="button" class="mc-lib-group-btn" onclick="toggleLibGroup('${cKey}')">
                        <span class="mc-lib-group-name">${escHtml(catMeta?.en || cKey)}</span>
                        <span class="mc-lib-group-count">${items.length}</span>
                        <i class="bi bi-chevron-right mc-lib-group-chevron"></i>
                    </button>
                    <div class="mc-lib-group-items">${itemsHtml}</div>
                </div>`;
        }).join('');

    bodies.forEach(({ bodyEl, totalEl, hintEl }) => {
        if (bodyEl)   bodyEl.innerHTML   = html;
        if (totalEl)  totalEl.textContent = totalCount;
        if (hintEl)   updateLibHint(hintEl);
    });
}

function updateLibHint(el) {
    if (!el) return;
    if (activeCid) {
        const sec = sections.find(s => s._cid === activeCid);
        el.className = 'mc-lib-hint --ok';
        el.innerHTML = `<i class="bi bi-check-circle me-1"></i>Adding to: <strong>${escHtml(sec?.label_en || '')}</strong>`;
    } else {
        el.className = 'mc-lib-hint';
        el.innerHTML = 'Click a section to select it,<br>then click items to add.';
    }
}

window.onLibSearch = function (val, panel) {
    libSearch = val;
    const clearDesktop = document.getElementById('mcLibSearchClear');
    const clearMob     = document.getElementById('mcLibSearchClearMob');
    if (clearDesktop) clearDesktop.classList.toggle('show', val.length > 0);
    if (clearMob)     clearMob.classList.toggle('show', val.length > 0);
    renderLibrary(val);
};

window.toggleLibGroup = function (cKey) {
    if (libExpanded.has(cKey)) libExpanded.delete(cKey);
    else libExpanded.add(cKey);
    document.querySelectorAll(`.mc-lib-group[data-cat="${cKey}"]`).forEach(el => {
        el.classList.toggle('--open', libExpanded.has(cKey));
    });
};

// Add item from library to active section
window.addToActive = function (item) {
    if (!activeCid) {
        showToast('Click a section first to select where to add.');
        // Pulse the section list to draw attention
        document.getElementById('mcSectionList')?.querySelectorAll('.mc-section').forEach(el => {
            el.style.animation = 'none';
            el.offsetWidth;
            el.style.animation = '';
        });
        return;
    }
    addItem(activeCid, item);
    // Auto-close mobile drawer after add
    closeLibDrawer();
};

// ── Mobile drawer ─────────────────────────────────────────────────────────

window.openLibDrawer = function () {
    document.getElementById('mcLibOverlay')?.classList.add('open');
    document.getElementById('mcLibDrawer')?.classList.add('open');
    setTimeout(() => document.getElementById('mcLibSearchMob')?.focus(), 100);
};

window.closeLibDrawer = function () {
    document.getElementById('mcLibOverlay')?.classList.remove('open');
    document.getElementById('mcLibDrawer')?.classList.remove('open');
};

// ── Section picker modal ──────────────────────────────────────────────────

function renderSectionPicker() {
    const box = document.getElementById('mcSectionPicker');
    if (!box) return;
    box.innerHTML = Object.entries(mealSections).map(([key, def]) => `
        <button type="button" class="mc-picker-btn" data-key="${key}" onclick="pickSection('${key}')">
            <i class="bi ${def.icon || 'bi-circle'} mc-picker-icon"></i>
            <span class="mc-picker-en">${escHtml(def.en)}</span>
            <span class="mc-picker-ta">${escHtml(def.ta)}</span>
        </button>`).join('');
}

window.openAddSection = function () {
    document.getElementById('mcCustomFields').style.display = 'none';
    document.getElementById('customLabelEn').value  = '';
    document.getElementById('customLabelTa').value  = '';
    document.getElementById('mcAddModal').style.display = 'flex';
};

window.closeAddSection = function () {
    document.getElementById('mcAddModal').style.display = 'none';
};

window.pickSection = function (key) {
    if (key === 'custom') {
        document.getElementById('mcCustomFields').style.display = 'block';
        document.getElementById('customLabelEn').focus();
        return;
    }
    const def = mealSections[key];
    addSection({ key, label_en: def.en, label_ta: def.ta });
    closeAddSection();
};

window.confirmAddCustom = function () {
    const en = document.getElementById('customLabelEn').value.trim();
    if (!en) { document.getElementById('customLabelEn').focus(); return; }
    const ta = document.getElementById('customLabelTa').value.trim();
    addSection({ key: 'custom', label_en: en, label_ta: ta });
    closeAddSection();
};

function addSection(def) {
    const cid = 'sec_' + (_cid++);
    sections.push({ _cid: cid, key: def.key, label_en: def.label_en, label_ta: def.label_ta, people_count: null, items: [] });
    setActive(cid);
    renderAllSections();
    scheduleSave();
    requestAnimationFrame(() => {
        document.getElementById('section-' + cid)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.getElementById('search-' + cid)?.focus();
    });
}

// ── Active section ────────────────────────────────────────────────────────

window.setActive = function setActive(cid) {
    if (activeCid === cid) return;
    activeCid = cid;

    // Update section card borders
    document.querySelectorAll('.mc-section').forEach(el => {
        el.classList.toggle('mc-section--active', el.dataset.cid === cid);
    });

    // Update library hint in both desktop + mobile panels
    updateLibHint(document.getElementById('mcLibHint'));
    updateLibHint(document.getElementById('mcLibHintMob'));
}

// ── Render all sections ───────────────────────────────────────────────────

function renderAllSections() {
    const list = document.getElementById('mcSectionList');
    if (!list) return;

    if (sections.length === 0) {
        activeCid = null;
        list.innerHTML = `
            <div class="mc-empty-sections">
                <i class="bi bi-layout-text-sidebar-reverse mc-empty-icon"></i>
                <p class="mc-empty-p">No sections yet</p>
                <p class="mc-empty-sub">Click <strong>+ Add Section</strong> below to start building the menu.</p>
            </div>`;
        updateLibHint(document.getElementById('mcLibHint'));
        updateLibHint(document.getElementById('mcLibHintMob'));
        return;
    }

    // If active section was deleted, clear
    if (activeCid && !sections.find(s => s._cid === activeCid)) activeCid = null;

    list.innerHTML = sections.map((sec, idx) => renderSectionHTML(sec, idx)).join('');

    // Attach section-level DnD listeners
    list.querySelectorAll('.mc-section').forEach(el => {
        el.addEventListener('dragstart',  onSecDragStart);
        el.addEventListener('dragend',    onSecDragEnd);
        el.addEventListener('dragover',   onSecDragOver);
        el.addEventListener('drop',       onSecDrop);
        el.addEventListener('dragleave',  onSecDragLeave);
    });

    // Attach item-level DnD listeners on drop zones
    list.querySelectorAll('.mc-items-drop-zone').forEach(el => {
        el.addEventListener('dragover',  onItemZoneDragOver);
        el.addEventListener('drop',      onItemZoneDrop);
        el.addEventListener('dragleave', onItemZoneDragLeave);
    });

    // Re-apply active class
    if (activeCid) {
        document.getElementById('section-' + activeCid)?.classList.add('mc-section--active');
    }

    updateLibHint(document.getElementById('mcLibHint'));
    updateLibHint(document.getElementById('mcLibHintMob'));
}

function renderSectionHTML(sec) {
    const iconKey  = mealSections[sec.key]?.icon || 'bi-layout-text-sidebar';
    const isActive = activeCid === sec._cid;

    return `
    <div class="mc-section${isActive ? ' mc-section--active' : ''}"
         id="section-${sec._cid}" data-cid="${sec._cid}"
         draggable="true" role="region" aria-label="${escAttr(sec.label_en)}"
         onclick="setActive('${sec._cid}')">

        <div class="mc-section-hdr">
            <div class="mc-drag-handle" title="Drag to reorder sections">
                <i class="bi bi-grip-vertical"></i>
            </div>
            <i class="bi ${iconKey} mc-section-icon"></i>
            <div class="mc-section-title-wrap">
                <span class="mc-section-title">${escHtml(sec.label_en)}</span>
                ${sec.label_ta ? `<span class="mc-section-title-ta">${escHtml(sec.label_ta)}</span>` : ''}
            </div>
            <div class="mc-people-wrap" title="Guest count for this section">
                <input type="number"
                       class="mc-people-input"
                       placeholder="Pax"
                       min="1" max="99999"
                       value="${sec.people_count || ''}"
                       onclick="event.stopPropagation()"
                       onchange="updatePeopleCount('${sec._cid}', this.value)"
                       oninput="updatePeopleCount('${sec._cid}', this.value)"
                       aria-label="Number of guests for ${escAttr(sec.label_en)}">
                <span class="mc-people-lbl">pax</span>
            </div>
            <span class="mc-section-count" id="count-${sec._cid}">${sectionSummaryText(sec)}</span>
            <div class="mc-section-actions" onclick="event.stopPropagation()">
                <button type="button" class="mc-sec-btn" title="Rename" onclick="openRename('${sec._cid}')">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="mc-sec-btn" title="Duplicate section" onclick="duplicateSection('${sec._cid}')">
                    <i class="bi bi-copy"></i>
                </button>
                <button type="button" class="mc-sec-btn --del" title="Delete section" onclick="deleteSection('${sec._cid}')">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>

        <div class="mc-items-body">
            <div id="items-${sec._cid}"
                 class="mc-items-body-inner mc-items-drop-zone"
                 data-cid="${sec._cid}">
                ${renderItemsHTML(sec)}
            </div>
        </div>

        <div class="mc-add-wrap" onclick="event.stopPropagation()">
            <div class="mc-add-inner" style="position:relative">
                <i class="bi bi-search mc-add-icon"></i>
                <input type="text"
                       id="search-${sec._cid}"
                       class="mc-add-input"
                       placeholder="Search to add item…"
                       autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                       data-cid="${sec._cid}"
                       oninput="handleSearch(this)"
                       onfocus="setActive('${sec._cid}');openResults('${sec._cid}')"
                       onblur="scheduleClose('${sec._cid}')"
                       onkeydown="handleSearchKey(event,'${sec._cid}')">
                <button type="button" class="mc-add-clear" id="clear-${sec._cid}"
                        onclick="clearSearch('${sec._cid}')">×</button>
                <div class="mc-results" id="results-${sec._cid}" role="listbox"></div>
            </div>
        </div>
    </div>`;
}

function renderItemsHTML(sec) {
    if (sec.items.length === 0) {
        return `<div class="mc-items-empty">No items yet — search below or pick from the library</div>`;
    }
    const groups = groupByCategory(sec.items);
    const otherSections = sections.filter(s => s._cid !== sec._cid);

    return groups.map(group => `
        <div class="mc-cat-group">
            <div class="mc-cat-label">
                ${escHtml(group.category_en)}
                <span class="mc-cat-label-ta">${escHtml(group.category_ta)}</span>
            </div>
            ${group.items.map(item => {
                const idx = sec.items.indexOf(item);
                const moveToHtml = otherSections.length > 0
                    ? `<div class="mc-item-moveto">
                        <button type="button" class="mc-item-moveto-btn"
                                title="Move to another section"
                                onclick="event.stopPropagation();toggleMoveTo('${sec._cid}',${idx},event)">
                            <i class="bi bi-arrow-right-square"></i>
                        </button>
                        <div class="mc-moveto-menu" id="moveto-${sec._cid}-${idx}">
                            ${otherSections.map(os => `
                                <button type="button" class="mc-moveto-item"
                                        onclick="event.stopPropagation();moveItemTo('${sec._cid}',${idx},'${os._cid}')">
                                    <i class="bi bi-arrow-right me-1" style="font-size:.75rem"></i>${escHtml(os.label_en)}
                                </button>`).join('')}
                        </div>
                      </div>` : '';
                return `
                <div class="mc-item-row"
                     draggable="true"
                     data-cid="${sec._cid}"
                     data-idx="${idx}"
                     ondragstart="onItemDragStart(event,'${sec._cid}',${idx})"
                     ondragend="onItemDragEnd(event)"
                     ondragover="onItemRowDragOver(event,'${sec._cid}',${idx})"
                     ondrop="onItemRowDrop(event,'${sec._cid}',${idx})"
                     onclick="event.stopPropagation()">
                    <div class="mc-item-drag" title="Drag to reorder">
                        <i class="bi bi-grip-vertical"></i>
                    </div>
                    <div class="mc-item-names">
                        <div class="mc-item-en">${escHtml(item.item_en)}</div>
                        <div class="mc-item-ta">${escHtml(item.item_ta)}</div>
                    </div>
                    <div class="mc-item-actions">
                        ${moveToHtml}
                        <button type="button" class="mc-item-remove"
                                onclick="event.stopPropagation();removeItem('${sec._cid}',${idx})"
                                aria-label="Remove ${escAttr(item.item_en)}">×</button>
                    </div>
                </div>`;
            }).join('')}
        </div>`).join('');
}

function sectionSummaryText(sec) {
    const items = sec.items || [];
    if (items.length === 0) return 'No Items';
    const cats  = new Set(items.map(i => i.category_key)).size;
    const pax   = sec.people_count ? number_format(sec.people_count) + ' Pax · ' : '';
    return pax + cats + (cats === 1 ? ' Cat' : ' Cat') + ' · ' + items.length + (items.length === 1 ? ' Item' : ' Items');
}

function number_format(n) {
    return Number(n).toLocaleString('en-IN');
}

function refreshSection(cid) {
    const sec = sections.find(s => s._cid === cid);
    if (!sec) return;
    const container = document.getElementById('items-' + cid);
    const countEl   = document.getElementById('count-' + cid);
    if (container) container.innerHTML = renderItemsHTML(sec);
    if (countEl)   countEl.textContent = sectionSummaryText(sec);

    // Re-attach item DnD drop zone listener after inner rerender
    if (container) {
        container.addEventListener('dragover',  onItemZoneDragOver);
        container.addEventListener('drop',      onItemZoneDrop);
        container.addEventListener('dragleave', onItemZoneDragLeave);
    }
}

// ── People count ──────────────────────────────────────────────────────────

window.updatePeopleCount = function (cid, val) {
    const sec = sections.find(s => s._cid === cid);
    if (!sec) return;
    const n = parseInt(val, 10);
    sec.people_count = (n > 0) ? n : null;
    const countEl = document.getElementById('count-' + cid);
    if (countEl) countEl.textContent = sectionSummaryText(sec);
    scheduleSave();
};

// ── Section management ────────────────────────────────────────────────────

window.deleteSection = function (cid) {
    const sec   = sections.find(s => s._cid === cid);
    const name  = sec?.label_en || 'this section';
    const count = sec?.items.length || 0;
    const msg   = count > 0
        ? `Delete "${name}" and its ${count} item${count !== 1 ? 's' : ''}?`
        : `Delete "${name}"?`;
    if (!confirm(msg)) return;
    sections = sections.filter(s => s._cid !== cid);
    if (activeCid === cid) activeCid = null;
    renderAllSections();
    scheduleSave();
};

window.duplicateSection = function (cid) {
    const sec = sections.find(s => s._cid === cid);
    if (!sec) return;
    const newCid = 'sec_' + (_cid++);
    const idx    = sections.indexOf(sec);
    const copy   = {
        _cid:         newCid,
        key:          sec.key,
        label_en:     sec.label_en + ' (Copy)',
        label_ta:     sec.label_ta,
        people_count: sec.people_count,
        items:        sec.items.map(i => ({ ...i })),
    };
    sections.splice(idx + 1, 0, copy);
    renderAllSections();
    scheduleSave();
    requestAnimationFrame(() => document.getElementById('section-' + newCid)?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
};

let _renameTarget = null;

window.openRename = function (cid) {
    _renameTarget = cid;
    const sec = sections.find(s => s._cid === cid);
    document.getElementById('renameLabelEn').value = sec?.label_en || '';
    document.getElementById('renameLabelTa').value = sec?.label_ta || '';
    document.getElementById('mcRenameModal').style.display = 'flex';
    setTimeout(() => document.getElementById('renameLabelEn').select(), 50);
};

window.closeRename = function () {
    document.getElementById('mcRenameModal').style.display = 'none';
    _renameTarget = null;
};

window.confirmRename = function () {
    const en = document.getElementById('renameLabelEn').value.trim();
    if (!en) { document.getElementById('renameLabelEn').focus(); return; }
    const ta = document.getElementById('renameLabelTa').value.trim();
    const sec = sections.find(s => s._cid === _renameTarget);
    if (sec) { sec.label_en = en; sec.label_ta = ta; renderAllSections(); scheduleSave(); }
    closeRename();
};

// ── Section drag-and-drop (reorder sections) ──────────────────────────────

function onSecDragStart(e) {
    // Only drag when handle or header clicked, not when item drag is active
    if (dragType === 'item') { e.preventDefault(); return; }
    dragType = 'section';
    dragCid  = e.currentTarget.dataset.cid;
    e.currentTarget.classList.add('mc-section--dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', dragCid);
}

function onSecDragEnd(e) {
    e.currentTarget.classList.remove('mc-section--dragging');
    document.querySelectorAll('.mc-section--dragover, .mc-section--item-drop').forEach(el => {
        el.classList.remove('mc-section--dragover', 'mc-section--item-drop');
    });
    if (dragType === 'section') { dragType = null; dragCid = null; dragOver = null; }
}

function onSecDragOver(e) {
    if (dragType !== 'section') return; // item DnD handled separately
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const targetCid = e.currentTarget.dataset.cid;
    if (targetCid === dragCid) return;
    if (targetCid !== dragOver) {
        document.querySelectorAll('.mc-section--dragover').forEach(el => el.classList.remove('mc-section--dragover'));
        e.currentTarget.classList.add('mc-section--dragover');
        dragOver = targetCid;
    }
}

function onSecDragLeave(e) {
    if (dragType !== 'section') return;
    if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('mc-section--dragover');
        if (dragOver === e.currentTarget.dataset.cid) dragOver = null;
    }
}

function onSecDrop(e) {
    if (dragType !== 'section') return;
    e.preventDefault();
    const targetCid = e.currentTarget.dataset.cid;
    if (!dragCid || targetCid === dragCid) return;

    const fromIdx = sections.findIndex(s => s._cid === dragCid);
    const toIdx   = sections.findIndex(s => s._cid === targetCid);
    if (fromIdx < 0 || toIdx < 0) return;

    const [moved] = sections.splice(fromIdx, 1);
    sections.splice(toIdx, 0, moved);

    renderAllSections();
    scheduleSave();
}

// ── Item drag-and-drop ────────────────────────────────────────────────────

window.onItemDragStart = function (e, fromCid, itemIdx) {
    e.stopPropagation(); // prevent section DnD from triggering
    dragType     = 'item';
    itemDragData = { fromCid, itemIdx };
    e.currentTarget.classList.add('mc-item--dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', fromCid + ':' + itemIdx);
};

window.onItemDragEnd = function (e) {
    e.currentTarget.classList.remove('mc-item--dragging');
    document.querySelectorAll('.mc-item--dragover-before').forEach(el => el.classList.remove('mc-item--dragover-before'));
    document.querySelectorAll('.mc-section--item-drop').forEach(el => el.classList.remove('mc-section--item-drop'));
    dragType        = null;
    itemDragData    = null;
    itemDragOverCid = null;
    itemDragOverIdx = null;
};

// Drag over individual item row (within-section reorder + shows insertion point)
window.onItemRowDragOver = function (e, toCid, toIdx) {
    if (dragType !== 'item') return;
    e.preventDefault();
    e.stopPropagation();
    e.dataTransfer.dropEffect = 'move';

    const sameSection = itemDragData?.fromCid === toCid;

    // Don't show insert-before on the item being dragged
    if (sameSection && toIdx === itemDragData.itemIdx) return;

    // Clear previous indicators
    if (itemDragOverCid !== toCid || itemDragOverIdx !== toIdx) {
        document.querySelectorAll('.mc-item--dragover-before').forEach(el => el.classList.remove('mc-item--dragover-before'));
        e.currentTarget.classList.add('mc-item--dragover-before');
        itemDragOverCid = toCid;
        itemDragOverIdx = toIdx;
    }

    // Highlight the section card
    if (itemDragData?.fromCid !== toCid) {
        document.querySelectorAll('.mc-section--item-drop').forEach(el => el.classList.remove('mc-section--item-drop'));
        document.getElementById('section-' + toCid)?.classList.add('mc-section--item-drop');
    }
};

// Drop on individual item row
window.onItemRowDrop = function (e, toCid, toIdx) {
    if (dragType !== 'item' || !itemDragData) return;
    e.preventDefault();
    e.stopPropagation();

    const { fromCid, itemIdx: fromIdx } = itemDragData;
    moveOrReorder(fromCid, fromIdx, toCid, toIdx);
};

// Drag over the entire items zone (cross-section drop to append)
function onItemZoneDragOver(e) {
    if (dragType !== 'item') return;
    const toCid = e.currentTarget.dataset.cid;
    if (!toCid || toCid === itemDragData?.fromCid) return;
    e.preventDefault();
    e.stopPropagation();
    e.dataTransfer.dropEffect = 'move';
    if (itemDragOverCid !== toCid) {
        document.querySelectorAll('.mc-section--item-drop').forEach(el => el.classList.remove('mc-section--item-drop'));
        document.getElementById('section-' + toCid)?.classList.add('mc-section--item-drop');
        itemDragOverCid = toCid;
        itemDragOverIdx = null;
    }
}

function onItemZoneDrop(e) {
    if (dragType !== 'item' || !itemDragData) return;
    const toCid = e.currentTarget.dataset.cid;
    if (!toCid || toCid === itemDragData.fromCid) return;
    e.preventDefault();
    e.stopPropagation();

    const toSec  = sections.find(s => s._cid === toCid);
    const toIdx  = toSec ? toSec.items.length : 0; // append
    moveOrReorder(itemDragData.fromCid, itemDragData.itemIdx, toCid, toIdx);
}

function onItemZoneDragLeave(e) {
    if (!e.currentTarget.contains(e.relatedTarget)) {
        const toCid = e.currentTarget.dataset.cid;
        document.getElementById('section-' + toCid)?.classList.remove('mc-section--item-drop');
        if (itemDragOverCid === toCid) itemDragOverCid = null;
    }
}

// Core: move item from fromCid[fromIdx] to toCid, inserting before toIdx
function moveOrReorder(fromCid, fromIdx, toCid, toIdx) {
    const fromSec = sections.find(s => s._cid === fromCid);
    const toSec   = sections.find(s => s._cid === toCid);
    if (!fromSec || !toSec) return;

    const [item] = fromSec.items.splice(fromIdx, 1);

    if (fromCid === toCid) {
        // Same section: reorder — adjust index after splice
        const insertAt = toIdx > fromIdx ? toIdx - 1 : toIdx;
        fromSec.items.splice(insertAt, 0, item);
        refreshSection(fromCid);
    } else {
        // Cross-section: move
        const insertAt = Math.min(toIdx, toSec.items.length);
        toSec.items.splice(insertAt, 0, item);
        refreshSection(fromCid);
        refreshSection(toCid);
    }
    scheduleSave();
}

// ── Move item between sections (menu) ────────────────────────────────────

window.toggleMoveTo = function (cid, idx, e) {
    const menuId = 'moveto-' + cid + '-' + idx;
    document.querySelectorAll('.mc-moveto-menu.open').forEach(m => {
        if (m.id !== menuId) m.classList.remove('open');
    });
    document.getElementById(menuId)?.classList.toggle('open');
};

window.moveItemTo = function (fromCid, itemIdx, toCid) {
    document.querySelectorAll('.mc-moveto-menu.open').forEach(m => m.classList.remove('open'));
    const toSec = sections.find(s => s._cid === toCid);
    if (!toSec) return;
    moveOrReorder(fromCid, itemIdx, toCid, toSec.items.length);
};

// ── Item management ───────────────────────────────────────────────────────

window.removeItem = function (cid, idx) {
    const sec = sections.find(s => s._cid === cid);
    if (!sec) return;
    sec.items.splice(idx, 1);
    refreshSection(cid);
    scheduleSave();
};

function addItem(cid, item) {
    const sec = sections.find(s => s._cid === cid);
    if (!sec) return;
    if (sec.items.some(i => i.id === item.id)) {
        showToast(`"${item.item_en}" already in ${sec.label_en}`);
        return;
    }
    sec.items.push({ ...item });
    refreshSection(cid);
    clearSearch(cid);
    scheduleSave();

    // Gold flash on section header
    const hdr = document.querySelector('#section-' + cid + ' .mc-section-hdr');
    if (hdr) { hdr.style.background = 'rgba(160,114,58,.18)'; setTimeout(() => hdr.style.background = '', 350); }
}

// ── In-section search ─────────────────────────────────────────────────────

window.handleSearch = function (input) {
    const cid   = input.dataset.cid;
    const query = input.value;
    document.getElementById('clear-' + cid)?.classList.toggle('show', query.length > 0);
    if (!query.trim()) { closeResults(cid); return; }
    clearTimeout(searchTimers[cid]);
    searchTimers[cid] = setTimeout(() => {
        const res = searchItems(query);
        searchResults[cid] = res;
        focusedIdx[cid]    = -1;
        renderResults(cid, res, query);
        openResults(cid);
    }, 60);
};

function searchItems(query) {
    const q = query.toLowerCase();
    return allItems.filter(item =>
        item.item_en.toLowerCase().includes(q) ||
        item.item_ta.includes(query) ||
        item.category_en.toLowerCase().includes(q)
    ).slice(0, 25);
}

function renderResults(cid, results, query) {
    const box = document.getElementById('results-' + cid);
    if (!box) return;
    const sec = sections.find(s => s._cid === cid);
    if (results.length === 0) {
        box.innerHTML = `<div class="mc-result-empty">No items found for "<strong>${escHtml(query)}</strong>"<br><small>Add it in Menu Item Master first.</small></div>`;
    } else {
        box.innerHTML = results.map((item, idx) => {
            const added = sec?.items.some(i => i.id === item.id) || false;
            return `<div class="mc-result-item${added ? ' mc-result--added' : ''}" role="option"
                        data-idx="${idx}"
                        onmousedown="event.preventDefault()"
                        onclick="pickResult('${cid}',${idx})">
                <span class="mc-result-cat">${escHtml(item.category_en)}</span>
                <div class="mc-result-names">
                    <div class="mc-result-en">${highlightMatch(item.item_en, query)}</div>
                    <div class="mc-result-ta">${escHtml(item.item_ta)}</div>
                </div>
                ${added ? '<span class="mc-result-added-badge">Added ✓</span>' : ''}
            </div>`;
        }).join('');
    }
    box.classList.add('open');
}

window.openResults = function (cid) {
    clearTimeout(closeTimers[cid]);
    const inp = document.getElementById('search-' + cid);
    if (inp?.value.trim()) document.getElementById('results-' + cid)?.classList.add('open');
};

window.scheduleClose = function (cid) {
    closeTimers[cid] = setTimeout(() => closeResults(cid), 160);
};

function closeResults(cid) {
    document.getElementById('results-' + cid)?.classList.remove('open');
    focusedIdx[cid] = -1;
}

window.clearSearch = function (cid) {
    const inp = document.getElementById('search-' + cid);
    if (inp) inp.value = '';
    searchResults[cid] = [];
    document.getElementById('clear-' + cid)?.classList.remove('show');
    closeResults(cid);
};

window.pickResult = function (cid, idx) {
    const item = (searchResults[cid] || [])[idx];
    if (item) addItem(cid, item);
};

window.handleSearchKey = function (e, cid) {
    const results = searchResults[cid] || [];
    const box     = document.getElementById('results-' + cid);
    let   idx     = focusedIdx[cid] ?? -1;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        idx = Math.min(idx + 1, results.length - 1);
        focusedIdx[cid] = idx;
        highlightRow(box, idx);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        idx = Math.max(idx - 1, -1);
        focusedIdx[cid] = idx;
        highlightRow(box, idx);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (idx >= 0 && results[idx]) addItem(cid, results[idx]);
        else if (results.length === 1) addItem(cid, results[0]);
    } else if (e.key === 'Escape') {
        closeResults(cid);
        document.getElementById('search-' + cid)?.blur();
    }
};

function highlightRow(box, idx) {
    if (!box) return;
    box.querySelectorAll('.mc-result-item').forEach((el, i) => el.classList.toggle('focused', i === idx));
    if (idx >= 0) box.querySelectorAll('.mc-result-item')[idx]?.scrollIntoView({ block: 'nearest' });
}

// ── Category grouping ─────────────────────────────────────────────────────

function groupByCategory(items) {
    const map = {};
    items.forEach(item => {
        if (!map[item.category_key]) {
            map[item.category_key] = { category_key: item.category_key, category_en: item.category_en, category_ta: item.category_ta, items: [] };
        }
        map[item.category_key].items.push(item);
    });
    return catKeys.filter(k => map[k]).map(k => map[k]);
}

// ── Auto-save ─────────────────────────────────────────────────────────────

function scheduleSave() {
    setSaveStatus('saving', 'Saving…');
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => saveDraft(false), 1800);
}

window.saveDraft = async function (manual = false) {
    clearTimeout(saveTimer);
    const title = document.getElementById('mcTitle').value.trim();
    if (!title) {
        showToast('Add a menu title before saving.');
        document.getElementById('mcTitle').focus();
        return;
    }
    setSaveStatus('saving', 'Saving…');

    const payload = {
        title,
        venue:      document.getElementById('mcVenue').value.trim() || null,
        event_date: document.getElementById('mcDate').value         || null,
        content:    sections.map(({ _cid, ...s }) => s),
    };

    try {
        const url    = draftId ? MC.routes.updateDraft + draftId : MC.routes.storeDraft;
        const method = draftId ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MC.csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);

        const data = await res.json();
        if (!draftId && data.id) {
            draftId = data.id;
            if (data.edit_url) history.replaceState({}, '', data.edit_url);
        }

        setSaveStatus('saved', 'All changes saved');
        if (manual) showToast('Draft saved ✓');
    } catch {
        setSaveStatus('error', 'Save failed');
        if (manual) showToast('Save failed. Check your connection.');
    }
};

function setSaveStatus(type, text) {
    const el = document.getElementById('mcSaveStatus');
    if (el) el.className = 'mc-save-status show ' + type;
    const t = document.getElementById('mcSaveText');
    if (t) t.textContent = text;
}

// ── PDF generation ────────────────────────────────────────────────────────

window.generatePdf = function (lang, withLetterhead = true) {
    const title = document.getElementById('mcTitle').value.trim();
    if (!title) { showToast('Add a menu title before generating PDF.'); document.getElementById('mcTitle').focus(); return; }
    if (sections.length === 0 || sections.every(s => s.items.length === 0)) {
        showToast('Add items to the menu first.'); return;
    }

    const label = { en: 'EN', ta: 'Tamil', bi: 'Bilingual' }[lang] || lang;
    showToast('Generating ' + label + ' PDF…');

    // Send JSON body so Laravel receives content as an array (not a string).
    // Accept: application/pdf ensures validation errors return JSON (422),
    // not a redirect that fetch follows back to the HTML page.
    const payload = {
        lang,
        title,
        venue:       document.getElementById('mcVenue').value.trim(),
        event_date:  document.getElementById('mcDate').value || null,
        content:     sections.map(({ _cid, ...s }) => s),   // real array, not JSON string
        letterhead:  withLetterhead ? 1 : 0,
    };

    fetch(MC.routes.generatePdf, {
        method:  'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': MC.csrf,
            'Accept':       'application/pdf, application/json',
        },
        body: JSON.stringify(payload),
    })
        .then(res => {
            const ct = res.headers.get('Content-Type') || '';
            if (!res.ok || !ct.includes('pdf')) {
                return res.text().then(t => {
                    console.error('[MENU PDF] Bad response', { status: res.status, ct, snippet: t.slice(0, 500) });
                    let msg = 'Server error ' + res.status;
                    try { const j = JSON.parse(t); msg = j.message || JSON.stringify(j.errors || j); } catch (_) {}
                    throw new Error(msg);
                });
            }
            return res.blob();
        })
        .then(blob => {
            const url    = URL.createObjectURL(blob);
            const anchor = document.getElementById('mcPdfAnchor');
            const ts     = new Date().toISOString().slice(0, 19).replace(/[T:]/g, '-');
            anchor.href     = url;
            anchor.download = 'menu-' + ts + '-' + lang + '.pdf';
            anchor.click();
            setTimeout(() => URL.revokeObjectURL(url), 30000);
            showToast(label + ' PDF downloaded.');
        })
        .catch(err => {
            console.error('[MENU PDF] Generation failed:', err);
            showToast('PDF failed: ' + err.message, 'error');
        });
};

// ── WhatsApp copy ─────────────────────────────────────────────────────────

window.copyWhatsApp = function () {
    const title = document.getElementById('mcTitle').value.trim();
    let   text  = title ? title + '\n\n' : '';
    let   hasContent = false;

    sections.forEach(sec => {
        if (!sec.items.length) return;
        text += '*' + sec.label_en.toUpperCase() + '*';
        if (sec.people_count) text += ' (' + number_format(sec.people_count) + ' pax)';
        text += '\n\n';
        groupByCategory(sec.items).forEach(group => {
            text += group.category_en + '\n';
            group.items.forEach(item => { text += item.item_en + '\n'; });
            text += '\n';
        });
        hasContent = true;
    });

    if (!hasContent) { showToast('No items to copy. Add items first.'); return; }

    navigator.clipboard.writeText(text.trim()).then(() => {
        showToast('Menu text copied! Paste into WhatsApp.');
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = text.trim();
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('Menu text copied!');
    });
};

// ── Save as Template ──────────────────────────────────────────────────────

window.saveAsTemplate = function () {
    if (sections.length === 0 || sections.every(s => s.items.length === 0)) {
        showToast('Add items before saving as template.'); return;
    }
    const title = document.getElementById('mcTitle').value.trim();
    document.getElementById('tplModalName').value = title;
    document.getElementById('mcTplModal').style.display = 'flex';
    setTimeout(() => document.getElementById('tplModalName').focus(), 60);
};

window.closeTplModal = function () { document.getElementById('mcTplModal').style.display = 'none'; };

window.confirmSaveTemplate = async function () {
    const name = document.getElementById('tplModalName').value.trim();
    if (!name) { document.getElementById('tplModalName').focus(); return; }
    // Strip people_count from template (it's event-specific)
    const content = sections.map(({ _cid, people_count, ...s }) => s);
    try {
        const res = await fetch(MC.routes.storeTemplate, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': MC.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ name, content }),
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        closeTplModal();
        showToast('Template "' + name + '" saved ✓');
    } catch {
        showToast('Failed to save template. Try again.');
    }
};

// ── Toast ─────────────────────────────────────────────────────────────────

let toastTimer = null;
function showToast(msg, type = 'info') {
    const el = document.getElementById('mcToast');
    if (!el) return;
    el.textContent = msg;
    el.classList.toggle('mc-toast--error', type === 'error');
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), type === 'error' ? 5000 : 2800);
}

// ── Utilities ─────────────────────────────────────────────────────────────

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(str) { return escHtml(str); }

function highlightMatch(text, query) {
    if (!query) return escHtml(text);
    const escaped = escHtml(text);
    const q       = escHtml(query);
    const re      = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return escaped.replace(re, '<mark style="background:rgba(160,114,58,.2);border-radius:2px;padding:0 1px">$1</mark>');
}

})();
</script>
