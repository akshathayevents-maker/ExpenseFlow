<x-admin-layout title="Employee Salaries">

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   EMPLOYEE SALARIES — reuses the application's existing design tokens/
   components (x-ds.hero, x-ds.kpi-card, ef-input, ef-btn, x-premium.chip)
   rather than a page-specific palette — same pattern as the redesigned
   Employees directory.
   ══════════════════════════════════════════════════════════════════ */

.ef-emp-toolbar-row { display: flex; gap: 8px; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
.ef-emp-search-wrap { position: relative; flex: 1; min-width: 220px; }
.ef-emp-search-icon {
    position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
    color: var(--ef-faint); font-size: .85rem; pointer-events: none;
}
.ef-emp-search-wrap .ef-input { padding-left: 2.2rem; }
.ef-emp-filter-toggle { position: relative; }
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
.ef-emp-chip-btn.--active { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.ef-emp-filter-footer { display: flex; justify-content: flex-end; margin-top: 12px; }

.ef-emp-list-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; border-bottom: 1px solid var(--ef-border); gap: 10px;
}
.ef-emp-list-title { font-size: .68rem; font-weight: 760; letter-spacing: .1em; text-transform: uppercase; color: var(--ef-faint); }
.ef-emp-list-count { font-size: .78rem; color: var(--ef-muted); }

/* ── Salary row/card — mobile column, desktop row ────────────────── */
.sal-row {
    display: flex; flex-direction: column; gap: 10px;
    padding: 14px 18px; border-bottom: 1px solid var(--ef-border);
    text-decoration: none; color: inherit; transition: background .14s var(--ef-ease);
}
.sal-row:last-child { border-bottom: none; }
.sal-row:hover, .sal-row:focus-visible { background: var(--ef-surface-2); }
.sal-row:focus-visible { outline: 2px solid var(--ef-emerald); outline-offset: -2px; }

.sal-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.sal-row-name { font-weight: 720; font-size: .94rem; color: var(--ef-ink); }
.sal-row-role { font-size: .8rem; color: var(--ef-muted); margin-top: 1px; text-transform: capitalize; }
.sal-row-chips { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

.sal-row-bottom { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.sal-amount-block { min-width: 0; }
.sal-amount { font-size: 1.35rem; font-weight: 800; letter-spacing: -.02em; color: var(--ef-ink); line-height: 1.15; }
.sal-amount-sub { font-size: .78rem; color: var(--ef-muted); margin-top: 1px; }
.sal-unconfigured {
    display: flex; align-items: center; gap: 7px;
    color: #7D5218; font-size: .88rem; font-weight: 650;
}
.sal-unconfigured i { font-size: 1rem; }

.sal-action-btn {
    flex-shrink: 0; white-space: nowrap;
}

@media (min-width: 640px) {
    .sal-row { flex-direction: row; align-items: center; padding: 12px 18px; }
    .sal-row-top { flex: 1 1 auto; min-width: 0; align-items: center; gap: 14px; }
    .sal-row-bottom { flex: 0 0 auto; }
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

<x-ds.hero eyebrow="Compensation / Payroll" title="Employee Salaries"
    :meta="[['icon' => 'bi-cash-coin', 'text' => 'Manage monthly salary configuration']]">
    <x-slot:actions>
        <a href="{{ route('admin.employees.index') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Set Salary</span>
        </a>
    </x-slot:actions>
    <x-slot:mobile_stat>
        <span class="ef-ds-hero-mstat-val">{{ $configuredCount }}/{{ $totalEmployees }}</span>
        <span class="ef-ds-hero-mstat-note">configured &middot; {{ $notConfiguredCount }} need setup</span>
    </x-slot:mobile_stat>
</x-ds.hero>

{{-- ═══ PAYROLL SUMMARY ═══════════════════════════════════════════════ --}}
<div class="ef-ds-kpi-wrap">
    <div class="ef-ds-kpi-grid" style="--kpi-cols:4">
        <x-ds.kpi-card icon="bi-people" label="Total Employees" :value="number_format($totalEmployees)" accent="emerald" value-color="c-emerald" />
        <x-ds.kpi-card icon="bi-check-circle" label="Salary Configured" :value="number_format($configuredCount)" accent="emerald" value-color="c-emerald" />
        <x-ds.kpi-card icon="bi-exclamation-circle" label="Need Salary Setup" :value="number_format($notConfiguredCount)" :accent="$notConfiguredCount > 0 ? 'amber' : 'muted'" :value-color="$notConfiguredCount > 0 ? 'c-amber' : ''" />
        <x-ds.kpi-card icon="bi-cash-stack" label="Monthly Payroll" value="₹{{ number_format($totalMonthlyPayroll, 0) }}" accent="gold" value-color="c-gold" note="currently configured" />
    </div>
</div>

{{-- ═══ SEARCH + FILTERS ══════════════════════════════════════════════ --}}
<div x-data="{
        search: @js($search),
        role:   @js($role),
        status: @js($salaryStatus),
        open:   false,
        base:   '{{ route('admin.salaries.index') }}',
        get activeCount() { return [this.role, this.status].filter(Boolean).length; },
        navigate() {
            const p = new URLSearchParams();
            if (this.search.trim()) p.set('search', this.search.trim());
            if (this.role)          p.set('role', this.role);
            if (this.status)        p.set('salary_status', this.status);
            window.location.href = this.base + (p.toString() ? '?' + p.toString() : '');
        },
        setRole(v)   { this.role = v;   this.navigate(); },
        setStatus(v) { this.status = v; this.navigate(); },
        reset()      { this.search = ''; this.role = ''; this.status = ''; this.navigate(); },
    }">

    <div class="ef-emp-toolbar-row">
        <div class="ef-emp-search-wrap">
            <i class="bi bi-search ef-emp-search-icon"></i>
            <input type="text" class="ef-input" placeholder="Search employee name or email…"
                   x-model="search" @keydown.enter="navigate()">
        </div>
        <button type="button" class="ef-btn ef-emp-filter-toggle" @click="open = !open" :aria-expanded="open.toString()" aria-controls="salFilterPanel">
            <i class="bi bi-funnel"></i> Filters
            <template x-if="activeCount > 0">
                <span class="ef-emp-filter-badge" x-text="activeCount"></span>
            </template>
        </button>
        <button type="button" class="ef-btn ef-btn-dark" @click="navigate()">
            <i class="bi bi-search"></i> <span class="d-none d-sm-inline">Search</span>
        </button>
    </div>

    <div class="ef-emp-filter-panel" id="salFilterPanel" x-show="open" x-cloak x-transition>
        <div class="ef-emp-filter-section">
            <div class="ef-emp-filter-label">Salary Status</div>
            <div class="ef-emp-chips">
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': status === '' }" @click="setStatus('')">All</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': status === 'set' }" @click="setStatus('set')">Salary Set</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': status === 'not_set' }" @click="setStatus('not_set')">Salary Not Set</button>
            </div>
        </div>
        <div class="ef-emp-filter-section">
            <div class="ef-emp-filter-label">Role</div>
            <div class="ef-emp-chips">
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': role === '' }" @click="setRole('')">All</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': role === 'employee' }" @click="setRole('employee')">Employee</button>
                <button type="button" class="ef-emp-chip-btn" :class="{ '--active': role === 'manager' }" @click="setRole('manager')">Manager</button>
            </div>
        </div>
        <div class="ef-emp-filter-footer">
            <button type="button" class="ef-btn" @click="reset()">
                <i class="bi bi-x-circle"></i> Reset
            </button>
        </div>
    </div>
</div>

{{-- ═══ EMPLOYEE SALARY LIST ══════════════════════════════════════════ --}}
<x-ds.card :no-pad="true">
    <div class="ef-emp-list-head">
        <span class="ef-emp-list-title">Employees</span>
        <span class="ef-emp-list-count">{{ $employees->total() }} member{{ $employees->total() != 1 ? 's' : '' }}</span>
    </div>

    @forelse($employees as $employee)
        @php $currentSalary = $currentSalaries[$employee->id] ?? null; @endphp
        <a href="{{ route('admin.employees.salaries.index', $employee) }}" class="sal-row">
            <div class="sal-row-top">
                <div>
                    <div class="sal-row-name">{{ $employee->name }}</div>
                    <div class="sal-row-role">{{ $employee->role }}</div>
                </div>
                <div class="sal-row-chips">
                    <x-premium.chip :tone="$employee->role === 'manager' ? 'gold' : 'neutral'">{{ ucfirst($employee->role) }}</x-premium.chip>
                    <x-premium.chip :tone="$employee->is_active ? 'emerald' : 'neutral'">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </x-premium.chip>
                </div>
            </div>

            <div class="sal-row-bottom">
                @if($currentSalary)
                    <div class="sal-amount-block">
                        <div class="sal-amount">₹{{ number_format((float) $currentSalary->monthly_salary, 0) }} <span style="font-size:.75rem;font-weight:600;color:var(--ef-muted)">/ month</span></div>
                        <div class="sal-amount-sub">Effective from {{ $currentSalary->effective_from->format('d M Y') }}</div>
                    </div>
                    <span class="ef-btn ef-btn-dark sal-action-btn">
                        <i class="bi bi-pencil"></i> Change Salary
                    </span>
                @else
                    <div class="sal-unconfigured">
                        <i class="bi bi-exclamation-circle"></i> Salary not configured
                    </div>
                    <span class="ef-btn ef-btn-dark sal-action-btn">
                        <i class="bi bi-plus-lg"></i> Set Salary
                    </span>
                @endif
            </div>
        </a>
    @empty
        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-people"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">No employees found</h3>
            <p style="color:var(--ef-muted);font-size:.86rem;margin:0 0 20px;max-width:300px;line-height:1.6">
                Try changing your search or filters.
            </p>
            <a href="{{ route('admin.salaries.index') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-x-circle"></i> Clear Filters
            </a>
        </div>
    @endforelse
</x-ds.card>

@if($employees->hasPages())
    <div class="ef-emp-pagination">{{ $employees->links() }}</div>
@endif

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

</x-admin-layout>
