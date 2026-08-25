<x-admin-layout title="Employees">

@php
$hasFilters = $search || $role || $status;
$activeFilterCount = ($role ? 1 : 0) + ($status ? 1 : 0);

$roleTones = [
    'manager'  => 'gold',
    'employee' => 'neutral',
];
@endphp

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   EMPLOYEES — reuses the application's existing design tokens/
   components (x-ds.hero, x-ds.kpi-card, ef-input, ef-btn, x-premium.chip
   — resources/css/app.css) rather than a page-specific palette. Only the
   employee-row layout and the compact filter panel need page-scoped
   CSS below; everything else is a shared class.
   ══════════════════════════════════════════════════════════════════ */

.ef-emp-toolbar-row { display: flex; gap: 8px; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
.ef-emp-search-wrap { position: relative; flex: 1; min-width: 220px; }
.ef-emp-search-icon {
    position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
    color: var(--ef-faint); font-size: .85rem; pointer-events: none;
}
.ef-emp-search-wrap .ef-input { padding-left: 2.2rem; }

.ef-emp-filter-toggle {
    position: relative;
}
.ef-emp-filter-badge {
    position: absolute; top: -6px; right: -6px;
    background: var(--ef-emerald); color: #fff;
    font-size: .62rem; font-weight: 800; line-height: 1;
    border-radius: 999px; min-width: 16px; height: 16px;
    display: flex; align-items: center; justify-content: center; padding: 0 3px;
}

.ef-emp-filter-panel {
    background: var(--ef-surface); border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    padding: 14px 16px; margin-bottom: 10px;
}
.ef-emp-filter-section + .ef-emp-filter-section { margin-top: 12px; }
.ef-emp-filter-label {
    font-size: .68rem; font-weight: 760; letter-spacing: .08em; text-transform: uppercase;
    color: var(--ef-faint); margin-bottom: 8px;
}
.ef-emp-chips { display: flex; flex-wrap: wrap; gap: 7px; }
.ef-emp-chip-btn {
    display: inline-flex; align-items: center; gap: 5px;
    min-height: 36px; padding: 6px 14px; border-radius: 20px;
    border: 1.5px solid var(--ef-border); background: var(--ef-surface-2);
    color: var(--ef-muted); font-size: .82rem; font-weight: 650; cursor: pointer;
    transition: all .15s var(--ef-ease); white-space: nowrap;
}
.ef-emp-chip-btn:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); }
.ef-emp-chip-btn.--active {
    background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff;
}
.ef-emp-filter-footer { display: flex; justify-content: flex-end; margin-top: 12px; }

/* ── Employee directory list ─────────────────────────────────────── */
.ef-emp-list-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; border-bottom: 1px solid var(--ef-border);
    gap: 10px;
}
.ef-emp-list-title { font-size: .68rem; font-weight: 760; letter-spacing: .1em; text-transform: uppercase; color: var(--ef-faint); }
.ef-emp-list-count { font-size: .78rem; color: var(--ef-muted); }

.ef-emp-row {
    display: flex; flex-direction: column; gap: 10px;
    padding: 14px 18px; border-bottom: 1px solid var(--ef-border);
    transition: background .14s var(--ef-ease);
}
.ef-emp-row:last-child { border-bottom: none; }
.ef-emp-row:hover { background: var(--ef-surface-2); }

.ef-emp-row-main {
    display: flex; align-items: center; gap: 12px; min-width: 0;
    text-decoration: none; color: inherit; border-radius: 8px;
}
.ef-emp-row-main:focus-visible { outline: 2px solid var(--ef-emerald); outline-offset: 2px; }
.ef-emp-avatar {
    width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, #1a6645, #22845a);
    color: #fff; font-size: .78rem; font-weight: 780; letter-spacing: .02em;
    display: flex; align-items: center; justify-content: center;
}
.ef-emp-identity { min-width: 0; flex: 1; }
.ef-emp-name { font-size: .92rem; font-weight: 720; color: var(--ef-ink); line-height: 1.25; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-emp-email { font-size: .78rem; color: var(--ef-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 1px; }

.ef-emp-row-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-left: 54px; }
.ef-emp-row-chips { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.ef-emp-more-btn {
    width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: transparent; border: 1px solid var(--ef-border);
    color: var(--ef-muted); transition: all .15s var(--ef-ease);
}
.ef-emp-more-btn:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); background: var(--ef-surface-2); }

@media (min-width: 640px) {
    .ef-emp-row { flex-direction: row; align-items: center; padding: 12px 18px; }
    .ef-emp-row-main { flex: 1 1 auto; }
    .ef-emp-row-foot { padding-left: 0; flex: 0 0 auto; gap: 16px; }
}

.ef-emp-pagination { display: flex; justify-content: center; margin-top: 14px; }
.ef-emp-pagination .pagination { gap: 4px; margin: 0; }
.ef-emp-pagination .page-link {
    border-radius: 10px !important; font-size: .78rem; font-weight: 650;
    height: 34px; line-height: 34px; min-width: 34px; padding: 0 9px; text-align: center;
}
.ef-emp-pagination .active .page-link { background: var(--ef-ink); border-color: var(--ef-ink); color: #fffdfa; }
</style>
@endpush

<x-ds.hero eyebrow="Workforce Operations" title="Employees"
    :meta="[['icon' => 'bi-people', 'text' => 'Manage workforce accounts, roles and access']]">
    <x-slot:actions>
        <a href="{{ route('admin.employees.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-person-plus"></i> <span>Add Employee</span>
        </a>
    </x-slot:actions>
    <x-slot:mobile_stat>
        <span class="ef-ds-hero-mstat-val">{{ number_format($stats['total']) }}</span>
        <span class="ef-ds-hero-mstat-note">total &middot; {{ $stats['active'] }} active &middot; {{ $stats['managers'] }} managers</span>
    </x-slot:mobile_stat>
</x-ds.hero>

{{-- ═══ SUMMARY ═══════════════════════════════════════════════════════ --}}
<div class="ef-ds-kpi-wrap">
    <div class="ef-ds-kpi-grid" style="--kpi-cols:4">
        <x-ds.kpi-card icon="bi-people" label="Total Workforce" :value="number_format($stats['total'])" accent="emerald" value-color="c-emerald" />
        <x-ds.kpi-card icon="bi-person-badge" label="Managers" :value="number_format($stats['managers'])" accent="gold" value-color="c-gold" />
        <x-ds.kpi-card icon="bi-check-circle" label="Active Staff" :value="number_format($stats['active'])" accent="emerald" value-color="c-emerald" />
        <x-ds.kpi-card icon="bi-pause-circle" label="Inactive" :value="number_format($stats['inactive'])" accent="muted" value-color="c-muted" />
    </div>
</div>

{{-- ═══ SEARCH + FILTERS ══════════════════════════════════════════════ --}}
<div x-data="{
        search:  @js($search),
        role:    @js($role),
        status:  @js($status),
        open:    false,
        base:    '{{ route('admin.employees.index') }}',
        get activeCount() { return [this.role, this.status].filter(Boolean).length; },
        navigate() {
            const p = new URLSearchParams();
            if (this.search.trim()) p.set('search', this.search.trim());
            if (this.role)          p.set('role', this.role);
            if (this.status)        p.set('status', this.status);
            window.location.href = this.base + (p.toString() ? '?' + p.toString() : '');
        },
        setRole(v)   { this.role = v;   this.navigate(); },
        setStatus(v) { this.status = v; this.navigate(); },
        reset()      { this.search = ''; this.role = ''; this.status = ''; this.navigate(); },
    }">

    <div class="ef-emp-toolbar-row">
        <div class="ef-emp-search-wrap">
            <i class="bi bi-search ef-emp-search-icon"></i>
            <input type="text" class="ef-input" placeholder="Search employees…"
                   x-model="search" @keydown.enter="navigate()">
        </div>
        <button type="button" class="ef-btn ef-emp-filter-toggle" @click="open = !open" :aria-expanded="open.toString()" aria-controls="empFilterPanel">
            <i class="bi bi-funnel"></i> Filters
            <template x-if="activeCount > 0">
                <span class="ef-emp-filter-badge" x-text="activeCount"></span>
            </template>
        </button>
        <button type="button" class="ef-btn ef-btn-dark" @click="navigate()">
            <i class="bi bi-search"></i> <span class="d-none d-sm-inline">Search</span>
        </button>
    </div>

    <div class="ef-emp-filter-panel" id="empFilterPanel" x-show="open" x-cloak x-transition>
        <div class="ef-emp-filter-section">
            <div class="ef-emp-filter-label">Role</div>
            <div class="ef-emp-chips">
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': role === '' }" @click="setRole('')">All</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': role === 'employee' }" @click="setRole('employee')">Employee</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': role === 'manager' }" @click="setRole('manager')">Manager</button>
            </div>
        </div>
        <div class="ef-emp-filter-section">
            <div class="ef-emp-filter-label">Status</div>
            <div class="ef-emp-chips">
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': status === '' }" @click="setStatus('')">Any</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': status === 'active' }" @click="setStatus('active')">Active</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': status === 'inactive' }" @click="setStatus('inactive')">Inactive</button>
            </div>
        </div>
        <div class="ef-emp-filter-footer">
            <button type="button" class="ef-btn" @click="reset()">
                <i class="bi bi-x-circle"></i> Reset
            </button>
        </div>
    </div>
</div>

{{-- ═══ EMPLOYEE DIRECTORY ════════════════════════════════════════════ --}}
<x-ds.card :no-pad="true">
    <div class="ef-emp-list-head">
        <span class="ef-emp-list-title">Workforce Directory</span>
        <span class="ef-emp-list-count">
            {{ $employees->total() }} member{{ $employees->total() != 1 ? 's' : '' }}
        </span>
    </div>

    @forelse($employees as $employee)
    @php
        $nameParts = explode(' ', trim($employee->name));
        $initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
        $tone      = $roleTones[$employee->role] ?? 'neutral';
        $waPhone   = preg_replace('/\D/', '', $employee->phone ?? '');
    @endphp

    <div class="ef-emp-row">
        <a href="{{ route('admin.employees.show', $employee) }}" class="ef-emp-row-main">
            <div class="ef-emp-avatar">{{ $initials }}</div>
            <div class="ef-emp-identity">
                <div class="ef-emp-name">{{ $employee->name }}</div>
                <div class="ef-emp-email">{{ $employee->email }}</div>
            </div>
        </a>

        <div class="ef-emp-row-foot">
            <div class="ef-emp-row-chips">
                <x-premium.chip :tone="$tone">{{ ucfirst($employee->role) }}</x-premium.chip>
                <x-premium.chip :tone="$employee->is_active ? 'emerald' : 'neutral'">
                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                </x-premium.chip>
            </div>

            <div class="dropdown">
                <button class="ef-emp-more-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions for {{ $employee->name }}" title="More actions">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-color:var(--ef-border);border-radius:12px;min-width:180px">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.employees.show', $employee) }}" style="font-size:.84rem">
                            <i class="bi bi-eye me-2 opacity-55"></i> View details
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.employees.edit', $employee) }}" style="font-size:.84rem">
                            <i class="bi bi-pencil me-2 opacity-55"></i> Edit
                        </a>
                    </li>
                    @if($employee->phone)
                    <li>
                        <a class="dropdown-item" href="tel:{{ $employee->phone }}" style="font-size:.84rem">
                            <i class="bi bi-telephone me-2 opacity-55"></i> Call
                        </a>
                    </li>
                    @if($waPhone)
                    <li>
                        <a class="dropdown-item" href="https://wa.me/{{ $waPhone }}" target="_blank" style="font-size:.84rem">
                            <i class="bi bi-whatsapp me-2 opacity-55"></i> WhatsApp
                        </a>
                    </li>
                    @endif
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="dropdown-item" style="font-size:.84rem">
                                <i class="bi bi-{{ $employee->is_active ? 'pause-circle' : 'play-circle' }} me-2 opacity-55"></i>
                                {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </li>
                    @if(auth()->id() !== $employee->id)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item" style="color:var(--ef-danger);font-size:.84rem"
                                data-bs-toggle="modal" data-bs-target="#delModal{{ $employee->id }}">
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
        <div class="ef-empty-orb">
            <i class="bi bi-{{ $hasFilters ? 'funnel' : 'people' }}"></i>
        </div>
        <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">
            {{ $hasFilters ? 'No employees found' : 'No employees yet' }}
        </h3>
        <p style="color:var(--ef-muted);font-size:.86rem;margin:0 0 20px;max-width:300px;line-height:1.6">
            @if($hasFilters)
                Try changing your search or filters.
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
</x-ds.card>

@if($employees->hasPages())
    <div class="ef-emp-pagination">{{ $employees->links() }}</div>
@endif

{{-- ═══ DELETE MODALS ═════════════════════════════════════════════════ --}}
@foreach($employees as $employee)
@if(auth()->id() !== $employee->id)
<div class="modal fade" id="delModal{{ $employee->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border:1px solid var(--ef-border);border-radius:16px">
            <div class="modal-header border-0">
                <h6 class="modal-title" style="color:var(--ef-ink);font-weight:760">
                    <i class="bi bi-person-x me-2" style="color:var(--ef-danger)"></i> Remove Employee
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="rounded-3 p-3 mb-3" style="background:rgba(200,75,68,.06);border:1px solid rgba(200,75,68,.16)">
                    <p class="mb-0" style="color:var(--ef-danger);font-size:.8rem;font-weight:680">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This permanently removes the employee and all associated data.
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                    <div class="ef-emp-avatar" style="width:36px;height:36px;border-radius:10px;font-size:.68rem">
                        @php
                            $p = explode(' ', trim($employee->name));
                            echo strtoupper(substr($p[0],0,1).(isset($p[1])?substr($p[1],0,1):''));
                        @endphp
                    </div>
                    <div>
                        <div style="color:var(--ef-ink);font-size:.9rem;font-weight:720">{{ $employee->name }}</div>
                        <div style="color:var(--ef-muted);font-size:.76rem">{{ $employee->email }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 gap-2">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn" style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fff">
                        Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

</x-admin-layout>
