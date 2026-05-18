@php
    $isAdmin = ($routePrefix === 'admin');
    $indexRoute = $isAdmin ? 'admin.expense-requests.index' : 'manager.expense-requests.index';

    $rdChips = [
        'pending'               => ['bg' => 'rgba(216,154,61,.14)',  'color' => '#7D5218', 'dot' => '#D89A3D', 'label' => 'Pending'],
        'pending_payment'       => ['bg' => 'rgba(47,111,237,.10)',  'color' => '#1E4DB7', 'dot' => '#2F6FED', 'label' => 'Pending Payment'],
        'approved'              => ['bg' => 'rgba(15,123,95,.12)',   'color' => '#0A5240', 'dot' => '#0F7B5F', 'label' => 'Approved'],
        'rejected'              => ['bg' => 'rgba(200,75,68,.12)',   'color' => '#9B2C2C', 'dot' => '#C84B44', 'label' => 'Rejected'],
        'paid'                  => ['bg' => 'rgba(13,148,136,.11)',  'color' => '#0E6B62', 'dot' => '#0D9488', 'label' => 'Paid'],
        'reimbursement_pending' => ['bg' => 'rgba(184,137,62,.13)',  'color' => '#6B4A12', 'dot' => '#B8893E', 'label' => 'Reimbursement Pending'],
        'reimbursed'            => ['bg' => 'rgba(15,123,95,.12)',   'color' => '#0A5240', 'dot' => '#0F7B5F', 'label' => 'Reimbursed'],
        'completed'             => ['bg' => 'rgba(110,106,100,.09)', 'color' => '#6E6A64', 'dot' => '#9A9690', 'label' => 'Completed'],
    ];
    $chip = $rdChips[$expenseRequest->status] ?? ['bg' => 'rgba(110,106,100,.09)', 'color' => '#6E6A64', 'dot' => '#9A9690', 'label' => ucfirst($expenseRequest->status)];

    $priColors = [
        'low'    => ['bg' => 'rgba(110,106,100,.09)', 'color' => '#6E6A64', 'icon' => 'bi-arrow-down'],
        'medium' => ['bg' => 'rgba(47,111,237,.09)',  'color' => '#1E4DB7', 'icon' => 'bi-dash'],
        'high'   => ['bg' => 'rgba(216,154,61,.13)',  'color' => '#7D5218', 'icon' => 'bi-arrow-up'],
        'urgent' => ['bg' => 'rgba(200,75,68,.13)',   'color' => '#9B2C2C', 'icon' => 'bi-exclamation-triangle-fill'],
    ];
    $pri = $priColors[$expenseRequest->priority] ?? $priColors['medium'];
@endphp

<style>
:root {
    --rd-emerald:    #0F7B5F;
    --rd-emerald-hi: #0D9E78;
    --rd-emerald-dk: #0D5C43;
    --rd-gold:       #B8893E;
    --rd-gold-soft:  #D6B97A;
    --rd-danger:     #C84B44;
    --rd-warning:    #D89A3D;
    --rd-info:       #2F6FED;
    --rd-ink:        #101714;
    --rd-muted:      #6E6A64;
    --rd-faint:      #F3F7F4;
    --rd-border:     rgba(15,123,95,.11);
    --rd-border-s:   rgba(15,123,95,.24);
}

/* ── Hero ─────────────────────────────────────────────────────────────── */
.ef-rd-hero {
    background: linear-gradient(135deg, #0a1810 0%, #0f2419 45%, #081510 100%);
    border-radius: 16px;
    padding: 2rem 2rem 1.5rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.ef-rd-hero::before {
    content: '';
    position: absolute;
    top: -60px; left: -60px;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(15,123,95,.22) 0%, transparent 70%);
    pointer-events: none;
}
.ef-rd-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -40px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(184,137,62,.15) 0%, transparent 70%);
    pointer-events: none;
}
.ef-rd-hero-eyebrow {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--rd-gold-soft);
    margin-bottom: .4rem;
}
.ef-rd-hero-title {
    color: #f0ede8;
    font-size: 1.45rem;
    font-weight: 700;
    margin-bottom: .25rem;
    line-height: 1.3;
}
.ef-rd-hero-meta {
    color: rgba(255,255,255,.45);
    font-size: .78rem;
}
.ef-rd-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .4px;
}
.ef-rd-chip-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ── KPI strip ───────────────────────────────────────────────────────── */
.ef-rd-kpi-strip {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255,255,255,.07);
    position: relative; z-index: 1;
}
.ef-rd-kpi-item {
    flex: 1;
    min-width: 90px;
}
.ef-rd-kpi-label {
    color: rgba(255,255,255,.38);
    font-size: .65rem;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: .2rem;
}
.ef-rd-kpi-val {
    color: rgba(255,255,255,.88);
    font-size: .92rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-rd-kpi-val.amount {
    color: var(--rd-gold-soft);
    font-size: 1.15rem;
    font-weight: 700;
}

/* ── Info card ───────────────────────────────────────────────────────── */
.ef-rd-card {
    background: #fff;
    border: 1px solid var(--rd-border);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 16px rgba(15,123,95,.04);
}
.ef-rd-card-header {
    padding: .85rem 1.25rem;
    background: linear-gradient(90deg, rgba(15,123,95,.04), transparent);
    border-bottom: 1px solid var(--rd-border);
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: var(--rd-emerald-dk);
    display: flex;
    align-items: center;
    gap: .5rem;
}
.ef-rd-card-body { padding: 1.25rem; }

.ef-rd-info-row {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    padding: .75rem 0;
    border-bottom: 1px solid var(--rd-border);
}
.ef-rd-info-row:last-child { border-bottom: none; padding-bottom: 0; }
.ef-rd-info-row:first-child { padding-top: 0; }

.ef-rd-icon-box {
    width: 36px; height: 36px;
    border-radius: 9px;
    background: rgba(15,123,95,.09);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: var(--rd-emerald);
    font-size: .95rem;
}
.ef-rd-info-label {
    font-size: .68rem;
    font-weight: 600;
    letter-spacing: .8px;
    text-transform: uppercase;
    color: var(--rd-muted);
    margin-bottom: .15rem;
}
.ef-rd-info-val {
    font-size: .9rem;
    font-weight: 600;
    color: var(--rd-ink);
    line-height: 1.3;
}
.ef-rd-info-sub {
    font-size: .73rem;
    color: var(--rd-muted);
    margin-top: .1rem;
}
.ef-rd-amount-val {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--rd-emerald);
}
.ef-rd-rejection-box {
    background: rgba(200,75,68,.06);
    border: 1px solid rgba(200,75,68,.18);
    border-radius: 10px;
    padding: .85rem 1rem;
    margin-top: .5rem;
}

/* ── Bills ───────────────────────────────────────────────────────────── */
.ef-rd-bill-thumb {
    aspect-ratio: 1;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid var(--rd-border);
    position: relative;
    background: var(--rd-faint);
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease;
}
.ef-rd-bill-thumb:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15,123,95,.14);
}
.ef-rd-bill-size {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,.55));
    color: #fff;
    font-size: .62rem;
    padding: .4rem .5rem .3rem;
    font-weight: 500;
}
.ef-rd-bills-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    border: 2px dashed var(--rd-border-s);
    border-radius: 12px;
    background: rgba(15,123,95,.02);
    color: var(--rd-muted);
    gap: .4rem;
}
.ef-rd-bills-empty i { font-size: 1.8rem; color: rgba(15,123,95,.25); }
.ef-rd-bills-empty span { font-size: .8rem; font-weight: 500; }

/* ── Timeline ────────────────────────────────────────────────────────── */
.ef-rd-timeline { position: relative; padding-left: 1.5rem; }
.ef-rd-timeline::before {
    content: '';
    position: absolute;
    left: 7px; top: 8px; bottom: 8px;
    width: 2px;
    background: linear-gradient(to bottom, var(--rd-emerald) 0%, rgba(15,123,95,.12) 100%);
    border-radius: 2px;
}
.ef-rd-tl-item {
    position: relative;
    margin-bottom: 1.1rem;
}
.ef-rd-tl-item:last-child { margin-bottom: 0; }
.ef-rd-tl-dot {
    position: absolute;
    left: -1.5rem;
    top: 3px;
    width: 14px; height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px var(--rd-emerald), 0 2px 8px rgba(15,123,95,.25);
    background: var(--rd-emerald);
}
.ef-rd-tl-dot.dot-danger {
    background: var(--rd-danger);
    box-shadow: 0 0 0 2px var(--rd-danger), 0 2px 8px rgba(200,75,68,.25);
}
.ef-rd-tl-dot.dot-warning {
    background: var(--rd-warning);
    box-shadow: 0 0 0 2px var(--rd-warning), 0 2px 8px rgba(216,154,61,.25);
}
.ef-rd-tl-dot.dot-gold {
    background: var(--rd-gold);
    box-shadow: 0 0 0 2px var(--rd-gold), 0 2px 8px rgba(184,137,62,.3);
}
.ef-rd-tl-dot.dot-muted {
    background: var(--rd-muted);
    box-shadow: 0 0 0 2px var(--rd-muted), 0 2px 8px rgba(110,106,100,.2);
}
.ef-rd-tl-event {
    font-size: .85rem;
    font-weight: 600;
    color: var(--rd-ink);
    margin-bottom: .1rem;
}
.ef-rd-tl-time {
    font-size: .72rem;
    color: var(--rd-muted);
    line-height: 1.5;
}

/* ── Action panel ────────────────────────────────────────────────────── */
.ef-rd-action-card {
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 1.25rem;
    border: 1px solid;
}
.ef-rd-action-card.ac-warning {
    border-color: rgba(216,154,61,.28);
    background: rgba(216,154,61,.04);
}
.ef-rd-action-card.ac-info {
    border-color: rgba(47,111,237,.2);
    background: rgba(47,111,237,.03);
}
.ef-rd-action-card.ac-gold {
    border-color: rgba(184,137,62,.25);
    background: rgba(184,137,62,.04);
}
.ef-rd-action-card.ac-muted {
    border-color: rgba(110,106,100,.18);
    background: rgba(110,106,100,.03);
}
.ef-rd-action-header {
    padding: .65rem 1rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: .4rem;
    border-bottom: 1px solid;
}
.ac-warning .ef-rd-action-header { color: #7D5218; border-color: rgba(216,154,61,.2); background: rgba(216,154,61,.08); }
.ac-info    .ef-rd-action-header { color: #1E4DB7; border-color: rgba(47,111,237,.15); background: rgba(47,111,237,.06); }
.ac-gold    .ef-rd-action-header { color: #6B4A12; border-color: rgba(184,137,62,.18); background: rgba(184,137,62,.07); }
.ac-muted   .ef-rd-action-header { color: #6E6A64; border-color: rgba(110,106,100,.12); background: rgba(110,106,100,.05); }
.ef-rd-action-body { padding: .85rem 1rem; display: grid; gap: .5rem; }

/* ── Premium btn variants ─────────────────────────────────────────────── */
.btn-rd-success {
    background: linear-gradient(135deg, #0f7b5f 0%, #0d9e78 100%);
    border: none;
    color: #fff;
    font-weight: 600;
    font-size: .85rem;
    box-shadow: 0 2px 10px rgba(15,123,95,.25);
    transition: box-shadow .2s ease, transform .15s ease;
}
.btn-rd-success:hover { color:#fff; box-shadow: 0 4px 18px rgba(15,123,95,.35); transform: translateY(-1px); }
.btn-rd-danger {
    background: linear-gradient(135deg, #c84b44 0%, #e05c54 100%);
    border: none; color: #fff; font-weight: 600; font-size: .85rem;
    box-shadow: 0 2px 10px rgba(200,75,68,.25);
    transition: box-shadow .2s ease, transform .15s ease;
}
.btn-rd-danger:hover { color:#fff; box-shadow: 0 4px 18px rgba(200,75,68,.35); transform: translateY(-1px); }
.btn-rd-gold {
    background: linear-gradient(135deg, #b8893e 0%, #d6a84e 100%);
    border: none; color: #fff; font-weight: 600; font-size: .85rem;
    box-shadow: 0 2px 10px rgba(184,137,62,.25);
    transition: box-shadow .2s ease, transform .15s ease;
}
.btn-rd-gold:hover { color:#fff; box-shadow: 0 4px 18px rgba(184,137,62,.35); transform: translateY(-1px); }
.btn-rd-outline {
    background: transparent;
    border: 1px solid var(--rd-border-s);
    color: var(--rd-emerald-dk);
    font-weight: 600; font-size: .85rem;
    transition: background .18s ease, border-color .18s ease;
}
.btn-rd-outline:hover { background: rgba(15,123,95,.06); border-color: var(--rd-emerald); color: var(--rd-emerald-dk); }

/* ── Modal premium tweaks ───────────────────────────────────────────── */
.ef-rd-modal .modal-content {
    border-radius: 16px;
    border: 1px solid var(--rd-border-s);
    box-shadow: 0 20px 60px rgba(0,0,0,.14), 0 4px 20px rgba(0,0,0,.08);
    overflow: hidden;
}
.ef-rd-modal .modal-header {
    background: linear-gradient(90deg, rgba(15,123,95,.05), transparent);
    border-bottom: 1px solid var(--rd-border);
    padding: 1.1rem 1.4rem .9rem;
}
.ef-rd-modal .modal-title { font-size: .95rem; font-weight: 700; color: var(--rd-ink); }
.ef-rd-modal .modal-footer {
    border-top: 1px solid var(--rd-border);
    background: rgba(15,123,95,.02);
    padding: .85rem 1.25rem;
}
.ef-rd-modal .modal-body { padding: 1.25rem 1.4rem; }
.ef-rd-modal .form-label { font-size: .8rem; font-weight: 600; color: var(--rd-ink); margin-bottom: .3rem; }
.ef-rd-modal .form-control,
.ef-rd-modal .form-select {
    border-color: var(--rd-border-s);
    border-radius: 8px;
    font-size: .88rem;
}
.ef-rd-modal .form-control:focus,
.ef-rd-modal .form-select:focus {
    border-color: var(--rd-emerald);
    box-shadow: 0 0 0 3px rgba(15,123,95,.12);
}
</style>

{{-- ── HERO ──────────────────────────────────────────────────────────── --}}
<div class="ef-rd-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1">
        <div>
            <p class="ef-rd-hero-eyebrow">
                <a href="{{ route($indexRoute) }}" class="text-decoration-none" style="color:inherit">
                    <i class="bi bi-arrow-left me-1"></i>
                </a>
                Request #{{ $expenseRequest->id }}
            </p>
            <h2 class="ef-rd-hero-title">{{ $expenseRequest->title }}</h2>
            <p class="ef-rd-hero-meta mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                {{ $expenseRequest->created_at->format('d M Y, h:i A') }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="ef-rd-chip" style="background:{{ $chip['bg'] }};color:{{ $chip['color'] }}">
                <span class="ef-rd-chip-dot" style="background:{{ $chip['dot'] }}"></span>
                {{ $chip['label'] }}
            </span>
            <span class="ef-rd-chip" style="background:{{ $pri['bg'] }};color:{{ $pri['color'] }}">
                <i class="bi {{ $pri['icon'] }}" style="font-size:.65rem"></i>
                {{ ucfirst($expenseRequest->priority) }}
            </span>
        </div>
    </div>

    <div class="ef-rd-kpi-strip">
        <div class="ef-rd-kpi-item">
            <div class="ef-rd-kpi-label">Amount</div>
            <div class="ef-rd-kpi-val amount">₹{{ number_format($expenseRequest->amount, 2) }}</div>
        </div>
        <div class="ef-rd-kpi-item">
            <div class="ef-rd-kpi-label">Category</div>
            <div class="ef-rd-kpi-val">{{ $expenseRequest->category?->name ?? '—' }}</div>
        </div>
        <div class="ef-rd-kpi-item">
            <div class="ef-rd-kpi-label">Vendor</div>
            <div class="ef-rd-kpi-val">{{ $expenseRequest->vendor?->name ?? '—' }}</div>
        </div>
        <div class="ef-rd-kpi-item">
            <div class="ef-rd-kpi-label">Requested By</div>
            <div class="ef-rd-kpi-val">{{ $expenseRequest->requester?->name ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- ── MAIN CONTENT ─────────────────────────────────────────────────── --}}
<div class="row g-4">
    {{-- Left column ──────────────────────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Request Details Card --}}
        <div class="ef-rd-card">
            <div class="ef-rd-card-header">
                <i class="bi bi-file-text"></i> Request Details
            </div>
            <div class="ef-rd-card-body">
                <div class="ef-rd-info-row">
                    <div class="ef-rd-icon-box"><i class="bi bi-person"></i></div>
                    <div>
                        <div class="ef-rd-info-label">Requested By</div>
                        <div class="ef-rd-info-val">{{ $expenseRequest->requester?->name ?? '—' }}</div>
                        @if($expenseRequest->requester?->email)
                            <div class="ef-rd-info-sub">{{ $expenseRequest->requester->email }}</div>
                        @endif
                    </div>
                </div>
                <div class="ef-rd-info-row">
                    <div class="ef-rd-icon-box"><i class="bi bi-tag"></i></div>
                    <div>
                        <div class="ef-rd-info-label">Category</div>
                        <div class="ef-rd-info-val">{{ $expenseRequest->category?->name ?? '—' }}</div>
                    </div>
                </div>
                <div class="ef-rd-info-row">
                    <div class="ef-rd-icon-box" style="background:rgba(15,123,95,.12)"><i class="bi bi-currency-rupee"></i></div>
                    <div>
                        <div class="ef-rd-info-label">Amount</div>
                        <div class="ef-rd-amount-val">₹{{ number_format($expenseRequest->amount, 2) }}</div>
                    </div>
                </div>
                <div class="ef-rd-info-row">
                    <div class="ef-rd-icon-box"><i class="bi bi-shop"></i></div>
                    <div>
                        <div class="ef-rd-info-label">Vendor</div>
                        <div class="ef-rd-info-val">{{ $expenseRequest->vendor?->name ?? '—' }}</div>
                    </div>
                </div>
                @if($expenseRequest->settlement_type)
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box"><i class="bi bi-arrow-left-right"></i></div>
                        <div>
                            <div class="ef-rd-info-label">Settlement Type</div>
                            <div class="ef-rd-info-val">{{ ucwords(str_replace('_', ' ', $expenseRequest->settlement_type)) }}</div>
                        </div>
                    </div>
                @endif
                @if($expenseRequest->notes)
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box"><i class="bi bi-chat-text"></i></div>
                        <div>
                            <div class="ef-rd-info-label">Notes</div>
                            <div class="ef-rd-info-val" style="font-weight:400;color:#3d3a34">{{ $expenseRequest->notes }}</div>
                        </div>
                    </div>
                @endif
                @if($expenseRequest->isRejected() && $expenseRequest->rejection_reason)
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box" style="background:rgba(200,75,68,.09);color:var(--rd-danger)">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div style="flex:1">
                            <div class="ef-rd-info-label" style="color:var(--rd-danger)">Rejection Reason</div>
                            <div class="ef-rd-rejection-box mt-1">
                                <span style="font-size:.88rem;color:#7D2020">{{ $expenseRequest->rejection_reason }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payment Details (admin only, when exists) --}}
        @if($isAdmin && $expenseRequest->payment)
            @php
                $p          = $expenseRequest->payment;
                $modeLabels = \App\Models\ExpensePayment::modeLabels();
                $modeDots   = ['cash' => '#D89A3D', 'upi' => '#0F7B5F', 'bank_transfer' => '#2F6FED', 'wallet' => '#B8893E'];
                $modeDot    = $modeDots[$p->payment_mode] ?? '#9A9690';
            @endphp
            <div class="ef-rd-card">
                <div class="ef-rd-card-header">
                    <i class="bi bi-credit-card"></i> Payment Details
                </div>
                <div class="ef-rd-card-body">
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box"><i class="bi bi-credit-card-2-front"></i></div>
                        <div>
                            <div class="ef-rd-info-label">Payment Mode</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="ef-rd-chip" style="background:rgba(15,123,95,.09);color:#0A5240">
                                    <span class="ef-rd-chip-dot" style="background:{{ $modeDot }}"></span>
                                    {{ $modeLabels[$p->payment_mode] ?? $p->payment_mode }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box" style="background:rgba(15,123,95,.12);color:var(--rd-emerald)">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                        <div>
                            <div class="ef-rd-info-label">Amount Paid</div>
                            <div class="ef-rd-amount-val">₹{{ number_format($p->amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <div class="ef-rd-info-label">Paid At</div>
                            <div class="ef-rd-info-val">{{ $p->paid_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="ef-rd-info-row">
                        <div class="ef-rd-icon-box"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <div class="ef-rd-info-label">Paid By</div>
                            <div class="ef-rd-info-val">{{ $p->payer->name }}</div>
                        </div>
                    </div>
                    @if($p->transaction_reference)
                        <div class="ef-rd-info-row">
                            <div class="ef-rd-icon-box"><i class="bi bi-hash"></i></div>
                            <div>
                                <div class="ef-rd-info-label">Transaction Reference</div>
                                <div class="ef-rd-info-val font-monospace" style="font-size:.83rem">{{ $p->transaction_reference }}</div>
                            </div>
                        </div>
                    @endif
                    @if($p->payment_notes)
                        <div class="ef-rd-info-row">
                            <div class="ef-rd-icon-box"><i class="bi bi-sticky"></i></div>
                            <div>
                                <div class="ef-rd-info-label">Payment Notes</div>
                                <div class="ef-rd-info-val" style="font-weight:400;color:#3d3a34">{{ $p->payment_notes }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Bills Card --}}
        <div class="ef-rd-card">
            <div class="ef-rd-card-header">
                <i class="bi bi-paperclip"></i> Uploaded Bills
                <span class="ms-auto" style="font-size:.72rem;color:var(--rd-muted);font-weight:500;text-transform:none;letter-spacing:0">
                    {{ $expenseRequest->bills->count() }} {{ Str::plural('file', $expenseRequest->bills->count()) }}
                </span>
            </div>
            <div class="ef-rd-card-body">
                @if($expenseRequest->bills->isEmpty())
                    <div class="ef-rd-bills-empty">
                        <i class="bi bi-paperclip"></i>
                        <span>No bills uploaded for this request</span>
                    </div>
                @else
                    <div class="row g-2">
                        @foreach($expenseRequest->bills as $bill)
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="ef-rd-bill-thumb"
                                     @if($bill->isImage()) data-bs-toggle="modal" data-bs-target="#billModal{{ $bill->id }}" @endif>
                                    @if($bill->isImage())
                                        <img src="{{ $bill->url() }}" alt="{{ $bill->original_name }}"
                                             class="w-100 h-100" style="object-fit:cover">
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                            <i class="bi bi-file-earmark-pdf" style="font-size:2rem;color:var(--rd-danger)"></i>
                                            <small class="mt-1 text-center px-2" style="font-size:.68rem;color:var(--rd-muted)">
                                                {{ Str::limit($bill->original_name, 18) }}
                                            </small>
                                        </div>
                                    @endif
                                    <div class="ef-rd-bill-size">{{ $bill->humanSize() }}</div>
                                </div>
                                <a href="{{ $bill->url() }}" target="_blank"
                                   class="btn btn-sm btn-rd-outline w-100 mt-1" style="font-size:.72rem;border-radius:7px;padding:.28rem .5rem">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>

                            @if($bill->isImage())
                                <div class="modal fade ef-rd-modal" id="billModal{{ $bill->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">{{ $bill->original_name }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-2 text-center">
                                                <img src="{{ $bill->url() }}" class="img-fluid rounded" alt="{{ $bill->original_name }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column: Actions + Timeline ────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Pending: Approve / Reject --}}
        @if($expenseRequest->isPending())
            <div class="ef-rd-action-card ac-warning">
                <div class="ef-rd-action-header">
                    <i class="bi bi-clock"></i> Action Required
                </div>
                <div class="ef-rd-action-body">
                    <button class="btn btn-rd-success w-100" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-circle me-1"></i> Approve Request
                    </button>
                    <button class="btn btn-rd-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-1"></i> Reject Request
                    </button>
                </div>
            </div>
        @endif

        {{-- Approved: Settlement options (admin only) --}}
        @if($isAdmin && $expenseRequest->isApproved())
            <div class="ef-rd-action-card ac-info">
                <div class="ef-rd-action-header">
                    <i class="bi bi-cash-coin"></i> Settle Payment
                </div>
                <div class="ef-rd-action-body">
                    <button class="btn btn-rd-outline w-100" data-bs-toggle="modal" data-bs-target="#walletModal">
                        <i class="bi bi-wallet2 me-1"></i> Settle via Wallet
                    </button>
                    <button class="btn btn-rd-success w-100" data-bs-toggle="modal" data-bs-target="#directModal">
                        <i class="bi bi-cash me-1"></i> Record Direct Payment
                    </button>
                    <button class="btn btn-rd-gold w-100" data-bs-toggle="modal" data-bs-target="#reimbPendingModal">
                        <i class="bi bi-arrow-return-left me-1"></i> Mark Reimbursement Pending
                    </button>
                </div>
            </div>
        @endif

        {{-- Reimbursement pending: Reimburse (admin only) --}}
        @if($isAdmin && $expenseRequest->isReimbursementPending())
            <div class="ef-rd-action-card ac-gold">
                <div class="ef-rd-action-header">
                    <i class="bi bi-arrow-return-left"></i> Reimbursement Due
                </div>
                <div class="ef-rd-action-body">
                    <button class="btn btn-rd-gold w-100" data-bs-toggle="modal" data-bs-target="#reimburseModal">
                        <i class="bi bi-cash-coin me-1"></i> Record Reimbursement
                    </button>
                </div>
            </div>
        @endif

        {{-- Paid or Reimbursed: Mark completed (admin only) --}}
        @if($isAdmin && ($expenseRequest->isPaid() || $expenseRequest->isReimbursed()))
            <div class="ef-rd-action-card ac-muted">
                <div class="ef-rd-action-body">
                    <button class="btn btn-rd-outline w-100" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="bi bi-check2-all me-1"></i> Mark as Completed
                    </button>
                </div>
            </div>
        @endif

        {{-- Timeline Card --}}
        <div class="ef-rd-card">
            <div class="ef-rd-card-header">
                <i class="bi bi-clock-history"></i> Timeline
            </div>
            <div class="ef-rd-card-body">
                <div class="ef-rd-timeline">

                    <div class="ef-rd-tl-item">
                        <div class="ef-rd-tl-dot"></div>
                        <div class="ef-rd-tl-event">Submitted</div>
                        <div class="ef-rd-tl-time">
                            {{ $expenseRequest->created_at->format('d M Y, h:i A') }}<br>
                            by {{ $expenseRequest->requester?->name ?? '—' }}
                        </div>
                    </div>

                    @if($expenseRequest->approved_at)
                        <div class="ef-rd-tl-item">
                            <div class="ef-rd-tl-dot {{ $expenseRequest->isRejected() ? 'dot-danger' : '' }}"></div>
                            <div class="ef-rd-tl-event">{{ $expenseRequest->isRejected() ? 'Rejected' : 'Approved' }}</div>
                            <div class="ef-rd-tl-time">
                                {{ $expenseRequest->approved_at->format('d M Y, h:i A') }}<br>
                                by {{ $expenseRequest->approver?->name ?? '—' }}
                            </div>
                        </div>
                    @endif

                    @if($expenseRequest->isReimbursementPending() || $expenseRequest->isReimbursed())
                        <div class="ef-rd-tl-item">
                            <div class="ef-rd-tl-dot dot-gold"></div>
                            <div class="ef-rd-tl-event">Reimbursement Pending</div>
                            <div class="ef-rd-tl-time">{{ $expenseRequest->updated_at->format('d M Y, h:i A') }}</div>
                        </div>
                    @endif

                    @if($expenseRequest->isPaid() || $expenseRequest->isReimbursed() || $expenseRequest->isCompleted())
                        <div class="ef-rd-tl-item">
                            <div class="ef-rd-tl-dot"></div>
                            <div class="ef-rd-tl-event">
                                @if($expenseRequest->isReimbursed()) Reimbursed
                                @else Paid
                                @endif
                            </div>
                            @if($expenseRequest->payment)
                                <div class="ef-rd-tl-time">{{ $expenseRequest->payment->paid_at->format('d M Y, h:i A') }}</div>
                            @endif
                        </div>
                    @endif

                    @if($expenseRequest->isCompleted())
                        <div class="ef-rd-tl-item">
                            <div class="ef-rd-tl-dot dot-muted"></div>
                            <div class="ef-rd-tl-event">Completed</div>
                            <div class="ef-rd-tl-time">{{ $expenseRequest->updated_at->format('d M Y, h:i A') }}</div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Delete button (admin only) --}}
        @if($isAdmin)
            <button type="button" class="btn btn-rd-danger w-100" style="font-size:.8rem"
                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i> Delete Request
            </button>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MODALS (shared: approve, reject — admin-only: the rest)           --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}

{{-- Approve Modal --}}
<div class="modal fade ef-rd-modal" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2" style="color:var(--rd-emerald)"></i>Approve Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Approve <strong>{{ $expenseRequest->title }}</strong> for
                    <strong style="color:var(--rd-emerald)">₹{{ number_format($expenseRequest->amount, 2) }}</strong>?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route($routePrefix . '.expense-requests.approve', $expenseRequest) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-rd-success">Confirm Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade ef-rd-modal" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2" style="color:var(--rd-danger)"></i>Reject Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.expense-requests.reject', $expenseRequest) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p class="mb-3">Provide a reason for rejecting <strong>{{ $expenseRequest->title }}</strong>.</p>
                    <textarea name="rejection_reason"
                              class="form-control @error('rejection_reason') is-invalid @enderror"
                              rows="3" placeholder="Enter reason (required)…" required minlength="5"></textarea>
                    @error('rejection_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rd-danger">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($isAdmin)
    {{-- Settle via Wallet Modal --}}
    <div class="modal fade ef-rd-modal" id="walletModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-wallet2 me-2" style="color:var(--rd-info)"></i>Settle via Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 rounded-3 mb-3" style="background:rgba(47,111,237,.07);border:1px solid rgba(47,111,237,.15)">
                        <p class="mb-1 small" style="color:#1E4DB7;font-weight:600">
                            <i class="bi bi-info-circle me-1"></i> Wallet Deduction
                        </p>
                        <p class="mb-0 small" style="color:#3d5a99">
                            <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong> will be deducted from
                            <strong>{{ $expenseRequest->requester?->name ?? '—' }}'s</strong> wallet.
                        </p>
                    </div>
                    <p class="text-muted small mb-0">Request will be marked as <strong>paid</strong> and a wallet debit transaction recorded.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.settle-wallet', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-rd-outline" style="border-color:rgba(47,111,237,.35);color:#1E4DB7">
                            Confirm Wallet Deduction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Record Direct Payment Modal --}}
    <div class="modal fade ef-rd-modal" id="directModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.expense-requests.settle-direct', $expenseRequest) }}">
                    @csrf @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-cash me-2" style="color:var(--rd-emerald)"></i>Record Direct Payment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="">Select mode…</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control"
                                   value="{{ $expenseRequest->amount }}" min="0.01" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction Reference</label>
                            <input type="text" name="transaction_reference" class="form-control" placeholder="UTR / cheque / ref no.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Paid At <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="paid_at" class="form-control"
                                   value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Payment Notes</label>
                            <textarea name="payment_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rd-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Mark Reimbursement Pending Modal --}}
    <div class="modal fade ef-rd-modal" id="reimbPendingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-return-left me-2" style="color:var(--rd-gold)"></i>Mark Reimbursement Pending
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Mark <strong>{{ $expenseRequest->title }}</strong> as reimbursement pending?</p>
                    <p class="text-muted small mb-0">
                        Employee paid out-of-pocket and is awaiting reimbursement of
                        <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong>.
                    </p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.reimbursement-pending', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-rd-gold">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mark as Completed Modal --}}
    <div class="modal fade ef-rd-modal" id="completeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check2-all me-2" style="color:var(--rd-muted)"></i>Mark as Completed
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Mark <strong>{{ $expenseRequest->title }}</strong> as completed?</p>
                    <div class="p-3 rounded-3" style="background:rgba(110,106,100,.07);border:1px solid rgba(110,106,100,.14)">
                        <span class="small" style="color:#6E6A64">
                            Amount: <strong style="color:var(--rd-ink)">₹{{ number_format($expenseRequest->amount, 2) }}</strong>
                            &nbsp;·&nbsp;
                            Requester: <strong style="color:var(--rd-ink)">{{ $expenseRequest->requester?->name ?? '—' }}</strong>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.mark-completed', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-rd-outline" style="border-color:rgba(110,106,100,.3);color:#6E6A64">
                            <i class="bi bi-check2-all me-1"></i>Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade ef-rd-modal" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-trash me-2" style="color:var(--rd-danger)"></i>Delete Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 rounded-3 mb-3" style="background:rgba(200,75,68,.07);border:1px solid rgba(200,75,68,.18)">
                        <p class="mb-0 small" style="color:#9B2C2C;font-weight:600">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            This action cannot be undone.
                        </p>
                    </div>
                    <p class="mb-0">Permanently delete <strong>{{ $expenseRequest->title }}</strong>
                        (₹{{ number_format($expenseRequest->amount, 2) }})?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.destroy', $expenseRequest) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-rd-danger">
                            <i class="bi bi-trash me-1"></i>Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Reimburse Modal --}}
    <div class="modal fade ef-rd-modal" id="reimburseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.expense-requests.reimburse', $expenseRequest) }}">
                    @csrf @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-cash-coin me-2" style="color:var(--rd-gold)"></i>Record Reimbursement
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="p-3 rounded-3 mb-3" style="background:rgba(184,137,62,.07);border:1px solid rgba(184,137,62,.2)">
                            <p class="mb-0 small" style="color:#6B4A12">
                                <i class="bi bi-info-circle me-1"></i>
                                Reimbursing <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong> to
                                <strong>{{ $expenseRequest->requester?->name ?? '—' }}</strong>.
                                This will also credit their wallet.
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="">Select mode…</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control"
                                   value="{{ $expenseRequest->amount }}" min="0.01" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction Reference</label>
                            <input type="text" name="transaction_reference" class="form-control" placeholder="UTR / ref no.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Paid At <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="paid_at" class="form-control"
                                   value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea name="payment_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rd-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rd-gold">Record Reimbursement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
