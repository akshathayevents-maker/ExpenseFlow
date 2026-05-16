<x-guest-layout>
    @if(session('status'))
    <div class="alert alert-success border-0 py-2 small mb-3">
        <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
    </div>
    @endif

    <h5 class="fw-bold mb-1">Reset Password</h5>
    <p class="text-muted small mb-4">Enter your email and we'll send you a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Email Address</label>
            <input id="email" type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required autofocus placeholder="you@example.com">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-semibold">
            <i class="bi bi-envelope me-1"></i> Send Reset Link
        </button>
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to login
            </a>
        </div>
    </form>
</x-guest-layout>
