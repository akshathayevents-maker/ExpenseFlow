<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking #{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }} • Akshathay Mini Hall • ExpenseFlow</title>
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
<style>
* { box-sizing: border-box; }
html { font-size: 13px; }
body {
    margin: 0;
    background: #f5f2ec;
    color: #171614;
    font-family: DejaVu Sans, Arial, sans-serif;
    line-height: 1.55;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.sheet {
    width: 780px;
    margin: 28px auto;
    background: #fffdfa;
    border: 1px solid #e5ded3;
}
.topline { height: 4px; background: #171614; }
.section { padding: 34px 40px; }
.header {
    display: table;
    width: 100%;
    border-bottom: 1px solid #e5ded3;
}
.brand, .doc-meta {
    display: table-cell;
    vertical-align: top;
}
.brand { width: 58%; }
.doc-meta { text-align: right; }
.hall-name {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin-bottom: 7px;
}
.hall-kind, .micro {
    color: #9c958a;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
}
.address {
    color: #5f5a52;
    font-size: 10.5px;
    line-height: 1.65;
    margin-top: 18px;
}
.doc-pill {
    border: 1px solid #d8d0c4;
    border-radius: 999px;
    color: #5f5a52;
    display: inline-block;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .14em;
    padding: 4px 10px;
    text-transform: uppercase;
}
.doc-id {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1;
    margin-top: 12px;
}
.doc-line {
    color: #777067;
    font-size: 10px;
    margin-top: 6px;
}
.summary {
    display: table;
    width: 100%;
    border-bottom: 1px solid #e5ded3;
    background: #faf8f3;
}
.summary-cell {
    display: table-cell;
    padding: 18px 40px;
    vertical-align: middle;
}
.summary-main { width: 62%; }
.summary-amount {
    text-align: right;
}
.customer {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.01em;
    margin-top: 5px;
}
.amount {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -0.02em;
}
.chips { margin-top: 14px; }
.chip {
    border: 1px solid #ded7cc;
    border-radius: 999px;
    color: #514d46;
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    margin-right: 6px;
    padding: 4px 9px;
}
.grid {
    display: table;
    width: 100%;
}
.col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
}
.col:first-child { padding-right: 28px; }
.col:last-child { padding-left: 28px; }
.title {
    border-bottom: 1px solid #e5ded3;
    color: #171614;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .15em;
    margin: 0 0 18px;
    padding-bottom: 10px;
    text-transform: uppercase;
}
.field { margin-bottom: 15px; }
.label {
    color: #9c958a;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .12em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
.value {
    color: #24221f;
    font-size: 11px;
    font-weight: 650;
}
.muted {
    color: #777067;
    font-size: 10px;
}
.meal {
    border: 1px solid #ded7cc;
    border-radius: 6px;
    display: inline-block;
    font-size: 10px;
    font-weight: 650;
    margin: 0 5px 6px 0;
    padding: 5px 9px;
}
.money-table, .txn-table {
    border-collapse: collapse;
    width: 100%;
}
.money-table td {
    border-bottom: 1px solid #eee8df;
    font-size: 11px;
    padding: 10px 0;
}
.money-table td:last-child {
    font-weight: 800;
    text-align: right;
}
.money-table .total td {
    border-bottom: 1px solid #171614;
    font-weight: 800;
    padding-top: 14px;
}
.money-table .balance td {
    border-bottom: 0;
    font-size: 13px;
    font-weight: 800;
    padding-top: 14px;
}
.txn-table th {
    border-bottom: 1px solid #ded7cc;
    color: #9c958a;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .12em;
    padding: 8px 0;
    text-align: left;
    text-transform: uppercase;
}
.txn-table th:last-child,
.txn-table td:last-child { text-align: right; }
.txn-table td {
    border-bottom: 1px solid #f0ebe3;
    color: #3d3933;
    font-size: 10.5px;
    padding: 9px 0;
}
.note {
    border-left: 2px solid #a98338;
    color: #5f5a52;
    font-size: 10.5px;
    line-height: 1.7;
    padding-left: 14px;
}
.footer {
    border-top: 1px solid #e5ded3;
    padding: 30px 40px 26px;
}
.signatures {
    display: table;
    margin-top: 34px;
    width: 100%;
}
.signature {
    display: table-cell;
    text-align: center;
    width: 50%;
}
.signature:first-child { padding-right: 30px; }
.signature:last-child { padding-left: 30px; }
.line {
    border-top: 1px solid #cfc7ba;
    padding-top: 8px;
}
.thanks {
    font-size: 13px;
    font-weight: 800;
    text-align: center;
}
.fineprint {
    border-top: 1px solid #eee8df;
    color: #aaa196;
    font-size: 8.5px;
    line-height: 1.7;
    margin-top: 28px;
    padding-top: 12px;
    text-align: center;
}
@media print {
    body { background: #fff; }
    .sheet { border: 0; margin: 0; width: 100%; }
    @page { size: A4 portrait; margin: 10mm 12mm; }
}
</style>
</head>
<body>
@php
    $totalPaid = $booking->total_paid;
    $balance = max(0, $booking->balance_amount);
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $eventLabel = $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type);
    $start = \Carbon\Carbon::parse($booking->start_time)->format('h:i A');
    $end = \Carbon\Carbon::parse($booking->end_time)->format('h:i A');
    $bookingRef = '#' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
@endphp

<main class="sheet">
    <div class="topline"></div>

    <section class="section header">
        <div class="brand">
            <div class="hall-name">Akshathay Mini Hall</div>
            <div class="hall-kind">Premium Event &amp; Banquet Hall</div>
            <div class="address">
                144 Nanjundapuram Road,<br>
                Apartment, near R.R KALIRU,<br>
                Coimbatore, Tamil Nadu 641036<br>
                9894594074 / 09789224440<br>
                contact@akshathay.com
            </div>
        </div>
        <div class="doc-meta">
            <span class="doc-pill">Booking Confirmation</span>
            <div class="doc-id">{{ $bookingRef }}</div>
            <div class="doc-line">Issued {{ now()->format('d M Y, h:i A') }}</div>
            <div class="doc-line">Event {{ $booking->booking_date->format('D, d M Y') }}</div>
            <div class="chips">
                <span class="chip">{{ \App\Models\HallBooking::statuses()[$booking->status] ?? Str::headline($booking->status) }}</span>
                <span class="chip">{{ \App\Models\HallBooking::paymentStatuses()[$booking->payment_status] ?? Str::headline($booking->payment_status) }} Payment</span>
            </div>
        </div>
    </section>

    <section class="summary">
        <div class="summary-cell summary-main">
            <div class="micro">Customer</div>
            <div class="customer">{{ $booking->customer_name }}</div>
            <div class="muted">{{ $eventLabel }} · {{ $booking->hall->name }} · {{ $start }} - {{ $end }}</div>
        </div>
        <div class="summary-cell summary-amount">
            <div class="micro">Total Booking Value</div>
            <div class="amount">₹{{ number_format($booking->total_amount, 2) }}</div>
        </div>
    </section>

    <section class="section">
        <div class="grid">
            <div class="col">
                <h2 class="title">Booking Details</h2>
                <div class="field"><div class="label">Customer Name</div><div class="value">{{ $booking->customer_name }}</div></div>
                <div class="field"><div class="label">Mobile</div><div class="value">{{ $booking->customer_mobile }}</div></div>
                @if($booking->customer_alt_mobile)
                    <div class="field"><div class="label">Alternate Mobile</div><div class="value">{{ $booking->customer_alt_mobile }}</div></div>
                @endif
                <div class="field"><div class="label">Event Type</div><div class="value">{{ $eventLabel }}</div></div>
                <div class="field"><div class="label">Guests</div><div class="value">{{ number_format($booking->number_of_people) }} guests</div></div>
            </div>
            <div class="col">
                <h2 class="title">Venue &amp; Schedule</h2>
                <div class="field"><div class="label">Hall</div><div class="value">{{ $booking->hall->name }}</div></div>
                <div class="field"><div class="label">Date</div><div class="value">{{ $booking->booking_date->format('l, d M Y') }}</div></div>
                <div class="field"><div class="label">Time</div><div class="value">{{ $start }} - {{ $end }}</div></div>
                <div class="field"><div class="label">Recorded By</div><div class="value">{{ $booking->creator?->name ?? 'System' }}</div></div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:0">
        <h2 class="title">Meal &amp; Catering</h2>
        <div class="grid">
            <div class="col">
                <div class="field"><div class="label">Meal Plan</div><div class="value">{{ $booking->mealPlan?->name ?? 'Not selected' }}</div></div>
                @if($booking->mealPlan?->price_per_person)
                    <div class="field"><div class="label">Rate Per Person</div><div class="value">₹{{ number_format($booking->mealPlan->price_per_person, 2) }}</div></div>
                @endif
            </div>
            <div class="col">
                <div class="label">Meal Selections</div>
                @if($booking->has_breakfast)<span class="meal">Breakfast</span>@endif
                @if($booking->has_lunch)<span class="meal">Lunch</span>@endif
                @if($booking->has_dinner)<span class="meal">Dinner</span>@endif
                @if(!$booking->has_breakfast && !$booking->has_lunch && !$booking->has_dinner)
                    <span class="muted">No meal selections</span>
                @endif
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:0">
        <h2 class="title">Payment Summary</h2>
        <table class="money-table">
            <tr><td>Total Booking Value</td><td>₹{{ number_format($booking->total_amount, 2) }}</td></tr>
            <tr><td>Advance Amount</td><td>₹{{ number_format($booking->advance_amount, 2) }}</td></tr>
            <tr><td>Total Received</td><td>₹{{ number_format($totalPaid, 2) }}</td></tr>
            <tr class="total"><td>Amount Payable</td><td>₹{{ number_format($booking->total_amount, 2) }}</td></tr>
            <tr class="balance"><td>{{ $balance > 0 ? 'Balance Due' : 'Balance Due — Fully Settled' }}</td><td>₹{{ number_format($balance, 2) }}</td></tr>
        </table>
    </section>

    @if($booking->payments->count())
        <section class="section" style="padding-top:0">
            <h2 class="title">Transaction History</h2>
            <table class="txn-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->payments->sortBy('paid_at') as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d M Y') }}</td>
                            <td>{{ \App\Models\BookingPayment::types()[$payment->payment_type] ?? Str::headline($payment->payment_type) }}</td>
                            <td>{{ \App\Models\BookingPayment::methods()[$payment->payment_method] ?? Str::headline($payment->payment_method) }}</td>
                            <td>{{ $payment->reference_number ?: '—' }}</td>
                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    @if($booking->notes)
        <section class="section" style="padding-top:0">
            <h2 class="title">Notes</h2>
            <div class="note">{{ $booking->notes }}</div>
        </section>
    @endif

    <footer class="footer">
        <div class="thanks">Thank you for choosing Akshathay Mini Hall</div>
        <div class="signatures">
            <div class="signature">
                <div class="line">
                    <div class="value">{{ $booking->customer_name }}</div>
                    <div class="micro">Customer Signature</div>
                </div>
            </div>
            <div class="signature">
                <div class="line">
                    <div class="value">Akshathay Mini Hall</div>
                    <div class="micro">Authorized Signatory</div>
                </div>
            </div>
        </div>
        <div class="fineprint">
            Generated {{ now()->format('d M Y, h:i:s A') }} · {{ $bookingRef }} ·
            144 Nanjundapuram Road, Coimbatore, Tamil Nadu 641036 ·
            9894594074 / 09789224440 · contact@akshathay.com
        </div>
    </footer>
</main>

<script>
if (window.location.search.indexOf('print=1') !== -1) {
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
    });
}
</script>
</body>
</html>
