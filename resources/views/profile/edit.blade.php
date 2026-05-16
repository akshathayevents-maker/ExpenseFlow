<x-admin-layout title="Profile">
<div class="page-header">
    <h4 class="mb-0 fw-bold">My Profile</h4>
    <p class="text-muted mb-0 small">Manage your account information and security</p>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Profile info --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-person me-1 text-primary"></i> Profile Information
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Password --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-lock me-1 text-warning"></i> Update Password
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Delete --}}
        <div class="card border-0 shadow-sm border-danger-subtle">
            <div class="card-header bg-transparent fw-semibold text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i> Danger Zone
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <div class="mx-auto mb-3 rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:72px;height:72px;font-size:1.8rem">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="fw-bold">{{ auth()->user()->name }}</div>
            <div class="text-muted small">{{ auth()->user()->email }}</div>
            <div class="mt-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase"
                      style="font-size:.7rem;letter-spacing:.5px">
                    {{ auth()->user()->role }}
                </span>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
