<x-admin-layout title="Hall Booking Calendar">

<style>
#calendar { min-height: 600px; }
.fc-event { cursor: pointer; font-size: .78rem; }
.fc-toolbar-title { font-size: 1.1rem !important; font-weight: 700 !important; }
.event-tooltip {
    position: fixed; z-index: 9999;
    background: #1e293b; color: #fff;
    border-radius: 10px; padding: .75rem 1rem;
    font-size: .8rem; max-width: 260px;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
    pointer-events: none;
}
</style>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2 text-primary"></i>Calendar View</h5>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <select id="hallFilter" class="form-select form-select-sm rounded-3" style="width:auto">
            <option value="">All Halls</option>
            @foreach(\App\Models\Hall::active()->orderBy('name')->get() as $h)
                <option value="{{ $h->id }}">{{ $h->name }}</option>
            @endforeach
        </select>
        <a href="{{ route('hall.bookings.create') }}" class="btn btn-primary btn-sm rounded-3">
            <i class="bi bi-plus-circle me-1"></i>New Booking
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <div id="calendar"></div>
    </div>
</div>

{{-- Event tooltip --}}
<div class="event-tooltip d-none" id="eventTooltip"></div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calEl    = document.getElementById('calendar');
    const tooltip  = document.getElementById('eventTooltip');
    const hallSel  = document.getElementById('hallFilter');

    const calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay',
        },
        height: 'auto',
        editable: false,
        selectable: true,
        selectMirror: true,

        events: function (info, success) {
            const params = new URLSearchParams({
                start:   info.startStr,
                end:     info.endStr,
                hall_id: hallSel.value,
            });
            fetch('{{ route("hall.bookings.calendar-events") }}?' + params)
                .then(r => r.json())
                .then(success);
        },

        select: function (info) {
            const date = info.startStr.split('T')[0];
            window.location.href = '{{ route("hall.bookings.create") }}?date=' + date;
        },

        eventClick: function (info) {
            window.location.href = info.event.extendedProps.url;
        },

        eventMouseEnter: function (info) {
            const p    = info.event.extendedProps;
            tooltip.innerHTML = `
                <div class="fw-semibold mb-1">${p.customer}</div>
                <div class="text-muted mb-1" style="font-size:.72rem">${p.hall} · ${p.people} pax</div>
                <div style="font-size:.72rem">${p.event_type}</div>
                <div class="mt-1 fw-semibold">₹${parseFloat(p.amount).toLocaleString('en-IN')}</div>
                <span class="badge bg-${p.status === 'confirmed' ? 'success' : p.status === 'completed' ? 'primary' : 'danger'} mt-1">${p.status}</span>
            `;
            tooltip.classList.remove('d-none');
        },

        eventMouseLeave: function () {
            tooltip.classList.add('d-none');
        },
    });

    calendar.render();

    document.addEventListener('mousemove', e => {
        tooltip.style.left = (e.clientX + 12) + 'px';
        tooltip.style.top  = (e.clientY + 8)  + 'px';
    });

    hallSel.addEventListener('change', () => calendar.refetchEvents());
});
</script>
@endpush

</x-admin-layout>
