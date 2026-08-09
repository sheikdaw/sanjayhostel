<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $payment->receipt_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .receipt-container { max-width: 700px; margin: 0 auto; border: 2px solid #1a237e; border-radius: 12px; padding: 25px; background: #fff; }
        .receipt-header { text-align: center; border-bottom: 2px solid #1a237e; padding-bottom: 15px; margin-bottom: 15px; }
        .receipt-header .logo { font-size: 22px; font-weight: bold; color: #1a237e; }
        .receipt-header .sub { font-size: 12px; color: #666; margin-top: 3px; }
        .receipt-header .receipt-no { font-size: 14px; font-weight: bold; color: #c62828; margin-top: 5px; }
        .receipt-body { padding: 5px 0; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e0e0e0; }
        .row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #555; width: 40%; }
        .value { width: 60%; text-align: right; }
        .section-title { font-weight: bold; color: #1a237e; font-size: 12px; margin: 10px 0 8px 0; border-bottom: 1px solid #1a237e; padding-bottom: 4px; }
        .amount-row { background: #f5f5f5; padding: 6px 10px; border-radius: 4px; margin: 4px 0; display: flex; justify-content: space-between; }
        .amount-row .label { font-weight: bold; color: #1a237e; }
        .amount-row .value { font-weight: bold; }
        .total-row { background: #e8eaf6; padding: 8px 10px; border-radius: 4px; margin: 6px 0; display: flex; justify-content: space-between; font-size: 13px; }
        .total-row .label { font-weight: bold; color: #1a237e; }
        .total-row .value { font-weight: bold; color: #1a237e; }
        .status-badge { display: inline-block; padding: 3px 12px; border-radius: 12px; font-size: 10px; font-weight: bold; }
        .status-paid { background: #c8e6c9; color: #2e7d32; }
        .status-pending { background: #ffcdd2; color: #c62828; }
        .status-partial { background: #ffe0b2; color: #e65100; }
        .footer { text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid #e0e0e0; font-size: 9px; color: #999; }
        .signature { margin-top: 15px; display: flex; justify-content: space-between; }
        .signature .line { border-top: 1px solid #333; width: 200px; text-align: center; padding-top: 4px; font-size: 9px; color: #666; }
        .watermark { position: relative; }
        .watermark::after {
            content: "PAID";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 60px;
            color: rgba(46, 125, 50, 0.08);
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
        .text-success { color: #2e7d32; }
        .text-danger { color: #c62828; }
        .text-warning { color: #e65100; }
        .text-primary { color: #1a237e; }
        .text-muted { color: #757575; }
        .payment-details { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="receipt-container @if($payment->status == 'PAID') watermark @endif">
        <div class="receipt-header">
            <div class="logo">🏠 {{ $hostel->hostel_name ?? 'Hostel' }}</div>
            <div class="sub">Monthly Rent Receipt</div>
            <div class="receipt-no">Receipt #{{ $payment->receipt_no }}</div>
        </div>

        <div class="receipt-body">
            <div class="section-title">📋 Resident Details</div>
            <div class="row">
                <span class="label">Resident Name</span>
                <span class="value">{{ $resident->name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Resident Code</span>
                <span class="value">{{ $resident->resident_code ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Hostel</span>
                <span class="value">{{ $hostel->hostel_name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Room Number</span>
                <span class="value">#{{ $room->room_no ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Bed Number</span>
                <span class="value">{{ $resident->bed_no ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Phone</span>
                <span class="value">{{ $resident->phone ?? 'N/A' }}</span>
            </div>

            <div class="section-title" style="margin-top:12px;">📅 Payment Details</div>
            <div class="row">
                <span class="label">Month</span>
                <span class="value">{{ date('F', mktime(0, 0, 0, $payment->month, 1)) }} {{ $payment->year }}</span>
            </div>
            <div class="row">
                <span class="label">Payment Date</span>
                <span class="value">{{ $payment->payment_date }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value">
                    <span class="status-badge status-{{ strtolower($payment->status) }}">
                        {{ $payment->status }}
                    </span>
                </span>
            </div>
            @if($payment->transaction_id)
            <div class="row">
                <span class="label">Transaction ID</span>
                <span class="value">{{ $payment->transaction_id }}</span>
            </div>
            @endif

            <div class="section-title" style="margin-top:12px;">💰 Amount Breakdown</div>
            <div class="amount-row">
                <span class="label">Monthly Rent</span>
                <span class="value">₹{{ number_format($payment->rent_amount, 2) }}</span>
            </div>
            @if($payment->discount_amount > 0)
            <div class="amount-row" style="background:#e8f5e9;">
                <span class="label text-success">Discount (-)</span>
                <span class="value text-success">-₹{{ number_format($payment->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($payment->fine_amount > 0)
            <div class="amount-row" style="background:#ffebee;">
                <span class="label text-danger">Fine (+)</span>
                <span class="value text-danger">+₹{{ number_format($payment->fine_amount, 2) }}</span>
            </div>
            @endif
            <div class="amount-row" style="background:#f5f5f5;">
                <span class="label">Cash Paid</span>
                <span class="value">₹{{ number_format($payment->cash_paid_amount, 2) }}</span>
            </div>
            <div class="amount-row" style="background:#f5f5f5;">
                <span class="label">UPI Paid</span>
                <span class="value">₹{{ number_format($payment->upi_paid_amount, 2) }}</span>
            </div>

            <div class="total-row">
                <span class="label">Total Paid</span>
                <span class="value">₹{{ number_format($payment->cash_paid_amount + $payment->upi_paid_amount, 2) }}</span>
            </div>
            <div class="total-row" style="background:{{ $payment->balance_amount > 0 ? '#ffebee' : '#e8f5e9' }};">
                <span class="label">Balance Due</span>
                <span class="value {{ $payment->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                    ₹{{ number_format($payment->balance_amount, 2) }}
                    @if($payment->balance_amount == 0)
                        <span style="font-size:9px;"> (✅ Cleared)</span>
                    @endif
                </span>
            </div>

            @if($payment->status == 'PAID')
            <div style="text-align:center; margin-top:10px; padding:6px; background:#e8f5e9; border-radius:4px;">
                <span style="color:#2e7d32; font-weight:bold; font-size:12px;">✅ Payment Completed</span>
            </div>
            @elseif($payment->status == 'PARTIAL')
            <div style="text-align:center; margin-top:10px; padding:6px; background:#fff3e0; border-radius:4px;">
                <span style="color:#e65100; font-weight:bold; font-size:12px;">🟡 Partial Payment - Balance Due: ₹{{ number_format($payment->balance_amount, 2) }}</span>
            </div>
            @else
            <div style="text-align:center; margin-top:10px; padding:6px; background:#ffebee; border-radius:4px;">
                <span style="color:#c62828; font-weight:bold; font-size:12px;">⏳ Pending Payment - Due: ₹{{ number_format($payment->balance_amount, 2) }}</span>
            </div>
            @endif
        </div>

        <div class="signature">
            <div class="line">Resident Signature</div>
            <div class="line">Authorized Signature</div>
        </div>

        <div class="footer">
            This is a computer-generated receipt. | Generated on: {{ $generated_at }}
        </div>
    </div>
</body>
</html>
