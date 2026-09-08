<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Status Summary</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 8px;
            padding: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 16px;
            color: #1a3a5c;
            margin: 0;
        }
        .header p {
            margin: 2px 0;
            font-size: 8px;
            color: #6b7280;
        }
        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .section-title {
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 4px;
        }
        .section-title.pending { background: #dc2626; color: white; }
        .section-title.partial { background: #f59e0b; color: white; }
        .section-title.unpaid { background: #6b7280; color: white; }
        .section-title.paid { background: #22c55e; color: white; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            font-size: 7px;
        }
        th {
            background: #f3f4f6;
            color: #1f2937;
            font-weight: 600;
            font-size: 6.5px;
            text-transform: uppercase;
            padding: 3px 4px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        td {
            padding: 2px 4px;
            border: 1px solid #d1d5db;
            font-size: 6.5px;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .amount { font-weight: 600; }
        .amount.due { color: #dc2626; }
        .amount.clear { color: #22c55e; }
        .summary-box {
            background: #f8fafc;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
        }
        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .summary-item { font-size: 8px; }
        .summary-item .number { font-weight: bold; font-size: 11px; }
        .summary-item .label { color: #666; }
        .text-danger { color: #dc2626; }
        .text-warning { color: #f59e0b; }
        .text-muted { color: #6b7280; }
        .text-success { color: #22c55e; }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 6px;
            font-weight: 600;
        }
        .badge-pending { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-no-payment { background: #e5e7eb; color: #4b5563; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Payment Status Summary</h1>
        <p>
            <strong>Month:</strong> {{ $month }} {{ $year }} &nbsp;|&nbsp;
            <strong>Generated:</strong> {{ $generated_at }} &nbsp;|&nbsp;
            <strong>Hostel:</strong> {{ $filters['hostel'] }}
        </p>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="number text-danger">{{ count($pendingData) }}</div>
                <div class="label">🔴 Pending</div>
            </div>
            <div class="summary-item">
                <div class="number text-warning">{{ count($partialData) }}</div>
                <div class="label">🟡 Partial</div>
            </div>
            <div class="summary-item">
                <div class="number text-muted">{{ count($unpaidData) }}</div>
                <div class="label">⬜ Unpaid</div>
            </div>
            <div class="summary-item">
                <div class="number text-success">{{ count($paidData) }}</div>
                <div class="label">✅ Paid</div>
            </div>
        </div>
    </div>

    <!-- PENDING SECTION -->
    @if(count($pendingData) > 0)
    <div class="section">
        <div class="section-title pending">🔴 PENDING PAYMENTS (Previous Balance + Current Balance)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Phone</th>
                    <th class="text-right">Rent</th>
                    <th class="text-right">Prev Pending</th>
                    <th class="text-right">Current Bal</th>
                    <th class="text-right">Total Due</th>
                    <th>Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @php $serialNo = 1; @endphp
                @foreach($pendingData as $resident)
                    <tr>
                        <td class="text-center">{{ $serialNo }}</td>
                        <td><strong>{{ $resident['name'] }}</strong></td>
                        <td>{{ $resident['room_no'] }}</td>
                        <td>{{ $resident['phone'] }}</td>
                        <td class="text-right">{{ number_format($resident['rent'], 2) }}</td>
                        <td class="text-right amount due">{{ number_format($resident['previous_pending'], 2) }}</td>
                        <td class="text-right amount due">{{ number_format($resident['current_balance'], 2) }}</td>
                        <td class="text-right amount due"><strong>₹{{ number_format($resident['total_due'], 2) }}</strong></td>
                        <td><span class="badge badge-{{ strtolower($resident['status']) }}">{{ $resident['status'] }}</span></td>
                        <td>{{ $resident['remark'] }}</td>
                    </tr>
                    @php $serialNo++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- PARTIAL SECTION -->
    @if(count($partialData) > 0)
    <div class="section">
        <div class="section-title partial">🟡 PARTIAL PAYMENTS (Paid some, balance remains)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Phone</th>
                    <th class="text-right">Rent</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @php $serialNo = 1; @endphp
                @foreach($partialData as $resident)
                    <tr>
                        <td class="text-center">{{ $serialNo }}</td>
                        <td><strong>{{ $resident['name'] }}</strong></td>
                        <td>{{ $resident['room_no'] }}</td>
                        <td>{{ $resident['phone'] }}</td>
                        <td class="text-right">{{ number_format($resident['rent'], 2) }}</td>
                        <td class="text-right amount clear">{{ number_format($resident['current_paid'], 2) }}</td>
                        <td class="text-right amount due">{{ number_format($resident['total_due'], 2) }}</td>
                        <td><span class="badge badge-{{ strtolower($resident['status']) }}">{{ $resident['status'] }}</span></td>
                        <td>{{ $resident['remark'] }}</td>
                    </tr>
                    @php $serialNo++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- UNPAID SECTION -->
    @if(count($unpaidData) > 0)
    <div class="section">
        <div class="section-title unpaid">⬜ UNPAID PAYMENTS (No payment at all)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Phone</th>
                    <th class="text-right">Rent</th>
                    <th class="text-right">Prev Pending</th>
                    <th class="text-right">Total Due</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @php $serialNo = 1; @endphp
                @foreach($unpaidData as $resident)
                    <tr>
                        <td class="text-center">{{ $serialNo }}</td>
                        <td><strong>{{ $resident['name'] }}</strong></td>
                        <td>{{ $resident['room_no'] }}</td>
                        <td>{{ $resident['phone'] }}</td>
                        <td class="text-right">{{ number_format($resident['rent'], 2) }}</td>
                        <td class="text-right amount due">{{ number_format($resident['previous_pending'], 2) }}</td>
                        <td class="text-right amount due"><strong>₹{{ number_format($resident['total_due'], 2) }}</strong></td>
                        <td>{{ $resident['remark'] }}</td>
                    </tr>
                    @php $serialNo++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- PAID SECTION -->
    @if(count($paidData) > 0)
    <div class="section">
        <div class="section-title paid">✅ PAID PAYMENTS (Fully paid - balance 0)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Phone</th>
                    <th class="text-right">Rent</th>
                    <th class="text-right">Paid</th>
                    <th>Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @php $serialNo = 1; @endphp
                @foreach($paidData as $resident)
                    <tr>
                        <td class="text-center">{{ $serialNo }}</td>
                        <td><strong>{{ $resident['name'] }}</strong></td>
                        <td>{{ $resident['room_no'] }}</td>
                        <td>{{ $resident['phone'] }}</td>
                        <td class="text-right">{{ number_format($resident['rent'], 2) }}</td>
                        <td class="text-right amount clear">{{ number_format($resident['current_paid'], 2) }}</td>
                        <td><span class="badge badge-{{ strtolower($resident['status']) }}">{{ $resident['status'] }}</span></td>
                        <td>{{ $resident['remark'] }}</td>
                    </tr>
                    @php $serialNo++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Generated by: {{ $user->name ?? 'System' }} | Hostel Management System</p>
    </div>
</body>
</html>