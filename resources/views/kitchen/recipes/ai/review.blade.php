<x-admin-layout title="Recipe Review Queue">
@include('inventory._styles')
@push('styles')
<style>
.rq-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}
.rq-tab{display:inline-flex;align-items:center;gap:6px;min-height:36px;padding:4px 12px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:.84rem;font-weight:700;color:var(--ef-muted);text-decoration:none}
.rq-tab.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.rq-tab b{font-variant-numeric:tabular-nums}
.rq-filters{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px}
@media (min-width:768px){.rq-filters{grid-template-columns:2fr 1fr 1fr 1fr auto}}
.rq-filters .ic-input,.rq-filters .ic-select{min-height:44px;font-size:16px}
.rq-item{display:grid;grid-template-columns:minmax(0,1fr);gap:6px 12px;align-items:center;padding:11px 4px;border-top:1px solid var(--ef-border)}
@media (min-width:640px){.rq-item{grid-template-columns:minmax(0,1fr) auto}}
.rq-item:first-child{border-top:0}
.rq-name{font-weight:750;font-size:.96rem;color:var(--ef-ink);text-decoration:none;overflow-wrap:anywhere}
.rq-name:hover{text-decoration:underline}
.rq-meta{display:flex;flex-wrap:wrap;gap:4px 10px;font-size:.78rem;color:var(--ef-muted);grid-column:1/-1}
.rq-meta .--warn{color:#7D5218;font-weight:700}
.rq-acts{display:flex;gap:6px;flex-wrap:wrap}
@media (min-width:640px){.rq-acts{justify-content:flex-end}}
.rq-meta{order:2}@media (min-width:640px){.rq-meta{order:0}}
.rq-veg{display:inline-block;width:12px;height:12px;border:1.5px solid #1f8a4c;border-radius:2px;position:relative;vertical-align:-1px}
.rq-veg::after{content:'';position:absolute;inset:2px;border-radius:50%;background:#1f8a4c}
.rq-veg.--non_veg{border-color:#a3341f}.rq-veg.--non_veg::after{background:#a3341f}
.rq-veg.--egg{border-color:#c98a12}.rq-veg.--egg::after{background:#c98a12}
</style>
@endpush
@php
    $tabs = ['to_review' => 'To review', 'ai_generated' => 'AI generated', 'needs_review' => 'Needs review', 'reviewed' => 'Reviewed', 'verified' => 'Verified', 'archived' => 'Archived'];
    $n = fn ($k) => $k === 'to_review' ? ($counts['ai_generated'] ?? 0) + ($counts['needs_review'] ?? 0) : ($counts[$k] ?? 0);
@endphp
<div class="ra-page">
    @include('kitchen.recipes.ai._nav', ['active' => 'review'])
    <div class="ra-head"><div><h1>Review queue</h1>
        <p>Check each recipe, fix quantities and stock-item links, then <b>Verify</b>. Only verified recipes are shown to the kitchen.</p></div></div>

    <nav class="rq-tabs" aria-label="Status">
        @foreach($tabs as $k => $l)
            <a class="rq-tab {{ $status === $k ? '--on' : '' }}" href="{{ route('kitchen.recipes.review', array_merge(request()->except('page'), ['status' => $k])) }}">{{ $l }} <b>{{ $n($k) }}</b></a>
        @endforeach
    </nav>

    <form class="rq-filters" method="GET">
        <input type="hidden" name="status" value="{{ $status }}">
        <input class="ic-input" type="search" name="q" value="{{ request('q') }}" placeholder="Search recipe" aria-label="Search recipe">
        <select class="ic-select" name="category" aria-label="Category"><option value="">All categories</option>
            @foreach($categories as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach</select>
        <select class="ic-select" name="cuisine" aria-label="Cuisine"><option value="">All cuisines</option>
            @foreach($cuisines as $c)<option @selected(request('cuisine') === $c)>{{ $c }}</option>@endforeach</select>
        <select class="ic-select" name="food_type" aria-label="Type"><option value="">Veg / non-veg</option>
            @foreach($foodTypes as $k => $l)<option value="{{ $k }}" @selected(request('food_type') === $k)>{{ $l }}</option>@endforeach</select>
        <button class="ic-btn" type="submit">Filter</button>
    </form>

    <div class="ra-card">
        @forelse($recipes as $r)
            <div class="rq-item">
                <div style="min-width:0">
                    @if($r->food_type)<span class="rq-veg --{{ $r->food_type }}" title="{{ $foodTypes[$r->food_type] ?? '' }}"></span>@endif
                    <a class="rq-name" href="{{ route('kitchen.recipes.show', $r) }}">{{ $r->name }}</a>
                    @include('kitchen.recipes._verify-badge', ['recipe' => $r])
                </div>
                <div class="rq-acts">
                    <a class="ic-btn ic-btn--sm" href="{{ route('kitchen.recipes.show', $r) }}"><i class="bi bi-eye"></i> Review</a>
                    @if(in_array($r->verification_status, ['ai_generated', 'needs_review'], true))
                        <form method="POST" action="{{ route('kitchen.recipes.reviewed', $r) }}" data-ic-form>@csrf
                            <button class="ic-btn ic-btn--sm" type="submit" title="Mark reviewed"><i class="bi bi-check2"></i><span class="visually-hidden"> Mark reviewed</span></button></form>
                    @endif
                </div>
                <div class="rq-meta">
                    <span>{{ $r->source === 'ai' ? 'AI' : ($r->source === 'starter' ? 'Starter set' : 'Manual') }}</span>
                    <span>{{ $r->category }}</span>@if($r->cuisine)<span>{{ $r->cuisine }}</span>@endif
                    <span>{{ rtrim(rtrim(number_format($r->yield_per_batch, 2), '0'), '.') }} {{ $r->yield_unit }}</span>
                    <span>{{ $r->ingredients_count }} ingredients</span>
                    @if($r->unmapped_count)<span class="--warn"><i class="bi bi-link-45deg"></i> {{ $r->unmapped_count }} not linked to stock</span>@endif
                    @if($r->duplicateOf)<span class="--warn"><i class="bi bi-files"></i> similar to {{ $r->duplicateOf->name }}</span>@endif
                    @if($r->generationItem?->batch)<span>{{ $r->generationItem->batch->label }}</span>@endif
                    <span>{{ $r->created_at->format('d M') }}</span>
                </div>
            </div>
        @empty
            <p class="ra-hint" style="margin:6px 0">Nothing here. @if($status === 'to_review')<a href="{{ route('kitchen.recipes.ai.index') }}">Generate recipes</a>@endif</p>
        @endforelse
        @if($recipes->hasPages())<div style="margin-top:8px">{{ $recipes->links() }}</div>@endif
    </div>
</div>
</x-admin-layout>
