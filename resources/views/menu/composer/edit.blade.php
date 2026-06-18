<x-admin-layout title="Edit Menu">
@push('styles')
@include('menu.composer._styles')
@endpush

@include('menu.composer._composer', ['draft' => $draft])
</x-admin-layout>
