<x-admin-layout title="My Profile">
@push('styles')
<style>
:root {
    --pf-gold:    #a0723a;
    --pf-gold-hi: #b8832a;
    --pf-gold-dim: rgba(160,114,58,.1);
    --pf-ink:    #1c1712;
    --pf-muted:  #7a6e62;
    --pf-faint:  #b0a89a;
    --pf-border: #e8e2d8;
    --pf-surface:#fff;
    --pf-page:   #f7f5f2;
    --pf-red:    #dc2626;
    --pf-red-dim:rgba(220,38,38,.08);
    --pf-radius: 18px;
    --pf-r-sm:   11px;
}
.pf-wrap { max-width: 860px; margin: 0 auto; padding-bottom: 60px; }

/* ── Hero ── */
.pf-hero {
    background: var(--pf-surface); border: 1.5px solid var(--pf-border);
    border-radius: var(--pf-radius); padding: 28px;
    display: flex; align-items: center; gap: 24px;
    margin-bottom: 20px; box-shadow: 0 2px 12px rgba(0,0,0,.04);
    flex-wrap: wrap;
}
.pf-avatar-wrap {
    flex-shrink: 0; width: 84px; height: 84px; border-radius: 50%;
    padding: 3px;
    background: linear-gradient(135deg, #a0723a, #e0b870, #a0723a);
}
.pf-avatar {
    width: 100%; height: 100%; border-radius: 50%;
    background: linear-gradient(135deg, #2a1f12, #4a3420);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 900; color: #e8c87a;
    letter-spacing: -.02em; user-select: none;
}
.pf-hero-identity { flex: 1; min-width: 200px; }
.pf-hero-name { font-size: 1.25rem; font-weight: 800; color: var(--pf-ink); line-height: 1.2; margin-bottom: 4px; }
.pf-hero-email { font-size: .84rem; color: var(--pf-muted); margin-bottom: 10px; }
.pf-role-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--pf-gold-dim); border: 1px solid rgba(160,114,58,.25);
    color: var(--pf-gold); border-radius: 999px;
    padding: 3px 12px; font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
}
.pf-hero-stats {
    display: flex; flex-shrink: 0;
    background: var(--pf-page); border: 1px solid var(--pf-border);
    border-radius: var(--pf-r-sm); overflow: hidden;
}
.pf-stat {
    padding: 10px 16px; text-align: center;
    border-right: 1px solid var(--pf-border);
}
.pf-stat:last-child { border-right: none; }
.pf-stat-lbl { display: block; font-size: .6rem; font-weight: 700; color: var(--pf-faint); text-transform: uppercase; letter-spacing: .07em; margin-bottom: 3px; }
.pf-stat-val { display: block; font-size: .82rem; font-weight: 700; color: var(--pf-ink); white-space: nowrap; }
.pf-stat-val.--green { color: #16a34a; }
@media (max-width: 640px) {
    .pf-hero { flex-direction: column; text-align: center; align-items: center; }
    .pf-hero-stats { width: 100%; justify-content: center; }
}

/* ── Cards ── */
.pf-card {
    background: var(--pf-surface); border: 1.5px solid var(--pf-border);
    border-radius: var(--pf-radius); margin-bottom: 16px;
    box-shadow: 0 1px 6px rgba(0,0,0,.04); overflow: hidden;
}
.pf-card-hdr {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 22px; border-bottom: 1px solid var(--pf-border);
    background: #faf8f5;
}
.pf-card-hdr-icon {
    width: 34px; height: 34px; border-radius: 9px;
    background: var(--pf-gold-dim); display: flex; align-items: center;
    justify-content: center; color: var(--pf-gold); font-size: .95rem; flex-shrink: 0;
}
.pf-card-hdr-title { font-size: .95rem; font-weight: 700; color: var(--pf-ink); }
.pf-card-hdr-sub   { font-size: .74rem; color: var(--pf-muted); margin-top: 1px; }
.pf-card-body { padding: 22px; }

/* ── Form ── */
.pf-field { margin-bottom: 16px; }
.pf-label { display: block; font-size: .72rem; font-weight: 700; color: var(--pf-muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.pf-input {
    width: 100%; padding: 10px 14px; border: 1.5px solid var(--pf-border);
    border-radius: var(--pf-r-sm); font-size: .9rem; color: var(--pf-ink);
    background: var(--pf-surface); outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.pf-input:focus { border-color: var(--pf-gold); box-shadow: 0 0 0 3px rgba(160,114,58,.12); }
.pf-input.is-invalid { border-color: var(--pf-red); }
.pf-input-err { font-size: .76rem; color: var(--pf-red); margin-top: 4px; }
.pf-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.pf-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
@media (max-width: 600px) { .pf-row-2, .pf-row-3 { grid-template-columns: 1fr; } }

/* Password strength */
.pf-strength-bar { height: 4px; border-radius: 2px; background: #e8e2d8; overflow: hidden; margin-top: 6px; margin-bottom: 3px; }
.pf-strength-fill { height: 100%; border-radius: 2px; width: 0; transition: width .3s, background .3s; }
.pf-strength-lbl  { font-size: .68rem; }

/* Buttons */
.pf-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px; border-radius: var(--pf-r-sm);
    font-size: .87rem; font-weight: 700; border: 1.5px solid transparent;
    cursor: pointer; transition: background .13s, box-shadow .13s, transform .12s; text-decoration: none;
}
.pf-btn--primary { background: var(--pf-gold); color: #fff; border-color: var(--pf-gold); box-shadow: 0 2px 8px rgba(160,114,58,.25); }
.pf-btn--primary:hover { background: var(--pf-gold-hi); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(160,114,58,.35); }
.pf-btn--cancel  { background: var(--pf-page); color: var(--pf-muted); border-color: var(--pf-border); }
.pf-btn--cancel:hover  { background: var(--pf-border); }
.pf-btn--danger  { background: #fff; color: var(--pf-red); border-color: rgba(220,38,38,.4); }
.pf-btn--danger:hover  { background: var(--pf-red-dim); border-color: var(--pf-red); }
.pf-btn-row { display: flex; align-items: center; gap: 14px; margin-top: 20px; flex-wrap: wrap; }
.pf-saved-msg { display: inline-flex; align-items: center; gap: 5px; font-size: .79rem; color: #16a34a; font-weight: 600; }
@media (max-width: 580px) {
    .pf-btn { width: 100%; justify-content: center; }
    .pf-btn-row { flex-direction: column; align-items: stretch; }
}

/* Preferences */
.pf-pref-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid var(--pf-border); }
.pf-pref-row:last-child { border-bottom: none; padding-bottom: 0; }
.pf-pref-lbl  { font-size: .87rem; font-weight: 600; color: var(--pf-ink); }
.pf-pref-sub  { font-size: .73rem; color: var(--pf-faint); margin-top: 1px; }
.pf-pref-chip { display: inline-flex; align-items: center; gap: 5px; background: var(--pf-page); border: 1px solid var(--pf-border); border-radius: 999px; padding: 4px 12px; font-size: .76rem; font-weight: 600; color: var(--pf-muted); }
.pf-toggle { width: 42px; height: 24px; border-radius: 999px; background: #ddd; position: relative; border: none; cursor: not-allowed; opacity: .55; flex-shrink: 0; }
.pf-toggle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.2); }

/* Danger zone */
.pf-danger-card { background: var(--pf-surface); border: 1.5px solid rgba(220,38,38,.3); border-radius: var(--pf-radius); overflow: hidden; box-shadow: 0 1px 6px rgba(220,38,38,.05); }
.pf-danger-hdr  { display: flex; align-items: center; gap: 10px; padding: 14px 22px; background: var(--pf-red-dim); border-bottom: 1px solid rgba(220,38,38,.15); }
.pf-danger-hdr i { color: var(--pf-red); font-size: 1rem; }
.pf-danger-hdr-title { font-size: .9rem; font-weight: 700; color: var(--pf-red); }
.pf-danger-body { padding: 20px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.pf-danger-desc { font-size: .83rem; color: var(--pf-muted); max-width: 500px; line-height: 1.55; }

/* Delete modal */
.pf-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1050; align-items: center; justify-content: center; }
.pf-modal-backdrop.show { display: flex; }
.pf-modal { background: var(--pf-surface); border-radius: var(--pf-radius); padding: 28px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.2); position: relative; }
.pf-modal-icon { width: 52px; height: 52px; border-radius: 50%; background: var(--pf-red-dim); border: 2px solid rgba(220,38,38,.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: var(--pf-red); font-size: 1.3rem; }
.pf-modal-title { font-size: 1.05rem; font-weight: 800; color: var(--pf-ink); text-align: center; margin-bottom: 8px; }
.pf-modal-desc  { font-size: .82rem; color: var(--pf-muted); text-align: center; margin-bottom: 20px; line-height: 1.55; }
.pf-modal-close { position: absolute; top: 16px; right: 18px; background: none; border: none; font-size: 1.1rem; color: var(--pf-faint); cursor: pointer; }
.pf-modal-close:hover { color: var(--pf-ink); }
.pf-modal-btns { display: flex; gap: 10px; }
.pf-modal-btns .pf-btn { flex: 1; justify-content: center; }
</style>
@endpush

@php $user = auth()->user(); @endphp

<div class="pf-wrap">

{{-- ── Hero ── --}}
<div class="pf-hero">
    <div class="pf-avatar-wrap">
        <div class="pf-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
    </div>

    <div class="pf-hero-identity">
        <div class="pf-hero-name">{{ $user->name }}</div>
        <div class="pf-hero-email">{{ $user->email }}</div>
        <span class="pf-role-badge">
            <i class="bi bi-shield-check"></i> {{ ucfirst($user->role ?? 'User') }}
        </span>
    </div>

    <div class="pf-hero-stats">
        <div class="pf-stat">
            <span class="pf-stat-lbl">Status</span>
            <span class="pf-stat-val --green"><i class="bi bi-circle-fill" style="font-size:.45rem;vertical-align:middle;margin-right:3px"></i>Active</span>
        </div>
        <div class="pf-stat">
            <span class="pf-stat-lbl">Member Since</span>
            <span class="pf-stat-val">{{ $user->created_at->format('M Y') }}</span>
        </div>
        <div class="pf-stat">
            <span class="pf-stat-lbl">Account Age</span>
            <span class="pf-stat-val">{{ $user->created_at->diffForHumans(null, true) }}</span>
        </div>
    </div>
</div>

{{-- ── Personal Information ── --}}
<div class="pf-card">
    <div class="pf-card-hdr">
        <div class="pf-card-hdr-icon"><i class="bi bi-person-fill"></i></div>
        <div>
            <div class="pf-card-hdr-title">Personal Information</div>
            <div class="pf-card-hdr-sub">Update your name and email address</div>
        </div>
    </div>
    <div class="pf-card-body">
        <form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('patch')
            <div class="pf-row-2">
                <div class="pf-field">
                    <label class="pf-label" for="name">Full Name</label>
                    <input id="name" type="text" name="name"
                           class="pf-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                    @error('name')<div class="pf-input-err">{{ $message }}</div>@enderror
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="email">Email Address</label>
                    <input id="email" type="email" name="email"
                           class="pf-input @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email')<div class="pf-input-err">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div style="margin-top:10px;padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;font-size:.8rem;color:#92400e;display:flex;align-items:center;gap:8px">
                <i class="bi bi-exclamation-triangle-fill"></i> Email not verified.
                <button form="send-verification" class="pf-btn pf-btn--cancel" style="padding:3px 10px;font-size:.75rem">Resend</button>
                @if(session('status') === 'verification-link-sent')
                    <span style="color:#16a34a;font-weight:600"><i class="bi bi-check-circle me-1"></i>Sent!</span>
                @endif
            </div>
            @endif

            <div class="pf-btn-row">
                <button type="submit" class="pf-btn pf-btn--primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                @if(session('status') === 'profile-updated')
                <span class="pf-saved-msg"><i class="bi bi-check-circle-fill"></i> Saved successfully.</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Security ── --}}
<div class="pf-card">
    <div class="pf-card-hdr">
        <div class="pf-card-hdr-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <div>
            <div class="pf-card-hdr-title">Security</div>
            <div class="pf-card-hdr-sub">Change your password to keep your account safe</div>
        </div>
    </div>
    <div class="pf-card-body">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf @method('put')
            <div class="pf-row-3">
                <div class="pf-field">
                    <label class="pf-label" for="current_password">Current Password</label>
                    <input id="current_password" type="password" name="current_password"
                           class="pf-input @error('current_password','updatePassword') is-invalid @enderror"
                           autocomplete="current-password" placeholder="••••••••">
                    @error('current_password','updatePassword')<div class="pf-input-err">{{ $message }}</div>@enderror
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="new_password">New Password</label>
                    <input id="new_password" type="password" name="password"
                           class="pf-input @error('password','updatePassword') is-invalid @enderror"
                           autocomplete="new-password" placeholder="••••••••"
                           oninput="pfStrength(this.value)">
                    @error('password','updatePassword')<div class="pf-input-err">{{ $message }}</div>@enderror
                    <div class="pf-strength-bar"><div class="pf-strength-fill" id="pfSFill"></div></div>
                    <span class="pf-strength-lbl" id="pfSLbl" style="color:var(--pf-faint)">Enter a new password</span>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="pf-input @error('password_confirmation','updatePassword') is-invalid @enderror"
                           autocomplete="new-password" placeholder="••••••••">
                    @error('password_confirmation','updatePassword')<div class="pf-input-err">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="pf-btn-row">
                <button type="submit" class="pf-btn pf-btn--primary"><i class="bi bi-shield-check"></i> Update Password</button>
                @if(session('status') === 'password-updated')
                <span class="pf-saved-msg"><i class="bi bi-check-circle-fill"></i> Password updated.</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Preferences ── --}}
<div class="pf-card">
    <div class="pf-card-hdr">
        <div class="pf-card-hdr-icon"><i class="bi bi-sliders"></i></div>
        <div>
            <div class="pf-card-hdr-title">Account Preferences</div>
            <div class="pf-card-hdr-sub">Appearance and notification settings</div>
        </div>
    </div>
    <div class="pf-card-body">
        <div class="pf-pref-row">
            <div><div class="pf-pref-lbl">Theme</div><div class="pf-pref-sub">Interface colour mode</div></div>
            <span class="pf-pref-chip"><i class="bi bi-sun"></i> Light</span>
        </div>
        <div class="pf-pref-row">
            <div><div class="pf-pref-lbl">Language</div><div class="pf-pref-sub">Display language</div></div>
            <span class="pf-pref-chip"><i class="bi bi-translate"></i> English</span>
        </div>
        <div class="pf-pref-row">
            <div><div class="pf-pref-lbl">Email Notifications</div><div class="pf-pref-sub">Coming soon</div></div>
            <button class="pf-toggle" disabled title="Coming soon"></button>
        </div>
    </div>
</div>

{{-- ── Danger Zone ── --}}
<div class="pf-danger-card">
    <div class="pf-danger-hdr">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span class="pf-danger-hdr-title">Danger Zone</span>
    </div>
    <div class="pf-danger-body">
        <p class="pf-danger-desc">
            Permanently delete your account and all associated data.
            This action is <strong>immediate and cannot be undone</strong>.
        </p>
        <button type="button" class="pf-btn pf-btn--danger"
                onclick="document.getElementById('pfDelModal').classList.add('show')">
            <i class="bi bi-trash3"></i> Delete Account
        </button>
    </div>
</div>

</div>{{-- /pf-wrap --}}

{{-- ── Delete modal ── --}}
<div class="pf-modal-backdrop" id="pfDelModal"
     onclick="if(event.target===this)this.classList.remove('show')">
    <div class="pf-modal">
        <button class="pf-modal-close" onclick="document.getElementById('pfDelModal').classList.remove('show')">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="pf-modal-icon"><i class="bi bi-trash3-fill"></i></div>
        <div class="pf-modal-title">Delete Your Account?</div>
        <div class="pf-modal-desc">
            This will permanently remove your account and all data. There is no recovery.
            Enter your password to confirm.
        </div>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('delete')
            <div class="pf-field" style="margin-bottom:16px">
                <label class="pf-label" for="del_password">Your Password</label>
                <input id="del_password" type="password" name="password"
                       class="pf-input @error('password','userDeletion') is-invalid @enderror"
                       placeholder="Enter your password" autocomplete="current-password">
                @error('password','userDeletion')<div class="pf-input-err">{{ $message }}</div>@enderror
            </div>
            <div class="pf-modal-btns">
                <button type="button" class="pf-btn pf-btn--cancel"
                        onclick="document.getElementById('pfDelModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="pf-btn pf-btn--danger"><i class="bi bi-trash3"></i> Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

@if($errors->userDeletion->isNotEmpty())
<script>document.addEventListener('DOMContentLoaded',function(){document.getElementById('pfDelModal').classList.add('show');});</script>
@endif

<script>
function pfStrength(v) {
    var f = document.getElementById('pfSFill'), l = document.getElementById('pfSLbl');
    if (!v) { f.style.width='0'; l.textContent='Enter a new password'; l.style.color='var(--pf-faint)'; return; }
    var s=0;
    if(v.length>=8)s++; if(v.length>=12)s++;
    if(/[A-Z]/.test(v)&&/[a-z]/.test(v))s++;
    if(/[0-9]/.test(v))s++;
    if(/[^A-Za-z0-9]/.test(v))s++;
    var m=[
        {w:'20%',bg:'#ef4444',t:'Weak'},
        {w:'40%',bg:'#f97316',t:'Fair'},
        {w:'65%',bg:'#eab308',t:'Good'},
        {w:'85%',bg:'#84cc16',t:'Strong'},
        {w:'100%',bg:'#22c55e',t:'Very Strong'},
    ];
    var lvl=m[Math.min(s,4)];
    f.style.width=lvl.w; f.style.background=lvl.bg;
    l.textContent=lvl.t; l.style.color=lvl.bg;
}
</script>

</x-admin-layout>
