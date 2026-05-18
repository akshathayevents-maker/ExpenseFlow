<x-admin-layout title="Wallet Ledger">

@php
$hasFilters = ($filters['employee_id'] ?? '') || ($filters['type'] ?? '') || ($filters['from'] ?? '') || ($filters['to'] ?? '');
$fFrom = $filters['from'] ?? '';
$fTo   = $filters['to']   ?? '';
$fEmp  = $filters['employee_id'] ?? '';
$fType = $filters['type'] ?? '';
@endphp

{{-- ── Mobile filter (d-md-none) ──────────────────────────────── --}}
<div class="d-md-none"
     x-data="{
         f:   @js($fFrom),
         t:   @js($fTo),
         emp: @js($fEmp),
         typ: @js($fType),
         f0:  @js($fFrom),
         t0:  @js($fTo),
         e0:  @js($fEmp),
         y0:  @js($fType),
         ld: false,
         tod()  { return new Date().toISOString().slice(0,10) },
         fom()  { const d=new Date(); d.setDate(1); return d.toISOString().slice(0,10) },
         folm() { const d=new Date(); d.setDate(1); d.setMonth(d.getMonth()-1); return d.toISOString().slice(0,10) },
         lolm() { const d=new Date(); d.setDate(0); return d.toISOString().slice(0,10) },
         get pre()   { if(!this.f&&!this.t) return 'all'; if(this.f===this.fom()&&this.t===this.tod()) return 'mo'; if(this.f===this.folm()&&this.t===this.lolm()) return 'lm'; return 'cu' },
         get dirty() { return this.f!==this.f0 || this.t!==this.t0 || this.emp!==this.e0 || this.typ!==this.y0 },
         sp(p) { if(p==='all'){this.f='';this.t=''} else if(p==='mo'){this.f=this.fom();this.t=this.tod()} else if(p==='lm'){this.f=this.folm();this.t=this.lolm()} },
         go() {
             this.ld=true;
             const p=new URLSearchParams();
             if(this.f)   p.set('from',this.f);
             if(this.t)   p.set('to',this.t);
             if(this.emp) p.set('employee_id',this.emp);
             if(this.typ) p.set('type',this.typ);
             window.location.href='{{ route('admin.reports.ledger') }}'+(p.size?'?'+p:'');
         },
         rst() { this.ld=true; window.location.href='{{ route('admin.reports.ledger') }}' }
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

        <div class="ef-rpf-lbl">Filters</div>
        <div class="ef-rpf-grid-2">
            <div>
                <label class="ef-rpf-field-lbl">Employee</label>
                <select class="ef-rpf-select" x-model="emp">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="ef-rpf-field-lbl">Type</label>
                <select class="ef-rpf-select" x-model="typ">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="reimbursement">Reimbursement</option>
                </select>
            </div>
        </div>

        @if($hasFilters)
        <div class="ef-rpf-fsbar">
            <span class="ef-rpf-fsbar-lbl">Showing:</span>
            @if($fFrom)<span class="ef-rpf-fsbar-chip">From {{ \Carbon\Carbon::parse($fFrom)->format('d M') }}</span>@endif
            @if($fTo)<span class="ef-rpf-fsbar-chip">To {{ \Carbon\Carbon::parse($fTo)->format('d M') }}</span>@endif
            @if($fEmp)
                @php $empName = $employees->firstWhere('id', $fEmp)?->name ?? 'Employee'; @endphp
                <span class="ef-rpf-fsbar-chip"><i class="bi bi-person"></i> {{ $empName }}</span>
            @endif
            @if($fType)<span class="ef-rpf-fsbar-chip">{{ ucfirst($fType) }}</span>@endif
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
<x-ds.hero eyebrow="Reports" title="Wallet Ledger"
    :meta="[['icon' => 'bi-journal-text', 'text' => 'All wallet transactions across employees']]">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div>
            <label class="ef-label">Employee</label>
            <select name="employee_id" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:160px">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ ($filters['employee_id'] ?? '') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="ef-label">Type</label>
            <select name="type" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:140px">
                <option value="">All Types</option>
                <option value="credit" {{ ($filters['type'] ?? '') === 'credit' ? 'selected' : '' }}>Credit</option>
                <option value="debit" {{ ($filters['type'] ?? '') === 'debit' ? 'selected' : '' }}>Debit</option>
                <option value="adjustment" {{ ($filters['type'] ?? '') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                <option value="reimbursement" {{ ($filters['type'] ?? '') === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
            </select>
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">From</label>
            <input type="date" name="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">To</label>
            <input type="date" name="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Filter</button>
            <a href="{{ route('admin.reports.ledger') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>
</div>

{{-- ── Mobile hero (d-md-none) ─────────────────────────────────── --}}
<div class="d-md-none">
    <x-ds.hero eyebrow="Reports" title="Wallet Ledger">
        <x-slot:actions>
            <a href="{{ route('admin.reports.index') }}" class="ef-btn ef-btn-icon"><i class="bi bi-arrow-left"></i></a>
        </x-slot:actions>
    </x-ds.hero>
</div>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-wlt-txn-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Notes / Reference</th>
                    <th class="r">Debit</th>
                    <th class="r">Credit</th>
                    <th class="r">Balance After</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td style="white-space:nowrap">
                        <div style="font-size:.86rem;font-weight:600">{{ $txn->created_at->format('d M Y') }}</div>
                        <div style="color:var(--ef-faint);font-size:.76rem">{{ $txn->created_at->format('h:i A') }}</div>
                    </td>
                    <td style="font-size:.86rem;font-weight:600">{{ $txn->wallet->user->name }}</td>
                    <td><span class="ef-wlt-type-badge --{{ $txn->type }}">{{ $txn->type }}</span></td>
                    <td style="color:var(--ef-faint);font-size:.84rem;max-width:180px">
                        {{ $txn->notes ?? '' }}
                        @if($txn->expenseRequest)
                            <a href="{{ route('admin.expense-requests.show', $txn->expenseRequest) }}"
                               style="display:block;color:var(--ef-emerald);text-decoration:none;font-size:.8rem;margin-top:2px">
                                <i class="bi bi-link-45deg"></i>{{ Str::limit($txn->expenseRequest->title, 25) }}
                            </a>
                        @endif
                    </td>
                    <td class="r">
                        @if($txn->isDebit())
                            <span style="color:var(--ef-danger);font-weight:680">₹{{ number_format($txn->amount, 2) }}</span>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($txn->isCredit())
                            <span style="color:var(--ef-emerald);font-weight:680">₹{{ number_format($txn->amount, 2) }}</span>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                    <td class="r" style="font-weight:680">₹{{ number_format($txn->balance_after, 2) }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $txn->creator->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--ef-faint)">
                        <i class="bi bi-journal-text" style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.3"></i>
                        No transactions found.
                        @if($hasFilters)
                            <a href="{{ route('admin.reports.ledger') }}" style="display:block;margin-top:8px;font-size:.8rem;color:var(--ef-emerald)">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div style="padding:12px 18px;border-top:1px solid var(--ef-border);display:flex;align-items:center;justify-content:space-between">
        <div style="color:var(--ef-faint);font-size:.8rem">{{ $transactions->total() }} transactions</div>
        {{ $transactions->links() }}
    </div>
    @endif
</x-ds.card>

</x-admin-layout>
