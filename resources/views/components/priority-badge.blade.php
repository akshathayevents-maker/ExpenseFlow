@props(['priority'])

@php
$colors = [
    'low'    => 'secondary',
    'medium' => 'info',
    'high'   => 'warning',
    'urgent' => 'danger',
];
$icons = [
    'low'    => 'bi-arrow-down',
    'medium' => 'bi-dash',
    'high'   => 'bi-arrow-up',
    'urgent' => 'bi-exclamation-triangle-fill',
];
$color = $colors[$priority] ?? 'secondary';
$icon  = $icons[$priority] ?? 'bi-dash';
@endphp

<span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
      style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px">
    <i class="bi {{ $icon }}"></i> {{ ucfirst($priority) }}
</span>
