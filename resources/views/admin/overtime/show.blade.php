<x-admin-layout title="Overtime #{{ $ot->id }}">
    @include('partials.overtime-detail', ['ot' => $ot, 'routePrefix' => 'admin'])
</x-admin-layout>
