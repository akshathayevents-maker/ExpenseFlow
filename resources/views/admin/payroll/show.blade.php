<x-admin-layout title="Payroll — {{ $employee->name }}">

@push('styles')
<style>
.pr-ot-list { display: flex; flex-direction: column; gap: 10px; }
.pr-ot-card { background: #fff; border: 1px solid var(--ef-border, #e5e7eb); border-left: 4px solid var(--ef-border-strong, #cbd5e1); border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
.pr-ot-card.--approved { border-left-color: #0F7B5F; }
.pr-ot-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap; }
.pr-ot-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; }
.pr-ot-amounts { display: flex; gap: 18px; flex-wrap: wrap; font-size: .84rem; }
.pr-ot-amounts .lbl { color: var(--ef-muted); display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; }
.pr-ot-amounts .val { font-weight: 700; }
.pr-ot-amounts .val.--approved { color: #0F7B5F; font-weight: 800; }
.pr-summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--ef-border); font-size: .92rem; }
.pr-summary-row:last-child { border-bottom: none; }
.pr-summary-row.--net { font-weight: 800; font-size: 1.1rem; border-top: 2px solid var(--ef-border-strong); margin-top: 4px; padding-top: 14px; }
</style>
@endpush

<x-ds.hero eyebrow="Compensation / Payroll" :title="$employee->name"
    :meta="[['icon' => 'bi-calendar-month', 'text' => $month->format('F Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.payroll.index', ['month' => $month->format('Y-m')]) }}" class="ef-ds-btn">
            <i class="bi bi-arrow-left"></i> <span>Back to Payroll</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

<div class="row g-4">
    <div class="col-lg-5">
        <x-ds.card title="Monthly Payable Breakdown">
            @if($breakdown)
                <div class="pr-summary-row"><span>Monthly Salary</span><span>₹{{ number_format($breakdown['monthly_salary'], 2) }}</span></div>
                <div class="pr-summary-row"><span>Payable Days</span><span>{{ $breakdown['payable_days'] }} / {{ $breakdown['applicable_working_days'] }}</span></div>
                <div class="pr-summary-row"><span>Payable Salary (after LOP)</span><span>₹{{ number_format($breakdown['payable_salary'], 2) }}</span></div>
                <div class="pr-summary-row"><span>Approved Overtime</span><span>₹{{ number_format($breakdown['approved_overtime_amount'], 2) }}</span></div>
                <div class="pr-summary-row">
                    <span>Advance Deduction <span style="font-size:.72rem;color:var(--ef-faint)">(not applicable — no payroll-deduction mechanism configured)</span></span>
                    <span>− ₹{{ number_format($breakdown['advance_deduction_amount'], 2) }}</span>
                </div>
                <div class="pr-summary-row --net"><span>Net Payable</span><span>₹{{ number_format($breakdown['net_payable'], 2) }}</span></div>

                <div class="pr-summary-row" style="border-top:1px dashed var(--ef-border);margin-top:6px;padding-top:10px;">
                    <span>Advance Outstanding <span style="font-size:.72rem;color:var(--ef-faint)">(informational — not deducted)</span></span>
                    <span>₹{{ number_format($breakdown['advance_outstanding_balance'], 2) }}</span>
                </div>

                @if($breakdown['advance_outstanding_balance'] > 0)
                    <p style="margin-top:10px;font-size:.78rem;color:var(--ef-faint)">
                        Repayment Schedule: Not configured. Advance repayments are recorded as a
                        manual ledger entry independent of payroll (cash, bank transfer, or other
                        means) and are never automatically deducted here; the Advance Outstanding
                        figure is the employee's total remaining balance across all time and is not
                        subtracted from Net Payable.
                    </p>
                @endif
            @else
                <div class="pr-unavailable" style="color:#7D5218;font-weight:650">
                    <i class="bi bi-exclamation-circle"></i> {{ $unavailableReason }}
                </div>
            @endif
        </x-ds.card>
    </div>

    <div class="col-lg-7">
        <x-ds.card title="Overtime Records — {{ $month->format('F Y') }}">
            <div class="pr-ot-list">
                @forelse($overtimeRecords as $record)
                    <div class="pr-ot-card {{ $record->request_status === 'approved' ? '--approved' : '' }}">
                        <div class="pr-ot-top">
                            <div class="pr-ot-meta">{{ $record->ot_date->format('d M Y') }} · {{ $record->hours }}h · <span style="text-transform:capitalize">{{ $record->category }}</span></div>
                            <x-overtime-status-badge :status="$record->request_status" />
                        </div>
                        <div class="pr-ot-amounts">
                            <div>
                                <span class="lbl">Calculated Amount</span>
                                <span class="val">{{ $record->calculated_amount !== null ? '₹' . number_format((float) $record->calculated_amount, 2) : '—' }}</span>
                            </div>
                            <div>
                                <span class="lbl">Approved Amount (payable)</span>
                                <span class="val --approved">{{ $record->approved_amount !== null ? '₹' . number_format((float) $record->approved_amount, 2) : '—' }}</span>
                            </div>
                            @if($record->used_manual_override)
                                <span class="sal-badge" style="background:rgba(216,154,61,.15);color:#7D5218;font-size:.68rem;align-self:center">Manual Override</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="ef-empty-state">
                        <div class="ef-empty-orb"><i class="bi bi-clock-history"></i></div>
                        <p style="color:var(--ef-muted);font-size:.86rem;margin:0">No overtime records for this month.</p>
                    </div>
                @endforelse
            </div>
        </x-ds.card>
    </div>
</div>

</x-admin-layout>
