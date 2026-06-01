<x-errors.layout title="Page Not Found">
<div class="error-code">404</div>
<h1 class="error-title">Page Not Found</h1>
<p class="error-sub">
    The page you're looking for doesn't exist or has been moved.
</p>
<div class="error-actions">
    <a href="{{ url('/') }}" class="btn btn-primary">← Go to Dashboard</a>
    <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
</div>
</x-errors.layout>
