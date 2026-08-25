<x-admin-layout title="Leave Request — {{ $leaveRequest->user->name ?? '' }}">
    @include('partials.leave-request-detail', ['leaveRequest' => $leaveRequest, 'routePrefix' => $routePrefix ?? 'admin'])
</x-admin-layout>
