<x-errors.layout title="Too Many Requests">
<div class="error-code">429</div>
<h1 class="error-title">Too Many Requests</h1>
<p class="error-sub">
    You've made too many requests in a short period.
    Please wait a moment before trying again.
</p>
<div class="error-actions">
    <a href="javascript:location.reload()" class="btn btn-primary">Try Again</a>
    <a href="{{ url('/') }}" class="btn btn-outline">Dashboard</a>
</div>
@if(isset($exception) && method_exists($exception, 'getHeaders') && isset($exception->getHeaders()['Retry-After']))
    <p class="error-ref">Retry after: {{ $exception->getHeaders()['Retry-After'] }} seconds</p>
@endif
</x-errors.layout>
