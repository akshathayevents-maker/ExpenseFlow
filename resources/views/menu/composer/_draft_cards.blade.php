@foreach($drafts as $draft)
@php
    $sections   = array_values(array_filter($draft->normalizedContent(), fn($s) => !empty($s['items'])));
    $totalItems = array_sum(array_map(fn($s) => count($s['items']), $sections));
    $totalSecs  = count($sections);
    $totalPax   = collect($sections)->sum('people_count') ?: $draft->people_count;

    $showSecs   = array_slice($sections, 0, 3);
    $hiddenSecs = max(0, $totalSecs - 3);
@endphp
<div class="mc-card">
    <div class="mc-card-accent"></div>
    <div class="mc-card-inner">

        {{-- P1: Title + age --}}
        <div class="mc-card-top">
            <div class="mc-card-name" title="{{ $draft->title }}">{{ $draft->title }}</div>
            <span class="mc-card-age"><i class="bi bi-clock"></i> {{ $draft->updated_at->diffForHumans() }}</span>
        </div>

        {{-- P2: Venue + Date --}}
        <div class="mc-card-where">
            @if($draft->venue)
                <div class="mc-where-row"><i class="bi bi-geo-alt-fill"></i> {{ Str::limit($draft->venue, 30) }}</div>
            @endif
            @if($draft->event_date)
                <div class="mc-where-row"><i class="bi bi-calendar3"></i> {{ $draft->formattedDate() }}</div>
            @endif
            @if(!$draft->venue && !$draft->event_date)
                <div class="mc-where-empty">No venue or date set</div>
            @endif
        </div>

        {{-- P3: Pax --}}
        @if($totalPax)
        <div class="mc-pax-line">
            <i class="bi bi-people-fill"></i> {{ number_format($totalPax) }} Pax
        </div>
        @endif

        {{-- P4: Section summary --}}
        <div class="mc-sec-summary">
            @forelse($showSecs as $sec)
            <div class="mc-sec-line">
                <span class="mc-sec-name">{{ $sec['label_en'] ?? 'Section' }}</span>
                <span class="mc-sec-count">{{ count($sec['items']) }}</span>
            </div>
            @empty
            <div class="mc-sec-none">No sections yet — open to build menu</div>
            @endforelse
            @if($hiddenSecs > 0)
            <div class="mc-sec-more">+{{ $hiddenSecs }} more {{ Str::plural('section', $hiddenSecs) }}</div>
            @endif
        </div>

    </div>

    {{-- Footer --}}
    <div class="mc-card-foot">
        <a href="{{ route('menu.drafts.edit', $draft) }}" class="mc-btn-open">
            <i class="bi bi-pencil-square"></i> Open Menu
        </a>
        <form method="POST" action="{{ route('menu.drafts.duplicate', $draft) }}">
            @csrf
            <button type="submit" class="mc-icon-btn mc-icon-btn--dupe" title="Duplicate">
                <i class="bi bi-copy"></i>
            </button>
        </form>
        <form method="POST" action="{{ route('menu.drafts.destroy', $draft) }}"
              onsubmit="return confirm('Delete \'{{ addslashes($draft->title) }}\'?')">
            @csrf @method('DELETE')
            <button type="submit" class="mc-icon-btn mc-icon-btn--del" title="Delete">
                <i class="bi bi-trash3"></i>
            </button>
        </form>
    </div>
</div>
@endforeach
