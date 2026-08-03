@php
    $statusChip = match($eventRequest->status) {
        'rejected' => 'text-bg-danger',
        'scheduled' => 'text-bg-success',
        'need_changes' => 'text-bg-warning',
        'draft' => 'text-bg-secondary',
        default => 'text-bg-info',
    };
    $publicUrl = $token ? route('event-request.public.show', $token->token) : null;
    $canDecide = in_array($eventRequest->status, ['submitted', 'under_review', 'resubmitted']);

    $categoryIcons = ['welcome drinks' => '🥂', 'soup' => '🍲', 'starter' => '🍢', 'main course' => '🍛', 'rice' => '🍚', 'indian bread' => '🫓', 'gravy' => '🍛', 'dessert' => '🍮', 'ice cream' => '🍨', 'beverage' => '🥤'];

    $menuData = $categories->map(function ($category) use ($categoryIcons) {
        return [
            'id'    => $category->id,
            'name'  => $category->name,
            'icon'  => $categoryIcons[strtolower($category->name)] ?? '🍽',
            'items' => $category->activeItems->map(fn ($item) => [
                'id'                  => $item->id,
                'name'                => $item->name,
                'description'         => $item->description,
                'is_veg'              => (bool) $item->is_veg,
                'price'               => (float) $item->price_per_person,
                'is_popular'          => (bool) $item->is_popular,
                'is_chef_recommended' => (bool) $item->is_chef_recommended,
            ])->values(),
        ];
    })->values();

    $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
@endphp
<x-admin-layout title="Event Request · {{ $eventRequest->referenceNumber() }}">
<style>
    .erq-chip {
        border: 1.5px solid #e2dccc; background: #fff; color: #4a4536;
        font-size: .78rem; font-weight: 650; padding: 6px 13px; border-radius: 999px;
        cursor: pointer; transition: background .12s, border-color .12s, color .12s;
        white-space: nowrap;
    }
    .erq-chip:hover { border-color: #B8893E; }
    .erq-chip.active { background: #3E2D23; border-color: #3E2D23; color: #fff; }

    .erq-cat-block { margin-bottom: 18px; }
    .erq-cat-block:last-child { margin-bottom: 0; }
    .erq-cat-head { display: flex; align-items: center; justify-content: space-between; padding: 6px 2px; cursor: pointer; }
    .erq-cat-head-name { font-weight: 700; font-size: .88rem; display: flex; align-items: center; gap: 6px; }
    .erq-cat-head-progress { font-size: .74rem; font-weight: 650; color: #8a8370; }
    .erq-cat-head-progress.has-selection { color: #8A6820; }
    .erq-cat-chevron { transition: transform .18s ease; color: #9c8f79; }
    .erq-cat-block.collapsed .erq-cat-chevron { transform: rotate(-90deg); }
    .erq-cat-block.collapsed .erq-cat-body { display: none; }

    .erq-row {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 10px; cursor: pointer;
        min-height: 46px; transition: background .12s, border-color .12s, box-shadow .12s;
        border: 1.5px solid transparent;
    }
    .erq-row + .erq-row { margin-top: 4px; }
    .erq-row-glyph { width: 20px; text-align: center; flex-shrink: 0; font-size: .95rem; }
    .erq-row-main { flex: 1; min-width: 0; display: flex; align-items: baseline; gap: 8px; }
    .erq-row-name { font-size: .86rem; min-width: 0; }
    .erq-row-price { font-size: .82rem; font-variant-numeric: tabular-nums; flex-shrink: 0; margin-left: auto; }
    .erq-row-tag { font-size: .68rem; font-weight: 650; flex-shrink: 0; }

    /* Client-selected (untouched by admin) — dominant gold treatment */
    .erq-row.client {
        border-color: #C89B3C; background: rgba(200,155,60,.08);
        box-shadow: 0 1px 3px rgba(200,155,60,.15);
    }
    .erq-row.client .erq-row-glyph { color: #8A6820; }
    .erq-row.client .erq-row-name { font-weight: 700; color: #2A211A; }

    /* Admin added — blue */
    .erq-row.added { border-color: #3b82f6; background: rgba(59,130,246,.08); }
    .erq-row.added .erq-row-glyph { color: #2563eb; }
    .erq-row.added .erq-row-name { font-weight: 700; }

    /* Admin removed — red, still visible so the admin remembers what changed */
    .erq-row.removed { background: rgba(220,53,69,.06); }
    .erq-row.removed .erq-row-glyph { color: #dc3545; }
    .erq-row.removed .erq-row-name { text-decoration: line-through; color: #8a8370; }

    /* Unselected / available (Show All only) — faded, low priority */
    .erq-row.unselected { opacity: .68; }
    .erq-row.unselected:hover { opacity: 1; background: #faf8f3; }
    .erq-row.unselected .erq-row-glyph { color: #c8c4bb; }

    .erq-summary-item { display: flex; align-items: baseline; gap: 6px; font-size: .84rem; padding: 3px 0; }
    .erq-summary-item .check { color: #2E7D32; font-weight: 700; }
    .erq-summary-cat-title { font-size: .72rem; font-weight: 750; text-transform: uppercase; letter-spacing: .04em; color: #6B5D4C; margin: 10px 0 2px; }
    .erq-summary-cat-title:first-child { margin-top: 0; }

    .erq-stat-line { display: flex; justify-content: space-between; font-size: .84rem; padding: 6px 0; border-top: 1px solid #eee; }
    .erq-stat-line:first-of-type { border-top: none; }
    .erq-changes-bar { display: flex; gap: 16px; flex-wrap: wrap; background: #f3f0e8; border-radius: 10px; padding: 10px 14px; }
    .erq-changes-bar .stat { font-size: .78rem; font-weight: 650; }
    .erq-changes-bar .stat.added { color: #2563eb; }
    .erq-changes-bar .stat.removed { color: #dc3545; }
</style>

<div class="container-fluid py-4">

    <a href="{{ route('admin.event-requests.index') }}" class="text-decoration-none small text-muted mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Event Requests</a>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">{{ $eventRequest->event_name ?: $eventRequest->client_name ?: 'Untitled request' }}</h1>
            <div class="text-muted small">{{ $eventRequest->referenceNumber() }}</div>
        </div>
        <span class="badge {{ $statusChip }} fs-6">{{ $eventRequest->statusLabel() }}</span>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Public link management --}}
    <x-premium.card class="mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="flex-grow-1" style="min-width:260px">
                <div class="small fw-bold text-muted mb-1">Public link</div>
                @if($publicUrl)
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace small" readonly value="{{ $publicUrl }}" id="publicLinkInput">
                        <button class="btn btn-outline-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('publicLinkInput').value)">Copy</button>
                    </div>
                @else
                    <div class="text-muted small">No active link.</div>
                @endif
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.event-requests.regenerate-link', $eventRequest) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark" onclick="return confirm('Regenerate link? The old link will stop working.')">Regenerate</button>
                </form>
                @if($token)
                <form method="POST" action="{{ route('admin.event-requests.deactivate-link', $eventRequest) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this link?')">Deactivate</button>
                </form>
                @endif
            </div>
        </div>
    </x-premium.card>

    {{-- Menu data for the review UI — item cards/rows are rendered by JS so
         switching Selected Only / Show All / filters never round-trips. --}}
    <script type="application/json" id="erqMenuData">{!! json_encode($menuData, $jsonFlags) !!}</script>
    <script type="application/json" id="erqSelectedIds">{!! json_encode(array_values($selectedItemIds), $jsonFlags) !!}</script>
    <script type="application/json" id="erqBaselineIds">{!! json_encode(array_values($clientBaselineItemIds), $jsonFlags) !!}</script>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Selected menu summary — the first thing the admin should see --}}
            <x-premium.card class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h2 class="h6 fw-bold mb-0">Selected Menu</h2>
                    <span class="badge text-bg-dark" id="summaryCountBadge">0 Items Selected</span>
                </div>
                <div id="summaryList"></div>
                <button type="button" class="btn btn-sm btn-outline-dark mt-3" id="editSelectionBtn">
                    <i class="bi bi-pencil-square me-1"></i>Edit Selection
                </button>
            </x-premium.card>

            <form method="POST" action="{{ route('admin.event-requests.update', $eventRequest) }}" id="adminEditForm">
                @csrf
                @method('PUT')
                <div id="selectedInputsContainer"></div>

                <x-premium.card class="mb-4">
                    <h2 class="h6 fw-bold mb-3">Event details</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Client Name</label>
                            <input class="form-control" name="client_name" value="{{ old('client_name', $eventRequest->client_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Number</label>
                            <input class="form-control" name="client_mobile" value="{{ old('client_mobile', $eventRequest->client_mobile) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" name="client_email" value="{{ old('client_email', $eventRequest->client_email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Event Name</label>
                            <input class="form-control" name="event_name" value="{{ old('event_name', $eventRequest->event_name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Event Date</label>
                            <input type="date" class="form-control" name="event_date" value="{{ old('event_date', $eventRequest->event_date?->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Guest Count</label>
                            <input type="number" min="1" class="form-control" name="guest_count" id="guestCountInput" value="{{ old('guest_count', $eventRequest->guest_count) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Meal Type</label>
                            <select class="form-select" name="meal_type" required>
                                @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('meal_type', $eventRequest->meal_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Menu Type</label>
                            <select class="form-select" name="menu_type" required>
                                @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('menu_type', $eventRequest->menu_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Special Instructions</label>
                            <textarea class="form-control" name="special_instructions" rows="3">{{ old('special_instructions', $eventRequest->special_instructions) }}</textarea>
                        </div>
                    </div>
                </x-premium.card>

                <x-premium.card class="mb-4" id="menuReviewSection">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h2 class="h6 fw-bold mb-0">Menu Review</h2>
                        <div class="btn-group btn-group-sm" role="group" id="viewModeToggle">
                            <button type="button" class="btn btn-outline-dark active" data-mode="selected">Selected Only</button>
                            <button type="button" class="btn btn-outline-dark" data-mode="all">Show All</button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3" id="quickFilters">
                        <button type="button" class="erq-chip active" data-qf="">All shown</button>
                        <button type="button" class="erq-chip" data-qf="added">Added by Admin</button>
                        <button type="button" class="erq-chip" data-qf="removed">Removed</button>
                        <button type="button" class="erq-chip" data-qf="veg">Veg</button>
                        <button type="button" class="erq-chip" data-qf="non_veg">Non-Veg</button>
                        <button type="button" class="erq-chip" data-qf="popular">Popular</button>
                    </div>

                    <div class="erq-changes-bar mb-3 d-none" id="changesMadeBar">
                        <span class="stat">Changes Made</span>
                        <span class="stat added"><i class="bi bi-plus-circle-fill me-1"></i><span id="changesAdded">0</span> Added</span>
                        <span class="stat removed"><i class="bi bi-dash-circle-fill me-1"></i><span id="changesRemoved">0</span> Removed</span>
                    </div>

                    <div id="menuCategoriesList"></div>

                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-light rounded-3">
                        <span class="small fw-bold text-muted">Per person: <span id="adminPerPerson">₹{{ number_format($eventRequest->per_person_cost,0) }}</span> &middot; Estimated total</span>
                        <strong id="adminTotal" class="fs-5">₹{{ number_format($eventRequest->estimated_total,0) }}</strong>
                    </div>
                </x-premium.card>

                <div class="d-flex justify-content-end mt-3 mb-4">
                    <button type="submit" class="btn btn-dark">Save changes</button>
                </div>
            </form>

            {{-- Decision actions --}}
            @if($canDecide)
            <x-premium.card class="mb-4">
                <h2 class="h6 fw-bold mb-3">Decision</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.event-requests.approve', $eventRequest) }}" id="approveForm">
                        @csrf
                        <button class="btn btn-success" onclick="return confirm('Approve and add to the calendar?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </form>
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#needChangesModal"><i class="bi bi-pencil-square me-1"></i>Need Changes</button>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg me-1"></i>Reject</button>
                </div>
            </x-premium.card>
            @endif

            @if($eventRequest->status === 'scheduled' && $eventRequest->hallBooking)
                <x-premium.card class="mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold small text-success"><i class="bi bi-calendar-check me-1"></i>On the calendar</div>
                            <div class="text-muted small">This request created a confirmed booking.</div>
                        </div>
                        <a href="{{ route('hall.bookings.show', $eventRequest->hallBooking) }}" class="btn btn-outline-dark btn-sm">View booking</a>
                    </div>
                </x-premium.card>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="position-sticky" style="top:16px">

                {{-- Sticky review panel — never scroll back up to approve --}}
                <x-premium.card class="mb-4">
                    <h2 class="h6 fw-bold mb-3">Client Selection</h2>
                    <div class="erq-stat-line"><span>Items</span><strong id="sideCount">0</strong></div>
                    <div class="erq-stat-line"><span>Veg</span><strong id="sideVeg">0</strong></div>
                    <div class="erq-stat-line"><span>Non-Veg</span><strong id="sideNonVeg">0</strong></div>
                    <div class="erq-stat-line"><span>Per Person</span><strong id="sidePerPerson">₹0</strong></div>
                    <div class="erq-stat-line"><span class="fw-bold">Estimated Total</span><strong id="sideTotal" class="fs-6">₹0</strong></div>

                    @if($canDecide)
                        <hr>
                        <div class="d-grid gap-2">
                            <button type="submit" form="approveForm" class="btn btn-success btn-sm" onclick="return confirm('Approve and add to the calendar?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
                            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#needChangesModal"><i class="bi bi-pencil-square me-1"></i>Need Changes</button>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg me-1"></i>Reject</button>
                        </div>
                    @endif
                </x-premium.card>

                {{-- Revision timeline --}}
                <x-premium.card>
                    <h2 class="h6 fw-bold mb-3">History</h2>
                    <div class="d-flex flex-column gap-3">
                        @foreach($eventRequest->revisions as $rev)
                            <div class="d-flex gap-2">
                                <div class="mt-1"><i class="bi bi-circle-fill" style="font-size:.5rem;color:#B8893E"></i></div>
                                <div>
                                    <div class="small fw-bold">{{ \App\Models\EventRequestRevision::labels()[$rev->action] ?? $rev->action }}</div>
                                    <div class="text-muted" style="font-size:.72rem">{{ $rev->actor_name ?? ucfirst($rev->actor_type) }} &middot; {{ $rev->created_at->format('d M, h:i A') }}</div>
                                    @if($rev->comment)
                                        <div class="small mt-1 p-2 bg-light rounded-2">{{ $rev->comment }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-premium.card>
            </div>
        </div>
    </div>
</div>

{{-- Need Changes modal --}}
<div class="modal fade" id="needChangesModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.event-requests.need-changes', $eventRequest) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Request changes from client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label small fw-bold">Comment to client</label>
                <textarea class="form-control" name="comment" rows="4" placeholder="e.g. Paneer Tikka unavailable, please choose another starter." required></textarea>
            </div>
            <div class="modal-footer"><button class="btn btn-warning">Send</button></div>
        </form>
    </div>
</div>

{{-- Reject modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.event-requests.reject', $eventRequest) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Reject this request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label small fw-bold">Reason</label>
                <textarea class="form-control" name="comment" rows="4" required></textarea>
            </div>
            <div class="modal-footer"><button class="btn btn-danger">Reject</button></div>
        </form>
    </div>
</div>

{{-- Before-saving confirmation --}}
<div class="modal fade" id="confirmSaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Confirm menu changes</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Client Selected</span><strong id="confirmBaselineCount">0 Items</strong></div>
                <div class="text-center text-muted small mb-2"><i class="bi bi-arrow-down"></i></div>
                <div class="d-flex justify-content-between mb-3"><span class="text-muted small">Final Menu</span><strong id="confirmFinalCount">0 Items</strong></div>
                <div class="d-flex justify-content-between small"><span class="text-primary">Added</span><strong id="confirmAdded">0</strong></div>
                <div class="d-flex justify-content-between small mb-3"><span class="text-danger">Removed</span><strong id="confirmRemoved">0</strong></div>
                <div class="d-flex justify-content-between border-top pt-2"><span class="fw-bold">New Total</span><strong id="confirmNewTotal">₹0/person</strong></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="confirmSaveBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const menuData = JSON.parse(document.getElementById('erqMenuData').textContent || '[]');
    const initialSelected = JSON.parse(document.getElementById('erqSelectedIds').textContent || '[]').map(Number);
    const baselineIds = JSON.parse(document.getElementById('erqBaselineIds').textContent || '[]').map(Number);

    let current = new Set(initialSelected);
    const baseline = new Set(baselineIds);
    const itemIndex = new Map(); // id -> { item, categoryId, categoryName }
    menuData.forEach(cat => cat.items.forEach(item => itemIndex.set(item.id, { item, categoryId: cat.id, categoryName: cat.name })));

    let viewMode = 'selected'; // selected | all
    let quickFilter = '';
    const collapsedCats = new Set(); // explicit user choice — never inferred from what happened to render last pass

    const rupee = v => '₹' + Math.round(v).toLocaleString('en-IN');
    const escapeHtml = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function itemStatus(id) {
        const isCurrent = current.has(id);
        const wasBaseline = baseline.has(id);
        if (isCurrent && wasBaseline) return 'client';
        if (isCurrent && !wasBaseline) return 'added';
        if (!isCurrent && wasBaseline) return 'removed';
        return 'unselected';
    }

    function passesQuickFilter(item, status) {
        if (quickFilter === 'added') return status === 'added';
        if (quickFilter === 'removed') return status === 'removed';
        if (quickFilter === 'veg') return item.is_veg;
        if (quickFilter === 'non_veg') return !item.is_veg;
        if (quickFilter === 'popular') return item.is_popular;
        return true;
    }

    function syncHiddenInputs() {
        const container = document.getElementById('selectedInputsContainer');
        container.innerHTML = '';
        current.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'menu_item_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    function toggle(id) {
        if (current.has(id)) current.delete(id); else current.add(id);
        syncHiddenInputs();
        renderAll();
    }

    function buildRow(item) {
        const status = itemStatus(item.id);
        if (!passesQuickFilter(item, status)) return null;
        if (viewMode === 'selected' && status === 'unselected') return null;

        const glyphs = { client: '✓', added: '+', removed: '−', unselected: '○' };
        const tagLabel = { client: 'Client Selected', added: 'Admin Added', removed: 'Admin Removed', unselected: '' };

        const row = document.createElement('div');
        row.className = 'erq-row ' + status;
        row.dataset.itemId = item.id;
        row.setAttribute('role', 'checkbox');
        row.setAttribute('aria-checked', status === 'client' || status === 'added' ? 'true' : 'false');
        row.tabIndex = 0;

        row.innerHTML = `
            <span class="erq-row-glyph">${glyphs[status]}</span>
            <div class="erq-row-main">
                <span class="erq-row-name"></span>
                ${tagLabel[status] ? `<span class="erq-row-tag text-muted">(${tagLabel[status]})</span>` : ''}
            </div>
            <span class="erq-row-price text-muted">₹${Math.round(item.price)}</span>
        `;
        row.querySelector('.erq-row-name').textContent = item.name;
        row.addEventListener('click', () => toggle(item.id));
        row.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(item.id); } });
        return row;
    }

    function renderCategories() {
        const container = document.getElementById('menuCategoriesList');
        container.innerHTML = '';

        menuData.forEach(category => {
            const rows = category.items.map(buildRow).filter(Boolean);
            if (!rows.length) return;

            const selectedInCat = category.items.filter(i => current.has(i.id)).length;
            const totalInCat = category.items.length;

            const block = document.createElement('div');
            block.className = 'erq-cat-block' + (collapsedCats.has(category.id) ? ' collapsed' : '');
            block.dataset.categoryId = category.id;

            const head = document.createElement('div');
            head.className = 'erq-cat-head';
            head.innerHTML = `
                <span class="erq-cat-head-name"><i class="bi bi-chevron-down erq-cat-chevron"></i> ${category.icon} <span></span></span>
                <span class="erq-cat-head-progress ${selectedInCat ? 'has-selection' : ''}">${selectedInCat} of ${totalInCat} Selected</span>
            `;
            head.querySelector('.erq-cat-head-name span:last-child').textContent = category.name;
            head.addEventListener('click', () => {
                if (collapsedCats.has(category.id)) collapsedCats.delete(category.id);
                else collapsedCats.add(category.id);
                block.classList.toggle('collapsed');
            });

            const body = document.createElement('div');
            body.className = 'erq-cat-body';
            rows.forEach(r => body.appendChild(r));

            block.appendChild(head);
            block.appendChild(body);
            container.appendChild(block);
        });

        if (!container.children.length) {
            container.innerHTML = '<div class="text-center text-muted small py-4">Nothing matches this view. Try "Show All" or clear filters.</div>';
        }
    }

    function renderSummary() {
        const list = document.getElementById('summaryList');
        const byCategory = new Map();
        current.forEach(id => {
            const entry = itemIndex.get(id);
            if (!entry) return;
            if (!byCategory.has(entry.categoryName)) byCategory.set(entry.categoryName, []);
            byCategory.get(entry.categoryName).push(entry.item);
        });

        if (!byCategory.size) {
            list.innerHTML = '<div class="text-muted small">No items selected yet.</div>';
        } else {
            list.innerHTML = '';
            byCategory.forEach((items, categoryName) => {
                const title = document.createElement('div');
                title.className = 'erq-summary-cat-title';
                title.textContent = categoryName;
                list.appendChild(title);
                items.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'erq-summary-item';
                    row.innerHTML = '<span class="check">✓</span><span></span>';
                    row.querySelector('span:last-child').textContent = item.name;
                    list.appendChild(row);
                });
            });
        }

        document.getElementById('summaryCountBadge').textContent = current.size + ' Item' + (current.size === 1 ? '' : 's') + ' Selected';
    }

    function renderChangesBar() {
        const added = [...current].filter(id => !baseline.has(id)).length;
        const removed = [...baseline].filter(id => !current.has(id)).length;
        const bar = document.getElementById('changesMadeBar');
        if (added || removed) {
            bar.classList.remove('d-none');
            document.getElementById('changesAdded').textContent = added;
            document.getElementById('changesRemoved').textContent = removed;
        } else {
            bar.classList.add('d-none');
        }
    }

    function recalcTotals() {
        const guests = Number(document.getElementById('guestCountInput').value || 0);
        const perPerson = [...current].reduce((s, id) => s + (itemIndex.get(id)?.item.price || 0), 0);
        const total = perPerson * guests;
        const vegCount = [...current].filter(id => itemIndex.get(id)?.item.is_veg).length;

        document.getElementById('adminPerPerson').textContent = rupee(perPerson);
        document.getElementById('adminTotal').textContent = rupee(total);
        document.getElementById('sideCount').textContent = current.size;
        document.getElementById('sideVeg').textContent = vegCount;
        document.getElementById('sideNonVeg').textContent = current.size - vegCount;
        document.getElementById('sidePerPerson').textContent = rupee(perPerson);
        document.getElementById('sideTotal').textContent = rupee(total);
    }

    function renderAll() {
        renderCategories();
        renderSummary();
        renderChangesBar();
        recalcTotals();
    }

    // View mode toggle
    document.getElementById('viewModeToggle').querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('viewModeToggle').querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            viewMode = btn.dataset.mode;
            renderCategories();
        });
    });

    // Quick filters
    document.getElementById('quickFilters').querySelectorAll('.erq-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('quickFilters').querySelectorAll('.erq-chip').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            quickFilter = btn.dataset.qf;
            renderCategories();
        });
    });

    // "Edit Selection" scrolls down and switches to Selected Only (so the
    // admin lands exactly on what they need to review/edit).
    document.getElementById('editSelectionBtn').addEventListener('click', () => {
        document.getElementById('viewModeToggle').querySelector('[data-mode="selected"]').click();
        document.getElementById('menuReviewSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('guestCountInput').addEventListener('input', recalcTotals);

    // Before-saving confirmation — only interrupts when the menu actually changed.
    const form = document.getElementById('adminEditForm');
    let confirmedSave = false;
    form.addEventListener('submit', function (e) {
        if (confirmedSave) return;
        const addedVsBaseline = [...current].filter(id => !baseline.has(id));
        const removedVsBaseline = [...baseline].filter(id => !current.has(id));
        if (addedVsBaseline.length === 0 && removedVsBaseline.length === 0) return; // nothing to confirm

        // stopPropagation, not just preventDefault: admin-layout's global
        // double-submit guard listens on `document` and would otherwise
        // still disable the button + show "Processing…" even though we're
        // not actually submitting yet (waiting on the confirm modal).
        e.preventDefault();
        e.stopPropagation();
        const guests = Number(document.getElementById('guestCountInput').value || 0);
        const perPerson = [...current].reduce((s, id) => s + (itemIndex.get(id)?.item.price || 0), 0);

        document.getElementById('confirmBaselineCount').textContent = baseline.size + ' Items';
        document.getElementById('confirmFinalCount').textContent = current.size + ' Items';
        document.getElementById('confirmAdded').textContent = addedVsBaseline.length;
        document.getElementById('confirmRemoved').textContent = removedVsBaseline.length;
        document.getElementById('confirmNewTotal').textContent = rupee(perPerson) + '/person';

        new bootstrap.Modal(document.getElementById('confirmSaveModal')).show();
    });
    document.getElementById('confirmSaveBtn').addEventListener('click', () => {
        confirmedSave = true;
        bootstrap.Modal.getInstance(document.getElementById('confirmSaveModal'))?.hide();
        form.submit();
    });

    syncHiddenInputs();
    renderAll();
})();
</script>
</x-admin-layout>
