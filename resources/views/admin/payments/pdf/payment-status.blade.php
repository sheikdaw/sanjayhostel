<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Payment Status Report</title>
    <style>
        /* ============================================================
           RESET & BASE
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            padding: 15px 20px;
            background: #ffffff;
            color: #1a1a2e;
            line-height: 1.4;
        }
        
        /* ============================================================
           HEADER
           ============================================================ */
        .report-header {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            padding: 18px 25px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
            position: relative;
        }
        
        .report-header .logo-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .report-header h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
        }
        
        .report-header .subtitle {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 3px;
        }
        
        .report-header .header-right {
            text-align: right;
            font-size: 9px;
            opacity: 0.9;
        }
        
        .report-header .header-right .label {
            opacity: 0.7;
            font-size: 8px;
        }
        
        .report-header .header-right .value {
            font-weight: 600;
            font-size: 10px;
        }
        
        .report-meta {
            background: #f0f2f5;
            padding: 10px 25px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-size: 9px;
            color: #555;
        }
        
        .report-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .report-meta .meta-item .tag {
            background: #e0e0e0;
            padding: 1px 8px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 600;
            color: #333;
        }
        
        .report-meta .meta-item .value {
            font-weight: 600;
            color: #1a1a2e;
        }
        
        /* ============================================================
           SUMMARY CARDS
           ============================================================ */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin: 15px 0 20px 0;
        }
        
        .summary-card {
            background: white;
            border: 1px solid #e8e8e8;
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        
        .summary-card .icon {
            font-size: 18px;
            display: block;
            margin-bottom: 2px;
        }
        
        .summary-card .number {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .summary-card .label {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            margin-top: 2px;
        }
        
        .summary-card.pending .number { color: #dc3545; }
        .summary-card.partial .number { color: #fd7e14; }
        .summary-card.unpaid .number { color: #6c757d; }
        .summary-card.paid .number { color: #28a745; }
        .summary-card.total .number { color: #1a237e; }
        
        .summary-card.pending { border-top: 3px solid #dc3545; }
        .summary-card.partial { border-top: 3px solid #fd7e14; }
        .summary-card.unpaid { border-top: 3px solid #6c757d; }
        .summary-card.paid { border-top: 3px solid #28a745; }
        .summary-card.total { border-top: 3px solid #1a237e; }
        
        /* ============================================================
           SECTION HEADERS
           ============================================================ */
        .section {
            margin-top: 18px;
            page-break-inside: avoid;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            border-radius: 4px 4px 0 0;
            font-weight: 700;
            font-size: 11px;
        }
        
        .section-header .count {
            font-size: 9px;
            background: rgba(255,255,255,0.3);
            padding: 1px 12px;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .section-header.pending { background: #fce4e4; color: #721c24; border-left: 4px solid #dc3545; }
        .section-header.partial { background: #fff3e0; color: #856404; border-left: 4px solid #fd7e14; }
        .section-header.unpaid { background: #f0f0f0; color: #495057; border-left: 4px solid #6c757d; }
        .section-header.paid { background: #e8f5e9; color: #155724; border-left: 4px solid #28a745; }
        
        /* ============================================================
           TABLES
           ============================================================ */
        .table-wrap {
            border: 1px solid #e0e0e0;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        table thead th {
            background: #f5f5f5;
            color: #333;
            font-weight: 700;
            padding: 6px 8px;
            border-bottom: 2px solid #ddd;
            text-align: left;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        table tbody tr:last-child td {
            border-bottom: none;
        }
        
        table tbody tr:hover {
            background: #fafafa;
        }
        
        table tbody tr.striped:nth-child(even) {
            background: #fafafa;
        }
        
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* ============================================================
           STATUS BADGES
           ============================================================ */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 7px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        
        .badge-pending { background: #fce4e4; color: #721c24; }
        .badge-partial { background: #fff3e0; color: #856404; }
        .badge-paid { background: #e8f5e9; color: #155724; }
        .badge-unpaid { background: #f0f0f0; color: #495057; }
        
        /* ============================================================
           SUBTOTAL ROW
           ============================================================ */
        .subtotal-row td {
            background: #f8f9fa;
            font-weight: 700;
            border-top: 2px solid #ddd;
            padding: 6px 8px;
            font-size: 8px;
        }
        
        .subtotal-row.pending td { background: #fce4e4; }
        .subtotal-row.partial td { background: #fff3e0; }
        .subtotal-row.unpaid td { background: #f0f0f0; }
        .subtotal-row.paid td { background: #e8f5e9; }
        
        /* ============================================================
           AMOUNT STYLES
           ============================================================ */
        .amount-positive { color: #28a745; font-weight: 600; }
        .amount-negative { color: #dc3545; font-weight: 600; }
        .amount-warning { color: #fd7e14; font-weight: 600; }
        .amount-neutral { color: #6c757d; font-weight: 600; }
        
        /* ============================================================
           FOOTER / GRAND TOTAL
           ============================================================ */
        .grand-total-box {
            margin-top: 15px;
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .grand-total-box .title {
            font-size: 12px;
            font-weight: 700;
        }
        
        .grand-total-box .stats {
            display: flex;
            gap: 18px;
            font-size: 9px;
        }
        
        .grand-total-box .stats .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .grand-total-box .stats .stat-item .num {
            font-weight: 700;
            font-size: 11px;
        }
        
        /* ============================================================
           AMOUNT SUMMARY
           ============================================================ */
        .amount-summary {
            margin-top: 10px;
            background: #f8f9fa;
            border: 1px solid #e8e8e8;
            border-radius: 6px;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .amount-summary .item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 9px;
        }
        
        .amount-summary .item .label {
            color: #888;
            font-size: 7.5px;
            text-transform: uppercase;
        }
        
        .amount-summary .item .value {
            font-weight: 700;
            font-size: 11px;
        }
        
        /* ============================================================
           FOOTER
           ============================================================ */
        .report-footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #e8e8e8;
            display: flex;
            justify-content: space-between;
            font-size: 7.5px;
            color: #999;
        }
        
        .report-footer .generated {
            color: #666;
        }
        
        /* ============================================================
           PAGE BREAKS
           ============================================================ */
        .page-break {
            page-break-after: always;
            border: none;
            margin: 0;
            padding: 0;
        }
        
        /* ============================================================
           RESPONSIVE / PRINT
           ============================================================ */
        @media print {
            body { padding: 10px 15px; }
            .page-break { page-break-after: always; }
            
            .summary-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .report-header {
                background: #1a237e !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .section-header.pending,
            .section-header.partial,
            .section-header.unpaid,
            .section-header.paid {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .grand-total-box {
                background: #1a237e !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            table thead th {
                background: #f5f5f5 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .badge-pending,
            .badge-partial,
            .badge-paid,
            .badge-unpaid {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .subtotal-row.pending td,
            .subtotal-row.partial td,
            .subtotal-row.unpaid td,
            .subtotal-row.paid td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .amount-summary {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .summary-card.pending { border-top: 3px solid #dc3545 !important; }
            .summary-card.partial { border-top: 3px solid #fd7e14 !important; }
            .summary-card.unpaid { border-top: 3px solid #6c757d !important; }
            .summary-card.paid { border-top: 3px solid #28a745 !important; }
            .summary-card.total { border-top: 3px solid #1a237e !important; }
        }
        
        /* ============================================================
           UTILITY
           ============================================================ */
        .mt-0 { margin-top: 0; }
        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-15 { margin-top: 15px; }
        .mb-5 { margin-bottom: 5px; }
        .mb-10 { margin-bottom: 10px; }
        
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }
        
        .fs-7 { font-size: 7px; }
        .fs-8 { font-size: 8px; }
        .fs-9 { font-size: 9px; }
        .fs-10 { font-size: 10px; }
        .fs-11 { font-size: 11px; }
        .fs-12 { font-size: 12px; }
    </style>
</head>
<body>

<!-- ============================================================
     HEADER
     ============================================================ -->
<div class="report-header">
    <div class="logo-area">
        <div>
            <h1>📊 Payment Status Report</h1>
            <div class="subtitle">Complete payment summary with status breakdown</div>
        </div>
        <div class="header-right">
            <div><span class="label">Report ID:</span> <span class="value">#{{ rand(10000, 99999) }}</span></div>
            <div><span class="label">Version:</span> <span class="value">1.0</span></div>
        </div>
    </div>
</div>

<div class="report-meta">
    <div class="meta-item">
        <span>📅</span>
        <span class="tag">MONTH</span>
        <span class="value">{{ $month }} {{ $year }}</span>
    </div>
    <div class="meta-item">
        <span>🏢</span>
        <span class="tag">HOSTEL</span>
        <span class="value">{{ $filters['hostel'] ?? 'All Hostels' }}</span>
    </div>
    <div class="meta-item">
        <span>👤</span>
        <span class="tag">GENERATED BY</span>
        <span class="value">{{ $user->name ?? 'System' }}</span>
    </div>
    <div class="meta-item">
        <span>⏰</span>
        <span class="tag">GENERATED AT</span>
        <span class="value">{{ $generated_at }}</span>
    </div>
</div>

<!-- ============================================================
     SUMMARY CARDS
     ============================================================ -->
<div class="summary-cards">
    <div class="summary-card pending">
        <span class="icon">🔴</span>
        <div class="number">{{ count($pendingData ?? []) }}</div>
        <div class="label">Pending</div>
    </div>
    <div class="summary-card partial">
        <span class="icon">🟡</span>
        <div class="number">{{ count($partialData ?? []) }}</div>
        <div class="label">Partial</div>
    </div>
    <div class="summary-card unpaid">
        <span class="icon">⬜</span>
        <div class="number">{{ count($unpaidData ?? []) }}</div>
        <div class="label">Unpaid</div>
    </div>
    <div class="summary-card paid">
        <span class="icon">✅</span>
        <div class="number">{{ count($paidData ?? []) }}</div>
        <div class="label">Paid</div>
    </div>
    <div class="summary-card total">
        <span class="icon">👥</span>
        <div class="number">{{ count($pendingData ?? []) + count($partialData ?? []) + count($unpaidData ?? []) + count($paidData ?? []) }}</div>
        <div class="label">Total Residents</div>
    </div>
</div>

<!-- ============================================================
     PENDING SECTION
     ============================================================ -->
@if(count($pendingData ?? []) > 0)
<div class="section">
    <div class="section-header pending">
        <span>🔴 PENDING PAYMENTS</span>
        <span class="count">{{ count($pendingData) }} Residents</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="width:130px;">Resident</th>
                    <th style="width:45px;">Room</th>
                    <th style="width:70px;">Phone</th>
                    <th style="width:65px;" class="text-right">Rent</th>
                    <th style="width:75px;" class="text-right">Prev Pending</th>
                    <th style="width:75px;" class="text-right">Current Bal</th>
                    <th style="width:75px;" class="text-right">Total Due</th>
                    <th style="width:55px;">Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingData as $index => $resident)
                <tr class="striped">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $resident['name'] }}</strong></td>
                    <td>{{ $resident['room_no'] }}</td>
                    <td>{{ $resident['phone'] }}</td>
                    <td class="text-right">₹{{ number_format($resident['rent'], 0) }}</td>
                    <td class="text-right amount-negative">₹{{ number_format($resident['previous_pending'] ?? 0, 0) }}</td>
                    <td class="text-right amount-warning">₹{{ number_format($resident['current_balance'] ?? 0, 0) }}</td>
                    <td class="text-right amount-negative">₹{{ number_format($resident['total_due'] ?? 0, 0) }}</td>
                    <td class="text-center"><span class="badge badge-pending">Pending</span></td>
                    <td class="fs-7" style="color:#666;">{{ Str::limit($resident['remark'] ?? '', 50) }}</td>
                </tr>
                @endforeach
                <tr class="subtotal-row pending">
                    <td colspan="7" class="text-right">SUBTOTAL</td>
                    <td class="text-right amount-negative">₹{{ number_format(collect($pendingData)->sum('total_due'), 0) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ============================================================
     PARTIAL SECTION
     ============================================================ -->
@if(count($partialData ?? []) > 0)
<div class="section">
    <div class="section-header partial">
        <span>🟡 PARTIAL PAYMENTS</span>
        <span class="count">{{ count($partialData) }} Residents</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="width:130px;">Resident</th>
                    <th style="width:45px;">Room</th>
                    <th style="width:70px;">Phone</th>
                    <th style="width:65px;" class="text-right">Rent</th>
                    <th style="width:75px;" class="text-right">Paid</th>
                    <th style="width:75px;" class="text-right">Balance</th>
                    <th style="width:55px;">Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partialData as $index => $resident)
                <tr class="striped">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $resident['name'] }}</strong></td>
                    <td>{{ $resident['room_no'] }}</td>
                    <td>{{ $resident['phone'] }}</td>
                    <td class="text-right">₹{{ number_format($resident['rent'], 0) }}</td>
                    <td class="text-right amount-positive">₹{{ number_format($resident['current_paid'] ?? 0, 0) }}</td>
                    <td class="text-right amount-warning">₹{{ number_format($resident['total_due'] ?? 0, 0) }}</td>
                    <td class="text-center"><span class="badge badge-partial">Partial</span></td>
                    <td class="fs-7" style="color:#666;">{{ Str::limit($resident['remark'] ?? '', 50) }}</td>
                </tr>
                @endforeach
                <tr class="subtotal-row partial">
                    <td colspan="6" class="text-right">SUBTOTAL</td>
                    <td class="text-right amount-warning">₹{{ number_format(collect($partialData)->sum('total_due'), 0) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ============================================================
     UNPAID SECTION
     ============================================================ -->
@if(count($unpaidData ?? []) > 0)
<div class="section">
    <div class="section-header unpaid">
        <span>⬜ UNPAID PAYMENTS</span>
        <span class="count">{{ count($unpaidData) }} Residents</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="width:130px;">Resident</th>
                    <th style="width:45px;">Room</th>
                    <th style="width:70px;">Phone</th>
                    <th style="width:80px;" class="text-right">Rent</th>
                    <th style="width:55px;">Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unpaidData as $index => $resident)
                <tr class="striped">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $resident['name'] }}</strong></td>
                    <td>{{ $resident['room_no'] }}</td>
                    <td>{{ $resident['phone'] }}</td>
                    <td class="text-right amount-neutral">₹{{ number_format($resident['rent'], 0) }}</td>
                    <td class="text-center"><span class="badge badge-unpaid">Unpaid</span></td>
                    <td class="fs-7" style="color:#666;">{{ Str::limit($resident['remark'] ?? 'No payment recorded', 50) }}</td>
                </tr>
                @endforeach
                <tr class="subtotal-row unpaid">
                    <td colspan="4" class="text-right">SUBTOTAL</td>
                    <td class="text-right amount-neutral">₹{{ number_format(collect($unpaidData)->sum('rent'), 0) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ============================================================
     PAID SECTION
     ============================================================ -->
@if(count($paidData ?? []) > 0)
<div class="section">
    <div class="section-header paid">
        <span>✅ PAID PAYMENTS</span>
        <span class="count">{{ count($paidData) }} Residents</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="width:130px;">Resident</th>
                    <th style="width:45px;">Room</th>
                    <th style="width:70px;">Phone</th>
                    <th style="width:65px;" class="text-right">Rent</th>
                    <th style="width:75px;" class="text-right">Paid</th>
                    <th style="width:55px;">Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paidData as $index => $resident)
                <tr class="striped">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $resident['name'] }}</strong></td>
                    <td>{{ $resident['room_no'] }}</td>
                    <td>{{ $resident['phone'] }}</td>
                    <td class="text-right">₹{{ number_format($resident['rent'], 0) }}</td>
                    <td class="text-right amount-positive">₹{{ number_format($resident['current_paid'] ?? 0, 0) }}</td>
                    <td class="text-center"><span class="badge badge-paid">Paid</span></td>
                    <td class="fs-7" style="color:#666;">{{ Str::limit($resident['remark'] ?? '', 50) }}</td>
                </tr>
                @endforeach
                <tr class="subtotal-row paid">
                    <td colspan="5" class="text-right">SUBTOTAL</td>
                    <td class="text-right amount-positive">₹{{ number_format(collect($paidData)->sum('current_paid'), 0) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ============================================================
     GRAND TOTAL
     ============================================================ -->
<div class="grand-total-box">
    <div>
        <span class="title">📊 GRAND TOTAL</span>
        <div style="font-size:8px; opacity:0.7; margin-top:2px;">Complete summary of all residents</div>
    </div>
    <div class="stats">
        <div class="stat-item">
            <span>🔴</span>
            <span class="num">{{ count($pendingData ?? []) }}</span>
            <span style="font-size:8px; opacity:0.7;">Pending</span>
        </div>
        <div class="stat-item">
            <span>🟡</span>
            <span class="num">{{ count($partialData ?? []) }}</span>
            <span style="font-size:8px; opacity:0.7;">Partial</span>
        </div>
        <div class="stat-item">
            <span>⬜</span>
            <span class="num">{{ count($unpaidData ?? []) }}</span>
            <span style="font-size:8px; opacity:0.7;">Unpaid</span>
        </div>
        <div class="stat-item">
            <span>✅</span>
            <span class="num">{{ count($paidData ?? []) }}</span>
            <span style="font-size:8px; opacity:0.7;">Paid</span>
        </div>
        <div class="stat-item" style="border-left:1px solid rgba(255,255,255,0.2); padding-left:12px;">
            <span>👥</span>
            <span class="num" style="font-size:13px;">{{ count($pendingData ?? []) + count($partialData ?? []) + count($unpaidData ?? []) + count($paidData ?? []) }}</span>
            <span style="font-size:8px; opacity:0.7;">Total</span>
        </div>
    </div>
</div>

<!-- ============================================================
     AMOUNT SUMMARY
     ============================================================ -->
<div class="amount-summary">
    <div class="item">
        <span style="color:#dc3545;">🔴</span>
        <span class="label">Total Pending</span>
        <span class="value" style="color:#dc3545;">₹{{ number_format(collect($pendingData)->sum('total_due'), 0) }}</span>
    </div>
    <div class="item">
        <span style="color:#fd7e14;">🟡</span>
        <span class="label">Total Partial</span>
        <span class="value" style="color:#fd7e14;">₹{{ number_format(collect($partialData)->sum('total_due'), 0) }}</span>
    </div>
    <div class="item">
        <span style="color:#6c757d;">⬜</span>
        <span class="label">Total Unpaid</span>
        <span class="value" style="color:#6c757d;">₹{{ number_format(collect($unpaidData)->sum('rent'), 0) }}</span>
    </div>
    <div class="item">
        <span style="color:#28a745;">✅</span>
        <span class="label">Total Collected</span>
        <span class="value" style="color:#28a745;">₹{{ number_format(collect($paidData)->sum('current_paid') + collect($partialData)->sum('current_paid'), 0) }}</span>
    </div>
</div>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<div class="report-footer">
    <div>
        <span class="generated">Generated by: {{ $user->name ?? 'System' }}</span>
    </div>
    <div>
        <span class="generated">Generated on: {{ $generated_at }}</span>
    </div>
    <div>
        <span class="generated">Page 1 of 1</span>
    </div>
</div>

</body>
</html>