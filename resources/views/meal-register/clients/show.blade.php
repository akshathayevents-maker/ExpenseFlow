<x-admin-layout title="{{ $client->name }}">
@push('styles')
<style>
:root{
    --cs-gold:#a0723a;--cs-gold-hi:#b8832a;--cs-gold-dim:rgba(160,114,58,.1);
    --cs-ink:#1c1712;--cs-muted:#7a6e62;--cs-faint:#b0a89a;
    --cs-border:#e8e2d8;--cs-surface:#fff;--cs-page:#f7f5f2;
    --cs-radius:16px;--cs-r-sm:10px;
    --cs-green:#16a34a;--cs-red:#dc2626;
    --cs-over:rgba(22,163,74,.1);--cs-under:rgba(220,38,38,.1);
}
.cs-wrap{max-width:860px;margin:0 auto;padding-bottom:60px}
.cs-page-hdr{display:flex;align-items:flex-start;gap:16px;margin-bottom:22px;flex-wrap:wrap}
.cs-back{color:var(--cs-gold);text-decoration:none;font-size:.84rem;display:inline-flex;align-items:center;gap:5px;margin-top:6px}
.cs-back:hover{text-decoration:underline}
.cs-hero{flex:1}
.cs-client-name{font-size:1.6rem;font-weight:900;color:var(--cs-ink);margin:0 0 4px}
.cs-client-meta{font-size:.84rem;color:var(--cs-muted);display:flex;flex-wrap:wrap;gap:12px;align-items:center}
.cs-status-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:.7rem;font-weight:700}
.cs-status-badge--active{background:rgba(22,163,74,.1);color:var(--cs-green)}
.cs-status-badge--inactive{background:rgba(0,0,0,.05);color:var(--cs-faint)}
.cs-hdr-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}
.cs-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--cs-r-sm);font-size:.84rem;font-weight:700;border:1.5px solid transparent;cursor:pointer;text-decoration:none;transition:all .14s;white-space:nowrap}
.cs-btn--primary{background:var(--cs-gold);color:#fff}
.cs-btn--primary:hover{background:var(--cs-gold-hi);color:#fff}
.cs-btn--ghost{background:transparent;border-color:var(--cs-border);color:var(--cs-muted)}
.cs-btn--ghost:hover{border-color:var(--cs-gold);color:var(--cs-gold)}
.cs-btn--danger{background:transparent;border-color:rgba(220,38,38,.3);color:var(--cs-red)}
.cs-btn--danger:hover{background:rgba(220,38,38,.06)}

/* Stats strip */
.cs-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
@media(max-width:600px){.cs-stats{grid-template-columns:repeat(2,1fr)}}
.cs-stat{background:var(--cs-surface);border:1.5px solid var(--cs-border);border-radius:var(--cs-r-sm);padding:16px 14px;text-align:center}
.cs-stat-val{font-size:1.6rem;font-weight:900;color:var(--cs-ink);line-height:1}
.cs-stat-lbl{font-size:.68rem;font-weight:700;color:var(--cs-faint);text-transform:uppercase;letter-spacing:.07em;margin-top:5px}
.cs-stat-val.--green{color:var(--cs-green)}
.cs-stat-val.--red{color:var(--cs-red)}

/* Cards */
.cs-card{background:var(--cs-surface);border:1.5px solid var(--cs-border);border-radius:var(--cs-radius);overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04);margin-bottom:16px}
.cs-card-hdr{padding:14px 18px;background:#faf8f5;border-bottom:1px solid var(--cs-border);font-size:.88rem;font-weight:700;color:var(--cs-ink);display:flex;align-items:center;gap:8px}
.cs-card-body{padding:18px}

/* Info grid */
.cs-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:480px){.cs-info-grid{grid-template-columns:1fr}}
.cs-info-row{display:flex;flex-direction:column;gap:3px}
.cs-info-lbl{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--cs-faint)}
.cs-info-val{font-size:.9rem;color:var(--cs-ink);font-weight:600}

/* Meal type summary */
.cs-type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
@media(max-width:480px){.cs-type-grid{grid-template-columns:1fr}}
.cs-type-card{background:#faf8f5;border:1px solid var(--cs-border);border-radius:10px;padding:14px;text-align:center}
.cs-type-title{font-size:.78rem;font-weight:700;color:var(--cs-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px}
.cs-type-num{font-size:1.2rem;font-weight:800;color:var(--cs-ink)}
.cs-type-sub{font-size:.72rem;color:var(--cs-faint);margin-top:2px}
.cs-diff-chip{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.72rem;font-weight:700;margin-top:5px}
.cs-diff-pos{background:var(--cs-over);color:var(--cs-green)}
.cs-diff-neg{background:var(--cs-under);color:var(--cs-red)}
.cs-diff-neu{background:rgba(0,0,0,.05);color:var(--cs-faint)}

/* Entry list */
.cs-entry{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--cs-border);text-decoration:none;color:inherit}
.cs-entry:last-child{border-bottom:none}
.cs-entry:hover .cs-entry-title{color:var(--cs-gold)}
.cs-entry-date{width:44px;text-align:center;flex-shrink:0}
.cs-entry-day{font-size:1.1rem;font-weight:800;color:var(--cs-ink);line-height:1}
.cs-entry-mon{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--cs-faint)}
.cs-entry-title{font-size:.88rem;font-weight:700;color:var(--cs-ink);flex:1}
.cs-entry-chips{display:flex;gap:5px;flex-wrap:wrap}
.cs-entry-chip{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:6px;background:#f0ece6;color:var(--cs-muted)}
.cs-entry-chip.--over{background:var(--cs-over);color:var(--cs-green)}
.cs-entry-chip.--under{background:var(--cs-under);color:var(--cs-red)}
.cs-empty{text-align:center;padding:40px 20px;color:var(--cs-faint)}
</style>
@endpush

<div class="cs-wrap">
    {{-- Header --}}
    <div class="cs-page-hdr">
        <a href="{{ route('meal-register.clients.index') }}" class="cs-back"><i class="bi bi-arrow-left"></i> Clients</a>
        <div class="cs-hero">
            <h1 class="cs-client-name">{{ $client->name }}</h1>
            <div class="cs-client-meta">
                @if($client->active)
                    <span class="cs-status-badge cs-status-badge--active"><i class="bi bi-circle-fill" style="font-size:.4rem;vertical-align:middle"></i> Active</span>
                @else
                    <span class="cs-status-badge cs-status-badge--inactive">Inactive</span>
                @endif
                @if($client->contact_person)<span>{{ $client->contact_person }}</span>@endif
                @if($client->mobile)<span><i class="bi bi-telephone" style="font-size:.75rem"></i> {{ $client->mobile }}</span>@endif
                @if($client->email)<span><i class="bi bi-envelope" style="font-size:.75rem"></i> {{ $client->email }}</span>@endif
            </div>
            @if(in_array(auth()->user()->role, ['admin','manager']))
            <div class="cs-hdr-actions mt-3">
                <a href="{{ route('meal-register.clients.edit', $client) }}" class="cs-btn cs-btn--ghost"><i class="bi bi-pencil"></i> Edit</a>
                <form method="POST" action="{{ route('meal-register.clients.toggle', $client) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="cs-btn {{ $client->active ? 'cs-btn--danger' : 'cs-btn--ghost' }}">
                        {{ $client->active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                <a href="{{ route('meal-register.entries.create') }}?client_id={{ $client->id }}" class="cs-btn cs-btn--primary">
                    <i class="bi bi-plus-lg"></i> New Entry
                </a>
            </div>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    @php $diff = $totalActual - $totalPlanned; @endphp
    <div class="cs-stats">
        <div class="cs-stat">
            <div class="cs-stat-val">{{ $totalEntries }}</div>
            <div class="cs-stat-lbl">Total Days</div>
        </div>
        <div class="cs-stat">
            <div class="cs-stat-val">{{ number_format($totalPlanned) }}</div>
            <div class="cs-stat-lbl">Planned Meals</div>
        </div>
        <div class="cs-stat">
            <div class="cs-stat-val">{{ number_format($totalActual) }}</div>
            <div class="cs-stat-lbl">Actual Meals</div>
        </div>
        <div class="cs-stat">
            <div class="cs-stat-val {{ $diff > 0 ? '--green' : ($diff < 0 ? '--red' : '') }}">
                {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }}
            </div>
            <div class="cs-stat-lbl">Difference</div>
        </div>
    </div>

    {{-- Per meal-type breakdown --}}
    @if(!empty($typeSummary))
    <div class="cs-card">
        <div class="cs-card-hdr"><i class="bi bi-bar-chart"></i> Meal-Type Summary</div>
        <div class="cs-card-body">
            <div class="cs-type-grid">
                @foreach($mealTypes as $key => $meta)
                    @if(isset($typeSummary[$key]))
                    @php
                        $s = $typeSummary[$key];
                        $d = $s['actual'] - $s['planned'];
                    @endphp
                    <div class="cs-type-card">
                        <div class="cs-type-title">{{ $meta['icon'] }} {{ $meta['label'] }}</div>
                        <div class="cs-type-num">{{ number_format($s['planned']) }}</div>
                        <div class="cs-type-sub">Planned</div>
                        <div class="cs-type-num" style="margin-top:8px">{{ number_format($s['actual']) }}</div>
                        <div class="cs-type-sub">Actual</div>
                        <div class="cs-diff-chip {{ $d > 0 ? 'cs-diff-pos' : ($d < 0 ? 'cs-diff-neg' : 'cs-diff-neu') }}">
                            {{ $d >= 0 ? '+' : '' }}{{ number_format($d) }}
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Client details card --}}
    <div class="cs-card">
        <div class="cs-card-hdr"><i class="bi bi-info-circle"></i> Client Details</div>
        <div class="cs-card-body">
            <div class="cs-info-grid">
                <div class="cs-info-row">
                    <span class="cs-info-lbl">Company</span>
                    <span class="cs-info-val">{{ $client->name }}</span>
                </div>
                <div class="cs-info-row">
                    <span class="cs-info-lbl">Contact Person</span>
                    <span class="cs-info-val">{{ $client->contact_person ?? '—' }}</span>
                </div>
                <div class="cs-info-row">
                    <span class="cs-info-lbl">Mobile</span>
                    <span class="cs-info-val">{{ $client->mobile ?? '—' }}</span>
                </div>
                <div class="cs-info-row">
                    <span class="cs-info-lbl">Email</span>
                    <span class="cs-info-val">{{ $client->email ?? '—' }}</span>
                </div>
                @if($client->gst_number)
                <div class="cs-info-row">
                    <span class="cs-info-lbl">GST Number</span>
                    <span class="cs-info-val" style="font-family:monospace">{{ $client->gst_number }}</span>
                </div>
                @endif
                @if($client->address)
                <div class="cs-info-row" style="grid-column:1/-1">
                    <span class="cs-info-lbl">Address</span>
                    <span class="cs-info-val">{{ $client->address }}</span>
                </div>
                @endif
                @if($client->remarks)
                <div class="cs-info-row" style="grid-column:1/-1">
                    <span class="cs-info-lbl">Remarks</span>
                    <span class="cs-info-val" style="font-weight:400;color:var(--cs-muted)">{{ $client->remarks }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent entries --}}
    <div class="cs-card">
        <div class="cs-card-hdr">
            <i class="bi bi-clock-history"></i> Recent Entries
            <a href="{{ route('meal-register.entries.index') }}?client_id={{ $client->id }}"
               style="margin-left:auto;font-size:.78rem;color:var(--cs-gold);font-weight:600;text-decoration:none">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="cs-card-body" style="padding:0 18px">
            @forelse($recentEntries as $entry)
            <a href="{{ route('meal-register.entries.show', $entry) }}" class="cs-entry">
                <div class="cs-entry-date">
                    <div class="cs-entry-day">{{ $entry->meal_date->format('d') }}</div>
                    <div class="cs-entry-mon">{{ $entry->meal_date->format('M') }}</div>
                </div>
                <div class="cs-entry-title">{{ $entry->meal_date->format('l, d M Y') }}</div>
                <div class="cs-entry-chips">
                    @foreach($entry->items as $item)
                    @php $d = $item->difference(); @endphp
                    <span class="cs-entry-chip {{ $d > 0 ? '--over' : ($d < 0 ? '--under' : '') }}">
                        {{ $item->mealIcon() }} {{ $item->planned_count }}/{{ $item->actual_count ?? '?' }}
                    </span>
                    @endforeach
                </div>
            </a>
            @empty
            <div class="cs-empty"><i class="bi bi-calendar-x" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>No entries yet</div>
            @endforelse
        </div>
    </div>
</div>
</x-admin-layout>
