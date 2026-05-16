<tr data-expense-id="{{ $expense->id }}" class="{{ $expense->removed ? 'table-danger text-muted' : '' }}">
    <td class="text-center">
        @if ($expense->removed)
            <span class="badge bg-danger">Removed</span>
        @else
            <span class="badge bg-{{ $expense->statusColors()[$expense->payment_status] ?? 'secondary' }}">
                {{ $expense->statusLabels()[$expense->payment_status] ?? ucfirst($expense->payment_status) }}
            </span>
        @endif
    </td>
    <td>{{ $expense->title }}</td>
    <td>{{ optional($expense->category)->name ?? '—' }}</td>
    <td>{{ optional($expense->employee)->name ?? '—' }}</td>
    <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
    <td>{{ $expense->remarks ?: '—' }}</td>
    @unless ($closing->isFinalized())
    <td class="text-center">
        @if ($expense->removed)
            <button type="button" class="btn btn-sm btn-outline-warning btn-restore-expense"
                data-id="{{ $expense->id }}" title="Restore">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        @else
            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-expense"
                data-id="{{ $expense->id }}"
                data-title="{{ $expense->title }}"
                data-amount="{{ $expense->amount }}"
                data-remarks="{{ $expense->remarks }}"
                title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-expense"
                data-id="{{ $expense->id }}" title="Remove">
                <i class="bi bi-trash"></i>
            </button>
        @endif
    </td>
    @endunless
</tr>
