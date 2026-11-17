<x-admin-layout :title="'Menu · ' . $booking->booking_reference">
@include('inventory._styles')
<div class="ic-wrap" style="max-width:820px">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('hall.bookings.show', $booking) }}"><i class="bi bi-arrow-left"></i> Booking</a>
        <h1>Final menu · {{ $booking->customer_name }}</h1>
    </div>
    <p class="ic-note">Plan: <strong>{{ $booking->uses_mixed_food ? $booking->foodSplits->pluck('meal_plan_name')->join(', ') : ($booking->mealPlan?->name ?? '—') }}</strong> · {{ $booking->number_of_people }} guests.
        Keep plan dishes, mark removed ones, add new ones and note changes (e.g. “extra paneer”). Material requests already made are never changed by this page.</p>
    @if($errors->any())<div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" data-ic-form action="{{ route('hall.bookings.menu.update', $booking) }}">
        @csrf @method('PUT')
        <div class="ic-card" id="menuRows">
            @foreach($lines as $i => $l)
                @include('hall.bookings._menu-row', ['i' => $i, 'l' => $l])
            @endforeach
        </div>
        <template id="menuTpl">@include('hall.bookings._menu-row', ['i' => '__I__', 'l' => ['change_type' => 'added']])</template>
        <button type="button" class="ic-btn ic-btn--sm" id="addDish"><i class="bi bi-plus-lg"></i> Add dish</button>
        <div class="ic-sticky" style="margin-top:14px">
            <span class="ic-total" style="font-size:1rem">{{ $booking->booking_reference }}</span>
            <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-save"></i> Save final menu</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
(function () {
    const rows = document.getElementById('menuRows');
    let n = rows.children.length + 1000;
    document.getElementById('addDish').addEventListener('click', () => {
        const html = document.getElementById('menuTpl').innerHTML.replaceAll('__I__', String(n++));
        rows.insertAdjacentHTML('beforeend', html);
        rows.lastElementChild.querySelector('input').focus();
    });
    rows.addEventListener('click', e => { if (e.target.closest('[data-del]')) e.target.closest('.ic-menu-row').remove(); });
})();
</script>
@endpush
</x-admin-layout>
