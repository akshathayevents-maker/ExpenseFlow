<x-admin-layout title="Meal Entry">
@push('styles')
<style>
:root{
    --dmr-gold:#a0723a;--dmr-gold-hi:#b8832a;
    --dmr-ink:#1c1712;--dmr-muted:#7a6e62;--dmr-faint:#b0a89a;
    --dmr-border:#e8e2d8;--dmr-surface:#fff;
    --dmr-radius:16px;
    --dmr-green:#16a34a;--dmr-red:#dc2626;
}
.dmr-wrap{max-width:600px;margin:0 auto;padding-bottom:60px}
.dmr-page-hdr{display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.dmr-page-title{font-size:1.3rem;font-weight:800;color:var(--dmr-ink);flex:1;margin:0}
.dmr-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;font-size:.88rem;font-weight:700;border:1.5px solid transparent;cursor:pointer;text-decoration:none;transition:all .14s;min-height:44px}
.dmr-btn--primary{background:var(--dmr-gold);color:#fff}
.dmr-btn--primary:hover{background:var(--dmr-gold-hi);color:#fff}
.dmr-btn--ghost{background:transparent;border-color:var(--dmr-border);color:var(--dmr-muted)}
.dmr-btn--ghost:hover{border-color:var(--dmr-gold);color:var(--dmr-gold);text-decoration:none}
.dmr-btn--danger-sm{background:transparent;border-color:rgba(220,38,38,.3);color:var(--dmr-red)}
.dmr-btn--danger-sm:hover{background:rgba(220,38,38,.06)}
.dmr-card{background:var(--dmr-surface);border:1.5px solid var(--dmr-border);border-radius:var(--dmr-radius);overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04);margin-bottom:16px}
.dmr-card-hdr{padding:14px 18px;background:#faf8f5;border-bottom:1px solid var(--dmr-border);font-size:.9rem;font-weight:700;color:var(--dmr-ink);display:flex;align-items:center;gap:8px}
.dmr-card-body{padding:18px}
.dmr-info-row{display:flex;gap:12px;padding:8px 0;border-bottom:1px solid #f0ece6}
.dmr-info-row:last-child{border-bottom:none}
.dmr-info-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--dmr-faint);min-width:90px}
.dmr-info-val{font-size:.88rem;color:var(--dmr-ink);font-weight:600}
/* meal display */
.dmr-meal-block{border:1.5px solid var(--dmr-border);border-radius:14px;padding:16px;margin-bottom:12px}
.dmr-meal-title{font-size:1rem;font-weight:800;color:var(--dmr-ink);margin-bottom:12px}
.dmr-count-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.dmr-count-chip{display:flex;flex-direction:column;align-items:center;background:#f7f5f2;border-radius:10px;padding:10px 18px;min-width:80px}
.dmr-count-chip-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--dmr-faint);margin-bottom:4px}
.dmr-count-chip-val{font-size:1.5rem;font-weight:800;color:var(--dmr-ink)}
.dmr-count-chip-val.over{color:var(--dmr-green)}
.dmr-count-chip-val.under{color:var(--dmr-red)}
.dmr-diff-chip{display:flex;flex-direction:column;align-items:center;border-radius:10px;padding:10px 18px;min-width:80px}
.dmr-diff-chip.dmr-over{background:rgba(22,163,74,.1)}
.dmr-diff-chip.dmr-under{background:rgba(220,38,38,.1)}
.dmr-diff-chip.dmr-neutral{background:rgba(0,0,0,.05)}
.dmr-arrow{font-size:1.4rem;color:var(--dmr-faint)}
</style>
@endpush

<div class="dmr-wrap">
    <div class="dmr-page-hdr">
        <a href="{{ route('meal-register.entries.index') }}" style="color:var(--dmr-gold);text-decoration:none;font-size:.85rem">
            <i class="bi bi-arrow-left"></i> Entries
        </a>
        <h1 class="dmr-page-title">Meal Entry</h1>
        <a href="{{ route('meal-register.entries.create', ['client_id' => $entry->meal_client_id, 'entry_date' => $entry->entry_date->toDateString()]) }}" class="dmr-btn dmr-btn--ghost">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @if(in_array(auth()->user()->role, ['admin','manager']))
        <form method="POST" action="{{ route('meal-register.entries.destroy', $entry) }}"
              onsubmit="return confirm('Delete this entry?')">
            @csrf @method('DELETE')
            <button type="submit" class="dmr-btn dmr-btn--danger-sm"><i class="bi bi-trash"></i> Delete</button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Entry info --}}
    <div class="dmr-card">
        <div class="dmr-card-hdr"><i class="bi bi-info-circle"></i> Entry Details</div>
        <div class="dmr-card-body">
            <div class="dmr-info-row">
                <span class="dmr-info-label">Client</span>
                <span class="dmr-info-val">{{ $entry->client->name ?? '—' }}</span>
            </div>
            <div class="dmr-info-row">
                <span class="dmr-info-label">Date</span>
                <span class="dmr-info-val">{{ $entry->entry_date->format('d M Y, D') }}</span>
            </div>
            <div class="dmr-info-row">
                <span class="dmr-info-label">Total Planned</span>
                <span class="dmr-info-val">{{ $entry->totalPlanned() }}</span>
            </div>
            <div class="dmr-info-row">
                <span class="dmr-info-label">Total Actual</span>
                <span class="dmr-info-val">{{ $entry->totalActual() }}</span>
            </div>
            @if($entry->remarks)
            <div class="dmr-info-row">
                <span class="dmr-info-label">Remarks</span>
                <span class="dmr-info-val">{{ $entry->remarks }}</span>
            </div>
            @endif
            <div class="dmr-info-row">
                <span class="dmr-info-label">Created by</span>
                <span class="dmr-info-val">{{ $entry->creator->name ?? '—' }} &mdash; {{ $entry->created_at->format('d M Y H:i') }}</span>
            </div>
            @if($entry->updater)
            <div class="dmr-info-row">
                <span class="dmr-info-label">Updated by</span>
                <span class="dmr-info-val">{{ $entry->updater->name }} &mdash; {{ $entry->updated_at->format('d M Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Meal blocks --}}
    <div class="dmr-card">
        <div class="dmr-card-hdr"><i class="bi bi-egg-fried"></i> Meal Counts</div>
        <div class="dmr-card-body">
            @foreach($entry->items as $item)
            @php $d = $item->difference(); @endphp
            <div class="dmr-meal-block">
                <div class="dmr-meal-title">{{ $item->mealIcon() }} {{ $item->mealLabel() }}</div>
                <div class="dmr-count-row">
                    <div class="dmr-count-chip">
                        <span class="dmr-count-chip-label">Planned</span>
                        <span class="dmr-count-chip-val">{{ $item->planned_count }}</span>
                    </div>
                    <span class="dmr-arrow">→</span>
                    <div class="dmr-count-chip">
                        <span class="dmr-count-chip-label">Actual</span>
                        <span class="dmr-count-chip-val {{ $d !== null && $d > 0 ? 'over' : ($d !== null && $d < 0 ? 'under' : '') }}">
                            {{ $item->actual_count ?? '—' }}
                        </span>
                    </div>
                    @if($d !== null)
                    <div class="dmr-diff-chip {{ $item->diffClass() }}">
                        <span class="dmr-count-chip-label">Diff</span>
                        <span style="font-size:1.2rem;font-weight:800;color:{{ $d > 0 ? 'var(--dmr-green)' : ($d < 0 ? 'var(--dmr-red)' : 'var(--dmr-faint)') }}">
                            {{ $d > 0 ? '+' : '' }}{{ $d }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
</x-admin-layout>
