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
        
        /* 🔥 BUTTON STYLES */
        .share-section {
            margin-top: 15px;
            text-align: center;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .share-section .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 25px;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .share-section .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-whatsapp-text { background: #25D366; }
        .btn-whatsapp-text:hover { background: #1da851; }
        .btn-whatsapp-image { background: #128C7E; }
        .btn-whatsapp-image:hover { background: #075E54; }
        .btn-print { background: #6b7280; }
        .btn-print:hover { background: #4b5563; }
        .btn-pdf { background: #dc2626; }
        .btn-pdf:hover { background: #b91c1c; }
        .btn-copy { background: #1a237e; }
        .btn-copy:hover { background: #0d1445; }
        
        /* 🔥 TOAST NOTIFICATION */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a237e;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .toast-notification.show {
            opacity: 1;
        }
        
        /* 🔥 IMAGE CAPTURE AREA */
        #captureArea {
            background: white;
            padding: 20px;
            border-radius: 12px;
        }
        
        @media (max-width: 600px) {
            .report-container { padding: 15px; }
            table { font-size: 9px; }
            table th { font-size: 8px; padding: 4px 3px; }
            table td { padding: 3px; }
            .header h1 { font-size: 18px; }
            .report-info { font-size: 10px; }
            .summary-item .number { font-size: 14px; }
            .share-section .btn { padding: 8px 15px; font-size: 11px; }
        }
        @media print {
            body { background: white; padding: 10px; }
            .report-container { box-shadow: none; border: 1px solid #ddd; }
            .share-section { display: none; }
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast" class="toast-notification"></div>

    <div class="report-container" id="reportContainer">
        <!-- 🔥 CAPTURE AREA FOR IMAGE -->
        <div id="captureArea">
            <div class="header">
                <h1>🔴 {{ $title }}</h1>
                <div class="sub">Generated on: {{ $generated_at }}</div>
            </div>

            <div class="report-info">
                <div><span class="label">Month:</span> <span class="value">{{ $month }} {{ $year }}</span></div>
                <div><span class="label">Hostel:</span> <span class="value">{{ $hostel }}</span></div>
                <div><span class="label">Unpaid Residents:</span> <span class="value">{{ $totalUnpaid }}</span></div>
                <div><span class="label">Total Due:</span> <span class="value">{{ number_format($totalDue, 2) }}</span></div>
            </div>

            @if($totalUnpaid > 0)
                <!-- Summary -->
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="number text-danger">{{ $totalUnpaid }}</div>
                        <div class="label">Total Unpaid</div>
                    </div>
                    <div class="summary-item">
                        <div class="number text-danger">{{ number_format($totalPreviousPending, 2) }}</div>
                        <div class="label">Previous Pending</div>
                    </div>
                    <div class="summary-item">
                        <div class="number text-warning">{{ number_format($totalCurrentBalance, 2) }}</div>
                        <div class="label">Current Balance</div>
                    </div>
                    <div class="summary-item" style="border-left-color: #1a237e;">
                        <div class="number text-primary">{{ number_format($totalDue, 2) }}</div>
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
                                <th>Prev Pending ()</th>
                                <th>Current Bal ()</th>
                                <th>Total Due ()</th>
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
                                <tr>
                                    <td>{{ $serialNo++ }}</td>
                                    <td><strong>{{ $resident->name }}</strong></td>
                                    <td>{{ $resident->hostel->hostel_name ?? 'N/A' }}</td>
                                    <td>#{{ $roomNo }}</td>
                                    <td>{{ $resident->phone ?? '' }}</td>
                                    <td>
                                        @if($item['total_previous_pending'] > 0)
                                            <strong style="color:#c62828;">{{ number_format($item['total_previous_pending'], 2) }}</strong>
                                            @if($item['previous_pending_count'] > 0)
                                                <br><span style="font-size:8px; color:#999;">({{ $item['previous_pending_count'] }} month(s))</span>
                                            @endif
                                        @else
                                            <span style="color:#999;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['current_balance'] > 0)
                                            <strong style="color:#e65100;">{{ number_format($item['current_balance'], 2) }}</strong>
                                        @else
                                            <span style="color:#22c55e;">✅ Paid</span>
                                        @endif
                                    </td>
                                    <td><strong style="color:#c62828;">{{ number_format($item['total_due'], 2) }}</strong></td>
                                    <td><span class="badge badge-{{ $statusClass }}">{{ $status }}</span></td>
                                    <td style="font-size:10px; max-width:150px; word-wrap:break-word;">{{ $item['remark'] }}</td>
                                </tr>
                                @if($item['previous_pending_count'] > 0)
                                    <tr style="background:#fef3c7;">
                                        <td colspan="10" style="padding:4px 8px; font-size:9px; color:#666;">
                                            <i class="bi bi-clock-history"></i> 
                                            <strong>Previous Months:</strong>
                                            @foreach($prevMonths as $prev)
                                                @if($prev['balance'] > 0)
                                                    <span style="display:inline-block; margin-right:10px;">
                                                        <span class="month-label">{{ $prev['month_name'] }} {{ $prev['year'] }}</span>
                                                        : {{ number_format($prev['balance'], 2) }}
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
        </div>

        <!-- 🔥 SHARE BUTTONS -->
        <div class="share-section">
            <button class="btn btn-whatsapp-text" onclick="shareAsText()">
                <i class="bi bi-whatsapp"></i> Share as Text
            </button>
            <button class="btn btn-whatsapp-image" onclick="shareAsImage()">
                <i class="bi bi-whatsapp"></i> Share as Image
            </button>
            <button class="btn btn-copy" onclick="copyText()">
                <i class="bi bi-clipboard"></i> Copy Text
            </button>
            <button class="btn btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Print / Save
            </button>
            <button class="btn btn-pdf" onclick="downloadPDF()">
                <i class="bi bi-file-pdf"></i> Download PDF
            </button>
        </div>
    </div>

    <!-- html2canvas for image capture -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // ============================================================
        // 🔥 SHARE AS TEXT - Beautiful Formatted Table
        // ============================================================
        function shareAsText() {
            const phone = prompt('Enter WhatsApp number (with country code, e.g., 91):', '91');
            if (!phone) return;

            const cleanPhone = phone.replace(/\D/g, '');
            const message = generateTextMessage();
            const url = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
            window.open(url, '_blank');
        }

        // ============================================================
        // 🔥 GENERATE FORMATTED TEXT MESSAGE
        // ============================================================
        function generateTextMessage() {
            let lines = [];
            lines.push('📊 *UNPAID PAYMENTS REPORT*');
            lines.push('━'.repeat(40));
            lines.push(`📅 Month: {{ $month }} {{ $year }}`);
            lines.push(`🏢 Hostel: {{ $hostel }}`);
            lines.push(`🔴 Unpaid: {{ $totalUnpaid }}`);
            lines.push(`💰 Total Due: {{ number_format($totalDue, 2) }}`);
            lines.push('━'.repeat(40));
            lines.push('');

            @if($totalUnpaid > 0)
                // Summary
                lines.push('📋 *SUMMARY*');
                lines.push(`  Total Unpaid: {{ $totalUnpaid }}`);
                lines.push(`  Previous Pending: {{ number_format($totalPreviousPending, 2) }}`);
                lines.push(`  Current Balance: {{ number_format($totalCurrentBalance, 2) }}`);
                lines.push(`  Total Due: {{ number_format($totalDue, 2) }}`);
                lines.push('');
                lines.push('━'.repeat(40));
                lines.push('');

                // Table Header
                lines.push('📋 *DETAILED REPORT*');
                lines.push('');
                lines.push('┌────┬──────────────────┬──────────┬──────────────┬────────────┬────────────┐');
                lines.push('│ #  │ Resident         │ Room     │ Prev Pending │ Curr Bal   │ Total Due  │');
                lines.push('├────┼──────────────────┼──────────┼──────────────┼────────────┼────────────┤');

                @foreach($unpaidResidents as $index => $item)
                    @php
                        $resident = $item['resident'];
                        $roomNo = $resident->room ? $resident->room->room_no : 'N/A';
                        $name = Str::limit($resident->name, 16);
                        $prevPending = number_format($item['total_previous_pending'], 0);
                        $currBal = number_format($item['current_balance'], 0);
                        $totalDue = number_format($item['total_due'], 0);
                        $serial = $index + 1;
                    @endphp
                    lines.push(`│ {{ str_pad($serial, 2, ' ', STR_PAD_LEFT) }} │ {{ str_pad($name, 16, ' ', STR_PAD_RIGHT) }} │ #{{ str_pad($roomNo, 6, ' ', STR_PAD_RIGHT) }} │ {{ str_pad($prevPending, 10, ' ', STR_PAD_LEFT) }} │ {{ str_pad($currBal, 9, ' ', STR_PAD_LEFT) }} │ {{ str_pad($totalDue, 9, ' ', STR_PAD_LEFT) }} │`);
                @endforeach

                lines.push('└────┴──────────────────┴──────────┴──────────────┴────────────┴────────────┘');
                lines.push('');

                // Previous Months Details
                lines.push('📋 *PREVIOUS MONTHS PENDING*');
                lines.push('');
                @foreach($unpaidResidents as $item)
                    @php
                        $resident = $item['resident'];
                        $prevMonths = $item['previous_months_details'];
                    @endphp
                    @if(count($prevMonths) > 0)
                        lines.push(`👤 *{{ $resident->name }}*`);
                        @foreach($prevMonths as $prev)
                            @if($prev['balance'] > 0)
                                lines.push(`  📅 {{ $prev['month_name'] }} {{ $prev['year'] }}: {{ number_format($prev['balance'], 2) }} ({{ $prev['status'] }})`);
                            @endif
                        @endforeach
                        lines.push('');
                    @endif
                @endforeach
            @else
                lines.push('✅ *All residents have paid for this month!*');
            @endif

            lines.push('━'.repeat(40));
            lines.push(`📄 Generated: {{ $generated_at }}`);
            lines.push(`👤 By: {{ $user->name }} ({{ $user->role }})`);

            return lines.join('\n');
        }

        // ============================================================
        // 🔥 SHARE AS IMAGE (Using html2canvas)
        // ============================================================
        function shareAsImage() {
            const phone = prompt('Enter WhatsApp number (with country code, e.g., 91):', '91');
            if (!phone) return;

            const cleanPhone = phone.replace(/\D/g, '');
            
            // Show loading
            showToast('📸 Capturing image... Please wait');
            
            const captureElement = document.getElementById('captureArea');
            
            html2canvas(captureElement, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                width: captureElement.scrollWidth,
                height: captureElement.scrollHeight
            }).then(canvas => {
                // Convert to image
                const imageData = canvas.toDataURL('image/png');
                
                // Create a temporary link to download
                const link = document.createElement('a');
                link.download = 'unpaid-report.png';
                link.href = imageData;
                
                // For WhatsApp - we need to upload image first
                // Option 1: Open WhatsApp and user manually attaches image
                // Option 2: Use WhatsApp API (not available for simple web)
                
                // Since we can't directly send image via WhatsApp Web API,
                // we'll download the image and guide user
                link.click();
                
                showToast('✅ Image downloaded! Share it manually on WhatsApp');
                
                // Also open WhatsApp with text message
                const textMsg = encodeURIComponent(`📊 Unpaid Payments Report - {{ $month }} {{ $year }}\n\nPlease find the attached image.`);
                const waUrl = `https://wa.me/${cleanPhone}?text=${textMsg}`;
                
                setTimeout(() => {
                    if (confirm('📱 Open WhatsApp to share the downloaded image?')) {
                        window.open(waUrl, '_blank');
                    }
                }, 1000);
            }).catch(error => {
                showToast('❌ Failed to capture image: ' + error.message);
                console.error(error);
            });
        }

        // ============================================================
        // 🔥 SHARE AS IMAGE - Alternative: Open WhatsApp with image preview
        // ============================================================
        function shareAsImageAlt() {
            const phone = prompt('Enter WhatsApp number (with country code, e.g., 91):', '91');
            if (!phone) return;

            const cleanPhone = phone.replace(/\D/g, '');
            const captureElement = document.getElementById('captureArea');
            
            showToast('📸 Preparing image...');
            
            html2canvas(captureElement, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                width: captureElement.scrollWidth,
                height: captureElement.scrollHeight
            }).then(canvas => {
                // Create image blob
                canvas.toBlob(function(blob) {
                    const file = new File([blob], 'unpaid-report.png', { type: 'image/png' });
                    
                    // Create download link
                    const url = URL.createObjectURL(file);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'unpaid-report.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    
                    // Clean up
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                    
                    showToast('✅ Image downloaded! Share it on WhatsApp');
                    
                    // Open WhatsApp with instructions
                    setTimeout(() => {
                        const msg = encodeURIComponent(`📊 Unpaid Payments Report - {{ $month }} {{ $year }}\n\nI have downloaded the report image. Please check your downloads folder.`);
                        window.open(`https://wa.me/${cleanPhone}?text=${msg}`, '_blank');
                    }, 1500);
                });
            }).catch(error => {
                showToast('❌ Failed to capture image: ' + error.message);
            });
        }

        // ============================================================
        // 🔥 COPY TEXT TO CLIPBOARD
        // ============================================================
        function copyText() {
            const text = generateTextMessage();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast('✅ Report copied to clipboard!');
                }).catch(() => {
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showToast('✅ Report copied to clipboard!');
            } catch (err) {
                showToast('❌ Failed to copy. Please select and copy manually.');
            }
            document.body.removeChild(textarea);
        }

        // ============================================================
        // 🔥 DOWNLOAD PDF
        // ============================================================
        function downloadPDF() {
            const url = window.location.href;
            const pdfUrl = url.replace('/unpaid-image', '/unpaid-pdf-details');
            window.open(pdfUrl, '_blank');
        }

        // ============================================================
        // 🔥 TOAST NOTIFICATION
        // ============================================================
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        // ============================================================
        // 🔥 AUTO PRINT (if URL has print=1)
        // ============================================================
        if (window.location.search.includes('print=1')) {
            setTimeout(function() { window.print(); }, 1000);
        }
    </script>
</body>
</html>