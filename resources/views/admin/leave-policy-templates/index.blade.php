<x-admin-layout title="Leave Policy Templates">

@php
    $hasDefault = $templates->firstWhere('is_default', true);
    $activeTemplates = $templates->where('is_active', true)->values();

    // Human-readable accrual-frequency labels — only the three modes the
    // backend actually supports (StoreLeavePolicyTemplateRequest /
    // UpdateLeavePolicyTemplateRequest: yearly|monthly_accrual|quarterly_accrual).
    $accrualLabels = [
        'yearly'            => 'Yearly — full entitlement credited annually',
        'monthly_accrual'   => 'Monthly — credited every month',
        'quarterly_accrual' => 'Quarterly — credited every quarter',
    ];
    $accrualShort = [
        'yearly'            => 'Yearly',
        'monthly_accrual'   => 'Monthly accrual',
        'quarterly_accrual' => 'Quarterly accrual',
    ];

    $fmtDays = fn ($v) => rtrim(rtrim(number_format((float) $v, 1), '0'), '.');
@endphp

<x-ds.hero eyebrow="Leave Management" title="Leave Policy Templates"
    :meta="[['icon' => 'bi-collection', 'text' => 'Reusable leave-entitlement bundles you can assign to employees in bulk']]">
    <x-slot:actions>
        <a href="{{ route('admin.leave-policy-templates.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Create Policy</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════════════
       LEAVE POLICY TEMPLATES — reuses shared tokens/components (x-ds.hero,
       x-ds.card, x-premium.chip, .ef-emp-avatar, .ef-btn/.ef-ds-btn,
       .ef-input/.ef-select/.ef-label, .ef-empty-state/.ef-empty-orb).
       Only page-scoped layout CSS lives here — zero new hex colors.
       ══════════════════════════════════════════════════════════════════ */

    /* ── In-page section nav (Templates / Assignment) ─────────────────── */
    .lpt-jump-nav {
        display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap;
    }
    .lpt-jump-link {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 16px; border-radius: 20px;
        border: 1.5px solid var(--ef-border); background: var(--ef-surface);
        color: var(--ef-muted); font-size: .82rem; font-weight: 650;
        text-decoration: none; transition: all .15s var(--ef-ease);
        scroll-margin-top: 90px;
    }
    .lpt-jump-link:hover { border-color: var(--ef-emerald); color: var(--ef-emerald-dk); }

    /* ── Section headers ────────────────────────────────────────────── */
    .lpt-section { margin-bottom: 28px; scroll-margin-top: 90px; }
    .lpt-section-head { margin-bottom: 6px; }
    .lpt-section-title { font-size: 1.08rem; font-weight: 760; color: var(--ef-ink); display: flex; align-items: center; gap: 8px; }
    .lpt-section-desc { font-size: .82rem; color: var(--ef-muted); margin: 3px 0 14px; max-width: 62ch; }

    /* ── Default-policy explanation banner (shown once, not per card) ─── */
    .lpt-default-note {
        display: flex; align-items: flex-start; gap: 10px;
        background: rgba(15,123,95,.06); border: 1px solid rgba(15,123,95,.18);
        border-radius: var(--ef-radius); padding: 10px 14px; margin-bottom: 14px;
        font-size: .8rem; color: var(--ef-emerald-dk);
    }
    .lpt-default-note i { font-size: .95rem; margin-top: 1px; flex-shrink: 0; }
    .lpt-default-note.--muted {
        background: var(--ef-faint); border-color: var(--ef-border); color: var(--ef-muted);
    }

    /* ── Template card grid ────────────────────────────────────────── */
    .lpt-grid {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }
    @media (max-width: 1200px) { .lpt-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .lpt-grid { grid-template-columns: 1fr; } }

    .lpt-card {
        background: var(--ef-surface); border: 1px solid var(--ef-border);
        border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
        padding: 11px 14px 10px; display: flex; flex-direction: column; min-width: 0;
    }
    .lpt-card.--default { border-color: rgba(184,137,62,.35); background: linear-gradient(180deg, rgba(184,137,62,.045), var(--ef-surface) 60px); }
    .lpt-card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .lpt-card-name { font-size: .94rem; font-weight: 760; color: var(--ef-ink); line-height: 1.25; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
    .lpt-card-badges { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; flex-shrink: 0; }
    .lpt-card-desc { font-size: .78rem; color: var(--ef-muted); margin-top: 3px; }

    .lpt-default-star {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .64rem; font-weight: 760; color: var(--ef-gold);
        letter-spacing: .02em; white-space: nowrap; flex-shrink: 0;
    }

    .lpt-item-list { list-style: none; margin: 6px 0 2px; padding: 0; display: flex; flex-direction: column; }
    .lpt-item-row {
        display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
        padding: 4px 0; border-bottom: 1px solid var(--ef-border); flex-wrap: nowrap;
    }
    .lpt-item-list li.lpt-item-row:last-child { border-bottom: none; padding-bottom: 0; }
    .lpt-item-type { font-size: .82rem; font-weight: 660; color: var(--ef-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
    .lpt-item-detail { display: flex; align-items: baseline; gap: 5px; flex-shrink: 0; white-space: nowrap; }
    .lpt-item-days { font-weight: 700; color: var(--ef-emerald-dk); font-size: .8rem; }
    .lpt-item-freq { font-size: .68rem; color: var(--ef-muted); }

    .lpt-card-actions { display: flex; gap: 6px; margin-top: 8px; }
    .lpt-card-actions .ef-btn, .lpt-card-actions .lpt-quiet-btn {
        flex: 1 1 0; justify-content: center; min-width: 0;
        padding: .38rem .5rem; font-size: .76rem;
    }
    .lpt-quiet-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 5px;
        border: 1.5px dashed rgba(184,137,62,.4); background: rgba(184,137,62,.06);
        color: var(--ef-gold); border-radius: 9px; padding: .38rem .5rem;
        font-size: .76rem; font-weight: 660; cursor: pointer; transition: all .15s var(--ef-ease);
    }
    .lpt-quiet-btn:hover { background: rgba(184,137,62,.12); border-style: solid; }

    /* ── Bulk-assign step flow ─────────────────────────────────────── */
    .lpt-steps { display: flex; flex-direction: column; gap: 18px; }
    .lpt-step { display: flex; gap: 14px; }
    .lpt-step-num {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        background: var(--ef-emerald); color: #fff; font-size: .82rem; font-weight: 760;
        display: flex; align-items: center; justify-content: center;
    }
    .lpt-step-body { flex: 1; min-width: 0; }
    .lpt-step-title { font-size: .88rem; font-weight: 720; color: var(--ef-ink); margin-bottom: 8px; }

    .lpt-emp-toolbar { display: flex; gap: 8px; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
    .lpt-emp-search-wrap { position: relative; flex: 1; min-width: 200px; }
    .lpt-emp-search-icon { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--ef-faint); font-size: .82rem; pointer-events: none; }
    .lpt-emp-search-wrap .ef-input { padding-left: 2.15rem; }
    .lpt-emp-toolbar-btns { display: flex; gap: 6px; flex-shrink: 0; }

    .lpt-emp-panel {
        border: 1px solid var(--ef-border); border-radius: var(--ef-radius);
        background: var(--ef-surface-2); max-height: 320px; overflow-y: auto;
        padding: 6px;
    }
    .lpt-emp-row {
        display: flex; align-items: center; gap: 10px; padding: 8px 8px;
        border-radius: 9px; cursor: pointer; transition: background .12s var(--ef-ease);
    }
    .lpt-emp-row:hover { background: var(--ef-surface); }
    .lpt-emp-row input[type="checkbox"] { flex-shrink: 0; width: 17px; height: 17px; cursor: pointer; }
    .lpt-emp-identity { min-width: 0; flex: 1; }
    .lpt-emp-name-row { display: flex; align-items: center; gap: 6px; min-width: 0; }
    .lpt-emp-name { font-size: .85rem; font-weight: 700; color: var(--ef-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .lpt-emp-email { font-size: .72rem; color: var(--ef-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .lpt-emp-policy { font-size: .68rem; color: var(--ef-faint); margin-top: 1px; }
    .lpt-emp-policy.--none { color: var(--ef-muted); font-style: italic; }

    .lpt-emp-counter {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        margin-top: 10px; padding: 8px 12px;
        background: rgba(15,123,95,.06); border: 1px solid rgba(15,123,95,.16);
        border-radius: 9px; font-size: .82rem; color: var(--ef-emerald-dk); font-weight: 650;
        flex-wrap: wrap; transition: background .15s var(--ef-ease), border-color .15s var(--ef-ease);
    }
    .lpt-emp-counter.--zero { background: var(--ef-faint); border-color: var(--ef-border); color: var(--ef-muted); font-weight: 500; }
    .lpt-emp-counter strong { color: inherit; font-size: .95rem; }

    /* ── Policy-select consequence preview ─────────────────────────────── */
    .lpt-select-preview {
        margin-top: 8px; font-size: .78rem; color: var(--ef-muted);
        display: flex; align-items: center; gap: 6px;
    }
    .lpt-select-preview i { color: var(--ef-emerald); }

    .lpt-helper-caption { font-size: .74rem; color: var(--ef-faint); margin-top: 5px; }

    .lpt-assign-caption { font-size: .78rem; color: var(--ef-muted); margin-bottom: 8px; text-align: right; }

    .lpt-inline-empty { padding: 22px 10px; text-align: center; color: var(--ef-muted); font-size: .82rem; }

    /* ── Confirmation summary before submit ─────────────────────────── */
    .lpt-confirm {
        background: var(--ef-faint); border: 1px solid var(--ef-border);
        border-radius: var(--ef-radius); padding: 12px 16px; margin-top: 18px;
        display: flex; flex-wrap: wrap; gap: 16px 28px;
    }
    .lpt-confirm-item { display: flex; flex-direction: column; gap: 2px; min-width: 110px; }
    .lpt-confirm-label { font-size: .64rem; font-weight: 760; letter-spacing: .07em; text-transform: uppercase; color: var(--ef-faint); }
    .lpt-confirm-value { font-size: .88rem; font-weight: 700; color: var(--ef-ink); }
    .lpt-confirm-value.--muted { color: var(--ef-muted); font-weight: 500; font-style: italic; }

    /* Only fall back to a stacked leave-type row when a single line would
       genuinely clip (very narrow phones) — actions stay side-by-side at
       every width, including 360px. */
    @media (max-width: 359.98px) {
        .lpt-item-row { flex-direction: column; align-items: flex-start; gap: 2px; }
        .lpt-item-detail { padding-left: 0; }
    }
</style>
@endpush

{{-- ── In-page wayfinding ─────────────────────────────────────────── --}}
<div class="lpt-jump-nav">
    <a href="#templates-section" class="lpt-jump-link"><i class="bi bi-collection"></i> Templates</a>
    <a href="#assignment-section" class="lpt-jump-link"><i class="bi bi-people"></i> Assignment</a>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 1 — Policy Templates
════════════════════════════════════════════════════════════════════ --}}
<div class="lpt-section" id="templates-section">
    <div class="lpt-section-head">
        <div class="lpt-section-title"><i class="bi bi-collection text-secondary"></i> Policy Templates</div>
        <div class="lpt-section-desc">Reusable leave-entitlement bundles. Editing a template only affects future assignments — employees already assigned it keep their existing history untouched.</div>
    </div>

    @if($templates->isNotEmpty())
        @if($hasDefault)
            <div class="lpt-default-note">
                <i class="bi bi-info-circle-fill"></i>
                <span>New employees are automatically assigned <strong>"{{ $hasDefault->name }}"</strong> unless another template is explicitly selected when they're created.</span>
            </div>
        @else
            <div class="lpt-default-note --muted">
                <i class="bi bi-info-circle"></i>
                <span>No default template is set — new employees will not be auto-assigned a template unless one is selected explicitly.</span>
            </div>
        @endif
    @endif

    @if($templates->isEmpty())
        <x-ds.card>
            <div class="ef-empty-state">
                <div class="ef-empty-orb"><i class="bi bi-collection"></i></div>
                <div style="font-weight:700;color:var(--ef-ink);margin-bottom:4px">No leave policies yet</div>
                <div style="color:var(--ef-muted);font-size:.85rem;margin-bottom:16px">Create your first template to start assigning leave entitlements in bulk.</div>
                <a href="{{ route('admin.leave-policy-templates.create') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-plus-lg"></i> Create Policy
                </a>
            </div>
        </x-ds.card>
    @else
        <div class="lpt-grid">
            @foreach($templates as $template)
                <div class="lpt-card {{ $template->is_default ? '--default' : '' }}">
                    <div class="lpt-card-top">
                        <div class="lpt-card-name" title="{{ $template->name }}">{{ $template->name }}</div>
                        <div class="lpt-card-badges">
                            @if($template->is_default)
                                <span class="lpt-default-star" title="Default template for new employees">
                                    <i class="bi bi-star-fill"></i> Default
                                </span>
                            @endif
                            <x-premium.chip :tone="$template->is_active ? 'emerald' : 'neutral'">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </x-premium.chip>
                        </div>
                    </div>
                    @if($template->description)
                        <div class="lpt-card-desc">{{ $template->description }}</div>
                    @endif

                    <ul class="lpt-item-list">
                        @forelse($template->items as $item)
                            <li class="lpt-item-row">
                                <span class="lpt-item-type">{{ $item->leaveType->name ?? '—' }}</span>
                                <span class="lpt-item-detail">
                                    <span class="lpt-item-days">{{ $fmtDays($item->annual_entitlement) }} days/yr</span>
                                    <span class="lpt-item-freq">{{ $accrualShort[$item->allocation_mode] ?? str_replace('_', ' ', $item->allocation_mode) }}</span>
                                </span>
                            </li>
                        @empty
                            <li class="lpt-inline-empty">No leave types configured.</li>
                        @endforelse
                    </ul>

                    <div class="lpt-card-actions">
                        <a href="{{ route('admin.leave-policy-templates.edit', $template) }}" class="ef-btn">
                            <i class="bi bi-pencil"></i> Edit Policy
                        </a>
                        @if($template->is_default)
                            <form method="POST" action="{{ route('admin.leave-policy-templates.clear-default', $template) }}" style="flex:1 1 0;min-width:0">
                                @csrf @method('PATCH')
                                <button type="submit" class="lpt-quiet-btn" style="width:100%" title="Remove default status">
                                    <i class="bi bi-star-fill"></i> Clear Default
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.leave-policy-templates.set-default', $template) }}" style="flex:1 1 0;min-width:0">
                                @csrf @method('PATCH')
                                <button type="submit" class="ef-btn" style="width:100%">
                                    <i class="bi bi-star"></i> Set as Default
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 2 — Assign Policy to Employees
════════════════════════════════════════════════════════════════════ --}}
<div class="lpt-section" id="assignment-section">
    <div class="lpt-section-head">
        <div class="lpt-section-title"><i class="bi bi-people text-secondary"></i> Assign Policy to Employees</div>
        <div class="lpt-section-desc">Assigns the selected template's entitlements to every selected employee as new effective-dated policy rows — existing history is never edited.</div>
    </div>

    <x-ds.card :no-pad="false">
        <form method="POST" action="{{ route('admin.leave-policy-templates.bulk-assign') }}" id="lptBulkForm">
            @csrf
            <div class="lpt-steps">

                {{-- Step 1 — Select Policy --}}
                <div class="lpt-step">
                    <div class="lpt-step-num">1</div>
                    <div class="lpt-step-body">
                        <div class="lpt-step-title">Select Policy</div>
                        <select id="bulk_template" name="leave_policy_template_id" class="ef-select @error('leave_policy_template_id') --error @enderror" required>
                            <option value="">Select template</option>
                            @foreach($activeTemplates as $template)
                                <option value="{{ $template->id }}"
                                        data-name="{{ $template->name }}"
                                        data-mode="{{ $accrualShort[$template->items->first()->allocation_mode ?? 'yearly'] ?? 'Yearly' }}"
                                        data-default="{{ $template->is_default ? '1' : '0' }}"
                                        data-count="{{ $template->items->count() }}"
                                        data-days="{{ $fmtDays($template->items->sum('annual_entitlement')) }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                        @error('leave_policy_template_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                        @if($activeTemplates->isEmpty())
                            <div class="lpt-inline-empty" style="padding:10px 0">No active leave policies available. Create or activate one first.</div>
                        @else
                            <div class="lpt-select-preview" id="lptTemplatePreview" style="display:none"></div>
                        @endif
                    </div>
                </div>

                {{-- Step 2 — Effective From --}}
                <div class="lpt-step">
                    <div class="lpt-step-num">2</div>
                    <div class="lpt-step-body">
                        <div class="lpt-step-title">Effective From</div>
                        <input type="date" id="bulk_effective_from" name="effective_from" class="ef-input @error('effective_from') --error @enderror"
                               value="{{ old('effective_from', now()->toDateString()) }}" required style="max-width:220px">
                        @error('effective_from') <div class="ef-field-error">{{ $message }}</div> @enderror
                        <div class="lpt-helper-caption">The selected policy will apply from this date.</div>
                    </div>
                </div>

                {{-- Step 3 — Select Employees --}}
                <div class="lpt-step">
                    <div class="lpt-step-num">3</div>
                    <div class="lpt-step-body">
                        <div class="lpt-step-title">Select Employees</div>
                        @error('employee_ids') <div class="ef-field-error" style="margin-bottom:8px">{{ $message }}</div> @enderror

                        @if($allEmployees->isEmpty())
                            <div class="lpt-inline-empty">No employees found.</div>
                        @else
                            <div class="lpt-emp-toolbar">
                                <div class="lpt-emp-search-wrap">
                                    <i class="bi bi-search lpt-emp-search-icon"></i>
                                    <input type="text" id="lptEmpSearch" class="ef-input" placeholder="Search employee name or email…">
                                </div>
                                <div class="lpt-emp-toolbar-btns">
                                    <button type="button" class="ef-btn" id="lptSelectAll">Select All</button>
                                    <button type="button" class="ef-btn" id="lptClearAll">Clear All</button>
                                </div>
                            </div>

                            <div class="lpt-emp-panel" id="lptEmpPanel">
                                @foreach($allEmployees as $employee)
                                    @php
                                        $nameParts = explode(' ', trim($employee->name));
                                        $initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                        $currentTemplate = $employee->leavePolicyTemplate;
                                    @endphp
                                    <label class="lpt-emp-row" data-lpt-emp-row
                                           data-search="{{ Str::lower($employee->name.' '.$employee->email) }}">
                                        <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" data-lpt-emp-checkbox>
                                        <div class="ef-emp-avatar" style="width:36px;height:36px;border-radius:10px;font-size:.68rem">{{ $initials }}</div>
                                        <div class="lpt-emp-identity">
                                            <div class="lpt-emp-name-row">
                                                <span class="lpt-emp-name">{{ $employee->name }}</span>
                                                <x-premium.chip :tone="$employee->role === 'manager' ? 'bluegray' : 'neutral'" style="font-size:.62rem;padding:1px 7px">{{ strtoupper($employee->role) }}</x-premium.chip>
                                            </div>
                                            <div class="lpt-emp-email">{{ $employee->email }}</div>
                                            @if($currentTemplate)
                                                <div class="lpt-emp-policy">Current: {{ $currentTemplate->name }}</div>
                                            @else
                                                <div class="lpt-emp-policy --none">Current: No policy</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                                <div class="lpt-inline-empty" id="lptEmpNoResults" style="display:none">No employees found.</div>
                            </div>

                            <div class="lpt-emp-counter --zero" id="lptEmpCounter">
                                <span><i class="bi bi-check2-square"></i> <strong id="lptSelectedCount">0</strong> employee(s) selected</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Confirmation summary --}}
            <div class="lpt-confirm">
                <div class="lpt-confirm-item">
                    <span class="lpt-confirm-label">Policy</span>
                    <span class="lpt-confirm-value --muted" id="lptConfirmPolicy">Not selected</span>
                </div>
                <div class="lpt-confirm-item">
                    <span class="lpt-confirm-label">Effective From</span>
                    <span class="lpt-confirm-value" id="lptConfirmDate">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="lpt-confirm-item">
                    <span class="lpt-confirm-label">Employees</span>
                    <span class="lpt-confirm-value --muted" id="lptConfirmCount">No employees selected</span>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="lpt-assign-caption" id="lptAssignCaption">Select a policy, date, and at least one employee to continue.</div>
            <div class="ef-form-actions">
                <button type="submit" class="ef-ds-btn --primary" style="color:#fff" id="lptSubmitBtn" disabled>
                    <i class="bi bi-check-lg"></i> <span>Assign Policy</span>
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

@push('scripts')
<script>
(function () {
    var panel = document.getElementById('lptEmpPanel');
    if (!panel) return; // no employees at all

    var rows = Array.prototype.slice.call(panel.querySelectorAll('[data-lpt-emp-row]'));
    var checkboxes = Array.prototype.slice.call(panel.querySelectorAll('[data-lpt-emp-checkbox]'));
    var noResults = document.getElementById('lptEmpNoResults');
    var searchInput = document.getElementById('lptEmpSearch');
    var countEl = document.getElementById('lptSelectedCount');
    var confirmCount = document.getElementById('lptConfirmCount');
    var confirmPolicy = document.getElementById('lptConfirmPolicy');
    var confirmDate = document.getElementById('lptConfirmDate');
    var templateSelect = document.getElementById('bulk_template');
    var dateInput = document.getElementById('bulk_effective_from');
    var templatePreview = document.getElementById('lptTemplatePreview');
    var empCounterBox = document.getElementById('lptEmpCounter');
    var assignCaption = document.getElementById('lptAssignCaption');
    var submitBtn = document.getElementById('lptSubmitBtn');

    function formattedDate() {
        return dateInput && dateInput.value
            ? new Date(dateInput.value + 'T00:00:00').toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' })
            : null;
    }

    function refreshCaptionAndButton() {
        var n = checkboxes.filter(function (c) { return c.checked; }).length;
        var opt = templateSelect ? templateSelect.options[templateSelect.selectedIndex] : null;
        var mode = opt ? opt.getAttribute('data-mode') : null;
        var date = formattedDate();

        var ready = !!(opt && opt.value) && !!(dateInput && dateInput.value) && n > 0;
        if (submitBtn) submitBtn.disabled = !ready;

        if (assignCaption) {
            if (ready) {
                assignCaption.textContent = mode + ' · Effective ' + date + ' · ' + n + ' employee' + (n === 1 ? '' : 's');
            } else {
                assignCaption.textContent = 'Select a policy, date, and at least one employee to continue.';
            }
        }
    }

    function updateCount() {
        var n = checkboxes.filter(function (c) { return c.checked; }).length;
        countEl.textContent = n;
        confirmCount.textContent = n > 0 ? (n + ' employee' + (n === 1 ? '' : 's') + ' selected') : 'No employees selected';
        confirmCount.classList.toggle('--muted', n === 0);
        if (empCounterBox) empCounterBox.classList.toggle('--zero', n === 0);
        refreshCaptionAndButton();
    }

    function updatePolicy() {
        var opt = templateSelect.options[templateSelect.selectedIndex];
        var name = opt ? (opt.getAttribute('data-name') || '') : '';
        confirmPolicy.textContent = name || 'Not selected';
        confirmPolicy.classList.toggle('--muted', !name);

        if (templatePreview) {
            if (opt && opt.value) {
                var mode = opt.getAttribute('data-mode') || 'Yearly';
                var isDefault = opt.getAttribute('data-default') === '1';
                var count = opt.getAttribute('data-count') || '0';
                var days = opt.getAttribute('data-days') || '0';
                templatePreview.innerHTML = '<i class="bi bi-info-circle"></i> ' + mode +
                    (isDefault ? ' · Default policy' : '') +
                    ' · ' + count + ' leave type' + (count === '1' ? '' : 's') +
                    ' · ' + days + ' days/year';
                templatePreview.style.display = '';
            } else {
                templatePreview.style.display = 'none';
            }
        }
        refreshCaptionAndButton();
    }

    function updateDate() {
        var date = formattedDate();
        confirmDate.textContent = date || '—';
        refreshCaptionAndButton();
    }

    checkboxes.forEach(function (c) { c.addEventListener('change', updateCount); });
    if (templateSelect) templateSelect.addEventListener('change', updatePolicy);
    if (dateInput) dateInput.addEventListener('change', updateDate);

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;
            rows.forEach(function (row) {
                var match = !q || row.getAttribute('data-search').indexOf(q) !== -1;
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            noResults.style.display = visibleCount === 0 ? '' : 'none';
        });
    }

    var selectAllBtn = document.getElementById('lptSelectAll');
    var clearAllBtn = document.getElementById('lptClearAll');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            rows.forEach(function (row) {
                if (row.style.display !== 'none') {
                    var cb = row.querySelector('[data-lpt-emp-checkbox]');
                    if (cb) cb.checked = true;
                }
            });
            updateCount();
        });
    }
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            checkboxes.forEach(function (c) { c.checked = false; });
            updateCount();
        });
    }

    updateCount();
    updatePolicy();
})();
</script>
@endpush

</x-admin-layout>
