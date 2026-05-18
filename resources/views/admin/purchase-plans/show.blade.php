<x-admin-layout title="{{ $purchasePlan->title }}">

@push('styles')
<style>
.ef-pp-show-split { display: grid; gap: 14px; grid-template-columns: 1fr 280px; align-items: start; }
.ef-pp-meta-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--ef-border); font-size: .86rem; }
.ef-pp-meta-row:last-child { border-bottom: none; }
.ef-pp-meta-label { color: var(--ef-faint); font-weight: 500; }
.ef-pp-meta-val { color: var(--ef-ink-2); font-weight: 600; text-align: right; }
@media (max-width: 991.98px) { .ef-pp-show-split { grid-template-columns: 1fr; } }
</style>
@endpush

@php $colors = \App\Models\PurchasePlan::statusColors(); $color = $colors[$purchasePlan->status] ?? 'secondary'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;padding-top:8px">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('admin.purchase-plans.index') }}" class="ef-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 style="font-size:1.25rem;font-weight:760;color:var(--ef-ink);margin:0;letter-spacing:-.02em">{{ $purchasePlan->title }}</h1>
            <p style="color:var(--ef-faint);font-size:.82rem;margin:2px 0 0">Planned: {{ $purchasePlan->planned_date->format('d M Y') }}</p>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <x-status-badge :status="$purchasePlan->status" />

        @if($purchasePlan->isDraft())
        <form method="POST" action="{{ route('admin.purchase-plans.approve', $purchasePlan) }}" style="display:inline">
            @csrf @method('PATCH')
            <button class="ef-btn ef-btn-dark">
                <i class="bi bi-check-circle"></i> Approve Plan
            </button>
        </form>
        @endif

        @if($purchasePlan->isApproved())
        <form method="POST" action="{{ route('admin.purchase-plans.status', $purchasePlan) }}" style="display:inline">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="ordered">
            <button class="ef-btn ef-btn-dark">Mark Ordered</button>
        </form>
        @endif

        @if($purchasePlan->isOrdered())
        <form method="POST" action="{{ route('admin.purchase-plans.status', $purchasePlan) }}" style="display:inline">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="completed">
            <button class="ef-btn ef-btn-dark">Mark Completed</button>
        </form>
        @endif
    </div>
</div>

@if($purchasePlan->notes)
<div style="background:var(--ef-bg-subtle);border:1px solid var(--ef-border);border-radius:var(--ef-radius);padding:12px 16px;margin-bottom:14px;font-size:.88rem;color:var(--ef-ink-2);display:flex;align-items:center;gap:10px">
    <i class="bi bi-sticky flex-shrink-0"></i>
    {{ $purchasePlan->notes }}
</div>
@endif

<div class="ef-pp-show-split">
    {{-- Items table --}}
    <x-ds.card :no-pad="true">
        <x-slot:head_right>
            <x-ds.section-head title="Items" :count="$purchasePlan->items->count()" />
        </x-slot:head_right>

        <div style="overflow-x:auto">
            <table class="ef-an-trend-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th style="text-align:center">Priority</th>
                        <th class="r">Qty</th>
                        <th class="r">Unit Cost</th>
                        <th class="r">Total</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchasePlan->items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('admin.inventory.items.show', $item->inventoryItem) }}"
                               style="color:var(--ef-ink-2);text-decoration:none;font-weight:600;font-size:.88rem">
                                {{ $item->inventoryItem->name }}
                            </a>
                            <div style="color:var(--ef-faint);font-size:.72rem;margin-top:2px">
                                Stock: {{ $item->inventoryItem->current_stock }} {{ $item->inventoryItem->unit }}
                            </div>
                        </td>
                        <td style="color:var(--ef-faint);font-size:.84rem">{{ $item->inventoryItem->category->name }}</td>
                        <td style="text-align:center">
                            <span class="ef-ds-priority --{{ $item->priority }}">{{ $item->priority }}</span>
                        </td>
                        <td class="r fw">{{ $item->suggested_quantity }} {{ $item->inventoryItem->unit }}</td>
                        <td class="r" style="color:var(--ef-faint)">
                            {{ $item->estimated_unit_cost ? '₹' . number_format($item->estimated_unit_cost, 2) : '—' }}
                        </td>
                        <td class="r fw">
                            {{ $item->estimatedTotal() > 0 ? '₹' . number_format($item->estimatedTotal(), 2) : '—' }}
                        </td>
                        <td style="color:var(--ef-faint);font-size:.84rem">{{ $item->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="fw" style="background:var(--ef-bg-subtle)">Estimated Total</td>
                        <td class="r fw" style="background:var(--ef-bg-subtle)">
                            @if($purchasePlan->estimatedTotal() > 0)
                                ₹{{ number_format($purchasePlan->estimatedTotal(), 2) }}
                            @else —
                            @endif
                        </td>
                        <td style="background:var(--ef-bg-subtle)"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-ds.card>

    {{-- Sidebar --}}
    <div>
        <x-ds.card title="Plan Details" style="margin-bottom:14px">
            <div class="ef-pp-meta-row">
                <span class="ef-pp-meta-label">Created By</span>
                <span class="ef-pp-meta-val">{{ $purchasePlan->creator->name }}</span>
            </div>
            <div class="ef-pp-meta-row">
                <span class="ef-pp-meta-label">Planned Date</span>
                <span class="ef-pp-meta-val">{{ $purchasePlan->planned_date->format('d M Y') }}</span>
            </div>
            @if($purchasePlan->approver)
            <div class="ef-pp-meta-row">
                <span class="ef-pp-meta-label">Approved By</span>
                <span class="ef-pp-meta-val" style="color:var(--ef-emerald)">{{ $purchasePlan->approver->name }}</span>
            </div>
            <div class="ef-pp-meta-row">
                <span class="ef-pp-meta-label">Approved At</span>
                <span class="ef-pp-meta-val" style="font-size:.8rem">{{ $purchasePlan->approved_at->format('d M Y, h:i A') }}</span>
            </div>
            @endif
            <div class="ef-pp-meta-row">
                <span class="ef-pp-meta-label">Total Items</span>
                <span class="ef-pp-meta-val">{{ $purchasePlan->items->count() }}</span>
            </div>
        </x-ds.card>

        @if(!$purchasePlan->isCompleted() && !($purchasePlan->isCancelled ?? false))
        <x-ds.card>
            <form method="POST" action="{{ route('admin.purchase-plans.status', $purchasePlan) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button class="ef-btn" style="width:100%;justify-content:center;color:var(--ef-danger)"
                        onclick="return confirm('Cancel this plan?')">
                    <i class="bi bi-x-circle"></i> Cancel Plan
                </button>
            </form>
        </x-ds.card>
        @endif
    </div>
</div>

</x-admin-layout>
