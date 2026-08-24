<x-admin-layout title="Overtime #{{ $ot->id }}">
    @include('partials.overtime-detail', ['ot' => $ot, 'routePrefix' => 'manager'])
</x-admin-layout>
