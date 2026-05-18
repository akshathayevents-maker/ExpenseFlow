<x-admin-layout title="Request #{{ $expenseRequest->id }}">
    @include('partials.expense-request-detail', ['routePrefix' => 'manager'])
</x-admin-layout>
