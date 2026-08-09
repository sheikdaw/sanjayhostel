<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #1a237e; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #1a237e; }
        .header p { font-size: 11px; color: #666; margin-top: 5px; }
        .report-info { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 10px; }
        .report-info .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        table th { background: #1a237e; color: white; padding: 6px 4px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        table td { padding: 4px; border-bottom: 1px solid #e0e0e0; }
        table tr:nth-child(even) { background: #f8f9fa; }
        .status-paid { color: #2e7d32; font-weight: bold; }
        .status-pending { color: #c62828; font-weight: bold; }
        .status-partial { color: #e65100; font-weight: bold; }
        .status-no-payment { color: #757575; font-weight: bold; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e0e0e0; text-align: center; font-size: 9px; color: #999; }
        .summary-box { margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
        .summary-box h3 { font-size: 12px; color: #1a237e; margin-bottom: 8px; }
        .summary-grid { display: flex; flex-wrap: wrap; gap: 15px; }
        .summary-item { font-size: 10px; }
        .summary-item .number { font-weight: bold; font-size: 12px; }
        .summary-item .label { color: #666; }
        .text-success { color: #2e7d32; }
        .text-danger { color: #c62828; }
        .text-warning { color: #e65100; }
        .text-muted { color: #757575; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 7px; font-weight: bold; }
        .badge-paid { background: #c8e6c9; color: #2e7d32; }
        .badge-pending { background: #ffcdd2; color: #c62828; }
        .badge-partial { background: #ffe0b2; color: #e65100; }
        .badge-no-payment { background: #e0e0e0; color: #757575; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="report-info">
        <div><span class="label">Month:</span> {{ $month }} {{ $year }}</div>
        <div><span class="label">Hostel:</span> {{ $hostel }}</div>
        <div><span class="label">Total Residents:</span> {{ $residents->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Hostel</th>
                <th>Room No</th>
                <th>Bed No</th>
                <th>Resident Name</th>
                <th>Phone</th>
                <th>Monthly Rent (₹)</th>
                <th>Payment Status</th>
                <th>Receipt No</th>
                <th>Paid (₹)</th>
                <th>Balance (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $serialNo = 1; @endphp
            @foreach($residents as $resident)
                @php
                    $payment = $payments->get($resident->id);
                    $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
                    $bedNo = $resident->bed_no ?? 'N/A';
                    $rent = $resident->rent_amount ?? 0;
                @endphp
                <tr>
                    <td>{{ $serialNo++ }}</td>
                    <td>{{ $resident->hostel->hostel_name ?? 'N/A' }}</td>
                    <td>#{{ $roomNo }}</td>
                    <td>{{ $bedNo }}</td>
                    <td>{{ $resident->name }}</td>
                    <td>{{ $resident->phone ?? '' }}</td>
                    <td>{{ number_format($rent, 2) }}</td>
                    <td>
                        @if($payment)
                            @php
                                $statusClass = strtolower($payment->status);
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">{{ $payment->status }}</span>
                        @else
                            <span class="badge badge-no-payment">NO PAYMENT</span>
                        @endif
                    </td>
                    <td>{{ $payment ? $payment->receipt_no : '' }}</td>
                    <td>
                        @if($payment)
                            {{ number_format($payment->cash_paid_amount + $payment->upi_paid_amount, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td>
                        @if($payment)
                            {{ number_format($payment->balance_amount, 2) }}
                        @else
                            {{ number_format($rent, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-grid">
            @php
                $totalPaid = 0;
                $totalBalance = 0;
                $totalRent = 0;
                $paidCount = 0;
                $pendingCount = 0;
                $partialCount = 0;
                $noPaymentCount = 0;

                foreach($residents as $resident) {
                    $payment = $payments->get($resident->id);
                    $totalRent += $resident->rent_amount ?? 0;
                    if ($payment) {
                        $totalPaid += $payment->cash_paid_amount + $payment->upi_paid_amount;
                        $totalBalance += $payment->balance_amount;
                        if ($payment->status === 'PAID') $paidCount++;
                        elseif ($payment->status === 'PENDING') $pendingCount++;
                        elseif ($payment->status === 'PARTIAL') $partialCount++;
                    } else {
                        $noPaymentCount++;
                        $totalBalance += $resident->rent_amount ?? 0;
                    }
                }
            @endphp
            <div class="summary-item">
                <div class="number text-success">{{ $paidCount }}</div>
                <div class="label">Paid</div>
            </div>
            <div class="summary-item">
                <div class="number text-danger">{{ $pendingCount }}</div>
                <div class="label">Pending</div>
            </div>
            <div class="summary-item">
                <div class="number text-warning">{{ $partialCount }}</div>
                <div class="label">Partial</div>
            </div>
            <div class="summary-item">
                <div class="number text-muted">{{ $noPaymentCount }}</div>
                <div class="label">No Payment</div>
            </div>
            <div class="summary-item">
                <div class="number">₹{{ number_format($totalRent, 2) }}</div>
                <div class="label">Total Rent</div>
            </div>
            <div class="summary-item">
                <div class="number text-success">₹{{ number_format($totalPaid, 2) }}</div>
                <div class="label">Total Paid</div>
            </div>
            <div class="summary-item">
                <div class="number text-danger">₹{{ number_format($totalBalance, 2) }}</div>
                <div class="label">Total Balance</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Generated by: {{ $user->name }} ({{ $user->role }}) | {{ $generated_at }}
    </div>
</body>
</html>
