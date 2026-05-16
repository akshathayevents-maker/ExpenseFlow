@props(['tone' => 'neutral'])

<span {{ $attributes->merge(['class' => 'ef-chip'])->merge(['data-tone' => $tone]) }}>
    <span class="ef-chip-dot"></span>
    {{ $slot }}
</span>
