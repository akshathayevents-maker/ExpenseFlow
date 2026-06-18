<x-admin-layout title="New Menu">
@push('styles')
@include('menu.composer._styles')
@endpush

@include('menu.composer._composer', ['draft' => null])
</x-admin-layout>
