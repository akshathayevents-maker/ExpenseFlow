@props(['label'])

<div>
    <span class="ef-label">{{ $label }}</span>
    <span {{ $attributes->merge(['class' => 'ef-value']) }}>
        {{ $slot }}
    </span>
</div>
