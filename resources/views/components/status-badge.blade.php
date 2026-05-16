@props(['status'])

@php
$colors = [
    'pending'              => 'warning',
    'approved'             => 'success',
    'rejected'             => 'danger',
    'paid'                 => 'info',
    'reimbursement_pending'=> 'primary',
    'reimbursed'           => 'success',
    'completed'            => 'secondary',
];
$labels = [
    'reimbursement_pending' => 'Reimb. Pending',
];
$color = $colors[$status] ?? 'secondary';
@endphp

<span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
      style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px">
    {{ $labels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
</span>
