<x-admin-layout title="Edit Meal Plan">
@push('styles')
<style>
/* ── Meal Plan Form — ef-mpf-* ──────────────────────────────────── */
.ef-mpf-shell {
    max-width: 720px;
    margin: 0 auto;
    padding-bottom: 60px;
}

/* Back nav */
.ef-mpf-back {
    align-items: center;
    color: var(--ef-muted);
    display: inline-flex;
    font-size: .78rem;
    font-weight: 640;
    gap: 6px;
    letter-spacing: .04em;
    margin-bottom: 24px;
    text-decoration: none;
    text-transform: uppercase;
    transition: color .15s;
}
.ef-mpf-back:hover { color: var(--ef-ink); }
.ef-mpf-back i { font-size: .82rem; }

/* Hero */
.ef-mpf-kicker {
    color: var(--ef-faint);
    font-size: .65rem;
    font-weight: 760;
    letter-spacing: .18em;
    text-transform: uppercase;
}
.ef-mpf-title {
    color: var(--ef-ink);
    font-size: clamp(1.6rem, 4vw, 2.4rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: 1.05;
    margin: 6px 0 6px;
}
.ef-mpf-subtitle {
    color: var(--ef-muted);
    font-size: .88rem;
    margin-bottom: 28px;
}

/* Form card */
.ef-mpf-card {
    background: rgba(255, 253, 250, .96);
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}
.ef-mpf-section {
    padding: 28px 32px;
}
.ef-mpf-section + .ef-mpf-section {
    border-top: 1px solid var(--ef-border);
}
.ef-mpf-section-label {
    color: var(--ef-faint);
    font-size: .64rem;
    font-weight: 760;
    letter-spacing: .16em;
    margin-bottom: 18px;
    text-transform: uppercase;
}

/* Field */
.ef-mpf-field { margin-bottom: 20px; }
.ef-mpf-field:last-child { margin-bottom: 0; }
.ef-mpf-label {
    color: var(--ef-ink);
    display: block;
    font-size: .8rem;
    font-weight: 680;
    margin-bottom: 7px;
}
.ef-mpf-label .req { color: var(--ef-danger); margin-left: 2px; }
.ef-mpf-hint {
    color: var(--ef-faint);
    font-size: .72rem;
    margin-top: 5px;
}

/* Inputs */
.ef-mpf-input,
.ef-mpf-select,
.ef-mpf-textarea {
    background: rgba(255, 253, 250, .7);
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 11px;
    color: var(--ef-ink);
    font-family: inherit;
    font-size: .88rem;
    padding: 11px 14px;
    transition: border-color .15s, box-shadow .15s;
    width: 100%;
}
.ef-mpf-input:focus,
.ef-mpf-select:focus,
.ef-mpf-textarea:focus {
    border-color: var(--ef-gold);
    box-shadow: 0 0 0 3px rgba(180, 145, 90, .12);
    outline: none;
}
.ef-mpf-input.is-invalid,
.ef-mpf-select.is-invalid,
.ef-mpf-textarea.is-invalid {
    border-color: var(--ef-danger);
    box-shadow: 0 0 0 3px rgba(141, 74, 60, .1);
}
.ef-mpf-textarea { min-height: 96px; resize: vertical; }
.ef-mpf-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23888' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }

/* Price field layout */
.ef-mpf-price-wrap {
    align-items: center;
    display: grid;
    gap: 0;
    grid-template-columns: 38px minmax(0, 1fr);
}
.ef-mpf-currency {
    align-items: center;
    background: rgba(20, 20, 18, .04);
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 11px 0 0 11px;
    border-right: none;
    color: var(--ef-muted);
    display: flex;
    font-size: .82rem;
    font-weight: 680;
    justify-content: center;
    padding: 11px 0;
}
.ef-mpf-price-wrap .ef-mpf-input { border-radius: 0 11px 11px 0; }

/* Toggle */
.ef-mpf-toggle-row {
    align-items: center;
    display: flex;
    gap: 14px;
    justify-content: space-between;
}
.ef-mpf-toggle-info { flex: 1; min-width: 0; }
.ef-mpf-toggle-title {
    color: var(--ef-ink);
    font-size: .88rem;
    font-weight: 680;
}
.ef-mpf-toggle-note {
    color: var(--ef-muted);
    font-size: .74rem;
    margin-top: 3px;
}
.ef-mpf-toggle {
    appearance: none;
    background: rgba(20, 20, 18, .12);
    border: none;
    border-radius: 20px;
    cursor: pointer;
    flex-shrink: 0;
    height: 26px;
    position: relative;
    transition: background .18s;
    width: 48px;
}
.ef-mpf-toggle::after {
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
    content: '';
    height: 20px;
    left: 3px;
    position: absolute;
    top: 3px;
    transition: transform .18s;
    width: 20px;
}
.ef-mpf-toggle:checked {
    background: var(--ef-gold);
}
.ef-mpf-toggle:checked::after {
    transform: translateX(22px);
}

/* Error text */
.ef-mpf-error { color: var(--ef-danger); font-size: .75rem; margin-top: 5px; }

/* Footer actions */
.ef-mpf-footer {
    align-items: center;
    border-top: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 20px 32px;
}
.ef-mpf-footer-right {
    align-items: center;
    display: flex;
    gap: 10px;
}

/* Danger zone */
.ef-mpf-danger-zone {
    align-items: center;
    display: flex;
    gap: 10px;
    justify-content: space-between;
}
.ef-mpf-danger-note {
    color: var(--ef-muted);
    font-size: .78rem;
    flex: 1;
    min-width: 0;
}

/* Errors summary */
.ef-mpf-alert {
    background: rgba(141, 74, 60, .06);
    border: 1px solid rgba(141, 74, 60, .18);
    border-radius: 12px;
    color: var(--ef-danger);
    font-size: .82rem;
    margin-bottom: 20px;
    padding: 14px 18px;
}
.ef-mpf-alert ul { margin: 6px 0 0 0; padding-left: 16px; }

/* Responsive */
@media (max-width: 767.98px) {
    .ef-mpf-section { padding: 20px 18px; }
    .ef-mpf-footer  { flex-direction: column; align-items: stretch; padding: 16px 18px; }
    .ef-mpf-footer-right { flex-direction: row-reverse; }
    .ef-mpf-danger-zone { flex-direction: column; align-items: flex-start; gap: 8px; }
    .ef-mpf-title { font-size: clamp(1.5rem, 6vw, 2rem); }
}
</style>
@endpush

<div class="ef-mpf-shell">

    <a href="{{ route('hall.meal-plans.index') }}" class="ef-mpf-back">
        <i class="bi bi-arrow-left"></i> Meal Plans
    </a>

    <p class="ef-mpf-kicker">Catering Management</p>
    <h1 class="ef-mpf-title">{{ $mealPlan->name }}</h1>
    <p class="ef-mpf-subtitle">Update plan details — changes apply to future bookings only.</p>

    @if ($errors->any())
        <div class="ef-mpf-alert">
            <strong>Please fix the following:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="ef-mpf-card">
        <form method="POST" action="{{ route('hall.meal-plans.update', $mealPlan) }}">
        @csrf @method('PUT')

        {{-- Identity --}}
        <div class="ef-mpf-section">
            <p class="ef-mpf-section-label">Plan Identity</p>

            <div class="ef-mpf-field">
                <label class="ef-mpf-label" for="name">Plan Name <span class="req">*</span></label>
                <input id="name" type="text" name="name" autocomplete="off"
                       class="ef-mpf-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $mealPlan->name) }}" required>
                @error('name')<p class="ef-mpf-error">{{ $message }}</p>@enderror
            </div>

            <div class="ef-mpf-field">
                <label class="ef-mpf-label" for="category">Category <span class="req">*</span></label>
                <select id="category" name="category"
                        class="ef-mpf-select @error('category') is-invalid @enderror" required>
                    @foreach(\App\Models\MealPlan::categories() as $v => $l)
                        <option value="{{ $v }}" {{ old('category', $mealPlan->category) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('category')<p class="ef-mpf-error">{{ $message }}</p>@enderror
            </div>

            <div class="ef-mpf-field">
                <label class="ef-mpf-label" for="description">Description</label>
                <textarea id="description" name="description"
                          class="ef-mpf-textarea @error('description') is-invalid @enderror"
                          placeholder="Describe what's included — courses, service style, dietary options…">{{ old('description', $mealPlan->description) }}</textarea>
                @error('description')<p class="ef-mpf-error">{{ $message }}</p>@enderror
                <p class="ef-mpf-hint">Shown to staff during booking — helps choose the right plan for each event.</p>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="ef-mpf-section">
            <p class="ef-mpf-section-label">Pricing</p>

            <div class="ef-mpf-field">
                <label class="ef-mpf-label" for="price_per_person">Price per Guest <span class="req">*</span></label>
                <div class="ef-mpf-price-wrap">
                    <div class="ef-mpf-currency">₹</div>
                    <input id="price_per_person" type="number" name="price_per_person"
                           step="0.01" min="0"
                           class="ef-mpf-input @error('price_per_person') is-invalid @enderror"
                           value="{{ old('price_per_person', $mealPlan->price_per_person) }}" required>
                </div>
                @error('price_per_person')<p class="ef-mpf-error">{{ $message }}</p>@enderror
                <p class="ef-mpf-hint">Per-person rate multiplied by guest count at booking time.</p>
            </div>
        </div>

        {{-- Settings --}}
        <div class="ef-mpf-section">
            <p class="ef-mpf-section-label">Availability</p>

            <div class="ef-mpf-toggle-row">
                <div class="ef-mpf-toggle-info">
                    <div class="ef-mpf-toggle-title">Active</div>
                    <div class="ef-mpf-toggle-note">Active plans appear as options during hall booking.</div>
                </div>
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="ef-mpf-toggle"
                       {{ old('is_active', $mealPlan->is_active) ? 'checked' : '' }}>
            </div>
        </div>

        {{-- Footer --}}
        <div class="ef-mpf-footer">
            {{-- Danger zone: delete (only if no bookings) --}}
            @if($mealPlan->bookings_count === 0)
                <form method="POST" action="{{ route('hall.meal-plans.destroy', $mealPlan) }}"
                      onsubmit="return confirm('Delete {{ addslashes($mealPlan->name) }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn" style="color:var(--ef-danger);border-color:rgba(141,74,60,.22);">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            @else
                <span class="ef-mpf-danger-note">
                    <i class="bi bi-lock me-1" style="color:var(--ef-faint)"></i>
                    Can't delete — {{ $mealPlan->bookings_count }} {{ Str::plural('booking', $mealPlan->bookings_count) }} attached
                </span>
            @endif

            <div class="ef-mpf-footer-right">
                <a href="{{ route('hall.meal-plans.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn" style="background:var(--ef-gold);border-color:var(--ef-gold);color:#fffdfa;">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
            </div>
        </div>

        </form>
    </div>
</div>

</x-admin-layout>
