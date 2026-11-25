{{-- Shared Hall Operations look (dark header, insight tiles, quick actions, today strip).
     Source of truth for hall/bookings/calendar, hall/dashboard and hall/bookings/index. --}}
@once
@push('styles')
<style>
/* ── Calendar header: dark dramatic override ───────────────────── */
.ef-cal-header {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    margin-bottom: 24px;
    /* NO overflow:hidden — it clips positioned dropdowns AND makes native <select>
       option text inherit dark color, rendering invisible on OS light popup background */
    overflow: visible;
    padding: 32px;
    position: relative;
    z-index: 10;
}
.ef-cal-header::before {
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    /* keep orb within header bounds: was right:-80px top:-140px which bled outside */
    height: 360px;
    pointer-events: none;
    position: absolute;
    right: 0;
    top: -60px;
    width: 360px;
    z-index: 0;
}
.ef-cal-header::after {
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 70%);
    border-radius: 50%;
    /* keep orb within header bounds: was bottom:-80px which bled outside */
    bottom: -20px;
    content: "";
    height: 220px;
    left: 28%;
    pointer-events: none;
    position: absolute;
    width: 220px;
    z-index: 0;
}
.ef-cal-kicker  { color: rgba(160,114,56,.9) !important; }
.ef-cal-title   { color: #fffdfa !important; }
.ef-cal-subtitle { color: rgba(255,253,250,.52) !important; }
/* Controls need a high z-index so any dropdown/popover renders above the
   insights strip, quick actions, and calendar grid that follow in DOM order */
.ef-cal-controls {
    position: relative;
    z-index: 50;
}
.ef-cal-controls .ef-btn {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.85);
}
.ef-cal-controls .ef-btn:hover {
    background: rgba(255,255,255,.14);
    color: #fffdfa;
}
.ef-cal-controls .ef-btn-dark {
    background: #a07238;
    border-color: #a07238;
    color: #fff;
}
.ef-cal-controls .ef-btn-dark:hover {
    background: #b8854a;
    border-color: #b8854a;
}
.ef-cal-select,
.ef-cal-search {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.88);
}
.ef-cal-select::placeholder,
.ef-cal-search::placeholder { color: rgba(255,253,250,.32); }
.ef-cal-select:focus,
.ef-cal-search:focus {
    border-color: rgba(160,114,56,.6);
    box-shadow: 0 0 0 3px rgba(160,114,56,.12);
}

/* ── Quick actions row ──────────────────────────────────────────── */
.ef-cal-qactions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 18px;
}
.ef-cal-qa {
    align-items: center;
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 10px;
    box-shadow: var(--ef-shadow);
    color: var(--ef-ink-2);
    display: inline-flex;
    font-size: .78rem;
    font-weight: 680;
    gap: 7px;
    height: 36px;
    padding: 0 14px;
    text-decoration: none;
    transition: background .13s, box-shadow .13s, transform .13s;
}
.ef-cal-qa:hover { background: var(--ef-surface-2); box-shadow: var(--ef-shadow-hover); color: var(--ef-ink); transform: translateY(-1px); }
.ef-cal-qa i { color: var(--ef-faint); font-size: .8rem; }
.ef-cal-qa.--primary {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
    font-weight: 760;
}
.ef-cal-qa.--primary i { color: rgba(255,253,250,.7); }
.ef-cal-qa.--primary:hover { background: var(--ef-ink-2); border-color: var(--ef-ink-2); color: #fffdfa; }
.ef-cal-qa-sep { background: var(--ef-border); height: 28px; width: 1px; }

/* ── Today's ops strip ──────────────────────────────────────────── */
.ef-cal-today-strip {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-left: 3px solid #a07238;
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 18px;
    overflow: hidden;
}
.ef-cal-today-head {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 11px 18px;
}
.ef-cal-today-title {
    align-items: center;
    color: var(--ef-ink);
    display: flex;
    font-size: .84rem;
    font-weight: 780;
    gap: 8px;
}
.ef-cal-today-badge {
    background: rgba(160,114,56,.12);
    border: 1px solid rgba(160,114,56,.25);
    border-radius: 20px;
    color: #a07238;
    font-size: .62rem;
    font-weight: 720;
    padding: 2px 9px;
}
.ef-cal-today-kpi {
    align-items: center;
    display: flex;
    gap: 16px;
}
.ef-cal-today-kpi-item {
    text-align: right;
}
.ef-cal-today-kpi-val { color: var(--ef-ink); font-size: .9rem; font-variant-numeric: tabular-nums; font-weight: 760; line-height: 1; }
.ef-cal-today-kpi-lbl { color: var(--ef-faint); font-size: .58rem; font-weight: 720; letter-spacing: .1em; margin-top: 2px; text-transform: uppercase; }
.ef-cal-today-rows { display: flex; flex-wrap: wrap; gap: 0; }
.ef-cal-today-row {
    align-items: center;
    border-right: 1px solid var(--ef-border);
    display: flex;
    flex: 1;
    gap: 10px;
    min-width: 220px;
    padding: 12px 18px;
    text-decoration: none;
    transition: background .12s;
}
.ef-cal-today-row:hover { background: var(--ef-surface-2); }
.ef-cal-today-row:last-child { border-right: none; }
.ef-cal-today-live {
    background: rgba(220,38,38,.1);
    border: 1px solid rgba(220,38,38,.25);
    border-radius: 6px;
    color: #dc2626;
    flex-shrink: 0;
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .06em;
    padding: 2px 6px;
    text-transform: uppercase;
}
.ef-cal-today-time { color: var(--ef-muted); flex-shrink: 0; font-size: .72rem; font-weight: 700; min-width: 60px; }
.ef-cal-today-name { color: var(--ef-ink-2); font-size: .84rem; font-weight: 660; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-cal-today-meta { color: var(--ef-faint); font-size: .68rem; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-cal-today-empty { color: var(--ef-faint); font-size: .82rem; padding: 16px 18px; text-align: center; }

/* ── Shared hall additions (dashboard / bookings) ─────────────── */
.ef-cal-header .ef-cal-subtitle span{display:inline;min-width:0}
.ef-cal-header .ef-cal-subtitle i{color:rgba(160,114,56,.85);margin-right:6px}
.ef-cal-insights.--4{grid-template-columns:repeat(4,minmax(0,1fr))}
a.ef-cal-insight{display:block;text-decoration:none;color:inherit;transition:box-shadow .13s,transform .13s,border-color .13s}
a.ef-cal-insight:hover{box-shadow:var(--ef-shadow-hover);transform:translateY(-1px);border-color:rgba(160,114,56,.45)}
.ef-cal-insight .ef-label{display:flex;align-items:center;gap:7px}
.ef-cal-insight .ef-label i{color:#a07238}
.ef-cal-insight-value.--good{color:#1a6645}
.ef-cal-insight-value.--warn{color:#a07238}
.ef-cal-insight-value.--bad{color:var(--ef-danger)}
.ef-cal-insight-value.--muted{color:var(--ef-faint)}
@media (max-width:1199.98px){.ef-cal-insights.--4{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:767.98px){
  .ef-cal-header.--page{padding:20px 18px;border-radius:16px;gap:14px}
  .ef-cal-header.--page .ef-cal-title{font-size:1.7rem}
  .ef-cal-insight{min-height:0;padding:14px}
  .ef-cal-insight-value{font-size:1.2rem;margin-top:8px}
}

</style>
@endpush
@endonce
