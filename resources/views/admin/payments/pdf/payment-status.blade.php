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
            font-size: 18px;
            margin: 0;
            color: #1a237e;
        }
        .header p {
            font-size: 11px;
            color: #666;
            margin: 5px 0;
        }
        .header .sub-info {
            font-size: 10px;
            color: #888;
        }
        
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .summary-item {
            text-align: center;
            padding: 5px 10px;
        }
        .summary-item .number {
            font-size: 20px;
            font-weight: bold;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .summary-item .icon {
            font-size: 14px;
            display: block;
            margin-bottom: 2px;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            padding: 8px 12px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-title .count-badge {
            font-size: 11px;
            padding: 2px 10px;
            border-radius: 12px;
            background: rgba(255,255,255,0.5);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5px;
        }
        th {
            font-weight: bold;
            padding: 5px 6px;
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
        .text-left {
            text-align: left;
        }
        
        /* Status Badges */
        .badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 7px;
            display: inline-block;
        }
        .badge-pending {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-partial {
            background: #fff3cd;
            color: #856404;
        }
        .badge-paid {
            background: #d4edda;
            color: #155724;
        }
        .badge-unpaid {
            background: #e2e3e5;
            color: #383d41;
        }
        
        /* Section Colors */
        .section-pending { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .section-partial { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .section-unpaid { background: #e9ecef; color: #495057; border-left: 4px solid #6c757d; }
        .section-paid { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        
        .footer {
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 20px;
        }
        
        .highlight-red { color: #dc3545; font-weight: bold; }
        .highlight-orange { color: #fd7e14; font-weight: bold; }
        .highlight-green { color: #28a745; font-weight: bold; }
        .highlight-gray { color: #6c757d; font-weight: bold; }
        
        .amount-positive { color: #28a745; }
        .amount-negative { color: #dc3545; }
        .amount-warning { color: #fd7e14; }
        
        .striped-row:nth-child(even) {
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>📊 PAYMENT STATUS SUMMARY</h1>
    <p><strong>Report Month:</strong> {{ $month }} {{ $year }} | <strong>Generated:</strong> {{ $generated_at }}</p>
    <p class="sub-info">Filters: Hostel - {{ $filters['hostel'] ?? 'All' }}</p>
</div>

{{-- Summary Box with Icons --}}
<div class="summary-box">
    <div class="summary-item">
        <span class="icon">🔴</span>
        <div class="number" style="color:#dc3545;">{{ count($pendingData ?? []) }}</div>
        <div class="label">Pending</div>
    </div>
    <div class="summary-item">
        <span class="icon">🟡</span>
        <div class="number" style="color:#fd7e14;">{{ count($partialData ?? []) }}</div>
        <div class="label">Partial</div>
    </div>
    <div class="summary-item">
        <span class="icon">⬜</span>
        <div class="number" style="color:#6c757d;">{{ count($unpaidData ?? []) }}</div>
        <div class="label">Unpaid</div>
    </div>
    <div class="summary-item">
        <span class="icon">✅</span>
        <div class="number" style="color:#28a745;">{{ count($paidData ?? []) }}</div>
        <div class="label">Paid</div>
    </div>
    <div class="summary-item">
        <span class="icon">👥</span>
        <div class="number" style="color:#1a237e;">{{ count($pendingData ?? []) + count($partialData ?? []) + count($unpaidData ?? []) + count($paidData ?? []) }}</div>
        <div class="label">Total</div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PENDING SECTION --}}
{{-- ============================================================ --}}
@if(count($pendingData ?? []) > 0)
<div class="section-title section-pending">
    <span>🔴 PENDING PAYMENTS</span>
    <span class="count-badge">{{ count($pendingData) }} Residents</span>
</div>
<table>
    <thead>
        <tr style="background:#f8d7da;">
            <th style="width:30px;">#</th>
            <th style="width:120px;">Name</th>
            <th style="width:50px;">Room</th>
            <th style="width:80px;">Phone</th>
            <th style="width:70px;" class="text-right">Rent (₹)</th>
            <th style="width:80px;" class="text-right">Prev Pending (₹)</th>
            <th style="width:80px;" class="text-right">Current Bal (₹)</th>
            <th style="width:80px;" class="text-right">Total Due (₹)</th>
            <th style="width:60px;">Status</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pendingData as $index => $resident)
        <tr class="striped-row">
            <td class="text-center">{{ $index + 1 }}</td>
            <td><strong>{{ $resident['name'] }}</strong></td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 0) }}</td>
            <td class="text-right highlight-red">₹{{ number_format($resident['previous_pending'] ?? 0, 0) }}</td>
            <td class="text-right highlight-orange">₹{{ number_format($resident['current_balance'] ?? 0, 0) }}</td>
            <td class="text-right highlight-red">₹{{ number_format($resident['total_due'] ?? 0, 0) }}</td>
            <td class="text-center"><span class="badge badge-pending">{{ $resident['status'] }}</span></td>
            <td style="font-size:7.5px;">{{ Str::limit($resident['remark'] ?? '', 60) }}</td>
        </tr>
        @endforeach
        {{-- Subtotal Row --}}
        <tr style="background:#f8d7da; font-weight:bold;">
            <td colspan="7" class="text-right">SUBTOTAL</td>
            <td class="text-right highlight-red">₹{{ number_format(collect($pendingData)->sum('total_due'), 0) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
@endif

{{-- ============================================================ --}}
{{-- PARTIAL SECTION --}}
{{-- ============================================================ --}}
@if(count($partialData ?? []) > 0)
<div class="section-title section-partial">
    <span>🟡 PARTIAL PAYMENTS</span>
    <span class="count-badge">{{ count($partialData) }} Residents</span>
</div>
<table>
    <thead>
        <tr style="background:#fff3cd;">
            <th style="width:30px;">#</th>
            <th style="width:120px;">Name</th>
            <th style="width:50px;">Room</th>
            <th style="width:80px;">Phone</th>
            <th style="width:70px;" class="text-right">Rent (₹)</th>
            <th style="width:80px;" class="text-right">Paid (₹)</th>
            <th style="width:80px;" class="text-right">Balance (₹)</th>
            <th style="width:60px;">Status</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($partialData as $index => $resident)
        <tr class="striped-row">
            <td class="text-center">{{ $index + 1 }}</td>
            <td><strong>{{ $resident['name'] }}</strong></td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 0) }}</td>
            <td class="text-right highlight-green">₹{{ number_format($resident['current_paid'] ?? 0, 0) }}</td>
            <td class="text-right highlight-orange">₹{{ number_format($resident['total_due'] ?? 0, 0) }}</td>
            <td class="text-center"><span class="badge badge-partial">{{ $resident['status'] }}</span></td>
            <td style="font-size:7.5px;">{{ Str::limit($resident['remark'] ?? '', 60) }}</td>
        </tr>
        @endforeach
        {{-- Subtotal Row --}}
        <tr style="background:#fff3cd; font-weight:bold;">
            <td colspan="6" class="text-right">SUBTOTAL</td>
            <td class="text-right highlight-orange">₹{{ number_format(collect($partialData)->sum('total_due'), 0) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
@endif

{{-- ============================================================ --}}
{{-- UNPAID SECTION --}}
{{-- ============================================================ --}}
@if(count($unpaidData ?? []) > 0)
<div class="section-title section-unpaid">
    <span>⬜ UNPAID PAYMENTS</span>
    <span class="count-badge">{{ count($unpaidData) }} Residents</span>
</div>
<table>
    <thead>
        <tr style="background:#e9ecef;">
            <th style="width:30px;">#</th>
            <th style="width:120px;">Name</th>
            <th style="width:50px;">Room</th>
            <th style="width:80px;">Phone</th>
            <th style="width:80px;" class="text-right">Rent (₹)</th>
            <th style="width:80px;" class="text-right">Status</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($unpaidData as $index => $resident)
        <tr class="striped-row">
            <td class="text-center">{{ $index + 1 }}</td>
            <td><strong>{{ $resident['name'] }}</strong></td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right highlight-gray">₹{{ number_format($resident['rent'], 0) }}</td>
            <td class="text-center"><span class="badge badge-unpaid">{{ $resident['status'] }}</span></td>
            <td style="font-size:7.5px;">{{ Str::limit($resident['remark'] ?? 'No payment recorded', 60) }}</td>
        </tr>
        @endforeach
        {{-- Subtotal Row --}}
        <tr style="background:#e9ecef; font-weight:bold;">
            <td colspan="4" class="text-right">SUBTOTAL</td>
            <td class="text-right highlight-gray">₹{{ number_format(collect($unpaidData)->sum('rent'), 0) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
@endif

{{-- ============================================================ --}}
{{-- PAID SECTION --}}
{{-- ============================================================ --}}
@if(count($paidData ?? []) > 0)
<div class="section-title section-paid">
    <span>✅ PAID PAYMENTS</span>
    <span class="count-badge">{{ count($paidData) }} Residents</span>
</div>
<table>
    <thead>
        <tr style="background:#d4edda;">
            <th style="width:30px;">#</th>
            <th style="width:120px;">Name</th>
            <th style="width:50px;">Room</th>
            <th style="width:80px;">Phone</th>
            <th style="width:70px;" class="text-right">Rent (₹)</th>
            <th style="width:80px;" class="text-right">Paid (₹)</th>
            <th style="width:60px;">Status</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($paidData as $index => $resident)
        <tr class="striped-row">
            <td class="text-center">{{ $index + 1 }}</td>
            <td><strong>{{ $resident['name'] }}</strong></td>
            <td>{{ $resident['room_no'] }}</td>
            <td>{{ $resident['phone'] }}</td>
            <td class="text-right">₹{{ number_format($resident['rent'], 0) }}</td>
            <td class="text-right highlight-green">₹{{ number_format($resident['current_paid'] ?? 0, 0) }}</td>
            <td class="text-center"><span class="badge badge-paid">{{ $resident['status'] }}</span></td>
            <td style="font-size:7.5px;">{{ Str::limit($resident['remark'] ?? '', 60) }}</td>
        </tr>
        @endforeach
        {{-- Subtotal Row --}}
        <tr style="background:#d4edda; font-weight:bold;">
            <td colspan="5" class="text-right">SUBTOTAL</td>
            <td class="text-right highlight-green">₹{{ number_format(collect($paidData)->sum('current_paid'), 0) }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
@endif

{{-- ============================================================ --}}
{{-- GRAND TOTAL --}}
{{-- ============================================================ --}}
<div style="margin-top:15px; padding:10px 15px; background:#1a237e; color:white; border-radius:5px; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <span style="font-size:12px; font-weight:bold;">📊 GRAND TOTAL SUMMARY</span>
    </div>
    <div style="display:flex; gap:20px; font-size:10px;">
        <span>🔴 Pending: <strong>{{ count($pendingData ?? []) }}</strong></span>
        <span>🟡 Partial: <strong>{{ count($partialData ?? []) }}</strong></span>
        <span>⬜ Unpaid: <strong>{{ count($unpaidData ?? []) }}</strong></span>
        <span>✅ Paid: <strong>{{ count($paidData ?? []) }}</strong></span>
        <span>👥 Total: <strong>{{ count($pendingData ?? []) + count($partialData ?? []) + count($unpaidData ?? []) + count($paidData ?? []) }}</strong></span>
    </div>
</div>

{{-- Amount Summary --}}
<div style="margin-top:10px; padding:10px 15px; background:#f8f9fa; border-radius:5px; border:1px solid #dee2e6; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <span style="font-size:11px; font-weight:bold;">💰 AMOUNT SUMMARY</span>
    </div>
    <div style="display:flex; gap:20px; font-size:10px;">
        <span>Total Pending: <strong style="color:#dc3545;">₹{{ number_format(collect($pendingData)->sum('total_due'), 0) }}</strong></span>
        <span>Total Partial: <strong style="color:#fd7e14;">₹{{ number_format(collect($partialData)->sum('total_due'), 0) }}</strong></span>
        <span>Total Unpaid: <strong style="color:#6c757d;">₹{{ number_format(collect($unpaidData)->sum('rent'), 0) }}</strong></span>
        <span>Total Collected: <strong style="color:#28a745;">₹{{ number_format(collect($paidData)->sum('current_paid') + collect($partialData)->sum('current_paid'), 0) }}</strong></span>
    </div>
</div>

<div class="footer">
    Generated on {{ $generated_at }} | Filters: Hostel - {{ $filters['hostel'] ?? 'All' }}
</div>

</body>
</html>