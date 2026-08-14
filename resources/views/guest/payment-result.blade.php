<!-- resources/views/guest/payment-result.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Payment Result</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            padding: 40px 32px;
            text-align: center;
        }
        .icon {
            font-size: 72px;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .success { color: #22c55e; }
        .failed { color: #dc2626; }
        .pending { color: #f59e0b; }
        .message {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
            text-align: left;
        }
        .details .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details .row:last-child { border-bottom: none; }
        .details .label { color: #6b7280; font-size: 14px; }
        .details .value { font-weight: 600; color: #1a3a5c; }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 58, 92, 0.3);
        }
        .btn-outline {
            background: transparent;
            color: #1a3a5c;
            border: 2px solid #1a3a5c;
        }
        .btn-outline:hover {
            background: #1a3a5c;
            color: white;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($success) && $success === true)
            <div class="icon">✅</div>
            <h1 class="success">Payment Successful!</h1>
            <p class="message">{{ $message ?? 'Your rent payment has been completed.' }}</p>
            <div class="details">
                <div class="row">
                    <span class="label">Reference</span>
                    <span class="value">{{ $reference ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span class="label">Amount</span>
                    <span class="value">₹{{ isset($amount) ? number_format($amount, 2) : '0.00' }}</span>
                </div>
                <div class="row">
                    <span class="label">Receipt No</span>
                    <span class="value">{{ $receipt_no ?? $reference ?? 'N/A' }}</span>
                </div>
                @if(isset($transaction_id))
                <div class="row">
                    <span class="label">Transaction ID</span>
                    <span class="value">{{ $transaction_id }}</span>
                </div>
                @endif
                <div class="row">
                    <span class="label">Payment Date</span>
                    <span class="value">{{ now()->format('d M Y h:i A') }}</span>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ url('/guest/payment') }}" class="btn">Make Another Payment</a>
                <a href="{{ url('/') }}" class="btn btn-outline">Go Home</a>
            </div>

        @elseif(isset($success) && $success === null)
            <div class="icon">⏳</div>
            <h1 class="pending">Payment Pending</h1>
            <p class="message">{{ $message ?? 'Your payment is being processed.' }}</p>
            <div class="details">
                <div class="row">
                    <span class="label">Reference</span>
                    <span class="value">{{ $reference ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span class="label">Status</span>
                    <span class="value" style="color: #f59e0b;">Processing</span>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ url('/guest/payment') }}" class="btn">Try Again</a>
                <a href="{{ url('/') }}" class="btn btn-outline">Go Home</a>
            </div>

        @else
            <div class="icon">❌</div>
            <h1 class="failed">Payment Failed</h1>
            <p class="message">{{ $message ?? 'Payment could not be completed.' }}</p>
            <div class="details">
                <div class="row">
                    <span class="label">Reference</span>
                    <span class="value">{{ $reference ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ url('/guest/payment') }}" class="btn">Try Again</a>
                <a href="{{ url('/') }}" class="btn btn-outline">Go Home</a>
            </div>
        @endif
    </div>
</body>
</html>
