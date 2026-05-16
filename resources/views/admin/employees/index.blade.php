<x-admin-layout title="Employees">

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Employees — Hospitality Workforce Operations
   ═══════════════════════════════════════════════════════ */

.ef-emp-shell {
    max-width: 1480px;
    margin: 0 auto;
    padding-bottom: 88px;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-emp-hero {
    align-items: stretch;
    background: linear-gradient(135deg, rgba(255,253,250,.98), rgba(249,247,242,.94));
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, 380px);
    margin-bottom: 18px;
    overflow: hidden;
}

.ef-emp-hero-main { padding: 32px 36px; }

.ef-emp-hero-side {
    background: rgba(20,20,18,.022);
    border-left: 1px solid var(--ef-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 32px 36px;
}

.ef-emp-title {
    color: var(--ef-ink);
    font-size: clamp(2.4rem, 4vw, 3.75rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: .96;
    margin: 8px 0 16px;
}

.ef-emp-subtitle {
    color: var(--ef-muted);
    display: flex;
    flex-wrap: wrap;
    font-size: .92rem;
    gap: 5px 16px;
    margin: 0;
}

.ef-emp-subtitle i { font-size: .76rem; opacity: .55; }

.ef-emp-hero-stat {
    margin-bottom: 22px;
}

.ef-emp-hero-stat-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-emp-hero-stat-value {
    color: var(--ef-ink);
    font-size: 2.6rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1.05;
    margin-top: 4px;
}

.ef-emp-hero-stat-note {
    color: var(--ef-muted);
    font-size: .78rem;
    margin-top: 5px;
}

.ef-emp-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

/* ── Stats Strip ──────────────────────────────────────── */
.ef-emp-stats {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 18px;
}

.ef-emp-stat {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    min-height: 108px;
    padding: 18px 20px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
}

.ef-emp-stat:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
    transform: translateY(-1px);
}

.ef-emp-stat-icon {
    color: var(--ef-faint);
    font-size: .86rem;
    margin-bottom: 10px;
}

.ef-emp-stat-label {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.ef-emp-stat-value {
    color: var(--ef-ink);
    font-size: 1.38rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 9px;
}

.ef-emp-stat-note {
    color: var(--ef-muted);
    font-size: .72rem;
    line-height: 1.4;
    margin-top: 6px;
}

.ef-emp-stat.--managers  .ef-emp-stat-value { color: var(--ef-bluegray); }
.ef-emp-stat.--active    .ef-emp-stat-value { color: var(--ef-emerald); }
.ef-emp-stat.--inactive  .ef-emp-stat-value { color: var(--ef-muted); }
.ef-emp-stat.--recent    .ef-emp-stat-value { color: var(--ef-gold); }

/* ── Search + Filter Toolbar ──────────────────────────── */
.ef-emp-toolbar {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 16px;
    padding: 14px 22px;
}

.ef-emp-toolbar-inner {
    align-items: flex-end;
    display: flex;
    flex-wrap: wrap;
    gap: 10px 14px;
}

.ef-emp-search-wrap {
    flex: 1;
    min-width: 240px;
    position: relative;
}

.ef-emp-search-icon {
    color: var(--ef-faint);
    font-size: .9rem;
    left: 12px;
    pointer-events: none;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}

.ef-emp-search-input {
    background: rgba(251,250,247,.96);
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink);
    font-size: .9rem;
    height: 42px;
    padding: 0 12px 0 34px;
    transition: background .16s var(--ef-ease), border-color .16s var(--ef-ease), box-shadow .16s var(--ef-ease);
    width: 100%;
}

.ef-emp-search-input:focus {
    background: #fff;
    border-color: rgba(20,20,18,.46);
    box-shadow: 0 0 0 4px rgba(20,20,18,.05);
    outline: 0;
}

.ef-emp-search-input::placeholder { color: var(--ef-faint); }

.ef-emp-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ef-emp-filter-label {
    color: var(--ef-faint);
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-emp-filter-select {
    background: rgba(251,250,247,.96);
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink-2);
    font-size: .84rem;
    font-weight: 520;
    height: 38px;
    padding: 0 11px;
    transition: border-color .16s var(--ef-ease), box-shadow .16s var(--ef-ease);
}

.ef-emp-filter-select:focus {
    border-color: rgba(20,20,18,.46);
    box-shadow: 0 0 0 4px rgba(20,20,18,.05);
    outline: 0;
}

.ef-emp-toolbar-actions {
    align-items: flex-end;
    display: flex;
    gap: 8px;
}

.ef-emp-toolbar-sep {
    background: var(--ef-border);
    height: 30px;
    width: 1px;
    flex-shrink: 0;
    align-self: flex-end;
    margin-bottom: 4px;
}

.ef-emp-active-chip {
    align-items: center;
    background: rgba(96,112,128,.08);
    border: 1px solid rgba(96,112,128,.18);
    border-radius: 999px;
    color: var(--ef-bluegray);
    display: flex;
    font-size: .64rem;
    font-weight: 760;
    gap: 5px;
    letter-spacing: .06em;
    padding: 4px 10px;
    text-transform: uppercase;
    align-self: flex-end;
}

/* ── Employee List ────────────────────────────────────── */
.ef-emp-list-wrap {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

.ef-emp-list-head {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.065);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 13px 24px;
}

.ef-emp-list-title {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-emp-list-count {
    color: var(--ef-muted);
    font-size: .76rem;
}

/* ── Employee Row ─────────────────────────────────────── */
.ef-emp-row {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.058);
    display: grid;
    gap: 0 16px;
    grid-template-columns: 48px minmax(0, 1fr) 190px auto auto;
    padding: 16px 24px;
    transition: background .14s var(--ef-ease);
}

.ef-emp-row:last-child { border-bottom: 0; }
.ef-emp-row:hover { background: rgba(20,20,18,.015); }

/* Avatar */
.ef-emp-avatar {
    align-items: center;
    border-radius: 12px;
    color: rgba(255,253,250,.94);
    display: flex;
    flex-shrink: 0;
    font-size: .78rem;
    font-weight: 780;
    height: 44px;
    justify-content: center;
    letter-spacing: .02em;
    transition: transform .14s var(--ef-ease);
    width: 44px;
}

.ef-emp-row:hover .ef-emp-avatar { transform: scale(1.04); }

.ef-emp-avatar[data-role="manager"] {
    background: linear-gradient(135deg, #607080, #4a5f70);
    box-shadow: 0 4px 12px rgba(96,112,128,.28);
}

.ef-emp-avatar[data-role="employee"] {
    background: linear-gradient(135deg, #494642, #343130);
    box-shadow: 0 4px 12px rgba(20,20,18,.22);
}

.ef-emp-avatar[data-role="admin"] {
    background: linear-gradient(135deg, #8d4a3c, #6e3a2f);
    box-shadow: 0 4px 12px rgba(141,74,60,.28);
}

/* Identity */
.ef-emp-identity { min-width: 0; }

.ef-emp-name {
    color: var(--ef-ink);
    font-size: .96rem;
    font-weight: 720;
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-emp-email {
    color: var(--ef-muted);
    font-size: .78rem;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Contact */
.ef-emp-contact {
    align-items: center;
    display: flex;
    gap: 6px;
}

.ef-emp-phone-text {
    color: var(--ef-muted);
    font-size: .8rem;
    font-variant-numeric: tabular-nums;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-emp-contact-btn {
    align-items: center;
    background: rgba(20,20,18,.04);
    border: 1px solid rgba(20,20,18,.08);
    border-radius: 8px;
    color: var(--ef-muted);
    display: inline-flex;
    font-size: .82rem;
    height: 30px;
    justify-content: center;
    text-decoration: none;
    transition: background .14s var(--ef-ease), border-color .14s var(--ef-ease), color .14s var(--ef-ease);
    width: 30px;
    flex-shrink: 0;
}

.ef-emp-contact-btn:hover {
    background: rgba(20,20,18,.08);
    border-color: rgba(20,20,18,.16);
    color: var(--ef-ink);
}

.ef-emp-contact-btn.--wa:hover {
    background: rgba(37,211,102,.1);
    border-color: rgba(37,211,102,.2);
    color: #25d366;
}

.ef-emp-no-contact {
    color: var(--ef-faint);
    font-size: .76rem;
}

/* Chips column */
.ef-emp-chips {
    align-items: flex-start;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

/* Actions column */
.ef-emp-row-actions {
    align-items: center;
    display: flex;
    gap: 5px;
}

/* ── Pagination ────────────────────────────────────────── */
.ef-emp-pagination {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}

.ef-emp-pagination .pagination { gap: 4px; margin: 0; }

.ef-emp-pagination .page-link {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: 10px !important;
    color: var(--ef-ink-2);
    font-size: .8rem;
    font-weight: 650;
    height: 36px;
    line-height: 36px;
    min-width: 36px;
    padding: 0 10px;
    text-align: center;
    transition: background .15s var(--ef-ease), border-color .15s var(--ef-ease);
}

.ef-emp-pagination .page-link:hover {
    background: var(--ef-surface-2);
    border-color: var(--ef-border-strong);
    color: var(--ef-ink);
}

.ef-emp-pagination .active .page-link {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
}

.ef-emp-pagination .disabled .page-link { opacity: .36; }

/* ── Delete Modal ──────────────────────────────────────── */
.ef-emp-modal .modal-content {
    background: #fffdfa;
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: 0 28px 80px rgba(24,22,18,.2);
}

.ef-emp-modal .modal-header,
.ef-emp-modal .modal-footer {
    border-color: var(--ef-border);
    padding: 20px 24px;
}

.ef-emp-modal .modal-body { padding: 24px; }

/* ── Mobile Bar ────────────────────────────────────────── */
.ef-emp-mobile-bar {
    backdrop-filter: blur(18px) saturate(160%);
    background: rgba(255,253,250,.94);
    border-top: 1px solid var(--ef-border);
    bottom: 0;
    display: none;
    gap: 8px;
    grid-template-columns: 1fr auto;
    left: 0;
    padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
    position: fixed;
    right: 0;
    z-index: 1040;
}

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-emp-hero { grid-template-columns: 1fr; }
    .ef-emp-hero-side {
        border-left: 0;
        border-top: 1px solid var(--ef-border);
    }
    .ef-emp-actions { justify-content: flex-start; }
    .ef-emp-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ef-emp-hero-stat-value { font-size: 2rem; }
}

@media (max-width: 991.98px) {
    .ef-emp-row {
        grid-template-columns: 44px minmax(0, 1fr) auto auto;
    }
    .ef-emp-contact { display: none; }
}

@media (max-width: 767.98px) {
    .ef-emp-shell { padding-bottom: 84px; }
    .ef-emp-hero-main, .ef-emp-hero-side { padding: 24px; }
    .ef-emp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-emp-stats .ef-emp-stat:last-child { display: none; }

    .ef-emp-toolbar-inner { gap: 10px; }
    .ef-emp-search-wrap { min-width: 100%; }
    .ef-emp-toolbar-sep { display: none; }

    .ef-emp-row {
        grid-template-columns: 38px minmax(0, 1fr) auto;
        gap: 0 12px;
        padding: 13px 16px;
    }

    .ef-emp-chips { display: none; } /* chips shown as avatar color instead */
    .ef-emp-avatar {
        height: 38px;
        width: 38px;
        border-radius: 10px;
        font-size: .7rem;
    }

    .ef-emp-mobile-bar { display: grid; }
}

@media print {
    .ef-emp-toolbar,
    .ef-emp-actions,
    .ef-emp-mobile-bar,
    .ef-emp-row-actions { display: none !important; }
}
</style>
@endpush

@php
$hasFilters = $search || $role || $status;

$roleTones = [
    'admin'    => 'danger',
    'manager'  => 'bluegray',
    'employee' => 'neutral',
];
@endphp

<div class="ef-emp-shell">

    {{-- ═══ HERO ════════════════════════════════════════════════════════════ --}}
    <header class="ef-emp-hero">

        <div class="ef-emp-hero-main">
            <p class="ef-eyebrow">Hospitality Workforce Operations</p>
            <h1 class="ef-emp-title">Employees</h1>
            <p class="ef-emp-subtitle">
                <span><i class="bi bi-calendar3"></i> {{ now()->format('l, d F Y') }}</span>
                <span><i class="bi bi-people"></i> Workforce and access management</span>
            </p>
        </div>

        <div class="ef-emp-hero-side">
            <div class="ef-emp-hero-stat">
                <div class="ef-emp-hero-stat-label">Total Workforce</div>
                <div class="ef-emp-hero-stat-value">{{ number_format($stats['total']) }}</div>
                <div class="ef-emp-hero-stat-note">{{ $stats['active'] }} active · {{ $stats['managers'] }} managers</div>
            </div>

            <div class="ef-emp-actions">
                <a href="{{ route('admin.employees.create') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-person-plus"></i> Add Employee
                </a>
                <button class="ef-btn" onclick="window.print()" title="Print Directory">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>

    </header>

    {{-- ═══ STATS STRIP ═══════════════════════════════════════════════════ --}}
    <div class="ef-emp-stats">

        <div class="ef-emp-stat">
            <div class="ef-emp-stat-icon"><i class="bi bi-people"></i></div>
            <div class="ef-emp-stat-label">Total Workforce</div>
            <div class="ef-emp-stat-value">{{ number_format($stats['total']) }}</div>
            <div class="ef-emp-stat-note">employees and managers</div>
        </div>

        <div class="ef-emp-stat --managers">
            <div class="ef-emp-stat-icon"><i class="bi bi-person-badge"></i></div>
            <div class="ef-emp-stat-label">Managers</div>
            <div class="ef-emp-stat-value">{{ number_format($stats['managers']) }}</div>
            <div class="ef-emp-stat-note">operational leads</div>
        </div>

        <div class="ef-emp-stat --active">
            <div class="ef-emp-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="ef-emp-stat-label">Active Staff</div>
            <div class="ef-emp-stat-value">{{ number_format($stats['active']) }}</div>
            <div class="ef-emp-stat-note">with system access</div>
        </div>

        <div class="ef-emp-stat --inactive">
            <div class="ef-emp-stat-icon"><i class="bi bi-pause-circle"></i></div>
            <div class="ef-emp-stat-label">Inactive</div>
            <div class="ef-emp-stat-value">{{ number_format($stats['inactive']) }}</div>
            <div class="ef-emp-stat-note">access suspended</div>
        </div>

        <div class="ef-emp-stat --recent">
            <div class="ef-emp-stat-icon"><i class="bi bi-person-check"></i></div>
            <div class="ef-emp-stat-label">Recent Joins</div>
            <div class="ef-emp-stat-value">{{ number_format($stats['recent']) }}</div>
            <div class="ef-emp-stat-note">last 30 days</div>
        </div>

    </div>

    {{-- ═══ SEARCH + FILTER TOOLBAR ════════════════════════════════════════ --}}
    <div class="ef-emp-toolbar">
        <form method="GET" action="{{ route('admin.employees.index') }}"
              class="ef-emp-toolbar-inner" id="empFilterForm">

            <div class="ef-emp-search-wrap">
                <i class="bi bi-search ef-emp-search-icon"></i>
                <input type="text" name="search"
                       class="ef-emp-search-input"
                       placeholder="Search by name, email or phone…"
                       value="{{ $search }}">
            </div>

            <div class="ef-emp-toolbar-sep"></div>

            <div class="ef-emp-filter-group">
                <label class="ef-emp-filter-label">Role</label>
                <select name="role" class="ef-emp-filter-select">
                    <option value="">All roles</option>
                    <option value="manager"  {{ $role === 'manager'  ? 'selected' : '' }}>Manager</option>
                    <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
            </div>

            <div class="ef-emp-filter-group">
                <label class="ef-emp-filter-label">Status</label>
                <select name="status" class="ef-emp-filter-select">
                    <option value="">All statuses</option>
                    <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="ef-emp-toolbar-actions">
                @if($hasFilters)
                    <span class="ef-emp-active-chip">
                        <i class="bi bi-funnel-fill"></i> Filtered
                    </span>
                    <a href="{{ route('admin.employees.index') }}" class="ef-btn">
                        <i class="bi bi-x"></i> Reset
                    </a>
                @endif
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>

        </form>
    </div>

    {{-- ═══ EMPLOYEE LIST ══════════════════════════════════════════════════ --}}
    <div class="ef-emp-list-wrap">

        <div class="ef-emp-list-head">
            <span class="ef-emp-list-title">Workforce Directory</span>
            <span class="ef-emp-list-count">
                {{ $employees->total() }} member{{ $employees->total() != 1 ? 's' : '' }}
                @if($employees->total() > 0)
                    · {{ $employees->firstItem() }}–{{ $employees->lastItem() }} shown
                @endif
            </span>
        </div>

        @forelse($employees as $employee)
        @php
            $nameParts = explode(' ', trim($employee->name));
            $initials  = strtoupper(
                substr($nameParts[0], 0, 1) .
                (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
            );
            $tone     = $roleTones[$employee->role] ?? 'neutral';
            $waPhone  = preg_replace('/\D/', '', $employee->phone ?? '');
        @endphp

        <div class="ef-emp-row">

            {{-- Avatar --}}
            <div class="ef-emp-avatar" data-role="{{ $employee->role }}">{{ $initials }}</div>

            {{-- Identity --}}
            <div class="ef-emp-identity">
                <div class="ef-emp-name">{{ $employee->name }}</div>
                <div class="ef-emp-email">{{ $employee->email }}</div>
            </div>

            {{-- Contact (hidden on mobile) --}}
            <div class="ef-emp-contact">
                @if($employee->phone)
                    <span class="ef-emp-phone-text">{{ $employee->phone }}</span>
                    <a href="tel:{{ $employee->phone }}"
                       class="ef-emp-contact-btn"
                       title="Call {{ $employee->name }}"
                       onclick="event.stopPropagation()">
                        <i class="bi bi-telephone"></i>
                    </a>
                    @if($waPhone)
                    <a href="https://wa.me/{{ $waPhone }}"
                       class="ef-emp-contact-btn --wa"
                       target="_blank"
                       title="WhatsApp {{ $employee->name }}"
                       onclick="event.stopPropagation()">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    @endif
                @else
                    <span class="ef-emp-no-contact">No phone</span>
                @endif
            </div>

            {{-- Chips --}}
            <div class="ef-emp-chips">
                <x-premium.chip :tone="$tone">{{ ucfirst($employee->role) }}</x-premium.chip>
                <x-premium.chip :tone="$employee->is_active ? 'emerald' : 'neutral'">
                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                </x-premium.chip>
            </div>

            {{-- Actions --}}
            <div class="ef-emp-row-actions">
                <a href="{{ route('admin.employees.edit', $employee) }}"
                   class="ef-btn ef-btn-icon" title="Edit {{ $employee->name }}">
                    <i class="bi bi-pencil"></i>
                </a>

                <div class="dropdown">
                    <button class="ef-btn ef-btn-icon"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                        style="border-color:var(--ef-border);border-radius:12px;min-width:172px">

                        @if($employee->phone)
                        <li>
                            <a class="dropdown-item" href="tel:{{ $employee->phone }}"
                               style="font-size:.84rem">
                                <i class="bi bi-telephone me-2 opacity-55"></i> Call
                            </a>
                        </li>
                        @if($waPhone)
                        <li>
                            <a class="dropdown-item"
                               href="https://wa.me/{{ $waPhone }}" target="_blank"
                               style="font-size:.84rem">
                                <i class="bi bi-whatsapp me-2 opacity-55"></i> WhatsApp
                            </a>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        @endif

                        <li>
                            <form method="POST"
                                  action="{{ route('admin.employees.toggle-status', $employee) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="dropdown-item"
                                        style="font-size:.84rem">
                                    <i class="bi bi-{{ $employee->is_active ? 'pause-circle' : 'play-circle' }} me-2 opacity-55"></i>
                                    {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </li>

                        @if(auth()->id() !== $employee->id)
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-danger"
                                    style="font-size:.84rem"
                                    data-bs-toggle="modal"
                                    data-bs-target="#delModal{{ $employee->id }}">
                                <i class="bi bi-trash me-2 opacity-65"></i> Delete
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        @empty

        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-people"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">
                No employees found
            </h3>
            <p style="color:var(--ef-muted);font-size:.88rem;margin:0 0 22px;max-width:300px;line-height:1.6">
                @if($hasFilters)
                    No employees match your current filters. Try adjusting the search or role selection.
                @else
                    Employee records and workforce operations will appear here once staff are added.
                @endif
            </p>
            @if($hasFilters)
                <a href="{{ route('admin.employees.index') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            @else
                <a href="{{ route('admin.employees.create') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-person-plus"></i> Add Employee
                </a>
            @endif
        </div>

        @endforelse
    </div>

    {{-- Pagination --}}
    @if($employees->hasPages())
        <div class="ef-emp-pagination">{{ $employees->links() }}</div>
    @endif

</div>

{{-- ═══ MOBILE STICKY BAR ════════════════════════════════════════════════ --}}
<div class="ef-emp-mobile-bar">
    <a href="{{ route('admin.employees.create') }}" class="ef-btn ef-btn-dark"
       style="justify-content:center">
        <i class="bi bi-person-plus"></i> Add Employee
    </a>
    <button class="ef-btn ef-btn-icon" onclick="window.print()" title="Print">
        <i class="bi bi-printer"></i>
    </button>
</div>

{{-- ═══ DELETE MODALS ══════════════════════════════════════════════════════ --}}
@foreach($employees as $employee)
@if(auth()->id() !== $employee->id)
<div class="modal fade ef-emp-modal" id="delModal{{ $employee->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title" style="color:var(--ef-ink);font-weight:760">
                    <i class="bi bi-person-x text-danger me-2"></i> Remove Employee
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="rounded-3 p-3 mb-3"
                     style="background:rgba(141,74,60,.06);border:1px solid rgba(141,74,60,.14)">
                    <p class="mb-0" style="color:var(--ef-danger);font-size:.82rem;font-weight:680">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This permanently removes the employee and all associated data.
                    </p>
                </div>

                <div style="display:flex;align-items:center;gap:12px">
                    <div class="ef-emp-avatar" data-role="{{ $employee->role }}"
                         style="width:38px;height:38px;border-radius:10px;font-size:.7rem;flex-shrink:0">
                        @php
                            $p = explode(' ', trim($employee->name));
                            echo strtoupper(substr($p[0],0,1).(isset($p[1])?substr($p[1],0,1):''));
                        @endphp
                    </div>
                    <div>
                        <div style="color:var(--ef-ink);font-size:.92rem;font-weight:720">{{ $employee->name }}</div>
                        <div style="color:var(--ef-muted);font-size:.78rem">{{ $employee->email }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 gap-2">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST"
                      action="{{ route('admin.employees.destroy', $employee) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn"
                            style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fff"
                            data-loading-text="Removing…">
                        Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

</x-admin-layout>
