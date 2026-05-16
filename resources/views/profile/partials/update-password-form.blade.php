<form method="POST" action="{{ route('password.update') }}">
    @csrf @method('put')

    <div class="row g-3">
        <div class="col-md-4">
            <label for="current_password" class="form-label fw-semibold small">Current Password</label>
            <input id="current_password" type="password" name="current_password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="new_password" class="form-label fw-semibold small">New Password</label>
            <input id="new_password" type="password" name="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="password_confirmation" class="form-label fw-semibold small">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-3 d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-shield-lock me-1"></i> Update Password
        </button>
        @if(session('status') === 'password-updated')
        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Password updated.</span>
        @endif
    </div>
</form>
