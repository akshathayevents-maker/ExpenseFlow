<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $booking->effective_invoice_number }} &bull; Akshathay Mini Hall</title>
<style>
/* ─── Reset ─────────────────────────────────────────────────── */
* { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 12.5px; }
body {
    background: #eeeeee;
    color: #1a1a1a;
    font-family: "DejaVu Sans", sans-serif;
    line-height: 1.5;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
table, thead, tbody, tfoot, tr, th, td {
    font-family: "DejaVu Sans", sans-serif;
}

/* ─── Config panel (screen only — never rendered into the PDF) ── */
.config-wrap { width: 760px; margin: 20px auto 0; }
.config-panel {
    background: #fff;
    border: 1px solid #d8d8d8;
    border-radius: 6px;
    padding: 16px 20px;
    margin-bottom: 4px;
}
.config-title { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #444; margin-bottom: 12px; }
.config-row { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
.config-field { display: flex; flex-direction: column; gap: 4px; }
.config-field label { font-size: 10px; font-weight: 700; color: #555; }
.config-field input {
    border: 1px solid #ccc; border-radius: 4px; padding: 7px 9px;
    font-size: 12px; font-family: inherit; color: #1a1a1a;
}
.config-field.number input { width: 70px; text-align: right; }
.config-field.wide input { width: 190px; }
.config-actions { margin-left: auto; display: flex; gap: 8px; }
.config-actions button, .config-actions a {
    border-radius: 4px; padding: 8px 16px; font-size: 12px; font-weight: 700;
    cursor: pointer; text-decoration: none; display: inline-block; border: 1px solid #a8792c;
}
.btn-primary { background: #a8792c; color: #fff; }
.btn-secondary { background: #fff; color: #333; border-color: #ccc !important; }
.config-preview { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ddd; font-size: 11.5px; color: #333; }
.config-preview .row { display: flex; justify-content: space-between; padding: 2px 0; }
.config-preview .row.total { font-weight: 800; border-top: 1px solid #ccc; margin-top: 4px; padding-top: 6px; }
.config-flash { background: #eef7ee; border: 1px solid #bfe0bf; color: #2a6b2a; border-radius: 4px; padding: 8px 12px; font-size: 11.5px; margin-bottom: 8px; }
.config-errors { background: #fdeeee; border: 1px solid #e3bcbc; color: #a33; border-radius: 4px; padding: 8px 12px; font-size: 11.5px; margin-bottom: 8px; }
@media print { .config-wrap { display: none !important; } }

/* ─── Page shell ─────────────────────────────────────────────── */
.page {
    width: 760px;
    margin: 16px auto 24px;
    background: #ffffff;
    border: 1px solid #d8d8d8;
    padding: 28px 32px;
}

/* ─── Table helpers (DomPDF safe layout) ────────────────────── */
.t    { display: table; width: 100%; border-collapse: collapse; }
.td   { display: table-cell; vertical-align: top; }
.t-right { text-align: right; }

/* ─── Masthead ───────────────────────────────────────────────── */
.masthead { display: table; width: 100%; padding-bottom: 14px; border-bottom: 2px solid #1a1a1a; }
.masthead-brand { display: table-cell; vertical-align: top; width: 60%; }
.masthead-doc    { display: table-cell; vertical-align: top; width: 40%; text-align: right; }
.brand-name { font-size: 18px; font-weight: 800; letter-spacing: -0.01em; color: #1a1a1a; }
.brand-tagline { font-size: 9px; color: #a8792c; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; margin-top: 2px; }
.brand-addr { font-size: 9.5px; color: #444; line-height: 1.65; margin-top: 8px; }
.doc-title { font-size: 15px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #1a1a1a; }
.doc-sub { font-size: 9px; color: #777; letter-spacing: .06em; text-transform: uppercase; margin-top: 2px; }

/* ─── Meta grid: invoice no/date + booking ref/event date ─────── */
.meta-grid { display: table; width: 100%; margin-top: 16px; }
.meta-col { display: table-cell; width: 50%; vertical-align: top; }
.meta-row { margin-bottom: 4px; font-size: 10.5px; }
.meta-label { color: #777; display: inline-block; min-width: 118px; }
.meta-value { color: #1a1a1a; font-weight: 700; }

/* ─── Bill To ────────────────────────────────────────────────── */
.bill-to { margin-top: 18px; padding-top: 14px; border-top: 1px solid #ddd; }
.bill-to-label { font-size: 9px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #777; margin-bottom: 5px; }
.bill-to-name { font-size: 12.5px; font-weight: 800; color: #1a1a1a; }
.bill-to-sub { font-size: 10px; color: #555; margin-top: 2px; }

/* ─── Section title ──────────────────────────────────────────── */
.sec { margin-top: 20px; }
.sec-title { font-size: 9px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #777; border-bottom: 1px solid #ddd; padding-bottom: 6px; margin-bottom: 10px; }

/* ─── Line items table ───────────────────────────────────────── */
.items-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
.items-table thead th {
    background: #1a1a1a; color: #fff; font-size: 9px; font-weight: 800;
    letter-spacing: .1em; text-transform: uppercase; padding: 8px 10px; text-align: left;
}
.items-table thead th.t-right { text-align: right; }
.items-table tbody td { border-bottom: 1px solid #e5e5e5; font-size: 10.5px; padding: 9px 10px; vertical-align: top; }
.items-table tbody td.t-right { text-align: right; font-weight: 700; white-space: nowrap; }
.item-name { font-weight: 700; color: #1a1a1a; }
.item-sub  { color: #777; font-size: 9px; margin-top: 2px; }

.totals-table { width: 100%; border-collapse: collapse; margin-top: 0; }
.totals-table td { font-size: 10.5px; padding: 5px 10px; }
.totals-table td.t-right { text-align: right; white-space: nowrap; }
.totals-table tr.subtotal td { border-top: 1px solid #ccc; padding-top: 8px; }
.totals-table tr.grand td { border-top: 2px solid #1a1a1a; font-size: 12.5px; font-weight: 800; padding-top: 9px; }
.totals-table tr.advance td { color: #555; }
.totals-table tr.balance td { font-weight: 800; }
.totals-table tr.balance.settled td { color: #2a6b2a; }

/* ─── Payment details ────────────────────────────────────────── */
.pay-details-grid { display: table; width: 100%; }
.pay-details-row { display: table-row; }
.pay-details-key { display: table-cell; color: #777; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; width: 150px; padding: 3px 0; }
.pay-details-val { display: table-cell; color: #1a1a1a; font-size: 10.5px; font-weight: 700; padding: 3px 0; }

/* ─── Notes / terms ──────────────────────────────────────────── */
.notes-box { border: 1px solid #ddd; border-left: 3px solid #a8792c; padding: 10px 14px; font-size: 10px; color: #333; line-height: 1.6; }
.terms-grid { display: table; width: 100%; }
.terms-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 18px; }
.terms-col:last-child { padding-right: 0; padding-left: 18px; }
.terms-item { font-size: 9px; color: #555; line-height: 1.6; margin-bottom: 6px; }
.terms-item strong { color: #1a1a1a; }
.meal-tag { display: inline-block; border: 1px solid #ddd; border-radius: 3px; font-size: 8.5px; font-weight: 700; color: #555; padding: 2px 7px; margin: 0 4px 4px 0; text-transform: uppercase; }
.no-meal { color: #999; font-size: 9.5px; font-style: italic; }

/* ─── Signatures ─────────────────────────────────────────────── */
.sig-row { display: table; width: 100%; margin-top: 34px; }
.sig-cell { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
.sig-space { height: 36px; border-bottom: 1px solid #999; margin-bottom: 6px; }
.sig-name { font-size: 10px; font-weight: 700; color: #1a1a1a; }
.sig-role { font-size: 8.5px; color: #777; text-transform: uppercase; letter-spacing: .08em; margin-top: 2px; }

/* ─── Footer ─────────────────────────────────────────────────── */
.inv-footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #ddd; text-align: center; }
.inv-footer .brand { font-size: 10px; font-weight: 800; color: #1a1a1a; }
.inv-footer .contact { font-size: 8.5px; color: #888; margin-top: 2px; line-height: 1.6; }
.inv-footer .stamp { font-size: 8px; color: #aaa; margin-top: 8px; }

@media print {
    body { background: #fff; }
    .page { border: 0; margin: 0; width: 100%; }
    @page { size: A4 portrait; margin: 10mm 12mm; }
}
</style>
</head>
<body>
@php
    $rs          = "\u{20B9}";
    $totalPaid   = $booking->total_paid;
    $eventTypes  = \App\Models\HallBooking::eventTypes();
    $eventLabel  = $eventTypes[$booking->event_type] ?? \Illuminate\Support\Str::headline($booking->event_type);
    $start       = \Carbon\Carbon::parse($booking->start_time)->format('h:i A');
    $end         = \Carbon\Carbon::parse($booking->end_time)->format('h:i A');
    $bookingRef  = $booking->booking_reference;
    $invoiceNo   = $booking->effective_invoice_number;
    // Default is "today" (the date the invoice is first generated), never the
    // booking's creation date — those are unrelated. Once saved, the stored
    // value always wins and is never silently replaced on regeneration.
    $invoiceDate = $booking->invoice_date ?? now();
    $mealList    = collect([
        'Breakfast' => $booking->has_breakfast,
        'Lunch'     => $booking->has_lunch,
        'Dinner'    => $booking->has_dinner,
    ])->filter()->keys();
@endphp

@if($editable)
<div class="config-wrap">
    @if(session('status'))
        <div class="config-flash">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="config-errors">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
    <div class="config-panel">
        <div class="config-title">Invoice Details</div>
        <form method="POST" action="{{ route('hall.bookings.invoice.update', $booking) }}" id="invoiceConfigForm">
            @csrf
            <div class="config-row">
                <div class="config-field wide">
                    <label for="invoice_number">Invoice Number</label>
                    <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $invoiceNo) }}" maxlength="50" required>
                </div>
                <div class="config-field">
                    <label for="invoice_date">Invoice Date</label>
                    <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', \Illuminate\Support\Carbon::parse($invoiceDate)->toDateString()) }}" required>
                </div>
                <div class="config-field number">
                    <label for="cgst_rate">CGST (%)</label>
                    <input type="number" id="cgst_rate" name="cgst_rate" value="{{ old('cgst_rate', $calc['cgst_rate']) }}" min="0" max="28" step="0.01" required>
                </div>
                <div class="config-field number">
                    <label for="sgst_rate">SGST (%)</label>
                    <input type="number" id="sgst_rate" name="sgst_rate" value="{{ old('sgst_rate', $calc['sgst_rate']) }}" min="0" max="28" step="0.01" required>
                </div>
                <div class="config-actions">
                    <button type="submit" class="btn-primary">Generate Invoice</button>
                    <button type="button" class="btn-secondary" id="printBtn">Print</button>
                    <a href="{{ route('hall.bookings.invoice.pdf', $booking) }}" class="btn-secondary">Download PDF</a>
                </div>
            </div>
        </form>
        <div class="config-preview" id="livePreview" data-subtotal="{{ $calc['subtotal'] }}">
            <div class="row"><span>Subtotal</span><span id="pvSubtotal">{{ $rs }}{{ number_format($calc['subtotal'], 2) }}</span></div>
            <div class="row"><span id="pvCgstLabel">CGST @ {{ number_format($calc['cgst_rate'], 2) }}%</span><span id="pvCgst">{{ $rs }}{{ number_format($calc['cgst_amount'], 2) }}</span></div>
            <div class="row"><span id="pvSgstLabel">SGST @ {{ number_format($calc['sgst_rate'], 2) }}%</span><span id="pvSgst">{{ $rs }}{{ number_format($calc['sgst_amount'], 2) }}</span></div>
            <div class="row total"><span>Grand Total</span><span id="pvGrand">{{ $rs }}{{ number_format($calc['grand_total'], 2) }}</span></div>
        </div>
    </div>
</div>
@endif

<div class="page">

    {{-- ══════════════════════════════════════════ --}}
    {{-- MASTHEAD                                   --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="masthead">
        <div class="masthead-brand">
            <div class="brand-name">Akshathay Mini Hall</div>
            <div class="brand-tagline">Premium Event &amp; Banquet Hall</div>
            <div class="brand-addr">
                144 Nanjundapuram Road, Coimbatore &ndash; 641036, Tamil Nadu, India<br>
                9894594074 / 09789224440 &nbsp;&bull;&nbsp; contact@akshathay.com
            </div>
        </div>
        <div class="masthead-doc">
            <div class="doc-title">Tax Invoice</div>
            <div class="doc-sub">{{ \App\Models\HallBooking::statuses()[$booking->status] ?? $booking->status }}</div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-col">
            <div class="meta-row"><span class="meta-label">Invoice No:</span> <span class="meta-value">{{ $invoiceNo }}</span></div>
            <div class="meta-row"><span class="meta-label">Invoice Date:</span> <span class="meta-value">{{ \Illuminate\Support\Carbon::parse($invoiceDate)->format('d M Y') }}</span></div>
        </div>
        <div class="meta-col">
            <div class="meta-row"><span class="meta-label">Booking Reference:</span> <span class="meta-value">{{ $bookingRef }}</span></div>
            <div class="meta-row"><span class="meta-label">Event Date:</span> <span class="meta-value">{{ $booking->booking_date->format('d M Y') }}</span></div>
        </div>
    </div>

    <div class="bill-to">
        <div class="bill-to-label">Bill To</div>
        <div class="bill-to-name">{{ $booking->customer_name }}</div>
        <div class="bill-to-sub">{{ $booking->customer_mobile }}@if($booking->customer_alt_mobile) &nbsp;/&nbsp; {{ $booking->customer_alt_mobile }}@endif</div>
        <div class="bill-to-sub">{{ $eventLabel }} &nbsp;&bull;&nbsp; {{ $booking->location_label }} &nbsp;&bull;&nbsp; {{ number_format($booking->number_of_people) }} guests &nbsp;&bull;&nbsp; {{ $start }}&ndash;{{ $end }}</div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- MEALS & CATERING (context, not billed here) --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($booking->mealPlan || $mealList->isNotEmpty())
    <div class="sec">
        <div class="sec-title">Meals &amp; Catering</div>
        <div class="t">
            <div class="td" style="width:55%;padding-right:20px">
                @if($mealList->isNotEmpty())
                    @foreach($mealList as $ml)
                        <span class="meal-tag">{{ $ml }}</span>
                    @endforeach
                @else
                    <span class="no-meal">No specific meal selections</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- LINE ITEMS                                 --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sec">
        <div class="sec-title">Charges</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:78%">Description</th>
                    <th class="t-right" style="width:22%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($calc['line_items'] as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item['label'] }}</div>
                        @if($item['description'])
                            <div class="item-sub">{{ $item['description'] }}</div>
                        @endif
                    </td>
                    <td class="t-right">{{ $rs }}{{ number_format($item['amount'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="text-align:center;color:#999">No billable items recorded.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals-table">
            <tr class="subtotal">
                <td style="width:78%">Subtotal</td>
                <td class="t-right" style="width:22%">{{ $rs }}{{ number_format($calc['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td>CGST @ {{ number_format($calc['cgst_rate'], 2) }}%</td>
                <td class="t-right">{{ $rs }}{{ number_format($calc['cgst_amount'], 2) }}</td>
            </tr>
            <tr>
                <td>SGST @ {{ number_format($calc['sgst_rate'], 2) }}%</td>
                <td class="t-right">{{ $rs }}{{ number_format($calc['sgst_amount'], 2) }}</td>
            </tr>
            <tr class="grand">
                <td>Grand Total</td>
                <td class="t-right">{{ $rs }}{{ number_format($calc['grand_total'], 2) }}</td>
            </tr>
            <tr class="advance">
                <td>Advance / Amount Received</td>
                <td class="t-right">{{ $rs }}{{ number_format($calc['amount_received'], 2) }}</td>
            </tr>
            <tr class="balance {{ $calc['balance_due'] <= 0 ? 'settled' : '' }}">
                <td>{{ $calc['balance_due'] > 0 ? 'Balance Due' : 'Fully Settled' }}</td>
                <td class="t-right">{{ $rs }}{{ number_format($calc['balance_due'], 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- TRANSACTION HISTORY                        --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($booking->payments->count())
    <div class="sec">
        <div class="sec-title">Payment History</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:22%">Date</th>
                    <th style="width:22%">Method</th>
                    <th style="width:34%">Reference</th>
                    <th class="t-right" style="width:22%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->payments->sortBy('paid_at') as $payment)
                <tr>
                    <td>{{ $payment->paid_at->format('d M Y') }}</td>
                    <td>{{ \App\Models\BookingPayment::methods()[$payment->payment_method] ?? \Illuminate\Support\Str::headline($payment->payment_method) }}</td>
                    <td>{{ $payment->reference_number ?: '—' }}</td>
                    <td class="t-right">{{ $rs }}{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- SPECIAL NOTES                               --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($booking->notes)
    <div class="sec">
        <div class="sec-title">Special Notes &amp; Requirements</div>
        <div class="notes-box">{{ $booking->notes }}</div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- PAYMENT DETAILS (balance > 0 only)         --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($calc['balance_due'] > 0)
    <div class="sec">
        <div class="sec-title">Payment Details</div>
        <div class="pay-details-grid">
            <div class="pay-details-row"><div class="pay-details-key">UPI ID</div><div class="pay-details-val">9894594074@upi</div></div>
            <div class="pay-details-row"><div class="pay-details-key">Account Name</div><div class="pay-details-val">Akshathay Mini Hall</div></div>
            <div class="pay-details-row"><div class="pay-details-key">Amount Due</div><div class="pay-details-val">{{ $rs }}{{ number_format($calc['balance_due'], 2) }}</div></div>
            <div class="pay-details-row"><div class="pay-details-key">Reference</div><div class="pay-details-val">{{ $invoiceNo }} &mdash; {{ $booking->customer_name }}</div></div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- TERMS & CONDITIONS                         --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sec">
        <div class="sec-title">Terms &amp; Conditions</div>
        <div class="terms-grid">
            <div class="terms-col">
                <div class="terms-item"><strong>Advance:</strong> Booking confirmed only after advance payment received.</div>
                <div class="terms-item"><strong>Cancellation:</strong> Cancellations within 7 days may forfeit the advance. Contact us to discuss.</div>
                <div class="terms-item"><strong>Timing:</strong> Hall access begins 1 hour before event start. Overtime charged at actuals.</div>
            </div>
            <div class="terms-col">
                <div class="terms-item"><strong>Balance:</strong> Remaining balance due on or before event day.</div>
                <div class="terms-item"><strong>Damages:</strong> Customer liable for any damage to hall property.</div>
                <div class="terms-item"><strong>Support:</strong> 9894594074 / 09789224440 &nbsp;&bull;&nbsp; contact@akshathay.com</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- SIGNATURES                                 --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sig-row">
        <div class="sig-cell">
            <div class="sig-space"></div>
            <div class="sig-name">{{ $booking->customer_name }}</div>
            <div class="sig-role">Customer Signature</div>
        </div>
        <div class="sig-cell">
            <div class="sig-space"></div>
            <div class="sig-name">Akshathay Mini Hall</div>
            <div class="sig-role">Authorized Signatory</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- FOOTER                                     --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="inv-footer">
        <div class="brand">Akshathay Mini Hall</div>
        <div class="contact">
            144 Nanjundapuram Road, Coimbatore &ndash; 641036, Tamil Nadu &nbsp;&bull;&nbsp;
            9894594074 &nbsp;&bull;&nbsp; 09789224440 &nbsp;&bull;&nbsp; contact@akshathay.com
        </div>
        <div class="stamp">{{ $invoiceNo }} &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y, h:i A') }}</div>
    </div>

</div>{{-- /page --}}

@if($editable)
<script>
(function () {
    var subtotal = parseFloat(document.getElementById('livePreview').dataset.subtotal) || 0;
    var cgstInput = document.getElementById('cgst_rate');
    var sgstInput = document.getElementById('sgst_rate');
    var rupee = function (v) { return '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    function recalc() {
        var cgstRate = parseFloat(cgstInput.value) || 0;
        var sgstRate = parseFloat(sgstInput.value) || 0;
        var cgstAmt = Math.round(subtotal * cgstRate) / 100;
        var sgstAmt = Math.round(subtotal * sgstRate) / 100;
        var grand = subtotal + cgstAmt + sgstAmt;

        document.getElementById('pvCgstLabel').textContent = 'CGST @ ' + cgstRate.toFixed(2) + '%';
        document.getElementById('pvSgstLabel').textContent = 'SGST @ ' + sgstRate.toFixed(2) + '%';
        document.getElementById('pvCgst').textContent = rupee(cgstAmt);
        document.getElementById('pvSgst').textContent = rupee(sgstAmt);
        document.getElementById('pvGrand').textContent = rupee(grand);
    }
    cgstInput.addEventListener('input', recalc);
    sgstInput.addEventListener('input', recalc);

    document.getElementById('printBtn').addEventListener('click', function () { window.print(); });
})();
</script>
@endif

<script>
if (window.location.search.indexOf('print=1') !== -1) {
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });
}
</script>
</body>
</html>
