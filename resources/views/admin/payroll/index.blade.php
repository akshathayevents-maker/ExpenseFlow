<x-admin-layout title="Monthly Payroll">

@push('styles')
<style>
html { scroll-behavior: smooth; }

/* ── Sticky section nav ──────────────────────────────────────────────── */
.pr-nav { position: sticky; top: 0; z-index: 5; display: flex; gap: 18px; padding: 8px 2px; margin-bottom: 14px; background: var(--ef-bg); border-bottom: 1px solid var(--ef-border); }
.pr-nav a { font-size: .76rem; font-weight: 700; color: var(--ef-muted); text-decoration: none; letter-spacing: .02em; }
.pr-nav a:hover { color: var(--ef-emerald); }

/* ── Compact page header (replaces full dashboard hero) ─────────────── */
.pr-pagehead { background: var(--ef-hero-grad); border: 1px solid rgba(255,255,255,.06); border-radius: 14px; padding: 18px 24px; margin-bottom: 14px; position: relative; overflow: hidden; }
.pr-pagehead-eyebrow { color: var(--ef-on-dark-gold); font-size: .65rem; font-weight: 760; letter-spacing: .16em; text-transform: uppercase; margin-bottom: 6px; }
.pr-pagehead-title { color: var(--ef-on-dark); font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.15; margin: 0 0 4px; }
.pr-pagehead-desc { color: var(--ef-on-dark-muted); font-size: .82rem; margin: 0; display: flex; align-items: center; gap: 6px; }
@media (max-width: 767.98px) {
    .pr-pagehead { padding: 14px 16px; border-radius: 12px; }
    .pr-pagehead-title { font-size: 1.2rem; }
}

/* ── Compact KPI bar (primary + secondary, page-scoped) ─────────────── */
.pr-kpi-bar { display: flex; border: 1px solid var(--ef-border); border-radius: 12px; background: var(--ef-surface, #fff); overflow: hidden; margin-bottom: 14px; }
.pr-kpi-primary { padding: 12px 20px; border-right: 1px solid var(--ef-border); min-width: 190px; display: flex; flex-direction: column; justify-content: center; }
.pr-kpi-primary-lbl { font-size: .64rem; font-weight: 740; letter-spacing: .07em; text-transform: uppercase; color: var(--ef-faint); }
.pr-kpi-primary-val { font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; margin-top: 2px; }
.pr-kpi-primary-note { font-size: .7rem; color: var(--ef-muted); margin-top: 2px; }
.pr-kpi-secondary { display: flex; flex: 1; }
.pr-kpi-sec-item { flex: 1; padding: 12px 18px; border-right: 1px solid var(--ef-border); display: flex; flex-direction: column; justify-content: center; }
.pr-kpi-sec-item:last-child { border-right: none; }
.pr-kpi-sec-lbl { font-size: .6rem; font-weight: 720; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-faint); }
.pr-kpi-sec-val { font-size: 1.05rem; font-weight: 750; color: var(--ef-ink); margin-top: 1px; }

@media (max-width: 700px) {
    .pr-kpi-bar { flex-direction: column; }
    .pr-kpi-primary { border-right: none; border-bottom: 1px solid var(--ef-border); padding: 12px 16px; min-width: 0; }
    .pr-kpi-secondary { display: grid; grid-template-columns: 1fr 1fr; }
    .pr-kpi-sec-item { border-right: 1px solid var(--ef-border); border-bottom: 1px solid var(--ef-border); padding: 10px 14px; }
    .pr-kpi-sec-item:nth-child(2n) { border-right: none; }
}

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.pr-toolbar { border: 1px solid var(--ef-border); background: var(--ef-surface, #fff); border-radius: 12px; padding: 10px 12px; margin-bottom: 14px; }
.pr-toolbar-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.pr-search-wrap { position: relative; flex: 1; min-width: 220px; }
.pr-search-icon { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--ef-faint); font-size: .85rem; pointer-events: none; }
.pr-search-wrap .ef-input { padding-left: 2.2rem; }
.pr-apply-btn { width: 100%; justify-content: center; }
@media (min-width: 576px) { .pr-apply-btn { width: auto; } }
@media (max-width: 575.98px) { .pr-toolbar-row > * { width: 100%; } }

.pr-list-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; border-bottom: 1px solid var(--ef-border); gap: 10px; }
.pr-list-title { font-size: .66rem; font-weight: 760; letter-spacing: .1em; text-transform: uppercase; color: var(--ef-faint); }
.pr-list-count { font-size: .78rem; color: var(--ef-muted); }

/* ── Monthly payroll: mobile cards ──────────────────────────────────── */
.pr-cards { display: flex; flex-direction: column; }
.pr-card { padding: 14px 16px; border-bottom: 1px solid var(--ef-border); text-decoration: none; color: inherit; display: block; transition: background .14s var(--ef-ease); }
.pr-card:last-child { border-bottom: none; }
.pr-card:hover, .pr-card:focus-visible { background: var(--ef-surface-2); }
.pr-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
.pr-card--compact .pr-card-head { margin-bottom: 4px; }
.pr-card--compact { padding: 10px 16px; }
.pr-card-name { font-weight: 720; font-size: .95rem; color: var(--ef-ink); }
.pr-card-role { font-size: .78rem; color: var(--ef-muted); margin-top: 1px; text-transform: capitalize; }
.pr-net-hero { padding: 8px 0 12px; border-top: 1px dashed var(--ef-border); border-bottom: 1px dashed var(--ef-border); margin-bottom: 10px; }
.pr-net-hero-lbl { font-size: .66rem; font-weight: 760; letter-spacing: .08em; text-transform: uppercase; color: var(--ef-faint); }
.pr-net-hero-val { font-size: 1.7rem; font-weight: 800; letter-spacing: -.02em; color: var(--ef-emerald); margin-top: 2px; }
.pr-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; }
.pr-grid2-lbl { font-size: .66rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: var(--ef-faint); }
.pr-grid2-val { font-size: .86rem; font-weight: 650; color: var(--ef-ink); margin-top: 1px; }
.pr-grid2-caption { font-size: .66rem; color: var(--ef-faint); font-weight: 500; font-style: italic; display: block; }

.pr-unavailable { color: #7D5218; font-size: .82rem; font-weight: 650; display: flex; align-items: center; gap: 6px; }

/* ── Monthly payroll: desktop table ─────────────────────────────────── */
.pr-table-wrap { display: none; }
.pr-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.pr-table th { text-align: left; font-size: .66rem; font-weight: 760; letter-spacing: .07em; text-transform: uppercase; color: var(--ef-faint); padding: 10px 14px; border-bottom: 1px solid var(--ef-border); white-space: nowrap; }
.pr-table td { padding: 10px 14px; border-bottom: 1px solid var(--ef-border); font-size: .84rem; color: var(--ef-ink); vertical-align: middle; overflow: hidden; text-overflow: ellipsis; }
.pr-table tbody tr:last-child td { border-bottom: none; }
.pr-table tbody tr { transition: background .14s var(--ef-ease); }
.pr-table tbody tr.pr-tr-link { cursor: pointer; }
.pr-table tbody tr.pr-tr-link:hover { background: var(--ef-surface-2); }
.pr-table .num { text-align: right; white-space: nowrap; }
.pr-table .pr-td-name { font-weight: 700; }
.pr-table .pr-td-role { display: block; font-weight: 500; font-size: .74rem; color: var(--ef-muted); text-transform: capitalize; }
.pr-table .pr-net-cell { font-weight: 800; font-size: 1rem; color: var(--ef-emerald); }
.pr-table .pr-caption { display: block; font-size: .66rem; color: var(--ef-faint); font-style: italic; font-weight: 500; }
.pr-table tr.pr-tr-unavailable td { padding-top: 8px; padding-bottom: 8px; }

@media (min-width: 860px) {
    .pr-cards { display: none; }
    .pr-table-wrap { display: block; overflow-x: auto; }
}

.pr-section-gap { margin-top: 20px; }
@media (max-width: 767.98px) { .pr-section-gap { margin-top: 14px; } }
.pr-elig-note { font-size: .8rem; color: var(--ef-muted); margin: 0 0 12px; max-width: 70ch; }

/* ── Eligibility: unavailable status chip ───────────────────────────── */
.pr-chip-unavailable {
    display: inline-flex; align-items: center; gap: 5px; padding: 2px 10px; border-radius: 999px;
    background: rgba(216,154,61,.14); color: #7D5218; font-size: .66rem; font-weight: 760;
    letter-spacing: .05em; text-transform: uppercase; white-space: nowrap;
}
.pr-unavailable-reason { font-size: .8rem; color: var(--ef-muted); margin-top: 4px; }
.pr-unavailable-link { font-size: .78rem; font-weight: 650; margin-top: 6px; display: inline-flex; align-items: center; gap: 5px; min-height: 24px; }

.pr-showing-note { font-size: .78rem; color: var(--ef-muted); margin: 0 0 10px; display: flex; align-items: center; gap: 6px; }

.pr-no-match { padding: 24px 18px; text-align: center; color: var(--ef-faint); font-size: .86rem; }
</style>
@endpush

<div class="pr-nav">
    <a href="#monthly-payroll">Monthly Payroll</a>
    <a href="#advance-eligibility">Advance Eligibility</a>
</div>

{{-- ═══════════════════════════════ MONTHLY PAYROLL ═══════════════════════════════ --}}

<div id="monthly-payroll">

<div class="pr-pagehead">
    <div class="pr-pagehead-eyebrow">Compensation / Payroll</div>
    <h1 class="pr-pagehead-title">Monthly Payroll</h1>
    <p class="pr-pagehead-desc"><i class="bi bi-calendar-month"></i> {{ $month->format('F Y') }} &middot; salary, overtime and advance deductions for the period.</p>
</div>

<div class="pr-kpi-bar">
    <div class="pr-kpi-primary">
        <div class="pr-kpi-primary-lbl">Total Net Payable</div>
        <div class="pr-kpi-primary-val c-gold" style="color:var(--ef-gold)">₹{{ number_format($totalNetPayable, 0) }}</div>
        <div class="pr-kpi-primary-note">{{ $month->format('F Y') }}</div>
    </div>
    <div class="pr-kpi-secondary">
        <div class="pr-kpi-sec-item">
            <div class="pr-kpi-sec-lbl">Employees with Salary</div>
            <div class="pr-kpi-sec-val" style="color:var(--ef-emerald)">{{ number_format($employeesWithSalary) }}</div>
        </div>
        <div class="pr-kpi-sec-item">
            <div class="pr-kpi-sec-lbl">Missing Salary Setup</div>
            <div class="pr-kpi-sec-val" style="{{ (count($rows) - $employeesWithSalary) > 0 ? 'color:#7D5218' : '' }}">{{ number_format(count($rows) - $employeesWithSalary) }}</div>
        </div>
    </div>
</div>

<div class="pr-toolbar">
    <form method="GET" action="{{ route('admin.payroll.index') }}" class="pr-toolbar-row">
        <input type="hidden" name="eligibility_date" value="{{ $eligibilityAsOf->format('Y-m-d') }}">
        <div class="pr-search-wrap">
            <i class="bi bi-search pr-search-icon"></i>
            <label for="pr-search" class="visually-hidden">Search employee name or email</label>
            <input type="text" id="pr-search" name="search" class="ef-input" placeholder="Search employee name or email…" value="{{ $search }}">
        </div>
        <label for="pr-month" class="visually-hidden">Month</label>
        <input type="month" id="pr-month" name="month" class="ef-input" style="max-width:180px" value="{{ $month->format('Y-m') }}">
        <button type="submit" class="ef-btn ef-btn-dark pr-apply-btn"><i class="bi bi-check2"></i> Apply</button>
    </form>
</div>

<x-ds.card :no-pad="true">
    <div class="pr-list-head">
        <span class="pr-list-title">Employees</span>
        <span class="pr-list-count">{{ count($rows) }} member{{ count($rows) != 1 ? 's' : '' }}</span>
    </div>

    @if(count($rows) === 0)
        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-people"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">No employees found</h3>
        </div>
    @else
        {{-- Mobile: stacked cards --}}
        <div class="pr-cards">
            @foreach($rows as $row)
                @php $employee = $row['employee']; @endphp
                <a href="{{ route('admin.payroll.show', ['employee' => $employee->id, 'month' => $month->format('Y-m')]) }}" class="pr-card {{ $row['available'] ? '' : 'pr-card--compact' }}">
                    <div class="pr-card-head">
                        <div>
                            <div class="pr-card-name">{{ $employee->name }}</div>
                            <div class="pr-card-role">{{ $employee->role }}</div>
                        </div>
                    </div>

                    @if($row['available'])
                        @php $b = $row['breakdown']; @endphp
                        <div class="pr-net-hero">
                            <div class="pr-net-hero-lbl">Net Payable</div>
                            <div class="pr-net-hero-val">₹{{ number_format($b['net_payable'], 0) }}</div>
                        </div>
                        <div class="pr-grid2">
                            <div>
                                <div class="pr-grid2-lbl">Monthly Salary</div>
                                <div class="pr-grid2-val">₹{{ number_format($b['monthly_salary'], 0) }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Payable Salary</div>
                                <div class="pr-grid2-val">₹{{ number_format($b['payable_salary'], 0) }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Approved OT</div>
                                <div class="pr-grid2-val">₹{{ number_format($b['approved_overtime_amount'], 0) }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Payable / Working Days</div>
                                <div class="pr-grid2-val">{{ $b['payable_days'] }} / {{ $b['applicable_working_days'] }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Advance Ded.</div>
                                <div class="pr-grid2-val">₹{{ number_format($b['advance_deduction_amount'], 0) }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Advance Outstanding</div>
                                <div class="pr-grid2-val">₹{{ number_format($b['advance_outstanding_balance'], 0) }}</div>
                                <span class="pr-grid2-caption">(informational only)</span>
                            </div>
                        </div>
                    @else
                        <div class="pr-unavailable"><i class="bi bi-exclamation-circle"></i> {{ $row['reason'] }}</div>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Desktop/tablet: table --}}
        <div class="pr-table-wrap">
            <table class="pr-table">
                <colgroup>
                    <col style="width:22%">
                    <col style="width:13%">
                    <col style="width:13%">
                    <col style="width:13%">
                    <col style="width:13%">
                    <col style="width:13%">
                    <col style="width:13%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th class="num">Monthly Salary</th>
                        <th class="num">Payable Days</th>
                        <th class="num">Payable Salary</th>
                        <th class="num">Approved OT</th>
                        <th class="num">Advance Outstanding</th>
                        <th class="num">Net Payable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php $employee = $row['employee']; @endphp
                        <tr class="pr-tr-link {{ $row['available'] ? '' : 'pr-tr-unavailable' }}" onclick="window.location='{{ route('admin.payroll.show', ['employee' => $employee->id, 'month' => $month->format('Y-m')]) }}'">
                            <td class="pr-td-name">{{ $employee->name }}<span class="pr-td-role">{{ $employee->role }}</span></td>
                            @if($row['available'])
                                @php $b = $row['breakdown']; @endphp
                                <td class="num">₹{{ number_format($b['monthly_salary'], 0) }}</td>
                                <td class="num">{{ $b['payable_days'] }} / {{ $b['applicable_working_days'] }}</td>
                                <td class="num">₹{{ number_format($b['payable_salary'], 0) }}</td>
                                <td class="num">₹{{ number_format($b['approved_overtime_amount'], 0) }}</td>
                                <td class="num">
                                    ₹{{ number_format($b['advance_outstanding_balance'], 0) }}
                                    <span class="pr-caption">(informational only)</span>
                                </td>
                                <td class="num pr-net-cell">₹{{ number_format($b['net_payable'], 0) }}</td>
                            @else
                                <td colspan="5"><span class="pr-unavailable"><i class="bi bi-exclamation-circle"></i> {{ $row['reason'] }}</span></td>
                                <td class="num">—</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-ds.card>

</div>

{{-- ═══════════════════════════ DAILY ADVANCE ELIGIBILITY ═══════════════════════════ --}}

<div class="pr-section-gap" id="advance-eligibility">

<div class="pr-pagehead">
    <div class="pr-pagehead-eyebrow">Compensation / Payroll</div>
    <h1 class="pr-pagehead-title">Advance Eligibility</h1>
    <p class="pr-pagehead-desc"><i class="bi bi-calendar-check"></i> As of {{ $eligibilityAsOf->format('d M Y') }} &middot; earned salary, previous advances and outstanding balance.</p>
</div>

<div class="pr-kpi-bar">
    <div class="pr-kpi-primary">
        <div class="pr-kpi-primary-lbl">Total Available</div>
        <div class="pr-kpi-primary-val" style="color:var(--ef-gold)">₹{{ number_format($totalEligibleAmount, 0) }}</div>
        <div class="pr-kpi-primary-note">as of {{ $eligibilityAsOf->format('d M Y') }}</div>
    </div>
    <div class="pr-kpi-secondary">
        <div class="pr-kpi-sec-item">
            <div class="pr-kpi-sec-lbl">Eligible</div>
            <div class="pr-kpi-sec-val" style="color:var(--ef-emerald)">{{ number_format($eligibleCount) }}</div>
        </div>
        <div class="pr-kpi-sec-item">
            <div class="pr-kpi-sec-lbl">With Outstanding</div>
            <div class="pr-kpi-sec-val">{{ number_format($withOutstandingCount) }}</div>
        </div>
        <div class="pr-kpi-sec-item">
            <div class="pr-kpi-sec-lbl">Unavailable</div>
            <div class="pr-kpi-sec-val" style="{{ $unavailableCount > 0 ? 'color:#7D5218' : '' }}">{{ number_format($unavailableCount) }}</div>
        </div>
    </div>
</div>

<div class="pr-toolbar">
    <form method="GET" action="{{ route('admin.payroll.index') }}" class="pr-toolbar-row">
        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <div class="pr-search-wrap">
            <i class="bi bi-search pr-search-icon"></i>
            <label for="elig-search" class="visually-hidden">Search employee name or email</label>
            <input type="text" id="elig-search" name="elig_search" class="ef-input" placeholder="Search employee name or email…" value="{{ $eligSearch }}">
        </div>
        <label for="elig-status" class="visually-hidden">Status</label>
        <select id="elig-status" name="elig_status" class="ef-input" style="max-width:170px">
            <option value="" {{ $eligStatus === '' ? 'selected' : '' }}>All statuses</option>
            <option value="eligible" {{ $eligStatus === 'eligible' ? 'selected' : '' }}>Eligible</option>
            <option value="unavailable" {{ $eligStatus === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
        </select>
        <label for="elig-date" style="display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--ef-muted)">
            As of
            <input type="date" id="elig-date" name="eligibility_date" class="ef-input" style="max-width:170px" value="{{ $eligibilityAsOf->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
        </label>
        <button type="submit" class="ef-btn ef-btn-dark pr-apply-btn"><i class="bi bi-check2"></i> <span class="d-none d-sm-inline">Apply</span><span class="d-inline d-sm-none">Apply date</span></button>
    </form>
</div>

<p class="pr-showing-note"><i class="bi bi-info-circle"></i> Showing eligibility as of {{ $eligibilityAsOf->format('d M Y') }}</p>

<x-ds.card :no-pad="true">
    <div class="pr-list-head">
        <span class="pr-list-title">Employees</span>
        <span class="pr-list-count">{{ count($eligibilityRows) }} member{{ count($eligibilityRows) != 1 ? 's' : '' }}</span>
    </div>

    @if(count($eligibilityRows) === 0)
        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-people"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">No employees found</h3>
        </div>
    @else
        <div class="pr-cards">
            @foreach($eligibilityRows as $row)
                @php $employee = $row['employee']; $e = $row['eligibility']; $isElig = $e['salary_configured'] && $e['unavailable_reason'] === null; @endphp
                <div class="pr-card {{ $isElig ? '' : 'pr-card--compact' }}" style="cursor:default">
                    <div class="pr-card-head">
                        <div>
                            <div class="pr-card-name">{{ $employee->name }}</div>
                            <div class="pr-card-role">{{ $employee->role }}</div>
                        </div>
                        @if(!$isElig)
                            <span class="pr-chip-unavailable"><i class="bi bi-x-circle"></i> Unavailable</span>
                        @endif
                    </div>

                    @if($isElig)
                        <div class="pr-net-hero">
                            <div class="pr-net-hero-lbl">Advance Available</div>
                            <div class="pr-net-hero-val">₹{{ number_format($e['eligible_advance_amount'], 0) }}</div>
                        </div>
                        <div class="pr-grid2">
                            <div>
                                <div class="pr-grid2-lbl">Earned Salary</div>
                                <div class="pr-grid2-val">₹{{ number_format($e['earned_salary'], 0) }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Payable Days MTD</div>
                                <div class="pr-grid2-val">{{ $e['payable_days'] }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Previous Advances</div>
                                <div class="pr-grid2-val">₹{{ number_format($e['previous_advances_amount'], 0) }}</div>
                            </div>
                            <div>
                                <div class="pr-grid2-lbl">Outstanding</div>
                                <div class="pr-grid2-val">₹{{ number_format($e['outstanding_amount'], 0) }}</div>
                            </div>
                        </div>
                    @else
                        <div class="pr-unavailable-reason">{{ $e['unavailable_reason'] }}</div>
                        @if(!$e['salary_configured'])
                            <a href="{{ route('admin.employees.salaries.index', $employee) }}" class="pr-unavailable-link" style="color:var(--ef-emerald)">
                                <i class="bi bi-gear"></i> Configure salary
                            </a>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

        <div class="pr-table-wrap">
            <table class="pr-table">
                <colgroup>
                    <col style="width:24%">
                    <col style="width:15%">
                    <col style="width:14%">
                    <col style="width:15%">
                    <col style="width:14%">
                    <col style="width:18%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th class="num">Earned Salary</th>
                        <th class="num">Payable Days MTD</th>
                        <th class="num">Previous Advances</th>
                        <th class="num">Outstanding</th>
                        <th class="num">Advance Available</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eligibilityRows as $row)
                        @php $employee = $row['employee']; $e = $row['eligibility']; $isElig = $e['salary_configured'] && $e['unavailable_reason'] === null; @endphp
                        <tr class="{{ $isElig ? '' : 'pr-tr-unavailable' }}">
                            <td class="pr-td-name">{{ $employee->name }}<span class="pr-td-role">{{ $employee->role }}</span></td>
                            @if($isElig)
                                <td class="num">₹{{ number_format($e['earned_salary'], 0) }}</td>
                                <td class="num">{{ $e['payable_days'] }}</td>
                                <td class="num">₹{{ number_format($e['previous_advances_amount'], 0) }}</td>
                                <td class="num">₹{{ number_format($e['outstanding_amount'], 0) }}</td>
                                <td class="num pr-net-cell">₹{{ number_format($e['eligible_advance_amount'], 0) }}</td>
                            @else
                                <td colspan="4">
                                    <span class="pr-chip-unavailable"><i class="bi bi-x-circle"></i> Unavailable</span>
                                    <span class="pr-unavailable-reason" style="margin-top:4px">{{ $e['unavailable_reason'] }}</span>
                                </td>
                                <td class="num">—</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-ds.card>
</div>

</x-admin-layout>
