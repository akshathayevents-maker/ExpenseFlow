<x-admin-layout title="Add Employee">

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   ADD EMPLOYEE — reuses the application's existing design tokens
   (--ef-emerald/gold/danger/border/ink/muted/surface, --ef-radius,
   --ef-shadow, --ef-ease — resources/css/app.css) and the same
   ef-form-page/ef-input/ef-label/ef-switch/ef-btn classes already used
   by admin/employees/edit.blade.php and every other admin form. Only the
   role-selector card and password-strength meter need page-scoped CSS —
   nothing else in this file duplicates an existing token.
   ══════════════════════════════════════════════════════════════════ */

/* ── Password eye toggle ──────────────────────────────────────── */
.ef-emp-pw-wrap { position: relative; }
.ef-emp-pw-wrap .ef-input { padding-right: 2.8rem; }
.ef-emp-pw-eye {
    position: absolute; right: .7rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--ef-muted); cursor: pointer;
    padding: .25rem; display: flex; align-items: center; font-size: .9rem;
    transition: color .15s;
}
.ef-emp-pw-eye:hover { color: var(--ef-ink); }

/* ── Password strength ─────────────────────────────────────────── */
.ef-emp-strength-bars { display: flex; gap: 3px; margin-top: .4rem; }
.ef-emp-strength-bar { flex: 1; height: 3px; border-radius: 4px; background: var(--ef-faint); transition: background .3s var(--ef-ease); }
.ef-emp-strength-bar.--weak   { background: var(--ef-danger); }
.ef-emp-strength-bar.--fair   { background: var(--ef-warning); }
.ef-emp-strength-bar.--good   { background: var(--ef-teal); }
.ef-emp-strength-bar.--strong { background: var(--ef-emerald); }
.ef-emp-strength-label { font-size: .72rem; color: var(--ef-muted); margin-top: .3rem; }

/* ── Role selector — same selected/unselected language as the rest of
   the app (emerald = selected, matching x-ds.kpi-card's hover accent
   and .ef-ds-btn.--primary; role-icon colors match the existing
   employee=info/manager=gold/admin=muted convention used on the
   Wallets role badges). ──────────────────────────────────────────── */
.ef-emp-roles { display: grid; grid-template-columns: repeat(3, 1fr); gap: .7rem; }
@media (max-width: 640px) { .ef-emp-roles { grid-template-columns: 1fr; } }

.ef-emp-role-card { position: relative; cursor: pointer; }
.ef-emp-role-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.ef-emp-role-card input[type="radio"]:focus-visible + .ef-emp-role-face { outline: 2px solid var(--ef-emerald); outline-offset: 2px; }
.ef-emp-role-face {
    border: 1.5px solid var(--ef-border-strong); border-radius: 11px;
    padding: 1rem 1rem .9rem; transition: all .18s var(--ef-ease);
    background: var(--ef-surface); height: 100%;
    display: flex; flex-direction: column; gap: .3rem;
}
.ef-emp-role-card:hover .ef-emp-role-face { border-color: var(--ef-emerald); background: var(--ef-surface-2); }
.ef-emp-role-card input:checked + .ef-emp-role-face {
    border-color: var(--ef-emerald);
    background: rgba(15,123,95,.06);
    box-shadow: 0 0 0 2px rgba(15,123,95,.16);
}
.ef-emp-role-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; margin-bottom: .35rem;
}
.ef-emp-role-icon.--employee { background: rgba(47,111,237,.1);  color: var(--ef-info); }
.ef-emp-role-icon.--manager  { background: rgba(184,137,62,.12); color: var(--ef-gold); }
.ef-emp-role-icon.--admin    { background: rgba(119,115,106,.12);color: var(--ef-ink-2); }
.ef-emp-role-title { font-size: .875rem; font-weight: 700; color: var(--ef-ink); line-height: 1.2; }
.ef-emp-role-desc  { font-size: .7rem; color: var(--ef-muted); line-height: 1.45; }
.ef-emp-role-check {
    width: 16px; height: 16px; border-radius: 50%;
    border: 1.5px solid var(--ef-border-strong); margin-top: auto; align-self: flex-end;
    display: flex; align-items: center; justify-content: center;
    font-size: .55rem; color: transparent; transition: all .18s var(--ef-ease); flex-shrink: 0;
}
.ef-emp-role-card input:checked + .ef-emp-role-face .ef-emp-role-check {
    background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff;
}
</style>
@endpush

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.employees.index') }}" class="ef-back" title="Back to Employees">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Add Employee</h1>
            <p class="ef-form-page-sub">Create workforce access and a hospitality operations account</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.employees.store') }}" novalidate>
        @csrf

        {{-- ── Identity ─────────────────────────────────────────── --}}
        <x-ds.card title="Identity">
            <div class="ef-form-grid ef-form-grid-1">
                <div>
                    <label class="ef-label" for="name">Full Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Priya Sharma"
                           autocomplete="name" autofocus>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="ef-form-grid ef-form-grid-2" style="margin-top:1.1rem">
                <div>
                    <label class="ef-label" for="email">Email Address <span style="color:var(--ef-danger)">*</span></label>
                    <input type="email" id="email" name="email"
                           class="ef-input @error('email') --error @enderror"
                           value="{{ old('email') }}"
                           placeholder="priya@example.com"
                           autocomplete="email">
                    @error('email') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="ef-label" for="phone">Phone Number <span style="color:var(--ef-faint);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
                    <input type="text" id="phone" name="phone"
                           class="ef-input @error('phone') --error @enderror"
                           value="{{ old('phone') }}"
                           placeholder="9876543210"
                           autocomplete="tel" inputmode="tel">
                    @error('phone') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="ef-form-grid ef-form-grid-2" style="margin-top:1.1rem">
                <div>
                    <label class="ef-label" for="employment_start_date">Employment Start Date</label>
                    <input type="date" id="employment_start_date" name="employment_start_date"
                           class="ef-input @error('employment_start_date') --error @enderror"
                           value="{{ old('employment_start_date') }}">
                    @error('employment_start_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="ef-label" for="employment_end_date">Employment End Date</label>
                    <input type="date" id="employment_end_date" name="employment_end_date"
                           class="ef-input @error('employment_end_date') --error @enderror"
                           value="{{ old('employment_end_date') }}">
                    @error('employment_end_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </x-ds.card>

        {{-- ── Leave Policy ─────────────────────────────────────── --}}
        <div class="mt-3">
        <x-ds.card title="Leave Policy">
            <div class="ef-form-grid ef-form-grid-1">
                <div>
                    <label class="ef-label" for="leave_policy_template_id">Leave Policy Template</label>
                    <select id="leave_policy_template_id" name="leave_policy_template_id"
                            class="ef-select @error('leave_policy_template_id') --error @enderror">
                        <option value="">
                            @if(($defaultLeavePolicyTemplate ?? null))
                                Use default ({{ $defaultLeavePolicyTemplate->name }})
                            @else
                                No leave policy
                            @endif
                        </option>
                        @foreach(($leavePolicyTemplates ?? []) as $template)
                            <option value="{{ $template->id }}" {{ old('leave_policy_template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}{{ $template->is_default ? ' (default)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p style="font-size:.78rem;color:var(--ef-muted);margin:.4rem 0 0">
                        Leaves this blank to use the configured default template, if any. The template's leave-type
                        entitlements are assigned effective from the employment start date above.
                    </p>
                    @error('leave_policy_template_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </x-ds.card>
        </div>

        {{-- ── Account Access ───────────────────────────────────── --}}
        <div class="mt-3">
        <x-ds.card title="Account Access">
            <div class="ef-form-grid ef-form-grid-1">
                <div>
                    <label class="ef-label" for="password">Password <span style="color:var(--ef-danger)">*</span></label>
                    <div style="font-size:.78rem;color:var(--ef-muted);margin:0 0 .4rem">Minimum 8 characters — share securely with the staff member</div>
                    <div class="ef-emp-pw-wrap">
                        <input type="password" id="password" name="password"
                               class="ef-input @error('password') --error @enderror"
                               placeholder="Create a strong password"
                               autocomplete="new-password"
                               oninput="efPwStrength(this.value)">
                        <button type="button" class="ef-emp-pw-eye" onclick="efTogglePw()" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="pwEyeIcon"></i>
                        </button>
                    </div>
                    <div class="ef-emp-strength-bars" id="pwStrengthBars">
                        <div class="ef-emp-strength-bar" id="psb1"></div>
                        <div class="ef-emp-strength-bar" id="psb2"></div>
                        <div class="ef-emp-strength-bar" id="psb3"></div>
                        <div class="ef-emp-strength-bar" id="psb4"></div>
                    </div>
                    <div class="ef-emp-strength-label" id="pwStrengthLabel">Enter password to check strength</div>
                    @error('password') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-top:1.2rem">
                <label class="ef-label">Operational Role <span style="color:var(--ef-danger)">*</span></label>
                @error('role') <div class="ef-field-error">{{ $message }}</div> @enderror
                <div class="ef-emp-roles" style="margin-top:.5rem" role="radiogroup" aria-label="Operational role">

                    <label class="ef-emp-role-card">
                        <input type="radio" name="role" value="employee"
                            {{ old('role', 'employee') === 'employee' ? 'checked' : '' }}>
                        <div class="ef-emp-role-face">
                            <div class="ef-emp-role-icon --employee"><i class="bi bi-person"></i></div>
                            <div class="ef-emp-role-title">Employee</div>
                            <div class="ef-emp-role-desc">Expense reporting, booking history, personal dashboard</div>
                            <div class="ef-emp-role-check">✓</div>
                        </div>
                    </label>

                    <label class="ef-emp-role-card">
                        <input type="radio" name="role" value="manager"
                            {{ old('role') === 'manager' ? 'checked' : '' }}>
                        <div class="ef-emp-role-face">
                            <div class="ef-emp-role-icon --manager"><i class="bi bi-person-badge"></i></div>
                            <div class="ef-emp-role-title">Manager</div>
                            <div class="ef-emp-role-desc">Team oversight, approval authority, operational reports</div>
                            <div class="ef-emp-role-check">✓</div>
                        </div>
                    </label>

                    <label class="ef-emp-role-card">
                        <input type="radio" name="role" value="admin"
                            {{ old('role') === 'admin' ? 'checked' : '' }}>
                        <div class="ef-emp-role-face">
                            <div class="ef-emp-role-icon --admin"><i class="bi bi-shield-check"></i></div>
                            <div class="ef-emp-role-title">Admin</div>
                            <div class="ef-emp-role-desc">Full platform access, system config, all operations</div>
                            <div class="ef-emp-role-check">✓</div>
                        </div>
                    </label>

                </div>
            </div>
        </x-ds.card>
        </div>

        {{-- ── Operational Settings ─────────────────────────────── --}}
        <div class="mt-3">
        <x-ds.card title="Operational Settings">
            <label class="ef-switch">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', '1') ? 'checked' : '' }}>
                <span>Activate account immediately</span>
            </label>
            <p style="font-size:.78rem;color:var(--ef-muted);margin:.5rem 0 0 50px">
                When active, the employee can log in and access the platform right away. Deactivate to create the account without granting immediate access.
            </p>
        </x-ds.card>
        </div>

        <div class="mt-3">
            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.employees.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-person-check"></i> Create Employee
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function efTogglePw() {
    const inp  = document.getElementById('password');
    const icon = document.getElementById('pwEyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function efPwStrength(val) {
    const bars  = [1,2,3,4].map(i => document.getElementById('psb' + i));
    const label = document.getElementById('pwStrengthLabel');
    const mods  = ['--weak','--fair','--good','--strong'];
    const lbls  = ['Too weak','Fair — could be stronger','Good strength','Strong password'];
    const colors = ['var(--ef-danger)','var(--ef-warning)','var(--ef-teal)','var(--ef-emerald)'];

    bars.forEach(b => b.className = 'ef-emp-strength-bar');

    if (!val) { label.textContent = 'Enter password to check strength'; label.style.color = ''; return; }

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    score = Math.max(1, score);

    for (let i = 0; i < score; i++) bars[i].classList.add(mods[score - 1]);
    label.textContent = lbls[score - 1];
    label.style.color = colors[score - 1];
}
</script>
@endpush
</x-admin-layout>
