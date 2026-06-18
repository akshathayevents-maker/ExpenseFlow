<x-admin-layout title="Menu Item Master">
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
            <i class="bi bi-journal-text me-2" style="color:var(--mi-gold)"></i>Menu Item Master
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
        </form>

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
            <table class="mi-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>English</th>
                        <th>Tamil</th>
                        <th>Status</th>
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
                        <td class="mi-ta-text">{{ $item->item_ta }}</td>
                        <td>
                            <form method="POST" action="{{ route('menu.items.toggle', $item) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="mi-btn-sm" style="border-color:transparent;background:none;padding:0" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                                    <span class="mi-status-dot {{ $item->is_active ? '--on' : '--off' }}"></span>
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
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
            </table>
        @endif
    </div>

</div>
</x-admin-layout>
