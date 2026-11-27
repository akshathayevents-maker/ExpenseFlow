<x-admin-layout title="Purchase Plans">
@include('inventory.plans._style')
@php
    $tiles = [
        'mine' => ['My open plans', 'bi-person', '#2c5784', 'rgba(59,110,165,.06)'],
        'approve' => ['Waiting approval', 'bi-hourglass-split', '#9a6413', 'rgba(200,144,42,.08)'],
        'buy' => ['To buy / receive', 'bi-truck', 'var(--ef-emerald)', 'rgba(30,122,60,.05)'],
        'done' => ['Closed', 'bi-check2-all', '#5f6b5a', 'transparent'],
        'all' => ['All', 'bi-collection', 'var(--ef-ink)', 'transparent'],
    ];
@endphp
@push('styles')
<style>
.pl-q{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden}
.pl-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;min-width:0;background:linear-gradient(var(--tint),var(--tint)),var(--ef-surface)}
.pl-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pl-qi small .bi{color:var(--acc)}
.pl-qi strong{font-size:1.35rem;font-weight:800;color:var(--acc);line-height:1.15}
.pl-qi.--zero strong{color:var(--ef-faint)}
.pl-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
@media (max-width:719.98px){.pl-q{grid-template-columns:repeat(2,minmax(0,1fr))}.pl-qi[data-k=all]{grid-column:1/-1}.pl-qi strong{font-size:1.15rem}}
.pl-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;padding:12px 14px;border-top:1px solid var(--ef-border);text-decoration:none;color:var(--ef-ink)}
.pl-row:first-child{border-top:0}
.pl-row[data-status=submitted]{border-left:3px solid #d66e28}
.pl-row.--review{background:rgba(214,110,40,.05)}
.pl-row[data-status=rejected]{border-left:3px solid var(--ef-danger)}
.pl-row[data-status=approved]{border-left:3px solid var(--ef-emerald)}
.pl-qi[data-k=approve]:not(.--zero) small::after{content:"";width:7px;height:7px;border-radius:50%;background:var(--acc);margin-left:auto;animation:plPulse 1.6s ease-in-out infinite}
@keyframes plPulse{0%,100%{opacity:1}50%{opacity:.25}}
.pl-row:hover{background:var(--ef-bg-subtle);color:var(--ef-ink)}
.pl-t{display:block;font-weight:750;font-size:.94rem;overflow-wrap:anywhere}
.pl-s{display:block;font-size:.76rem;color:var(--ef-muted)}
.pl-empty{padding:34px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.pl-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
</style>
@endpush
<div class="pl">
    <header class="pl-head">
        <div style="flex:1 1 280px;min-width:0">
            <h1>Purchase plans</h1>
            <p>List what needs buying, send it for approval, then buy and receive it — stock only changes when goods are received.</p>
            @if(auth()->user()->isAdmin() && $counts['approve'] && $tab !== 'approve')<p style="margin-top:6px"><a href="{{ route('inventory.plans.index', ['tab' => 'approve']) }}" style="color:#b4531c;font-weight:800"><i class="bi bi-bell-fill"></i> {{ $counts['approve'] }} {{ Str::plural('plan', $counts['approve']) }} waiting for your approval →</a></p>@endif
        </div>
        <div class="pl-acts">
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.purchase-plans.index') }}" class="pl-btn"><i class="bi bi-graph-down"></i> Low-stock suggestions</a>@endif
            <a href="{{ route('inventory.plans.create') }}" class="pl-btn --main"><i class="bi bi-plus-lg"></i> New plan</a>
        </div>
    </header>
    @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif

    <nav class="pl-q" aria-label="Plans">
        @foreach($tiles as $k => [$l, $i, $acc, $tint])
            @continue(! $all && $k === 'approve' && ! $counts['approve'])
            <a href="{{ route('inventory.plans.index', ['tab' => $k]) }}" data-k="{{ $k }}" class="pl-qi {{ $tab === $k ? '--on' : '' }} {{ $counts[$k] ? '' : '--zero' }}" style="--acc:{{ $acc }};--tint:{{ $tint }}">
                <small><i class="bi {{ $i }}"></i>{{ $l }}</small><strong>{{ $counts[$k] }}</strong>
            </a>
        @endforeach
    </nav>

    <section class="pl-panel" aria-label="Plans">
        @forelse($plans as $p)
            @php $review = $p->status === 'submitted' && auth()->user()->isAdmin(); @endphp
            <a class="pl-row {{ $review ? '--review' : '' }}" href="{{ route('inventory.plans.show', $p) }}" data-plan="{{ $p->id }}" data-status="{{ $p->status }}">
                <span style="min-width:0">
                    <span class="pl-t">{{ $p->title }}</span>
                    <span class="pl-s">{{ $p->reference() }} · {{ $p->items_count }} {{ Str::plural('item', $p->items_count) }} · needed {{ $p->planned_date?->format('d M') }}@if($all) · {{ $p->creator?->name }}@endif @if($p->purchases_count) · {{ $p->purchases_count }} {{ Str::plural('purchase', $p->purchases_count) }}@endif</span>
                </span>
                <span style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end">
                    <span class="pl-chip" data-s="{{ $p->status }}">{{ $p->statusLabel() }}</span>
                    @if($review)<span class="pl-btn --sm --main"><i class="bi bi-check2-square"></i> Review</span>@endif
                </span>
            </a>
        @empty
            <div class="pl-empty"><i class="bi bi-cart"></i>No plans here. <a href="{{ route('inventory.plans.create') }}">Start a purchase plan</a></div>
        @endforelse
        @if($plans->hasPages())<div style="padding:10px 14px;border-top:1px solid var(--ef-border)">{{ $plans->links() }}</div>@endif
    </section>
</div>
</x-admin-layout>
