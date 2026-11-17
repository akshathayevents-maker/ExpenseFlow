{{-- Materials for one meal of a Meal Register entry (kitchen inventory control). --}}
@php
    $m = $materials[$mealType] ?? null;
    $fq = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0';
@endphp
<div style="margin-top:12px;border-top:1px dashed var(--dmr-border);padding-top:12px">
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <strong style="font-size:.85rem;flex:1">Materials</strong>
        <a class="dmr-btn dmr-btn--primary" style="min-height:40px;padding:8px 14px"
           href="{{ route('inventory.material-requests.create', ['meal_entry_id' => $entry->id, 'meal_type' => $mealType]) }}">
            <i class="bi bi-basket"></i> Request materials
        </a>
    </div>
    @if($m)
        <div style="margin-top:8px;display:flex;flex-direction:column;gap:6px">
            @foreach($m['requests'] as $r)
                <a href="{{ route('inventory.material-requests.show', $r) }}" style="display:flex;justify-content:space-between;gap:8px;text-decoration:none;color:var(--dmr-ink);font-size:.85rem;background:#faf8f5;border-radius:10px;padding:8px 12px">
                    <span><strong>{{ $r->reference() }}</strong> · {{ $r->preparation_name ?: 'Materials' }} · {{ $r->requester?->name }}</span>
                    <span>{{ $r->statusLabel() }}</span>
                </a>
            @endforeach
        </div>
        @if(! empty($m['summary']['rows']))
            <div style="overflow-x:auto;margin-top:8px">
                <table style="width:100%;font-size:.8rem;border-collapse:collapse">
                    <thead><tr style="color:var(--dmr-faint);text-transform:uppercase;font-size:.68rem">
                        <th style="text-align:left;padding:4px">Item</th><th style="text-align:right;padding:4px">Approved</th><th style="text-align:right;padding:4px">Issued</th>
                        <th style="text-align:right;padding:4px">Returned</th><th style="text-align:right;padding:4px">Used</th><th style="text-align:right;padding:4px">Wasted</th><th style="text-align:right;padding:4px">Unexpl.</th>
                    </tr></thead>
                    <tbody>
                    @foreach($m['summary']['rows'] as $row)
                        <tr style="border-top:1px solid #f0ece6">
                            <td style="padding:4px">{{ $row['item'] }} <span style="color:var(--dmr-faint)">{{ $row['unit'] }}</span></td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['approved']) }}</td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['issued']) }}</td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['returned']) }}</td>
                            <td style="text-align:right;padding:4px">{{ $fq($row['consumed']) }}</td>
                            <td style="text-align:right;padding:4px;{{ $row['wasted'] > 0 ? 'color:var(--dmr-red);font-weight:700' : '' }}">{{ $fq($row['wasted']) }}</td>
                            <td style="text-align:right;padding:4px;{{ $row['unexplained'] > 0 ? 'color:var(--dmr-red);font-weight:700' : '' }}">{{ $fq($row['unexplained']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($showCost && $m['summary']['cost'] > 0)
                <div style="font-size:.8rem;color:var(--dmr-muted);margin-top:6px">Material cost ₹{{ number_format($m['summary']['cost'], 2) }}@if($guests) · ₹{{ number_format($m['summary']['cost'] / max(1, $guests), 2) }} per person @endif</div>
            @endif
        @endif
    @endif
</div>
