<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Status Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
        }
        .header p {
            font-size: 11px;
            color: #666;
            margin: 5px 0;
        }
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .number {
            font-size: 18px;
            font-weight: bold;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 5px 0;
            padding: 5px 10px;
            background: #e8e8e8;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 8px;
        }
        td {
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge-pending {
            color: #dc3545;
            font-weight: bold;
        }
        .badge-partial {
            color: #fd7e14;
            font-weight: bold;
        }
        .badge-paid {
            color: #28a745;
            font-weight: bold;
        }
        .badge-unpaid {
            color: #6c757d;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>PAYMENT STATUS SUMMARY</h1>
    <p>Report Month: {{ $month }} {{ $year }} | Generated: {{ $generated_at }}</p>
</div>

<div class="summary-box">
    <div class="summary-item">
        <div class="number" style="color:#dc3545;">{{ count($pendingData ?? []) }}</div>
        <div class="label">🔴 Pending</div>
    </div>
    <div class="summary-item">
        <div class="number" style="color:#fd7e14;">{{ count($partialData ?? []) }}</div>
        <div class="label">🟡 Partial</div>
    </div>
    <div class="summary-item">
        <div class="number" style="color:#6c757d;">{{ count($unpaidData ?? []) }}</div>
        <div class="label">⬜ Unpaid</div>
    </div>
    <div class="summary-item">
        <div class="number" style="color:#28a745;">{{ count($paidData ?? []) }}</div>
        <div class="label">✅ Paid</div>
    </div>
</div>

{{-- PENDING SECTION --}}
@if(count($pendingData ?? []) > 0)
<div class="section-title" style="background:#f8d7da; color:#721c24;">🔴 PENDING PAYMENTS ({{ count($pendingData) }})</div>
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
        @foreach($pendingData as $index => $resident)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $resident['name'] }}</td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 2) }}</td>
            <td class="text-right">₹{{ number_format($resident['previous_pending'] ?? 0, 2) }}</td>
            <td class="text-right">₹{{ number_format($resident['current_balance'] ?? 0, 2) }}</td>
            <td class="text-right">₹{{ number_format($resident['total_due'] ?? 0, 2) }}</td>
            <td><span class="badge-pending">{{ $resident['status'] }}</span></td>
            <td>{{ Str::limit($resident['remark'] ?? '', 50) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- PARTIAL SECTION --}}
@if(count($partialData ?? []) > 0)
<div class="section-title" style="background:#fff3cd; color:#856404;">🟡 PARTIAL PAYMENTS ({{ count($partialData) }})</div>
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
        @foreach($partialData as $index => $resident)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $resident['name'] }}</td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 2) }}</td>
            <td class="text-right">₹{{ number_format($resident['current_paid'] ?? 0, 2) }}</td>
            <td class="text-right">₹{{ number_format($resident['total_due'] ?? 0, 2) }}</td>
            <td><span class="badge-partial">{{ $resident['status'] }}</span></td>
            <td>{{ Str::limit($resident['remark'] ?? '', 50) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- UNPAID SECTION --}}
@if(count($unpaidData ?? []) > 0)
<div class="section-title" style="background:#e2e3e5; color:#383d41;">⬜ UNPAID PAYMENTS ({{ count($unpaidData) }})</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Room</th>
            <th>Phone</th>
            <th class="text-right">Rent</th>
            <th>Status</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($unpaidData as $index => $resident)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $resident['name'] }}</td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 2) }}</td>
            <td><span class="badge-unpaid">{{ $resident['status'] }}</span></td>
            <td>{{ Str::limit($resident['remark'] ?? 'No payment recorded', 50) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- PAID SECTION --}}
@if(count($paidData ?? []) > 0)
<div class="section-title" style="background:#d4edda; color:#155724;">✅ PAID PAYMENTS ({{ count($paidData) }})</div>
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
        @foreach($paidData as $index => $resident)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $resident['name'] }}</td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 2) }}</td>
            <td class="text-right">₹{{ number_format($resident['current_paid'] ?? 0, 2) }}</td>
            <td><span class="badge-paid">{{ $resident['status'] }}</span></td>
            <td>{{ Str::limit($resident['remark'] ?? '', 50) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    Generated on {{ $generated_at }} | Filters: Hostel - {{ $filters['hostel'] ?? 'All' }}
</div>

</body>
</html>