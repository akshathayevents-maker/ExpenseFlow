<x-admin-layout title="Stock Alerts">

<x-ds.hero eyebrow="Inventory" title="Stock Alerts"
    :meta="[['icon' => 'bi-exclamation-triangle', 'text' => $unresolvedCount . ' unresolved alert(s)']]">
    @if($unresolvedCount > 0)
    <x-slot:actions>
        <form method="POST" action="{{ route('admin.inventory.alerts.resolve-all') }}">
            @csrf @method('PATCH')
            <button class="ef-btn ef-btn-dark">
                <i class="bi bi-check2-all"></i> Resolve All
            </button>
        </form>
    </x-slot:actions>
    @endif
</x-ds.hero>

<div style="display:flex;gap:8px;margin-bottom:14px">
    <a href="{{ route('admin.inventory.alerts.index') }}"
       class="ef-btn {{ request('resolved') !== '1' ? 'ef-btn-dark' : '' }}">
        Unresolved
        @if($unresolvedCount > 0)
            <span style="background:var(--ef-danger);color:#fff;border-radius:10px;font-size:.68rem;font-weight:700;padding:1px 7px;margin-left:4px">{{ $unresolvedCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.inventory.alerts.index', ['resolved' => 1]) }}"
       class="ef-btn {{ request('resolved') === '1' ? 'ef-btn-dark' : '' }}">
        Resolved
    </a>
</div>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Alert Type</th>
                    <th class="r">Stock at Alert</th>
                    <th>Triggered</th>
                    <th>Resolved By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr>
                    <td>
                        <a href="{{ route('admin.inventory.items.show', $alert->item) }}"
                           style="color:var(--ef-ink-2);text-decoration:none;font-weight:600">{{ $alert->item->name }}</a>
                        <div style="color:var(--ef-faint);font-size:.76rem;margin-top:2px">
                            Now: {{ $alert->item->current_stock }} {{ $alert->item->unit }}
                            (min: {{ $alert->item->minimum_stock }})
                        </div>
                    </td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $alert->item->category->name }}</td>
                    <td>
                        @if($alert->alert_type === 'out_of_stock')
                            <span style="background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.2);border-radius:5px;color:var(--ef-danger);font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Out of Stock</span>
                        @else
                            <span style="background:rgba(216,154,61,.1);border:1px solid rgba(216,154,61,.2);border-radius:5px;color:var(--ef-amber);font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Low Stock</span>
                        @endif
                    </td>
                    <td class="r" style="color:var(--ef-danger);font-weight:680">
                        {{ $alert->stock_at_alert }} {{ $alert->item->unit }}
                    </td>
                    <td style="color:var(--ef-faint);font-size:.84rem;white-space:nowrap">{{ $alert->created_at->format('d M Y, h:i A') }}</td>
                    <td style="font-size:.84rem">
                        @if($alert->is_resolved)
                            <div style="color:var(--ef-ink-2)">{{ $alert->resolver?->name ?? '—' }}</div>
                            <div style="color:var(--ef-emerald);font-size:.76rem">{{ $alert->resolved_at->format('d M') }}</div>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if(! $alert->is_resolved)
                        <form method="POST" action="{{ route('admin.inventory.alerts.resolve', $alert) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <button class="ef-btn" style="color:var(--ef-emerald)">
                                <i class="bi bi-check-lg"></i> Resolve
                            </button>
                        </form>
                        @else
                            <span style="background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);border-radius:5px;color:var(--ef-emerald);font-size:.68rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Resolved</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--ef-faint)">
                        <i class="bi bi-check-circle" style="font-size:1.5rem;display:block;margin-bottom:8px;color:var(--ef-emerald)"></i>
                        {{ request('resolved') === '1' ? 'No resolved alerts.' : 'No active alerts. Stock levels are healthy!' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($alerts->hasPages())
    <div style="padding:12px 18px;border-top:1px solid var(--ef-border)">{{ $alerts->links() }}</div>
    @endif
</x-ds.card>

</x-admin-layout>
