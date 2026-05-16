<x-admin-layout title="Settings">
<div class="page-header">
    <h4 class="mb-0 fw-bold">Application Settings</h4>
    <p class="text-muted mb-0 small">Configure system-wide preferences</p>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')

    @foreach($settings as $group => $groupSettings)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent fw-semibold text-capitalize">
            <i class="bi bi-gear me-1 text-secondary"></i> {{ ucfirst($group) }} Settings
        </div>
        <div class="card-body">
            @foreach($groupSettings as $setting)
            <div class="row mb-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-0">{{ $setting->label }}</label>
                    <div class="text-muted" style="font-size:.75rem">{{ $setting->key }}</div>
                </div>
                <div class="col-md-8">
                    @if($setting->type === 'boolean')
                    <div class="form-check form-switch">
                        <input type="checkbox" name="{{ $setting->key }}" id="setting_{{ $setting->key }}"
                               class="form-check-input" value="1"
                               {{ $setting->value === '1' || $setting->value === 'true' ? 'checked' : '' }}>
                        <label class="form-check-label" for="setting_{{ $setting->key }}">
                            {{ $setting->value === '1' || $setting->value === 'true' ? 'Enabled' : 'Disabled' }}
                        </label>
                    </div>
                    @elseif($setting->type === 'integer')
                    <input type="number" name="{{ $setting->key }}"
                           class="form-control @error($setting->key) is-invalid @enderror"
                           value="{{ old($setting->key, $setting->value) }}"
                           min="0">
                    @else
                    <input type="text" name="{{ $setting->key }}"
                           class="form-control @error($setting->key) is-invalid @enderror"
                           value="{{ old($setting->key, $setting->value) }}">
                    @endif
                    @error($setting->key)
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @if(!$loop->last)<hr class="my-2">@endif
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Save Settings
        </button>
    </div>
</form>
</x-admin-layout>
