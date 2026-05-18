<x-admin-layout title="Daily Report">

<x-ds.hero eyebrow="Reports" title="Daily Expense Aggregates">
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
            <a href="{{ route('admin.reports.daily') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="r">Requests</th>
                    <th class="r">Total Amount</th>
                    <th class="r">Avg / Request</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="fw">{{ \Carbon\Carbon::parse($row->date)->format('d M Y, l') }}</td>
                    <td class="r">{{ $row->count }}</td>
                    <td class="r fw">₹{{ number_format($row->total, 2) }}</td>
                    <td class="r" style="color:var(--ef-faint)">
                        ₹{{ $row->count > 0 ? number_format($row->total / $row->count, 2) : '0.00' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--ef-faint)">No data for selected period.</td></tr>
                @endforelse
            </tbody>
            @if($data->isNotEmpty())
            <tfoot>
                <tr>
                    <td class="fw" style="background:var(--ef-bg-subtle)">Total ({{ $data->count() }} days)</td>
                    <td class="r" style="background:var(--ef-bg-subtle)">{{ $data->sum('count') }}</td>
                    <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($data->sum('total'), 2) }}</td>
                    <td style="background:var(--ef-bg-subtle)"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
