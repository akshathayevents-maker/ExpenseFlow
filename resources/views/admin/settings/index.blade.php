<x-admin-layout title="Settings">

<x-ds.hero eyebrow="System" title="Application Settings"
    :meta="[['icon' => 'bi-gear', 'text' => 'Configure system-wide preferences']]">
</x-ds.hero>

@if(session('success'))
<div style="background:rgba(15,123,95,.08);border:1px solid rgba(15,123,95,.22);border-radius:var(--ef-radius);color:var(--ef-emerald);display:flex;align-items:center;gap:10px;padding:12px 16px;margin-bottom:16px;font-size:.88rem;font-weight:500">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')

    @foreach($settings as $group => $groupSettings)
    <x-ds.card :title="ucfirst($group) . ' Settings'" style="margin-bottom:14px">

        @foreach($groupSettings as $setting)
        <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:12px 24px;align-items:center;{{ !$loop->last ? 'padding-bottom:14px;border-bottom:1px solid var(--ef-border);margin-bottom:14px' : '' }}">
            <div>
                <div style="color:var(--ef-ink-2);font-size:.9rem;font-weight:600;margin-bottom:2px">{{ $setting->label }}</div>
                <div style="color:var(--ef-faint);font-size:.72rem;font-family:monospace">{{ $setting->key }}</div>
            </div>
            <div>
                @if($setting->type === 'boolean')
                <label class="ef-switch">
                    <input type="checkbox" name="{{ $setting->key }}" id="setting_{{ $setting->key }}"
                           value="1"
                           {{ $setting->value === '1' || $setting->value === 'true' ? 'checked' : '' }}>
                    <span>{{ $setting->value === '1' || $setting->value === 'true' ? 'Enabled' : 'Disabled' }}</span>
                </label>
                @elseif($setting->type === 'integer')
                <input type="number" name="{{ $setting->key }}"
                       class="ef-input @error($setting->key) --error @enderror"
                       value="{{ old($setting->key, $setting->value) }}"
                       min="0">
                @else
                <input type="text" name="{{ $setting->key }}"
                       class="ef-input @error($setting->key) --error @enderror"
                       value="{{ old($setting->key, $setting->value) }}">
                @endif
                @error($setting->key)
                <div class="ef-field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endforeach

    </x-ds.card>
    @endforeach

    <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="ef-btn ef-btn-dark">
            <i class="bi bi-save"></i> Save Settings
        </button>
    </div>
</form>

</x-admin-layout>
