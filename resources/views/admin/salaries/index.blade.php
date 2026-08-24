<x-admin-layout title="Employee Salaries">

<x-ds.hero eyebrow="Compensation / Payroll" title="Employee Salaries"
    :meta="[['icon' => 'bi-cash-coin', 'text' => 'Set and review employee monthly salaries']]">
</x-ds.hero>

@push('styles')
<style>
    .sal-list { display: flex; flex-direction: column; gap: 10px; }
    .sal-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 14px; border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px;
        text-decoration: none; color: inherit;
    }
    .sal-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .sal-row-name { font-weight: 700; font-size: 1.0rem; }
    .sal-row-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    .sal-row-amount { font-weight: 700; }
    .sal-badge {
        display: inline-flex; align-items: center; border-radius: 6px; font-size: .72rem;
        font-weight: 700; padding: 3px 10px; white-space: nowrap;
    }
    .sal-search { max-width: 360px; }
    @media (min-width: 576px) {
        .sal-row { flex-direction: row; align-items: center; }
        .sal-row-top { flex: 1; }
    }
</style>
@endpush

<x-ds.card title="Employees">
    <x-slot:head_right>
        <form method="GET" action="{{ route('admin.salaries.index') }}" class="sal-search">
            <input type="text" name="search" class="ef-input" placeholder="Search name or email…" value="{{ $search }}">
        </form>
    </x-slot:head_right>

    <div class="sal-list">
        @forelse($employees as $employee)
            @php $currentSalary = $currentSalaries[$employee->id] ?? null; @endphp
            <a href="{{ route('admin.employees.salaries.index', $employee) }}" class="sal-row">
                <div class="sal-row-top">
                    <div>
                        <div class="sal-row-name">{{ $employee->name }}</div>
                        <div class="sal-row-meta">
                            Employee ID #{{ $employee->id }}
                            &middot; <span style="text-transform:capitalize">{{ $employee->role }}</span>
                            &middot;
                            @if($employee->is_active)
                                <span class="sal-badge" style="background:rgba(15,123,95,.11);color:#0A5240">Active</span>
                            @else
                                <span class="sal-badge" style="background:rgba(100,116,139,.11);color:#334155">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right">
                        @if($currentSalary)
                            <div class="sal-row-amount">₹{{ number_format((float) $currentSalary->monthly_salary, 2) }}</div>
                            <div class="sal-row-meta">Effective {{ $currentSalary->effective_from->format('d M Y') }}</div>
                        @else
                            <span class="sal-badge" style="background:rgba(216,154,61,.13);color:#7D5218">No salary set</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:40px 16px;color:var(--ef-faint,#6b7280)">
                <i class="bi bi-people" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No employees found.
            </div>
        @endforelse
    </div>

    @if($employees->hasPages())
    <div class="mt-3">
        {{ $employees->links() }}
    </div>
    @endif
</x-ds.card>

</x-admin-layout>
