<x-admin-layout title="New Leave Type">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.leave-types.index') }}" class="ef-back" title="Back to Leave Types">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">New Leave Type</h1>
            <p class="ef-form-page-sub">Define a leave type employees can apply against</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.leave-types.store') }}">
            @csrf
            @include('admin.leave-types._fields', ['leaveType' => null])

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.leave-types.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-check-lg"></i> Create Leave Type
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
