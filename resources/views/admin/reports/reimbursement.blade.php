<x-admin-layout title="Reimbursement Report">

@php $hasFilters = $status || $from || $to; @endphp

{{-- ── Mobile filter (d-md-none) ──────────────────────────────── --}}
<div class="d-md-none"
     x-data="{
         f:  @js($from),
         t:  @js($to),
         st: @js($status),
         f0: @js($from),
         t0: @js($to),
         s0: @js($status),
         ld: false,
         tod()  { return new Date().toISOString().slice(0,10) },
         fom()  { const d=new Date(); d.setDate(1); return d.toISOString().slice(0,10) },
         folm() { const d=new Date(); d.setDate(1); d.setMonth(d.getMonth()-1); return d.toISOString().slice(0,10) },
         lolm() { const d=new Date(); d.setDate(0); return d.toISOString().slice(0,10) },
         get pre()   { if(!this.f&&!this.t) return 'all'; if(this.f===this.fom()&&this.t===this.tod()) return 'mo'; if(this.f===this.folm()&&this.t===this.lolm()) return 'lm'; return 'cu' },
         get dirty() { return this.f!==this.f0 || this.t!==this.t0 || this.st!==this.s0 },
         sp(p) { if(p==='all'){this.f='';this.t=''} else if(p==='mo'){this.f=this.fom();this.t=this.tod()} else if(p==='lm'){this.f=this.folm();this.t=this.lolm()} },
         go() {
             this.ld=true;
             const p=new URLSearchParams();
             if(this.f)  p.set('from',this.f);
             if(this.t)  p.set('to',this.t);
             if(this.st) p.set('status',this.st);
             window.location.href='{{ route('admin.reports.reimbursement') }}'+(p.size?'?'+p:'');
         },
         rst() { this.ld=true; window.location.href='{{ route('admin.reports.reimbursement') }}' }
     }">

    <div class="ef-rpf-wrap">
        <div class="ef-rpf-lbl">Quick Range</div>
        <div class="ef-rpf-ranges">
            <button type="button" class="ef-rpf-chip" :class="{'--active': pre==='all'}" @click="sp('all')">
                <i class="bi bi-infinity"></i> All Time
            </button>
            <button type="button" class="ef-rpf-chip" :class="{'--active': pre==='mo'}" @click="sp('mo')">
                <i class="bi bi-calendar-check"></i> This Month
            </button>
            <button type="button" class="ef-rpf-chip" :class="{'--active': pre==='lm'}" @click="sp('lm')">
                <i class="bi bi-calendar-minus"></i> Last Month
            </button>
        </div>

        <hr class="ef-rpf-sep">

        <div class="ef-rpf-lbl">Date Range</div>
        <div class="ef-rpf-grid-2">
            <div>
                <label class="ef-rpf-field-lbl">From</label>
                <input type="date" class="ef-rpf-date" x-model="f">
            </div>
            <div>
                <label class="ef-rpf-field-lbl">To</label>
                <input type="date" class="ef-rpf-date" x-model="t">
            </div>
        </div>

        <hr class="ef-rpf-sep">

        <div class="ef-rpf-lbl">Status Filter</div>
        <div class="ef-rpf-grid-1">
            <div>
                <label class="ef-rpf-field-lbl">Reimbursement Status</label>
                <select class="ef-rpf-select" x-model="st">
                    <option value="">All Statuses</option>
                    <option value="reimbursement_pending">Pending</option>
                    <option value="reimbursed">Reimbursed</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>

        @if($hasFilters)
        <div class="ef-rpf-fsbar">
            <span class="ef-rpf-fsbar-lbl">Showing:</span>
            @if($from)<span class="ef-rpf-fsbar-chip">From {{ \Carbon\Carbon::parse($from)->format('d M Y') }}</span>@endif
            @if($to)<span class="ef-rpf-fsbar-chip">To {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>@endif
            @if($status)
                @php $statusLabels = ['reimbursement_pending' => 'Pending', 'reimbursed' => 'Reimbursed', 'completed' => 'Completed']; @endphp
                <span class="ef-rpf-fsbar-chip"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> {{ $statusLabels[$status] ?? $status }}</span>
            @endif
        </div>
        @endif

        <div class="ef-rpf-footer">
            @if($hasFilters)
            <button type="button" class="ef-rpf-reset" @click="rst()" x-show="!ld" x-cloak>
                <i class="bi bi-x-circle"></i> Reset
            </button>
            @endif
            <button type="button" class="ef-rpf-apply" @click="go()" :disabled="!dirty || ld">
                <template x-if="ld"><span><i class="bi bi-hourglass-split ef-rpf-spinner"></i></span></template>
                <template x-if="!ld"><span>Apply Filters</span></template>
            </button>
        </div>
    </div>
</div>

{{-- ── Desktop view ─────────────────────────────────────────────── --}}
<div class="d-none d-md-block">
<x-ds.hero eyebrow="Reports" title="Reimbursement Tracking">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
    <x-ds.kpi-card icon="bi-hourglass-split" label="Pending Reimbursement"
        value="₹{{ number_format($totals['pending'], 2) }}"
        accent="amber" value-color="c-amber" />
    <x-ds.kpi-card icon="bi-check-circle" label="Total Reimbursed"
        value="₹{{ number_format($totals['reimbursed'], 2) }}"
        accent="emerald" value-color="c-emerald" />
</div>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div>
            <label class="ef-label">Status</label>
            <select name="status" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:150px">
                <option value="">All Statuses</option>
                <option value="reimbursement_pending" {{ $status === 'reimbursement_pending' ? 'selected' : '' }}>Pending</option>
                <option value="reimbursed" {{ $status === 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">From</label>
            <input type="date" name="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $from }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">To</label>
            <input type="date" name="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $to }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Filter</button>
            <a href="{{ route('admin.reports.reimbursement') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>
</div>

{{-- ── Mobile hero + KPIs (d-md-none) ─────────────────────────── --}}
<div class="d-md-none">
    <x-ds.hero eyebrow="Reports" title="Reimbursement Tracking">
        <x-slot:actions>
            <a href="{{ route('admin.reports.index') }}" class="ef-btn ef-btn-icon"><i class="bi bi-arrow-left"></i></a>
        </x-slot:actions>
    </x-ds.hero>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
        <x-ds.kpi-card icon="bi-hourglass-split" label="Pending"
            value="₹{{ number_format($totals['pending'], 2) }}"
            accent="amber" value-color="c-amber" />
        <x-ds.kpi-card icon="bi-check-circle" label="Reimbursed"
            value="₹{{ number_format($totals['reimbursed'], 2) }}"
            accent="emerald" value-color="c-emerald" />
    </div>
</div>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="r">Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $req)
                <tr>
                    <td style="color:var(--ef-faint);font-size:.84rem;white-space:nowrap">{{ $req->created_at->format('d M Y') }}</td>
                    <td class="fw">{{ $req->requester->name }}</td>
                    <td style="color:var(--ef-ink-2)">{{ Str::limit($req->title, 40) }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $req->category->name }}</td>
                    <td><x-status-badge :status="$req->status"/></td>
                    <td class="r fw">₹{{ number_format($req->amount, 2) }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.expense-requests.show', $req) }}"
                           class="ef-btn" style="padding:4px 12px;font-size:.8rem">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--ef-faint)">
                    <i class="bi bi-arrow-return-left" style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.3"></i>
                    No reimbursement requests found.
                    @if($hasFilters)
                        <a href="{{ route('admin.reports.reimbursement') }}" style="display:block;margin-top:8px;font-size:.8rem;color:var(--ef-emerald)">Clear filters</a>
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($data->hasPages())
    <div style="padding:14px 18px;border-top:1px solid var(--ef-border)">
        {{ $data->links() }}
    </div>
    @endif
</x-ds.card>

</x-admin-layout>
