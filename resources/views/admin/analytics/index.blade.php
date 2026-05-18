<x-admin-layout title="Analytics">

<x-ds.hero eyebrow="Reports" title="Analytics & Insights"
    :meta="[['icon' => 'bi-calendar3', 'text' => 'Period: ' . \Carbon\Carbon::parse($from)->format('d M') . ' — ' . \Carbon\Carbon::parse($to)->format('d M Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.analytics.inventory') }}" class="ef-btn">
            <i class="bi bi-boxes"></i> Inventory Analytics
        </a>
    </x-slot:actions>
</x-ds.hero>

{{-- Date filter --}}
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
            <a href="{{ route('admin.analytics.index') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

{{-- Grand total --}}
<div class="ef-an-total">
    <div class="ef-an-total-icon"><i class="bi bi-cash-stack"></i></div>
    <div>
        <div class="ef-an-total-label">
            Total Settled Expenses &middot; {{ \Carbon\Carbon::parse($from)->format('d M') }} &mdash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </div>
        <div class="ef-an-total-value">₹{{ number_format($grandTotal, 2) }}</div>
    </div>
</div>

{{-- Top 3 breakdown --}}
<div class="ef-an-grid">

    {{-- Top categories --}}
    <x-ds.card title="Top Categories">
        @forelse($topCategories as $i => $cat)
            <div class="ef-an-rank-item">
                <div class="ef-an-rank-row">
                    <div class="ef-an-rank-name">
                        <span class="ef-an-rank-num">{{ $i + 1 }}</span>
                        {{ $cat->name }}
                    </div>
                    <div class="ef-an-rank-val">₹{{ number_format($cat->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="ef-an-bar-wrap">
                    <div class="ef-an-bar --emerald" style="width:{{ ($cat->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
            </div>
        @empty
            <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No data for period.</div>
        @endforelse
    </x-ds.card>

    {{-- Top spenders --}}
    <x-ds.card title="Top Spenders">
        @forelse($topEmployees as $i => $emp)
            <div class="ef-an-rank-item">
                <div class="ef-an-rank-row">
                    <div class="ef-an-rank-name">
                        <span class="ef-an-rank-num">{{ $i + 1 }}</span>
                        {{ $emp->name }}
                    </div>
                    <div class="ef-an-rank-val">₹{{ number_format($emp->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="ef-an-bar-wrap">
                    <div class="ef-an-bar --gold" style="width:{{ ($emp->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
            </div>
        @empty
            <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No data for period.</div>
        @endforelse
    </x-ds.card>

    {{-- Top vendors --}}
    <x-ds.card title="Top Vendors">
        @forelse($topVendors as $i => $vendor)
            <div class="ef-an-rank-item">
                <div class="ef-an-rank-row">
                    <div class="ef-an-rank-name">
                        <span class="ef-an-rank-num --amber">{{ $i + 1 }}</span>
                        {{ $vendor->name }}
                    </div>
                    <div class="ef-an-rank-val">₹{{ number_format($vendor->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="ef-an-bar-wrap">
                    <div class="ef-an-bar --amber" style="width:{{ ($vendor->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
            </div>
        @empty
            <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No data for period.</div>
        @endforelse
    </x-ds.card>

</div>

{{-- Monthly trend --}}
<x-ds.card title="Monthly Expense Trend" :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="r">Requests</th>
                    <th class="r">Total</th>
                    <th style="width:180px">Relative</th>
                </tr>
            </thead>
            <tbody>
                @php $maxTotal = $monthlyTrend->max('total') ?: 1; @endphp
                @forelse($monthlyTrend as $row)
                <tr>
                    <td class="fw">{{ $row->month }}</td>
                    <td class="r">{{ $row->count }}</td>
                    <td class="r fw">₹{{ number_format($row->total, 2) }}</td>
                    <td>
                        <div class="ef-an-trend-bar-wrap">
                            <div class="ef-an-trend-bar" style="width:{{ ($row->total / $maxTotal) * 100 }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--ef-faint)">No data for period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
