<x-admin-layout title="Material Requests">
@include('inventory._styles')

@push('styles')
<style>
/* Material requests list — same mobile-first language as the bill review page (--ef-* tokens + ic-*). */
.mr-page{max-width:1100px;margin:0 auto}
.mr-head{display:flex;align-items:center;gap:10px;margin:2px 0 14px}
.mr-head h1{flex:1;min-width:0;margin:0;font-size:1.15rem;font-weight:800;letter-spacing:-.01em;color:var(--ef-ink)}
.mr-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
@media (max-width:519.98px){.mr-head p{display:none}.mr-ico{display:none!important}.mr-card{padding:12px}.mr-go{display:none}}
.mr-head-act{display:none}
@media (min-width:768px){.mr-head h1{font-size:1.35rem}.mr-head-act{display:inline-flex}}

.mr-tabs{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;margin:0 -16px 12px;padding:2px 16px 4px;scroll-snap-type:x proximity}
.mr-tabs::-webkit-scrollbar{display:none}
@media (min-width:768px){.mr-tabs{margin:0 0 14px;padding:2px 0 4px;flex-wrap:wrap}}
.mr-tab{flex:0 0 auto;scroll-snap-align:start;display:inline-flex;align-items:center;gap:7px;min-height:40px;padding:6px 14px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-muted);font-weight:700;font-size:.88rem;text-decoration:none;white-space:nowrap}
.mr-tab:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.mr-tab.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.mr-badge{min-width:22px;height:22px;padding:0 6px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:.74rem;font-weight:800;background:rgba(216,154,61,.18);color:#7D5218}
.mr-tab.--on .mr-badge{background:var(--ef-gold);color:#fff}

.mr-card{display:flex;align-items:flex-start;gap:12px;padding:12px 14px;margin-bottom:10px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:var(--ef-shadow);color:var(--ef-ink);text-decoration:none;border-left:3px solid var(--mr-tone,var(--ef-border))}
.mr-card:hover{border-color:var(--ef-gold);border-left-color:var(--mr-tone,var(--ef-gold));color:var(--ef-ink)}
.mr-card:focus-visible{outline:3px solid var(--ef-gold-soft);outline-offset:2px}
.mr-card[data-tone=warn]{--mr-tone:var(--ef-amber)}
.mr-card[data-tone=info]{--mr-tone:#3b6ea5}
.mr-card[data-tone=ok]{--mr-tone:var(--ef-emerald)}
.mr-card[data-tone=bad]{--mr-tone:var(--ef-danger)}
.mr-ico{flex:0 0 auto;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:var(--ef-bg-subtle);color:var(--ef-gold);font-size:1.15rem}
.mr-main{flex:1;min-width:0}
.mr-top{display:flex;flex-wrap:wrap;align-items:flex-start;gap:4px 10px}
.mr-title{flex:1 1 160px;min-width:0;font-weight:750;font-size:.98rem;line-height:1.3;overflow-wrap:anywhere;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.mr-chips{display:flex;flex-wrap:wrap;gap:4px}
@media (min-width:768px){.mr-chips{margin-left:auto}}
.mr-chips .ic-chip{font-size:.72rem;padding:3px 9px}
.mr-for{font-size:.84rem;color:var(--ef-muted);margin-top:2px;overflow-wrap:anywhere}
.mr-meta{display:flex;flex-wrap:wrap;gap:3px 12px;margin-top:6px;font-size:.8rem;color:var(--ef-muted)}
.mr-meta span{display:inline-flex;align-items:center;gap:5px;min-width:0}
.mr-meta .bi{color:var(--ef-faint)}
.mr-meta .--soon{color:#7D5218;font-weight:700}
.mr-meta .--soon .bi{color:var(--ef-amber)}
.mr-ref{font-variant-numeric:tabular-nums;color:var(--ef-faint)}
.mr-go{flex:0 0 auto;align-self:center;color:var(--ef-faint)}

.mr-empty{text-align:center;padding:36px 16px;border:1px dashed var(--ef-border);border-radius:14px;background:var(--ef-surface);color:var(--ef-muted)}
.mr-empty .bi{font-size:1.8rem;color:var(--ef-faint)}
.mr-empty p{margin:6px 0 14px;font-size:.92rem}
.mr-pager{margin-top:6px;overflow-x:auto}

.mr-bar{display:flex;margin-top:12px;padding-bottom:calc(10px + env(safe-area-inset-bottom,0px))}
.mr-bar .ic-btn{flex:1}
@media (min-width:768px){.mr-bar{display:none}}
</style>
@endpush

@php
    $tabIcons = ['mine' => 'bi-person', 'approve' => 'bi-check2-square', 'review' => 'bi-clipboard-data', 'handover' => 'bi-box-seam', 'out' => 'bi-arrow-left-right', 'all' => 'bi-list-ul'];
    $empty = [
        'mine' => 'You have not requested any materials yet.',
        'approve' => 'No requests are waiting for approval.',
        'review' => 'No usage reports need review.',
        'handover' => 'Nothing is waiting to be handed over.',
        'out' => 'No materials are out with the kitchen right now.',
        'all' => 'No material requests yet.',
    ][$tab] ?? 'Nothing here.';
    $srcIcon = fn ($r) => match ($r->source) { 'meal' => 'bi-egg-fried', 'hall' => 'bi-building', default => 'bi-basket' };
@endphp

<div class="mr-page">
    <div class="mr-head">
        <div style="flex:1;min-width:0">
            <h1>Material requests</h1>
            <p>Request stock for cooking, approve it, hand it over and record what came back.</p>
        </div>
        <a href="{{ route('inventory.material-requests.create') }}" class="ic-btn ic-btn--gold mr-head-act"><i class="bi bi-plus-lg"></i> Request materials</a>
    </div>

    @if(count($tabs) > 1)
        <nav class="mr-tabs" aria-label="Request lists">
            @foreach($tabs as $k => $l)
                <a class="mr-tab {{ $tab === $k ? '--on' : '' }}" href="{{ route('inventory.material-requests.index', ['tab' => $k]) }}" @if($tab === $k) aria-current="page" @endif>
                    <i class="bi {{ $tabIcons[$k] ?? 'bi-dot' }}"></i>{{ $l }}
                    @if(($counts[$k] ?? 0) > 0)<span class="mr-badge" aria-label="{{ $counts[$k] }} waiting">{{ $counts[$k] }}</span>@endif
                </a>
            @endforeach
        </nav>
    @endif

    @forelse($requests as $r)
        @php
            $tone = \App\Models\MaterialRequest::statusTone($r->status);
            $due  = $r->required_date;
            $dueLabel = $due->isToday() ? 'Today' : ($due->isTomorrow() ? 'Tomorrow' : $due->format('D d M'));
            $soon = in_array($r->status, ['submitted', 'approved'], true) && $due->lte(now()->addDay()->endOfDay());
        @endphp
        <a class="mr-card" data-tone="{{ $tone }}" href="{{ route('inventory.material-requests.show', $r) }}">
            <span class="mr-ico" aria-hidden="true"><i class="bi {{ $srcIcon($r) }}"></i></span>
            <div class="mr-main">
                <div class="mr-top">
                    <div class="mr-title">{{ $r->preparation_name ?: $r->forLabel() }}</div>
                    <div class="mr-chips">
                        @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => $tone])
                        @if($r->reconciliation_status === 'pending_review')<span class="ic-chip ic-chip--warn"><i class="bi bi-clipboard-data"></i>Usage review</span>@endif
                    </div>
                </div>
                @if($r->preparation_name)<div class="mr-for">{{ $r->forLabel() }}</div>@endif
                <div class="mr-meta">
                    <span class="{{ $soon ? '--soon' : '' }}"><i class="bi bi-calendar3"></i>Needed {{ $dueLabel }}{{ $r->required_time ? ' · ' . \Carbon\Carbon::parse($r->required_time)->format('h:i A') : '' }}</span>
                    <span><i class="bi bi-box"></i>{{ $r->items_count }} {{ \Illuminate\Support\Str::plural('item', $r->items_count) }}</span>
                    @if($r->guest_count)<span><i class="bi bi-people"></i>{{ $r->guest_count }} people</span>@endif
                    @if($r->requester)<span><i class="bi bi-person"></i>{{ $r->requester->name }}</span>@endif
                    <span class="mr-ref">{{ $r->reference() }}</span>
                </div>
            </div>
            <i class="bi bi-chevron-right mr-go" aria-hidden="true"></i>
        </a>
    @empty
        <div class="mr-empty">
            <i class="bi bi-inbox"></i>
            <p>{{ $empty }}</p>
            @if($tab === 'mine')
                <a href="{{ route('inventory.material-requests.create') }}" class="ic-btn ic-btn--sm"><i class="bi bi-plus-lg"></i> Request materials</a>
            @endif
        </div>
    @endforelse

    @if($requests->hasPages())<div class="mr-pager">{{ $requests->links() }}</div>@endif

    @if($requests->isNotEmpty() || $tab !== 'mine')
        <div class="mr-bar ic-sticky">
            <a href="{{ route('inventory.material-requests.create') }}" class="ic-btn ic-btn--gold"><i class="bi bi-plus-lg"></i> Request materials</a>
        </div>
    @endif
</div>
</x-admin-layout>
