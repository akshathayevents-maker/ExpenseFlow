<x-admin-layout title="My Overtime">

<x-ds.hero eyebrow="Employee Self-Service" title="My Overtime"
    :meta="[['icon' => 'bi-clock-history', 'text' => 'Track your overtime requests and approvals']]">
    <x-slot:actions>
        <a href="{{ route('employee.overtime.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Request Overtime</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card title="Overtime Requests">
    @include('partials.overtime-card-list', [
        'records' => $records,
        'showEmployee' => false,
        'showRoutePrefix' => 'employee',
        'emptyRoute' => 'employee.overtime.create',
        'emptyLabel' => 'Request Overtime',
    ])
</x-ds.card>

</x-admin-layout>
