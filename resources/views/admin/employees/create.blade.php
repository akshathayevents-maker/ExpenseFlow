<x-admin-layout title="Add Employee">
@push('styles')
<style>
/* ── Design tokens ─────────────────────────────────────── */
:root {
    --ef-bg:            #f7f4f0;
    --ef-ink:           #1a1612;
    --ef-gold:          #a07238;
    --ef-gold-hi:       #b8854a;
    --ef-muted:         #6b6560;
    --ef-faint:         #ede9e3;
    --ef-border:        rgba(160,114,56,.15);
    --ef-border-strong: rgba(160,114,56,.30);
    --ef-shadow:        0 1px 3px rgba(26,22,18,.08),0 4px 12px rgba(26,22,18,.06);
    --ef-shadow-hover:  0 4px 16px rgba(26,22,18,.14),0 1px 4px rgba(26,22,18,.08);
    --ef-radius:        14px;
    --ef-ease:          cubic-bezier(.25,.46,.45,.94);
    --ef-danger:        #c0392b;
    --ef-success:       #16a34a;
}

/* ── Hero ──────────────────────────────────────────────── */
.ef-emp-hero {
    background: linear-gradient(135deg, #1a1612 0%, #2d2420 60%, #3a2e22 100%);
    border-radius: var(--ef-radius);
    padding: 2rem 2.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}
.ef-emp-hero-eyebrow {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ef-gold);
    margin-bottom: .4rem;
}
.ef-emp-hero-title {
    font-size: clamp(1.6rem, 3.5vw, 2.4rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -.02em;
}
.ef-emp-hero-sub {
    color: rgba(255,255,255,.5);
    font-size: .875rem;
    margin-top: .35rem;
}
.ef-emp-hero-date {
    color: rgba(255,255,255,.3);
    font-size: .75rem;
    margin-top: .5rem;
    letter-spacing: .02em;
}
.ef-emp-hero-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
.ef-emp-btn-ghost {
    background: rgba(255,255,255,.09);
    color: rgba(255,255,255,.8);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 8px;
    padding: .48rem .95rem;
    font-size: .82rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s var(--ef-ease);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}
.ef-emp-btn-ghost:hover { background: rgba(255,255,255,.16); color: #fff; }

/* ── Onboarding canvas ─────────────────────────────────── */
.ef-emp-canvas {
    max-width: 780px;
    margin: 0 auto;
    padding-bottom: 6rem;
}

/* ── Progress rail ─────────────────────────────────────── */
.ef-emp-progress-rail {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 1.8rem;
}
.ef-emp-step {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex: 1;
}
.ef-emp-step-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: #fff;
    border: 1.5px solid var(--ef-border-strong);
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem;
    font-weight: 700;
    color: var(--ef-muted);
    flex-shrink: 0;
    transition: all .25s var(--ef-ease);
}
.ef-emp-step.--active .ef-emp-step-num {
    background: var(--ef-gold);
    border-color: var(--ef-gold);
    color: #fff;
}
.ef-emp-step-label {
    font-size: .75rem;
    font-weight: 600;
    color: var(--ef-muted);
    letter-spacing: .02em;
}
.ef-emp-step.--active .ef-emp-step-label { color: var(--ef-gold); }
.ef-emp-step-line {
    flex: 1;
    height: 1px;
    background: var(--ef-border);
    margin: 0 .75rem;
}

/* ── Section card ──────────────────────────────────────── */
.ef-emp-section {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 1.1rem;
    overflow: hidden;
}
.ef-emp-section-head {
    padding: 1.4rem 1.8rem 0;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}
.ef-emp-section-num {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(160,114,56,.1);
    border: 1px solid rgba(160,114,56,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem;
    font-weight: 800;
    color: var(--ef-gold);
    flex-shrink: 0;
    margin-top: .1rem;
    letter-spacing: .04em;
}
.ef-emp-section-meta { flex: 1; }
.ef-emp-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ef-ink);
    line-height: 1.2;
    margin-bottom: .18rem;
}
.ef-emp-section-desc {
    font-size: .78rem;
    color: var(--ef-muted);
    line-height: 1.4;
}
.ef-emp-section-body {
    padding: 1.4rem 1.8rem 1.8rem;
}
.ef-emp-divider {
    height: 1px;
    background: var(--ef-border);
    margin: 1.2rem 1.8rem 0;
}

/* ── Field system ──────────────────────────────────────── */
.ef-emp-field { display: flex; flex-direction: column; gap: .45rem; }
.ef-emp-label {
    font-size: .8rem;
    font-weight: 600;
    color: var(--ef-ink);
    letter-spacing: .01em;
    display: flex;
    align-items: center;
    gap: .3rem;
}
.ef-emp-required {
    color: var(--ef-danger);
    font-size: .75rem;
    font-weight: 700;
}
.ef-emp-hint {
    font-size: .72rem;
    color: var(--ef-muted);
    margin-top: -.2rem;
}
.ef-emp-input {
    width: 100%;
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .7rem .95rem;
    font-size: .9rem;
    color: var(--ef-ink);
    background: var(--ef-faint);
    outline: none;
    transition: border-color .18s var(--ef-ease),
                background .18s var(--ef-ease),
                box-shadow .18s var(--ef-ease);
    -webkit-appearance: none;
}
.ef-emp-input::placeholder { color: #b5afa8; }
.ef-emp-input:focus {
    border-color: var(--ef-gold);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(160,114,56,.12);
}
.ef-emp-input.--invalid {
    border-color: var(--ef-danger);
    background: rgba(192,57,43,.03);
}
.ef-emp-input.--invalid:focus { box-shadow: 0 0 0 3px rgba(192,57,43,.1); }
.ef-emp-error {
    font-size: .74rem;
    color: var(--ef-danger);
    display: flex;
    align-items: center;
    gap: .3rem;
}
.ef-emp-error::before { content: "⚠"; font-size: .7rem; }

/* Password wrapper */
.ef-emp-pw-wrap { position: relative; }
.ef-emp-pw-wrap .ef-emp-input { padding-right: 2.8rem; }
.ef-emp-pw-eye {
    position: absolute;
    right: .7rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--ef-muted);
    cursor: pointer;
    padding: .25rem;
    display: flex;
    align-items: center;
    transition: color .15s;
    font-size: .9rem;
}
.ef-emp-pw-eye:hover { color: var(--ef-gold); }

/* Password strength */
.ef-emp-strength-bars {
    display: flex;
    gap: 3px;
    margin-top: .3rem;
}
.ef-emp-strength-bar {
    flex: 1;
    height: 3px;
    border-radius: 4px;
    background: var(--ef-faint);
    transition: background .3s var(--ef-ease);
}
.ef-emp-strength-bar.--weak   { background: var(--ef-danger); }
.ef-emp-strength-bar.--fair   { background: #d97706; }
.ef-emp-strength-bar.--good   { background: #65a30d; }
.ef-emp-strength-bar.--strong { background: var(--ef-success); }
.ef-emp-strength-label {
    font-size: .7rem;
    color: var(--ef-muted);
    margin-top: .2rem;
}

/* ── Field grid ────────────────────────────────────────── */
.ef-emp-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem;
}
@media (max-width: 600px) { .ef-emp-row { grid-template-columns: 1fr; } }
.ef-emp-stack { display: flex; flex-direction: column; gap: 1.1rem; }

/* ── Role selector ─────────────────────────────────────── */
.ef-emp-roles {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .7rem;
}
@media (max-width: 640px) { .ef-emp-roles { grid-template-columns: 1fr; } }

.ef-emp-role-card {
    position: relative;
    cursor: pointer;
}
.ef-emp-role-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.ef-emp-role-face {
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 11px;
    padding: 1rem 1rem .9rem;
    transition: all .2s var(--ef-ease);
    background: var(--ef-faint);
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.ef-emp-role-card:hover .ef-emp-role-face {
    border-color: var(--ef-gold);
    background: rgba(160,114,56,.04);
}
.ef-emp-role-card input:checked + .ef-emp-role-face {
    border-color: var(--ef-gold);
    background: rgba(160,114,56,.07);
    box-shadow: 0 0 0 2px rgba(160,114,56,.18);
}
.ef-emp-role-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
    margin-bottom: .35rem;
    transition: all .2s;
}
.ef-emp-role-icon.--employee { background: rgba(37,99,235,.1);   color: #1d4ed8; }
.ef-emp-role-icon.--manager  { background: rgba(160,114,56,.12); color: var(--ef-gold); }
.ef-emp-role-icon.--admin    { background: rgba(107,114,128,.12);color: #374151; }
.ef-emp-role-card input:checked + .ef-emp-role-face .ef-emp-role-icon.--employee { background: rgba(37,99,235,.15);   }
.ef-emp-role-card input:checked + .ef-emp-role-face .ef-emp-role-icon.--manager  { background: rgba(160,114,56,.2);  }
.ef-emp-role-card input:checked + .ef-emp-role-face .ef-emp-role-icon.--admin    { background: rgba(107,114,128,.2); }
.ef-emp-role-title {
    font-size: .875rem;
    font-weight: 700;
    color: var(--ef-ink);
    line-height: 1.2;
}
.ef-emp-role-desc {
    font-size: .7rem;
    color: var(--ef-muted);
    line-height: 1.45;
}
.ef-emp-role-check {
    width: 16px; height: 16px;
    border-radius: 50%;
    border: 1.5px solid var(--ef-border-strong);
    margin-top: auto;
    align-self: flex-end;
    display: flex; align-items: center; justify-content: center;
    font-size: .55rem;
    color: transparent;
    transition: all .2s var(--ef-ease);
    flex-shrink: 0;
}
.ef-emp-role-card input:checked + .ef-emp-role-face .ef-emp-role-check {
    background: var(--ef-gold);
    border-color: var(--ef-gold);
    color: #fff;
}

/* ── Status toggle ─────────────────────────────────────── */
.ef-emp-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.1rem 1.3rem;
    border: 1.5px solid var(--ef-border);
    border-radius: 10px;
    background: var(--ef-faint);
    transition: border-color .18s var(--ef-ease), background .18s;
    cursor: pointer;
}
.ef-emp-toggle-row:hover { border-color: var(--ef-border-strong); background: rgba(160,114,56,.04); }
.ef-emp-toggle-info { flex: 1; }
.ef-emp-toggle-title {
    font-size: .875rem;
    font-weight: 700;
    color: var(--ef-ink);
    margin-bottom: .15rem;
}
.ef-emp-toggle-sub { font-size: .74rem; color: var(--ef-muted); line-height: 1.4; }
.ef-emp-toggle-switch { position: relative; flex-shrink: 0; }
.ef-emp-toggle-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.ef-emp-switch-face {
    display: block;
    width: 44px; height: 24px;
    border-radius: 12px;
    background: #d1cbc3;
    transition: background .25s var(--ef-ease);
    position: relative;
    cursor: pointer;
}
.ef-emp-switch-face::after {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #fff;
    top: 3px; left: 3px;
    transition: transform .25s var(--ef-ease);
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.ef-emp-toggle-switch input:checked + .ef-emp-switch-face {
    background: var(--ef-success);
}
.ef-emp-toggle-switch input:checked + .ef-emp-switch-face::after {
    transform: translateX(20px);
}
.ef-emp-status-badge {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--ef-muted);
    transition: color .2s;
}
.ef-emp-toggle-switch input:checked ~ .ef-emp-status-badge { color: var(--ef-success); }

/* ── Action footer (desktop) ────────────────────────────── */
.ef-emp-footer {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 1.15rem 1.8rem;
    box-shadow: var(--ef-shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}
.ef-emp-footer-left { font-size: .8rem; color: var(--ef-muted); }
.ef-emp-footer-left span { color: var(--ef-danger); }
.ef-emp-footer-actions { display: flex; gap: .6rem; }
.ef-emp-btn-cancel {
    background: transparent;
    color: var(--ef-muted);
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .62rem 1.3rem;
    font-size: .875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all .18s var(--ef-ease);
}
.ef-emp-btn-cancel:hover { border-color: var(--ef-danger); color: var(--ef-danger); background: rgba(192,57,43,.04); }
.ef-emp-btn-submit {
    background: var(--ef-gold);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: .65rem 1.8rem;
    font-size: .875rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .2s var(--ef-ease), transform .15s, box-shadow .2s;
    box-shadow: 0 2px 8px rgba(160,114,56,.25);
}
.ef-emp-btn-submit:hover {
    background: var(--ef-gold-hi);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(160,114,56,.35);
}
.ef-emp-btn-submit:active { transform: translateY(0); }

/* ── Mobile sticky footer ───────────────────────────────── */
.ef-emp-sticky {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: rgba(26,22,18,.97);
    backdrop-filter: blur(12px);
    padding: .85rem 1.2rem;
    z-index: 1000;
    gap: .6rem;
}
@media (max-width: 767px) { .ef-emp-sticky { display: flex; } }
.ef-emp-sticky-cancel {
    flex: 0 0 auto;
    background: rgba(255,255,255,.09);
    color: rgba(255,255,255,.75);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 9px;
    padding: .7rem 1.1rem;
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: .35rem;
    transition: background .18s;
}
.ef-emp-sticky-cancel:hover { background: rgba(255,255,255,.15); color: #fff; }
.ef-emp-sticky-submit {
    flex: 1;
    background: var(--ef-gold);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: .7rem 1rem;
    font-size: .875rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    transition: background .18s;
}
.ef-emp-sticky-submit:hover { background: var(--ef-gold-hi); }
</style>
@endpush

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="ef-emp-hero">
    <div>
        <div class="ef-emp-hero-eyebrow">Workforce Onboarding</div>
        <div class="ef-emp-hero-title">Add Employee</div>
        <div class="ef-emp-hero-sub">Create workforce access and hospitality operations account</div>
        <div class="ef-emp-hero-date">{{ now()->format('l, j F Y') }}</div>
    </div>
    <div class="ef-emp-hero-actions">
        <a href="{{ route('admin.employees.index') }}" class="ef-emp-btn-ghost">
            <i class="bi bi-arrow-left"></i> Back to Employees
        </a>
        <a href="{{ route('admin.employees.index') }}" class="ef-emp-btn-ghost">
            <i class="bi bi-people"></i> All Staff
        </a>
    </div>
</div>

{{-- ── Onboarding canvas ───────────────────────────────────────── --}}
<div class="ef-emp-canvas">

    {{-- Progress rail --}}
    <div class="ef-emp-progress-rail">
        <div class="ef-emp-step --active">
            <div class="ef-emp-step-num">01</div>
            <div class="ef-emp-step-label">Identity</div>
        </div>
        <div class="ef-emp-step-line"></div>
        <div class="ef-emp-step --active">
            <div class="ef-emp-step-num">02</div>
            <div class="ef-emp-step-label">Access</div>
        </div>
        <div class="ef-emp-step-line"></div>
        <div class="ef-emp-step --active">
            <div class="ef-emp-step-num">03</div>
            <div class="ef-emp-step-label">Settings</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.employees.store') }}" novalidate id="empForm">
        @csrf

        {{-- ── Section 1: Identity ─────────────────────────────── --}}
        <div class="ef-emp-section">
            <div class="ef-emp-section-head">
                <div class="ef-emp-section-num">01</div>
                <div class="ef-emp-section-meta">
                    <div class="ef-emp-section-title">Identity</div>
                    <div class="ef-emp-section-desc">Staff member's personal and contact information</div>
                </div>
            </div>
            <div class="ef-emp-divider"></div>
            <div class="ef-emp-section-body">
                <div class="ef-emp-stack">
                    {{-- Full Name --}}
                    <div class="ef-emp-field">
                        <label class="ef-emp-label" for="name">
                            Full Name <span class="ef-emp-required">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="ef-emp-input @error('name') --invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Priya Sharma"
                               autocomplete="name"
                               autofocus>
                        @error('name')
                            <div class="ef-emp-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email + Phone --}}
                    <div class="ef-emp-row">
                        <div class="ef-emp-field">
                            <label class="ef-emp-label" for="email">
                                Email Address <span class="ef-emp-required">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   class="ef-emp-input @error('email') --invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="priya@example.com"
                                   autocomplete="email">
                            @error('email')
                                <div class="ef-emp-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="ef-emp-field">
                            <label class="ef-emp-label" for="phone">Phone Number</label>
                            <div class="ef-emp-hint">Used for WhatsApp · shift coordination</div>
                            <input type="text" id="phone" name="phone"
                                   class="ef-emp-input @error('phone') --invalid @enderror"
                                   value="{{ old('phone') }}"
                                   placeholder="9876543210"
                                   autocomplete="tel"
                                   inputmode="tel">
                            @error('phone')
                                <div class="ef-emp-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 2: Account Access ────────────────────────── --}}
        <div class="ef-emp-section">
            <div class="ef-emp-section-head">
                <div class="ef-emp-section-num">02</div>
                <div class="ef-emp-section-meta">
                    <div class="ef-emp-section-title">Account Access</div>
                    <div class="ef-emp-section-desc">Login credentials and operational role assignment</div>
                </div>
            </div>
            <div class="ef-emp-divider"></div>
            <div class="ef-emp-section-body">
                <div class="ef-emp-stack">
                    {{-- Password --}}
                    <div class="ef-emp-field">
                        <label class="ef-emp-label" for="password">
                            Password <span class="ef-emp-required">*</span>
                        </label>
                        <div class="ef-emp-hint">Minimum 8 characters · share securely with staff member</div>
                        <div class="ef-emp-pw-wrap">
                            <input type="password" id="password" name="password"
                                   class="ef-emp-input @error('password') --invalid @enderror"
                                   placeholder="Create a strong password"
                                   autocomplete="new-password"
                                   oninput="efPwStrength(this.value)">
                            <button type="button" class="ef-emp-pw-eye" onclick="efTogglePw()" aria-label="Toggle password">
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
                        @error('password')
                            <div class="ef-emp-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="ef-emp-field">
                        <label class="ef-emp-label">
                            Operational Role <span class="ef-emp-required">*</span>
                        </label>
                        <div class="ef-emp-hint">Determines platform access and operational permissions</div>
                        @error('role')
                            <div class="ef-emp-error">{{ $message }}</div>
                        @enderror
                        <div class="ef-emp-roles">

                            {{-- Employee --}}
                            <label class="ef-emp-role-card">
                                <input type="radio" name="role" value="employee"
                                    {{ old('role', 'employee') === 'employee' ? 'checked' : '' }}>
                                <div class="ef-emp-role-face">
                                    <div class="ef-emp-role-icon --employee">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div class="ef-emp-role-title">Employee</div>
                                    <div class="ef-emp-role-desc">Expense reporting · booking history · personal dashboard</div>
                                    <div class="ef-emp-role-check">✓</div>
                                </div>
                            </label>

                            {{-- Manager --}}
                            <label class="ef-emp-role-card">
                                <input type="radio" name="role" value="manager"
                                    {{ old('role') === 'manager' ? 'checked' : '' }}>
                                <div class="ef-emp-role-face">
                                    <div class="ef-emp-role-icon --manager">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div class="ef-emp-role-title">Manager</div>
                                    <div class="ef-emp-role-desc">Team oversight · approval authority · operational reports</div>
                                    <div class="ef-emp-role-check">✓</div>
                                </div>
                            </label>

                            {{-- Admin --}}
                            <label class="ef-emp-role-card">
                                <input type="radio" name="role" value="admin"
                                    {{ old('role') === 'admin' ? 'checked' : '' }}>
                                <div class="ef-emp-role-face">
                                    <div class="ef-emp-role-icon --admin">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <div class="ef-emp-role-title">Admin</div>
                                    <div class="ef-emp-role-desc">Full platform access · system config · all operations</div>
                                    <div class="ef-emp-role-check">✓</div>
                                </div>
                            </label>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 3: Operational Settings ─────────────────── --}}
        <div class="ef-emp-section">
            <div class="ef-emp-section-head">
                <div class="ef-emp-section-num">03</div>
                <div class="ef-emp-section-meta">
                    <div class="ef-emp-section-title">Operational Settings</div>
                    <div class="ef-emp-section-desc">Account status and access configuration</div>
                </div>
            </div>
            <div class="ef-emp-divider"></div>
            <div class="ef-emp-section-body">
                <label class="ef-emp-toggle-row" for="is_active">
                    <div class="ef-emp-toggle-info">
                        <div class="ef-emp-toggle-title">Activate Account Immediately</div>
                        <div class="ef-emp-toggle-sub">
                            When active, the employee can log in and access the platform right away.
                            Deactivate to create the account without granting immediate access.
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:.35rem;flex-shrink:0">
                        <label class="ef-emp-toggle-switch">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', '1') ? 'checked' : '' }}
                                   onchange="document.getElementById('statusBadge').textContent = this.checked ? 'ACTIVE' : 'INACTIVE'; document.getElementById('statusBadge').style.color = this.checked ? 'var(--ef-success)' : 'var(--ef-muted)'">
                            <span class="ef-emp-switch-face"></span>
                        </label>
                        <span class="ef-emp-status-badge" id="statusBadge"
                              style="color:{{ old('is_active', '1') ? 'var(--ef-success)' : 'var(--ef-muted)' }}">
                            {{ old('is_active', '1') ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </div>
                </label>
            </div>
        </div>

        {{-- ── Desktop action footer ───────────────────────────── --}}
        <div class="ef-emp-footer">
            <div class="ef-emp-footer-left">
                <span>*</span> Required fields must be completed before creating the account
            </div>
            <div class="ef-emp-footer-actions">
                <a href="{{ route('admin.employees.index') }}" class="ef-emp-btn-cancel">
                    Cancel
                </a>
                <button type="submit" class="ef-emp-btn-submit">
                    <i class="bi bi-person-check"></i> Create Employee
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ── Mobile sticky footer ────────────────────────────────────── --}}
<div class="ef-emp-sticky">
    <a href="{{ route('admin.employees.index') }}" class="ef-emp-sticky-cancel">
        <i class="bi bi-x-lg"></i> Cancel
    </a>
    <button type="submit" form="empForm" class="ef-emp-sticky-submit">
        <i class="bi bi-person-check"></i> Create Employee
    </button>
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

    bars.forEach(b => b.className = 'ef-emp-strength-bar');

    if (!val) { label.textContent = 'Enter password to check strength'; return; }

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    score = Math.max(1, score);

    for (let i = 0; i < score; i++) bars[i].classList.add(mods[score - 1]);
    label.textContent = lbls[score - 1];
    label.style.color = ['var(--ef-danger)','#d97706','#65a30d','var(--ef-success)'][score - 1];
}
</script>
@endpush
</x-admin-layout>
