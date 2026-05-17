@props(['status'])

@php
$chips = [
    'pending'               => ['bg' => 'rgba(216,154,61,.13)',  'color' => '#7D5218', 'dot' => '#D89A3D'],
    'pending_payment'       => ['bg' => 'rgba(47,111,237,.10)',  'color' => '#1E4DB7', 'dot' => '#2F6FED'],
    'approved'              => ['bg' => 'rgba(15,123,95,.11)',   'color' => '#0A5240', 'dot' => '#0F7B5F'],
    'rejected'              => ['bg' => 'rgba(200,75,68,.11)',   'color' => '#9B2C2C', 'dot' => '#C84B44'],
    'paid'                  => ['bg' => 'rgba(13,148,136,.10)',  'color' => '#0E6B62', 'dot' => '#0D9488'],
    'reimbursement_pending' => ['bg' => 'rgba(184,137,62,.12)',  'color' => '#6B4A12', 'dot' => '#B8893E'],
    'reimbursed'            => ['bg' => 'rgba(15,123,95,.11)',   'color' => '#0A5240', 'dot' => '#0F7B5F'],
    'completed'             => ['bg' => 'rgba(110,106,100,.08)', 'color' => '#6E6A64', 'dot' => '#9A9690'],
];
$labels = [
    'pending_payment'       => 'Pmt. Pending',
    'reimbursement_pending' => 'Reimb. Pending',
];
$chip  = $chips[$status] ?? ['bg' => 'rgba(110,106,100,.08)', 'color' => '#6E6A64', 'dot' => '#9A9690'];
$label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
@endphp

<span style="display:inline-flex;align-items:center;gap:5px;font-size:.68rem;font-weight:720;letter-spacing:.04em;text-transform:uppercase;border-radius:6px;padding:3px 9px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};white-space:nowrap">
    <span style="width:5px;height:5px;border-radius:50%;background:{{ $chip['dot'] }};flex-shrink:0"></span>
    {{ $label }}
</span>
