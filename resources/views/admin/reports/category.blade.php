<x-admin-layout title="Category Report">

<x-ds.hero eyebrow="Reports" title="Category-wise Expenses">
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
            <a href="{{ route('admin.reports.category') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Category</th>
                    <th class="r">Requests</th>
                    <th class="r">Total Amount</th>
                    <th class="r">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $cat)
                <tr>
                    <td style="color:var(--ef-faint);font-size:.78rem">{{ $i + 1 }}</td>
                    <td class="fw">{{ $cat->name }}</td>
                    <td class="r">{{ $cat->total_count }}</td>
                    <td class="r fw">₹{{ number_format($cat->total_amount, 2) }}</td>
                    <td class="r">
                        @if($grandTotal > 0)
                            <div style="color:var(--ef-faint);font-size:.82rem;margin-bottom:3px">
                                {{ number_format(($cat->total_amount / $grandTotal) * 100, 1) }}%
                            </div>
                            <div class="ef-an-bar-wrap">
                                <div class="ef-an-bar --emerald" style="width:{{ ($cat->total_amount / $grandTotal) * 100 }}%"></div>
                            </div>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--ef-faint)">No data for selected period.</td></tr>
                @endforelse
            </tbody>
            @if($data->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2" class="fw" style="background:var(--ef-bg-subtle)">Grand Total</td>
                    <td class="r" style="background:var(--ef-bg-subtle)">{{ $data->sum('total_count') }}</td>
                    <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($grandTotal, 2) }}</td>
                    <td class="r" style="background:var(--ef-bg-subtle)">100%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
