{{-- Sub-navigation shared by the AI generator, batch and review pages. $active: generate|review|library --}}
@php $toReview = \App\Models\Recipe::whereIn('verification_status', ['ai_generated', 'needs_review'])->count(); @endphp
<nav class="ra-tabs" aria-label="Recipe tools">
    <a class="ra-tab {{ $active === 'library' ? '--on' : '' }}" href="{{ route('kitchen.recipes.index') }}"><i class="bi bi-journal-richtext"></i> Library</a>
    <a class="ra-tab {{ $active === 'generate' ? '--on' : '' }}" href="{{ route('kitchen.recipes.ai.index') }}"><i class="bi bi-stars"></i> AI Generator</a>
    <a class="ra-tab {{ $active === 'review' ? '--on' : '' }}" href="{{ route('kitchen.recipes.review') }}"><i class="bi bi-clipboard-check"></i> Review queue
        @if($toReview)<span class="ra-badge">{{ $toReview }}</span>@endif</a>
</nav>
@once
@push('styles')
<style>
.ra-page{max-width:1100px;margin:0 auto}
.ra-tabs{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;margin:0 0 14px;padding-bottom:2px}
.ra-tabs::-webkit-scrollbar{display:none}
.ra-tab{flex:0 0 auto;display:inline-flex;align-items:center;gap:6px;min-height:40px;padding:6px 14px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-muted);font-weight:700;font-size:.88rem;text-decoration:none;white-space:nowrap}
.ra-tab:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.ra-tab.--on{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.ra-badge{min-width:22px;height:22px;padding:0 6px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:.74rem;font-weight:800;background:var(--ef-gold);color:#fff}
.ra-head{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin:2px 0 12px}
.ra-head h1{margin:0;font-size:1.25rem;font-weight:800;color:var(--ef-ink)}
.ra-head p{margin:2px 0 0;font-size:.86rem;color:var(--ef-muted)}
.ra-card{border:1px solid var(--ef-border);border-radius:16px;background:var(--ef-surface);box-shadow:var(--ef-shadow);padding:14px;margin-bottom:14px}
.ra-card h2{font-size:1rem;font-weight:800;margin:0 0 10px;display:flex;align-items:center;gap:8px}
.ra-grid{display:grid;gap:10px;grid-template-columns:1fr}
@media (min-width:520px){.ra-grid{grid-template-columns:1fr 1fr}}
@media (min-width:768px){.ra-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.ra-f{display:flex;flex-direction:column;gap:4px;min-width:0}
.ra-f.--wide{grid-column:1/-1}
.ra-cap{font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:var(--ef-muted)}
.ra-f .ic-input,.ra-f .ic-select{min-height:46px;font-size:16px}
.ra-hint{font-size:.8rem;color:var(--ef-muted)}
.ra-row{display:flex;align-items:center;gap:10px;padding:10px 2px;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none;min-height:56px}
.ra-row:first-of-type{border-top:0}
.ra-row-b{flex:1;min-width:0}
.ra-row-t{display:block;font-weight:750;font-size:.94rem;overflow-wrap:anywhere}
.ra-row-s{display:block;font-size:.78rem;color:var(--ef-muted)}
.ra-bar{height:8px;border-radius:99px;background:rgba(20,20,18,.08);overflow:hidden}
.ra-bar i{display:block;height:100%;background:var(--ef-emerald);border-radius:inherit;transition:width .3s}
.ra-warn{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:14px;border:1px solid rgba(216,154,61,.45);background:rgba(216,154,61,.09);margin-bottom:14px;font-size:.9rem}
</style>
@endpush
@endonce
