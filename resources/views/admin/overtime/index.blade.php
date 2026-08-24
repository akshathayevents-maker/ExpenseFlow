<x-admin-layout title="Overtime">

<x-ds.hero eyebrow="Approvals" title="Overtime Requests"
    :meta="[['icon' => 'bi-clock-history', 'text' => 'Review, approve, and record employee overtime']]">
    <x-slot:actions>
        <a href="{{ route('admin.overtime.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Record Historical Overtime</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card title="Overtime Requests">
    @include('partials.overtime-card-list', [
        'records' => $records,
        'showEmployee' => true,
        'showOrigin' => true,
        'showRoutePrefix' => 'admin',
        'emptyRoute' => 'admin.overtime.create',
        'emptyLabel' => 'Record Historical Overtime',
    ])
</x-ds.card>

</x-admin-layout>
