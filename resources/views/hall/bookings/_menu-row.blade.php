<div class="ic-menu-row" style="display:grid;grid-template-columns:2fr 1fr 2fr 40px;gap:8px;padding:6px 0;border-bottom:1px solid var(--ef-border)">
    <input class="ic-input" name="menu[{{ $i }}][name]" value="{{ $l['name'] ?? '' }}" placeholder="Dish" maxlength="150" aria-label="Dish">
    <select class="ic-select" name="menu[{{ $i }}][change_type]" aria-label="Change">
        @foreach(\App\Models\HallBookingMenuItem::CHANGES as $k => $lab)
            <option value="{{ $k }}" @selected(($l['change_type'] ?? 'plan') === $k || (! empty($l['is_removed']) && $k === 'removed'))>{{ $lab }}</option>
        @endforeach
    </select>
    <input class="ic-input" name="menu[{{ $i }}][notes]" value="{{ $l['notes'] ?? '' }}" placeholder="Note (e.g. extra paneer)" maxlength="300" aria-label="Note">
    <button type="button" class="ic-del" data-del aria-label="Remove line"><i class="bi bi-x-circle"></i></button>
</div>
