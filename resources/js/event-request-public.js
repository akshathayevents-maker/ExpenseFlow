// Event Request Portal — public wizard interactions.
// Progressive enhancement only: server-side validation is the source of
// truth; this drives the 3-step, mobile-first menu configurator.
//
// Performance note: hundreds of menu items are never rendered into the DOM
// up front. They arrive as a small JSON payload and item cards are built
// lazily, only when a category is actually opened (see renderCategoryBody).

const rupee = value => '₹' + Math.round(value).toLocaleString('en-IN');
const CGST_RATE = 2.5;
const SGST_RATE = 2.5;
const escapeHtml = str => String(str).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const isDesktop = () => window.matchMedia('(min-width: 940px)').matches;

let menuData = [];
let itemIndex = new Map(); // itemId -> { item, categoryId, categoryName }
let selected = new Set();
let currentFilter = 'all'; // all | veg | non_veg
let openDesktopBodies = new Map(); // categoryId -> container element
let overlayCategoryId = null;
let currentStep = 1;

function loadMenuData() {
    const dataEl = document.getElementById('erpMenuData');
    const selectedEl = document.getElementById('erpSelectedIds');
    if (!dataEl) return;

    menuData = JSON.parse(dataEl.textContent || '[]');
    const initialSelected = selectedEl ? JSON.parse(selectedEl.textContent || '[]') : [];
    selected = new Set(initialSelected.map(Number));

    menuData.forEach(category => {
        category.items.forEach(item => {
            itemIndex.set(item.id, { item, categoryId: category.id, categoryName: category.name });
        });
    });
}

function itemMatchesFilter(item) {
    if (currentFilter === 'veg') return item.is_veg;
    if (currentFilter === 'non_veg') return !item.is_veg;
    if (currentFilter === 'popular') return item.is_popular;
    return true;
}

function syncHiddenInputs() {
    const container = document.getElementById('selectedInputsContainer');
    if (!container) return;
    container.innerHTML = '';
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'menu_item_ids[]';
        input.value = id;
        container.appendChild(input);
    });
}

function toggleItem(itemId) {
    if (selected.has(itemId)) selected.delete(itemId);
    else selected.add(itemId);
    syncHiddenInputs();
    recalcTotals();
    renderCategoryList();
    refreshOpenBodies();
}

function refreshOpenBodies() {
    openDesktopBodies.forEach((container, categoryId) => {
        const category = menuData.find(c => c.id === categoryId);
        if (category) renderCategoryBody(container, category);
    });
    if (overlayCategoryId !== null) {
        const category = menuData.find(c => c.id === overlayCategoryId);
        const overlayBody = document.getElementById('catOverlayBody');
        if (category && overlayBody) renderCategoryBody(overlayBody, category, { keepSearch: true });
    }
}

// ── Dish row ─────────────────────────────────────────────────────────────
// A menu checklist, not a shopping card: one compact 48–56px row per dish,
// a circular selector instead of an "Add" button, no repeated "/person",
// and the description stays collapsed until the row itself is tapped.
function buildItemCard(item) {
    const row = document.createElement('div');
    const isSelected = selected.has(item.id);
    row.className = 'erp-dish-row' + (isSelected ? ' selected' : '');
    row.dataset.itemId = item.id;

    const tags = [];
    if (item.is_popular) tags.push('Popular');
    if (item.is_chef_recommended) tags.push('Chef Recommended');

    row.innerHTML = `
        <span class="erp-dish-selector" role="checkbox" aria-checked="${isSelected ? 'true' : 'false'}" tabindex="0" aria-label="Select ${item.name}">
            <i class="bi bi-check-lg"></i>
        </span>
        <div class="erp-dish-main">
            <div class="erp-dish-line">
                <span class="erp-veg-dot ${item.is_veg ? 'veg' : 'non_veg'}"></span>
                <span class="erp-dish-name"></span>
                <span class="erp-dish-price">${rupee(item.price)}</span>
            </div>
            ${tags.length ? `<div class="erp-dish-tags">${tags.join(' · ')}</div>` : ''}
            ${item.description ? '<div class="erp-dish-desc"></div>' : ''}
        </div>
    `;
    row.querySelector('.erp-dish-name').textContent = item.name;
    if (item.description) row.querySelector('.erp-dish-desc').textContent = item.description;

    const selector = row.querySelector('.erp-dish-selector');
    selector.addEventListener('click', e => { e.stopPropagation(); toggleItem(item.id); });
    selector.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); e.stopPropagation(); toggleItem(item.id); }
    });

    if (item.description) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', () => row.classList.toggle('expanded'));
    }

    return row;
}

// ── Category body (search + veg/non-veg groups) — built lazily ──────────
function renderCategoryBody(container, category, opts = {}) {
    const existingSearch = opts.keepSearch ? container.querySelector('.erp-search-box input')?.value ?? '' : '';

    container.innerHTML = '';

    const searchBox = document.createElement('div');
    searchBox.className = 'erp-search-box';
    searchBox.innerHTML = '<i class="bi bi-search"></i><input type="search" placeholder="Search menu" aria-label="Search menu">';
    const searchInput = searchBox.querySelector('input');
    searchInput.value = existingSearch;
    container.appendChild(searchBox);
    container.appendChild(buildFilterTabsElement());

    const list = document.createElement('div');
    container.appendChild(list);

    function draw() {
        const term = searchInput.value.trim().toLowerCase();
        list.innerHTML = '';

        const vegItems = category.items.filter(i => i.is_veg && itemMatchesFilter(i) && i.name.toLowerCase().includes(term));
        const nonVegItems = category.items.filter(i => !i.is_veg && itemMatchesFilter(i) && i.name.toLowerCase().includes(term));

        if (!vegItems.length && !nonVegItems.length) {
            const empty = document.createElement('div');
            empty.className = 'erp-cat-empty';
            empty.textContent = term ? `No dishes match "${term}".` : 'No dishes match the current filter.';
            list.appendChild(empty);
            return;
        }

        if (vegItems.length) {
            const divider = document.createElement('div');
            divider.className = 'erp-veg-divider veg';
            divider.textContent = 'Veg';
            list.appendChild(divider);
            const grid = document.createElement('div');
            grid.className = 'erp-dish-list';
            vegItems.forEach(i => grid.appendChild(buildItemCard(i)));
            list.appendChild(grid);
        }
        if (nonVegItems.length) {
            const divider = document.createElement('div');
            divider.className = 'erp-veg-divider non_veg';
            divider.textContent = 'Non-Veg';
            list.appendChild(divider);
            const grid = document.createElement('div');
            grid.className = 'erp-dish-list';
            nonVegItems.forEach(i => grid.appendChild(buildItemCard(i)));
            list.appendChild(grid);
        }
    }

    searchInput.addEventListener('input', draw);
    draw();
}

// ── Category list (Step 2 landing view) ──────────────────────────────────
function categorySelectedCount(category) {
    return category.items.filter(i => selected.has(i.id)).length;
}
function categoryAvailableCount(category) {
    return category.items.filter(itemMatchesFilter).length;
}
function categoryTotalCount(category) {
    return category.items.length;
}

function renderCategoryList() {
    const listEl = document.getElementById('categoryList');
    if (!listEl) return;

    const openIds = new Set(openDesktopBodies.keys());
    listEl.innerHTML = '';
    openDesktopBodies = new Map();

    menuData.forEach(category => {
        const row = document.createElement('div');
        row.className = 'erp-cat-row';
        row.dataset.categoryId = category.id;
        row.setAttribute('role', 'button');
        row.tabIndex = 0;

        const selectedCount = categorySelectedCount(category);
        const totalCount = categoryTotalCount(category);
        const availableCount = categoryAvailableCount(category);
        const done = selectedCount > 0;

        row.innerHTML = `
            <span class="erp-cat-row-icon">${category.icon}</span>
            <div class="erp-cat-row-main">
                <div class="erp-cat-row-name">
                    <span class="erp-cat-complete-glyph ${done ? 'done' : ''}">${done ? '✓' : '○'}</span>
                    <span class="erp-cat-row-name-text"></span>
                    ${currentFilter !== 'all' && availableCount !== totalCount ? `<span class="erp-cat-row-filtered">(${availableCount} match filter)</span>` : ''}
                </div>
                <div class="erp-cat-row-meta">
                    <span class="erp-progress-badge ${done ? 'has-selection' : ''}">${done ? selectedCount + ' of ' + totalCount + ' Selected' : 'Not selected yet'}</span>
                </div>
            </div>
            <i class="bi bi-chevron-right erp-cat-row-chevron"></i>
        `;
        row.querySelector('.erp-cat-row-name-text').textContent = category.name;

        const inlineBody = document.createElement('div');
        inlineBody.className = 'erp-cat-inline-body';
        inlineBody.innerHTML = '<div><div class="erp-cat-inline-body-inner"></div></div>';

        row.addEventListener('click', () => onCategoryRowClick(category, row, inlineBody));
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onCategoryRowClick(category, row, inlineBody); }
        });

        listEl.appendChild(row);
        listEl.appendChild(inlineBody);

        if (openIds.has(category.id)) {
            inlineBody.classList.add('open');
            row.querySelector('.erp-cat-row-chevron').style.transform = 'rotate(90deg)';
            const inner = inlineBody.querySelector('.erp-cat-inline-body-inner');
            renderCategoryBody(inner, category);
            openDesktopBodies.set(category.id, inner);
        }
    });
}

function onCategoryRowClick(category, row, inlineBody) {
    if (isDesktop()) {
        const isOpen = inlineBody.classList.toggle('open');
        row.querySelector('.erp-cat-row-chevron').style.transform = isOpen ? 'rotate(90deg)' : '';
        if (isOpen) {
            const inner = inlineBody.querySelector('.erp-cat-inline-body-inner');
            renderCategoryBody(inner, category);
            openDesktopBodies.set(category.id, inner);
        } else {
            openDesktopBodies.delete(category.id);
        }
    } else {
        openCategoryOverlay(category);
    }
}

function openCategoryOverlay(category) {
    overlayCategoryId = category.id;
    document.getElementById('catOverlayTitle').textContent = category.name;
    const body = document.getElementById('catOverlayBody');
    renderCategoryBody(body, category);
    document.getElementById('catOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCategoryOverlay() {
    document.getElementById('catOverlay').classList.remove('open');
    document.body.style.overflow = '';
    overlayCategoryId = null;
    renderCategoryList(); // refresh counts on the row that was just closed
}

function initCategoryOverlay() {
    document.getElementById('catOverlayBack')?.addEventListener('click', closeCategoryOverlay);
    document.getElementById('catOverlayDone')?.addEventListener('click', closeCategoryOverlay);
}

const FILTER_OPTIONS = [
    ['all', 'All'],
    ['veg', 'Veg'],
    ['non_veg', 'Non-Veg'],
    ['popular', 'Popular'],
];

function setFilter(value) {
    currentFilter = value;
    document.querySelectorAll('.erp-filter-tab').forEach(b => {
        b.classList.toggle('selected', b.dataset.filter === value);
    });
    renderCategoryList();
    refreshOpenBodies();
}

// Filter tabs are rebuilt in every place they appear (the outer category
// list AND inside each opened category) so the same All/Veg/Non-Veg/Popular
// control is always visible, never just on a screen the user has to back
// out of to reach.
function buildFilterTabsElement() {
    const tabs = document.createElement('div');
    tabs.className = 'erp-filter-tabs';
    tabs.setAttribute('role', 'radiogroup');
    tabs.setAttribute('aria-label', 'Diet filter');
    FILTER_OPTIONS.forEach(([value, label]) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'erp-filter-tab' + (currentFilter === value ? ' selected' : '');
        btn.dataset.filter = value;
        btn.textContent = label;
        btn.addEventListener('click', () => setFilter(value));
        tabs.appendChild(btn);
    });
    return tabs;
}

function initGlobalFilter() {
    const tabs = document.getElementById('globalFilterTabs');
    if (!tabs) return;
    tabs.querySelectorAll('.erp-filter-tab').forEach(btn => {
        btn.addEventListener('click', () => setFilter(btn.dataset.filter));
    });
}

// ── Totals (desktop rail + mobile bar + sheet) ───────────────────────────
function bumpTotal(el) {
    if (!el) return;
    el.classList.remove('bump');
    void el.offsetWidth;
    el.classList.add('bump');
}

function recalcTotals() {
    const guestInput = document.getElementById('guest_count');
    const guests = Number(guestInput ? guestInput.value : 0) || 0;
    const perPerson = [...selected].reduce((sum, id) => sum + (itemIndex.get(id)?.item.price || 0), 0);
    const subtotal = perPerson * guests;
    const cgst = subtotal * CGST_RATE / 100;
    const sgst = subtotal * SGST_RATE / 100;
    const total = subtotal + cgst + sgst;
    const count = selected.size;

    const setText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

    setText('sumGuests', guests.toLocaleString('en-IN'));
    setText('sumCount', count);
    setText('sumPerPerson', rupee(perPerson));
    setText('sumSubtotal', rupee(subtotal));
    setText('sumCgst', rupee(cgst));
    setText('sumSgst', rupee(sgst));
    setText('sumTotal', rupee(total));
    bumpTotal(document.getElementById('sumTotal'));

    setText('barCount', count);
    setText('barPerPerson', rupee(perPerson));
    setText('barTotal', rupee(total));
    bumpTotal(document.getElementById('barTotal'));

    setText('sheetGuests', guests.toLocaleString('en-IN'));
    setText('sheetCount', count);
    setText('sheetPerPerson', rupee(perPerson));
    setText('sheetSubtotal', rupee(subtotal));
    setText('sheetCgst', rupee(cgst));
    setText('sheetSgst', rupee(sgst));
    setText('sheetTotal', rupee(total));
    bumpTotal(document.getElementById('sheetTotal'));

    const sheetList = document.getElementById('sheetSelectedList');
    if (sheetList) {
        sheetList.innerHTML = [...selected].map(id => {
            const entry = itemIndex.get(id);
            if (!entry) return '';
            return `<div class="erp-sheet-selected-item"><span class="name">${escapeHtml(entry.item.name)}</span><span class="price">${rupee(entry.item.price)}</span></div>`;
        }).join('');
    }

    ['toStep3Btn', 'desktopReviewBtn'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.disabled = count === 0;
    });
    const sheetActionBtn = document.getElementById('sheetActionBtn');
    if (sheetActionBtn && sheetActionBtn.dataset.mode !== 'submit') sheetActionBtn.disabled = count === 0;
}

// ── Step navigation (1 → 2 → 3) ───────────────────────────────────────────
function setStep(stepNumber) {
    currentStep = stepNumber;
    [1, 2, 3].forEach(n => {
        const el = document.getElementById('step' + n);
        if (el) el.classList.toggle('active', n === stepNumber);
        const dot = document.getElementById('progDot' + n);
        if (dot) dot.classList.toggle('done', n <= stepNumber);
    });
    const stepIndicator = document.getElementById('stepIndicator');
    if (stepIndicator) stepIndicator.textContent = `Step ${stepNumber} of 3`;

    const stickyBar = document.getElementById('mobileStickyBar');
    if (stickyBar) stickyBar.style.display = (stepNumber === 2 && !isDesktop()) ? 'flex' : 'none';

    document.dispatchEvent(new CustomEvent('erp:step-changed', { detail: { step: stepNumber } }));
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function pillLabel(pickerId) {
    const picker = document.getElementById(pickerId);
    return picker?.querySelector('.selected')?.textContent?.trim() || '—';
}

function buildReview() {
    const recap = document.getElementById('reviewRecap');
    const groups = document.getElementById('reviewGroups');
    if (!recap || !groups) return;

    const fields = [
        ['Client', document.getElementById('client_name')?.value || '—'],
        ['Event Date', document.getElementById('event_date')?.value || '—'],
        ['Guests', document.getElementById('guest_count')?.value || '—'],
        ['Meal Type', pillLabel('mealTypePicker')],
        ['Menu Type', pillLabel('menuTypePicker')],
        ['Items Selected', String(selected.size)],
    ];
    recap.innerHTML = fields.map(([k, v]) => `
        <div class="erp-review-kv"><div class="k">${k}</div><div class="v"></div></div>
    `).join('');
    recap.querySelectorAll('.erp-review-kv .v').forEach((el, i) => { el.textContent = fields[i][1]; });

    const byCategory = new Map();
    selected.forEach(id => {
        const entry = itemIndex.get(id);
        if (!entry) return;
        if (!byCategory.has(entry.categoryName)) byCategory.set(entry.categoryName, []);
        byCategory.get(entry.categoryName).push(entry.item);
    });

    groups.innerHTML = '';
    byCategory.forEach((items, categoryName) => {
        const group = document.createElement('div');
        group.className = 'erp-review-group';
        const title = document.createElement('div');
        title.className = 'erp-review-group-title';
        title.textContent = categoryName;
        group.appendChild(title);
        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'erp-review-item-row';
            row.innerHTML = `<span class="check">✓</span><span class="name"></span><span class="price">${rupee(item.price)}</span>`;
            row.querySelector('.name').textContent = item.name;
            group.appendChild(row);
        });
        groups.appendChild(group);
    });

    const guests = Number(document.getElementById('guest_count')?.value || 0);
    const perPerson = [...selected].reduce((sum, id) => sum + (itemIndex.get(id)?.item.price || 0), 0);
    const subtotal = perPerson * guests;
    const cgst = subtotal * CGST_RATE / 100;
    const sgst = subtotal * SGST_RATE / 100;

    const setText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };
    setText('reviewPerPerson', rupee(perPerson));
    setText('reviewSubtotal', rupee(subtotal));
    setText('reviewCgst', rupee(cgst));
    setText('reviewSgst', rupee(sgst));
    setText('reviewTotal', rupee(subtotal + cgst + sgst));
}

function initStepNavigation() {
    document.getElementById('toStep2Btn')?.addEventListener('click', () => {
        const required = ['client_name', 'client_mobile', 'event_date', 'guest_count'];
        for (const id of required) {
            const el = document.getElementById(id);
            if (el && !el.value) { el.reportValidity(); return; }
        }
        if (!document.getElementById('meal_type').value) return alert('Please select a meal type.');
        if (!document.getElementById('menu_type').value) return alert('Please select a menu type.');
        setStep(2);
    });

    document.getElementById('backToStep1Btn')?.addEventListener('click', () => setStep(1));

    const goToReview = () => { if (selected.size === 0) return; buildReview(); setStep(3); };
    document.getElementById('toStep3Btn')?.addEventListener('click', goToReview);
    document.getElementById('desktopReviewBtn')?.addEventListener('click', goToReview);

    document.getElementById('backToStep2Btn')?.addEventListener('click', () => setStep(2));
}

function initPillPickers() {
    function wire(pickerId, hiddenId) {
        const picker = document.getElementById(pickerId);
        const hidden = document.getElementById(hiddenId);
        if (!picker || !hidden) return;
        picker.querySelectorAll('[data-value]').forEach(btn => {
            btn.addEventListener('click', () => {
                picker.querySelectorAll('[data-value]').forEach(b => {
                    b.classList.remove('selected');
                    b.setAttribute('aria-checked', 'false');
                });
                btn.classList.add('selected');
                btn.setAttribute('aria-checked', 'true');
                hidden.value = btn.dataset.value;
            });
        });
    }
    wire('mealTypePicker', 'meal_type');
    wire('menuTypePicker', 'menu_type');
}

// ── Mobile sticky bar + bottom sheet ──────────────────────────────────────
function initMobileSheet() {
    const sheet = document.getElementById('mobileSheet');
    const backdrop = document.getElementById('mobileSheetBackdrop');
    const viewSummaryBtn = document.getElementById('viewSummaryBtn');
    const sheetClose = document.getElementById('sheetClose');
    const sheetActionBtn = document.getElementById('sheetActionBtn');
    if (!sheet) return;

    function openSheet() {
        sheet.classList.add('expanded');
        backdrop?.classList.add('expanded');
    }
    function closeSheet() {
        sheet.classList.remove('expanded');
        backdrop?.classList.remove('expanded');
    }

    viewSummaryBtn?.addEventListener('click', openSheet);
    sheetClose?.addEventListener('click', closeSheet);
    backdrop?.addEventListener('click', closeSheet);

    function syncSheetAction(step) {
        if (!sheetActionBtn) return;
        if (step === 2) {
            sheetActionBtn.textContent = 'Continue';
            sheetActionBtn.dataset.mode = 'review';
            sheetActionBtn.type = 'button';
            sheetActionBtn.removeAttribute('form');
        } else if (step === 3) {
            sheetActionBtn.textContent = 'Submit request';
            sheetActionBtn.dataset.mode = 'submit';
            sheetActionBtn.type = 'submit';
            sheetActionBtn.setAttribute('form', 'erpForm');
            sheetActionBtn.disabled = false;
        }
    }
    sheetActionBtn?.addEventListener('click', () => {
        if (sheetActionBtn.dataset.mode === 'review') {
            closeSheet();
            if (selected.size === 0) return;
            buildReview();
            setStep(3);
        }
    });
    document.addEventListener('erp:step-changed', e => {
        syncSheetAction(e.detail.step);
        if (e.detail.step !== 2) closeSheet();
    });
    syncSheetAction(2);
}

function initRipple() {
    document.querySelectorAll('.erp-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            ripple.className = 'erp-ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 500);
        });
    });
}

function initSubmitGuard() {
    const form = document.getElementById('erpForm');
    const submitBtn = document.getElementById('submitBtn');
    if (!form || !submitBtn) return;

    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="erp-spinner"></span> Submitting…';
        const sheetActionBtn = document.getElementById('sheetActionBtn');
        if (sheetActionBtn) sheetActionBtn.disabled = true;
    });
}

function initGuestCountField() {
    // Prevents a real bug: typing into a pre-filled numeric field without
    // selecting existing text first inserts at the caret position (often
    // the start), silently producing values like "50123" instead of "50".
    const el = document.getElementById('guest_count');
    if (el) el.addEventListener('focus', () => el.select());
}

function initDownloadSummary() {
    const btn = document.getElementById('downloadSummaryBtn');
    if (btn) btn.addEventListener('click', () => window.print());
}

function initResizeSync() {
    // Switching between mobile <-> desktop breakpoints mid-session (e.g.
    // rotating a tablet) should reset the category browser to a clean state.
    let lastIsDesktop = isDesktop();
    window.addEventListener('resize', () => {
        if (isDesktop() !== lastIsDesktop) {
            lastIsDesktop = isDesktop();
            closeCategoryOverlay();
            renderCategoryList();
            setStep(currentStep);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('erp-body');
    loadMenuData();
    syncHiddenInputs(); // reflect any pre-selected items (e.g. editing after "need changes") even if the client never opens a category
    initStepNavigation();
    initPillPickers();
    initGlobalFilter();
    initCategoryOverlay();
    renderCategoryList();
    recalcTotals();
    document.getElementById('guest_count')?.addEventListener('input', recalcTotals);
    initMobileSheet();
    initGuestCountField();
    initRipple();
    initSubmitGuard();
    initDownloadSummary();
    initResizeSync();
    setStep(1);
});
