<x-admin-layout title="Monthly Report">

<x-ds.hero eyebrow="Reports" title="Monthly Expense Aggregates">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div class="ef-an-filter-field">
            <label class="ef-label" for="year">Year</label>
            <select name="year" id="year" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:100px">
                @foreach($years as $y)
                    <option value="{{ (int)$y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ (int)$y }}</option>
                @endforeach
                @if($years->isEmpty())
                    <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                @endif
            </select>
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Apply</button>
        </div>
    </form>
</x-ds.card>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="r">Requests</th>
                    <th class="r">Total Amount</th>
                    <th class="r">Avg / Request</th>
                    <th style="width:180px">Distribution</th>
                </tr>
            </thead>
            <tbody>
                @php $maxTotal = $data->max('total') ?: 1; @endphp
                @forelse($data as $row)
                <tr>
                    <td class="fw">{{ trim($row->month_name) }} {{ $year }}</td>
                    <td class="r">{{ $row->count }}</td>
                    <td class="r fw">₹{{ number_format($row->total, 2) }}</td>
                    <td class="r" style="color:var(--ef-faint)">
                        ₹{{ $row->count > 0 ? number_format($row->total / $row->count, 2) : '0.00' }}
                    </td>
                    <td>
                        <div class="ef-an-trend-bar-wrap">
                            <div class="ef-an-trend-bar" style="width:{{ ($row->total / $maxTotal) * 100 }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--ef-faint)">No data for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
            @if($data->isNotEmpty())
            <tfoot>
                <tr>
                    <td class="fw" style="background:var(--ef-bg-subtle)">Year Total</td>
                    <td class="r" style="background:var(--ef-bg-subtle)">{{ $data->sum('count') }}</td>
                    <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($data->sum('total'), 2) }}</td>
                    <td colspan="2" style="background:var(--ef-bg-subtle)"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
