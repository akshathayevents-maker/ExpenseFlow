<x-admin-layout title="Vendor Report">

@php $hasFilters = $from || $to; @endphp

{{-- ── Mobile filter (d-md-none) ──────────────────────────────── --}}
<div class="d-md-none"
     x-data="{
         f:  @js($from),
         t:  @js($to),
         f0: @js($from),
         t0: @js($to),
         ld: false,
         tod()  { return new Date().toISOString().slice(0,10) },
         fom()  { const d=new Date(); d.setDate(1); return d.toISOString().slice(0,10) },
         folm() { const d=new Date(); d.setDate(1); d.setMonth(d.getMonth()-1); return d.toISOString().slice(0,10) },
         lolm() { const d=new Date(); d.setDate(0); return d.toISOString().slice(0,10) },
         get pre()   { if(!this.f&&!this.t) return 'all'; if(this.f===this.fom()&&this.t===this.tod()) return 'mo'; if(this.f===this.folm()&&this.t===this.lolm()) return 'lm'; return 'cu' },
         get dirty() { return this.f!==this.f0 || this.t!==this.t0 },
         sp(p) { if(p==='all'){this.f='';this.t=''} else if(p==='mo'){this.f=this.fom();this.t=this.tod()} else if(p==='lm'){this.f=this.folm();this.t=this.lolm()} },
         go()  { this.ld=true; const p=new URLSearchParams(); if(this.f) p.set('from',this.f); if(this.t) p.set('to',this.t); window.location.href='{{ route('admin.reports.vendor') }}'+(p.size?'?'+p:'') },
         rst() { this.ld=true; window.location.href='{{ route('admin.reports.vendor') }}' }
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

        @if($hasFilters)
        <div class="ef-rpf-fsbar">
            <span class="ef-rpf-fsbar-lbl">Showing:</span>
            @if($from)<span class="ef-rpf-fsbar-chip"><i class="bi bi-calendar-event"></i> From {{ \Carbon\Carbon::parse($from)->format('d M Y') }}</span>@endif
            @if($to)<span class="ef-rpf-fsbar-chip"><i class="bi bi-calendar-event"></i> To {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>@endif
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

{{-- ── Desktop filter ──────────────────────────────────────────── --}}
<div class="d-none d-md-block">
<x-ds.hero eyebrow="Reports" title="Vendor-wise Expenses">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div class="ef-an-filter-field">
            <label class="ef-label" for="from">From</label>
            <input type="date" name="from" id="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $from }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label" for="to">To</label>
            <input type="date" name="to" id="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $to }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Apply</button>
            <a href="{{ route('admin.reports.vendor') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>
</div>

{{-- ── Mobile hero (d-md-none) ─────────────────────────────────── --}}
<div class="d-md-none">
    <x-ds.hero eyebrow="Reports" title="Vendor-wise Expenses">
        <x-slot:actions>
            <a href="{{ route('admin.reports.index') }}" class="ef-btn ef-btn-icon"><i class="bi bi-arrow-left"></i></a>
        </x-slot:actions>
    </x-ds.hero>
</div>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Vendor</th>
                    <th class="r">Requests</th>
                    <th class="r">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $vendor)
                <tr>
                    <td style="color:var(--ef-faint);font-size:.78rem">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw">{{ $vendor->name }}</div>
                        @if($vendor->phone)
                            <div style="color:var(--ef-faint);font-size:.78rem">{{ $vendor->phone }}</div>
                        @endif
                    </td>
                    <td class="r">{{ $vendor->total_count }}</td>
                    <td class="r fw">₹{{ number_format($vendor->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--ef-faint)">
                    <i class="bi bi-shop" style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.3"></i>
                    No data{{ $hasFilters ? ' for selected period' : '' }}.
                    @if($hasFilters)
                        <a href="{{ route('admin.reports.vendor') }}" style="display:block;margin-top:8px;font-size:.8rem;color:var(--ef-emerald)">Clear filters</a>
                    @endif
                </td></tr>
                @endforelse
            </tbody>
            @if($data->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2" class="fw" style="background:var(--ef-bg-subtle)">Total</td>
                    <td class="r" style="background:var(--ef-bg-subtle)">{{ $data->sum('total_count') }}</td>
                    <td class="r fw" style="background:var(--ef-bg-subtle)">₹{{ number_format($data->sum('total_amount'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
