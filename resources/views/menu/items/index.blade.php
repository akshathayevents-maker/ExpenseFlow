<x-admin-layout title="Dishes">
@push('styles')
<style>
/* ══ MENU ITEM MASTER ═══════════════════════════════════════════ */
:root {
    --mi-gold:    #a0723a;
    --mi-gold-hi: #b8832a;
    --mi-surface: #fff;
    --mi-page:    #f7f5f2;
    --mi-border:  #e8e2d8;
    --mi-ink:     #1c1712;
    --mi-muted:   #7a6e62;
    --mi-faint:   #b8af9e;
    --mi-radius:  14px;
    --mi-r-sm:    10px;
}

.mi-wrap { max-width: 1100px; margin: 0 auto; }

.mi-toolbar {
    display: flex; align-items: center; gap: 12px;
    flex-wrap: wrap; margin-bottom: 20px;
}
.mi-toolbar-title { font-size: 1.35rem; font-weight: 800; color: var(--mi-ink); flex: 1; min-width: 0; }

.mi-search-wrap { position: relative; flex: 1; max-width: 320px; }
.mi-search-wrap .bi-search { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--mi-faint); font-size: .9rem; pointer-events: none; }
.mi-search-input {
    width: 100%; padding: 8px 12px 8px 34px;
    border: 1.5px solid var(--mi-border); border-radius: var(--mi-r-sm);
    font-size: .88rem; background: var(--mi-surface);
    transition: border-color .12s;
}
.mi-search-input:focus { outline: none; border-color: var(--mi-gold); }

.mi-cat-select {
    padding: 8px 12px; border: 1.5px solid var(--mi-border);
    border-radius: var(--mi-r-sm); font-size: .88rem;
    background: var(--mi-surface); cursor: pointer;
    max-width: 200px;
}

.mi-add-btn {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--mi-gold); color: #fff;
    border: none; border-radius: var(--mi-r-sm);
    padding: 9px 18px; font-size: .88rem; font-weight: 700;
    text-decoration: none; cursor: pointer; transition: background .12s;
    white-space: nowrap;
}
.mi-add-btn:hover { background: var(--mi-gold-hi); color: #fff; }

/* ── Table ─────────────────────────────────────────────────────── */
.mi-table-wrap {
    background: var(--mi-surface); border: 1.5px solid var(--mi-border);
    border-radius: var(--mi-radius); overflow: hidden;
}
.mi-table { width: 100%; border-collapse: collapse; }
.mi-table th {
    padding: 11px 16px; text-align: left;
    font-size: .72rem; font-weight: 800; letter-spacing: .08em;
    text-transform: uppercase; color: var(--mi-muted);
    border-bottom: 1.5px solid var(--mi-border);
    background: #faf8f5;
}
.mi-table td {
    padding: 13px 16px; font-size: .9rem; color: var(--mi-ink);
    border-bottom: 1px solid #f0ece4;
    vertical-align: middle;
}
.mi-table tr:last-child td { border-bottom: 0; }
.mi-table tr:hover td { background: #fdfcfb; }

.mi-cat-badge {
    display: inline-block; padding: 3px 10px;
    background: rgba(160,114,58,.12); color: var(--mi-gold);
    border-radius: 999px; font-size: .77rem; font-weight: 700;
    white-space: nowrap;
}
.mi-ta-text { font-size: .88rem; color: var(--mi-muted); }
.mi-status-dot {
    display: inline-block; width: 8px; height: 8px;
    border-radius: 50%; margin-right: 5px;
}
.mi-status-dot.--on  { background: #16a34a; }
.mi-status-dot.--off { background: #d1d5db; }

.mi-actions { display: flex; gap: 6px; align-items: center; }
.mi-btn-sm {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 10px; border-radius: 7px; font-size: .78rem;
    font-weight: 600; border: 1.5px solid transparent; cursor: pointer;
    text-decoration: none; transition: all .1s;
}
.mi-btn-edit  { border-color: var(--mi-border); color: var(--mi-muted); background: transparent; }
.mi-btn-edit:hover { border-color: var(--mi-gold); color: var(--mi-gold); }
.mi-btn-del   { border-color: #fee2e2; color: #dc2626; background: transparent; }
.mi-btn-del:hover { background: #fee2e2; }

.mi-empty { padding: 60px 24px; text-align: center; color: var(--mi-muted); }
.mi-empty i { font-size: 2.5rem; color: var(--mi-faint); margin-bottom: 12px; display: block; }

.mi-count { font-size: .82rem; color: var(--mi-muted); margin-bottom: 12px; }

@media (max-width: 767.98px) {
    .mi-toolbar { gap: 8px; }
    .mi-toolbar-title { font-size: 1.1rem; }
    .mi-search-wrap { max-width: 100%; flex: 1 1 100%; order: 3; }
    .mi-table th:nth-child(3),
    .mi-table td:nth-child(3) { display: none; } /* hide sort_order on mobile */
}
</style>
@endpush

<div class="mi-wrap">

    {{-- Toolbar --}}
    <div class="mi-toolbar">
        <div class="mi-toolbar-title">
            <i class="bi bi-journal-text me-2" style="color:var(--mi-gold)"></i>Dishes
        </div>

        <form method="GET" action="{{ route('menu.items.index') }}" class="d-contents" id="filterForm">
            <div class="mi-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="mi-search-input"
                       placeholder="Search items…" value="{{ request('search') }}"
                       oninput="document.getElementById('filterForm').submit()">
            </div>

            <select name="category" class="mi-cat-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $key => $cat)
                    <option value="{{ $key }}" @selected(request('category') === $key)>
                        {{ $cat['en'] }}
                    </option>
                @endforeach
            </select>
            <select name="recipe" class="mi-cat-select" onchange="this.form.submit()" aria-label="Recipe">
                <option value="">Any recipe status</option>
                <option value="none" @selected(request('recipe') === 'none')>No recipe yet</option>
                <option value="unverified" @selected(request('recipe') === 'unverified')>Recipe not verified</option>
            </select>
            <select name="tamil" class="mi-cat-select" onchange="this.form.submit()" aria-label="Tamil name">
                <option value="">All items</option>
                <option value="missing" @selected(request('tamil') === 'missing')>Tamil needed</option>
            </select>
        </form>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.event-request-menu.items.index') }}" class="mi-btn-sm" style="text-decoration:none" title="Same dishes, with prices">
            <i class="bi bi-link-45deg"></i> Event Request Menu
        </a>
        @endif
        <a href="{{ route('menu.items.create') }}" class="mi-add-btn">
            <i class="bi bi-plus-lg"></i> Add Item
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:var(--mi-r-sm)">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <p class="mi-count">{{ $items->count() }} item{{ $items->count() !== 1 ? 's' : '' }}</p>

    <div class="mi-table-wrap">
        @if($items->isEmpty())
            <div class="mi-empty">
                <i class="bi bi-journal-plus"></i>
                <p class="fw-700 mb-1">No items yet</p>
                <p class="small mb-3">Add your first menu item to get started.</p>
                <a href="{{ route('menu.items.create') }}" class="mi-add-btn">
                    <i class="bi bi-plus-lg"></i> Add First Item
                </a>
            </div>
        @else
            <div class="dh-stats">
                <a href="{{ route('menu.items.index') }}"><b>{{ $stats['total'] }}</b><span>dishes</span></a>
                <a href="{{ route('menu.items.index', ['recipe' => 'none']) }}"><b>{{ $stats['total'] - $stats['with_recipe'] }}</b><span>without recipe</span></a>
                <a href="{{ route('menu.items.index', ['recipe' => 'unverified']) }}"><b>{{ $stats['with_recipe'] - $stats['verified'] }}</b><span>recipe not verified</span></a>
                <a href="{{ route('menu.items.index', ['tamil' => 'missing']) }}"><b>{{ $stats['tamil_missing'] }}</b><span>Tamil needed</span></a>
                <span><b>{{ $stats['used'] }}</b><span>used in requests / plans</span></span>
            </div>
            <p style="font-size:.82rem;color:var(--ef-muted);margin:0 0 10px">One list of dishes for the whole app: the same dishes appear on the
                <b>Customer Menu &amp; Prices</b> (event request form) and in the <b>Menu Composer</b>. Names and categories stay in sync automatically.</p>
            <style>
            .dh-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;margin:0 0 10px}
            @media (max-width:760px){.dh-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
            .dh-stats>*{display:flex;flex-direction:column;padding:8px 12px;border:1px solid var(--ef-border,#e5e0d6);border-radius:12px;background:var(--ef-surface,#fff);color:inherit;text-decoration:none}
            .dh-stats b{font-size:1.25rem;line-height:1.1}
            .dh-stats span{font-size:.76rem;color:var(--ef-muted)}
            .mi-table{min-width:0}
            .dh-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
            .mi-table td,.mi-table th{padding-left:10px!important;padding-right:10px!important}
            @media (max-width:760px){
              .dh-scroll{overflow:visible}
              .mi-table thead{display:none}
              .mi-table,.mi-table tbody{display:block;width:100%}
              .mi-table tr{display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;padding:12px 4px;border-bottom:1px solid var(--ef-border,#e5e0d6)}
              .mi-table td{border:0!important;padding:0!important;min-width:0}
              .mi-table td:nth-child(2){order:1;grid-column:1/-1;font-size:1rem}
              .mi-table td:nth-child(3){order:2;grid-column:1/-1;display:block!important;margin-top:-4px}
              .mi-table td:nth-child(1){order:3}
              .mi-table td:nth-child(5){order:4;text-align:right}
              .mi-table td:nth-child(4){order:5}
              .mi-table td:nth-child(6){order:6;text-align:right}
              .mi-table td:nth-child(7){order:7}
              .mi-table td:nth-child(8){order:8;justify-self:end}
            }
            </style>
            <div class="dh-scroll"><table class="mi-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>English</th>
                        <th>Tamil</th>
                        <th>Composer</th>
                        <th>Customer menu</th>
                        <th>Recipe</th>
                        <th>Used in</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <span class="mi-cat-badge">{{ $item->category_en }}</span>
                        </td>
                        <td>
                            <strong>{{ $item->item_en }}</strong>
                        </td>
                        <td class="mi-ta-text">@if($item->item_ta === $item->item_en)<span style="color:#9a6413;font-size:.78rem;font-weight:700"><i class="bi bi-translate"></i> Tamil needed</span>@else{{ $item->item_ta }}@endif</td>
                        <td>
                            <form method="POST" action="{{ route('menu.items.toggle', $item) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="mi-btn-sm" style="border-color:transparent;background:none;padding:0" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                                    <span class="mi-status-dot {{ $item->is_active ? '--on' : '--off' }}"></span>
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td style="font-size:.8rem;white-space:nowrap">
                            @if($er = $item->eventRequestItem)
                                ₹{{ rtrim(rtrim(number_format((float) $er->price_per_person, 2), '0'), '.') }}
                                <span style="color:{{ $er->is_active ? '#1f6a48' : '#9a6413' }};font-weight:700">· {{ $er->is_active ? 'offered' : 'not offered' }}</span>
                            @else<span style="color:var(--ef-faint)">—</span>@endif
                        </td>
                        <td style="font-size:.8rem;white-space:nowrap">
                            @if($rc = $item->recipe)
                                <a href="{{ route('kitchen.recipes.show', $rc) }}" style="font-weight:700;color:{{ $rc->isVerified() ? '#1f6a48' : '#9a6413' }};text-decoration:none">
                                    {{ $rc->isVerified() ? '✓ Verified' : 'Draft' }}</a>
                            @else
                                <a href="{{ route('kitchen.recipes.create', ['name' => $item->item_en]) }}" style="color:var(--ef-muted)"><i class="bi bi-plus"></i> Add</a>
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:var(--ef-muted);white-space:nowrap">
                            @if($item->used_requests)<span title="Customer event requests">{{ $item->used_requests }} req</span>@endif
                            @if($item->used_plans)<span title="Active meal plans">{{ $item->used_requests ? ' · ' : '' }}{{ $item->used_plans }} plan{{ $item->used_plans > 1 ? 's' : '' }}</span>@endif
                            @if(! $item->used_requests && ! $item->used_plans)—@endif
                        </td>
                        <td>
                            <div class="mi-actions">
                                <a href="{{ route('menu.items.edit', $item) }}" class="mi-btn-sm mi-btn-edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('menu.items.destroy', $item) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($item->item_en) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="mi-btn-sm mi-btn-del">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

</div>
</x-admin-layout>
