<x-admin-layout title="{{ $date->isToday() ? 'Close Today' : 'Close Past Date' }}">

@push('styles')
<style>
.ef-dc-grid { display: grid; gap: 14px; grid-template-columns: 1fr 1fr; }
.ef-dc-split { display: grid; gap: 14px; grid-template-columns: 1fr 340px; align-items: start; }
.ef-dc-warn {
    align-items: flex-start;
    background: rgba(255,200,0,.06);
    border: 1px solid rgba(255,200,0,.28);
    border-radius: var(--ef-radius);
    color: #7d6400;
    display: flex;
    font-size: .82rem;
    gap: 8px;
    padding: 10px 14px;
}
.ef-dc-date-chip {
    background: var(--ef-bg-subtle);
    border: 1px solid var(--ef-border);
    border-radius: 10px;
    color: var(--ef-ink-2);
    font-size: .9rem;
    padding: 10px 13px;
}
@media (max-width: 767.98px) {
    .ef-dc-grid { grid-template-columns: 1fr 1fr; }
    .ef-dc-split { grid-template-columns: 1fr; }
}
@media (max-width: 479.98px) {
    .ef-dc-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

<x-ds.hero
    eyebrow="Daily Closings"
    :title="$date->isToday() ? 'Close Today — ' . $date->format('d M Y') : 'Past Date Closing — ' . $date->format('d M Y')">
</x-ds.hero>

@if(!$date->isToday())
<div class="ef-dc-warn" style="margin-bottom:14px">
    <i class="bi bi-exclamation-triangle flex-shrink-0"></i>
    <span>You are creating a closing for a past date. Figures are calculated from data recorded on that date.</span>
</div>
@endif

{{-- Summary KPIs --}}
<div class="ef-dc-grid" style="margin-bottom:16px">
    <x-ds.kpi-card icon="bi-receipt" label="Expense Total" value="₹{{ number_format($expenseTotal ?? 0, 2) }}" accent="emerald" value-color="c-emerald" />
    <x-ds.kpi-card icon="bi-credit-card" label="Payments Made" value="₹{{ number_format($paymentTotal ?? 0, 2) }}" accent="gold" value-color="c-gold" />
    <x-ds.kpi-card icon="bi-box-arrow-in-down" label="Stock Added" value="{{ number_format($stockAdditions ?? 0, 3) + 0 }}" accent="teal" />
    <x-ds.kpi-card icon="bi-box-arrow-up" label="Stock Deducted" value="{{ number_format($stockDeductions ?? 0, 3) + 0 }}" accent="amber" value-color="c-amber" />
</div>

<div class="ef-dc-split">
    {{-- Expenses table --}}
    <x-ds.card :no-pad="true">
        <x-slot:head_right>
            <x-ds.section-head title="Expenses for {{ $date->format('d M Y') }}" count="{{ $expenseCount ?? 0 }}" />
        </x-slot:head_right>

        @if($recentExpenses->isEmpty())
            <div style="text-align:center;padding:24px;color:var(--ef-faint);font-size:.84rem">No expenses on this date.</div>
        @else
        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Category</th>
                        <th class="r">Amount</th>
                        <th style="text-align:center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentExpenses as $exp)
                    <tr>
                        <td class="fw">{{ $exp->requester->name }}</td>
                        <td style="color:var(--ef-faint);font-size:.84rem">{{ $exp->category->name }}</td>
                        <td class="r fw">₹{{ number_format($exp->amount, 2) }}</td>
                        <td style="text-align:center"><x-status-badge :status="$exp->status" /></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-ds.card>

    {{-- Record closing form --}}
    <x-ds.card title="Record Closing">
        <form method="POST" action="{{ route('admin.daily-closings.store') }}" novalidate>
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div style="margin-bottom:14px">
                <label class="ef-label">Closing Date</label>
                <div class="ef-dc-date-chip">{{ $date->format('l, d M Y') }}</div>
            </div>

            <div style="margin-bottom:16px">
                <label class="ef-label" for="notes">Notes <span style="color:var(--ef-faint);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
                <textarea name="notes" id="notes" class="ef-textarea @error('notes') --error @enderror"
                          rows="3"
                          placeholder="Any remarks for this closing…">{{ old('notes') }}</textarea>
                @error('notes') <div class="ef-field-error">{{ $message }}</div> @enderror
            </div>

            <div class="ef-dc-warn" style="margin-bottom:16px">
                <i class="bi bi-info-circle flex-shrink-0"></i>
                <span>Figures are auto-calculated from live data. Closing locks a snapshot.</span>
            </div>

            <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                <i class="bi bi-lock"></i> Confirm Daily Closing
            </button>
            <a href="{{ route('admin.daily-closings.index') }}" class="ef-btn" style="width:100%;justify-content:center;margin-top:8px">
                Cancel
            </a>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
