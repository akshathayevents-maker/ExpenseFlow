<form method="GET" class="ic-card">
    <div class="ic-fields">
        <div><label class="ic-label">From</label><input class="ic-input" type="date" name="from" value="{{ $f['from'] ?? now('Asia/Kolkata')->subDays(29)->toDateString() }}"></div>
        <div><label class="ic-label">To</label><input class="ic-input" type="date" name="to" value="{{ $f['to'] ?? now('Asia/Kolkata')->toDateString() }}"></div>
        <div><label class="ic-label">Item</label><select class="ic-select" name="item_id"><option value="">All</option>@foreach($items as $id => $n)<option value="{{ $id }}" @selected(($f['item_id'] ?? null) == $id)>{{ $n }}</option>@endforeach</select></div>
        <div><label class="ic-label">Employee</label><select class="ic-select" name="user_id"><option value="">All</option>@foreach($users as $id => $n)<option value="{{ $id }}" @selected(($f['user_id'] ?? null) == $id)>{{ $n }}</option>@endforeach</select></div>
    </div>
    <details style="margin-top:10px" @if(! empty($f['meal_type']) || ! empty($f['hall_booking_id']) || ! empty($f['vendor_id']) || ! empty($f['category_id'])) open @endif>
        <summary class="ic-note" style="cursor:pointer;font-weight:700">More filters</summary>
        <div class="ic-fields" style="margin-top:10px">
            <div><label class="ic-label">Meal</label><select class="ic-select" name="meal_type"><option value="">All</option>@foreach(\App\Models\MealEntryItem::mealTypes() as $k => $m)<option value="{{ $k }}" @selected(($f['meal_type'] ?? null) === $k)>{{ $m['label'] }}</option>@endforeach</select></div>
            <div><label class="ic-label">Hall order</label><select class="ic-select" name="hall_booking_id"><option value="">All</option>@foreach($bookings as $b)<option value="{{ $b->id }}" @selected(($f['hall_booking_id'] ?? null) == $b->id)>{{ $b->booking_date->format('d M') }} · {{ $b->customer_name }}</option>@endforeach</select></div>
            <div><label class="ic-label">Supplier</label><select class="ic-select" name="vendor_id"><option value="">All</option>@foreach($vendors as $id => $n)<option value="{{ $id }}" @selected(($f['vendor_id'] ?? null) == $id)>{{ $n }}</option>@endforeach</select></div>
            <div><label class="ic-label">Category</label><select class="ic-select" name="category_id"><option value="">All</option>@foreach($categories as $id => $n)<option value="{{ $id }}" @selected(($f['category_id'] ?? null) == $id)>{{ $n }}</option>@endforeach</select></div>
        </div>
    </details>
    <button class="ic-btn ic-btn--dark" type="submit" style="margin-top:10px"><i class="bi bi-funnel"></i> Apply</button>
</form>
