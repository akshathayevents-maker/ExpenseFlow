<x-admin-layout :title="'Standard · ' . $h['preparation']">
@php
    $cur = $h['current'] ? $h['current']->ingredients->pluck('quantity_per_guest', 'inventory_item_id') : collect();
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 5, '.', ''), '0'), '.') ?: '0';
    $m = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $ready = \App\Services\Inventory\RecipeStandardService::READY_AFTER;
    $guests = collect($h['guests']);
    $usage = collect($h['usage'] ?? []);
    $costMedian = collect($h['rows'])->sum(fn ($r) => $r['median'] * $r['cost']);
    $costStd = $h['current'] ? $h['current']->ingredients->sum(fn ($i) => (float) $i->quantity_per_guest * (float) (collect($h['rows'])->firstWhere('item_id', $i->inventory_item_id)['cost'] ?? 0)) : null;
    $outTot = $usage->sum('cost');
    $wasteAvg = $usage->count() ? (int) round($usage->avg('waste_pct')) : 0;
    $planFor = (int) ($guests->last() ?: 100);
    // standard items not used in any recorded request (still part of the standard)
    $extraStd = $h['current'] ? $h['current']->ingredients->filter(fn ($i) => $i->inventory_item_id && ! isset($h['rows'][$i->inventory_item_id])) : collect();
@endphp

@push('styles')
<style>
.sd2{display:flex;flex-direction:column;gap:14px;max-width:1500px;padding-bottom:80px}
.sd2-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.sd2-back{display:inline-flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;color:var(--ef-muted);text-decoration:none;margin-bottom:4px}
.sd2-head h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink);display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.sd2-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.sd2-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:8px;font-size:.72rem;font-weight:800}
.sd2-chip.--ok{background:rgba(59,110,165,.12);color:#2c5784}
.sd2-chip.--new{background:rgba(30,122,60,.12);color:var(--ef-emerald-dk)}
.sd2-chip.--warn{background:rgba(214,110,40,.12);color:#a3480f}
.sd2-chip.--muted{background:var(--ef-bg-subtle);color:var(--ef-muted)}
@media (max-width:639.98px){.sd2-head h1{font-size:1.25rem}}

.sd2-k{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden}
.sd2-k > div{padding:11px 14px;background:linear-gradient(var(--tint,transparent),var(--tint,transparent)),var(--ef-surface);min-width:0}
.sd2-k small{display:block;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sd2-k strong{display:block;font-size:1.3rem;font-weight:800;color:var(--acc,var(--ef-ink));font-variant-numeric:tabular-nums;line-height:1.2}
.sd2-k span{font-size:.7rem;color:var(--ef-muted)}
@media (max-width:899.98px){.sd2-k{grid-template-columns:repeat(2,minmax(0,1fr))}.sd2-k > div:first-child{grid-column:1/-1}.sd2-k strong{font-size:1.1rem}}

.sd2-body{display:grid;gap:14px}
@media (min-width:1500px){.sd2-body{grid-template-columns:minmax(0,1fr) 330px;align-items:start}}
.sd2-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}
.sd2-ph{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint,transparent)}
.sd2-ph h2{margin:0;font-size:.95rem;font-weight:800;display:flex;align-items:center;gap:8px}
.sd2-ph h2 .bi{color:var(--acc)}
.sd2-plan{margin-left:auto;display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;color:var(--ef-muted)}
.sd2-plan input{width:96px;min-height:38px;padding:4px 10px;border-radius:9px;border:1px solid var(--ef-border);font-size:16px;font-weight:800;text-align:right}
.sd2-tools{display:flex;gap:6px;flex-wrap:wrap;padding:8px 14px;border-bottom:1px solid var(--ef-border);background:var(--ef-bg-subtle)}
.sd2-mini{display:inline-flex;align-items:center;gap:5px;min-height:34px;padding:0 11px;border-radius:9px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:.78rem;font-weight:700;color:var(--ef-ink);cursor:pointer}
.sd2-mini:hover{border-color:var(--ef-gold)}

.sd2-th,.sd2-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:11px 14px}
.sd2-th{display:none}
.sd2-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent)}
.sd2-row:first-of-type{border-top:0}
.sd2-row[data-gap=hi]{--tone:#d66e28}
.sd2-name b{display:block;font-size:.92rem}
.sd2-name small{font-size:.74rem;color:var(--ef-muted)}
.sd2-range{grid-column:1/-1;position:relative;height:22px}
.sd2-range .tr{position:absolute;left:0;right:0;top:9px;height:4px;border-radius:3px;background:var(--ef-bg-subtle)}
.sd2-range .rg{position:absolute;top:9px;height:4px;border-radius:3px;background:rgba(59,110,165,.35)}
.sd2-range .md{position:absolute;top:4px;width:3px;height:14px;border-radius:2px;background:#2c5784;transform:translateX(-1px)}
.sd2-range .st{position:absolute;top:2px;width:12px;height:12px;border-radius:50%;background:var(--ef-gold);border:2px solid #fff;box-shadow:0 0 0 1px var(--ef-gold);transform:translateX(-6px)}
.sd2-stats{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.76rem;color:var(--ef-muted)}
.sd2-stats b{color:var(--ef-ink)}
.sd2-stats .--bad{color:var(--ef-danger);font-weight:800}
.sd2-in{grid-column:1/-1;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px;align-items:end}
.sd2-in label{display:flex;flex-direction:column;gap:3px;font-size:.7rem;font-weight:700;color:var(--ef-muted);margin:0}
.sd2-in input{min-height:42px;padding:6px 10px;border-radius:9px;border:1px solid var(--ef-border);font-size:16px;font-weight:700;width:100%;background:var(--ef-surface)}
.sd2-in input:focus{outline:none;border-color:var(--ef-gold);box-shadow:0 0 0 3px rgba(200,144,42,.15)}
.sd2-for{font-size:.95rem;font-weight:800;text-align:right;font-variant-numeric:tabular-nums;padding-bottom:9px}
.sd2-for small{display:block;font-size:.68rem;font-weight:600;color:var(--ef-muted)}
@media (min-width:1100px){
  .sd2-th,.sd2-row{grid-template-columns:minmax(150px,1fr) minmax(170px,1.2fr) 150px 130px 120px;gap:14px}
  .sd2-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .sd2-th .--r{text-align:right}
  .sd2-cell-range{display:flex;flex-direction:column;gap:2px}
  .sd2-range,.sd2-stats{grid-column:auto}
  .sd2-in{display:contents}
  .sd2-in label span{display:none}
}
.sd2-legend{display:flex;flex-wrap:wrap;gap:12px;padding:8px 14px;border-top:1px solid var(--ef-border);font-size:.74rem;color:var(--ef-muted)}
.sd2-legend i{display:inline-block;vertical-align:middle;margin-right:4px}
.sd2-extra{display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:8px;padding:12px 14px;border-top:1px solid var(--ef-border)}
.sd2-extra label{display:flex;flex-direction:column;gap:3px;font-size:.72rem;font-weight:700;color:var(--ef-muted);margin:0;min-width:0}
.sd2-extra select,.sd2-extra input{min-height:42px;padding:6px 10px;border-radius:9px;border:1px solid var(--ef-border);font-size:16px;background:var(--ef-surface);width:100%;min-width:0}
.sd2-extra .--wide{grid-column:1/-1}
@media (max-width:519.98px){.sd2-extra{grid-template-columns:1fr}}

.sd2-pb{padding:6px 14px 12px;font-size:.82rem}
.sd2-use{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-top:1px solid var(--ef-border);color:var(--ef-ink);text-decoration:none}
.sd2-use:first-child{border-top:0}
.sd2-use span{font-size:.74rem;color:var(--ef-muted)}
.sd2-use b + span{display:block}
.sd2-use em{font-style:normal;font-weight:800;font-size:.8rem}
.sd2-use em.--bad{color:var(--ef-danger)}
.sd2-ver{padding:8px 0;border-top:1px solid var(--ef-border)}
.sd2-ver:first-child{border-top:0}
.sd2-ver b{font-size:.86rem}
.sd2-ver span{display:block;font-size:.74rem;color:var(--ef-muted)}
.sd2-save{position:sticky;bottom:0;z-index:5;display:flex;align-items:center;gap:10px;padding:10px 14px calc(10px + env(safe-area-inset-bottom,0px));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 -6px 20px rgba(19,17,16,.08)}
.sd2-save p{margin:0 auto 0 0;font-size:.8rem;color:var(--ef-muted);min-width:0}
.sd2-save p b{color:var(--ef-ink)}
.sd2-save button{display:inline-flex;align-items:center;gap:7px;min-height:44px;padding:8px 18px;border-radius:10px;border:0;background:var(--ef-ink);color:#fff;font-weight:800;cursor:pointer;white-space:nowrap}
</style>
@endpush

<form method="POST" action="{{ route('inventory.standards.store') }}" class="sd2" id="sdPage" data-ic-form>
    @csrf
    <input type="hidden" name="name" value="{{ $h['preparation'] }}">

    <header class="sd2-head">
        <div style="flex:1 1 320px;min-width:0">
            <a class="sd2-back" href="{{ route('inventory.standards.index') }}"><i class="bi bi-arrow-left"></i> Recipe standards</a>
            <h1>{{ $h['preparation'] }}
                @if($h['current'])<span class="sd2-chip --ok"><i class="bi bi-patch-check"></i> Standard v{{ $h['current']->version }}</span>
                @elseif($h['requests'] >= $ready)<span class="sd2-chip --new"><i class="bi bi-stars"></i> Ready to standardise</span>
                @else<span class="sd2-chip --muted">{{ $h['requests'] }} of {{ $ready }} uses recorded</span>@endif
                @if(($drift ?? 0) >= 20)<span class="sd2-chip --warn">Off recent usage by {{ $drift }}%</span>@endif
            </h1>
            <p>Per-person amounts from {{ $h['requests'] }} recorded {{ Str::plural('use', $h['requests']) }}. “Out per person” = used + wasted + unexplained ÷ people.</p>
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="sd2-k">
        <div style="--acc:var(--ef-ink)"><small>Recorded uses</small><strong>{{ $h['requests'] }}</strong><span>{{ $guests->count() ? number_format($guests->min()).'–'.number_format($guests->max()).' people' : '—' }}</span></div>
        <div style="--acc:#9a6413;--tint:rgba(200,144,42,.07)"><small>Cost / person (usual)</small><strong>{{ $costMedian > 0 ? '₹'.$m($costMedian) : '—' }}</strong><span>median usage × today's price</span></div>
        <div style="--acc:#2c5784;--tint:rgba(59,110,165,.06)"><small>Cost / person (standard)</small><strong>{{ $costStd ? '₹'.$m($costStd) : '—' }}</strong><span>{{ $h['current'] ? 'v'.$h['current']->version : 'no standard yet' }}</span></div>
        <div style="--acc:{{ $wasteAvg >= 15 ? 'var(--ef-danger)' : '#7a6e62' }};--tint:{{ $wasteAvg >= 15 ? 'rgba(192,57,43,.05)' : 'transparent' }}"><small>Avg waste</small><strong>{{ $wasteAvg }}%</strong><span>wasted + unexplained</span></div>
        <div style="--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)"><small>Material cost recorded</small><strong>₹{{ number_format($outTot, 0) }}</strong><span>all uses, today's prices</span></div>
    </div>

    <div class="sd2-body">
        <section class="sd2-panel" style="--acc:var(--ef-gold);--tint:rgba(200,144,42,.05)">
            <div class="sd2-ph">
                <h2><i class="bi bi-sliders"></i> Per-person standard</h2>
                <label class="sd2-plan">Plan for <input type="number" min="1" inputmode="numeric" value="{{ $planFor }}" data-plan aria-label="People to plan for"> people</label>
            </div>
            @if(count($h['rows']))
                <div class="sd2-tools">
                    <button type="button" class="sd2-mini" data-fill="median"><i class="bi bi-magic"></i> Use usual amounts (median)</button>
                    @if($h['current'])<button type="button" class="sd2-mini" data-fill="current"><i class="bi bi-arrow-counterclockwise"></i> Back to v{{ $h['current']->version }}</button>@endif
                </div>
                <div class="sd2-th" aria-hidden="true"><span>Item</span><span>Recorded per person</span><span>Standard / person</span><span class="--r">For <span data-plan-label>{{ $planFor }}</span></span><span class="--r">Cost</span></div>
            @endif
            @forelse($h['rows'] as $r)
                @php
                    $std = $cur[$r['item_id']] ?? null;
                    $val = $std ?? $r['median'];
                    $hi = max($r['max'], (float) $val, 0.00001) * 1.1;
                    $gap = $std ? abs($r['median'] - $std) / $std : 0;
                @endphp
                <div class="sd2-row" data-std data-median="{{ $q($r['median']) }}" data-current="{{ $std !== null ? $q($std) : '' }}" data-cost="{{ $r['cost'] }}" data-unit="{{ $r['unit'] }}" data-gap="{{ $gap >= .2 && $r['times'] >= 2 ? 'hi' : '' }}">
                    <div class="sd2-name"><b>{{ $r['item'] }}</b><small>{{ $r['unit'] }} · {{ $r['times'] }} {{ Str::plural('use', $r['times']) }}</small></div>
                    <div class="sd2-cell-range">
                        <div class="sd2-range" title="Range {{ $q($r['min']) }}–{{ $q($r['max']) }}, median {{ $q($r['median']) }}">
                            <span class="tr"></span>
                            <span class="rg" style="left:{{ $r['min'] / $hi * 100 }}%;width:{{ max(1, ($r['max'] - $r['min']) / $hi * 100) }}%"></span>
                            <span class="md" style="left:{{ $r['median'] / $hi * 100 }}%"></span>
                            <span class="st" data-dot data-hi="{{ $hi }}" style="left:{{ (float) $val / $hi * 100 }}%"></span>
                        </div>
                        <div class="sd2-stats">
                            <span>median <b>{{ $q($r['median']) }}</b></span>
                            <span>{{ $q($r['min']) }}–{{ $q($r['max']) }}</span>
                            @if($r['waste_pct'])<span class="{{ $r['waste_pct'] >= 15 ? '--bad' : '' }}">{{ $r['waste_pct'] }}% waste</span>@endif
                            @if($std !== null)<span>v{{ $h['current']->version }}: <b>{{ $q($std) }}</b></span>@endif
                        </div>
                    </div>
                    <div class="sd2-in">
                        <label><span>Standard / person ({{ $r['unit'] }})</span>
                            <input type="number" step="0.00001" min="0" name="lines[{{ $r['item_id'] }}]" value="{{ $q($val) }}" data-pg aria-label="{{ $r['item'] }} per person">
                        </label>
                        <div class="sd2-for" data-for>—</div>
                        <div class="sd2-for" data-costcell>—</div>
                    </div>
                </div>
            @empty
                <div style="padding:24px 14px;color:var(--ef-muted);font-size:.86rem">No recorded usage for this dish yet. You can still start a standard with items below.</div>
            @endforelse
            @if(count($h['rows']))
                <div class="sd2-legend">
                    <span><i style="width:18px;height:4px;background:rgba(59,110,165,.35);border-radius:3px"></i>recorded range</span>
                    <span><i style="width:3px;height:12px;background:#2c5784;border-radius:2px"></i>median</span>
                    <span><i style="width:10px;height:10px;border-radius:50%;background:var(--ef-gold)"></i>your standard</span>
                    <span style="margin-left:auto">Total for <b data-plan-label>{{ $planFor }}</b> people: <b data-total>—</b></span>
                </div>
            @endif
            @if($extraStd->isNotEmpty())
                <div style="padding:8px 14px;border-top:1px solid var(--ef-border);font-size:.78rem;color:var(--ef-muted)">
                    In v{{ $h['current']->version }} but never recorded: {{ $extraStd->map(fn ($i) => $i->ingredient_name.' '.$q($i->quantity_per_guest).' '.$i->unit)->join(', ') }} — kept only if you add them again below.
                </div>
            @endif
            <div class="sd2-extra">
                <label>Add another item
                    <select name="extra_item_id"><option value="">—</option>@foreach($items as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit }})</option>@endforeach</select>
                </label>
                <label>Per person<input type="number" step="0.00001" min="0" name="extra_per_guest"></label>
                <label class="--wide">Notes (method, batch size…)<input name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="e.g. For 250+ cook in two vessels"></label>
            </div>
        </section>

        <aside style="display:flex;flex-direction:column;gap:14px;min-width:0">
            <section class="sd2-panel" style="--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)">
                <div class="sd2-ph"><h2><i class="bi bi-clock-history"></i> Recorded uses</h2></div>
                <div class="sd2-pb">
                    @forelse($usage->take(10) as $u)
                        <a class="sd2-use" href="{{ route('inventory.material-requests.show', $u['id']) }}">
                            <span style="color:var(--ef-ink)"><b>{{ $u['ref'] }}</b><span>{{ optional($u['date'])->format('d M Y') }} · {{ number_format($u['guests']) }} people</span></span>
                            <span style="text-align:right">₹{{ number_format($u['cost'], 0) }}<br><em class="{{ $u['waste_pct'] >= 15 ? '--bad' : '' }}">{{ $u['waste_pct'] }}% waste</em></span>
                        </a>
                    @empty
                        <p style="margin:0;color:var(--ef-muted)">None yet.</p>
                    @endforelse
                </div>
            </section>
            @if($versions->isNotEmpty())
                <section class="sd2-panel" style="--acc:#2c5784;--tint:rgba(59,110,165,.05)">
                    <div class="sd2-ph"><h2><i class="bi bi-layers"></i> Versions</h2></div>
                    <div class="sd2-pb">
                        @foreach($versions as $v)
                            <div class="sd2-ver"><b>v{{ $v->version }}</b> {{ $v->superseded_at ? '' : '· current' }}
                                <span>{{ $v->created_at->format('d M Y') }}@if($v->createdBy) · {{ $v->createdBy->name }}@endif{{ $v->superseded_at ? ' · replaced '.$v->superseded_at->format('d M Y') : '' }}</span>
                                @if($v->description)<span>{{ Str::limit($v->description, 80) }}</span>@endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>

    <div class="sd2-save">
        <p>Saving creates <b>version {{ ($h['current']?->version ?? 0) + 1 }}</b>. Earlier material requests keep the version they used.</p>
        <button type="submit"><i class="bi bi-save"></i> Save standard</button>
    </div>
</form>

@push('scripts')
<script>
(() => {
    const page = document.getElementById('sdPage'); if (!page) return;
    const plan = page.querySelector('[data-plan]');
    const fmt = n => (Math.round(n * 1000) / 1000).toLocaleString('en-IN');
    function paint() {
        const n = Math.max(0, parseInt(plan.value, 10) || 0);
        page.querySelectorAll('[data-plan-label]').forEach(e => e.textContent = n.toLocaleString('en-IN'));
        let total = 0;
        page.querySelectorAll('[data-std]').forEach(row => {
            const v = parseFloat(row.querySelector('[data-pg]').value) || 0, cost = parseFloat(row.dataset.cost) || 0;
            row.querySelector('[data-for]').innerHTML = fmt(v * n) + '<small>' + row.dataset.unit + ' for ' + n.toLocaleString('en-IN') + '</small>';
            row.querySelector('[data-costcell]').innerHTML = cost ? '₹' + fmt(v * n * cost) + '<small>at ₹' + fmt(cost) + '/' + row.dataset.unit + '</small>' : '—';
            total += v * n * cost;
            const dot = row.querySelector('[data-dot]'), hi = parseFloat(dot.dataset.hi) || 1;
            dot.style.left = Math.min(100, v / hi * 100) + '%';
        });
        const t = page.querySelector('[data-total]'); if (t) t.textContent = total ? '₹' + fmt(total) : '—';
    }
    page.addEventListener('input', e => { if (e.target.matches('[data-pg], [data-plan]')) paint(); });
    page.querySelectorAll('[data-fill]').forEach(b => b.addEventListener('click', () => {
        page.querySelectorAll('[data-std]').forEach(row => {
            const v = row.dataset[b.dataset.fill]; if (v !== '') row.querySelector('[data-pg]').value = v;
        });
        paint();
    }));
    paint();
})();
</script>
@endpush
</x-admin-layout>
