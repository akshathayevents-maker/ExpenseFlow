<x-admin-layout title="Corporate Clients">
@push('styles')
<style>
:root{
    --cr-gold:#a0723a;--cr-gold-hi:#b8832a;--cr-gold-dim:rgba(160,114,58,.1);
    --cr-ink:#1c1712;--cr-muted:#7a6e62;--cr-faint:#b0a89a;
    --cr-border:#e8e2d8;--cr-surface:#fff;--cr-page:#f7f5f2;
    --cr-radius:16px;--cr-r-sm:10px;
    --cr-green:#16a34a;--cr-red:#dc2626;
}
.cr-wrap{max-width:1000px;margin:0 auto;padding-bottom:60px}
.cr-page-hdr{display:flex;align-items:center;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.cr-page-title{font-size:1.4rem;font-weight:800;color:var(--cr-ink);flex:1;margin:0}
.cr-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:var(--cr-r-sm);font-size:.88rem;font-weight:700;border:1.5px solid transparent;cursor:pointer;text-decoration:none;transition:all .14s;white-space:nowrap}
.cr-btn--primary{background:var(--cr-gold);color:#fff;box-shadow:0 2px 8px rgba(160,114,58,.25)}
.cr-btn--primary:hover{background:var(--cr-gold-hi);color:#fff;transform:translateY(-1px)}
.cr-btn--sm{padding:6px 13px;font-size:.78rem}
.cr-btn--ghost{background:transparent;border-color:var(--cr-border);color:var(--cr-muted)}
.cr-btn--ghost:hover{border-color:var(--cr-gold);color:var(--cr-gold)}
.cr-btn--danger{background:transparent;border-color:rgba(220,38,38,.3);color:var(--cr-red)}
.cr-btn--danger:hover{background:rgba(220,38,38,.06)}

/* Filter bar */
.cr-filter-bar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.cr-search{flex:1;min-width:200px;position:relative}
.cr-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--cr-faint);font-size:.9rem;pointer-events:none}
.cr-search input{width:100%;padding:10px 14px 10px 36px;border:1.5px solid var(--cr-border);border-radius:var(--cr-r-sm);font-size:.88rem;color:var(--cr-ink);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;min-height:42px}
.cr-search input:focus{border-color:var(--cr-gold);box-shadow:0 0 0 3px rgba(160,114,58,.1)}
.cr-filter-select{padding:10px 14px;border:1.5px solid var(--cr-border);border-radius:var(--cr-r-sm);font-size:.84rem;color:var(--cr-ink);background:#fff;outline:none;cursor:pointer;min-height:42px}
.cr-filter-select:focus{border-color:var(--cr-gold)}
.cr-filter-btn{padding:10px 16px;border:1.5px solid var(--cr-border);border-radius:var(--cr-r-sm);background:#fff;color:var(--cr-muted);font-size:.84rem;cursor:pointer;min-height:42px;font-weight:600;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.cr-filter-btn:hover{border-color:var(--cr-gold);color:var(--cr-gold)}
.cr-filter-btn.active{border-color:var(--cr-gold);background:var(--cr-gold-dim);color:var(--cr-gold)}

/* Card */
.cr-card{background:var(--cr-surface);border:1.5px solid var(--cr-border);border-radius:var(--cr-radius);overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04)}

/* Table */
.cr-table{width:100%;border-collapse:collapse}
.cr-table th{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--cr-faint);padding:12px 18px;background:#faf8f5;border-bottom:1.5px solid var(--cr-border);text-align:left;white-space:nowrap}
.cr-table td{padding:14px 18px;border-bottom:1px solid var(--cr-border);vertical-align:middle;font-size:.88rem;color:var(--cr-ink)}
.cr-table tr:last-child td{border-bottom:none}
.cr-table tr:hover td{background:rgba(160,114,58,.025)}
.cr-co-name{font-weight:700;color:var(--cr-ink);text-decoration:none}
.cr-co-name:hover{color:var(--cr-gold)}
.cr-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:.7rem;font-weight:700}
.cr-badge--active{background:rgba(22,163,74,.1);color:var(--cr-green)}
.cr-badge--inactive{background:rgba(0,0,0,.05);color:var(--cr-faint)}
.cr-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.cr-empty{text-align:center;padding:60px 24px}
.cr-empty i{font-size:2.5rem;color:var(--cr-faint);display:block;margin-bottom:14px}

/* Mobile cards */
@media(max-width:640px){
    .cr-desktop-only{display:none}
    .cr-mobile-cards{display:block}
    .cr-mobile-card{background:var(--cr-surface);border:1.5px solid var(--cr-border);border-radius:12px;padding:16px;margin-bottom:10px}
    .cr-mobile-card-name{font-weight:800;color:var(--cr-ink);font-size:.95rem;margin-bottom:4px;text-decoration:none}
    .cr-mobile-card-meta{font-size:.78rem;color:var(--cr-muted);margin-bottom:10px}
    .cr-mobile-card-foot{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
}
@media(min-width:641px){
    .cr-mobile-cards{display:none}
}
</style>
@endpush

<div class="cr-wrap">
    <div class="cr-page-hdr">
        <h1 class="cr-page-title">
            <i class="bi bi-building me-2" style="color:var(--cr-gold)"></i>Corporate Clients
        </h1>
        @if(in_array(auth()->user()->role, ['admin','manager']))
        <a href="{{ route('meal-register.clients.create') }}" class="cr-btn cr-btn--primary">
            <i class="bi bi-plus-lg"></i> Add Client
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:var(--cr-r-sm)">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('meal-register.clients.index') }}" class="cr-filter-bar">
        <div class="cr-search">
            <i class="bi bi-search cr-search-icon"></i>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search company, contact, mobile…" autocomplete="off">
        </div>
        <select name="status" class="cr-filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="cr-filter-btn">
            <i class="bi bi-funnel"></i> Filter
        </button>
        @if(request()->hasAny(['q','status']))
        <a href="{{ route('meal-register.clients.index') }}" class="cr-filter-btn">
            <i class="bi bi-x"></i> Clear
        </a>
        @endif
    </form>

    {{-- Desktop table --}}
    <div class="cr-card cr-desktop-only">
        @if($clients->isEmpty())
        <div class="cr-empty">
            <i class="bi bi-building-slash"></i>
            <p style="font-size:1rem;font-weight:700;margin-bottom:6px;color:var(--cr-ink)">No clients found</p>
            <p style="color:var(--cr-muted);font-size:.85rem">Add corporate clients like TCS, Infosys to get started.</p>
        </div>
        @else
        <div style="overflow-x:auto">
        <table class="cr-table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact Person</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                <tr>
                    <td>
                        <a href="{{ route('meal-register.clients.show', $client) }}" class="cr-co-name">
                            {{ $client->name }}
                        </a>
                    </td>
                    <td>{{ $client->contact_person ?? '—' }}</td>
                    <td>{{ $client->mobile ?? '—' }}</td>
                    <td>
                        @if($client->active)
                            <span class="cr-badge cr-badge--active"><i class="bi bi-circle-fill" style="font-size:.4rem;vertical-align:middle"></i> Active</span>
                        @else
                            <span class="cr-badge cr-badge--inactive">Inactive</span>
                        @endif
                    </td>
                    <td style="color:var(--cr-muted)">{{ $client->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="cr-actions">
                            <a href="{{ route('meal-register.clients.show', $client) }}" class="cr-btn cr-btn--sm cr-btn--ghost">
                                <i class="bi bi-eye"></i> View
                            </a>
                            @if(in_array(auth()->user()->role, ['admin','manager']))
                            <a href="{{ route('meal-register.clients.edit', $client) }}" class="cr-btn cr-btn--sm cr-btn--ghost">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('meal-register.clients.toggle', $client) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="cr-btn cr-btn--sm {{ $client->active ? 'cr-btn--danger' : 'cr-btn--ghost' }}">
                                    {{ $client->active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

    {{-- Mobile cards --}}
    <div class="cr-mobile-cards">
        @forelse($clients as $client)
        <div class="cr-mobile-card">
            <a href="{{ route('meal-register.clients.show', $client) }}" class="cr-mobile-card-name d-block">
                {{ $client->name }}
            </a>
            <div class="cr-mobile-card-meta">
                {{ $client->contact_person ?? '' }}{{ $client->contact_person && $client->mobile ? ' · ' : '' }}{{ $client->mobile ?? '' }}
            </div>
            <div class="cr-mobile-card-foot">
                @if($client->active)
                    <span class="cr-badge cr-badge--active">Active</span>
                @else
                    <span class="cr-badge cr-badge--inactive">Inactive</span>
                @endif
                <a href="{{ route('meal-register.clients.show', $client) }}" class="cr-btn cr-btn--sm cr-btn--ghost">View</a>
                @if(in_array(auth()->user()->role, ['admin','manager']))
                <a href="{{ route('meal-register.clients.edit', $client) }}" class="cr-btn cr-btn--sm cr-btn--ghost">Edit</a>
                @endif
            </div>
        </div>
        @empty
        <div class="cr-empty">
            <i class="bi bi-building-slash"></i>
            <p style="font-size:1rem;font-weight:700;margin-bottom:6px;color:var(--cr-ink)">No clients found</p>
        </div>
        @endforelse
    </div>

    @if($clients->hasPages())
    <div class="mt-4">{{ $clients->links() }}</div>
    @endif
</div>
</x-admin-layout>
