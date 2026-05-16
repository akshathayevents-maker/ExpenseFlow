<form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>

<form method="POST" action="{{ route('profile.update') }}">
    @csrf @method('patch')

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label fw-semibold small">Full Name</label>
            <input id="name" type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label fw-semibold small">Email Address</label>
            <input id="email" type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
        <div class="col-12">
            <div class="alert alert-warning py-2 small border-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Email not verified.
                <button form="send-verification" class="btn btn-link p-0 small">Resend verification.</button>
            </div>
            @if(session('status') === 'verification-link-sent')
            <div class="alert alert-success py-2 small border-0 mt-2">
                <i class="bi bi-check-circle me-1"></i> Verification link sent.
            </div>
            @endif
        </div>
        @endif
    </div>

    <div class="mt-3 d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Save Changes
        </button>
        @if(session('status') === 'profile-updated')
        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Saved.</span>
        @endif
    </div>
</form>
