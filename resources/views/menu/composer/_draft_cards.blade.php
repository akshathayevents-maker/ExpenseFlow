@php
    $secIconMap = [
        'breakfast' => 'bi-brightness-high',
        'lunch'     => 'bi-cup-hot',
        'dinner'    => 'bi-moon-stars',
        'evening_snacks' => 'bi-cup-straw',
        'snacks'    => 'bi-cup-straw',
    ];
@endphp
@foreach($drafts as $draft)
@php
    $sections   = array_values(array_filter($draft->normalizedContent(), fn($s) => !empty($s['items'])));
    $totalSecs  = count($sections);
    $totalPax   = collect($sections)->sum('people_count') ?: $draft->people_count;

    $showSecs   = array_slice($sections, 0, 3);
    $hiddenSecs = max(0, $totalSecs - 3);

    $isPast  = $draft->isPastEvent();
    $isToday = $draft->isTodayEvent();
    $statusLabel = $draft->dateStatusLabel();
    $statusCls = $isToday ? '--today' : ($isPast ? '--past' : '--future');
    $cardCls = $isPast ? '--past' : ($isToday ? '--today' : '');
@endphp
<div class="mc-card {{ $cardCls }}">
    <a href="{{ route('menu.drafts.edit', $draft) }}" class="mc-card-hit" aria-label="Open {{ $draft->title }}"></a>

    <div class="mc-card-inner">

        {{-- Name + updated --}}
        <div class="mc-card-top">
            <div class="mc-card-name">{{ $draft->title }}</div>
            <span class="mc-card-age">Updated {{ $draft->updated_at->diffForHumans(null, true) }} ago</span>
        </div>

        {{-- Venue · Date · Pax, one compact line --}}
        @if($draft->venue || $draft->event_date || $totalPax)
        <div class="mc-card-meta">
            @if($draft->venue){{ $draft->venue }}@endif
            @if($draft->event_date)<span class="sep">&middot;</span>{{ $draft->formattedDate() }}@endif
            @if($totalPax)<span class="sep">&middot;</span><span class="mc-card-pax">{{ number_format($totalPax) }} pax</span>@endif
        </div>
        @else
        <div class="mc-card-meta" style="font-style:italic;color:var(--mc-faint)">No venue or date set</div>
        @endif

        @if($statusLabel)
        <span class="mc-status {{ $statusCls }}">{{ $statusLabel }}</span>
        @endif

        {{-- Meals --}}
        <div class="mc-meals-label">Meals</div>
        <div class="mc-sec-summary">
            @forelse($showSecs as $sec)
            @php
                $icon = $secIconMap[$sec['key'] ?? ''] ?? 'bi-egg-fried';
            @endphp
            <div class="mc-sec-line">
                <i class="bi {{ $icon }} mc-sec-icon"></i>
                <span class="mc-sec-name">{{ $sec['label_en'] ?? 'Section' }}</span>
                <span class="mc-sec-count">{{ count($sec['items']) }} {{ Str::plural('item', count($sec['items'])) }}</span>
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
        <div class="dropdown">
            <button type="button" class="mc-more-btn" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="More actions for {{ $draft->title }}">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end mc-more-menu">
                <li>
                    <form method="POST" action="{{ route('menu.drafts.duplicate', $draft) }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-copy me-2"></i>Duplicate Menu
                        </button>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('menu.drafts.destroy', $draft) }}"
                          onsubmit="return confirm('Delete &quot;{{ addslashes($draft->title) }}&quot;?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-trash3 me-2"></i>Delete Menu
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
@endforeach
