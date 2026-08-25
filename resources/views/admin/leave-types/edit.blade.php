<x-admin-layout title="Edit {{ $leaveType->name }}">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.leave-types.index') }}" class="ef-back" title="Back to Leave Types">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Edit {{ $leaveType->name }}</h1>
            <p class="ef-form-page-sub">Leave types are never deleted — deactivate instead</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.leave-types.update', $leaveType) }}">
            @csrf
            @method('PUT')
            @include('admin.leave-types._fields', ['leaveType' => $leaveType])

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.leave-types.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
