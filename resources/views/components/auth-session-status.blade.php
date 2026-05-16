@props(['status'])

@if ($status)
<div {{ $attributes->merge(['class' => 'alert alert-success border-0 py-2 small']) }}>
    <i class="bi bi-check-circle me-1"></i> {{ $status }}
</div>
@endif
