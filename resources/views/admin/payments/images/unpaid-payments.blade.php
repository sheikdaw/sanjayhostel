<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            font-size: 13px; 
            color: #333; 
            padding: 15px; 
            background: #f5f7fa;
            display: flex;
            justify-content: center;
        }
        .report-container {
            max-width: 1100px;
            width: 100%;
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header { 
            text-align: center; 
            border-bottom: 4px solid #c62828; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .header h1 { 
            font-size: 24px; 
            color: #c62828; 
            font-weight: 700;
        }
        .header .sub { 
            font-size: 13px; 
            color: #666; 
            margin-top: 4px; 
        }
        .report-info { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 15px; 
            font-size: 12px; 
            background: #ffebee; 
            padding: 10px 15px; 
            border-radius: 8px; 
            flex-wrap: wrap; 
            gap: 5px;
        }
        .report-info .label { font-weight: 600; color: #666; }
        .report-info .value { font-weight: 600; color: #c62828; }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .summary-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            border-left: 3px solid #c62828;
        }
        .summary-item .number { font-size: 18px; font-weight: 700; }
        .summary-item .label { font-size: 11px; color: #666; margin-top: 2px; }
        .text-danger { color: #c62828; }
        .text-success { color: #2e7d32; }
        .text-warning { color: #e65100; }
        .text-primary { color: #1a237e; }
        .text-muted { color: #757575; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 11px; 
            margin-top: 10px;
        }
        table th { 
            background: #c62828; 
            color: white; 
            padding: 6px 4px; 
            text-align: left; 
            font-size: 10px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        table td { 
            padding: 5px 4px; 
            border-bottom: 1px solid #e0e0e0; 
        }
        table tr:nth-child(even) { background: #f8f9fa; }
        .badge { 
            padding: 2px 8px; 
            border-radius: 10px; 
            font-size: 9px; 
            font-weight: 600; 
            display: inline-block; 
        }
        .badge-pending { background: #ffcdd2; color: #c62828; }
        .badge-partial { background: #ffe0b2; color: #e65100; }
        .badge-paid { background: #c8e6c9; color: #2e7d32; }
        .badge-no-payment { background: #e0e0e0; color: #757575; }
        .footer { 
            margin-top: 15px; 
            padding-top: 10px; 
            border-top: 1px solid #e0e0e0; 
            text-align: center; 
            font-size: 10px; 
            color: #999; 
        }
        .prev-details {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }
        .prev-details .month-label { font-weight: 600; color: #c62828; }
        .whatsapp-share {
            margin-top: 15px;
            text-align: center;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .whatsapp-share .btn {
            display: inline-block;
            padding: 8px 25px;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            cursor: pointer;
        }
        .btn-whatsapp { background: #25D366; }
        .btn-whatsapp:hover { background: #1da851; }
        .btn-print { background: #6b7280; }
        .btn-print:hover { background: #4b5563; }
        .btn-pdf { background: #dc2626; }
        .btn-pdf:hover { background: #b91c1c; }
        @media (max-width: 600px) {
            .report-container { padding: 15px; }
            table { font-size: 9px; }
            table th { font-size: 8px; padding: 4px 3px; }
            table td { padding: 3px; }
            .header h1 { font-size: 18px; }
            .report-info { font-size: 10px; }
            .summary-item .number { font-size: 14px; }
        }
        @media print {
            body { background: white; padding: 10px; }
            .report-container { box-shadow: none; border: 1px solid #ddd; }
            .whatsapp-share { display: none; }
        }
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background: #fff3e0 !important; }
    </style>
</head>
<body>
    <div class="report-container" id="reportContainer">
        <div class="header">
            <h1>🔴 {{ $title }}</h1>
            <div class="sub">Generated on: {{ $generated_at }}</div>
        </div>

        <div class="report-info">
            <div><span class="label">Month:</span> <span class="value">{{ $month }} {{ $year }}</span></div>
            <div><span class="label">Hostel:</span> <span class="value">{{ $hostel }}</span></div>
            <div><span class="label">Unpaid Residents:</span> <span class="value">{{ $totalUnpaid }}</span></div>
            <div><span class="label">Total Due:</span> <span class="value">₹{{ number_format($totalDue, 2) }}</span></div>
        </div>

        @if($totalUnpaid > 0)
            <!-- Summary -->
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="number text-danger">{{ $totalUnpaid }}</div>
                    <div class="label">Total Unpaid</div>
                </div>
                <div class="summary-item">
                    <div class="number text-danger">₹{{ number_format($totalPreviousPending, 2) }}</div>
                    <div class="label">Previous Pending</div>
                </div>
                <div class="summary-item">
                    <div class="number text-warning">₹{{ number_format($totalCurrentBalance, 2) }}</div>
                    <div class="label">Current Balance</div>
                </div>
                <div class="summary-item" style="border-left-color: #1a237e;">
                    <div class="number text-primary">₹{{ number_format($totalDue, 2) }}</div>
                    <div class="label">Total Due</div>
                </div>
            </div>

            <!-- Detailed Table -->
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Resident</th>
                            <th>Hostel</th>
                            <th>Room</th>
                            <th>Phone</th>
                            <th>Rent (₹)</th>
                            <th>Previous Pending (₹)</th>
                            <th>Current Balance (₹)</th>
                            <th>Total Due (₹)</th>
                            <th>Status</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serialNo = 1; @endphp
                        @foreach($unpaidResidents as $item)
                            @php
                                $resident = $item['resident'];
                                $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
                                $status = $item['overall_status'];
                                $statusClass = strtolower(str_replace(' ', '-', $status));
                                $prevMonths = $item['previous_months_details'];
                            @endphp
                            <tr class="clickable-row" title="Click to see previous months details">
                                <td>{{ $serialNo++ }}</td>
                                <td><strong>{{ $resident->name }}</strong></td>
                                <td>{{ $resident->hostel->hostel_name ?? 'N/A' }}</td>
                                <td>#{{ $roomNo }}</td>
                                <td>{{ $resident->phone ?? '' }}</td>
                                <td>₹{{ number_format($item['total_rent'], 2) }}</td>
                                <td>
                                    @if($item['total_previous_pending'] > 0)
                                        <strong style="color:#c62828;">₹{{ number_format($item['total_previous_pending'], 2) }}</strong>
                                        @if($item['previous_pending_count'] > 0)
                                            <br><span style="font-size:8px; color:#999;">({{ $item['previous_pending_count'] }} month(s))</span>
                                        @endif
                                    @else
                                        <span style="color:#999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['current_balance'] > 0)
                                        <strong style="color:#e65100;">₹{{ number_format($item['current_balance'], 2) }}</strong>
                                    @else
                                        <span style="color:#22c55e;">✅ Paid</span>
                                    @endif
                                </td>
                                <td><strong style="color:#c62828;">₹{{ number_format($item['total_due'], 2) }}</strong></td>
                                <td><span class="badge badge-{{ $statusClass }}">{{ $status }}</span></td>
                                <td style="font-size:10px; max-width:150px; word-wrap:break-word;">{{ $item['remark'] }}</td>
                            </tr>
                            @if($item['previous_pending_count'] > 0)
                                <tr style="background:#fef3c7;">
                                    <td colspan="11" style="padding:4px 8px; font-size:9px; color:#666;">
                                        <i class="bi bi-clock-history"></i> 
                                        <strong>Previous Months:</strong>
                                        @foreach($prevMonths as $prev)
                                            @if($prev['balance'] > 0)
                                                <span style="display:inline-block; margin-right:10px;">
                                                    <span class="month-label">{{ $prev['month_name'] }} {{ $prev['year'] }}</span>
                                                    : ₹{{ number_format($prev['balance'], 2) }}
                                                    ({{ $prev['status'] }})
                                                </span>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center; padding:30px; background:#e8f5e9; border-radius:8px;">
                <h2 style="color:#2e7d32;">✅ All Residents Have Paid</h2>
                <p style="color:#666; font-size:14px;">No unpaid residents found for {{ $month }} {{ $year }}</p>
            </div>
        @endif

        <div class="footer">
            Generated by: {{ $user->name }} ({{ $user->role }}) | {{ $generated_at }}
        </div>

        <div class="whatsapp-share">
            <button class="btn btn-whatsapp" onclick="shareWhatsApp()">
                <i class="bi bi-whatsapp"></i> Share via WhatsApp
            </button>
            <button class="btn btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Print / Save Image
            </button>
            <button class="btn btn-pdf" onclick="downloadPDF()">
                <i class="bi bi-file-pdf"></i> Download PDF
            </button>
        </div>
    </div>

    <script>
        function shareWhatsApp() {
            const reportContent = document.getElementById('reportContainer').innerHTML;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = reportContent;
            let textContent = tempDiv.textContent || tempDiv.innerText;
            
            textContent = textContent
                .replace(/\s+/g, ' ')
                .replace(/Share via WhatsApp/g, '')
                .replace(/Print \/ Save Image/g, '')
                .replace(/Download PDF/g, '')
                .trim();
            
            const url = window.location.href;
            const whatsappMsg = encodeURIComponent(
                `📊 *Unpaid Payments Report*\n\n` +
                `${textContent}\n\n` +
                `📄 View online: ${url}`
            );
            
            const phone = prompt('Enter WhatsApp number (with country code, e.g., 91):', '91');
            if (phone) {
                const cleanPhone = phone.replace(/\D/g, '');
                window.open(`https://wa.me/${cleanPhone}?text=${whatsappMsg}`, '_blank');
            }
        }

        function downloadPDF() {
            window.location.href = "{{ route('admin.payments.pdf.unpaid-with-details') }}?" + window.location.search.replace('?', '');
        }

        if (window.location.search.includes('print=1')) {
            setTimeout(function() { window.print(); }, 1000);
        }
    </script>
</body>
</html>