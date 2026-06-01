<x-errors.layout title="Server Error">
<div class="error-code">500</div>
<h1 class="error-title">Something went wrong</h1>
<p class="error-sub">
    An unexpected error occurred on the server.
    Our team has been notified. Please try again in a few moments.
</p>
<div class="error-actions">
    <a href="javascript:location.reload()" class="btn btn-primary">Try Again</a>
    <a href="{{ url('/') }}" class="btn btn-outline">Go to Dashboard</a>
</div>
@if(config('app.debug') && isset($exception))
    <hr class="divider">
    <p style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#7f1d1d;font-family:monospace;font-size:.75rem;padding:12px;text-align:left;word-break:break-all">
        <strong>Debug (local only)</strong><br>
        {{ $exception->getMessage() }}
    </p>
@endif
</x-errors.layout>
