<x-errors.layout title="Page Expired">
<div class="error-code">419</div>
<h1 class="error-title">Page Expired</h1>
<p class="error-sub">
    Your session has expired or the security token was invalid.
    This happens when a form is submitted after too long, or when the page is opened in multiple tabs.
</p>
<div class="error-actions">
    <a href="{{ url()->previous('/') }}" class="btn btn-primary">← Go Back &amp; Try Again</a>
    <a href="{{ url('/') }}" class="btn btn-outline">Dashboard</a>
</div>
<p class="error-ref">If this keeps happening, try clearing your browser cookies.</p>
</x-errors.layout>
