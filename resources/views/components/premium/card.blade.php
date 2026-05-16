@props(['title' => null, 'aside' => null])

<section {{ $attributes->merge(['class' => 'ef-card']) }}>
    <div class="ef-card-body">
        @if($title || $aside)
            <div class="ef-card-head">
                @if($title)
                    <h2 class="ef-card-title">{{ $title }}</h2>
                @endif
                @if($aside)
                    <div>{{ $aside }}</div>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
</section>
