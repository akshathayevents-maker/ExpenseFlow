<x-guest-layout>
    @if(session('status'))
    <div class="alert alert-success border-0 py-2 small mb-3">
        <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
    </div>
    @endif

    <h5 class="fw-bold mb-1">Sign In</h5>
    <p class="text-muted small mb-4">Enter your credentials to continue</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Email Address</label>
            <input id="email" type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="you@example.com">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">Password</label>
            <div class="input-group">
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required autocomplete="current-password" placeholder="••••••••">
                <button class="btn btn-outline-secondary" type="button" id="togglePwd"
                        onclick="(function(b){const i=document.getElementById('password');const t=i.type==='password';i.type=t?'text':'password';b.querySelector('i').className=t?'bi bi-eye-slash':'bi bi-eye';})(this)">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
                <label for="remember_me" class="form-check-label small">Remember me</label>
            </div>
            @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </button>
    </form>
</x-guest-layout>
