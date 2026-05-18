@php
$statusDsColors = [
    'success'   => 'background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);color:var(--ef-emerald)',
    'warning'   => 'background:rgba(216,154,61,.1);border:1px solid rgba(216,154,61,.2);color:var(--ef-amber)',
    'danger'    => 'background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);color:var(--ef-danger)',
    'secondary' => 'background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);color:#64748b',
    'primary'   => 'background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);color:#3b82f6',
    'info'      => 'background:rgba(13,148,136,.08);border:1px solid rgba(13,148,136,.15);color:var(--ef-teal)',
];
$statusBadgeStyle = $statusDsColors[$expense->statusColors()[$expense->payment_status] ?? 'secondary'] ?? $statusDsColors['secondary'];
@endphp
<tr data-expense-id="{{ $expense->id }}"
    style="{{ $expense->removed ? 'background:rgba(220,53,69,.04);color:var(--ef-faint)' : '' }}">
    <td style="text-align:center">
        @if($expense->removed)
            <span style="background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);border-radius:5px;color:var(--ef-danger);font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Removed</span>
        @else
            <span style="{{ $statusBadgeStyle }};border-radius:5px;font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">
                {{ $expense->statusLabels()[$expense->payment_status] ?? ucfirst($expense->payment_status) }}
            </span>
        @endif
    </td>
    <td>{{ $expense->title }}</td>
    <td style="color:var(--ef-faint);font-size:.84rem">{{ optional($expense->category)->name ?? '—' }}</td>
    <td style="color:var(--ef-faint);font-size:.84rem">{{ optional($expense->employee)->name ?? '—' }}</td>
    <td class="r">{{ number_format($expense->amount, 2) }}</td>
    <td style="color:var(--ef-faint);font-size:.84rem">{{ $expense->remarks ?: '—' }}</td>
    @unless($closing->isFinalized())
    <td style="text-align:center">
        @if($expense->removed)
            <button type="button" class="ef-btn ef-btn-icon btn-restore-expense"
                style="color:var(--ef-amber)" data-id="{{ $expense->id }}" title="Restore">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        @else
            <button type="button" class="ef-btn ef-btn-icon btn-edit-expense"
                data-id="{{ $expense->id }}"
                data-title="{{ $expense->title }}"
                data-amount="{{ $expense->amount }}"
                data-remarks="{{ $expense->remarks }}"
                title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="ef-btn ef-btn-icon btn-remove-expense"
                style="color:var(--ef-danger)" data-id="{{ $expense->id }}" title="Remove">
                <i class="bi bi-trash"></i>
            </button>
        @endif
    </td>
    @endunless
</tr>
