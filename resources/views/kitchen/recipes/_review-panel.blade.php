{{-- Review / verification panel on the recipe page. Admin actions only for admins. --}}
@php
    $u = auth()->user();
    $canVerify = $u->can('recipes.verify');
    $unmapped = $recipe->ingredients->where('needs_mapping', true);
    $approx = $recipe->ingredients->where('is_approximate', true)->count();
    $vs = $recipe->verification_status;
    $pending = in_array($vs, ['ai_generated', 'needs_review', 'reviewed'], true);
    $gaps = $recipe->languageGaps();
    $tamilGap = $gaps['name_ta'] || $gaps['steps_without_tamil'];
    $ingNoTa = $recipe->ingredients->filter(fn ($i) => ! $i->displayNameTa())->count();
    $check = [
        ['ok' => ! $gaps['steps_without_english'], 'en' => 'English instructions for every step', 'bad' => 'English missing for steps ' . implode(', ', $gaps['steps_without_english'])],
        ['ok' => ! $gaps['name_ta'], 'en' => 'Tamil recipe name', 'bad' => 'Tamil recipe name missing'],
        ['ok' => ! $gaps['steps_without_tamil'], 'en' => 'Tamil instructions for every step', 'bad' => 'Tamil missing for steps ' . implode(', ', $gaps['steps_without_tamil'])],
        ['ok' => $ingNoTa === 0, 'en' => 'Tamil names for ingredients', 'bad' => $ingNoTa . ' ingredients without a Tamil name', 'soft' => true],
        ['ok' => $unmapped->isEmpty(), 'en' => 'Every ingredient linked to a stock item', 'bad' => $unmapped->count() . ' not linked to stock', 'soft' => true],
        ['ok' => $approx === 0, 'en' => 'Exact quantities', 'bad' => $approx . ' approximate quantities — confirm them', 'soft' => true],
        ['ok' => ! $recipe->duplicateOf, 'en' => 'Not a duplicate', 'bad' => 'Similar to ' . ($recipe->duplicateOf->name ?? ''), 'soft' => true],
        ['ok' => (bool) ($recipe->kitchen_notes || $recipe->kitchen_notes_ta), 'en' => 'Kitchen / food-safety notes', 'bad' => 'No kitchen or food-safety notes', 'soft' => true],
    ];
@endphp
@push('styles')
<style>
.rp{border:1px solid var(--ef-border);border-radius:16px;background:var(--ef-surface,#fff);box-shadow:var(--ef-shadow);padding:14px;margin:4px 0 18px}
.rp.--ai{border-left:4px solid #7c4dff}
.rp.--ok{border-left:4px solid var(--ef-emerald)}
.rp.--warn{border-left:4px solid var(--ef-amber)}
.rp-top{display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap}
.rp-top>.bi{font-size:1.4rem;line-height:1}
.rp-t{flex:1;min-width:220px}
.rp-t strong{display:block;font-size:1rem}
.rp-t span{font-size:.86rem;color:var(--ef-muted)}
.rp-list{margin:10px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:4px;font-size:.86rem}
.rp-list li{display:flex;gap:6px;align-items:baseline}
.rp-list .bi{color:var(--ef-amber)}
.rp-acts{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.rp-btn{display:inline-flex;align-items:center;gap:6px;min-height:42px;padding:8px 14px;border-radius:11px;border:1px solid var(--ef-border);background:var(--ef-surface,#fff);color:var(--ef-ink);font-weight:700;font-size:.88rem;cursor:pointer;text-decoration:none}
.rp-btn:hover{border-color:var(--ef-gold)}
.rp-btn.--go{background:var(--ef-emerald);border-color:var(--ef-emerald);color:#fff}
.rp-btn.--danger{color:var(--ef-danger)}
.rp-verify{margin-top:12px;padding:12px;border-radius:12px;background:rgba(46,125,90,.06);border:1px solid rgba(46,125,90,.25)}
.rp-verify[hidden]{display:none}
.rp-verify p{margin:0 0 8px;font-size:.88rem}
.rp-verify textarea{width:100%;min-height:60px;border:1px solid var(--ef-border);border-radius:10px;padding:8px 10px;font:inherit;font-size:16px}
.rp-verify label.rp-ok{display:flex;gap:8px;align-items:flex-start;margin:8px 0;font-size:.88rem;font-weight:600}
.rp-verify label.rp-ok input{width:20px;height:20px;flex:0 0 auto}
.rp-map{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.3fr) auto;gap:8px;align-items:center;padding:8px 0;border-top:1px solid var(--ef-border)}
@media (max-width:560px){.rp-map{grid-template-columns:1fr auto}.rp-map>div:nth-child(2){grid-column:1/-1;grid-row:2}}
.rp-map input[type=text]{min-height:42px;font-size:16px;width:100%}
.rp-check{list-style:none;margin:10px 0 0;padding:0;display:grid;gap:4px;font-size:.86rem}
@media (min-width:760px){.rp-check{grid-template-columns:1fr 1fr}}
.rp-check li{display:flex;gap:6px;align-items:baseline}
.rp-check .--ok{color:#1f6a48}.rp-check .--bad{color:var(--ef-danger)}.rp-check .--soft{color:#9a6413}
.rp-scale{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-top:10px;font-size:.84rem}
.rp-scale a{min-height:36px;display:inline-flex;align-items:center;padding:4px 12px;border:1px solid var(--ef-border);border-radius:999px;text-decoration:none;color:var(--ef-ink);font-weight:700}
.rp-hist{margin-top:10px;font-size:.84rem}
.rp-hist summary{cursor:pointer;font-weight:700;min-height:36px;display:flex;align-items:center}
.rp-hist li{margin:4px 0}
</style>
@endpush

@if($vs !== 'unverified' || $recipe->versions->isNotEmpty() || $canVerify)
<section class="rp {{ $recipe->isVerified() ? '--ok' : ($vs === 'ai_generated' ? '--ai' : ($pending ? '--warn' : '')) }}" aria-label="Verification">
    <div class="rp-top">
        @if($recipe->isVerified())
            <i class="bi bi-patch-check-fill" style="color:var(--ef-emerald)"></i>
            <div class="rp-t"><strong>Verified ✓ — trusted recipe</strong>
                <span>Revision {{ $recipe->revision }} · verified {{ $recipe->verified_at->format('d M Y') }} by {{ $recipe->verifier?->name ?? '—' }}.
                    Editing ingredients, quantities, yield or steps will send it back for review.</span>
                @if($recipe->verification_notes)<span style="display:block;margin-top:2px">“{{ $recipe->verification_notes }}”</span>@endif</div>
        @elseif($vs === 'archived')
            <i class="bi bi-archive" style="color:var(--ef-muted)"></i>
            <div class="rp-t"><strong>Archived</strong><span>Hidden from the kitchen and the review queue.</span></div>
        @else
            <i class="bi {{ $recipe->isAiGenerated() ? 'bi-stars' : ($recipe->source === 'starter' ? 'bi-box-seam' : 'bi-shield-exclamation') }}" style="color:{{ $recipe->isAiGenerated() ? '#7c4dff' : 'var(--ef-amber)' }}"></i>
            <div class="rp-t"><strong>{{ $recipe->isAiGenerated() ? 'AI Generated — Not Verified' : ($recipe->source === 'starter' ? 'Curated starter recipe — Not Verified' : 'Not verified') }}</strong>
                <span>{{ $recipe->verificationLabel() }}.
                    @if($recipe->source === 'starter') Hand-written starter recipe for 100 people (not AI). Adjust quantities to your kitchen before verifying — it stays hidden from the kitchen until then.@endif
                    @if($recipe->isAiGenerated()) Written by AI{{ isset($recipe->ai_meta['model']) ? ' (' . $recipe->ai_meta['model'] . ')' : '' }}. Check every quantity and step before verifying — it stays hidden from the kitchen until then.@endif
                </span></div>
        @endif
    </div>

    @if(! $recipe->isVerified() && $vs !== 'archived')
        <ul class="rp-check" aria-label="Verification checklist">
            @foreach($check as $c)
                <li class="{{ $c['ok'] ? '--ok' : (! empty($c['soft']) ? '--soft' : '--bad') }}"><i class="bi {{ $c['ok'] ? 'bi-check-circle-fill' : (! empty($c['soft']) ? 'bi-exclamation-circle' : 'bi-x-circle-fill') }}"></i>{{ $c['ok'] ? $c['en'] : $c['bad'] }}</li>
            @endforeach
        </ul>
        <div class="rp-scale"><span>Check scaled quantities:</span>
            @foreach([50, 100, 250, 500] as $pp)<a href="{{ route('kitchen.recipes.print', [$recipe, 'people' => $pp]) }}" target="_blank" data-rlang-link>{{ $pp }}</a>@endforeach</div>
        <ul class="rp-list">
            @if($recipe->duplicateOf)<li><i class="bi bi-files"></i> Looks similar to <a href="{{ route('kitchen.recipes.show', $recipe->duplicateOf) }}">{{ $recipe->duplicateOf->name }}</a> — archive one if they are the same dish.</li>@endif
            @if($unmapped->isNotEmpty())<li><i class="bi bi-link-45deg"></i> {{ $unmapped->count() }} {{ \Illuminate\Support\Str::plural('ingredient', $unmapped->count()) }} not linked to a stock item — link them below so issue and costing work.</li>@endif
            @if($approx)<li><i class="bi bi-rulers"></i> {{ $approx }} approximate {{ \Illuminate\Support\Str::plural('quantity', $approx) }} — confirm or replace with exact numbers.</li>@endif
        </ul>
    @endif

    @if($canVerify)
        <div class="rp-acts">
            @if($vs !== 'archived' && ! $recipe->isVerified())
                <button type="button" class="rp-btn --go" data-rp-verify><i class="bi bi-patch-check"></i> Verify…</button>
                @if(in_array($vs, ['ai_generated', 'needs_review'], true))
                    <form method="POST" action="{{ route('kitchen.recipes.reviewed', $recipe) }}" data-ic-form style="margin:0">@csrf
                        <button class="rp-btn" type="submit"><i class="bi bi-check2"></i> Mark reviewed</button></form>
                @endif
                <a class="rp-btn" href="{{ route('kitchen.recipes.edit', $recipe) }}"><i class="bi bi-pencil"></i> Edit</a>
            @endif
            @if($vs === 'archived')
                <form method="POST" action="{{ route('kitchen.recipes.restore', $recipe) }}" style="margin:0">@csrf
                    <button class="rp-btn" type="submit"><i class="bi bi-arrow-counterclockwise"></i> Restore</button></form>
            @else
                <form method="POST" action="{{ route('kitchen.recipes.archive', $recipe) }}" style="margin:0">@csrf
                    <button class="rp-btn --danger" type="submit"><i class="bi bi-archive"></i> Archive</button></form>
            @endif
            @if($recipe->isAiGenerated() || $vs !== 'unverified')
                <a class="rp-btn" href="{{ route('kitchen.recipes.review') }}"><i class="bi bi-list-check"></i> Review queue</a>
            @endif
        </div>

        @if($vs !== 'archived' && ! $recipe->isVerified())
            <form class="rp-verify" method="POST" action="{{ route('kitchen.recipes.verify', $recipe) }}" data-rp-verify-form hidden>
                @csrf
                <p><b>Verify “{{ $recipe->name }}”?</b> It will be marked as a trusted, final recipe: shown to the kitchen,
                    used for production planning, material requests and costing. This version is saved in its history.</p>
                @if($unmapped->isNotEmpty())<p style="color:#7D5218"><i class="bi bi-exclamation-triangle"></i> {{ $unmapped->count() }} {{ $unmapped->count() === 1 ? 'ingredient is' : 'ingredients are' }} still not linked to stock items.</p>@endif
                <textarea name="notes" maxlength="1000" placeholder="Notes (optional) — e.g. tested on 12 Dec for 120 people"></textarea>
                @if($tamilGap)<p style="color:#9a6413"><i class="bi bi-translate"></i> Tamil is incomplete{{ $gaps['steps_without_tamil'] ? ' (steps ' . implode(', ', $gaps['steps_without_tamil']) . ')' : '' }}. Kitchen staff reading Tamil will see “Tamil missing”.</p>
                    <label class="rp-ok"><input type="checkbox" name="allow_missing_tamil" value="1"> Verify with English only for now (Tamil to be added later).</label>@endif
                <label class="rp-ok"><input type="checkbox" name="confirm" value="1" required> I have checked the ingredients, quantities and steps (English{{ $tamilGap ? '' : ' and Tamil' }}).</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button class="rp-btn --go" type="submit"><i class="bi bi-patch-check-fill"></i> Verify recipe</button>
                    <button class="rp-btn" type="button" data-rp-verify-cancel>Cancel</button>
                </div>
            </form>
        @endif
    @endif

    @if($canVerify && $unmapped->isNotEmpty() && $vs !== 'archived')
        <div style="margin-top:12px">
            <div class="ra-cap" style="font-size:.74rem;font-weight:700;text-transform:uppercase;color:var(--ef-muted)">Link ingredients to stock items</div>
            @foreach($unmapped as $ing)
                <form class="rp-map" method="POST" action="{{ route('kitchen.recipes.map-ingredient', [$recipe, $ing]) }}">
                    @csrf
                    <div><b>{{ $ing->ingredient_name }}</b>@if($ing->match_confidence)<div style="font-size:.74rem;color:var(--ef-muted)">best guess {{ $ing->match_confidence }}% — not sure enough</div>@endif</div>
                    <div><input type="text" class="ef-input" data-rp-pick value="" placeholder="Search stock item" aria-label="Stock item for {{ $ing->ingredient_name }}" autocomplete="off">
                        <input type="hidden" name="inventory_item_id" data-rp-pick-id></div>
                    <button class="rp-btn" type="submit" data-rp-save disabled><i class="bi bi-link-45deg"></i> Link</button>
                </form>
            @endforeach
        </div>
    @endif

    @if($recipe->versions->isNotEmpty())
        <details class="rp-hist">
            <summary><i class="bi bi-clock-history" style="margin-right:6px"></i> Version history ({{ $recipe->versions->count() }})</summary>
            <ul>
                @foreach($recipe->versions as $v)
                    <li>Rev {{ $v->revision }} · {{ $v->event === 'verified' ? 'verified' : 'replaced' }} {{ $v->created_at?->format('d M Y, h:i A') }}
                        @if($v->event === 'verified') by {{ $v->verifier?->name }} @else by {{ $v->changer?->name }} @endif
                        · {{ count($v->snapshot['ingredients'] ?? []) }} ingredients, {{ count($v->snapshot['steps'] ?? []) }} steps,
                        {{ rtrim(rtrim(number_format((float) ($v->snapshot['yield_per_batch'] ?? 0), 2), '0'), '.') }} {{ $v->snapshot['yield_unit'] ?? '' }}
                        @if($v->change_summary) — {{ $v->change_summary }} @endif</li>
                @endforeach
            </ul>
        </details>
    @endif
</section>

@if($canVerify)
@include('inventory._item-picker')
@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-rp-verify-form]');
    document.querySelector('[data-rp-verify]')?.addEventListener('click', () => { form.hidden = false; form.querySelector('textarea').focus(); });
    document.querySelector('[data-rp-verify-cancel]')?.addEventListener('click', () => { form.hidden = true; });
    document.querySelectorAll('.rp-map').forEach(f => {
        const id = f.querySelector('[data-rp-pick-id]'), save = f.querySelector('[data-rp-save]');
        window.IcItemPicker?.attach(f.querySelector('[data-rp-pick]'), {
            allowCreate: true,
            onPick(r) { id.value = r.id; save.disabled = false; },
            onClear() { id.value = ''; save.disabled = true; },
        });
    });
})();
</script>
@endpush
@endif
@endif
