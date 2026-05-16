<x-guest-layout>
    <h5 class="fw-bold mb-1">Confirm Password</h5>
    <p class="text-muted small mb-4">This is a secure area. Please re-enter your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label for="password" class="form-label fw-semibold small">Password</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-semibold">
            <i class="bi bi-shield-lock me-1"></i> Confirm
        </button>
    </form>
</x-guest-layout>
