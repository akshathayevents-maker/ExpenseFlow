<x-guest-layout>
    <div class="text-center mb-4">
        <i class="bi bi-envelope-check text-primary" style="font-size:2.5rem"></i>
    </div>
    <h5 class="fw-bold mb-1 text-center">Verify Your Email</h5>
    <p class="text-muted small text-center mb-4">
        Check your inbox for a verification link. Didn't get it?
    </p>

    @if(session('status') == 'verification-link-sent')
    <div class="alert alert-success border-0 py-2 small mb-3">
        <i class="bi bi-check-circle me-1"></i> Verification link sent!
    </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100 fw-semibold mb-2">
            <i class="bi bi-send me-1"></i> Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
            Log Out
        </button>
    </form>
</x-guest-layout>
