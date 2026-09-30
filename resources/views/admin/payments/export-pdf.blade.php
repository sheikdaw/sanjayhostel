<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            font-size: 11px;
            color: #1f2937;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0A1E3F;
        }
        .header h1 {
            font-size: 20px;
            color: #0A1E3F;
            margin-bottom: 4px;
        }
        .header .sub {
            font-size: 11px;
            color: #6b7280;
        }
        .filters {
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .filters strong { color: #0A1E3F; }

        .summary {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .summary-box {
            flex: 1;
            min-width: 100px;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .summary-box .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .summary-box .value {
            font-size: 16px;
            font-weight: 700;
            font-family: monospace;
        }
        .summary-box .amount {
            font-size: 10px;
            color: #6b7280;
            font-family: monospace;
        }
        .summary-box.green  { background: #f0fdf4; border-color: #86efac; }
        .summary-box.green .value  { color: #059669; }
        .summary-box.orange { background: #fffbeb; border-color: #fcd34d; }
        .summary-box.orange .value { color: #d97706; }
        .summary-box.gray   { background: #f9fafb; border-color: #d1d5db; }
        .summary-box.gray .value   { color: #4b5563; }
        .summary-box.red    { background: #fef2f2; border-color: #fca5a5; }
        .summary-box.red .value    { color: #dc2626; }
        .summary-box.blue   { background: #eff6ff; border-color: #93c5fd; }
        .summary-box.blue .value   { color: #2563eb; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead th {
            background: #0A1E3F;
            color: white;
            padding: 6px 5px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
        }
        tbody td {
            padding: 5px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #fafbfc; }
        tbody tr.row-pending { background: #fef2f2; }
        tbody tr.row-unpaid  { background: #f3f4f6; }
        tbody tr.row-partial { background: #fffbeb; }

        .amount { font-family: monospace; font-weight: 600; }
        .amount.paid    { color: #059669; }
        .amount.pending { color: #dc2626; }
        .amount.prev    { color: #ef4444; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge.paid    { background: #dcfce7; color: #166534; }
        .badge.partial { background: #fef3c7; color: #92400e; }
        .badge.pending { background: #fee2e2; color: #991b1b; }
        .badge.unpaid  { background: #e5e7eb; color: #4b5563; }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }

        @media print {
            body { padding: 0; }
            thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr.row-pending { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr.row-unpaid  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr.row-partial { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Payment Report</h1>
    <div class="sub">Sanjay PG Hostel</div>
</div>

<div class="filters">
    <strong>Filter:</strong> {{ $filterLabel }}
</div>

<div class="summary">
    <div class="summary-box green">
        <div class="label">✅ Fully Paid</div>
        <div class="value">{{ $stats['paid'] }}</div>
        <div class="amount">₹{{ number_format($stats['paid_amount'], 2) }}</div>
    </div>
    <div class="summary-box orange">
        <div class="label">🟡 Partial</div>
        <div class="value">{{ $stats['partial'] }}</div>
        <div class="amount">₹{{ number_format($stats['partial_amount'], 2) }}</div>
    </div>
    <div class="summary-box gray">
        <div class="label">⬜ Unpaid</div>
        <div class="value">{{ $stats['unpaid'] }}</div>
        <div class="amount">₹{{ number_format($stats['unpaid_amount'], 2) }}</div>
    </div>
    <div class="summary-box red">
        <div class="label">🔴 Pending</div>
        <div class="value">{{ $stats['pending'] }}</div>
        <div class="amount">₹{{ number_format($stats['pending_amount'], 2) }}</div>
    </div>
    <div class="summary-box blue">
        <div class="label">📊 Total</div>
        <div class="value">{{ $stats['total'] }}</div>
        <div class="amount">₹{{ number_format($stats['total_balance'], 2) }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:25px;">#</th>
            <th>Resident</th>
            <th>Phone</th>
            <th>Hostel</th>
            <th>Room/Bed</th>
            <th style="text-align:right;">Rent</th>
            <th style="text-align:right;">Discount</th>
            <th style="text-align:right;">Paid</th>
            <th style="text-align:right;">Prev. Pending</th>
            <th style="text-align:right;">Balance</th>
            <th style="text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $index => $r)
            <tr class="{{ $r['status'] === 'PENDING' ? 'row-pending' : ($r['status'] === 'UNPAID' ? 'row-unpaid' : ($r['status'] === 'PARTIAL' ? 'row-partial' : '')) }}">
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $r['resident_name'] }}</strong><br>
                    <span style="font-size:8px;color:#6b7280;">{{ $r['resident_code'] }}</span>
                </td>
                <td>{{ $r['phone'] }}</td>
                <td>{{ $r['hostel_name'] }}</td>
                <td>{{ $r['room_no'] }} / {{ $r['bed_no'] }}</td>
                <td style="text-align:right;" class="amount">₹{{ number_format($r['rent_amount'], 0) }}</td>
                <td style="text-align:right;" class="amount">₹{{ number_format($r['discount_amount'], 0) }}</td>
                <td style="text-align:right;" class="amount paid">₹{{ number_format($r['current_paid'], 0) }}</td>
                <td style="text-align:right;" class="amount prev">
                    {{ $r['previous_pending'] > 0 ? '₹' . number_format($r['previous_pending'], 0) : '—' }}
                </td>
                <td style="text-align:right;" class="amount {{ $r['total_due'] > 0 ? 'pending' : 'paid' }}">
                    ₹{{ number_format($r['total_due'], 0) }}
                </td>
                <td style="text-align:center;">
                    <span class="badge {{ strtolower($r['status']) }}">{{ $r['status'] }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align:center; padding:20px; color:#9ca3af;">
                    No records found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generated on {{ $generatedAt }} | Sanjay PG Hostel Management System
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 400);
    });
</script>

</body>
</html>
