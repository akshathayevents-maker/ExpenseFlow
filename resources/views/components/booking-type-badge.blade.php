@props([
    'type',       // 'hall_only' | 'hall_food' | 'food_only'
    'size' => '',  // '' | 'sm' | 'xs'
])

@php
    $labels = \App\Models\HallBooking::bookingTypes();
    $label  = $labels[$type] ?? $type;

    $colorMap = [
        'hall_only' => ['bg' => 'rgba(59,130,246,.12)',  'border' => 'rgba(59,130,246,.3)',  'color' => '#1d4ed8'],
        'hall_food' => ['bg' => 'rgba(22,163,74,.12)',   'border' => 'rgba(22,163,74,.3)',   'color' => '#15803d'],
        'food_only' => ['bg' => 'rgba(234,88,12,.12)',   'border' => 'rgba(234,88,12,.3)',   'color' => '#c2410c'],
    ];
    $c = $colorMap[$type] ?? ['bg' => 'rgba(100,116,139,.1)', 'border' => 'rgba(100,116,139,.3)', 'color' => '#475569'];

    $iconMap = [
        'hall_only' => 'bi-building',
        'hall_food' => 'bi-building',
        'food_only' => 'bi-cup-hot',
    ];
    $icon = $iconMap[$type] ?? 'bi-tag';

    $fontSize = match($size) {
        'xs'    => '.58rem',
        'sm'    => '.65rem',
        default => '.72rem',
    };
    $padding = match($size) {
        'xs'    => '1px 6px',
        'sm'    => '2px 7px',
        default => '3px 9px',
    };
@endphp

<span style="
    display:inline-flex;
    align-items:center;
    gap:4px;
    background:{{ $c['bg'] }};
    border:1px solid {{ $c['border'] }};
    border-radius:999px;
    color:{{ $c['color'] }};
    font-size:{{ $fontSize }};
    font-weight:720;
    letter-spacing:.02em;
    padding:{{ $padding }};
    white-space:nowrap;
    vertical-align:middle;
" {{ $attributes }}>
    <i class="bi {{ $icon }}" style="font-size:.8em;line-height:1"></i>
    {{ $label }}
    @if($type === 'hall_food')
        <i class="bi bi-cup-hot" style="font-size:.8em;line-height:1;margin-left:-2px"></i>
    @endif
</span>
