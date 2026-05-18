{{--
    Design System: Dark Hero Component
    Usage:
        <x-ds.hero
            eyebrow="Operations Center"
            title="Page Title"
            :meta="[['icon'=>'bi-calendar3','text'=>'Mon 18 May 2026'],...]"
            variant=""          (leave blank for emerald, or "olive")
        >
            <x-slot:actions>
                <a href="#" class="ef-ds-btn --primary"><i class="bi bi-plus"></i> <span>Add</span></a>
            </x-slot:actions>

            <x-slot:side>
                <div>
                    <div class="ef-ds-side-label">Total</div>
                    <div class="ef-ds-side-value">42</div>
                </div>
            </x-slot:side>

            <x-slot:mobile_stat>
                <span class="ef-ds-hero-mstat-val">42</span>
                <span class="ef-ds-hero-mstat-note">total · 30 active</span>
            </x-slot:mobile_stat>
        </x-ds.hero>
--}}

@props([
    'eyebrow'  => null,
    'title'    => '',
    'meta'     => [],
    'variant'  => '',
])

<section class="ef-ds-hero {{ $variant ? '--' . $variant : '' }}">

    <div class="ef-ds-hero-main">

        @if($eyebrow)
            <div class="ef-ds-eyebrow">{{ $eyebrow }}</div>
        @endif

        <h1 class="ef-ds-title">{{ $title }}</h1>

        @if(count($meta))
            <div class="ef-ds-subtitle">
                @foreach($meta as $item)
                    <span><i class="bi {{ $item['icon'] }}"></i> {{ $item['text'] }}</span>
                @endforeach
            </div>
        @endif

        @if(isset($actions))
            <div class="ef-ds-hero-acts">{{ $actions }}</div>
        @endif

        @if(isset($mobile_stat))
            <div class="ef-ds-hero-mstat">{{ $mobile_stat }}</div>
        @endif

    </div>

    @if(isset($side) && $side->isNotEmpty())
        <div class="ef-ds-hero-side">{{ $side }}</div>
    @endif

</section>
