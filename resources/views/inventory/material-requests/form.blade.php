<x-admin-layout :title="$mr ? 'Edit ' . $mr->reference() : 'Request Materials'">
@include('inventory._styles')
@php
    $v = fn ($k, $d = null) => old($k, $prefill[$k] ?? $d);
    $source = $v('source', 'other');
@endphp
<div class="ic-wrap" style="max-width:960px">
    <div class="ic-head">
        <a class="ic-back" href="{{ $mr ? route('inventory.material-requests.show', $mr) : route('inventory.home') }}"><i class="bi bi-arrow-left"></i> Back</a>
        <h1>{{ $mr ? 'Edit ' . $mr->reference() : 'Request materials from store' }}</h1>
    </div>

    @if($errors->any())
        <div class="ic-err"><strong>Please fix:</strong><ul>@foreach(collect($errors->all())->unique()->take(12) as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" data-ic-form action="{{ $mr ? route('inventory.material-requests.update', $mr) : route('inventory.material-requests.store') }}">
        @csrf
        @if($mr) @method('PUT') @endif
        <input type="hidden" name="client_token" value="{{ old('client_token', $token) }}">
        @if(! empty($prefill['recipe_id']))<input type="hidden" name="recipe_id" value="{{ old('recipe_id', $prefill['recipe_id']) }}">@endif

        {{-- Step 1: what is it for --}}
        <div class="ic-card">
            <h2>1 · What is it for?</h2>
            <div class="ic-tabs" role="radiogroup">
                @foreach(['meal' => ['bi-egg-fried', 'Daily meal'], 'hall' => ['bi-stars', 'Hall order'], 'other' => ['bi-box', 'Other']] as $k => [$icon, $label])
                    <label class="ic-tab {{ $source === $k ? '--on' : '' }}" style="cursor:pointer;min-height:44px;display:flex;align-items:center;gap:6px">
                        <input type="radio" name="source" value="{{ $k }}" @checked($source === $k) style="display:none" data-source> <i class="bi {{ $icon }}"></i> {{ $label }}
                    </label>
                @endforeach
            </div>
            <div class="ic-fields">
                <div data-for="meal">
                    <label class="ic-label" for="meal_entry_id">Meal register entry</label>
                    <select class="ic-select" id="meal_entry_id" name="meal_entry_id">
                        <option value="">Choose…</option>
                        @foreach($mealEntries as $e)
                            <option value="{{ $e->id }}" data-date="{{ $e->entry_date->toDateString() }}" @selected((string) $v('meal_entry_id') === (string) $e->id)>{{ $e->entry_date->format('D d M') }} · {{ $e->client?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-for="meal">
                    <label class="ic-label" for="meal_type">Meal</label>
                    <select class="ic-select" id="meal_type" name="meal_type">
                        @foreach($mealTypes as $k => $m)
                            <option value="{{ $k }}" @selected($v('meal_type') === $k)>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-for="hall">
                    <label class="ic-label" for="hall_booking_id">Hall order</label>
                    <select class="ic-select" id="hall_booking_id" name="hall_booking_id">
                        <option value="">Choose…</option>
                        @foreach($bookings as $b)
                            <option value="{{ $b->id }}" data-date="{{ $b->booking_date->toDateString() }}" data-guests="{{ $b->number_of_people }}" @selected((string) $v('hall_booking_id') === (string) $b->id)>{{ $b->booking_date->format('D d M') }} · {{ $b->customer_name }} · {{ $b->number_of_people }} guests</option>
                        @endforeach
                    </select>
                </div>
                <div data-for="other">
                    <label class="ic-label" for="purpose">Purpose</label>
                    <input class="ic-input" id="purpose" name="purpose" value="{{ $v('purpose') }}" maxlength="200" placeholder="e.g. Staff tea, cleaning">
                </div>
            </div>
        </div>

        {{-- Step 2: details --}}
        <div class="ic-card">
            <h2>2 · Details</h2>
            <div class="ic-fields">
                <div>
                    <label class="ic-label" for="preparation_name">Preparation</label>
                    <input class="ic-input" id="preparation_name" name="preparation_name" value="{{ $v('preparation_name') }}" maxlength="150" placeholder="e.g. Upma">
                </div>
                <div>
                    <label class="ic-label" for="guest_count">Number of people</label>
                    <input class="ic-input" type="number" min="1" id="guest_count" name="guest_count" value="{{ $v('guest_count') }}" inputmode="numeric">
                </div>
                <div>
                    <label class="ic-label" for="required_date">Needed on</label>
                    <input class="ic-input" type="date" id="required_date" name="required_date" value="{{ $v('required_date') }}" required>
                </div>
                <div>
                    <label class="ic-label" for="required_time">Time</label>
                    <input class="ic-input" type="time" id="required_time" name="required_time" value="{{ $v('required_time') ? substr($v('required_time'), 0, 5) : '' }}">
                </div>
                <div>
                    <label class="ic-label" for="department">Section</label>
                    <select class="ic-select" id="department" name="department">
                        @foreach(\App\Models\MaterialRequest::DEPARTMENTS as $k => $l)
                            <option value="{{ $k }}" @selected($v('department', 'kitchen') === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($recipes->isNotEmpty() && ! $mr)
                <div class="ic-note" style="margin-top:10px">
                    Have a standard recipe? <select class="ic-select" id="recipeSuggest" style="display:inline-block;width:auto;min-height:38px">
                        <option value="">Suggest quantities from…</option>
                        @foreach($recipes as $rc)<option value="{{ $rc->id }}" @selected(($prefill['recipe_id'] ?? null) == $rc->id)>{{ $rc->name }} (v{{ $rc->version }})</option>@endforeach
                    </select>
                    <span>(you can still change every quantity)</span>
                </div>
            @endif
            @if($suggestion)
                <div class="ic-banner" style="margin-top:10px"><i class="bi bi-lightbulb"></i>
                    <span>Quantities suggested from <strong>{{ $suggestion['recipe'] }}</strong> v{{ $suggestion['version'] }} for {{ $suggestion['guests'] }} people. Check and change as needed.</span></div>
            @endif
        </div>

        {{-- Step 3: materials --}}
        <div class="ic-card">
            <h2>3 · Materials</h2>
            @if(! empty($quickItems['recent']) || ! empty($quickItems['frequent']))
                <div style="margin-bottom:10px">
                    <span class="ic-label">Tap to add</span>
                    <div class="ic-tabs" style="flex-wrap:wrap">
                        @foreach(collect($quickItems['frequent'])->merge($quickItems['recent'])->unique('id')->take(12) as $q)
                            <button type="button" class="ic-tab" data-quick="{{ $q['id'] }}"><i class="bi bi-plus"></i> {{ $q['name'] }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
            @include('inventory._grid', [
                'mode' => 'request', 'catalog' => $catalog, 'lines' => $lines,
                'storageKey' => $mr ? null : 'ic-request-draft-' . auth()->id(), 'vendorSelect' => null,
            ])
            <div style="margin-top:12px">
                <label class="ic-label" for="notes">Notes for the approver</label>
                <input class="ic-input" id="notes" name="notes" value="{{ $v('notes') }}" maxlength="1000">
            </div>
        </div>

        <div class="ic-sticky">
            <span class="ic-total" style="font-size:1rem"><small>Materials</small><span id="icRows">0</span> item(s)</span>
            <button type="submit" name="action" value="draft" class="ic-btn"><i class="bi bi-save"></i> Save draft</button>
            <button type="submit" name="action" value="submit" class="ic-btn ic-btn--gold"><i class="bi bi-send"></i> Send for approval</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const radios = document.querySelectorAll('[data-source]');
    function show() {
        const v = document.querySelector('[data-source]:checked')?.value || 'other';
        document.querySelectorAll('[data-for]').forEach(el => el.style.display = el.dataset.for === v ? '' : 'none');
        radios.forEach(r => r.closest('label').classList.toggle('--on', r.checked));
    }
    radios.forEach(r => r.addEventListener('change', show));
    show();
    const date = document.getElementById('required_date');
    const guests = document.getElementById('guest_count');
    ['meal_entry_id', 'hall_booking_id'].forEach(id => document.getElementById(id)?.addEventListener('change', e => {
        const o = e.target.selectedOptions[0];
        if (o?.dataset.date) date.value = o.dataset.date;
        if (o?.dataset.guests && !guests.value) guests.value = o.dataset.guests;
    }));
    document.querySelectorAll('[data-quick]').forEach(b => b.addEventListener('click', () => window.icGrids?.request?.addItem(b.dataset.quick)));
    document.addEventListener('ic-grid-total', e => { if (e.detail.mode === 'request') document.getElementById('icRows').textContent = e.detail.rows; });
    document.getElementById('recipeSuggest')?.addEventListener('change', e => {
        if (!e.target.value) return;
        const u = new URL(window.location.href);
        u.searchParams.set('recipe_id', e.target.value);
        if (guests.value) u.searchParams.set('guest_count', guests.value);
        window.location = u.toString();
    });
})();
</script>
@endpush
</x-admin-layout>
