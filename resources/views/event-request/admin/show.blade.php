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
    /* ── Page shell: constrain very wide desktops, tighten section rhythm ── */
    .erq-wrap { max-width: 1400px; margin: 0 auto; }
    .erq-wrap .ef-card-body { padding: 14px 16px; }
    @media (min-width: 768px) { .erq-wrap .ef-card-body { padding: 16px 20px; } }

    /* Sticky sidebar is a desktop-only affordance. Below lg, Bootstrap's
       col-lg-4/col-lg-8 stack into full-width blocks — if this stayed
       `position: sticky` at every width, it would detach and float on top
       of the main column's own content (including the Approve/Reject
       buttons there), making taps land on the wrong element. */
    .erq-sidebar-sticky { position: static; }
    @media (min-width: 992px) {
        .erq-sidebar-sticky { position: sticky; top: 16px; max-height: calc(100vh - 32px); overflow-y: auto; }
    }

    /* ── Compact header ── */
    .erq-back { font-size: .82rem; }
    .erq-header-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .erq-header-row h1 { font-size: 1.25rem; overflow-wrap: anywhere; }
    .erq-ref { font-size: .78rem; color: #8a8370; }

    /* ── Compact section-progress nav (anchors into the page, not a wizard) ── */
    .erq-progress { display: flex; gap: 6px; overflow-x: auto; -ms-overflow-style: none; scrollbar-width: none; padding-bottom: 2px; }
    .erq-progress::-webkit-scrollbar { display: none; }
    .erq-progress a {
        flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px;
        font-size: .74rem; font-weight: 650; color: #6B5D4C; text-decoration: none;
        padding: 6px 12px; border-radius: 999px; border: 1px solid #e9e3d5; background: #fff;
        min-height: 32px;
    }
    .erq-progress a:hover { border-color: #B8893E; color: #4a4536; }
    .erq-progress a .dot { width: 6px; height: 6px; border-radius: 50%; background: #c8c4bb; flex-shrink: 0; }

    /* ── Level-1 section headings ── */
    .erq-h { font-size: .82rem; font-weight: 750; text-transform: uppercase; letter-spacing: .04em; color: #2A211A; margin-bottom: 2px; }
    .erq-h-sub { font-size: .74rem; color: #9c8f79; margin-bottom: 12px; }

    /* ── Public link — compact, secondary ── */
    .erq-link-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .erq-link-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #9c8f79; font-weight: 700; margin-bottom: 4px; }

    /* ── Selected Menu ── */
    .erq-menu-empty { text-align: center; padding: 20px 12px; color: #8a8370; }
    .erq-menu-empty .glyph { font-size: 1.6rem; opacity: .5; margin-bottom: 6px; }

    .erq-chip {
        border: 1.5px solid #e2dccc; background: #fff; color: #4a4536;
        font-size: .78rem; font-weight: 650; padding: 6px 13px; border-radius: 999px;
        cursor: pointer; transition: background .12s, border-color .12s, color .12s;
        white-space: nowrap; min-height: 32px; display: inline-flex; align-items: center;
    }
    .erq-chip:hover { border-color: #B8893E; }
    .erq-chip.active { background: #3E2D23; border-color: #3E2D23; color: #fff; }

    .erq-cat-block { margin-bottom: 14px; }
    .erq-cat-block:last-child { margin-bottom: 0; }
    .erq-cat-head { display: flex; align-items: center; justify-content: space-between; padding: 6px 2px; cursor: pointer; min-height: 36px; }
    .erq-cat-head-name { font-weight: 700; font-size: .86rem; display: flex; align-items: center; gap: 6px; }
    .erq-cat-head-progress { font-size: .72rem; font-weight: 650; color: #8a8370; }
    .erq-cat-head-progress.has-selection { color: #8A6820; }
    .erq-cat-chevron { transition: transform .18s ease; color: #9c8f79; }
    .erq-cat-block.collapsed .erq-cat-chevron { transform: rotate(-90deg); }
    .erq-cat-block.collapsed .erq-cat-body { display: none; }

    .erq-row {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 10px; cursor: pointer;
        min-height: 44px; transition: background .12s, border-color .12s, box-shadow .12s;
        border: 1.5px solid transparent;
    }
    .erq-row + .erq-row { margin-top: 4px; }
    .erq-row-glyph { width: 20px; text-align: center; flex-shrink: 0; font-size: .95rem; }
    .erq-row-main { flex: 1; min-width: 0; display: flex; align-items: baseline; gap: 8px; }
    .erq-row-name { font-size: .86rem; min-width: 0; overflow-wrap: anywhere; }
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

    .erq-stat-line { display: flex; justify-content: space-between; align-items: baseline; font-size: .84rem; padding: 6px 0; border-top: 1px solid #eee; }
    .erq-stat-line:first-of-type { border-top: none; }
    .erq-stat-line.total { font-weight: 700; }
    .erq-stat-line .k { color: #6B5D4C; }
    .erq-changes-bar { display: flex; gap: 16px; flex-wrap: wrap; background: #f3f0e8; border-radius: 10px; padding: 10px 14px; }
    .erq-changes-bar .stat { font-size: .78rem; font-weight: 650; }
    .erq-changes-bar .stat.added { color: #2563eb; }
    .erq-changes-bar .stat.removed { color: #dc3545; }

    /* ── Event details fields — lighter than their section container ── */
    .erq-field label { font-size: .74rem; font-weight: 650; color: #6B5D4C; margin-bottom: 5px; }
    .erq-field .form-control, .erq-field .form-select, .erq-field textarea.form-control {
        min-height: 44px; border-color: #e2dccc;
    }
    .erq-field textarea.form-control { min-height: unset; }
    .erq-field .form-control:focus, .erq-field .form-select:focus {
        border-color: #B8893E; box-shadow: 0 0 0 .2rem rgba(184,137,62,.15);
    }

    /* ── History (collapsible on mobile, always open on desktop) ── */
    .erq-history-toggle { display: flex; align-items: center; justify-content: space-between; width: 100%; background: none; border: none; padding: 0; cursor: pointer; min-height: 32px; }
    .erq-history-body { display: none; margin-top: 12px; }
    .erq-history-body.expanded { display: block; }
    .erq-history-chevron { transition: transform .18s ease; color: #9c8f79; }
    .erq-history-toggle[aria-expanded="true"] .erq-history-chevron { transform: rotate(180deg); }
    @media (min-width: 992px) {
        .erq-history-toggle { display: none; }
        .erq-history-body { display: block !important; margin-top: 0; }
    }

    /* ── Sticky mobile action bar ── */
    .erq-mobile-bar {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
        background: #fff; border-top: 1px solid #e9e3d5;
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom, 0px));
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        box-shadow: 0 -2px 10px rgba(42,33,26,.06);
    }
    .erq-mobile-bar .meta { min-width: 0; }
    .erq-mobile-bar .meta .amount { font-size: 1rem; font-weight: 800; color: #2A211A; font-variant-numeric: tabular-nums; }
    .erq-mobile-bar .meta .sub { font-size: .72rem; color: #8a8370; }
    .erq-mobile-bar .btn { min-height: 44px; flex-shrink: 0; }
    .erq-bar-spacer { display: none; }
    @media (max-width: 991.98px) {
        .erq-bar-spacer { display: block; height: 72px; }
    }
</style>

<div class="container-fluid py-4">
<div class="erq-wrap">

    <a href="{{ route('admin.event-requests.index') }}" class="erq-back text-decoration-none text-muted mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Event Requests</a>

    <div class="erq-header-row mb-2">
        <div>
            <h1 class="fw-bold mb-0">{{ $eventRequest->event_name ?: $eventRequest->client_name ?: 'Untitled request' }}</h1>
            <div class="erq-ref">{{ $eventRequest->referenceNumber() }}</div>
        </div>
        <span class="badge {{ $statusChip }} fs-6">{{ $eventRequest->statusLabel() }}</span>
    </div>

    <div class="erq-progress mb-3">
        <a href="#selectedMenuSection"><span class="dot"></span>Menu</a>
        <a href="#eventDetailsSection"><span class="dot"></span>Details</a>
        <a href="#menuReviewSection"><span class="dot"></span>Review</a>
        @if($canDecide)
            <a href="#decisionSection"><span class="dot"></span>Decision</a>
        @endif
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Public link — compact, secondary --}}
    <x-premium.card class="mb-3">
        <div class="erq-link-row">
            <div class="flex-grow-1" style="min-width:240px">
                <div class="erq-link-label">Public request link</div>
                @if($publicUrl)
                    @php
                        $waMessage = 'Hi'.($eventRequest->client_name ? ' '.$eventRequest->client_name : '').", here's your event request link: ".$publicUrl;
                        $waHref = 'https://wa.me/?text='.rawurlencode($waMessage);
                    @endphp
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace small" readonly value="{{ $publicUrl }}" id="publicLinkInput">
                        <button class="btn btn-outline-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('publicLinkInput').value)">Copy</button>
                        <a class="btn btn-outline-success" href="{{ $waHref }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
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

    <div class="row g-3">
        <div class="col-lg-8">

            {{-- Selected menu summary — the first thing the admin should see --}}
            <x-premium.card class="mb-3" id="selectedMenuSection">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div class="erq-h mb-0">Selected Menu</div>
                    </div>
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

                <x-premium.card class="mb-3" id="eventDetailsSection">
                    <div class="erq-h">Event Details</div>
                    <div class="erq-h-sub">Client, schedule and menu preferences for this request</div>
                    <div class="row g-3">
                        <div class="col-md-6 erq-field">
                            <label class="form-label">Client Name</label>
                            <input class="form-control" name="client_name" value="{{ old('client_name', $eventRequest->client_name) }}" required>
                        </div>
                        <div class="col-md-6 erq-field">
                            <label class="form-label">Mobile Number</label>
                            <input class="form-control" name="client_mobile" value="{{ old('client_mobile', $eventRequest->client_mobile) }}" required>
                        </div>
                        <div class="col-md-6 erq-field">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="client_email" value="{{ old('client_email', $eventRequest->client_email) }}">
                        </div>
                        <div class="col-md-6 erq-field">
                            <label class="form-label">Event Name</label>
                            <input class="form-control" name="event_name" value="{{ old('event_name', $eventRequest->event_name) }}">
                        </div>
                        <div class="col-md-4 erq-field">
                            <label class="form-label">Event Date</label>
                            <input type="date" class="form-control" name="event_date" value="{{ old('event_date', $eventRequest->event_date?->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4 erq-field">
                            <label class="form-label">Guest Count</label>
                            <input type="number" min="1" class="form-control" name="guest_count" id="guestCountInput" value="{{ old('guest_count', $eventRequest->guest_count) }}" required>
                        </div>
                        <div class="col-md-4 erq-field">
                            <label class="form-label">Meal Type</label>
                            <select class="form-select" name="meal_type" required>
                                @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('meal_type', $eventRequest->meal_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 erq-field">
                            <label class="form-label">Menu Type</label>
                            <select class="form-select" name="menu_type" required>
                                @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('menu_type', $eventRequest->menu_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 erq-field">
                            <label class="form-label">Special Instructions</label>
                            <textarea class="form-control" name="special_instructions" rows="3">{{ old('special_instructions', $eventRequest->special_instructions) }}</textarea>
                        </div>
                    </div>
                </x-premium.card>

                <x-premium.card class="mb-3" id="menuReviewSection">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <div class="erq-h mb-0">Menu Review</div>
                        <div class="btn-group btn-group-sm" role="group" id="viewModeToggle">
                            <button type="button" class="btn btn-outline-dark active" data-mode="selected">Selected Only</button>
                            <button type="button" class="btn btn-outline-dark" data-mode="all">Show All</button>
                        </div>
                    </div>
                    <div class="erq-h-sub">Toggle items on or off — client's original picks stay marked</div>

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

                <div class="d-none d-lg-flex justify-content-end mt-3 mb-3">
                    <button type="submit" class="btn btn-dark">Save Changes</button>
                </div>
            </form>

            {{-- Decision actions --}}
            @if($canDecide)
            <x-premium.card class="mb-3" id="decisionSection">
                <div class="erq-h">Decision</div>
                <div class="erq-h-sub">Approve to confirm this event, or send it back for changes</div>
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
                <x-premium.card class="mb-3">
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
            <div class="erq-sidebar-sticky">

                {{-- Request summary — mirrors the sticky mobile bar's numbers --}}
                <x-premium.card class="mb-3">
                    <div class="erq-h mb-2">Request Summary</div>
                    <div class="erq-stat-line"><span class="k">Items</span><strong id="sideCount">0</strong></div>
                    <div class="erq-stat-line"><span class="k">Veg</span><strong id="sideVeg">0</strong></div>
                    <div class="erq-stat-line"><span class="k">Non-Veg</span><strong id="sideNonVeg">0</strong></div>
                    <div class="erq-stat-line"><span class="k">Per Person</span><strong id="sidePerPerson">₹0</strong></div>
                    <div class="erq-stat-line total"><span class="k">Estimated Total</span><strong id="sideTotal" class="fs-6">₹0</strong></div>

                    @if($canDecide)
                        <hr>
                        <div class="d-grid gap-2">
                            <button type="submit" form="approveForm" class="btn btn-success btn-sm" onclick="return confirm('Approve and add to the calendar?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
                            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#needChangesModal"><i class="bi bi-pencil-square me-1"></i>Need Changes</button>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg me-1"></i>Reject</button>
                        </div>
                    @endif
                </x-premium.card>

                {{-- Revision timeline — collapsed by default on mobile, always visible on desktop --}}
                <x-premium.card>
                    <button type="button" class="erq-history-toggle" id="historyToggle" aria-expanded="false" aria-controls="historyBody">
                        <span class="erq-h mb-0">History &middot; {{ $eventRequest->revisions->count() }} {{ Str::plural('update', $eventRequest->revisions->count()) }}</span>
                        <i class="bi bi-chevron-down erq-history-chevron"></i>
                    </button>
                    <div class="d-none d-lg-block erq-h mb-2" style="margin-top:0">History</div>
                    <div class="d-flex flex-column gap-3 erq-history-body" id="historyBody">
                        @forelse($eventRequest->revisions as $rev)
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
                        @empty
                            <div class="text-muted small">No previous updates.</div>
                        @endforelse
                    </div>
                </x-premium.card>
            </div>
        </div>
    </div>

</div>
</div>

{{-- Sticky mobile action bar — keeps guest count / estimated total and the
     primary Save action visible without scrolling back down. --}}
<div class="erq-mobile-bar d-lg-none">
    <div class="meta">
        <div class="sub"><span id="stickyGuestCount">0</span> Guests</div>
        <div class="amount" id="stickyTotal">₹0</div>
    </div>
    <button type="submit" form="adminEditForm" class="btn btn-dark">Save Changes</button>
</div>
<div class="erq-bar-spacer"></div>

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
            list.innerHTML = '<div class="erq-menu-empty"><div class="glyph"><i class="bi bi-basket"></i></div><div>No menu selected.</div></div>';
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
        document.getElementById('stickyGuestCount').textContent = guests;
        document.getElementById('stickyTotal').textContent = rupee(total);
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

    // History — collapsible on mobile only (CSS forces it open at lg+ and
    // hides this toggle there, so the listener is inert but harmless on desktop).
    const historyToggle = document.getElementById('historyToggle');
    const historyBody = document.getElementById('historyBody');
    historyToggle.addEventListener('click', () => {
        const expanded = historyBody.classList.toggle('expanded');
        historyToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });

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
