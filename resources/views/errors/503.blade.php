<x-errors.layout title="Maintenance">
<div class="error-code">503</div>
<h1 class="error-title">We'll be right back</h1>
<p class="error-sub">
    ExpenseFlow is being updated. This usually takes less than a minute.
    This page refreshes automatically.
</p>
<div class="error-actions">
    <a href="javascript:location.reload()" class="btn btn-primary">Refresh</a>
</div>
<script>setTimeout(function () { location.reload(); }, 15000);</script>
</x-errors.layout>
