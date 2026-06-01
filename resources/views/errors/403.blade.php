<x-errors.layout title="Access Denied">
<div class="error-code">403</div>
<h1 class="error-title">Access Denied</h1>
<p class="error-sub">
    {{ (isset($exception) && $exception->getMessage()) ? $exception->getMessage() : 'You don\'t have permission to access this page.' }}
</p>
<div class="error-actions">
    <a href="{{ url('/') }}" class="btn btn-primary">← Go to Dashboard</a>
    <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
</div>
</x-errors.layout>
