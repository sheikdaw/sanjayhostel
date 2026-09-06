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
            font-size: 14px; 
            color: #333; 
            padding: 20px; 
            background: #f5f7fa;
            display: flex;
            justify-content: center;
        }
        .report-container {
            max-width: 900px;
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
            font-size: 13px; 
            background: #ffebee; 
            padding: 10px 15px; 
            border-radius: 8px; 
            flex-wrap: wrap; 
            gap: 5px;
        }
        .report-info .label { font-weight: 600; color: #666; }
        .report-info .value { font-weight: 600; color: #c62828; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 12px; 
            margin-top: 10px;
        }
        table th { 
            background: #c62828; 
            color: white; 
            padding: 8px 6px; 
            text-align: left; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        table td { 
            padding: 6px; 
            border-bottom: 1px solid #e0e0e0; 
        }
        table tr:nth-child(even) { background: #f8f9fa; }
        table tr:hover { background: #fff3e0; }
        .badge { 
            padding: 2px 10px; 
            border-radius: 12px; 
            font-size: 10px; 
            font-weight: 600; 
            display: inline-block; 
        }
        .badge-pending { background: #ffcdd2; color: #c62828; }
        .badge-partial { background: #ffe0b2; color: #e65100; }
        .badge-no-payment { background: #e0e0e0; color: #757575; }
        .summary-box { 
            margin-top: 15px; 
            padding: 12px 15px; 
            background: #ffebee; 
            border-radius: 8px; 
            border-left: 4px solid #c62828; 
        }
        .summary-box h3 { 
            font-size: 14px; 
            color: #c62828; 
            margin-bottom: 8px; 
        }
        .summary-grid { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
        }
        .summary-item { 
            font-size: 13px; 
        }
        .summary-item .number { 
            font-weight: 700; 
            font-size: 16px; 
        }
        .summary-item .label { color: #666; }
        .text-danger { color: #c62828; }
        .text-success { color: #2e7d32; }
        .text-warning { color: #e65100; }
        .text-primary { color: #1a237e; }
        .text-muted { color: #757575; }
        .footer { 
            margin-top: 15px; 
            padding-top: 10px; 
            border-top: 1px solid #e0e0e0; 
            text-align: center; 
            font-size: 11px; 
            color: #999; 
        }
        .remark-cell { 
            max-width: 150px; 
            word-wrap: break-word; 
            font-size: 10.5px; 
            color: #555;
        }
        .whatsapp-share {
            margin-top: 15px;
            text-align: center;
        }
        .whatsapp-share .btn {
            display: inline-block;
            padding: 10px 30px;
            background: #25D366;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        .whatsapp-share .btn:hover {
            background: #1da851;
        }
        @media (max-width: 600px) {
            .report-container { padding: 15px; }
            table { font-size: 10px; }
            table th { font-size: 9px; padding: 5px 4px; }
            table td { padding: 4px; }
            .header h1 { font-size: 18px; }
            .report-info { font-size: 11px; }
            .remark-cell { max-width: 80px; font-size: 9px; }
            .summary-item { font-size: 11px; }
        }
        @media print {
            body { background: white; padding: 10px; }
            .report-container { box-shadow: none; border: 1px solid #ddd; }
            .whatsapp-share { display: none; }
        }
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
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th width="25">S.No</th>
                            <th width="70">Hostel</th>
                            <th width="40">Room</th>
                            <th width="90">Resident</th>
                            <th width="45">Rent (₹)</th>
                            <th width="45">Paid (₹)</th>
                            <th width="45">Due (₹)</th>
                            <th width="50">Status</th>
                            <th width="120">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serialNo = 1; @endphp
                        @foreach($unpaidResidents as $item)
                            @php
                                $resident = $item['resident'];
                                $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
                                $bedNo = $resident->bed_no ?? 'N/A';
                                $status = $item['status'];
                                $statusClass = strtolower(str_replace(' ', '-', $status));
                                $paid = $item['payment'] ? ($item['payment']->cash_paid_amount + $item['payment']->upi_paid_amount) : 0;
                                $remark = $item['remark'] ?? 'No payment recorded';
                            @endphp
                            <tr>
                                <td>{{ $serialNo++ }}</td>
                                <td>{{ $resident->hostel->hostel_name ?? 'N/A' }}</td>
                                <td>#{{ $roomNo }}</td>
                                <td><strong>{{ $resident->name }}</strong></td>
                                <td>{{ number_format($resident->rent_amount ?? 0, 2) }}</td>
                                <td>{{ number_format($paid, 2) }}</td>
                                <td><strong style="color:#c62828;">₹{{ number_format($item['due_amount'], 2) }}</strong></td>
                                <td><span class="badge badge-{{ $statusClass }}">{{ $status }}</span></td>
                                <td class="remark-cell">{{ $remark }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary-box">
                <h3>📊 Unpaid Summary</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="number text-danger">{{ $totalUnpaid }}</div>
                        <div class="label">Total Unpaid</div>
                    </div>
                    <div class="summary-item">
                        <div class="number text-danger">₹{{ number_format($totalDue, 2) }}</div>
                        <div class="label">Total Due</div>
                    </div>
                    <div class="summary-item">
                        <div class="number text-primary">{{ number_format($totalUnpaid > 0 ? ($totalDue / $totalUnpaid) : 0, 2) }}</div>
                        <div class="label">Avg Due per Resident</div>
                    </div>
                </div>
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
            <button class="btn" onclick="shareWhatsApp()">
                <i class="bi bi-whatsapp"></i> Share via WhatsApp
            </button>
            <button class="btn" onclick="window.print()" style="background:#6b7280; margin-left:10px;">
                <i class="bi bi-printer"></i> Print / Save as Image
            </button>
        </div>
    </div>

    <script>
        function shareWhatsApp() {
            // Get the report content
            const reportContent = document.getElementById('reportContainer').innerHTML;
            
            // Create a temporary element to get text
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = reportContent;
            const textContent = tempDiv.textContent || tempDiv.innerText;
            
            // Clean up the text for WhatsApp
            let message = textContent
                .replace(/\s+/g, ' ')  // Remove extra spaces
                .replace(/Share via WhatsApp/g, '')
                .replace(/Print \/ Save as Image/g, '')
                .trim();
            
            // Get the current URL
            const url = window.location.href;
            
            // WhatsApp message
            const whatsappMsg = encodeURIComponent(
                `📊 *Unpaid Payments Report*\n\n` +
                `${message}\n\n` +
                `📄 View online: ${url}`
            );
            
            // Open WhatsApp
            const phone = prompt('Enter WhatsApp number (with country code, e.g., 91):', '91');
            if (phone) {
                const cleanPhone = phone.replace(/\D/g, '');
                window.open(`https://wa.me/${cleanPhone}?text=${whatsappMsg}`, '_blank');
            }
        }

        // Auto-print for saving as image
        window.onload = function() {
            // If URL has ?print=1, auto print
            if (window.location.search.includes('print=1')) {
                setTimeout(function() {
                    window.print();
                }, 1000);
            }
        }
    </script>
</body>
</html>