<x-admin-layout title="Advance #{{ $advance->id }}">
    @include('partials.advance-detail', ['advance' => $advance, 'routePrefix' => 'manager'])
</x-admin-layout>
