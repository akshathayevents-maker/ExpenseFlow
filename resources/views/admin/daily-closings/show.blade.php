<x-admin-layout title="Daily Closing — {{ $dailyClosing->date->format('d M Y') }}">

@push('styles')
<style>
.ef-dc-show-split { display: grid; gap: 14px; grid-template-columns: 1fr 300px; align-items: start; }
.ef-dc-meta-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--ef-border); font-size: .86rem; }
.ef-dc-meta-row:last-child { border-bottom: none; }
.ef-dc-meta-label { color: var(--ef-faint); font-weight: 500; }
.ef-dc-meta-val { color: var(--ef-ink-2); font-weight: 600; text-align: right; }
.ef-dc-drift-banner {
    align-items: flex-start;
    background: rgba(255,200,0,.06);
    border: 1px solid rgba(255,200,0,.28);
    border-radius: var(--ef-radius);
    color: #7d6400;
    display: flex;
    font-size: .84rem;
    gap: 10px;
    margin-bottom: 14px;
    padding: 12px 16px;
}
.ef-dc-notes-banner {
    align-items: center;
    background: var(--ef-bg-subtle);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    display: flex;
    font-size: .88rem;
    gap: 10px;
    margin-bottom: 14px;
    padding: 12px 16px;
    color: var(--ef-ink-2);
}
@media (max-width: 991.98px) { .ef-dc-show-split { grid-template-columns: 1fr; } }
</style>
@endpush

@php $colors = \App\Models\DailyClosing::statusColors(); $color = $colors[$dailyClosing->status] ?? 'secondary'; @endphp

<x-ds.hero eyebrow="Daily Closings" title="{{ $dailyClosing->date->format('d M Y') }}"
    :meta="[['icon' => 'bi-calendar3', 'text' => 'Daily Closing Report']]">
    <x-slot:actions>
        <a href="{{ route('admin.daily-closings.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        @if($dailyClosing->isDraft())
        <form method="POST" action="{{ route('admin.daily-closings.verify', $dailyClosing) }}" style="display:inline">
            @csrf @method('PATCH')
            <button type="submit" class="ef-btn ef-btn-dark" data-loading-text="Verifying…">
                <i class="bi bi-check-circle"></i> Verify Closing
            </button>
        </form>
        @endif

        @if($dailyClosing->canEdit())
        <a href="{{ route('admin.daily-closings.edit', $dailyClosing) }}" class="ef-btn">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form method="POST" action="{{ route('admin.daily-closings.recalculate', $dailyClosing) }}" style="display:inline">
            @csrf @method('PATCH')
            <button type="submit" class="ef-btn" data-loading-text="Recalculating…">
                <i class="bi bi-arrow-repeat"></i> Recalculate
            </button>
        </form>
        @if($dailyClosing->canDelete())
        <button type="button" class="ef-btn" style="color:var(--ef-danger)"
                data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="bi bi-trash"></i>
        </button>
        @endif
        @endif
    </x-slot:actions>
</x-ds.hero>

{{-- Drift alert --}}
@if($hasDrift)
<div class="ef-dc-drift-banner">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>Data drift detected.</strong>
        The stored figures differ from current live data — expense records or payments may have been modified after this closing was created.
        <form method="POST" action="{{ route('admin.daily-closings.recalculate', $dailyClosing) }}" style="display:inline;margin-left:8px">
            @csrf @method('PATCH')
            <button type="submit" class="ef-btn ef-btn-dark" style="padding:3px 12px;font-size:.8rem" data-loading-text="Recalculating…">
                <i class="bi bi-arrow-repeat"></i> Recalculate Now
            </button>
        </form>
    </div>
</div>
@endif

@if($dailyClosing->notes)
<div class="ef-dc-notes-banner">
    <i class="bi bi-sticky flex-shrink-0"></i>
    {{ $dailyClosing->notes }}
</div>
@endif

{{-- Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px">
    <x-ds.kpi-card icon="bi-receipt" label="Expense Total"
        value="₹{{ number_format($dailyClosing->expense_total, 2) }}"
        :note="$dailyClosing->expense_count . ' requests' . (abs($liveFigures['expense_total'] - (float)$dailyClosing->expense_total) > 0.005 ? ' · Live: ₹' . number_format($liveFigures['expense_total'], 2) : '')"
        accent="emerald" value-color="c-emerald" />
    <x-ds.kpi-card icon="bi-credit-card" label="Payments Made"
        value="₹{{ number_format($dailyClosing->payment_total, 2) }}"
        :note="abs($liveFigures['payment_total'] - (float)$dailyClosing->payment_total) > 0.005 ? 'Live: ₹' . number_format($liveFigures['payment_total'], 2) : null"
        accent="gold" value-color="c-gold" />
    <x-ds.kpi-card icon="bi-box-arrow-in-down" label="Stock Added"
        value="{{ number_format($dailyClosing->stock_additions, 3) + 0 }}"
        accent="teal" />
    <x-ds.kpi-card icon="bi-box-arrow-up" label="Stock Deducted"
        value="{{ number_format($dailyClosing->stock_deductions, 3) + 0 }}"
        accent="amber" value-color="c-amber" />
</div>

<div class="ef-dc-show-split">
    <div>
        {{-- Expenses --}}
        <x-ds.card :no-pad="true" style="margin-bottom:14px">
            <x-slot:head_right>
                <x-ds.section-head title="Expenses" :count="$expenses->count()" />
            </x-slot:head_right>
            @if($expenses->isEmpty())
                <div style="text-align:center;padding:24px;color:var(--ef-faint);font-size:.84rem">No expenses for this date.</div>
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
                        @foreach($expenses as $exp)
                        <tr>
                            <td class="fw">{{ $exp->requester->name }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $exp->category->name }}</td>
                            <td class="r fw">₹{{ number_format($exp->amount, 2) }}</td>
                            <td style="text-align:center"><x-status-badge :status="$exp->status" /></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="fw" style="background:var(--ef-bg-subtle)">Total</td>
                            <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($expenses->sum('amount'), 2) }}</td>
                            <td style="background:var(--ef-bg-subtle)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </x-ds.card>

        {{-- Payments --}}
        <x-ds.card :no-pad="true">
            <x-slot:head_right>
                <x-ds.section-head title="Payments" :count="$payments->count()" />
            </x-slot:head_right>
            @if($payments->isEmpty())
                <div style="text-align:center;padding:24px;color:var(--ef-faint);font-size:.84rem">No payments for this date.</div>
            @else
            <div style="overflow-x:auto">
                <table class="ef-an-trend-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Mode</th>
                            <th>Reference</th>
                            <th class="r">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td class="fw">{{ $payment->expenseRequest?->requester?->name ?? '—' }}</td>
                            <td style="color:var(--ef-faint)">{{ $payment->payment_mode }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $payment->transaction_reference ?? '—' }}</td>
                            <td class="r fw">₹{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="fw" style="background:var(--ef-bg-subtle)">Total</td>
                            <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($payments->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </x-ds.card>
    </div>

    {{-- Closing details sidebar --}}
    <x-ds.card title="Closing Details">
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Date</span>
            <span class="ef-dc-meta-val">{{ $dailyClosing->date->format('d M Y') }}</span>
        </div>
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Status</span>
            <span class="ef-dc-meta-val">
                <x-status-badge :status="$dailyClosing->status" />
                @if($hasDrift)
                    <span style="font-size:.7rem;color:var(--ef-amber);display:block;margin-top:3px">
                        <i class="bi bi-exclamation-triangle"></i> Drift
                    </span>
                @endif
            </span>
        </div>
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Recorded by</span>
            <span class="ef-dc-meta-val">{{ $dailyClosing->creator->name }}</span>
        </div>
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Recorded at</span>
            <span class="ef-dc-meta-val" style="font-size:.8rem">{{ $dailyClosing->created_at->format('d M Y, h:i A') }}</span>
        </div>
        @if($dailyClosing->updater)
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Last edited by</span>
            <span class="ef-dc-meta-val" style="color:var(--ef-amber)">{{ $dailyClosing->updater->name }}</span>
        </div>
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Edited at</span>
            <span class="ef-dc-meta-val" style="font-size:.8rem">{{ $dailyClosing->updated_at->format('d M Y, h:i A') }}</span>
        </div>
        @endif
        @if($dailyClosing->verifier)
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Verified by</span>
            <span class="ef-dc-meta-val" style="color:var(--ef-emerald)">{{ $dailyClosing->verifier->name }}</span>
        </div>
        <div class="ef-dc-meta-row">
            <span class="ef-dc-meta-label">Verified at</span>
            <span class="ef-dc-meta-val" style="font-size:.8rem">{{ $dailyClosing->verified_at->format('d M Y, h:i A') }}</span>
        </div>
        @endif
    </x-ds.card>
</div>

{{-- Delete Modal --}}
@if($dailyClosing->canDelete())
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.18)">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title" style="color:var(--ef-danger)"><i class="bi bi-trash me-2"></i>Delete Closing</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.88rem">
                <div style="background:rgba(220,53,69,.06);border:1px solid rgba(220,53,69,.2);border-radius:10px;padding:8px 12px;margin-bottom:10px;color:var(--ef-danger);font-size:.8rem">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Cannot be undone.
                </div>
                Delete closing for <strong>{{ $dailyClosing->date->format('d M Y') }}</strong>?
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.daily-closings.destroy', $dailyClosing) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn ef-btn-dark" style="background:var(--ef-danger);border-color:var(--ef-danger)" data-loading-text="Deleting…">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

</x-admin-layout>
