<x-admin-layout title="Meal Entries">
@push('styles')
<style>
:root{
    --me-gold:#a0723a;--me-gold-dim:rgba(160,114,58,.1);
    --me-ink:#1c1712;--me-muted:#7a6e62;--me-faint:#b0a89a;
    --me-border:#e8e2d8;--me-surface:#fff;
    --me-green:#16a34a;--me-orange:#d97706;--me-blue:#2563eb;
}
.mei-wrap{max-width:900px;margin:0 auto}
.mei-hdr{display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.mei-title{font-size:1.2rem;font-weight:900;color:var(--me-ink);margin:0;flex:1}
.mei-btn-new{display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:10px;background:var(--me-gold);color:#fff;font-size:.85rem;font-weight:700;text-decoration:none}
.mei-btn-new:hover{background:#b8832a;color:#fff}
.mei-filters{background:#fff;border:1.5px solid var(--me-border);border-radius:14px;padding:16px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end}
.mei-filter-group{display:flex;flex-direction:column;gap:4px;flex:1;min-width:140px}
.mei-filter-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--me-faint)}
.mei-filter-ctrl{padding:9px 12px;border:1.5px solid var(--me-border);border-radius:8px;font-size:.85rem;color:var(--me-ink);background:#fff;outline:none;min-height:40px}
.mei-filter-ctrl:focus{border-color:var(--me-gold)}
.mei-filter-btn{padding:9px 18px;border-radius:8px;font-size:.83rem;font-weight:700;border:none;cursor:pointer;min-height:40px}
.mei-filter-apply{background:var(--me-gold);color:#fff}
.mei-filter-clear{background:#f7f5f2;color:var(--me-muted);border:1.5px solid var(--me-border);text-decoration:none;display:inline-flex;align-items:center}
.mei-card{background:var(--me-surface);border:1.5px solid var(--me-border);border-radius:14px;padding:16px;margin-bottom:12px;transition:box-shadow .13s}
.mei-card:hover{box-shadow:0 4px 18px rgba(0,0,0,.07)}
.mei-card-top{display:flex;align-items:flex-start;gap:12px;margin-bottom:12px}
.mei-date-badge{background:rgba(160,114,58,.1);color:var(--me-gold);font-size:.78rem;font-weight:800;padding:5px 10px;border-radius:8px;white-space:nowrap}
.mei-client-name{font-size:.95rem;font-weight:800;color:var(--me-ink);flex:1}
.mei-show-link{font-size:.78rem;font-weight:700;color:var(--me-gold);text-decoration:none;white-space:nowrap}
.mei-show-link:hover{text-decoration:underline}
.mei-meal-row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px}
.mei-meal-chip{display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:8px;background:#faf8f5;border:1px solid var(--me-border);font-size:.78rem}
.mei-meal-label{font-weight:700;color:var(--me-ink)}
.mei-meal-counts{color:var(--me-muted)}
.mei-var-over{color:var(--me-green);font-weight:700}
.mei-var-under{color:var(--me-orange);font-weight:700}
.mei-var-eq{color:var(--me-blue);font-weight:700}
.mei-totals{display:flex;gap:16px;flex-wrap:wrap;padding-top:8px;border-top:1px solid var(--me-border)}
.mei-total-item{font-size:.75rem;color:var(--me-muted)}
.mei-total-item strong{color:var(--me-ink)}
.mei-empty{text-align:center;padding:48px 20px;color:var(--me-faint);font-size:.9rem}
.mei-badge-employee{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;background:rgba(37,99,235,.07);color:var(--me-blue);font-size:.72rem;font-weight:700;margin-bottom:12px}
</style>
@endpush

<div class="mei-wrap">
    <div class="mei-hdr">
        <h1 class="mei-title"><i class="bi bi-journal-text me-2" style="color:var(--me-gold)"></i>Meal Entries</h1>
        @if(!$isEmployee)
        <a href="{{ route('meal-register.entries.create') }}" class="mei-btn-new">
            <i class="bi bi-plus-lg"></i> New Entry
        </a>
        @endif
    </div>

    @if($isEmployee)
    <div class="mei-badge-employee"><i class="bi bi-person-circle"></i> Showing last 7 days</div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" style="border-radius:10px;font-size:.84rem">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filters (admin/manager only) --}}
    @if(!$isEmployee)
    <form method="GET" class="mei-filters">
        <div class="mei-filter-group">
            <span class="mei-filter-label">Client</span>
            <select name="client_id" class="mei-filter-ctrl">
                <option value="">All clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mei-filter-group">
            <span class="mei-filter-label">From</span>
            <input type="date" name="from" class="mei-filter-ctrl" value="{{ request('from') }}">
        </div>
        <div class="mei-filter-group">
            <span class="mei-filter-label">To</span>
            <input type="date" name="to" class="mei-filter-ctrl" value="{{ request('to') }}">
        </div>
        <button type="submit" class="mei-filter-btn mei-filter-apply"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if(request()->hasAny(['client_id','from','to']))
        <a href="{{ route('meal-register.entries.index') }}" class="mei-filter-btn mei-filter-clear">Clear</a>
        @endif
    </form>
    @endif

    @forelse($entries as $entry)
    @php
        $types = \App\Models\MealEntryItem::mealTypes();
        $itemsByType = $entry->items->keyBy('meal_type');
    @endphp
    <div class="mei-card">
        <div class="mei-card-top">
            <span class="mei-date-badge"><i class="bi bi-calendar3 me-1"></i>{{ $entry->entry_date->format('d M Y') }}</span>
            <span class="mei-client-name">{{ $entry->client?->name ?? '—' }}</span>
            <a href="{{ route('meal-register.entries.show', $entry) }}" class="mei-show-link">
                <i class="bi bi-eye me-1"></i>View
            </a>
        </div>

        <div class="mei-meal-row">
            @foreach($types as $key => $meta)
            @php $item = $itemsByType[$key] ?? null; @endphp
            @if($item)
            <div class="mei-meal-chip">
                <span>{{ $meta['icon'] }}</span>
                <span class="mei-meal-label">{{ $meta['label'] }}</span>
                <span class="mei-meal-counts">
                    P:{{ $item->planned_count ?? '—' }}
                    / A:{{ $item->actual_count ?? '—' }}
                    @if($item->planned_count !== null && $item->actual_count !== null)
                        @php $v = $item->actual_count - $item->planned_count; @endphp
                        @if($v > 0)<span class="mei-var-over">(+{{ $v }})</span>
                        @elseif($v < 0)<span class="mei-var-under">({{ $v }})</span>
                        @else<span class="mei-var-eq">(=)</span>@endif
                    @endif
                </span>
            </div>
            @endif
            @endforeach
        </div>

        <div class="mei-totals">
            <div class="mei-total-item">Planned: <strong>{{ $entry->totalPlanned() }}</strong></div>
            <div class="mei-total-item">Actual: <strong>{{ $entry->totalActual() }}</strong></div>
            @php $totalVar = $entry->totalActual() - $entry->totalPlanned(); @endphp
            @if($totalVar !== 0)
            <div class="mei-total-item">
                Variance: <strong class="{{ $totalVar > 0 ? 'mei-var-over' : 'mei-var-under' }}">{{ $totalVar > 0 ? '+' : '' }}{{ $totalVar }}</strong>
            </div>
            @endif
            @if($entry->remarks)
            <div class="mei-total-item" style="flex:1">
                <i class="bi bi-chat-left-text me-1" style="color:var(--me-faint)"></i>{{ Str::limit($entry->remarks, 60) }}
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="mei-empty">
        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4"></i>
        No entries found.
        @if(!$isEmployee)
        <br><a href="{{ route('meal-register.entries.create') }}" style="color:var(--me-gold);font-weight:700">Create the first entry</a>
        @endif
    </div>
    @endforelse

    @if($entries->hasPages())
    <div class="mt-4">{{ $entries->links() }}</div>
    @endif
</div>
</x-admin-layout>
