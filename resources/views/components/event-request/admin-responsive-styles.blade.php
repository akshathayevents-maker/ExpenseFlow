{{-- Shared responsive primitives for the Event Request admin module
     (/admin/event-requests, /admin/event-request-menu/items, /categories).
     Included once per page via <x-event-request.admin-responsive-styles />.
     Uses Bootstrap's existing d-none/d-md-* utilities to switch between the
     desktop table and a mobile card list — no custom breakpoints, no JS. --}}
<style>
    /* ── Page header: title (+desc) left, primary action right on desktop;
       stacked with a full-width action on mobile ── */
    .erm-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .erm-header-text { min-width: 0; }
    .erm-header-text h1 { overflow-wrap: anywhere; }
    .erm-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    @media (max-width: 575.98px) {
        .erm-header { flex-direction: column; align-items: stretch; }
        .erm-header-actions { width: 100%; }
        .erm-header-actions .btn { flex: 1; }
    }

    /* ── Toolbar (search + filter selects) — wraps naturally, search takes
       full width first on mobile ── */
    .erm-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .erm-toolbar .erm-field { flex: 1 1 160px; min-width: 0; }
    .erm-toolbar .erm-field.erm-field-search { flex-basis: 100%; }
    @media (min-width: 576px) {
        .erm-toolbar .erm-field.erm-field-search { flex-basis: 240px; }
    }
    .erm-toolbar .btn { flex-shrink: 0; }

    /* ── Quick-filter chips ── */
    .erm-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .erm-chip {
        border: 1.5px solid #e2dccc;
        background: #fff;
        color: #4a4536;
        font-size: .78rem;
        font-weight: 650;
        min-height: 44px;
        box-sizing: border-box;
        padding: 7px 14px;
        border-radius: 999px;
        text-decoration: none;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }
    .erm-chip:hover { border-color: #B8893E; color: #4a4536; }
    .erm-chip.active { background: #3E2D23; border-color: #3E2D23; color: #fff; }

    /* ── Desktop table / mobile card toggle ── */
    .erm-desktop-table { display: none; }
    .erm-mobile-cards { display: flex; flex-direction: column; gap: 12px; }
    @media (min-width: 768px) {
        .erm-desktop-table { display: block; }
        .erm-mobile-cards { display: none; }
    }

    /* ── Mobile card ── */
    .erm-card {
        background: #fff;
        border: 1px solid #e9e3d5;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 1px 2px rgba(42,33,26,.04);
    }
    .erm-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .erm-card-title { font-weight: 700; font-size: .95rem; min-width: 0; overflow-wrap: anywhere; }
    .erm-card-subtitle { font-size: .78rem; color: #8a8370; margin-top: 1px; overflow-wrap: anywhere; }
    .erm-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 14px;
        margin-top: 12px;
    }
    .erm-card-field { min-width: 0; }
    .erm-card-field .k { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9c8f79; }
    .erm-card-field .v { font-size: .85rem; font-weight: 600; overflow-wrap: anywhere; margin-top: 1px; }
    .erm-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #f0ece0;
    }
    .erm-card-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .erm-card-actions .btn,
    .erm-card-actions .dropdown-toggle {
        min-height: 44px;
        min-width: 44px;
    }
    /* Primary action in a mobile card footer (e.g. "View Request") — keep
       full touch-target height even though it uses the compact btn-sm text size. */
    .erm-card-footer .btn {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
    }

    /* ── Compact card variant (menu items list) — denser than the default
       card: tighter padding/gaps, metadata collapsed into one inline row
       instead of a labeled k/v grid, minimal footer divider. Opt-in via
       .erm-cards-compact on the list wrapper so categories/event-requests
       keep their existing card look untouched. ── */
    .erm-mobile-cards.erm-cards-compact { gap: 8px; }
    .erm-cards-compact .erm-card { padding: 12px 14px; border-radius: 12px; }
    .erm-cards-compact .erm-card-top { gap: 8px; }
    .erm-cards-compact .erm-card-title { font-size: .92rem; line-height: 1.3; }
    .erm-cards-compact .erm-card-subtitle { font-size: .74rem; line-height: 1.25; margin-top: 2px; }

    .erm-item-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
        margin-top: 6px;
    }
    .erm-item-meta-price {
        font-size: .85rem;
        font-weight: 700;
        color: #2A211A;
        font-variant-numeric: tabular-nums;
    }

    .erm-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 8px;
    }
    .erm-item-id { font-size: .72rem; color: #9c8f79; font-variant-numeric: tabular-nums; }
    .erm-item-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .erm-item-actions .erm-btn-edit {
        min-height: 44px;
        padding: 0 14px;
        font-size: .82rem;
        display: inline-flex;
        align-items: center;
    }
    .erm-item-actions .erm-more-btn { width: 44px; height: 44px; }

    /* Restrained pill badges for the compact item card — softer than raw
       Bootstrap contextual colors so Popular/Chef don't visually compete
       with the item name and price. Desktop table keeps its existing
       Bootstrap badges untouched. */
    .erm-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .68rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        line-height: 1.4;
        white-space: nowrap;
    }
    .erm-badge-status { font-size: .66rem; }
    .erm-badge-status.is-active { background: #e7f3ea; color: #2f7a4f; }
    .erm-badge-status.is-active::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #2f7a4f; }
    .erm-badge-status.is-inactive { background: #f1ede4; color: #8a8370; }
    .erm-badge-status.is-inactive::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #8a8370; }
    .erm-badge-veg { background: #e7f3ea; color: #2f7a4f; }
    .erm-badge-nonveg { background: #fbe9e9; color: #c0392b; }
    .erm-badge-popular { background: rgba(184,137,62,.14); color: #8a6820; }
    .erm-badge-chef { background: #B8893E; color: #fff; }

    /* ── Consistent "More" action menu (Bootstrap dropdown) used on both
       mobile cards and can be reused on desktop rows when there are many
       actions ── */
    .erm-more-btn {
        width: 44px; height: 44px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px;
    }

    /* ── Empty state ── */
    .erm-empty { text-align: center; padding: 48px 20px; color: #6B5D4C; }
    .erm-empty .glyph { font-size: 2rem; margin-bottom: 10px; opacity: .5; }
    .erm-empty .title { font-weight: 700; font-size: .95rem; margin-bottom: 4px; color: #2A211A; }
    .erm-empty .body { font-size: .84rem; margin-bottom: 16px; }

    /* ── Forms: force single column below sm, regardless of existing
       col-md-* markup (which only kicks in at >=768px anyway, but some
       admin forms here use col-md-4 etc. starting at 576px in places) ── */
    @media (max-width: 575.98px) {
        .erm-form-1col .col-md-1,
        .erm-form-1col .col-md-2,
        .erm-form-1col .col-md-3,
        .erm-form-1col .col-md-4,
        .erm-form-1col .col-md-5,
        .erm-form-1col .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    /* ── Modals: never exceed viewport, inputs full width, footer buttons
       wrap instead of overflowing ── */
    @media (max-width: 575.98px) {
        .modal-dialog { width: calc(100% - 24px); max-width: 100%; margin: 12px auto; }
        .modal-footer { flex-wrap: wrap; }
        .modal-footer > * { flex: 1 1 auto; }
    }
</style>
