<x-admin-layout title="Employee Report">

<x-ds.hero eyebrow="Reports" title="Employee-wise Expenses">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div class="ef-an-filter-field">
            <label class="ef-label" for="from">From</label>
            <input type="date" name="from" id="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $from }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label" for="to">To</label>
            <input type="date" name="to" id="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $to }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Apply</button>
            <a href="{{ route('admin.reports.employee') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Employee</th>
                    <th>Role</th>
                    <th class="r">Requests</th>
                    <th class="r">Total Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $user)
                <tr>
                    <td style="color:var(--ef-faint);font-size:.78rem">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw" style="color:var(--ef-ink)">{{ $user->name }}</div>
                        <div style="color:var(--ef-faint);font-size:.78rem">{{ $user->email }}</div>
                    </td>
                    <td>
                        <span class="ef-chip" style="font-size:.7rem">{{ $user->role }}</span>
                    </td>
                    <td class="r">{{ $user->total_count }}</td>
                    <td class="r fw">₹{{ number_format($user->total_amount, 2) }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.expense-requests.index', ['employee_id' => $user->id]) }}"
                           class="ef-btn" style="padding:4px 12px;font-size:.8rem">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--ef-faint)">No data for selected period.</td></tr>
                @endforelse
            </tbody>
            @if($data->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" class="fw" style="background:var(--ef-bg-subtle)">Total</td>
                    <td class="r" style="background:var(--ef-bg-subtle)">{{ $data->sum('total_count') }}</td>
                    <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($data->sum('total_amount'), 2) }}</td>
                    <td style="background:var(--ef-bg-subtle)"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
