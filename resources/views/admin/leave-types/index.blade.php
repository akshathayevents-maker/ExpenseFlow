<x-admin-layout title="Leave Types">

<x-ds.hero eyebrow="Leave Management" title="Leave Types"
    :meta="[['icon' => 'bi-calendar-minus', 'text' => 'Configure the leave types available to employees']]">
    <x-slot:actions>
        <a href="{{ route('admin.leave-types.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>New Leave Type</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

@push('styles')
<style>
    .lt-list { display: flex; flex-direction: column; gap: 10px; }
    .lt-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 14px; border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px;
        text-decoration: none; color: inherit;
    }
    .lt-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .lt-row-title { font-weight: 700; font-size: 1.0rem; }
    .lt-row-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    .lt-flag { display: inline-flex; align-items: center; gap: 4px; font-size: .74rem; color: var(--ef-muted, #475569); }
    @media (min-width: 576px) {
        .lt-row { flex-direction: row; align-items: center; }
        .lt-row-top { flex: 1; }
    }
</style>
@endpush

<x-ds.card title="All Leave Types">
    <div class="lt-list">
        @forelse($leaveTypes as $leaveType)
            <a href="{{ route('admin.leave-types.edit', $leaveType) }}" class="lt-row">
                <div class="lt-row-top">
                    <div>
                        <div class="lt-row-title">{{ $leaveType->name }} <span style="color:var(--ef-faint,#6b7280);font-weight:500">({{ $leaveType->code }})</span></div>
                        <div class="lt-row-meta">
                            <span class="lt-flag"><i class="bi {{ $leaveType->is_paid ? 'bi-cash-coin' : 'bi-dash-circle' }}"></i> {{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}</span>
                            &middot;
                            <span class="lt-flag"><i class="bi {{ $leaveType->allow_half_day ? 'bi-check-circle' : 'bi-x-circle' }}"></i> Half-day {{ $leaveType->allow_half_day ? 'allowed' : 'not allowed' }}</span>
                            &middot;
                            <span class="lt-flag"><i class="bi {{ $leaveType->allow_carry_forward ? 'bi-check-circle' : 'bi-x-circle' }}"></i> Carry-forward {{ $leaveType->allow_carry_forward ? ('up to ' . rtrim(rtrim(number_format((float) $leaveType->max_carry_forward, 1), '0'), '.') . ' day(s)') : 'not allowed' }}</span>
                        </div>
                    </div>
                    <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.72rem;font-weight:700;padding:3px 10px;background:{{ $leaveType->is_active ? 'rgba(15,123,95,.11)' : 'rgba(100,116,139,.11)' }};color:{{ $leaveType->is_active ? '#0A5240' : '#334155' }}">
                        {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:40px 16px;color:var(--ef-faint,#6b7280)">
                <i class="bi bi-calendar-minus" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No leave types configured yet.
                <div style="margin-top:12px">
                    <a href="{{ route('admin.leave-types.create') }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-lg"></i> New Leave Type
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-ds.card>

</x-admin-layout>
