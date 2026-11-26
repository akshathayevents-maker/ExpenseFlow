<x-admin-layout :title="'Generation · ' . ($batch->label ?: '#' . $batch->id)">
@include('inventory._styles')
@php
    $itemLabel = ['pending' => ['Queued', 'info'], 'processing' => ['Writing…', 'info'], 'done' => ['Written', 'ok'],
        'duplicate' => ['Already exists', 'warn'], 'failed' => ['Failed', 'bad'], 'cancelled' => ['Cancelled', 'bad']];
@endphp
<div class="ra-page">
    @include('kitchen.recipes.ai._nav', ['active' => 'generate'])
    <div class="ra-head"><div style="flex:1;min-width:0">
        <h1>{{ $batch->label ?: 'Batch #' . $batch->id }}</h1>
        <p>{{ $batch->created_at->format('d M Y, h:i A') }} · {{ $batch->creator?->name }} · {{ $batch->config['yield_people'] ?? '—' }} people per batch ·
            {{ \App\Models\Recipe::FOOD_TYPES[$batch->config['food_type'] ?? ''] ?? '' }} · {{ $batch->model }}</p></div>
        <span id="rbStatus">@include('inventory._status', ['label' => $batch->statusLabel(), 'tone' => $batch->statusTone()])</span>
    </div>

    @unless($batch->isFinished())
        <div class="ra-warn" id="rbWorker" @if($queue['worker_alive']) hidden @endif><i class="bi bi-pause-circle" style="font-size:1.2rem;color:var(--ef-amber)"></i>
            <div><b>Waiting for a queue worker.</b> This batch is queued but nothing is processing it. Start one in the project folder:
                <code>{{ $workerCommand }}</code></div></div>
        <div class="ra-warn" id="rbStalled" @unless($stalled) hidden @endunless><i class="bi bi-hourglass-split" style="font-size:1.2rem;color:var(--ef-amber)"></i>
            <div><b><span id="rbStalledN">{{ $stalled }}</span> dishes look stuck</b> (no progress for {{ \App\Services\Recipes\RecipeGenerationService::STALL_MINUTES }}+ minutes).
                Use <b>Retry failed / stuck</b> to queue them again — recipes already written are kept.</div></div>
    @endunless

    <div class="ra-card">
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:.9rem;margin-bottom:8px">
            <span><b id="rbGen">{{ $batch->generated_count }}</b> of <b id="rbReq">{{ $batch->requested_count }}</b> written
                · <span id="rbDup">{{ $batch->duplicate_count }}</span> already existed · <span id="rbFail">{{ $batch->failed_count }}</span> failed</span>
            <span id="rbPct">{{ $batch->progressPercent() }}%</span>
        </div>
        <div class="ra-bar"><i id="rbBar" style="width:{{ $batch->progressPercent() }}%"></i></div>
        <div id="rbErr" class="ic-err" style="margin:10px 0 0" @unless($batch->error) hidden @endunless>{{ $batch->error }}</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
            <a class="ic-btn ic-btn--gold" href="{{ route('kitchen.recipes.review') }}"><i class="bi bi-clipboard-check"></i> Review recipes</a>
            <form method="POST" action="{{ route('kitchen.recipes.ai.retry', $batch) }}" data-ic-form>@csrf
                <button class="ic-btn" type="submit" id="rbRetry" @if(! $batch->failed_count && ! $stalled && ! ($batch->status === 'failed' && $batch->items->isEmpty())) hidden @endif><i class="bi bi-arrow-repeat"></i> Retry failed / stuck</button></form>
            @unless($batch->isFinished())
                <form method="POST" action="{{ route('kitchen.recipes.ai.cancel', $batch) }}" data-ic-form id="rbCancel">@csrf
                    <button class="ic-btn ic-btn--danger" type="submit"><i class="bi bi-x-circle"></i> Cancel</button></form>
            @endunless
        </div>
    </div>

    <div class="ra-card">
        <h2>Dishes</h2>
        <div id="rbItems">
            @forelse($batch->items as $i)
                <div class="ra-row">
                    <span class="ra-row-b"><span class="ra-row-t">
                        @if($i->recipe)<a href="{{ route('kitchen.recipes.show', $i->recipe) }}">{{ $i->recipe->name }}</a>@else{{ $i->dish_name }}@endif</span>
                        @if($i->error && $i->status !== 'done')<span class="ra-row-s">{{ \Illuminate\Support\Str::limit($i->error, 160) }}</span>@endif</span>
                    @include('inventory._status', ['label' => $itemLabel[$i->status][0] ?? $i->status, 'tone' => $itemLabel[$i->status][1] ?? 'neutral'])
                </div>
            @empty
                <p class="ra-hint">The AI is choosing dishes…</p>
            @endforelse
        </div>
    </div>
</div>

@unless($batch->isFinished())
@push('scripts')
<script>
(() => {
    const url = @json(route('kitchen.recipes.ai.progress', $batch));
    const labels = @json($itemLabel);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const chip = (t, tone) => '<span class="ic-chip ic-chip--' + tone + '">' + esc(t) + '</span>';
    let timer;
    async function poll() {
        try {
            const r = await fetch(url, {headers: {Accept: 'application/json'}});
            if (!r.ok) return;
            const d = await r.json();
            ['Gen', 'Req', 'Dup', 'Fail'].forEach((k, i) => document.getElementById('rb' + k).textContent = [d.generated, d.requested, d.duplicates, d.failed][i]);
            document.getElementById('rbPct').textContent = d.percent + '%';
            document.getElementById('rbBar').style.width = d.percent + '%';
            const err = document.getElementById('rbErr'); err.hidden = !d.error; err.textContent = d.error || '';
            document.getElementById('rbRetry').hidden = !(d.failed || d.stalled);
            document.getElementById('rbWorker').hidden = !!d.worker;
            document.getElementById('rbStalled').hidden = !d.stalled; document.getElementById('rbStalledN').textContent = d.stalled;
            if (d.items.length) document.getElementById('rbItems').innerHTML = d.items.map(i => {
                const l = labels[i.status] || [i.status, 'neutral'];
                return '<div class="ra-row"><span class="ra-row-b"><span class="ra-row-t">' +
                    (i.recipe ? '<a href="' + esc(i.recipe.url) + '">' + esc(i.recipe.name) + '</a>' : esc(i.dish)) + '</span>' +
                    (i.error && i.status !== 'done' ? '<span class="ra-row-s">' + esc(i.error.slice(0, 160)) + '</span>' : '') +
                    '</span>' + chip(l[0], l[1]) + '</div>';
            }).join('');
            if (d.finished) { location.reload(); return; }
        } catch (_) {}
        timer = setTimeout(poll, 4000);
    }
    timer = setTimeout(poll, 3000);
})();
</script>
@endpush
@endunless
</x-admin-layout>
