<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Unpaid Payments Summary</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 9px;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            color: #1a3a5c;
            margin: 0;
        }
        .header p {
            margin: 3px 0;
            font-size: 9px;
            color: #6b7280;
        }
        .hostel-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .hostel-title {
            background: #1a3a5c;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        th {
            background: #f3f4f6;
            color: #1f2937;
            font-weight: 600;
            font-size: 7px;
            text-transform: uppercase;
            padding: 4px 5px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        td {
            padding: 3px 5px;
            border: 1px solid #d1d5db;
            font-size: 7px;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .subtotal-row {
            background: #e8eaf6 !important;
            font-weight: bold;
        }
        .subtotal-row td {
            border-top: 2px solid #1a3a5c;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
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
        .grand-total {
            background: #1a3a5c;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .grand-total .total-amount {
            font-size: 16px;
            color: #fcd34d;
        }
        .remark-cell {
            max-width: 100px;
            word-wrap: break-word;
            font-size: 6px;
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
            <strong>Total Due:</strong> {{ number_format($totalOverall, 2) }}
        </p>
        @if($filters['hostel'] != 'All')
            <p><strong>Hostel:</strong> {{ $filters['hostel'] }}</p>
        @endif
    </div>

    @if(count($hostelData) > 0)
        @php $grandTotal = 0; @endphp
        @foreach($hostelData as $hostelName => $data)
            <div class="hostel-section">
                <div class="hostel-title">🏢 {{ $hostelName }}</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:25px;">#</th>
                            <th style="width:20%;">Name</th>
                            <th style="width:10%;">Room</th>
                            <th style="width:13%;">Phone</th>
                            <th style="width:10%;" class="text-right">Rent ()</th>
                            <th style="width:14%;" class="text-right">Previous Pending ()</th>
                            <th style="width:14%;" class="text-right">Current Balance ()</th>
                            <th style="width:14%;" class="text-right">Total Due ()</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serialNo = 1; $hostelTotal = 0; @endphp
                        @foreach($data['residents'] as $resident)
                            @php $hostelTotal += $resident['total_due']; @endphp
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
                                    <strong>{{ number_format($resident['total_due'], 2) }}</strong>
                                </td>
                            </tr>
                            @php $serialNo++; @endphp
                        @endforeach
                        <tr class="subtotal-row">
                            <td colspan="7" class="text-right"><strong>SUBTOTAL</strong></td>
                            <td class="text-right amount due">
                                <strong>{{ number_format($hostelTotal, 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @php $grandTotal += $hostelTotal; @endphp
        @endforeach

        <div class="grand-total">
            GRAND TOTAL DUE: <span class="total-amount">{{ number_format($grandTotal, 2) }}</span>
        </div>
    @else
        <div style="text-align:center; padding:30px; color:#6b7280;">
            <p style="font-size:12px;">✅ No unpaid payments found for the selected period.</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated by: {{ $user->name ?? 'System' }} | Hostel Management System</p>
    </div>
</body>
</html>