{{-- Draft card grid partial — used by index.blade.php (initial load) and AJAX infinite scroll --}}
@foreach($drafts as $draft)
<div class="mc-card">
    <div class="mc-card-title">{{ $draft->title }}</div>
    <div class="mc-card-meta">
        @if($draft->venue)
            <span><i class="bi bi-geo-alt"></i> {{ $draft->venue }}</span>
        @endif
        @if($draft->event_date)
            <span><i class="bi bi-calendar3"></i> {{ $draft->formattedDate() }}</span>
        @endif
        @if($draft->people_count)
            <span><i class="bi bi-people"></i> {{ number_format($draft->people_count) }} pax</span>
        @endif
        <span><i class="bi bi-clock"></i> {{ $draft->updated_at->diffForHumans() }}</span>
    </div>
    <div class="mc-card-count">{{ $draft->totalItems() }} items</div>
    <div class="mc-card-actions">
        <a href="{{ route('menu.drafts.edit', $draft) }}" class="mc-btn-open">
            <i class="bi bi-pencil me-1"></i> Open
        </a>
        <form method="POST" action="{{ route('menu.drafts.duplicate', $draft) }}">
            @csrf
            <button type="submit" class="mc-btn-dupe" title="Duplicate">
                <i class="bi bi-copy"></i>
            </button>
        </form>
        <form method="POST" action="{{ route('menu.drafts.destroy', $draft) }}"
              onsubmit="return confirm('Delete this draft?')">
            @csrf @method('DELETE')
            <button type="submit" class="mc-btn-del" title="Delete draft">
                <i class="bi bi-trash3"></i>
            </button>
        </form>
    </div>
</div>
@endforeach
