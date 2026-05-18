<x-admin-layout title="Reimbursement Report">

<x-ds.hero eyebrow="Reports" title="Reimbursement Tracking">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
    <x-ds.kpi-card icon="bi-hourglass-split" label="Pending Reimbursement"
        value="₹{{ number_format($totals['pending'], 2) }}"
        accent="amber" value-color="c-amber" />
    <x-ds.kpi-card icon="bi-check-circle" label="Total Reimbursed"
        value="₹{{ number_format($totals['reimbursed'], 2) }}"
        accent="emerald" value-color="c-emerald" />
</div>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div>
            <label class="ef-label">Status</label>
            <select name="status" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:150px">
                <option value="">All Statuses</option>
                <option value="reimbursement_pending" {{ $status === 'reimbursement_pending' ? 'selected' : '' }}>Pending</option>
                <option value="reimbursed" {{ $status === 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">From</label>
            <input type="date" name="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $from }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">To</label>
            <input type="date" name="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $to }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Filter</button>
            <a href="{{ route('admin.reports.reimbursement') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="r">Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $req)
                <tr>
                    <td style="color:var(--ef-faint);font-size:.84rem;white-space:nowrap">{{ $req->created_at->format('d M Y') }}</td>
                    <td class="fw">{{ $req->requester->name }}</td>
                    <td style="color:var(--ef-ink-2)">{{ Str::limit($req->title, 40) }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $req->category->name }}</td>
                    <td><x-status-badge :status="$req->status"/></td>
                    <td class="r fw">₹{{ number_format($req->amount, 2) }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.expense-requests.show', $req) }}"
                           class="ef-btn" style="padding:4px 12px;font-size:.8rem">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--ef-faint)">No reimbursement requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($data->hasPages())
    <div style="padding:14px 18px;border-top:1px solid var(--ef-border)">
        {{ $data->links() }}
    </div>
    @endif
</x-ds.card>

</x-admin-layout>
