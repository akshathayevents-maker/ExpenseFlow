{{--
    Design System: Content Card
    Standard panel card used across all pages.

    Usage:
        <x-ds.card title="Section Title" variant="">
            <x-slot:head_right>
                <a href="#" class="ef-ds-card-link">View all</a>
            </x-slot:head_right>

            Card body content goes here.
        </x-ds.card>

    Props:
        title     — Card section title (shown in header)
        variant   — Leave blank for light card, or "dark" for dark command card
        noPad     — Set :no-pad="true" to remove default body padding (for tables/lists)
--}}

@props([
    'title'  => null,
    'variant'=> '',
    'noPad'  => false,
])

<div class="ef-ds-card {{ $variant ? '--' . $variant : '' }}">

    @if($title || isset($head_right))
        <div class="ef-ds-card-head">
            @if($title)
                <div class="ef-ds-card-title">{{ $title }}</div>
            @endif
            @if(isset($head_right))
                {{ $head_right }}
            @endif
        </div>
    @endif

    <div class="{{ $noPad ? '' : 'ef-ds-card-body' }}">
        {{ $slot }}
    </div>

</div>
