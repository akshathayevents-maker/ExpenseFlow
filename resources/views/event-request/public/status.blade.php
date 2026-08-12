@php
    $statusChip = match($eventRequest->status) {
        'rejected' => 'erp-chip-crit',
        'scheduled' => 'erp-chip-good',
        default => 'erp-chip-warn',
    };
@endphp
<x-event-request.public-layout title="Track your request">

    @if(session('justSubmitted'))
        <div class="erp-card erp-status-banner">
            <div class="title"><i class="bi bi-check-circle-fill me-1"></i>Request submitted successfully</div>
            <div class="body">Your reference number is <strong>{{ session('reference') }}</strong>. Save this link to track your status.</div>
        </div>
    @endif

    <div class="erp-card erp-status-card">
        <div class="erp-eyebrow">Reference {{ $eventRequest->referenceNumber() }}</div>
        <div class="erp-status-head">
            <h1 class="erp-status-title">{{ $eventRequest->event_name ?: 'Your Event Request' }}</h1>
            <span class="erp-chip {{ $statusChip }}">{{ $eventRequest->statusLabel() }}</span>
        </div>

        <div class="erp-status-grid">
            <div class="erp-status-kv"><div class="k">Event Date</div><div class="v">{{ $eventRequest->event_date?->format('d M Y') ?? '—' }}</div></div>
            <div class="erp-status-kv"><div class="k">Meal</div><div class="v">{{ $eventRequest->mealTypeLabel() }}</div></div>
            <div class="erp-status-kv"><div class="k">Guests</div><div class="v">{{ number_format($eventRequest->guest_count) }}</div></div>
            <div class="erp-status-kv"><div class="k">Menu</div><div class="v">{{ $eventRequest->menuTypeLabel() }}</div></div>
        </div>

        @if($eventRequest->status === 'need_changes' && $eventRequest->admin_comment)
            <div class="erp-status-note erp-status-note-warn">
                <div class="heading"><i class="bi bi-pencil-square me-1"></i>Changes requested</div>
                <div class="body">{{ $eventRequest->admin_comment }}</div>
                <a href="{{ route('event-request.public.show', $token) }}" class="erp-btn erp-btn-gold mt-3">Edit &amp; resubmit</a>
            </div>
        @endif

        @if($eventRequest->status === 'rejected' && $eventRequest->admin_comment)
            <div class="erp-status-note erp-status-note-crit">
                <div class="heading">Note from the team</div>
                <div class="body">{{ $eventRequest->admin_comment }}</div>
            </div>
        @endif

        <div class="erp-status-items">
            <div class="erp-status-items-title">Selected items ({{ $eventRequest->items->count() }})</div>
            @foreach($eventRequest->items as $item)
                <div class="erp-status-item-row">
                    <span class="name">{{ $item->name_snapshot }}</span>
                    <span class="price text-muted">₹{{ number_format($item->price_per_person_snapshot, 0) }}/person</span>
                </div>
            @endforeach
        </div>

        <div class="erp-status-total-bar">
            <span class="fw-bold small text-muted">Estimated total</span>
            <strong style="font-size:1.15rem">₹{{ number_format($eventRequest->estimated_total, 0) }}</strong>
        </div>

        <div class="erp-status-actions">
            <button type="button" class="erp-btn erp-btn-ghost" id="downloadSummaryBtn"><i class="bi bi-download"></i> Download summary</button>
        </div>
    </div>
</x-event-request.public-layout>
