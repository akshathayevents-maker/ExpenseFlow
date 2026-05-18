<x-admin-layout title="Edit Closing — {{ $dailyClosing->date->format('d M Y') }}">

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-top:8px">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('admin.daily-closings.show', $dailyClosing) }}" class="ef-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 style="font-size:1.25rem;font-weight:760;color:var(--ef-ink);margin:0;letter-spacing:-.02em">
                Daily Closing — {{ $dailyClosing->date->format('d M Y') }}
            </h1>
            <div style="margin-top:4px;display:flex;align-items:center;gap:8px">
                <x-status-badge :status="$dailyClosing->status" />
                @if ($dailyClosing->isFinalized())
                    <span style="font-size:.72rem;background:rgba(30,30,30,.07);color:var(--ef-ink-2);border-radius:5px;padding:2px 8px;font-weight:600">
                        <i class="bi bi-lock-fill me-1"></i>Finalized {{ $dailyClosing->finalized_at->format('d M Y') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @if ($dailyClosing->canEdit())
            <button type="button" class="ef-btn" id="btnSyncSnapshot">
                <i class="bi bi-arrow-repeat"></i> Sync
            </button>
            <button type="button" class="ef-btn" id="btnPreview">
                <i class="bi bi-bar-chart"></i> Preview
            </button>
        @endif
        @if ($dailyClosing->canFinalize())
            <button type="button" class="ef-btn ef-btn-dark" data-bs-toggle="modal" data-bs-target="#finalizeModal">
                <i class="bi bi-lock"></i> Finalize &amp; Lock
            </button>
        @endif
    </div>
</div>

@if (session('success'))
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(15,123,95,.08);border:1px solid rgba(15,123,95,.2);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:var(--ef-emerald);font-size:.875rem;font-weight:500">
        <span><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:var(--ef-danger);font-size:.875rem;font-weight:500">
        <span><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</span>
    </div>
@endif

{{-- Balance Cards --}}
<div id="balanceCards" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    <div class="ef-ds-kpi" data-accent="gold">
        <div class="ef-ds-kpi-label"><i class="bi bi-wallet2 ef-ds-kpi-icon"></i> Opening Balance</div>
        <div class="ef-ds-kpi-value c-gold" id="cardOpening">₹{{ number_format($totals['opening_balance'], 2) }}</div>
    </div>
    <div class="ef-ds-kpi" data-accent="danger">
        <div class="ef-ds-kpi-label"><i class="bi bi-receipt ef-ds-kpi-icon"></i> Expenses</div>
        <div class="ef-ds-kpi-value c-danger" id="cardExpenses">₹{{ number_format($totals['expense_total'], 2) }}</div>
        <div class="ef-ds-kpi-note" id="cardExpenseCount">{{ $totals['expense_count'] }} item(s)</div>
    </div>
    <div class="ef-ds-kpi" data-accent="amber">
        <div class="ef-ds-kpi-label"><i class="bi bi-credit-card ef-ds-kpi-icon"></i> Payments Out</div>
        <div class="ef-ds-kpi-value c-amber" id="cardPayments">₹{{ number_format($totals['payment_total'], 2) }}</div>
        <div class="ef-ds-kpi-note">
            Adj: <span style="color:var(--ef-emerald)" id="cardCredit">+₹{{ number_format($totals['total_credit'], 2) }}</span>
            / <span style="color:var(--ef-danger)" id="cardDebit">-₹{{ number_format($totals['total_debit'], 2) }}</span>
        </div>
    </div>
    <div class="ef-ds-kpi" data-accent="emerald" style="background:var(--ef-hero-grad)">
        <div class="ef-ds-kpi-label" style="color:var(--ef-on-dark-muted)"><i class="bi bi-lock ef-ds-kpi-icon"></i> Closing Balance</div>
        <div class="ef-ds-kpi-value" style="color:var(--ef-on-dark-gold)" id="cardClosing">₹{{ number_format($totals['closing_balance'], 2) }}</div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs" id="closingTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-summary" type="button" role="tab">
            <i class="bi bi-clipboard-data me-1"></i>Summary
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-expenses" type="button" role="tab">
            <i class="bi bi-receipt me-1"></i>Expenses
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:rgba(107,114,128,.12);color:var(--ef-muted);font-size:.65rem;font-weight:700;margin-left:6px" id="expenseBadge">{{ $dailyClosing->snapshotExpenses->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-adjustments" type="button" role="tab">
            <i class="bi bi-sliders me-1"></i>Adjustments
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:rgba(107,114,128,.12);color:var(--ef-muted);font-size:.65rem;font-weight:700;margin-left:6px" id="adjBadge">{{ $dailyClosing->adjustments->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-audit" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i>Audit History
        </button>
    </li>
</ul>

<div style="background:var(--ef-surface);border:1px solid var(--ef-border);border-top:none;border-radius:0 0 12px 12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:24px">

    {{-- ── Summary Tab ──────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="pane-summary" role="tabpanel">
        @if ($dailyClosing->canEdit())
            <form method="POST" action="{{ route('admin.daily-closings.update', $dailyClosing) }}">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 3fr;gap:12px;align-items:start">
                    <div>
                        <label class="ef-label">Opening Balance (₹)</label>
                        <input type="number" name="opening_balance"
                            class="ef-input @error('opening_balance') --error @enderror"
                            step="0.01" min="0"
                            value="{{ old('opening_balance', number_format((float)$dailyClosing->opening_balance, 2, '.', '')) }}">
                        @error('opening_balance')<div class="ef-field-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="ef-label">Notes</label>
                        <textarea name="notes" rows="2"
                            class="ef-textarea @error('notes') --error @enderror"
                            placeholder="Optional internal notes…">{{ old('notes', $dailyClosing->notes) }}</textarea>
                        @error('notes')<div class="ef-field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="ef-btn ef-btn-dark" data-loading-text="Saving…">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
            </form>
            <hr>
        @endif

        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;text-align:center">
            @php $statCard = 'background:var(--ef-bg-subtle);border:1px solid var(--ef-border);border-radius:10px;padding:10px 8px'; @endphp
            <div style="{{ $statCard }}">
                <div style="font-size:.72rem;color:var(--ef-muted);margin-bottom:3px">Expense Total</div>
                <strong style="font-size:.9rem;color:var(--ef-ink)">₹{{ number_format($dailyClosing->expense_total, 2) }}</strong>
            </div>
            <div style="{{ $statCard }}">
                <div style="font-size:.72rem;color:var(--ef-muted);margin-bottom:3px">Payment Total</div>
                <strong style="font-size:.9rem;color:var(--ef-ink)">₹{{ number_format($dailyClosing->payment_total, 2) }}</strong>
            </div>
            <div style="{{ $statCard }}">
                <div style="font-size:.72rem;color:var(--ef-muted);margin-bottom:3px">Credits</div>
                <strong style="font-size:.9rem;color:var(--ef-emerald)">+₹{{ number_format($dailyClosing->total_credit, 2) }}</strong>
            </div>
            <div style="{{ $statCard }}">
                <div style="font-size:.72rem;color:var(--ef-muted);margin-bottom:3px">Debits</div>
                <strong style="font-size:.9rem;color:var(--ef-danger)">-₹{{ number_format($dailyClosing->total_debit, 2) }}</strong>
            </div>
            <div style="{{ $statCard }}">
                <div style="font-size:.72rem;color:var(--ef-muted);margin-bottom:3px">Expense Count</div>
                <strong style="font-size:.9rem;color:var(--ef-ink)">{{ $dailyClosing->expense_count }}</strong>
            </div>
            <div style="background:var(--ef-hero-grad);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:10px 8px;text-align:center">
                <div style="font-size:.72rem;color:rgba(255,253,250,.55);margin-bottom:3px">Closing Balance</div>
                <strong style="font-size:.9rem;color:var(--ef-on-dark-gold)">₹{{ number_format($dailyClosing->closing_balance, 2) }}</strong>
            </div>
        </div>

        <div style="margin-top:12px;font-size:.8rem;color:var(--ef-muted)">
            Created by {{ optional($dailyClosing->creator)->name ?? '—' }} on {{ $dailyClosing->created_at->format('d M Y, h:i A') }}.
            @if ($dailyClosing->updater)
                Last updated by {{ $dailyClosing->updater->name }} on {{ $dailyClosing->updated_at->format('d M Y, h:i A') }}.
            @endif
        </div>
    </div>

    {{-- ── Expenses Tab ─────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-expenses" role="tabpanel">
        @if ($dailyClosing->canEdit())
            <div class="mb-3">
                <button type="button" class="ef-btn" style="color:var(--ef-emerald)" id="btnAddExpense">
                    <i class="bi bi-plus-circle"></i> Add Expense
                </button>
            </div>
        @endif

        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th style="text-align:center">Status</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Employee</th>
                        <th class="r">Amount</th>
                        <th>Remarks</th>
                        @unless ($dailyClosing->isFinalized())<th style="text-align:center">Actions</th>@endunless
                    </tr>
                </thead>
                <tbody id="expenseTableBody">
                    @forelse ($dailyClosing->snapshotExpenses as $expense)
                        @include('admin.daily-closings.partials.expense-row', ['closing' => $dailyClosing])
                    @empty
                        <tr id="noExpensesRow">
                            <td colspan="7" style="text-align:center;color:var(--ef-faint);padding:32px">No expenses in this closing.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Adjustments Tab ──────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-adjustments" role="tabpanel">
        @if ($dailyClosing->canEdit())
            <div style="background:var(--ef-bg-subtle);border:1px solid var(--ef-border);border-radius:var(--ef-radius);padding:12px 14px;margin-bottom:14px">
                <div>
                    <div style="font-size:.86rem;font-weight:680;color:var(--ef-ink-2);margin-bottom:10px">Add Adjustment</div>
                    <form id="formAddAdjustment" style="display:grid;grid-template-columns:1fr 1fr 2fr 2fr auto;gap:8px;align-items:end">
                        <div>
                            <label class="ef-label" style="font-size:.72rem">Type</label>
                            <select name="type" class="ef-select" style="min-height:36px;padding:6px 10px" required>
                                <option value="credit">Credit (+)</option>
                                <option value="debit">Debit (−)</option>
                            </select>
                        </div>
                        <div>
                            <label class="ef-label" style="font-size:.72rem">Amount (₹)</label>
                            <input type="number" name="amount" class="ef-input" style="min-height:36px;padding:6px 10px" step="0.01" min="0.01" required>
                        </div>
                        <div>
                            <label class="ef-label" style="font-size:.72rem">Reason</label>
                            <input type="text" name="reason" class="ef-input" style="min-height:36px;padding:6px 10px" maxlength="255" required>
                        </div>
                        <div>
                            <label class="ef-label" style="font-size:.72rem">Notes (optional)</label>
                            <input type="text" name="notes" class="ef-input" style="min-height:36px;padding:6px 10px" maxlength="1000">
                        </div>
                        <div>
                            <button type="submit" class="ef-btn ef-btn-dark" style="white-space:nowrap">Add</button>
                        </div>
                    </form>
                    <div id="adjFormError" class="d-none" style="color:var(--ef-danger);font-size:.8rem;margin-top:4px"></div>
                </div>
            </div>
        @endif

        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="r">Amount</th>
                        <th>Reason</th>
                        <th>Notes</th>
                        <th>By</th>
                        <th>Date</th>
                        @unless ($dailyClosing->isFinalized())<th></th>@endunless
                    </tr>
                </thead>
                <tbody id="adjTableBody">
                    @forelse ($dailyClosing->adjustments as $adj)
                        <tr data-adj-id="{{ $adj->id }}">
                            <td>
                                @if ($adj->isCredit())
                                    <span style="background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);border-radius:5px;color:var(--ef-emerald);font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Credit</span>
                                @else
                                    <span style="background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);border-radius:5px;color:var(--ef-danger);font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Debit</span>
                                @endif
                            </td>
                            <td class="r fw">₹{{ number_format($adj->amount, 2) }}</td>
                            <td>{{ $adj->reason }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $adj->notes ?: '—' }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ optional($adj->creator)->name ?? '—' }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $adj->created_at->format('d M Y') }}</td>
                            @unless ($dailyClosing->isFinalized())
                            <td style="text-align:center">
                                <button type="button" class="ef-btn ef-btn-icon btn-del-adj" style="color:var(--ef-danger)" data-id="{{ $adj->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                            @endunless
                        </tr>
                    @empty
                        <tr id="noAdjRow">
                            <td colspan="7" style="text-align:center;color:var(--ef-faint);padding:32px">No adjustments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Audit History Tab ────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-audit" role="tabpanel">
        @php
        $auditDsColors = [
            'success'   => 'background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);color:var(--ef-emerald)',
            'warning'   => 'background:rgba(216,154,61,.1);border:1px solid rgba(216,154,61,.2);color:var(--ef-amber)',
            'danger'    => 'background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);color:var(--ef-danger)',
            'secondary' => 'background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);color:#64748b',
            'primary'   => 'background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);color:#3b82f6',
            'info'      => 'background:rgba(13,148,136,.08);border:1px solid rgba(13,148,136,.15);color:var(--ef-teal)',
        ];
        @endphp
        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Remarks</th>
                        <th>By</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($audits as $a)
                    @php $auditColor = \App\Models\DailyClosingAudit::actionColors()[$a->action_type] ?? 'secondary'; @endphp
                        <tr>
                            <td>
                                <span style="{{ $auditDsColors[$auditColor] ?? $auditDsColors['secondary'] }};border-radius:5px;font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">
                                    {{ \App\Models\DailyClosingAudit::actionLabels()[$a->action_type] ?? ucfirst($a->action_type) }}
                                </span>
                            </td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $a->field_name ?: '—' }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $a->old_value ?: '—' }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $a->new_value ?: '—' }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $a->remarks ?: '—' }}</td>
                            <td style="font-size:.84rem">{{ optional($a->user)->name ?? '—' }}</td>
                            <td style="color:var(--ef-faint);font-size:.84rem">{{ $a->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:var(--ef-faint);padding:32px">No audit records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($audits->hasPages())
            <div class="mt-2">{{ $audits->links() }}</div>
        @endif
    </div>

</div>{{-- /tab-content --}}

{{-- ── Modals ──────────────────────────────────────────────────────────────── --}}

{{-- Add / Edit Expense Modal --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalTitle">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="expModalError" style="background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.2);border-radius:6px;padding:8px 14px;font-size:.84rem;color:var(--ef-danger);margin-bottom:12px;display:none"></div>
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <label class="ef-label">Title <span style="color:var(--ef-danger)">*</span></label>
                        <input type="text" id="expTitle" class="ef-input" maxlength="255" required>
                        <div class="ef-field-error" id="errTitle"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div>
                            <label class="ef-label">Amount (₹) <span style="color:var(--ef-danger)">*</span></label>
                            <input type="number" id="expAmount" class="ef-input" step="0.01" min="0.01" required>
                            <div class="ef-field-error" id="errAmount"></div>
                        </div>
                        <div>
                            <label class="ef-label">Status <span style="color:var(--ef-danger)">*</span></label>
                            <select id="expStatus" class="ef-select" required>
                                <option value="approved">Approved</option>
                                <option value="paid">Paid</option>
                                <option value="reimbursement_pending">Reimbursement Pending</option>
                                <option value="reimbursed">Reimbursed</option>
                                <option value="completed">Completed</option>
                            </select>
                            <div class="ef-field-error" id="errStatus"></div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div>
                            <label class="ef-label">Category</label>
                            <select id="expCategory" class="ef-select">
                                <option value="">— None —</option>
                                @foreach ($categories as $catId => $catName)
                                    <option value="{{ $catId }}">{{ $catName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="ef-label">Employee</label>
                            <select id="expEmployee" class="ef-select">
                                <option value="">— None —</option>
                                @foreach ($employees as $empId => $empName)
                                    <option value="{{ $empId }}">{{ $empName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="ef-label">Remarks</label>
                        <textarea id="expRemarks" class="ef-textarea" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ef-btn ef-btn-dark" id="btnSaveExpense">Save Expense</button>
            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview: Stored vs Computed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewBody">
                <div style="text-align:center;padding:24px 0"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Finalize Modal --}}
<div class="modal fade" id="finalizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--ef-hero-grad);border-bottom:1px solid rgba(255,255,255,.1)">
                <h5 class="modal-title" style="color:#fffdfa"><i class="bi bi-lock me-2"></i>Finalize &amp; Lock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will <strong>lock</strong> the closing for <strong>{{ $dailyClosing->date->format('d M Y') }}</strong>. No further edits will be possible.</p>
                <p style="margin-bottom:0;color:var(--ef-muted);font-size:.84rem">Totals will be recalculated one final time before locking.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.daily-closings.finalize', $dailyClosing) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="ef-btn ef-btn-dark" data-loading-text="Finalizing…">
                        <i class="bi bi-lock"></i> Yes, Finalize
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="ajaxToast" class="toast" role="alert" style="background:var(--ef-hero-grad);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fffdfa;min-width:220px">
        <div style="display:flex;align-items:center;gap:10px;padding:12px 14px">
            <div id="ajaxToastBody" style="flex:1;font-size:.875rem;font-weight:500">Done.</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const URLS = {
    snapshot:    '{{ route('admin.daily-closings.snapshot', $dailyClosing) }}',
    preview:     '{{ route('admin.daily-closings.preview', $dailyClosing) }}',
    expenses:    '{{ route('admin.daily-closings.expenses.store', $dailyClosing) }}',
    adjustments: '{{ route('admin.daily-closings.adjustments.store', $dailyClosing) }}',
    expenseBase: '{{ url('admin/daily-closings/' . $dailyClosing->id . '/expenses') }}',
    adjBase:     '{{ url('admin/daily-closings/' . $dailyClosing->id . '/adjustments') }}',
};

function showToast(msg, ok = true) {
    const t = document.getElementById('ajaxToast');
    t.style.background = ok ? 'var(--ef-hero-grad)' : 'rgba(200,75,68,.95)';
    document.getElementById('ajaxToastBody').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(t, { delay: 3500 }).show();
}

function fmt(n) {
    return '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateCards(totals) {
    document.getElementById('cardOpening').textContent      = fmt(totals.opening_balance);
    document.getElementById('cardExpenses').textContent     = fmt(totals.expense_total);
    document.getElementById('cardExpenseCount').textContent = totals.expense_count + ' item(s)';
    document.getElementById('cardPayments').textContent     = fmt(totals.payment_total);
    document.getElementById('cardCredit').textContent       = '+' + fmt(totals.total_credit);
    document.getElementById('cardDebit').textContent        = '-' + fmt(totals.total_debit);
    document.getElementById('cardClosing').textContent      = fmt(totals.closing_balance);
}

async function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    };
    if (body instanceof FormData) {
        opts.body = body;
    } else if (body) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(body);
    }
    const res = await fetch(url, opts);
    const json = await res.json();
    if (!res.ok) throw { status: res.status, data: json };
    return json;
}

function clearExpenseErrors() {
    ['expTitle', 'expAmount', 'expStatus'].forEach(id => {
        document.getElementById(id)?.classList.remove('is-invalid');
    });
    ['errTitle', 'errAmount', 'errStatus'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
}

function setExpenseErrors(errors) {
    const map = { title: ['expTitle', 'errTitle'], amount: ['expAmount', 'errAmount'], payment_status: ['expStatus', 'errStatus'] };
    for (const [field, [inputId, errId]] of Object.entries(map)) {
        if (errors[field]) {
            document.getElementById(inputId)?.classList.add('is-invalid');
            const errEl = document.getElementById(errId);
            if (errEl) errEl.textContent = errors[field][0];
        }
    }
}

// ── Sync Snapshot ──────────────────────────────────────────────────────────

document.getElementById('btnSyncSnapshot')?.addEventListener('click', async function () {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Syncing…';
    try {
        const data = await apiFetch(URLS.snapshot, 'PATCH');
        updateCards(data.totals);
        showToast(data.message);
        setTimeout(() => location.reload(), 1200);
    } catch (e) {
        showToast(e.data?.message || 'Sync failed.', false);
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Sync Live Expenses';
    }
});

// ── Preview ────────────────────────────────────────────────────────────────

document.getElementById('btnPreview')?.addEventListener('click', async function () {
    document.getElementById('previewBody').innerHTML = '<div style="text-align:center;padding:24px 0"><span class="spinner-border"></span></div>';
    new bootstrap.Modal(document.getElementById('previewModal')).show();
    try {
        const data = await apiFetch(URLS.preview);
        const fields = [
            ['Expense Total',    'expense_total'],
            ['Payment Total',    'payment_total'],
            ['Total Credit',     'total_credit'],
            ['Total Debit',      'total_debit'],
            ['Opening Balance',  'opening_balance'],
            ['Closing Balance',  'closing_balance'],
        ];
        let html = '<table class="ef-an-trend-table"><thead><tr><th>Field</th><th>Stored</th><th>Computed</th><th></th></tr></thead><tbody>';
        for (const [label, key] of fields) {
            const oldVal = parseFloat(data.old[key] || 0);
            const newVal = parseFloat(data.new[key] || 0);
            const diff   = Math.abs(newVal - oldVal) > 0.005;
            html += `<tr ${diff ? 'style="background:rgba(184,137,62,.06)"' : ''}>
                <td>${label}</td>
                <td>${fmt(oldVal)}</td>
                <td>${fmt(newVal)}</td>
                <td>${diff ? '<span style="background:rgba(184,137,62,.12);color:#7a5a1e;border:1px solid rgba(184,137,62,.3);border-radius:5px;font-size:.68rem;font-weight:700;padding:2px 8px">Changed</span>' : '<span style="background:rgba(107,114,128,.08);color:var(--ef-muted);border:1px solid var(--ef-border);border-radius:5px;font-size:.68rem;font-weight:700;padding:2px 8px">Same</span>'}</td>
            </tr>`;
        }
        html += '</tbody></table>';
        document.getElementById('previewBody').innerHTML = html;
    } catch (e) {
        document.getElementById('previewBody').innerHTML = '<div style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;padding:12px 16px;color:var(--ef-danger);font-size:.875rem">Failed to load preview.</div>';
    }
});

// ── Expense Modal ──────────────────────────────────────────────────────────

const expenseModal   = new bootstrap.Modal(document.getElementById('expenseModal'));
const btnSaveExpense = document.getElementById('btnSaveExpense');

function openExpenseModal(data = null) {
    document.getElementById('expenseModalTitle').textContent = data ? 'Edit Expense' : 'Add Expense';
    document.getElementById('expTitle').value    = data?.title   ?? '';
    document.getElementById('expAmount').value   = data?.amount  ?? '';
    document.getElementById('expStatus').value   = data?.status  ?? 'approved';
    document.getElementById('expRemarks').value  = data?.remarks ?? '';
    document.getElementById('expCategory').value = data?.category_id ?? '';
    document.getElementById('expEmployee').value = data?.employee_id ?? '';
    document.getElementById('expenseModal').dataset.editId = data?.id ?? '';
    document.getElementById('expModalError').classList.add('d-none');
    clearExpenseErrors();
    expenseModal.show();
}

document.getElementById('btnAddExpense')?.addEventListener('click', () => openExpenseModal());

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit-expense');
    if (!btn) return;
    openExpenseModal({
        id:          btn.dataset.id,
        title:       btn.dataset.title,
        amount:      btn.dataset.amount,
        remarks:     btn.dataset.remarks,
    });
});

btnSaveExpense?.addEventListener('click', async function () {
    const editId = document.getElementById('expenseModal').dataset.editId;
    const payload = new FormData();
    payload.append('title',          document.getElementById('expTitle').value);
    payload.append('amount',         document.getElementById('expAmount').value);
    payload.append('payment_status', document.getElementById('expStatus').value);
    payload.append('category_id',    document.getElementById('expCategory').value);
    payload.append('employee_id',    document.getElementById('expEmployee').value);
    payload.append('remarks',        document.getElementById('expRemarks').value);

    this.disabled = true;
    this.textContent = 'Saving…';
    document.getElementById('expModalError').classList.add('d-none');
    clearExpenseErrors();

    try {
        let data;
        if (editId) {
            payload.append('_method', 'PUT');
            data = await apiFetch(`${URLS.expenseBase}/${editId}`, 'POST', payload);
        } else {
            data = await apiFetch(URLS.expenses, 'POST', payload);
        }

        const tbody = document.getElementById('expenseTableBody');
        if (editId) {
            const row = tbody.querySelector(`tr[data-expense-id="${editId}"]`);
            if (row) row.outerHTML = data.row_html;
        } else {
            document.getElementById('noExpensesRow')?.remove();
            tbody.insertAdjacentHTML('beforeend', data.row_html);
            const badge = document.getElementById('expenseBadge');
            badge.textContent = parseInt(badge.textContent || 0) + 1;
        }

        updateCards(data.totals);
        expenseModal.hide();
        showToast(data.message);
    } catch (e) {
        if (e.status === 422 && e.data?.errors) {
            setExpenseErrors(e.data.errors);
        } else {
            const errEl = document.getElementById('expModalError');
            errEl.textContent = e.data?.message || 'Save failed.';
            errEl.classList.remove('d-none');
        }
    } finally {
        this.disabled = false;
        this.textContent = 'Save Expense';
    }
});

// ── Remove / Restore Expense ───────────────────────────────────────────────

document.getElementById('expenseTableBody').addEventListener('click', async function (e) {
    const removeBtn  = e.target.closest('.btn-remove-expense');
    const restoreBtn = e.target.closest('.btn-restore-expense');
    if (!removeBtn && !restoreBtn) return;

    const btn    = removeBtn || restoreBtn;
    const id     = btn.dataset.id;
    const action = removeBtn ? 'remove' : 'restore';
    btn.disabled = true;

    try {
        const data = await apiFetch(`${URLS.expenseBase}/${id}/${action}`, 'PATCH');
        const row  = document.querySelector(`tr[data-expense-id="${id}"]`);
        if (row) row.outerHTML = data.row_html;
        updateCards(data.totals);
        showToast(data.message);
    } catch (e) {
        showToast(e.data?.message || 'Action failed.', false);
        btn.disabled = false;
    }
});

// ── Add Adjustment ─────────────────────────────────────────────────────────

document.getElementById('formAddAdjustment')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const errEl  = document.getElementById('adjFormError');
    const submit = this.querySelector('[type="submit"]');
    errEl.classList.add('d-none');
    submit.disabled = true;
    submit.textContent = 'Adding…';

    try {
        const data = await apiFetch(URLS.adjustments, 'POST', new FormData(this));
        document.getElementById('noAdjRow')?.remove();
        const adj    = data.adjustment;
        const badgeStyle = adj.type === 'credit'
            ? 'background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);color:var(--ef-emerald)'
            : 'background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);color:var(--ef-danger)';
        const label  = adj.type === 'credit' ? 'Credit' : 'Debit';
        document.getElementById('adjTableBody').insertAdjacentHTML('beforeend', `
            <tr data-adj-id="${adj.id}">
                <td><span style="${badgeStyle};border-radius:5px;font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">${label}</span></td>
                <td class="r fw">₹${parseFloat(adj.amount).toFixed(2)}</td>
                <td>${adj.reason}</td>
                <td style="color:var(--ef-faint);font-size:.84rem">${adj.notes || '—'}</td>
                <td style="color:var(--ef-faint);font-size:.84rem">${adj.created_by}</td>
                <td style="color:var(--ef-faint);font-size:.84rem">${adj.created_at}</td>
                <td style="text-align:center">
                    <button type="button" class="ef-btn ef-btn-icon btn-del-adj" style="color:var(--ef-danger)" data-id="${adj.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`);
        const adjBadge = document.getElementById('adjBadge');
        adjBadge.textContent = parseInt(adjBadge.textContent || 0) + 1;
        updateCards(data.totals);
        this.reset();
        showToast(data.message);
    } catch (err) {
        errEl.textContent = err.data?.message || 'Failed to add adjustment.';
        errEl.classList.remove('d-none');
    } finally {
        submit.disabled = false;
        submit.textContent = 'Add';
    }
});

// ── Delete Adjustment ──────────────────────────────────────────────────────

document.getElementById('adjTableBody').addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-del-adj');
    if (!btn || !confirm('Remove this adjustment?')) return;

    const id = btn.dataset.id;
    btn.disabled = true;

    try {
        const data = await apiFetch(`${URLS.adjBase}/${id}`, 'DELETE');
        document.querySelector(`tr[data-adj-id="${id}"]`)?.remove();
        const adjBadge = document.getElementById('adjBadge');
        adjBadge.textContent = Math.max(0, parseInt(adjBadge.textContent || 1) - 1);
        updateCards(data.totals);
        showToast(data.message);
    } catch (err) {
        showToast(err.data?.message || 'Delete failed.', false);
        btn.disabled = false;
    }
});
</script>
@endpush
</x-admin-layout>
