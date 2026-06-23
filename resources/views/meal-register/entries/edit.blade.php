<x-admin-layout title="Edit Meal Entry">
@push('styles')
<style>
:root{
    --dmr-gold:#a0723a;--dmr-gold-hi:#b8832a;
    --dmr-ink:#1c1712;--dmr-muted:#7a6e62;--dmr-faint:#b0a89a;
    --dmr-border:#e8e2d8;--dmr-surface:#fff;
    --dmr-radius:16px;
    --dmr-green:#16a34a;--dmr-red:#dc2626;
}
.dmr-wrap{max-width:600px;margin:0 auto;padding-bottom:100px}
.dmr-page-hdr{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.dmr-page-title{font-size:1.3rem;font-weight:800;color:var(--dmr-ink);flex:1;margin:0}
.dmr-card{background:var(--dmr-surface);border:1.5px solid var(--dmr-border);border-radius:var(--dmr-radius);overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04);margin-bottom:16px}
.dmr-card-hdr{padding:14px 18px;background:#faf8f5;border-bottom:1px solid var(--dmr-border);font-size:.9rem;font-weight:700;color:var(--dmr-ink);display:flex;align-items:center;gap:8px}
.dmr-card-body{padding:18px}
.dmr-info-row{display:flex;gap:12px;margin-bottom:4px}
.dmr-info-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--dmr-faint);min-width:80px}
.dmr-info-val{font-size:.88rem;font-weight:700;color:var(--dmr-ink)}
.dmr-input,.dmr-select{width:100%;padding:11px 14px;border:1.5px solid var(--dmr-border);border-radius:10px;font-size:.92rem;color:var(--dmr-ink);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box;min-height:44px}
.dmr-input:focus,.dmr-select:focus{border-color:var(--dmr-gold);box-shadow:0 0 0 3px rgba(160,114,58,.12)}
.dmr-label{display:block;font-size:.72rem;font-weight:700;color:var(--dmr-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.dmr-field{margin-bottom:14px}
.dmr-num-input{font-size:1.6rem;font-weight:800;text-align:center;padding:12px 8px;border-radius:12px;border:2px solid var(--dmr-border);width:100%;background:#fff;color:var(--dmr-ink);outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box;min-height:56px}
.dmr-num-input:focus{border-color:var(--dmr-gold);box-shadow:0 0 0 3px rgba(160,114,58,.12)}
.dmr-meal-card{background:var(--dmr-surface);border:1.5px solid var(--dmr-border);border-radius:14px;padding:18px;margin-bottom:12px;transition:border-color .14s}
.dmr-meal-card:focus-within{border-color:var(--dmr-gold);box-shadow:0 0 0 3px rgba(160,114,58,.08)}
.dmr-meal-title{font-size:1.05rem;font-weight:800;color:var(--dmr-ink);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.dmr-count-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:10px}
.dmr-count-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--dmr-faint);text-align:center;margin-bottom:6px}
.dmr-diff-badge{text-align:center;font-size:.82rem;font-weight:700;padding:4px 12px;border-radius:8px;margin-top:6px;min-height:24px}
.dmr-diff-over{background:rgba(22,163,74,.1);color:var(--dmr-green)}
.dmr-diff-under{background:rgba(220,38,38,.1);color:var(--dmr-red)}
.dmr-diff-neutral{background:rgba(0,0,0,.05);color:var(--dmr-faint)}
.dmr-textarea{resize:vertical;min-height:70px}
.dmr-sticky-save{position:fixed;bottom:0;left:0;right:0;background:rgba(255,255,255,.95);backdrop-filter:blur(8px);border-top:1.5px solid var(--dmr-border);padding:14px 20px;display:flex;gap:12px;align-items:center;z-index:100;box-shadow:0 -2px 12px rgba(0,0,0,.08)}
.dmr-btn-primary-lg{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:1rem;font-weight:800;border:none;cursor:pointer;background:var(--dmr-gold);color:#fff;box-shadow:0 3px 10px rgba(160,114,58,.3);flex:1;justify-content:center;min-height:52px}
.dmr-btn-primary-lg:hover{background:var(--dmr-gold-hi)}
.dmr-btn-cancel-sm{display:inline-flex;align-items:center;gap:6px;padding:12px 20px;border-radius:12px;font-size:.88rem;font-weight:700;border:1.5px solid var(--dmr-border);cursor:pointer;background:#fff;color:var(--dmr-muted);text-decoration:none;min-height:52px}
.dmr-btn-cancel-sm:hover{background:#f7f5f2;color:var(--dmr-ink);text-decoration:none}
</style>
@endpush

<div class="dmr-wrap">
    <div class="dmr-page-hdr">
        <a href="{{ route('meal-register.entries.index') }}" style="color:var(--dmr-gold);text-decoration:none;font-size:.85rem">
            <i class="bi bi-arrow-left"></i> Entries
        </a>
        <h1 class="dmr-page-title">Edit Entry</h1>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4" style="border-radius:10px;font-size:.85rem">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Read-only header --}}
    <div class="dmr-card">
        <div class="dmr-card-hdr"><i class="bi bi-info-circle"></i> Entry Info</div>
        <div class="dmr-card-body">
            <div class="dmr-info-row">
                <span class="dmr-info-label">Client</span>
                <span class="dmr-info-val">{{ $entry->client->name ?? '—' }}</span>
            </div>
            <div class="dmr-info-row">
                <span class="dmr-info-label">Date</span>
                <span class="dmr-info-val">{{ $entry->meal_date->format('d M Y, D') }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('meal-register.entries.update', $entry) }}">
        @csrf @method('PUT')

        {{-- Meal Cards --}}
        <div class="dmr-card">
            <div class="dmr-card-hdr"><i class="bi bi-egg-fried"></i> Meal Counts</div>
            <div class="dmr-card-body">
                @foreach($mealTypes as $key => $type)
                @php
                    $idx  = array_search($key, array_keys($mealTypes));
                    $item = $entry->items->firstWhere('meal_type', $key);
                    $oldItems = old('items', []);
                    $p = $oldItems[$idx]['planned_count'] ?? ($item ? $item->planned_count : 0);
                    $a = $oldItems[$idx]['actual_count']  ?? ($item ? $item->actual_count  : '');
                @endphp
                <div class="dmr-meal-card" data-meal="{{ $key }}">
                    <div class="dmr-meal-title">{{ $type['icon'] }} {{ $type['label'] }}</div>
                    <input type="hidden" name="items[{{ $idx }}][meal_type]" value="{{ $key }}">
                    <div class="dmr-count-grid">
                        <div>
                            <div class="dmr-count-label">Planned</div>
                            <input type="number" name="items[{{ $idx }}][planned_count]"
                                   class="dmr-num-input planned-input" data-meal="{{ $key }}"
                                   value="{{ $p }}" min="0" inputmode="numeric" placeholder="0" required>
                        </div>
                        <div>
                            <div class="dmr-count-label">Actual</div>
                            <input type="number" name="items[{{ $idx }}][actual_count]"
                                   class="dmr-num-input actual-input" data-meal="{{ $key }}"
                                   value="{{ $a !== null ? $a : '' }}" min="0" inputmode="numeric" placeholder="—">
                        </div>
                    </div>
                    <div class="dmr-diff-badge dmr-diff-neutral diff-badge" id="diff-{{ $key }}">—</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Remarks --}}
        <div class="dmr-card">
            <div class="dmr-card-hdr"><i class="bi bi-chat-left-text"></i> Remarks</div>
            <div class="dmr-card-body">
                <textarea name="remarks" class="dmr-input dmr-textarea"
                          placeholder="Optional notes…" maxlength="500">{{ old('remarks', $entry->remarks) }}</textarea>
            </div>
        </div>

        <div class="dmr-sticky-save">
            <a href="{{ route('meal-register.entries.show', $entry) }}" class="dmr-btn-cancel-sm">
                <i class="bi bi-x-lg"></i> Cancel
            </a>
            <button type="submit" class="dmr-btn-primary-lg">
                <i class="bi bi-check-lg"></i> Update Entry
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    function updateDiff(mealKey) {
        const card  = document.querySelector(`.dmr-meal-card[data-meal="${mealKey}"]`);
        const badge = document.getElementById('diff-' + mealKey);
        if (!card || !badge) return;
        const p = parseInt(card.querySelector('.planned-input').value) || 0;
        const aRaw = card.querySelector('.actual-input').value;
        if (aRaw === '') { badge.textContent = '—'; badge.className = 'dmr-diff-badge dmr-diff-neutral diff-badge'; return; }
        const a = parseInt(aRaw) || 0;
        const d = a - p;
        if (d > 0)      { badge.textContent = `+${d} extra`; badge.className = 'dmr-diff-badge dmr-diff-over diff-badge'; }
        else if (d < 0) { badge.textContent = `${d} short`;  badge.className = 'dmr-diff-badge dmr-diff-under diff-badge'; }
        else            { badge.textContent = '✓ On target'; badge.className = 'dmr-diff-badge dmr-diff-neutral diff-badge'; }
    }
    document.querySelectorAll('.planned-input,.actual-input').forEach(function (inp) {
        inp.addEventListener('input', function () { updateDiff(inp.dataset.meal); });
        updateDiff(inp.dataset.meal);
    });
})();
</script>
@endpush
</x-admin-layout>
