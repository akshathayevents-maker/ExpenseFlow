<x-admin-layout title="Venue Operations Calendar">
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
@endpush

@php
    $currentMonth = now()->format('F Y');
    $todayLabel = now()->format('l, d M Y');
    $shareText = "Akshathay Mini Hall schedule for " . now()->format('d M Y') . "\n" . route('hall.bookings.calendar');
@endphp

<div class="ef-cal-shell">
    <header class="ef-cal-header">
        <div>
            <div class="ef-cal-kicker">Luxury Venue Operations</div>
            <h1 class="ef-cal-title">Calendar Overview</h1>
            <div class="ef-cal-subtitle">
                <span id="calendarPeriod">{{ $currentMonth }}</span>
                <span>{{ $summary['total_bookings'] }} bookings</span>
                <span>{{ $summary['occupancy'] }}% occupancy</span>
                <span>{{ $todayLabel }}</span>
            </div>
        </div>

        <div class="ef-cal-controls">
            <select id="hallFilter" class="ef-cal-select" aria-label="Filter by hall">
                <option value="">All Halls</option>
                @foreach($halls as $hall)
                    <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                @endforeach
            </select>
            <input id="calendarSearch" type="search" class="ef-cal-search" placeholder="Search customer, hall, event">
            <button type="button" class="ef-btn" id="printSchedule">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="ef-btn" id="exportSchedule">
                <i class="bi bi-download"></i> Export
            </button>
            <a href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="ef-btn">
                <i class="bi bi-whatsapp"></i> Share
            </a>
            <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-plus-lg"></i> New Booking
            </a>
        </div>
    </header>

    <section class="ef-cal-insights" aria-label="Monthly booking insights">
        <div class="ef-cal-insight">
            <span class="ef-label">Month Bookings</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['total_bookings']) }}</div>
            <div class="ef-cal-insight-caption">confirmed operational load</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Upcoming</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['upcoming_events']) }}</div>
            <div class="ef-cal-insight-caption">events from today</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Revenue</span>
            <div class="ef-cal-insight-value">₹{{ number_format($summary['revenue'], 0) }}</div>
            <div class="ef-cal-insight-caption">booked this month</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Occupancy</span>
            <div class="ef-cal-insight-value">{{ $summary['occupancy'] }}%</div>
            <div class="ef-cal-insight-caption">days with bookings</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Pending Pay</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['pending_payments']) }}</div>
            <div class="ef-cal-insight-caption">need follow-up</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Catering Load</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['catering_load']) }}</div>
            <div class="ef-cal-insight-caption">guest covers planned</div>
        </div>
    </section>

    <section class="ef-calendar-card">
        <div class="ef-calendar-toolbar">
            <div class="ef-cal-nav">
                <button type="button" class="ef-btn ef-btn-icon" id="calPrev" aria-label="Previous period"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="ef-btn" id="calToday">Today</button>
                <button type="button" class="ef-btn ef-btn-icon" id="calNext" aria-label="Next period"><i class="bi bi-chevron-right"></i></button>
            </div>

            <div class="ef-cal-month" id="calendarTitle">{{ $currentMonth }}</div>

            <div class="ef-view-switcher" aria-label="Calendar view">
                <button type="button" class="ef-view-btn active" data-view="dayGridMonth">Month</button>
                <button type="button" class="ef-view-btn" data-view="timeGridWeek">Week</button>
                <button type="button" class="ef-view-btn" data-view="timeGridDay">Day</button>
                <button type="button" class="ef-view-btn" data-view="listWeek">Agenda</button>
            </div>
        </div>

        <div class="ef-calendar-wrap">
            <div id="venueCalendar"></div>
        </div>
    </section>

    <section class="ef-agenda-panel" id="mobileAgenda" aria-label="Mobile agenda"></section>
</div>

<a href="{{ route('hall.bookings.create') }}" class="ef-mobile-fab">
    <i class="bi bi-plus-lg"></i> Booking
</a>

<div class="ef-preview" id="bookingPreview" aria-live="polite"></div>

<div class="modal fade ef-quick-modal" id="quickBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="ef-label mb-1">Fast Operation</div>
                    <h2 class="modal-title fs-5 fw-bold mb-0">Create booking</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="ef-shell-note mb-4">Start a new booking from the selected date. The full booking form will open with the date and hall context attached.</p>
                <div class="ef-info-grid">
                    <div>
                        <span class="ef-label">Selected Date</span>
                        <div class="ef-value ef-value-strong" id="quickDateLabel">-</div>
                    </div>
                    <div>
                        <span class="ef-label">Hall Context</span>
                        <div class="ef-value ef-value-strong" id="quickHallLabel">All Halls</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark" id="quickCreateLink">
                    Continue <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('venueCalendar');
    const hallFilter = document.getElementById('hallFilter');
    const searchInput = document.getElementById('calendarSearch');
    const preview = document.getElementById('bookingPreview');
    const mobileAgenda = document.getElementById('mobileAgenda');
    const quickModalEl = document.getElementById('quickBookingModal');
    const quickModal = new bootstrap.Modal(quickModalEl);
    const quickCreateLink = document.getElementById('quickCreateLink');
    const quickDateLabel = document.getElementById('quickDateLabel');
    const quickHallLabel = document.getElementById('quickHallLabel');
    const createBaseUrl = @json(route('hall.bookings.create'));
    const eventsUrl = @json(route('hall.bookings.calendar-events'));

    let allEvents = [];
    let lockedPreview = false;
    let searchTerm = '';

    const money = value => '₹' + Number(value || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);

    function eventMatches(event) {
        if (!searchTerm) return true;
        const p = event.extendedProps || {};
        return [event.title, p.customer, p.hall, p.event_type, p.payment_status_label]
            .filter(Boolean)
            .join(' ')
            .toLowerCase()
            .includes(searchTerm);
    }

    function renderEvent(info) {
        const p = info.event.extendedProps;
        const meals = (p.meals || []).slice(0, 2).join(' + ') || 'No meals';
        return {
            html: `
                <div class="ef-event-card">
                    <div class="ef-event-title">${escapeHtml(p.customer)}</div>
                    <div class="ef-event-meta">${escapeHtml(p.hall)} · ${Number(p.people || 0).toLocaleString('en-IN')} guests</div>
                    <div class="ef-event-sub">${escapeHtml(meals)} · ${escapeHtml(p.start_time)}-${escapeHtml(p.end_time)}</div>
                    <div class="ef-event-foot">
                        <span class="ef-event-mini pay-${escapeHtml(p.payment_status)}">${escapeHtml(p.payment_status_label)}</span>
                        <span class="ef-event-mini">${escapeHtml(p.status_label)}</span>
                    </div>
                </div>
            `
        };
    }

    function updateTitles() {
        const title = calendar.view.title;
        document.getElementById('calendarTitle').textContent = title;
        document.getElementById('calendarPeriod').textContent = title;
    }

    function applyDayDensity() {
        const counts = {};
        calendar.getEvents().forEach(event => {
            if (!eventMatches(event)) return;
            const key = event.startStr.slice(0, 10);
            counts[key] = (counts[key] || 0) + 1;
        });

        calEl.querySelectorAll('.fc-daygrid-day').forEach(cell => {
            cell.classList.remove('ef-busy-soft', 'ef-busy-mid', 'ef-busy-full');
            const count = counts[cell.dataset.date] || 0;
            if (count >= 3) cell.classList.add('ef-busy-full');
            else if (count === 2) cell.classList.add('ef-busy-mid');
            else if (count === 1) cell.classList.add('ef-busy-soft');
        });
    }

    function showPreview(event, jsEvent, lock = false) {
        const p = event.extendedProps;
        lockedPreview = lock;
        preview.innerHTML = `
            <div class="ef-preview-title">${escapeHtml(p.customer)}</div>
            <div class="ef-preview-meta">${escapeHtml(p.event_type)} · ${escapeHtml(p.hall)}<br>${escapeHtml(p.date)} · ${escapeHtml(p.start_time)}-${escapeHtml(p.end_time)}</div>
            <div class="ef-preview-grid">
                <div><div class="ef-preview-label">Guests</div><div class="ef-preview-value">${Number(p.people || 0).toLocaleString('en-IN')}</div></div>
                <div><div class="ef-preview-label">Meals</div><div class="ef-preview-value">${escapeHtml((p.meals || []).join(', ') || 'None')}</div></div>
                <div><div class="ef-preview-label">Total</div><div class="ef-preview-value">${money(p.amount)}</div></div>
                <div><div class="ef-preview-label">Balance</div><div class="ef-preview-value">${money(p.balance)}</div></div>
            </div>
            <div class="ef-preview-actions">
                <a href="${p.url}">Open</a>
                <a href="${p.payment_url}">Payment</a>
                <a href="${p.whatsapp_url}" target="_blank" rel="noopener">WhatsApp</a>
            </div>
        `;
        movePreview(jsEvent);
        preview.classList.add('show');
    }

    function movePreview(event) {
        const margin = 18;
        const width = 320;
        const left = Math.min(event.clientX + 16, window.innerWidth - width - margin);
        const top = Math.min(event.clientY + 16, window.innerHeight - preview.offsetHeight - margin);
        preview.style.left = Math.max(margin, left) + 'px';
        preview.style.top = Math.max(margin, top) + 'px';
    }

    function hidePreview(force = false) {
        if (lockedPreview && !force) return;
        preview.classList.remove('show');
        lockedPreview = false;
    }

    function renderAgenda() {
        const visible = calendar.getEvents()
            .filter(eventMatches)
            .sort((a, b) => a.start - b.start)
            .slice(0, 30);

        if (!visible.length) {
            mobileAgenda.innerHTML = '<div class="ef-agenda-card ef-shell-note">No bookings match this view.</div>';
            return;
        }

        mobileAgenda.innerHTML = visible.map(event => {
            const p = event.extendedProps;
            return `
                <a href="${p.url}" class="ef-agenda-card d-block text-decoration-none text-reset">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <span class="ef-label">${escapeHtml(p.date)} · ${escapeHtml(p.start_time)}</span>
                            <div class="ef-value-strong">${escapeHtml(p.customer)}</div>
                            <div class="ef-shell-note mt-1">${escapeHtml(p.hall)} · ${Number(p.people || 0).toLocaleString('en-IN')} guests · ${escapeHtml((p.meals || []).join(', ') || 'No meals')}</div>
                        </div>
                        <div class="text-end">
                            <div class="ef-value-strong">${money(p.amount)}</div>
                            <div class="ef-muted small">${escapeHtml(p.payment_status_label)}</div>
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    function openQuickBooking(dateStr) {
        const params = new URLSearchParams({ date: dateStr });
        if (hallFilter.value) params.set('hall_id', hallFilter.value);

        quickDateLabel.textContent = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-IN', {
            weekday: 'long', day: '2-digit', month: 'short', year: 'numeric'
        });
        quickHallLabel.textContent = hallFilter.options[hallFilter.selectedIndex]?.text || 'All Halls';
        quickCreateLink.href = createBaseUrl + '?' + params.toString();
        quickModal.show();
    }

    const calendar = new FullCalendar.Calendar(calEl, {
        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
        headerToolbar: false,
        height: 'auto',
        firstDay: 1,
        nowIndicator: true,
        dayMaxEvents: 3,
        eventDisplay: 'block',
        selectable: true,
        selectMirror: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '23:00:00',
        allDaySlot: false,
        events: function (info, success, failure) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                hall_id: hallFilter.value,
            });
            fetch(eventsUrl + '?' + params)
                .then(response => response.json())
                .then(events => {
                    allEvents = events;
                    success(events.filter(event => {
                        const props = event.extendedProps || {};
                        if (!searchTerm) return true;
                        return [event.title, props.customer, props.hall, props.event_type, props.payment_status_label]
                            .filter(Boolean)
                            .join(' ')
                            .toLowerCase()
                            .includes(searchTerm);
                    }));
                })
                .catch(failure);
        },
        datesSet: function () {
            updateTitles();
            setTimeout(() => {
                applyDayDensity();
                renderAgenda();
            }, 0);
        },
        eventsSet: function () {
            applyDayDensity();
            renderAgenda();
        },
        eventContent: renderEvent,
        select: function (info) {
            openQuickBooking(info.startStr.slice(0, 10));
            calendar.unselect();
        },
        dateClick: function (info) {
            openQuickBooking(info.dateStr);
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            showPreview(info.event, info.jsEvent, true);
        },
        eventMouseEnter: function (info) {
            if (!lockedPreview) showPreview(info.event, info.jsEvent, false);
        },
        eventMouseMove: function (info) {
            if (!lockedPreview) movePreview(info.jsEvent);
        },
        eventMouseLeave: function () {
            hidePreview();
        },
    });

    calendar.render();
    updateTitles();

    document.getElementById('calPrev').addEventListener('click', () => {
        hidePreview(true);
        calendar.prev();
    });
    document.getElementById('calNext').addEventListener('click', () => {
        hidePreview(true);
        calendar.next();
    });
    document.getElementById('calToday').addEventListener('click', () => {
        hidePreview(true);
        calendar.today();
    });

    document.querySelectorAll('.ef-view-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.ef-view-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            calendar.changeView(button.dataset.view);
        });
    });

    hallFilter.addEventListener('change', () => {
        hidePreview(true);
        calendar.refetchEvents();
    });

    searchInput.addEventListener('input', () => {
        searchTerm = searchInput.value.trim().toLowerCase();
        calendar.removeAllEvents();
        calendar.addEventSource(allEvents.filter(event => {
            const props = event.extendedProps || {};
            return !searchTerm || [event.title, props.customer, props.hall, props.event_type, props.payment_status_label]
                .filter(Boolean)
                .join(' ')
                .toLowerCase()
                .includes(searchTerm);
        }));
    });

    document.getElementById('printSchedule').addEventListener('click', () => window.print());
    document.getElementById('exportSchedule').addEventListener('click', () => {
        const rows = calendar.getEvents().filter(eventMatches).map(event => {
            const p = event.extendedProps;
            return [
                p.date,
                p.start_time,
                p.end_time,
                p.customer,
                p.hall,
                p.event_type,
                p.people,
                (p.meals || []).join(' + '),
                p.payment_status_label,
                p.amount,
                p.balance,
                p.url,
            ];
        });
        const headers = ['Date','Start','End','Customer','Hall','Event','Guests','Meals','Payment Status','Amount','Balance','URL'];
        const csv = [headers, ...rows].map(row => row.map(value => `"${String(value ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'akshathay-booking-schedule.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    });
    document.addEventListener('click', event => {
        if (!preview.contains(event.target) && !event.target.closest('.fc-event')) {
            hidePreview(true);
        }
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth < 768 && calendar.view.type !== 'listWeek') {
            calendar.changeView('listWeek');
            document.querySelectorAll('.ef-view-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.view === 'listWeek'));
        }
    });
});
</script>
@endpush
</x-admin-layout>
