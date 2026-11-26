<x-admin-layout title="AI Recipe Generator">
@include('inventory._styles')
<div class="ra-page">
    @include('kitchen.recipes.ai._nav', ['active' => 'generate'])
    <div class="ra-head"><div style="flex:1;min-width:0">
        <h1>AI Recipe Generator</h1>
        <p>Writes commercial-kitchen recipes in batches and saves them to the library as <b>AI generated — not verified</b>.
            Nothing reaches the kitchen until an admin verifies it.</p></div></div>

    @php
        $workerOk = $queue['worker_alive'];
        $canGenerate = $configured && $workerOk;
        $why = ! $configured ? 'Add ANTHROPIC_API_KEY to enable AI generation.' : (! $workerOk ? 'Start a queue worker — batches only progress while one is running.' : null);
    @endphp
    <section class="ra-card rg-ready" aria-label="Generator status">
        <h2><i class="bi bi-activity" style="color:var(--ef-gold)"></i> Status
            <span class="ic-chip {{ $canGenerate ? 'ic-chip--ok' : 'ic-chip--warn' }}" style="margin-left:auto">{{ $canGenerate ? 'Ready' : 'Not ready' }}</span></h2>
        <div class="rg-checks">
            <div class="rg-check {{ $configured ? '--ok' : '--bad' }}">
                <i class="bi {{ $configured ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                <div><b>AI provider</b><span>{{ $provider === 'anthropic' ? 'Anthropic Claude' : $provider }} · {{ $model }}</span>
                    <span>{{ $configured ? 'API key configured' : 'API key missing' }}</span></div>
            </div>
            <div class="rg-check {{ $workerOk ? '--ok' : '--bad' }}">
                <i class="bi {{ $workerOk ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                <div><b>Background queue</b><span>Connection: {{ $queue['connection'] }}{{ $queue['sync'] ? ' (runs immediately)' : '' }}</span>
                    <span>@if($queue['sync']) No worker needed @elseif($workerOk) Worker running (last seen {{ $queue['worker_seen']->diffForHumans() }}) @else No worker seen{{ $queue['worker_seen'] ? ' since ' . $queue['worker_seen']->diffForHumans() : '' }} @endif
                        · {{ $queue['pending'] }} jobs waiting{{ $queue['oldest_pending_minutes'] ? ' (oldest ' . $queue['oldest_pending_minutes'] . ' min)' : '' }} · {{ $queue['failed'] }} failed</span></div>
            </div>
            <div class="rg-check --ok"><i class="bi bi-box-seam"></i>
                <div><b>Curated starter set</b><span>{{ $starterTotal }} hand-written recipes · {{ $starterMissing }} not imported yet</span><span>No AI needed</span></div>
            </div>
        </div>
        @if(! $configured || ! $workerOk)
            <details class="rg-how" @if(! $configured) open @endif>
                <summary>How to get ready</summary>
                <ol>
                    @unless($configured)
                        <li>Create an API key in the Anthropic Console (console.anthropic.com → API keys).</li>
                        <li>Add it to the server <code>.env</code>: <code>ANTHROPIC_API_KEY=…</code> (never commit it), then run <code>php artisan config:clear</code>.</li>
                    @endunless
                    @unless($workerOk)
                        <li>Start a queue worker in the project folder and keep it running: <code>{{ $workerCommand }}</code>
                            <span class="ra-hint">(production: supervisor already runs workers — check they use <code>--timeout=600</code>)</span></li>
                    @endunless
                    <li>Reload this page — the status above turns green.</li>
                </ol>
            </details>
        @endif
    </section>
    @push('styles')
    <style>
    .rg-checks{display:grid;gap:8px;grid-template-columns:1fr}
    @media (min-width:860px){.rg-checks{grid-template-columns:repeat(3,minmax(0,1fr))}}
    .rg-check{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border-radius:12px;background:var(--ef-bg-subtle,#f4f1ec);min-width:0}
    .rg-check>.bi{font-size:1.2rem;line-height:1.2}
    .rg-check.--ok>.bi{color:var(--ef-emerald)}
    .rg-check.--bad>.bi{color:var(--ef-danger)}
    .rg-check div{display:flex;flex-direction:column;min-width:0;font-size:.84rem}
    .rg-check span{color:var(--ef-muted);overflow-wrap:anywhere}
    .rg-how{margin-top:10px;font-size:.88rem}
    .rg-how summary{cursor:pointer;font-weight:750;min-height:36px;display:flex;align-items:center}
    .rg-how ol{margin:4px 0 0;padding-left:20px;display:flex;flex-direction:column;gap:6px}
    .rg-how code{background:rgba(20,20,18,.06);padding:1px 6px;border-radius:6px;overflow-wrap:anywhere}
    </style>
    @endpush

    <section class="ra-card" id="starter" aria-label="Curated starter recipes">
        <h2><i class="bi bi-box-seam" style="color:var(--ef-gold)"></i> Curated starter recipes <span class="ra-hint" style="font-weight:500">no AI needed</span></h2>
        <p class="ra-hint" style="margin:-4px 0 10px">{{ $starterTotal }} common South Indian catering recipes written by hand for 100 people —
            idli, pongal, upma, sambar, rasam, variety rice, biryani, kurma, poriyal, avial, pakoda, kesari, payasam, coffee and more.
            They are added as <b>Curated starter — not verified</b> and stay hidden from the kitchen until you check and verify each one.
            Existing dishes are skipped, so it is safe to run again.</p>
        <form method="POST" action="{{ route('kitchen.recipes.ai.starter') }}" data-ic-form>
            @csrf
            <button class="ic-btn ic-btn--gold" type="submit" @disabled($starterMissing === 0)><i class="bi bi-download"></i>
                {{ $starterMissing ? 'Add ' . $starterMissing . ' starter recipes' : 'All starter recipes added' }}</button>
        </form>
    </section>

    @if($errors->any())<div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form class="ra-card" method="POST" action="{{ route('kitchen.recipes.ai.store') }}" data-ic-form>
        @csrf
        <h2><i class="bi bi-stars" style="color:var(--ef-gold)"></i> Generate recipes</h2>
        <div class="ra-grid">
            <label class="ra-f"><span class="ra-cap">Category</span>
                <select class="ic-select" name="category" required>
                    @foreach($categories as $c)<option value="{{ $c }}" @selected(old('category', 'Main Course') === $c)>{{ $c }}</option>@endforeach
                </select></label>
            <label class="ra-f"><span class="ra-cap">Cuisine</span>
                <select class="ic-select" name="cuisine">
                    @foreach($cuisines as $c)<option value="{{ $c }}" @selected(old('cuisine', 'Tamil Nadu') === $c)>{{ $c }}</option>@endforeach
                </select></label>
            <label class="ra-f"><span class="ra-cap">Type</span>
                <select class="ic-select" name="food_type" required>
                    @foreach($foodTypes as $k => $l)<option value="{{ $k }}" @selected(old('food_type', 'veg') === $k)>{{ $l }}</option>@endforeach
                </select></label>
            <label class="ra-f"><span class="ra-cap">Batch for (people)</span>
                <input class="ic-input" type="number" name="yield_people" min="5" max="2000" list="raYields" value="{{ old('yield_people', 100) }}" inputmode="numeric" required>
                <datalist id="raYields">@foreach($yields as $y)<option value="{{ $y }}">@endforeach</datalist></label>
            <label class="ra-f --wide"><span class="ra-cap">Dishes (optional — one per line)</span>
                <textarea class="ic-input" name="dishes" rows="3" placeholder="Leave empty to let the AI choose dishes that are not in the library yet&#10;e.g. Ven Pongal&#10;Rava Kesari">{{ old('dishes') }}</textarea></label>
            <label class="ra-f"><span class="ra-cap">How many (if no list)</span>
                <input class="ic-input" type="number" name="count" min="1" max="{{ $max }}" value="{{ old('count', 10) }}" inputmode="numeric"></label>
            <label class="ra-f --wide"><span class="ra-cap">Notes for the AI (optional)</span>
                <input class="ic-input" name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="e.g. less oil, no onion-garlic, wedding style"></label>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:12px">
            <button class="ic-btn ic-btn--gold" type="submit" @disabled(! $configured)><i class="bi bi-stars"></i> Generate</button>
            @if($why)<span class="ra-hint" style="color:#7D5218;font-weight:650"><i class="bi bi-info-circle"></i> {{ $why }}</span>@endif
            <span class="ra-hint">Up to {{ $max }} per batch · one recipe per request · runs in the background · model {{ $model }}</span>
        </div>
    </form>

    <div class="ra-card">
        <h2><i class="bi bi-collection" style="color:var(--ef-gold)"></i> Generate Initial Recipe Library <span class="ra-hint" style="font-weight:500">with AI</span></h2>
        <p class="ra-hint" style="margin:-4px 0 10px">A starter set of common catering dishes, generated group by group. Dishes already in the library are skipped, so it is safe to run again.</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
            @foreach($plan as $g)
                <span class="ic-chip {{ $g['missing'] ? '' : 'ic-chip--ok' }}">{{ $g['group'] }} · {{ $g['missing'] ? $g['missing'] . ' to write' : 'done' }}</span>
            @endforeach
        </div>
        <form method="POST" action="{{ route('kitchen.recipes.ai.initial-plan') }}" data-ic-form style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            @csrf
            <label class="ra-f"><span class="ra-cap">Batch for (people)</span>
                <input class="ic-input" type="number" name="yield_people" min="5" max="2000" value="100" style="max-width:140px" inputmode="numeric"></label>
            <button class="ic-btn" type="submit" @disabled(! $configured || $plan->sum('missing') === 0)><i class="bi bi-play-circle"></i>
                Generate {{ $plan->sum('missing') }} missing recipes</button>
            @if($why)<span class="ra-hint" style="color:#7D5218;font-weight:650">{{ $why }}</span>@endif
        </form>
    </div>

    <div class="ra-card">
        <h2><i class="bi bi-clock-history" style="color:var(--ef-gold)"></i> Generation history</h2>
        @forelse($batches as $b)
            <a class="ra-row" href="{{ route('kitchen.recipes.ai.batch', $b) }}">
                <span class="ra-row-b"><span class="ra-row-t">{{ $b->label ?: 'Batch #' . $b->id }}</span>
                    <span class="ra-row-s">{{ $b->created_at->format('d M, h:i A') }} · {{ $b->creator?->name }} ·
                        {{ $b->generated_count }}/{{ $b->requested_count }} written @if($b->failed_count) · {{ $b->failed_count }} failed @endif @if($b->duplicate_count) · {{ $b->duplicate_count }} duplicates @endif</span></span>
                @include('inventory._status', ['label' => $b->statusLabel(), 'tone' => $b->statusTone()])
            </a>
        @empty
            <p class="ra-hint">No batches yet.</p>
        @endforelse
        @if($batches->hasPages())<div style="margin-top:8px">{{ $batches->links() }}</div>@endif
    </div>
</div>
</x-admin-layout>
