<x-admin-layout title="Salary — {{ $employee->name }}">

@php
    $isActive = (bool) $employee->is_active;
@endphp

@push('styles')
<style>
    .sal-wrap { margin: 0 auto; max-width: 1040px; padding: 0 16px; }

    .sal-header {
        align-items: flex-start; display: flex; flex-wrap: wrap; gap: 12px;
        padding: 20px 0 4px;
    }
    .sal-header-main { display: flex; gap: 12px; flex: 1 1 220px; min-width: 0; }
    .sal-title { font-size: 1.25rem; font-weight: 760; letter-spacing: -.02em; line-height: 1.2; margin: 0; overflow-wrap: anywhere; }
    .sal-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; margin: 2px 0 0; overflow-wrap: anywhere; }
    .sal-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .sal-grid { display: flex; flex-direction: column; gap: 14px; margin-top: 14px; }
    @media (min-width: 992px) {
        .sal-grid { flex-direction: row; align-items: flex-start; }
        .sal-grid-main { flex: 1 1 42%; min-width: 0; }
        .sal-grid-side { flex: 1 1 58%; min-width: 0; }
    }
    .sal-grid-main, .sal-grid-side { display: flex; flex-direction: column; gap: 14px; }

    .sal-amount-hero { padding: 2px 0 14px; }
    .sal-amount-lbl { color: var(--ef-faint, #6b7280); font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .sal-amount-val { font-size: 1.9rem; font-weight: 800; letter-spacing: -.02em; margin-top: 2px; }
    .sal-kv-list { display: flex; flex-direction: column; }
    .sal-kv-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-top: 1px solid var(--ef-border, #e5e7eb); }
    .sal-kv-row:first-child { border-top: none; }
    .sal-kv-lbl { color: var(--ef-faint, #6b7280); font-size: .78rem; font-weight: 600; }
    .sal-kv-val { font-weight: 650; text-align: right; overflow-wrap: anywhere; }

    .sal-empty { align-items: center; display: flex; flex-direction: column; gap: 6px; padding: 22px 12px; text-align: center; }
    .sal-empty i { color: var(--ef-faint, #6b7280); font-size: 1.4rem; }
    .sal-empty-title { font-weight: 650; }
    .sal-empty-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; max-width: 320px; }

    .sal-badge {
        display: inline-flex; align-items: center; border-radius: 6px; font-size: .74rem;
        font-weight: 700; padding: 4px 12px; white-space: nowrap;
    }

    /* Salary history — cards on mobile, table from 576px up */
    .sal-hist-cards { display: flex; flex-direction: column; gap: 8px; }
    .sal-hist-card { border: 1px solid var(--ef-border, #e5e7eb); border-radius: 8px; padding: 10px 12px; }
    .sal-hist-amount { font-weight: 700; font-size: 1.02rem; }
    .sal-hist-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; margin-top: 8px; }
    .sal-hist-item-lbl { color: var(--ef-faint, #6b7280); font-size: .7rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }
    .sal-hist-item-val { font-size: .84rem; font-weight: 600; }
    .sal-hist-table-wrap { display: none; }
    @media (min-width: 576px) {
        .sal-hist-cards { display: none; }
        .sal-hist-table-wrap { display: block; overflow-x: auto; }
    }

    .sal-form-actions { margin-top: 4px; }
</style>
@endpush

<div class="sal-wrap">
    <div class="sal-header">
        <div class="sal-header-main">
            <a href="{{ route('admin.employees.show', $employee) }}" class="ef-back" title="Back to {{ $employee->name }}" aria-label="Back to {{ $employee->name }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
            <div style="min-width:0">
                <div class="sal-sub" style="margin-bottom:2px">Compensation</div>
                <h1 class="sal-title">{{ $employee->name }}</h1>
                <p class="sal-sub">{{ $employee->email }} &middot; Employee ID #{{ $employee->id }}</p>
            </div>
        </div>
        <div class="sal-header-actions">
            <a href="{{ route('admin.employees.show', $employee) }}" class="ef-btn">
                <i class="bi bi-person"></i> Back to Employee
            </a>
            <a href="{{ route('admin.salaries.index') }}" class="ef-btn">
                <i class="bi bi-cash-coin"></i> All Employee Salaries
            </a>
        </div>
    </div>

    <div class="sal-grid">
        <div class="sal-grid-main">
            <x-ds.card title="Compensation">
                @if($currentSalary)
                    <div class="sal-amount-hero">
                        <div class="sal-amount-lbl">Current Monthly Salary</div>
                        <div class="sal-amount-val">₹{{ number_format((float) $currentSalary->monthly_salary, 2) }}</div>
                    </div>

                    <div class="sal-kv-list" style="border-top:1px solid var(--ef-border,#e5e7eb)">
                        <div class="sal-kv-row">
                            <div class="sal-kv-lbl">Effective From</div>
                            <div class="sal-kv-val">{{ $currentSalary->effective_from->format('d M Y') }}</div>
                        </div>
                        <div class="sal-kv-row">
                            <div class="sal-kv-lbl">Status</div>
                            <div class="sal-kv-val">
                                <span class="sal-badge" style="background:{{ $isActive ? 'rgba(15,123,95,.11)' : 'rgba(100,116,139,.11)' }};color:{{ $isActive ? '#0A5240' : '#334155' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="sal-empty">
                        <i class="bi bi-cash-stack" aria-hidden="true"></i>
                        <div class="sal-empty-title">No salary configured</div>
                        <div class="sal-empty-sub">Set the employee's first salary to enable payable calculations.</div>
                    </div>
                @endif
            </x-ds.card>

            @unless($isActive)
            <div class="sal-empty" style="border:1px solid rgba(216,154,61,.35);border-radius:10px;padding:14px">
                <i class="bi bi-exclamation-triangle" style="color:#7D5218" aria-hidden="true"></i>
                <div class="sal-empty-title">This employee is inactive</div>
                <div class="sal-empty-sub">Salary cannot be changed for an inactive employee.</div>
            </div>
            @else
            <x-ds.card title="{{ $currentSalary ? 'Change Salary' : 'Set Salary' }}">
                <form method="POST" action="{{ route('admin.employees.salaries.store', $employee) }}">
                    @csrf

                    <div class="ef-form-grid ef-form-grid-1">
                        <div>
                            <label class="ef-label" for="monthly_salary">Monthly Salary <span style="color:var(--ef-danger)">*</span></label>
                            <input type="number" step="0.01" min="0.01" id="monthly_salary" name="monthly_salary"
                                   class="ef-input @error('monthly_salary') --error @enderror"
                                   value="{{ old('monthly_salary') }}" required>
                            @error('monthly_salary') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ef-label" for="effective_from">Effective From <span style="color:var(--ef-danger)">*</span></label>
                            <input type="date" id="effective_from" name="effective_from"
                                   class="ef-input @error('effective_from') --error @enderror"
                                   value="{{ old('effective_from', now()->toDateString()) }}" required>
                            @error('effective_from') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="sal-form-actions">
                        <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                            <i class="bi bi-check-lg"></i> {{ $currentSalary ? 'Save New Salary' : 'Set Salary' }}
                        </button>
                    </div>
                </form>
            </x-ds.card>
            @endunless
        </div>

        <div class="sal-grid-side">
            <x-ds.card title="Salary History" :no-pad="true">
                @if($history->isEmpty())
                    <div class="sal-empty">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                        <div class="sal-empty-title">No salary history yet</div>
                        <div class="sal-empty-sub">Every salary change will be recorded here, with nothing overwritten.</div>
                    </div>
                @else
                    {{-- Mobile: stacked cards --}}
                    <div class="sal-hist-cards" style="padding:16px">
                        @foreach($history as $salary)
                        <div class="sal-hist-card">
                            <div class="sal-hist-amount">₹{{ number_format((float) $salary->monthly_salary, 2) }} / month</div>
                            <div class="sal-hist-grid">
                                <div>
                                    <div class="sal-hist-item-lbl">Effective</div>
                                    <div class="sal-hist-item-val">{{ $salary->effective_from->format('d M Y') }}</div>
                                </div>
                                <div>
                                    <div class="sal-hist-item-lbl">Until</div>
                                    <div class="sal-hist-item-val">{{ optional($salary->effective_to)->format('d M Y') ?? 'Current' }}</div>
                                </div>
                                <div>
                                    <div class="sal-hist-item-lbl">Created By</div>
                                    <div class="sal-hist-item-val">{{ $salary->creator->name ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="sal-hist-item-lbl">Created</div>
                                    <div class="sal-hist-item-val">{{ $salary->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop/tablet: compact table --}}
                    <div class="sal-hist-table-wrap">
                        <table class="ef-an-trend-table">
                            <thead>
                                <tr>
                                    <th>Monthly Salary</th>
                                    <th>Effective From</th>
                                    <th>Effective To</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $salary)
                                <tr>
                                    <td>₹{{ number_format((float) $salary->monthly_salary, 2) }}</td>
                                    <td style="white-space:nowrap">{{ $salary->effective_from->format('d M Y') }}</td>
                                    <td style="white-space:nowrap">{{ optional($salary->effective_to)->format('d M Y') ?? 'Current' }}</td>
                                    <td>{{ $salary->creator->name ?? '—' }}</td>
                                    <td style="white-space:nowrap">{{ $salary->created_at->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ds.card>
        </div>
    </div>
</div>

</x-admin-layout>
