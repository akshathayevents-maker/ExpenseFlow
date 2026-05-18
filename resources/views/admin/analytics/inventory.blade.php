<x-admin-layout title="Inventory Analytics">

<x-ds.hero eyebrow="Analytics" title="Inventory Analytics"
    :meta="[['icon' => 'bi-calendar3', 'text' => 'Period: ' . \Carbon\Carbon::parse($from)->format('d M') . ' — ' . \Carbon\Carbon::parse($to)->format('d M Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.analytics.index') }}" class="ef-btn">
            <i class="bi bi-bar-chart"></i> Expense Analytics
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
            <a href="{{ route('admin.analytics.inventory') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
    <x-ds.kpi-card icon="bi-boxes" label="Total Inventory Value"
        value="₹{{ number_format($totalInventoryValue, 2) }}"
        note="Current stock × avg cost"
        accent="emerald" value-color="c-emerald" />
    <x-ds.kpi-card icon="bi-trash" label="Wastage Cost (Period)"
        value="₹{{ number_format($totalWastageCost, 2) }}"
        :note="\Carbon\Carbon::parse($from)->format('d M') . ' — ' . \Carbon\Carbon::parse($to)->format('d M Y')"
        accent="amber" value-color="c-danger" />
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

    <x-ds.card title="Top Used Items">
        @forelse($topUsed as $i => $item)
        @php $maxUsed = $topUsed->max('used_qty') ?: 1; @endphp
        <div class="ef-an-rank-item">
            <div class="ef-an-rank-row">
                <div class="ef-an-rank-name">
                    <span class="ef-an-rank-num">{{ $i + 1 }}</span>
                    <a href="{{ route('admin.inventory.items.show', $item) }}"
                       style="color:var(--ef-ink-2);text-decoration:none;font-weight:600">{{ $item->name }}</a>
                    <span style="color:var(--ef-faint);font-size:.72rem"> · {{ $item->category->name }}</span>
                </div>
                <div class="ef-an-rank-val">{{ number_format($item->used_qty, 3) + 0 }} {{ $item->unit }}</div>
            </div>
            <div class="ef-an-bar-wrap">
                <div class="ef-an-bar --emerald" style="width:{{ ($item->used_qty / $maxUsed) * 100 }}%"></div>
            </div>
        </div>
        @empty
        <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No usage data for period.</div>
        @endforelse
    </x-ds.card>

    <x-ds.card title="Top Wasted Items">
        @forelse($topWasted as $i => $item)
        @php $maxWasted = $topWasted->max('wasted_qty') ?: 1; @endphp
        <div class="ef-an-rank-item">
            <div class="ef-an-rank-row">
                <div class="ef-an-rank-name">
                    <span class="ef-an-rank-num --amber">{{ $i + 1 }}</span>
                    <a href="{{ route('admin.inventory.items.show', $item) }}"
                       style="color:var(--ef-ink-2);text-decoration:none;font-weight:600">{{ $item->name }}</a>
                    <span style="color:var(--ef-faint);font-size:.72rem"> · {{ $item->category->name }}</span>
                </div>
                <div class="ef-an-rank-val" style="color:var(--ef-danger)">{{ number_format($item->wasted_qty, 3) + 0 }} {{ $item->unit }}</div>
            </div>
            <div class="ef-an-bar-wrap">
                <div class="ef-an-bar --amber" style="width:{{ ($item->wasted_qty / $maxWasted) * 100 }}%;background:var(--ef-danger)"></div>
            </div>
        </div>
        @empty
        <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No wastage recorded for period.</div>
        @endforelse
    </x-ds.card>

</div>

</x-admin-layout>
