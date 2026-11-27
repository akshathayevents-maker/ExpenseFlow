{{-- Hall order: final menu + materials + cost per event (kitchen inventory control). --}}
@php
    $fq = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0';
    $guests = max(1, (int) $booking->number_of_people);
    $menuLive = $booking->menuItems->where('is_removed', false);
@endphp
@if($booking->includesFood())
<div class="bs-card">
    <div class="bs-card-hdr">
        <span class="bs-card-title">Menu &amp; materials</span>
        <span class="bs-card-aside" style="display:flex;gap:6px;flex-wrap:wrap">
            <a href="{{ route('hall.bookings.menu.edit', $booking) }}" class="bs-act" style="min-height:36px"><i class="bi bi-list-ul"></i> Final menu</a>
            @if($booking->status !== 'cancelled')
                <a href="{{ route('inventory.material-requests.create', ['hall_booking_id' => $booking->id]) }}" class="bs-act" style="min-height:36px"><i class="bi bi-basket"></i> Request materials</a>
            @endif
        </span>
    </div>
    <div style="padding:12px 16px;font-size:.88rem">
        <div style="margin-bottom:8px"><strong>Plan:</strong> {{ $booking->uses_mixed_food ? $booking->foodSplits->pluck('meal_plan_name')->join(', ') : ($booking->mealPlan?->name ?? '—') }}</div>
        @if($booking->menuItems->isNotEmpty())
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px">
                @foreach($booking->menuItems as $m)
                    <span style="padding:3px 10px;border-radius:999px;font-size:.8rem;border:1px solid var(--ef-border);{{ $m->is_removed ? 'text-decoration:line-through;opacity:.6' : '' }}{{ $m->change_type === 'added' ? 'background:rgba(15,123,95,.1)' : '' }}{{ in_array($m->change_type, ['modified', 'replaced'], true) ? 'background:rgba(216,154,61,.14)' : '' }}">
                        @if($m->change_type === 'replaced' && $m->replaces){{ $m->replaces }} → @elseif($m->change_type === 'added')Extra: @endif{{ $m->name }}@if($m->servings) · {{ number_format($m->servings) }} servings @endif @if($m->notes) · {{ $m->notes }}@endif
                    </span>
                @endforeach
            </div>
        @else
            <div style="color:var(--ef-faint);margin-bottom:10px">Final menu not recorded yet.</div>
        @endif

        @forelse($booking->materialRequests as $r)
            <a href="{{ route('inventory.material-requests.show', $r) }}" style="display:flex;justify-content:space-between;gap:8px;padding:8px 0;border-top:1px solid var(--ef-border);text-decoration:none;color:var(--ef-ink)">
                <span><strong>{{ $r->reference() }}</strong> · {{ $r->preparation_name ?: 'Materials' }} · {{ $r->requester?->name }}</span>
                <span>{{ $r->statusLabel() }}</span>
            </a>
        @empty
            <div style="color:var(--ef-faint)">No material requests yet.</div>
        @endforelse

        @if(! empty($materials['rows']))
            <div style="overflow-x:auto;margin-top:10px">
                <table style="width:100%;font-size:.8rem;border-collapse:collapse">
                    <thead><tr style="color:var(--ef-faint);text-transform:uppercase;font-size:.68rem">
                        <th style="text-align:left;padding:4px">Item</th><th style="text-align:right;padding:4px">Issued</th><th style="text-align:right;padding:4px">Returned</th>
                        <th style="text-align:right;padding:4px">Used</th><th style="text-align:right;padding:4px">Wasted</th><th style="text-align:right;padding:4px">Unexpl.</th><th style="text-align:right;padding:4px">Cost ₹</th>
                    </tr></thead>
                    <tbody>
                    @foreach($materials['rows'] as $row)
                        <tr style="border-top:1px solid var(--ef-border)">
                            <td style="padding:4px">{{ $row['item'] }} <span style="color:var(--ef-faint)">{{ $row['unit'] }}</span></td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['issued'] ?: $row['approved']) }}</td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['returned']) }}</td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['consumed']) }}</td>
                            <td style="text-align:right;padding:4px;{{ $row['wasted'] > 0 ? 'color:var(--ef-danger);font-weight:700' : '' }}">{{ $fq($row['wasted']) }}</td>
                            <td style="text-align:right;padding:4px;{{ $row['unexplained'] > 0 ? 'color:var(--ef-danger);font-weight:700' : '' }}">{{ $fq($row['unexplained']) }}</td>
                            <td style="text-align:right;padding:4px">{{ number_format($row['cost'], 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @php
                $mealRevenue = $booking->meal_cost;
                $margin = $mealRevenue - $materials['cost'];
            @endphp
            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:10px">
                <div><div style="font-size:.68rem;text-transform:uppercase;color:var(--ef-faint)">Material cost</div><strong>₹{{ number_format($materials['cost'], 2) }}</strong></div>
                <div><div style="font-size:.68rem;text-transform:uppercase;color:var(--ef-faint)">Per guest</div><strong>₹{{ number_format($materials['cost'] / $guests, 2) }}</strong></div>
                @if($mealRevenue > 0)
                    <div><div style="font-size:.68rem;text-transform:uppercase;color:var(--ef-faint)">Food revenue</div><strong>₹{{ number_format($mealRevenue, 2) }}</strong></div>
                    <div><div style="font-size:.68rem;text-transform:uppercase;color:var(--ef-faint)">Food margin (materials only)</div><strong style="color:{{ $margin >= 0 ? 'var(--ef-emerald)' : 'var(--ef-danger)' }}">₹{{ number_format($margin, 2) }}</strong></div>
                @endif
            </div>
            <div style="font-size:.74rem;color:var(--ef-faint);margin-top:6px">Cost uses the store's average purchase cost at approval time. Labour, gas and overheads are not included.</div>
        @endif
    </div>
</div>
@endif
