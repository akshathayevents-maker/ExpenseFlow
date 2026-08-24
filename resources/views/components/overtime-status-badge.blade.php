@props(['status'])

@php
$chips = [
    'pending'   => ['bg' => 'rgba(216,154,61,.13)',  'color' => '#7D5218', 'dot' => '#D89A3D'],
    'approved'  => ['bg' => 'rgba(15,123,95,.11)',   'color' => '#0A5240', 'dot' => '#0F7B5F'],
    'rejected'  => ['bg' => 'rgba(200,75,68,.11)',   'color' => '#9B2C2C', 'dot' => '#C84B44'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.11)', 'color' => '#334155', 'dot' => '#64748B'],
];
$chip  = $chips[$status] ?? ['bg' => 'rgba(110,106,100,.08)', 'color' => '#6E6A64', 'dot' => '#9A9690'];
$label = ucfirst($status);
@endphp

<span style="display:inline-flex;align-items:center;gap:5px;font-size:.68rem;font-weight:720;letter-spacing:.04em;text-transform:uppercase;border-radius:6px;padding:3px 9px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};white-space:nowrap">
    <span style="width:5px;height:5px;border-radius:50%;background:{{ $chip['dot'] }};flex-shrink:0"></span>
    {{ $label }}
</span>
