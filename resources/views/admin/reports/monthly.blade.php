<x-admin-layout title="Monthly Report">

{{-- ── Mobile filter (d-md-none) ──────────────────────────────── --}}
<div class="d-md-none"
     x-data="{
         yr:  @js((int)$year),
         yr0: @js((int)$year),
         ld: false,
         get dirty() { return this.yr !== this.yr0 },
         go() { this.ld=true; window.location.href='{{ route('admin.reports.monthly') }}?year='+this.yr }
     }">

    <div class="ef-rpf-wrap">
        <div class="ef-rpf-lbl">Year</div>
        <div class="ef-rpf-ranges">
            @foreach($years->take(5) as $y)
            <button type="button"
                    class="ef-rpf-chip"
                    :class="{'--active': yr === {{ (int)$y }}}"
                    @click="yr = {{ (int)$y }}">
                {{ (int)$y }}
            </button>
            @endforeach
            @if($years->isEmpty())
            <button type="button" class="ef-rpf-chip --active">{{ now()->year }}</button>
            @endif
        </div>

        @if((int)$year !== now()->year)
        <div class="ef-rpf-fsbar">
            <span class="ef-rpf-fsbar-lbl">Viewing:</span>
            <span class="ef-rpf-fsbar-chip"><i class="bi bi-calendar3"></i> {{ $year }}</span>
        </div>
        @endif

        <div class="ef-rpf-footer">
            <button type="button" class="ef-rpf-apply" @click="go()" :disabled="!dirty || ld">
                <template x-if="ld"><span><i class="bi bi-hourglass-split ef-rpf-spinner"></i></span></template>
                <template x-if="!ld"><span>View Year</span></template>
            </button>
        </div>
    </div>
</div>

{{-- ── Desktop filter ──────────────────────────────────────────── --}}
<div class="d-none d-md-block">
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
</div>

{{-- ── Mobile hero (d-md-none) ─────────────────────────────────── --}}
<div class="d-md-none">
    <x-ds.hero eyebrow="Reports" title="Monthly Expense Aggregates">
        <x-slot:actions>
            <a href="{{ route('admin.reports.index') }}" class="ef-btn ef-btn-icon"><i class="bi bi-arrow-left"></i></a>
        </x-slot:actions>
    </x-ds.hero>
</div>

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
                <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--ef-faint)">
                    <i class="bi bi-calendar-month" style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.3"></i>
                    No data for {{ $year }}.
                </td></tr>
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
