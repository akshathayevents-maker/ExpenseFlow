<x-admin-layout title="Meal Entries">
@push('styles')
<style>
/* Meal Entries — Operations Register */
:root {
    --me-gold:    #a0723a;
    --me-gold-bg: #fdf7ee;
    --me-ink:     #1c1712;
    --me-sub:     #4a4238;
    --me-muted:   #7a6e62;
    --me-faint:   #b0a89a;
    --me-border:  rgba(0,0,0,.08);
    --me-surface: #fff;
    --me-bg:      #f5f3ef;
    --me-green:   #16a34a;
    --me-green-bg:#f0fdf4;
    --me-red:     #dc2626;
    --me-red-bg:  #fef2f2;
    --me-gray-bg: #f3f4f6;
    --me-shadow:  0 1px 2px rgba(0,0,0,.05), 0 3px 10px rgba(0,0,0,.06);
    --me-shadow-h:0 2px 6px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.1);
    --me-r:       14px;
}

#main-content { background: var(--me-bg) !important; }

.me-wrap { max-width: 680px; margin: 0 auto; padding: 0 0 80px; }

/* ── Sticky toolbar ── */
.me-toolbar {
    align-items: center;
    background: var(--me-bg);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 14px 0 10px;
    position: sticky;
    top: 0;
    z-index: 50;
}
.me-page-title {
    color: var(--me-ink);
    font-size: 1.05rem;
    font-weight: 900;
    margin: 0;
}
.me-btn-new {
    align-items: center;
    background: var(--me-gold);
    border-radius: 10px;
    color: #fff;
    display: inline-flex;
    font-size: .78rem;
    font-weight: 720;
    gap: 5px;
    padding: 8px 14px;
    text-decoration: none;
    white-space: nowrap;
}
.me-btn-new:hover { background: #b8832a; color: #fff; }

/* ── Filters ── */
.me-filter-bar {
    background: var(--me-surface);
    border: 1px solid var(--me-border);
    border-radius: var(--me-r);
    box-shadow: var(--me-shadow);
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
    padding: 12px 14px;
}
.me-fg { display: flex; flex-direction: column; flex: 1; gap: 3px; min-width: 120px; }
.me-fg-lbl { color: var(--me-faint); font-size: .6rem; font-weight: 720; letter-spacing: .07em; text-transform: uppercase; }
.me-fc {
    appearance: none;
    background: var(--me-bg);
    border: 1px solid var(--me-border);
    border-radius: 8px;
    color: var(--me-ink);
    font-size: .8rem;
    min-height: 36px;
    outline: none;
    padding: 0 10px;
    width: 100%;
}
.me-fc:focus { border-color: var(--me-gold); }
.me-filter-btns { align-items: flex-end; display: flex; flex-shrink: 0; gap: 6px; }
.me-fb {
    align-items: center;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    font-size: .78rem;
    font-weight: 700;
    gap: 5px;
    min-height: 36px;
    padding: 0 13px;
    text-decoration: none;
    white-space: nowrap;
}
.me-fb.--apply { background: var(--me-gold); border: none; color: #fff; }
.me-fb.--apply:hover { background: #b8832a; }
.me-fb.--clear { background: none; border: 1px solid var(--me-border); color: var(--me-muted); }
.me-fb.--clear:hover { border-color: var(--me-ink); color: var(--me-ink); }

/* ── Entry card ── */
.me-card {
    background: var(--me-surface);
    border: 1px solid var(--me-border);
    border-radius: var(--me-r);
    box-shadow: var(--me-shadow);
    cursor: pointer;
    display: block;
    margin-bottom: 10px;
    overflow: hidden;
    text-decoration: none;
    transition: box-shadow .15s, transform .12s;
}
.me-card:hover {
    box-shadow: var(--me-shadow-h);
    text-decoration: none;
    transform: translateY(-1px);
}

/* Card header */
.me-card-hdr {
    align-items: center;
    border-bottom: 1px solid var(--me-border);
    display: flex;
    gap: 10px;
    padding: 12px 15px 10px;
}
.me-client {
    color: var(--me-ink);
    flex: 1;
    font-size: .98rem;
    font-weight: 800;
    letter-spacing: -.01em;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.me-date {
    color: var(--me-muted);
    flex-shrink: 0;
    font-size: .71rem;
    font-weight: 640;
}
.me-status-chip {
    border-radius: 100px;
    flex-shrink: 0;
    font-size: .62rem;
    font-weight: 720;
    padding: 3px 9px;
    white-space: nowrap;
}
.me-status-chip.--done    { background: var(--me-green-bg); color: var(--me-green); }
.me-status-chip.--partial { background: var(--me-gold-bg);  color: var(--me-gold); }
.me-status-chip.--pending { background: var(--me-gray-bg);  color: var(--me-muted); }

/* Meal rows */
.me-meals { padding: 4px 0; }
.me-meal-row {
    align-items: center;
    display: flex;
    gap: 0;
    padding: 7px 15px;
    border-bottom: 1px solid rgba(0,0,0,.04);
}
.me-meal-row:last-child { border-bottom: none; }
.me-meal-icon { flex-shrink: 0; font-size: .95rem; margin-right: 8px; }
.me-meal-name {
    color: var(--me-sub);
    flex: 1;
    font-size: .77rem;
    font-weight: 680;
}
/* Plan / Actual / Variance triplet */
.me-meal-nums {
    align-items: baseline;
    display: flex;
    flex-shrink: 0;
    gap: 0;
}
.me-num-cell {
    min-width: 44px;
    text-align: right;
}
.me-num-val {
    color: var(--me-ink);
    display: block;
    font-size: .86rem;
    font-weight: 800;
    line-height: 1;
}
.me-num-val.--gray  { color: var(--me-faint); font-weight: 500; }
.me-num-val.--green { color: var(--me-green); }
.me-num-val.--red   { color: var(--me-red); }
.me-num-lbl {
    color: var(--me-faint);
    display: block;
    font-size: .56rem;
    font-weight: 680;
    letter-spacing: .04em;
    margin-top: 1px;
    text-transform: uppercase;
}
.me-num-div {
    align-self: center;
    color: var(--me-border);
    font-size: .7rem;
    margin: 0 4px;
    padding-bottom: 12px;
}

/* Summary footer */
.me-footer {
    border-top: 1px solid var(--me-border);
    display: flex;
}
.me-foot-cell {
    border-right: 1px solid var(--me-border);
    flex: 1;
    padding: 8px 10px;
    text-align: center;
}
.me-foot-cell:last-child { border-right: none; }
.me-foot-val {
    color: var(--me-ink);
    display: block;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1;
}
.me-foot-val.--green { color: var(--me-green); }
.me-foot-val.--red   { color: var(--me-red); }
.me-foot-val.--muted { color: var(--me-muted); }
.me-foot-lbl {
    color: var(--me-faint);
    display: block;
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .05em;
    margin-top: 2px;
    text-transform: uppercase;
}

/* Open entry link */
.me-open-link {
    background: var(--me-bg);
    border-top: 1px solid var(--me-border);
    color: var(--me-gold);
    display: block;
    font-size: .72rem;
    font-weight: 720;
    padding: 8px 15px;
    text-align: right;
    text-decoration: none;
}

/* Empty state */
.me-empty {
    padding: 48px 20px;
    text-align: center;
}
.me-empty-ico { color: var(--me-faint); display: block; font-size: 2rem; margin-bottom: 10px; }
.me-empty-txt { color: var(--me-muted); font-size: .85rem; }

/* Remarks */
.me-remarks {
    border-top: 1px solid var(--me-border);
    color: var(--me-muted);
    font-size: .7rem;
    padding: 6px 15px;
}
.me-remarks i { margin-right: 4px; opacity: .5; }

/* Employee badge */
.me-emp-badge {
    align-items: center;
    background: rgba(37,99,235,.06);
    border-radius: 8px;
    color: #2563eb;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 700;
    gap: 5px;
    margin-bottom: 12px;
    padding: 5px 11px;
}

@media (max-width: 480px) {
    .me-filter-bar { flex-direction: column; }
    .me-fg { min-width: 100%; }
    .me-filter-btns { flex-direction: row; width: 100%; }
    .me-fb { flex: 1; justify-content: center; }
}
</style>
@endpush

<div class="me-wrap px-3 px-md-0">

    {{-- Toolbar --}}
    <div class="me-toolbar">
        <h1 class="me-page-title"><i class="bi bi-journal-check me-2" style="color:var(--me-gold)"></i>Meal Register</h1>
        @if(!$isEmployee)
        <a href="{{ route('meal-register.entries.create') }}" class="me-btn-new">
            <i class="bi bi-plus-lg"></i> New Entry
        </a>
        @endif
    </div>

    @if($isEmployee)
    <div class="me-emp-badge"><i class="bi bi-person-circle"></i> Showing your last 7 days</div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" style="border-radius:10px;font-size:.83rem">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filters --}}
    @if(!$isEmployee)
    <form method="GET" class="me-filter-bar">
        <div class="me-fg">
            <span class="me-fg-lbl">Client</span>
            <select name="client_id" class="me-fc">
                <option value="">All clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="me-fg" style="max-width:148px">
            <span class="me-fg-lbl">From</span>
            <input type="date" name="from" class="me-fc" value="{{ request('from') }}">
        </div>
        <div class="me-fg" style="max-width:148px">
            <span class="me-fg-lbl">To</span>
            <input type="date" name="to" class="me-fc" value="{{ request('to') }}">
        </div>
        <div class="me-filter-btns">
            <button type="submit" class="me-fb --apply"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->hasAny(['client_id','from','to']))
            <a href="{{ route('meal-register.entries.index') }}" class="me-fb --clear"><i class="bi bi-x"></i></a>
            @endif
        </div>
    </form>
    @endif

    {{-- Entry cards --}}
    @forelse($entries as $entry)
    @php
        $types      = \App\Models\MealEntryItem::mealTypes();
        $byType     = $entry->items->keyBy('meal_type');
        $totPlan    = $entry->totalPlanned();
        $totAct     = $entry->totalActual();
        $totVar     = $totAct - $totPlan;
        $hasActuals = $totAct > 0;
        $allFilled  = $byType->filter(fn($i) => $i->planned_count !== null)->every(fn($i) => $i->actual_count !== null);

        if ($hasActuals && $allFilled) {
            $stChip = 'Completed'; $stCls = '--done';
        } elseif ($hasActuals) {
            $stChip = 'Partial';   $stCls = '--partial';
        } else {
            $stChip = 'Pending';   $stCls = '--pending';
        }
    @endphp

    <a href="{{ route('meal-register.entries.show', $entry) }}" class="me-card">

        {{-- Header --}}
        <div class="me-card-hdr">
            <span class="me-client">{{ $entry->client?->name ?? '—' }}</span>
            <span class="me-date">{{ $entry->entry_date->format('d M Y') }}</span>
            <span class="me-status-chip {{ $stCls }}">{{ $stChip }}</span>
        </div>

        {{-- Meal rows --}}
        <div class="me-meals">
            @foreach($types as $key => $meta)
            @php
                $item = $byType[$key] ?? null;
                if (!$item) continue;
                $plan = $item->planned_count;
                $act  = $item->actual_count;
                $var  = ($plan !== null && $act !== null) ? ($act - $plan) : null;
                $varCls = $var === null ? '--gray' : ($var > 0 ? '--green' : ($var < 0 ? '--red' : '--gray'));
                $varStr = $var === null ? '—' : ($var > 0 ? '+' . $var : (string)$var);
            @endphp
            <div class="me-meal-row">
                <span class="me-meal-icon">{{ $meta['icon'] }}</span>
                <span class="me-meal-name">{{ $meta['label'] }}</span>
                <div class="me-meal-nums">
                    <div class="me-num-cell">
                        <span class="me-num-val {{ $plan === null ? '--gray' : '' }}">{{ $plan ?? '—' }}</span>
                        <span class="me-num-lbl">Plan</span>
                    </div>
                    <span class="me-num-div">·</span>
                    <div class="me-num-cell">
                        <span class="me-num-val {{ $act === null ? '--gray' : '' }}">{{ $act ?? '—' }}</span>
                        <span class="me-num-lbl">Act</span>
                    </div>
                    <span class="me-num-div">·</span>
                    <div class="me-num-cell">
                        <span class="me-num-val {{ $varCls }}">{{ $varStr }}</span>
                        <span class="me-num-lbl">Var</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Remarks --}}
        @if($entry->remarks)
        <div class="me-remarks">
            <i class="bi bi-chat-left-text"></i>{{ Str::limit($entry->remarks, 80) }}
        </div>
        @endif

        {{-- Summary footer --}}
        <div class="me-footer">
            <div class="me-foot-cell">
                <span class="me-foot-val">{{ $totPlan ?: '—' }}</span>
                <span class="me-foot-lbl">Planned</span>
            </div>
            <div class="me-foot-cell">
                <span class="me-foot-val {{ $totAct === 0 ? '--muted' : '' }}">{{ $totAct ?: '—' }}</span>
                <span class="me-foot-lbl">Actual</span>
            </div>
            <div class="me-foot-cell">
                @if($totPlan > 0 && $totAct > 0)
                <span class="me-foot-val {{ $totVar > 0 ? '--green' : ($totVar < 0 ? '--red' : '--muted') }}">
                    {{ $totVar > 0 ? '+' . $totVar : ($totVar === 0 ? '=' : $totVar) }}
                </span>
                @else
                <span class="me-foot-val --muted">—</span>
                @endif
                <span class="me-foot-lbl">Variance</span>
            </div>
        </div>

    </a>
    @empty
    <div class="me-empty">
        <i class="bi bi-inbox me-empty-ico"></i>
        <div class="me-empty-txt">No entries found.</div>
        @if(!$isEmployee)
        <a href="{{ route('meal-register.entries.create') }}" style="color:var(--me-gold);font-weight:720;font-size:.82rem;text-decoration:none;display:inline-block;margin-top:8px">
            + Create first entry
        </a>
        @endif
    </div>
    @endforelse

    @if($entries->hasPages())
    <div class="mt-4">{{ $entries->links() }}</div>
    @endif

</div>
</x-admin-layout>
