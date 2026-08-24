<x-admin-layout title="Advance #{{ $advance->id }}">
    @include('partials.advance-detail', ['advance' => $advance, 'routePrefix' => 'admin'])
</x-admin-layout>
