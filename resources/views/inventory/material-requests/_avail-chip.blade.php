{{-- Stock availability for one request line. $a: state/need/have/gap/incoming (stock unit), $unit: stock unit --}}
@php $fq = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0'; @endphp
<div>
    @if($a['state'] === 'out')
        <span class="sa-chip --out"><i class="bi bi-x-octagon-fill"></i>Out of stock</span>
    @elseif($a['state'] === 'short')
        <span class="sa-chip --short"><i class="bi bi-exclamation-triangle-fill"></i>Short {{ $fq($a['gap']) }} {{ $unit }}</span>
    @else
        <span class="sa-chip --ok"><i class="bi bi-check-circle-fill"></i>In stock</span>
    @endif
    @if($a['state'] !== 'ok' && $a['incoming'] > 0)
        <span class="sa-chip --in"><i class="bi bi-truck"></i>{{ $fq($a['incoming']) }} {{ $unit }} arriving</span>
    @endif
</div>
