<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Unpaid Payments Summary</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            color: #1a3a5c;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #6b7280;
        }
        .hostel-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .hostel-title {
            background: #1a3a5c;
            color: white;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        th {
            background: #f3f4f6;
            color: #1f2937;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        td {
            padding: 5px 8px;
            border: 1px solid #d1d5db;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .total-row {
            background: #e5e7eb !important;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #1a3a5c;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge-pending {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
        }
        .badge-pending.due {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-pending.clear {
            background: #dcfce7;
            color: #166534;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount {
            font-weight: 600;
        }
        .amount.due {
            color: #dc2626;
        }
        .amount.clear {
            color: #22c55e;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 Unpaid Payments Summary</h1>
        <p>
            <strong>Month:</strong> {{ $month }} {{ $year }} &nbsp;|&nbsp;
            <strong>Generated:</strong> {{ $generated_at }} &nbsp;|&nbsp;
            <strong>Total Unpaid:</strong> {{ $totalResidents }} residents &nbsp;|&nbsp;
            <strong>Total Due:</strong> ₹{{ number_format($totalOverall, 2) }}
        </p>
        @if($filters['hostel'] != 'All')
            <p><strong>Hostel:</strong> {{ $filters['hostel'] }}</p>
        @endif
    </div>

    @if(count($hostelData) > 0)
        @foreach($hostelData as $hostelName => $data)
            <div class="hostel-section">
                <div class="hostel-title">🏢 {{ $hostelName }}</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th style="width:25%;">Name</th>
                            <th style="width:12%;">Room</th>
                            <th style="width:15%;">Phone</th>
                            <th style="width:12%;" class="text-right">Rent (₹)</th>
                            <th style="width:15%;" class="text-right">Previous Pending (₹)</th>
                            <th style="width:15%;" class="text-right">Current Balance (₹)</th>
                            <th style="width:15%;" class="text-right">Total Due (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serialNo = 1; @endphp
                        @foreach($data['residents'] as $resident)
                            <tr>
                                <td class="text-center">{{ $serialNo }}</td>
                                <td><strong>{{ $resident['name'] }}</strong></td>
                                <td>{{ $resident['room_no'] }}</td>
                                <td>{{ $resident['phone'] }}</td>
                                <td class="text-right">{{ number_format($resident['rent'], 2) }}</td>
                                <td class="text-right {{ $resident['previous_pending'] > 0 ? 'amount due' : '' }}">
                                    {{ number_format($resident['previous_pending'], 2) }}
                                </td>
                                <td class="text-right {{ $resident['current_balance'] > 0 ? 'amount due' : '' }}">
                                    {{ number_format($resident['current_balance'], 2) }}
                                </td>
                                <td class="text-right amount due">
                                    <strong>₹{{ number_format($resident['total_due'], 2) }}</strong>
                                </td>
                            </tr>
                            @php $serialNo++; @endphp
                        @endforeach
                        <tr class="total-row">
                            <td colspan="7" class="text-right"><strong>TOTAL</strong></td>
                            <td class="text-right amount due">
                                <strong>₹{{ number_format(collect($data['residents'])->sum('total_due'), 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        <div style="margin-top:20px; background:#f3f4f6; padding:10px; border-radius:4px;">
            <table style="border:none; background:transparent;">
                <tr style="background:transparent;">
                    <td style="border:none; font-size:12px; font-weight:bold; color:#1a3a5c;">
                        GRAND TOTAL DUE
                    </td>
                    <td style="border:none; text-align:right; font-size:14px; font-weight:bold; color:#dc2626;">
                        ₹{{ number_format($totalOverall, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div style="text-align:center; padding:40px; color:#6b7280;">
            <p style="font-size:14px;">✅ No unpaid payments found for the selected period.</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated by Hostel Management System</p>
    </div>
</body>
</html>