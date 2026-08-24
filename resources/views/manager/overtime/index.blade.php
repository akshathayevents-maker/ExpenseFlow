<x-admin-layout title="Overtime Approvals">

<x-ds.hero eyebrow="Approvals" title="Overtime Requests"
    :meta="[['icon' => 'bi-clock-history', 'text' => 'Review and approve employee overtime claims']]">
</x-ds.hero>

<x-ds.card title="Overtime Requests">
    @include('partials.overtime-card-list', [
        'records' => $records,
        'showEmployee' => true,
        'showRoutePrefix' => 'manager',
    ])
</x-ds.card>

</x-admin-layout>
